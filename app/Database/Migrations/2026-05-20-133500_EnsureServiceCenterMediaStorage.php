<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class EnsureServiceCenterMediaStorage extends Migration
{
    public function up()
    {
        $this->db->query('ALTER TABLE service_center_medan ADD COLUMN IF NOT EXISTS rating NUMERIC(2,1)');
        $this->db->query('UPDATE service_center_medan SET rating = 5.0 WHERE rating IS NULL');
        $this->db->query('ALTER TABLE service_center_medan ALTER COLUMN rating TYPE NUMERIC(2,1) USING ROUND(COALESCE(rating, 5.0)::numeric, 1)');
        $this->db->query('ALTER TABLE service_center_medan ALTER COLUMN rating SET DEFAULT 5.0');
        $this->db->query('ALTER TABLE service_center_medan ALTER COLUMN rating SET NOT NULL');

        $this->db->query('ALTER TABLE service_center_medan ADD COLUMN IF NOT EXISTS foto_lokasi TEXT');

        $this->db->query("
            INSERT INTO storage.buckets (id, name, public, file_size_limit, allowed_mime_types)
            VALUES (
                'service-center-photos',
                'service-center-photos',
                true,
                5242880,
                ARRAY['image/jpeg', 'image/png', 'image/webp']
            )
            ON CONFLICT (id) DO UPDATE
            SET
                public = EXCLUDED.public,
                file_size_limit = EXCLUDED.file_size_limit,
                allowed_mime_types = EXCLUDED.allowed_mime_types
        ");
    }

    public function down()
    {
        $this->db->query("DELETE FROM storage.buckets WHERE id = 'service-center-photos'");
    }
}
