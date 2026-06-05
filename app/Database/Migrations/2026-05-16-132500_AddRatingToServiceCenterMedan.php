<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddRatingToServiceCenterMedan extends Migration
{
    public function up()
    {
        $this->db->query('ALTER TABLE service_center_medan ADD COLUMN IF NOT EXISTS rating NUMERIC(2,1)');
        $this->db->query('UPDATE service_center_medan SET rating = 5.0 WHERE rating IS NULL');
        $this->db->query('ALTER TABLE service_center_medan ALTER COLUMN rating TYPE NUMERIC(2,1) USING ROUND(COALESCE(rating, 5.0)::numeric, 1)');
        $this->db->query('ALTER TABLE service_center_medan ALTER COLUMN rating SET DEFAULT 5.0');
        $this->db->query('ALTER TABLE service_center_medan ALTER COLUMN rating SET NOT NULL');
    }

    public function down()
    {
        $this->db->query('ALTER TABLE service_center_medan DROP COLUMN IF EXISTS rating');
    }
}
