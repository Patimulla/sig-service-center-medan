<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddCityBoundariesTable extends Migration
{
    public function up()
    {
        $this->db->query("
            CREATE TABLE IF NOT EXISTS city_boundaries (
                id BIGSERIAL PRIMARY KEY,
                name VARCHAR(160) NOT NULL,
                slug VARCHAR(180) NOT NULL UNIQUE,
                geojson TEXT NOT NULL,
                notes TEXT NULL,
                is_active BOOLEAN NOT NULL DEFAULT TRUE
            )
        ");
    }

    public function down()
    {
        $this->db->query('DROP TABLE IF EXISTS city_boundaries');
    }
}
