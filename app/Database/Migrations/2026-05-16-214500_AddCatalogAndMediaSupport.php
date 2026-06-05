<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddCatalogAndMediaSupport extends Migration
{
    public function up()
    {
        $this->db->query('ALTER TABLE service_center_medan ADD COLUMN IF NOT EXISTS foto_lokasi TEXT');
        $this->db->query('ALTER TABLE service_center_medan ADD COLUMN IF NOT EXISTS location_type_key VARCHAR(40)');
        $this->db->query("
            UPDATE service_center_medan
            SET location_type_key = CASE
                WHEN LOWER(COALESCE(tipe_lokasi, '')) LIKE '%mall%' THEN 'mall'
                WHEN LOWER(COALESCE(tipe_lokasi, '')) LIKE '%ruko%' THEN 'ruko'
                ELSE 'gerai-mandiri'
            END
            WHERE location_type_key IS NULL
        ");
        $this->db->query("ALTER TABLE service_center_medan ALTER COLUMN location_type_key SET DEFAULT 'gerai-mandiri'");

        $this->db->query("
            CREATE TABLE IF NOT EXISTS brands (
                id BIGSERIAL PRIMARY KEY,
                name VARCHAR(120) NOT NULL,
                slug VARCHAR(140) NOT NULL UNIQUE,
                description TEXT NULL,
                accent_color VARCHAR(16) NULL,
                is_active BOOLEAN NOT NULL DEFAULT TRUE
            )
        ");

        $this->db->query("
            CREATE TABLE IF NOT EXISTS districts (
                id BIGSERIAL PRIMARY KEY,
                name VARCHAR(140) NOT NULL,
                slug VARCHAR(160) NOT NULL UNIQUE,
                geojson TEXT NULL,
                notes TEXT NULL,
                is_active BOOLEAN NOT NULL DEFAULT TRUE
            )
        ");

        $this->db->query("
            INSERT INTO brands (name, slug, description, accent_color, is_active)
            SELECT DISTINCT brand,
                LOWER(REPLACE(REPLACE(brand, ' ', '-'), '&', 'dan')),
                CONCAT('Brand resmi ', brand, ' pada jaringan service center Medan.'),
                NULL,
                TRUE
            FROM service_center_medan
            WHERE brand IS NOT NULL
              AND brand <> ''
              AND NOT EXISTS (
                SELECT 1 FROM brands b WHERE b.name = service_center_medan.brand
              )
        ");

        $this->db->query("
            INSERT INTO districts (name, slug, geojson, notes, is_active)
            SELECT DISTINCT kecamatan,
                LOWER(REPLACE(REPLACE(kecamatan, ' ', '-'), '.', '')),
                NULL,
                CONCAT('Polygon batas untuk ', kecamatan, ' dapat ditambahkan dari panel admin.'),
                TRUE
            FROM service_center_medan
            WHERE kecamatan IS NOT NULL
              AND kecamatan <> ''
              AND NOT EXISTS (
                SELECT 1 FROM districts d WHERE d.name = service_center_medan.kecamatan
              )
        ");
    }

    public function down()
    {
        $this->db->query('DROP TABLE IF EXISTS districts');
        $this->db->query('DROP TABLE IF EXISTS brands');
        $this->db->query('ALTER TABLE service_center_medan DROP COLUMN IF EXISTS foto_lokasi');
        $this->db->query('ALTER TABLE service_center_medan DROP COLUMN IF EXISTS location_type_key');
    }
}
