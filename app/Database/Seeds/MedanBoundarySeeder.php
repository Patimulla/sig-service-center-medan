<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;
use RuntimeException;

class MedanBoundarySeeder extends Seeder
{
    public function run()
    {
        $districtPath = WRITEPATH . 'geojson/polygon-kecamatan-medan.geojson';
        $cityPath = WRITEPATH . 'geojson/polygon-kota-medan.geojson';

        $districtCollection = $this->decodeGeoJsonFile($districtPath);
        $cityCollection = $this->decodeGeoJsonFile($cityPath);

        $districtRows = [];
        foreach ((array) ($districtCollection['features'] ?? []) as $feature) {
            if (!is_array($feature)) {
                continue;
            }

            $name = trim((string) ($feature['properties']['name'] ?? ''));
            if ($name === '') {
                continue;
            }

            $districtRows[] = [
                'name' => $name,
                'slug' => $this->slugify($name),
                'geojson' => json_encode($feature, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
                'notes' => 'Polygon batas kecamatan Kota Medan.',
                'is_active' => true,
            ];
        }

        usort($districtRows, static fn (array $left, array $right) => strcmp($left['name'], $right['name']));

        if ($districtRows === []) {
            throw new RuntimeException('Seeder batas wilayah gagal karena data kecamatan Medan tidak ditemukan.');
        }

        $cityFeatureCollection = [
            'type' => 'FeatureCollection',
            'features' => array_values(array_filter(
                (array) ($cityCollection['features'] ?? []),
                static fn ($feature) => is_array($feature) && trim((string) ($feature['properties']['name'] ?? '')) === 'Kota Medan'
            )),
        ];

        if (($cityFeatureCollection['features'] ?? []) === []) {
            throw new RuntimeException('Seeder batas wilayah gagal karena polygon Kota Medan tidak ditemukan.');
        }

        $this->db->transStart();

        $this->db->table('districts')->truncate();
        $this->db->table('districts')->insertBatch($districtRows);

        $this->db->table('city_boundaries')->truncate();
        $this->db->table('city_boundaries')->insert([
            'name' => 'Kota Medan',
            'slug' => 'kota-medan',
            'geojson' => json_encode($cityFeatureCollection, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
            'notes' => 'Polygon batas administratif Kota Medan untuk cakupan sistem.',
            'is_active' => true,
        ]);

        $this->db->transComplete();

        if (!$this->db->transStatus()) {
            throw new RuntimeException('Seeder batas wilayah Medan gagal disimpan ke database.');
        }
    }

    protected function decodeGeoJsonFile(string $path): array
    {
        if (!is_file($path)) {
            throw new RuntimeException("File GeoJSON tidak ditemukan: {$path}");
        }

        $contents = file_get_contents($path);
        if ($contents === false) {
            throw new RuntimeException("File GeoJSON tidak dapat dibaca: {$path}");
        }

        $decoded = json_decode($contents, true);
        if (!is_array($decoded)) {
            throw new RuntimeException("Isi GeoJSON tidak valid: {$path}");
        }

        return $decoded;
    }

    protected function slugify(string $value): string
    {
        $slug = strtolower(trim($value));
        $slug = preg_replace('/[^a-z0-9]+/i', '-', $slug) ?? $slug;
        return trim($slug, '-');
    }
}
