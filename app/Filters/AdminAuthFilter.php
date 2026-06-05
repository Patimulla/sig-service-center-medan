<?php

namespace App\Filters;

use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;

class AdminAuthFilter implements FilterInterface
{
    public function before(RequestInterface $request, $arguments = null)
    {
        $config = config('AdminAuth');
        $session = session();
        $auth = $session->get($config->sessionKey);

        if (is_array($auth) && ($auth['authenticated'] ?? false) === true) {
            return null;
        }

        $session->set('admin_auth_redirect', current_url());

        return redirect()->to(base_url('login'));
    }

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
        return null;
    }
}
