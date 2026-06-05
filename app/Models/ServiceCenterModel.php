<?php

namespace App\Models;

use CodeIgniter\Model;

class ServiceCenterModel extends Model
{
    protected $table      = 'service_center_medan';
    protected $primaryKey = 'id';
    protected $allowedFields = [
        'nama_service',
        'brand',
        'alamat',
        'kecamatan',
        'no_telepon',
        'jam_buka',
        'jam_tutup',
        'hari_operasional',
        'tipe_lokasi',
        'layanan',
        'latitude',
        'longitude',
        'rating',
        'foto_lokasi',
        'location_type_key',
    ];
    protected $returnType = 'array';

    protected $useTimestamps = false;

    protected $validationRules = [
        'nama_service' => 'required|min_length[3]|max_length[255]',
        'brand'        => 'required',
    ];

    protected function baseSelect(): string
    {
        return "id,
                nama_service AS nama_tempat,
                brand,
                alamat,
                kecamatan,
                latitude,
                longitude,
                jam_buka,
                jam_tutup,
                hari_operasional,
                no_telepon,
                NULL::text AS email,
                NULL::text AS website,
                layanan AS jenis_layanan,
                tipe_lokasi AS estimasi_servis,
                tipe_lokasi AS tipe_lokasi,
                COALESCE(location_type_key,
                    CASE
                        WHEN LOWER(COALESCE(tipe_lokasi, '')) LIKE '%mall%' THEN 'mall'
                        WHEN LOWER(COALESCE(tipe_lokasi, '')) LIKE '%ruko%' THEN 'ruko'
                        ELSE 'gerai-mandiri'
                    END
                ) AS tipe_lokasi_key,
                foto_lokasi,
                ROUND(COALESCE(rating, 5.0)::numeric, 1) AS rating,
                NULL::timestamp AS created_at";
    }

    public function locationTypeLabel(?string $typeKey): string
    {
        return match ($typeKey) {
            'mall' => 'Dalam Mall',
            'ruko' => 'Dalam Ruko',
            default => 'Gerai Mandiri',
        };
    }

    protected function normalizeBrand(?string $brand): ?string
    {
        if ($brand === null) {
            return null;
        }

        return match (strtolower($brand)) {
            'oppo' => 'OPPO',
            default => $brand,
        };
    }

    public function getAll(?string $brand = null): array
    {
        $builder = $this->db->table($this->table)
            ->select($this->baseSelect())
            ->orderBy('nama_tempat', 'ASC');

        if ($brand !== null) {
            $builder->where('brand', $this->normalizeBrand($brand));
        }

        return $builder->get()->getResultArray();
    }

    public function getById(int|string $id): ?array
    {
        $builder = $this->db->table($this->table)
            ->select($this->baseSelect())
            ->where('id', $id);

        return $builder->get()->getRowArray();
    }

    public function searchByAlamat(string $keyword): array
    {
        return $this->db->table($this->table)
            ->select($this->baseSelect())
            ->like('alamat', $keyword, 'both', null, true)
            ->orderBy('nama_tempat', 'ASC')
            ->get()
            ->getResultArray();
    }

    public function getNearby(float $lat, float $lng, int $limit = 5): array
    {
        $sql = "SELECT
                    id,
                    nama_tempat,
                    brand,
                    alamat,
                    kecamatan,
                    latitude,
                    longitude,
                    jam_buka,
                    jam_tutup,
                    hari_operasional,
                    no_telepon,
                    email,
                    website,
                    jenis_layanan,
                    estimasi_servis,
                    tipe_lokasi,
                    tipe_lokasi_key,
                    foto_lokasi,
                    rating,
                    ROUND(
                        CAST((6371000 * acos(
                            cos(radians(?)) * cos(radians(latitude)) *
                            cos(radians(longitude) - radians(?)) +
                            sin(radians(?)) * sin(radians(latitude))
                        )) AS NUMERIC),
                        0
                    ) AS jarak_meter,
                    ROUND(
                        CAST((6371000 * acos(
                            cos(radians(?)) * cos(radians(latitude)) *
                            cos(radians(longitude) - radians(?)) +
                            sin(radians(?)) * sin(radians(latitude))
                        )) / 1000.0 AS NUMERIC),
                        2
                    ) AS jarak_km,
                    created_at
                FROM (
                    SELECT {$this->baseSelect()}
                    FROM {$this->table}
                ) AS sc
                ORDER BY jarak_meter ASC
                LIMIT ?";

        return $this->db
            ->query($sql, [$lat, $lng, $lat, $lat, $lng, $lat, $limit])
            ->getResultArray();
    }

    protected function mapWriteData(array $data): array
    {
        $fieldMap = [
            'nama_tempat'      => 'nama_service',
            'brand'            => 'brand',
            'alamat'           => 'alamat',
            'kecamatan'        => 'kecamatan',
            'no_telepon'       => 'no_telepon',
            'jam_buka'         => 'jam_buka',
            'jam_tutup'        => 'jam_tutup',
            'hari_operasional' => 'hari_operasional',
            'estimasi_servis'  => 'tipe_lokasi',
            'jenis_layanan'    => 'layanan',
            'latitude'         => 'latitude',
            'longitude'        => 'longitude',
            'rating'           => 'rating',
            'foto_lokasi'      => 'foto_lokasi',
        ];
        $mappedData = [];

        foreach ($fieldMap as $source => $target) {
            if (!array_key_exists($source, $data)) {
                continue;
            }

            $value = $data[$source];

            if ($source === 'brand') {
                $value = $this->normalizeBrand($value);
            }

            if ($source === 'rating' && $value !== null && $value !== '') {
                $value = number_format((float) $value, 1, '.', '');
            }

            $mappedData[$target] = $value;
        }

        if (array_key_exists('tipe_lokasi_key', $data)) {
            $typeKey = trim((string) $data['tipe_lokasi_key']);
            $mappedData['location_type_key'] = $typeKey !== '' ? $typeKey : 'gerai-mandiri';
            $mappedData['tipe_lokasi'] = $this->locationTypeLabel($mappedData['location_type_key']);
        } elseif (array_key_exists('estimasi_servis', $data) && $data['estimasi_servis'] !== null) {
            $mappedData['tipe_lokasi'] = $data['estimasi_servis'];
        }

        return $mappedData;
    }

    public function createCenter(array $data): ?array
    {
        $insertData = $this->mapWriteData($data);

        if ($insertData === []) {
            return null;
        }

        $columns = array_keys($insertData);
        $placeholders = implode(', ', array_fill(0, count($columns), '?'));
        $sql = sprintf(
            'INSERT INTO %s (%s) VALUES (%s) RETURNING id',
            $this->table,
            implode(', ', $columns),
            $placeholders
        );
        $result = $this->db->query($sql, array_values($insertData))->getRowArray();

        if (!$result || !isset($result['id'])) {
            return null;
        }

        return $this->getById($result['id']);
    }

    public function updateCenter(int|string $id, array $data): ?array
    {
        $updateData = $this->mapWriteData($data);

        if ($updateData === []) {
            return $this->getById($id);
        }

        $updated = $this->db->table($this->table)
            ->where('id', $id)
            ->update($updateData);

        if ($updated === false) {
            return null;
        }

        return $this->getById($id);
    }

    public function deleteCenter(int|string $id): bool
    {
        return $this->db->table($this->table)
            ->where('id', $id)
            ->delete();
    }

    public function createWithGeom(array $data, float $lat, float $lng): int|string|false
    {
        $created = $this->createCenter(array_merge($data, [
            'latitude'  => $lat,
            'longitude' => $lng,
        ]));

        return $created['id'] ?? false;
    }

    public function updateWithGeom(int|string $id, array $data, ?float $lat = null, ?float $lng = null): bool
    {
        $updateData = $data;

        if ($lat !== null) {
            $updateData['latitude'] = $lat;
        }

        if ($lng !== null) {
            $updateData['longitude'] = $lng;
        }

        return $this->updateCenter($id, $updateData) !== null;
    }
}
