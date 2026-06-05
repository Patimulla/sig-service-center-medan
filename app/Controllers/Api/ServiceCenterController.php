<?php

namespace App\Controllers\Api;

use App\Controllers\BaseController;
use App\Libraries\GeoBoundaryLocator;
use App\Models\DistrictModel;
use App\Models\ServiceCenterModel;
use CodeIgniter\HTTP\ResponseInterface;
use RuntimeException;
use Throwable;

class ServiceCenterController extends BaseController
{
    protected ServiceCenterModel $model;
    protected DistrictModel $districtModel;
    protected GeoBoundaryLocator $boundaryLocator;

    public function __construct()
    {
        $this->model = new ServiceCenterModel();
        $this->districtModel = new DistrictModel();
        $this->boundaryLocator = new GeoBoundaryLocator();
    }

    /**
     * GET /api/service-center
     * Menampilkan semua service center
     *
     * Contoh response:
     * {
     *   "status": true,
     *   "message": "Data service center berhasil diambil",
     *   "data": [
     *     {
     *       "id": 1,
     *       "nama_tempat": "Samsung Service Center Medan",
     *       "brand": "Samsung",
     *       "alamat": "Jl. Iskandar Muda No.101, Medan",
     *       "kecamatan": "Medan Baru",
     *       "latitude": "3.5629",
     *       "longitude": "98.6565",
     *       "jam_buka": "09:00:00",
     *       "jam_tutup": "17:00:00",
     *       "hari_operasional": "Senin-Sabtu",
     *       "no_telepon": "061-4155678",
     *       "email": "service.medan@samsung.com",
     *       "website": "https://www.samsung.com/id/support/",
     *       "jenis_layanan": "Servis layar, baterai, kamera, software, motherboard",
     *       "estimasi_servis": "1-3 hari kerja",
     *       "created_at": "2026-04-24 12:00:00"
     *     },
     *     ...
     *   ]
     * }
     */
    public function index(): ResponseInterface
    {
        $data = $this->model->getAll();

        return $this->response->setJSON([
            'status'  => true,
            'message' => 'Data service center berhasil diambil',
            'total'   => count($data),
            'data'    => $data,
        ]);
    }

    /**
     * GET /api/service-center/{id}
     * Menampilkan detail service center berdasarkan ID
     */
    public function show(string $id): ResponseInterface
    {
        $data = $this->model->getById($id);

        if (!$data) {
            return $this->response->setStatusCode(404)->setJSON([
                'status'  => false,
                'message' => 'Data service center tidak ditemukan',
            ]);
        }

        return $this->response->setJSON([
            'status'  => true,
            'message' => 'Detail service center berhasil diambil',
            'data'    => $data,
        ]);
    }

    /**
     * GET /api/service-center/search?alamat=gatot
     * Pencarian berdasarkan alamat (ILIKE)
     */
    public function search(): ResponseInterface
    {
        $alamat = $this->request->getGet('alamat');

        if (!$alamat || strlen(trim($alamat)) < 2) {
            return $this->response->setStatusCode(400)->setJSON([
                'status'  => false,
                'message' => 'Parameter alamat wajib diisi (minimal 2 karakter)',
            ]);
        }

        $data = $this->model->searchByAlamat($alamat);

        return $this->response->setJSON([
            'status'  => true,
            'message' => 'Hasil pencarian service center',
            'keyword' => $alamat,
            'total'   => count($data),
            'data'    => $data,
        ]);
    }

    /**
     * GET /api/service-center/filter?brand=Samsung
     * Filter berdasarkan brand
     */
    public function filter(): ResponseInterface
    {
        $brand = $this->request->getGet('brand');

        if (!$brand) {
            return $this->response->setStatusCode(400)->setJSON([
                'status'  => false,
                'message' => 'Parameter brand wajib diisi (Samsung, Xiaomi, Oppo, Vivo, Apple)',
            ]);
        }

        $validBrands = ['Samsung', 'Xiaomi', 'Oppo', 'OPPO', 'Vivo', 'Apple', 'Realme'];
        if (!in_array($brand, $validBrands)) {
            return $this->response->setStatusCode(400)->setJSON([
                'status'  => false,
                'message' => 'Brand tidak valid. Pilihan: ' . implode(', ', $validBrands),
            ]);
        }

        $data = $this->model->getAll($brand);

        return $this->response->setJSON([
            'status'  => true,
            'message' => "Data service center brand {$brand}",
            'brand'   => $brand,
            'total'   => count($data),
            'data'    => $data,
        ]);
    }

    /**
     * GET /api/service-center/nearest?lat=3.5920&lng=98.6787&limit=5
     * Mencari lokasi terdekat dari titik tertentu
     *
     * Contoh response:
     * {
     *   "status": true,
     *   "message": "Data service center terdekat berhasil diambil",
     *   "data": [
     *     {
     *       "id": 9,
     *       "nama_tempat": "iBox Authorized Service Provider Medan",
     *       "brand": "Apple",
     *       "latitude": "3.5826",
     *       "longitude": "98.6790",
     *       "jarak_meter": "1045",
     *       "jarak_km": "1.05",
     *       ...
     *     }
     *   ]
     * }
     */
    public function nearest(): ResponseInterface
    {
        $lat   = $this->request->getGet('lat');
        $lng   = $this->request->getGet('lng');
        $limit = $this->request->getGet('limit') ?? 5;

        if (!$lat || !$lng) {
            return $this->response->setStatusCode(400)->setJSON([
                'status'  => false,
                'message' => 'Parameter lat dan lng wajib diisi',
            ]);
        }

        $data = $this->model->getNearby(
            (float) $lat,
            (float) $lng,
            (int) $limit
        );

        return $this->response->setJSON([
            'status'  => true,
            'message' => 'Data service center terdekat berhasil diambil',
            'origin'  => ['lat' => (float) $lat, 'lng' => (float) $lng],
            'total'   => count($data),
            'data'    => $data,
        ]);
    }

    public function store(): ResponseInterface
    {
        $payload = $this->hydrateDetectedDistrict($this->getPayload());
        [$data, $errors] = $this->validateCenterPayload($payload);

        if ($errors !== []) {
            return $this->response->setStatusCode(422)->setJSON([
                'status'  => false,
                'message' => 'Validasi data service center gagal',
                'errors'  => $errors,
            ]);
        }

        $uploadedPhotoUrl = null;
        try {
            $data = $this->applyPhotoPayload($data);
            $uploadedPhotoUrl = $data['foto_lokasi'] ?? null;
            $created = $this->model->createCenter($data);
        } catch (Throwable $exception) {
            if ($uploadedPhotoUrl) {
                $this->deleteStorageObjectByUrl($uploadedPhotoUrl);
            }
            return $this->response->setStatusCode(500)->setJSON([
                'status'  => false,
                'message' => 'Gagal menambahkan service center ke Supabase',
                'error'   => ENVIRONMENT === 'development' ? $exception->getMessage() : null,
            ]);
        }

        if (!$created) {
            return $this->response->setStatusCode(500)->setJSON([
                'status'  => false,
                'message' => 'Data service center gagal disimpan',
            ]);
        }

        return $this->response->setStatusCode(201)->setJSON([
            'status'  => true,
            'message' => 'Service center berhasil ditambahkan',
            'data'    => $created,
        ]);
    }

    public function update(string $id): ResponseInterface
    {
        $existing = $this->model->getById($id);

        if (!$existing) {
            return $this->response->setStatusCode(404)->setJSON([
                'status'  => false,
                'message' => 'Data service center tidak ditemukan',
            ]);
        }

        $payload = $this->hydrateDetectedDistrict($this->getPayload(), $existing);
        [$data, $errors] = $this->validateCenterPayload($payload, true);

        if ($errors !== []) {
            return $this->response->setStatusCode(422)->setJSON([
                'status'  => false,
                'message' => 'Validasi update service center gagal',
                'errors'  => $errors,
            ]);
        }

        $uploadedPhotoUrl = null;
        try {
            $data = $this->applyPhotoPayload($data);
            $uploadedPhotoUrl = $data['foto_lokasi'] ?? null;
            $updated = $this->model->updateCenter($id, $data);

            if ($updated && array_key_exists('foto_lokasi', $data) && ($existing['foto_lokasi'] ?? null) && $existing['foto_lokasi'] !== $data['foto_lokasi']) {
                $this->deleteStorageObjectByUrl((string) $existing['foto_lokasi']);
            }
        } catch (Throwable $exception) {
            if ($uploadedPhotoUrl && $uploadedPhotoUrl !== ($existing['foto_lokasi'] ?? null)) {
                $this->deleteStorageObjectByUrl($uploadedPhotoUrl);
            }
            return $this->response->setStatusCode(500)->setJSON([
                'status'  => false,
                'message' => 'Gagal memperbarui data service center di Supabase',
                'error'   => ENVIRONMENT === 'development' ? $exception->getMessage() : null,
            ]);
        }

        if (!$updated) {
            return $this->response->setStatusCode(500)->setJSON([
                'status'  => false,
                'message' => 'Data service center gagal diperbarui',
            ]);
        }

        return $this->response->setJSON([
            'status'  => true,
            'message' => 'Service center berhasil diperbarui',
            'data'    => $updated,
        ]);
    }

    public function delete(string $id): ResponseInterface
    {
        $existing = $this->model->getById($id);

        if (!$existing) {
            return $this->response->setStatusCode(404)->setJSON([
                'status'  => false,
                'message' => 'Data service center tidak ditemukan',
            ]);
        }

        try {
            $deleted = $this->model->deleteCenter($id);
            if ($deleted && !empty($existing['foto_lokasi'])) {
                $this->deleteStorageObjectByUrl((string) $existing['foto_lokasi']);
            }
        } catch (Throwable $exception) {
            return $this->response->setStatusCode(500)->setJSON([
                'status'  => false,
                'message' => 'Gagal menghapus service center dari Supabase',
                'error'   => ENVIRONMENT === 'development' ? $exception->getMessage() : null,
            ]);
        }

        if (!$deleted) {
            return $this->response->setStatusCode(500)->setJSON([
                'status'  => false,
                'message' => 'Data service center gagal dihapus',
            ]);
        }

        return $this->response->setJSON([
            'status'  => true,
            'message' => 'Service center berhasil dihapus',
            'data'    => $existing,
        ]);
    }

    protected function getPayload(): array
    {
        $contentType = strtolower((string) $this->request->getHeaderLine('Content-Type'));
        if (str_contains($contentType, 'application/json')) {
            $json = $this->request->getJSON(true);

            if (is_array($json) && $json !== []) {
                return $json;
            }
        }

        $payload = $this->request->getPost();

        if (is_array($payload) && $payload !== []) {
            return $payload;
        }

        $raw = $this->request->getRawInput();

        if (is_array($raw) && $raw !== []) {
            return $raw;
        }

        return [];
    }

    protected function validateCenterPayload(array $payload, bool $isUpdate = false): array
    {
        $fieldRules = [
            'nama_tempat' => ['required' => true, 'label' => 'Nama tempat'],
            'brand' => ['required' => true, 'label' => 'Brand'],
            'alamat' => ['required' => true, 'label' => 'Alamat'],
            'kecamatan' => ['required' => true, 'label' => 'Kecamatan'],
            'no_telepon' => ['required' => false, 'label' => 'Nomor telepon'],
            'jam_buka' => ['required' => true, 'label' => 'Jam buka'],
            'jam_tutup' => ['required' => true, 'label' => 'Jam tutup'],
            'hari_operasional' => ['required' => true, 'label' => 'Hari operasional'],
            'jenis_layanan' => ['required' => true, 'label' => 'Jenis layanan'],
        ];
        $errors = [];
        $data = [];

        foreach ($fieldRules as $field => $rule) {
            if ($isUpdate && !array_key_exists($field, $payload)) {
                continue;
            }

            $value = trim((string) ($payload[$field] ?? ''));

            if ($value === '') {
                if ($rule['required']) {
                    $errors[$field] = $rule['label'] . ' wajib diisi.';
                } else {
                    $data[$field] = null;
                }

                continue;
            }

            $data[$field] = $value;
        }

        if (!$isUpdate || array_key_exists('tipe_lokasi_key', $payload)) {
            $typeKey = trim((string) ($payload['tipe_lokasi_key'] ?? ''));

            if ($typeKey === '') {
                $errors['tipe_lokasi_key'] = 'Tipe lokasi wajib dipilih.';
            } elseif (!in_array($typeKey, ['mall', 'ruko', 'gerai-mandiri'], true)) {
                $errors['tipe_lokasi_key'] = 'Tipe lokasi tidak valid.';
            } else {
                $data['tipe_lokasi_key'] = $typeKey;
            }
        }

        foreach (['jam_buka', 'jam_tutup'] as $timeField) {
            if (isset($data[$timeField]) && !preg_match('/^\d{2}:\d{2}(:\d{2})?$/', $data[$timeField])) {
                $errors[$timeField] = ucfirst(str_replace('_', ' ', $timeField)) . ' harus berformat HH:MM.';
            }
        }

        foreach (['latitude', 'longitude'] as $coordinateField) {
            if ($isUpdate && !array_key_exists($coordinateField, $payload)) {
                continue;
            }

            $raw = trim((string) ($payload[$coordinateField] ?? ''));

            if ($raw === '') {
                if (!$isUpdate) {
                    $errors[$coordinateField] = ucfirst($coordinateField) . ' wajib diisi.';
                }

                continue;
            }

            if (!is_numeric($raw)) {
                $errors[$coordinateField] = ucfirst($coordinateField) . ' harus berupa angka.';
                continue;
            }

            $data[$coordinateField] = (float) $raw;
        }

        if (!$isUpdate || array_key_exists('rating', $payload)) {
            $rating = trim((string) ($payload['rating'] ?? '5.0'));

            if ($rating === '') {
                $errors['rating'] = 'Rating wajib diisi.';
            } elseif (!is_numeric($rating)) {
                $errors['rating'] = 'Rating harus berupa angka.';
            } else {
                $numericRating = round((float) $rating, 1);

                if ($numericRating < 0 || $numericRating > 5) {
                    $errors['rating'] = 'Rating harus berada di rentang 0.0 sampai 5.0.';
                } else {
                    $data['rating'] = number_format($numericRating, 1, '.', '');
                }
            }
        }

        $photoField = $this->request->getFile('foto_lokasi_file');
        if ($photoField && $photoField->isValid() && !$photoField->hasMoved()) {
            $extension = strtolower($photoField->getExtension());
            if (!in_array($extension, ['jpg', 'jpeg', 'png', 'webp'], true)) {
                $errors['foto_lokasi_file'] = 'Foto lokasi harus berformat JPG, PNG, atau WEBP.';
            }
        }

        if (($payload['remove_foto_lokasi'] ?? '') === '1') {
            $data['foto_lokasi'] = null;
        }

        if ($isUpdate && $data === []) {
            $errors['payload'] = 'Minimal satu field harus dikirim untuk update.';
        }

        return [$data, $errors];
    }

    protected function hydrateDetectedDistrict(array $payload, ?array $existing = null): array
    {
        $coordinatesProvided = array_key_exists('latitude', $payload) || array_key_exists('longitude', $payload);
        $latitude = $payload['latitude'] ?? ($existing['latitude'] ?? null);
        $longitude = $payload['longitude'] ?? ($existing['longitude'] ?? null);

        if ($latitude === null || $longitude === null || !is_numeric((string) $latitude) || !is_numeric((string) $longitude)) {
            return $payload;
        }

        $matchedDistrict = $this->detectDistrictByPoint((float) $latitude, (float) $longitude);
        if ($matchedDistrict !== null) {
            $payload['kecamatan'] = $matchedDistrict['name'];
        } elseif ($coordinatesProvided) {
            $payload['kecamatan'] = '';
        }

        return $payload;
    }

    protected function detectDistrictByPoint(float $latitude, float $longitude): ?array
    {
        foreach ($this->districtModel->getActiveWithGeoJson() as $district) {
            if ($this->boundaryLocator->containsPoint($district['geojson'] ?? null, $latitude, $longitude)) {
                return $district;
            }
        }

        return null;
    }

    protected function applyPhotoPayload(array $data): array
    {
        $photoField = $this->request->getFile('foto_lokasi_file');
        if ($photoField && $photoField->isValid() && !$photoField->hasMoved()) {
            $data['foto_lokasi'] = $this->storeLocationPhoto($photoField);
        }

        return $data;
    }

    protected function storeLocationPhoto(object $file): string
    {
        $serviceRoleKey = trim($this->getSupabaseEnv('SUPABASE_SERVICE_ROLE_KEY', 'supabase.serviceRoleKey'));
        if ($serviceRoleKey === '') {
            throw new RuntimeException('Supabase service role key belum diisi di .env pada supabase.serviceRoleKey.');
        }

        $projectUrl = $this->getSupabaseProjectUrl();
        $bucket = trim($this->getSupabaseEnv('SUPABASE_STORAGE_BUCKET', 'supabase.storageBucket', 'service-center-photos'));
        $pathPrefix = trim($this->getSupabaseEnv('SUPABASE_STORAGE_PATH_PREFIX', 'supabase.storagePathPrefix', 'service-centers'));
        $objectPath = trim($pathPrefix . '/' . date('Y/m') . '/' . $file->getRandomName(), '/');
        $uploadUrl = rtrim($projectUrl, '/') . '/storage/v1/object/' . rawurlencode($bucket) . '/' . str_replace('%2F', '/', rawurlencode($objectPath));
        $mimeType = $file->getMimeType() ?: 'application/octet-stream';
        $contents = file_get_contents($file->getTempName());

        if ($contents === false) {
            throw new RuntimeException('File foto lokasi tidak dapat dibaca sebelum diupload ke Supabase Storage.');
        }

        $curl = curl_init($uploadUrl);
        curl_setopt_array($curl, [
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_POSTFIELDS => $contents,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPHEADER => [
                'Authorization: Bearer ' . $serviceRoleKey,
                'apikey: ' . $serviceRoleKey,
                'Content-Type: ' . $mimeType,
                'x-upsert: true',
            ],
        ]);

        $response = curl_exec($curl);
        $statusCode = (int) curl_getinfo($curl, CURLINFO_RESPONSE_CODE);
        $curlError = curl_error($curl);
        curl_close($curl);

        if ($response === false || $statusCode >= 400) {
            $message = 'Upload foto lokasi ke Supabase Storage gagal.';
            if ($curlError !== '') {
                $message .= ' ' . $curlError;
            } elseif (is_string($response) && $response !== '') {
                $decoded = json_decode($response, true);
                $message .= ' ' . (is_array($decoded) ? json_encode($decoded) : $response);
            }

            throw new RuntimeException($message);
        }

        return rtrim($projectUrl, '/') . '/storage/v1/object/public/' . rawurlencode($bucket) . '/' . str_replace('%2F', '/', rawurlencode($objectPath));
    }

    protected function getSupabaseProjectUrl(): string
    {
        $projectUrl = trim($this->getSupabaseEnv('SUPABASE_PROJECT_URL', 'supabase.projectUrl'));
        if ($projectUrl !== '') {
            return $projectUrl;
        }

        $hostname = trim((string) env('database.default.hostname'));
        if ($hostname !== '' && str_starts_with($hostname, 'db.')) {
            return 'https://' . substr($hostname, 3);
        }

        throw new RuntimeException('Supabase project URL belum dikonfigurasi.');
    }

    protected function deleteStorageObjectByUrl(string $photoUrl): void
    {
        $serviceRoleKey = trim($this->getSupabaseEnv('SUPABASE_SERVICE_ROLE_KEY', 'supabase.serviceRoleKey'));
        if ($serviceRoleKey === '' || $photoUrl === '') {
            return;
        }

        $projectUrl = rtrim($this->getSupabaseProjectUrl(), '/');
        $bucket = trim($this->getSupabaseEnv('SUPABASE_STORAGE_BUCKET', 'supabase.storageBucket', 'service-center-photos'));
        $publicPrefix = $projectUrl . '/storage/v1/object/public/' . rawurlencode($bucket) . '/';

        if (!str_starts_with($photoUrl, $publicPrefix)) {
            return;
        }

        $encodedPath = substr($photoUrl, strlen($publicPrefix));
        $objectPath = rawurldecode($encodedPath);
        $deleteUrl = $projectUrl . '/storage/v1/object/' . rawurlencode($bucket) . '/' . str_replace('%2F', '/', rawurlencode($objectPath));

        $curl = curl_init($deleteUrl);
        curl_setopt_array($curl, [
            CURLOPT_CUSTOMREQUEST => 'DELETE',
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPHEADER => [
                'Authorization: Bearer ' . $serviceRoleKey,
                'apikey: ' . $serviceRoleKey,
            ],
        ]);
        curl_exec($curl);
        curl_close($curl);
    }
}
