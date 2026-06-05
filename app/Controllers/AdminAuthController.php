<?php

namespace App\Controllers;

use CodeIgniter\HTTP\RedirectResponse;
use RuntimeException;

class AdminAuthController extends BaseController
{
    public function login(): string|RedirectResponse
    {
        $config = config('AdminAuth');
        $auth = session()->get($config->sessionKey);

        if (is_array($auth) && ($auth['authenticated'] ?? false) === true) {
            return redirect()->to(base_url('admin/dashboard'));
        }

        return view('admin_login', [
            'error' => session()->getFlashdata('admin_login_error'),
            'email' => (string) old('email'),
        ]);
    }

    public function attempt(): RedirectResponse
    {
        $config = config('AdminAuth');
        $email = trim((string) $this->request->getPost('email'));
        $password = (string) $this->request->getPost('password');

        if ($email === '' || $password === '') {
            session()->setFlashdata('admin_login_error', 'Email dan password wajib diisi.');

            return redirect()->back()->withInput();
        }

        try {
            $authData = $this->authenticateWithSupabase($email, $password);
        } catch (RuntimeException $exception) {
            session()->setFlashdata('admin_login_error', $exception->getMessage());

            return redirect()->back()->withInput();
        }

        session()->set($config->sessionKey, [
            'authenticated' => true,
            'email'         => $email,
            'user_id'       => (string) ($authData['user']['id'] ?? ''),
            'access_token'  => (string) ($authData['access_token'] ?? ''),
            'logged_in_at'  => date('c'),
        ]);

        $redirect = session()->get('admin_auth_redirect');
        session()->remove('admin_auth_redirect');

        return redirect()->to(is_string($redirect) && $redirect !== '' ? $redirect : base_url('admin/dashboard'));
    }

    public function logout(): RedirectResponse
    {
        $config = config('AdminAuth');
        session()->remove($config->sessionKey);
        session()->remove('admin_auth_redirect');

        return redirect()->to(base_url('login'));
    }

    private function authenticateWithSupabase(string $email, string $password): array
    {
        $projectUrl = rtrim(trim((string) env('supabase.projectUrl')), '/');
        $serviceRoleKey = trim((string) env('supabase.serviceRoleKey'));

        if ($projectUrl === '' || $serviceRoleKey === '') {
            throw new RuntimeException('Konfigurasi Supabase Auth belum lengkap.');
        }

        $endpoint = $projectUrl . '/auth/v1/token?grant_type=password';
        $payload = json_encode([
            'email'    => $email,
            'password' => $password,
        ], JSON_THROW_ON_ERROR);

        $curl = curl_init($endpoint);
        curl_setopt_array($curl, [
            CURLOPT_POST           => true,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPHEADER     => [
                'Content-Type: application/json',
                'apikey: ' . $serviceRoleKey,
                'Authorization: Bearer ' . $serviceRoleKey,
            ],
            CURLOPT_POSTFIELDS     => $payload,
            CURLOPT_TIMEOUT        => 15,
        ]);

        $response = curl_exec($curl);
        $statusCode = (int) curl_getinfo($curl, CURLINFO_HTTP_CODE);
        $curlError = curl_error($curl);
        curl_close($curl);

        if ($response === false || $curlError !== '') {
            throw new RuntimeException('Koneksi ke Supabase Auth gagal.');
        }

        $decoded = json_decode($response, true);
        if ($statusCode >= 400) {
            $message = is_array($decoded)
                ? (string) ($decoded['msg'] ?? $decoded['error_description'] ?? $decoded['message'] ?? '')
                : '';

            throw new RuntimeException($message !== '' ? $message : 'Email atau password admin tidak valid.');
        }

        if (!is_array($decoded) || empty($decoded['user'])) {
            throw new RuntimeException('Respons login dari Supabase tidak valid.');
        }

        return $decoded;
    }
}
