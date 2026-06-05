<?php

namespace App\Controllers\Api;

use App\Controllers\BaseController;
use App\Models\BrandModel;
use App\Models\CityBoundaryModel;
use App\Models\DistrictModel;
use CodeIgniter\HTTP\ResponseInterface;
use Config\Database;
use Throwable;

class CatalogController extends BaseController
{
    protected BrandModel $brandModel;
    protected CityBoundaryModel $cityBoundaryModel;
    protected DistrictModel $districtModel;

    public function __construct()
    {
        $this->brandModel = new BrandModel();
        $this->cityBoundaryModel = new CityBoundaryModel();
        $this->districtModel = new DistrictModel();
    }

    
    public function diagnoseDb(): ResponseInterface
    {
        $databaseUrl = getenv('DATABASE_URL') ?: ($_ENV['DATABASE_URL'] ?? $_SERVER['DATABASE_URL'] ?? env('DATABASE_URL'));
        $config = new Database();

        try {
            $db = Database::connect();
            $query = $db->query('SELECT current_database() AS db_name');
            $row = $query->getRowArray();

            return $this->response->setJSON([
                'status' => true,
                'message' => 'Koneksi database berhasil.',
                'data' => [
                    'hostname' => $db->hostname,
                    'database' => $db->database,
                    'port' => $db->port,
                    'db_name' => $row['db_name'] ?? null,
                    'database_url_present' => $databaseUrl ? true : false,
                    'runtime_hostname' => $config->default['hostname'] ?? null,
                    'runtime_port' => $config->default['port'] ?? null,
                    'runtime_username' => $config->default['username'] ?? null,
                ],
            ]);
        } catch (Throwable $exception) {
            return $this->response->setStatusCode(500)->setJSON([
                'status' => false,
                'message' => 'Koneksi database gagal.',
                'error' => $exception->getMessage(),
                'data' => [
                    'database_url_present' => $databaseUrl ? true : false,
                    'runtime_hostname' => $config->default['hostname'] ?? null,
                    'runtime_port' => $config->default['port'] ?? null,
                    'runtime_username' => $config->default['username'] ?? null,
                ],
            ]);
        }
    }
    public function brands(): ResponseInterface
    {
        return $this->response->setJSON([
            'status' => true,
            'data'   => $this->brandModel->getActive(),
        ]);
    }

    public function districts(): ResponseInterface
    {
        return $this->response->setJSON([
            'status' => true,
            'data'   => $this->districtModel->getAllForMap(),
        ]);
    }

    public function cityBoundary(): ResponseInterface
    {
        return $this->response->setJSON([
            'status' => true,
            'data'   => $this->cityBoundaryModel->getActiveBoundary(),
        ]);
    }

    public function storeBrand(): ResponseInterface
    {
        [$data, $errors] = $this->validateBrandPayload($this->request->getJSON(true) ?: $this->request->getPost());

        if ($errors !== []) {
            return $this->response->setStatusCode(422)->setJSON([
                'status' => false,
                'message' => 'Validasi brand gagal.',
                'errors' => $errors,
            ]);
        }

        try {
            $id = $this->brandModel->insert($data, true);
        } catch (Throwable $exception) {
            return $this->response->setStatusCode(500)->setJSON([
                'status' => false,
                'message' => 'Gagal menyimpan brand.',
                'error' => ENVIRONMENT === 'development' ? $exception->getMessage() : null,
            ]);
        }

        return $this->response->setStatusCode(201)->setJSON([
            'status' => true,
            'message' => 'Brand berhasil ditambahkan.',
            'data' => $this->brandModel->find($id),
        ]);
    }

    public function updateBrand(string $id): ResponseInterface
    {
        if (!$this->brandModel->find($id)) {
            return $this->response->setStatusCode(404)->setJSON([
                'status' => false,
                'message' => 'Brand tidak ditemukan.',
            ]);
        }

        [$data, $errors] = $this->validateBrandPayload($this->request->getJSON(true) ?: $this->request->getRawInput(), true);

        if ($errors !== []) {
            return $this->response->setStatusCode(422)->setJSON([
                'status' => false,
                'message' => 'Validasi brand gagal.',
                'errors' => $errors,
            ]);
        }

        $this->brandModel->update($id, $data);

        return $this->response->setJSON([
            'status' => true,
            'message' => 'Brand berhasil diperbarui.',
            'data' => $this->brandModel->find($id),
        ]);
    }

    public function deleteBrand(string $id): ResponseInterface
    {
        $existing = $this->brandModel->find($id);
        if (!$existing) {
            return $this->response->setStatusCode(404)->setJSON([
                'status' => false,
                'message' => 'Brand tidak ditemukan.',
            ]);
        }

        $this->brandModel->delete($id);

        return $this->response->setJSON([
            'status' => true,
            'message' => 'Brand berhasil dihapus.',
            'data' => $existing,
        ]);
    }

    public function storeDistrict(): ResponseInterface
    {
        [$data, $errors] = $this->validateDistrictPayload($this->request->getJSON(true) ?: $this->request->getPost());

        if ($errors !== []) {
            return $this->response->setStatusCode(422)->setJSON([
                'status' => false,
                'message' => 'Validasi kecamatan gagal.',
                'errors' => $errors,
            ]);
        }

        try {
            $id = $this->districtModel->insert($data, true);
        } catch (Throwable $exception) {
            return $this->response->setStatusCode(500)->setJSON([
                'status' => false,
                'message' => 'Gagal menyimpan kecamatan.',
                'error' => ENVIRONMENT === 'development' ? $exception->getMessage() : null,
            ]);
        }

        return $this->response->setStatusCode(201)->setJSON([
            'status' => true,
            'message' => 'Kecamatan berhasil ditambahkan.',
            'data' => $this->districtModel->find($id),
        ]);
    }

    public function updateDistrict(string $id): ResponseInterface
    {
        if (!$this->districtModel->find($id)) {
            return $this->response->setStatusCode(404)->setJSON([
                'status' => false,
                'message' => 'Kecamatan tidak ditemukan.',
            ]);
        }

        [$data, $errors] = $this->validateDistrictPayload($this->request->getJSON(true) ?: $this->request->getRawInput(), true);

        if ($errors !== []) {
            return $this->response->setStatusCode(422)->setJSON([
                'status' => false,
                'message' => 'Validasi kecamatan gagal.',
                'errors' => $errors,
            ]);
        }

        $this->districtModel->update($id, $data);

        return $this->response->setJSON([
            'status' => true,
            'message' => 'Kecamatan berhasil diperbarui.',
            'data' => $this->districtModel->find($id),
        ]);
    }

    public function deleteDistrict(string $id): ResponseInterface
    {
        $existing = $this->districtModel->find($id);
        if (!$existing) {
            return $this->response->setStatusCode(404)->setJSON([
                'status' => false,
                'message' => 'Kecamatan tidak ditemukan.',
            ]);
        }

        $this->districtModel->delete($id);

        return $this->response->setJSON([
            'status' => true,
            'message' => 'Kecamatan berhasil dihapus.',
            'data' => $existing,
        ]);
    }

    protected function validateBrandPayload(array $payload, bool $isUpdate = false): array
    {
        $data = [];
        $errors = [];

        foreach (['name', 'slug', 'description', 'accent_color'] as $field) {
            if ($isUpdate && !array_key_exists($field, $payload)) {
                continue;
            }

            $value = trim((string) ($payload[$field] ?? ''));
            if (in_array($field, ['name', 'slug'], true) && $value === '') {
                $errors[$field] = ucfirst($field) . ' wajib diisi.';
                continue;
            }

            $data[$field] = $value !== '' ? $value : null;
        }

        if (!$isUpdate || array_key_exists('is_active', $payload)) {
            $data['is_active'] = filter_var($payload['is_active'] ?? true, FILTER_VALIDATE_BOOL, FILTER_NULL_ON_FAILURE);
            $data['is_active'] = $data['is_active'] ?? true;
        }

        return [$data, $errors];
    }

    protected function validateDistrictPayload(array $payload, bool $isUpdate = false): array
    {
        $data = [];
        $errors = [];

        foreach (['name', 'slug', 'geojson', 'notes'] as $field) {
            if ($isUpdate && !array_key_exists($field, $payload)) {
                continue;
            }

            $value = trim((string) ($payload[$field] ?? ''));
            if (in_array($field, ['name', 'slug'], true) && $value === '') {
                $errors[$field] = ucfirst($field) . ' wajib diisi.';
                continue;
            }

            $data[$field] = $value !== '' ? $value : null;
        }

        if (isset($data['geojson']) && $data['geojson'] !== null) {
            json_decode($data['geojson'], true);
            if (json_last_error() !== JSON_ERROR_NONE) {
                $errors['geojson'] = 'GeoJSON kecamatan harus berupa JSON yang valid.';
            }
        }

        if (!$isUpdate || array_key_exists('is_active', $payload)) {
            $data['is_active'] = filter_var($payload['is_active'] ?? true, FILTER_VALIDATE_BOOL, FILTER_NULL_ON_FAILURE);
            $data['is_active'] = $data['is_active'] ?? true;
        }

        return [$data, $errors];
    }
}
