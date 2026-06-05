<?php

namespace App\Models;

use CodeIgniter\Model;

class DistrictModel extends Model
{
    protected $table = 'districts';
    protected $primaryKey = 'id';
    protected $returnType = 'array';
    protected $allowedFields = [
        'name',
        'slug',
        'geojson',
        'notes',
        'is_active',
    ];
    protected $useTimestamps = false;

    public function getAllForMap(): array
    {
        return $this->where('is_active', true)->orderBy('name', 'ASC')->findAll();
    }

    public function getActiveWithGeoJson(): array
    {
        return $this->where('is_active', true)
            ->where('geojson IS NOT NULL', null, false)
            ->where("geojson <> ''", null, false)
            ->orderBy('name', 'ASC')
            ->findAll();
    }
}
