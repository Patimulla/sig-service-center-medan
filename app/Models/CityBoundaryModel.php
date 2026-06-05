<?php

namespace App\Models;

use CodeIgniter\Model;

class CityBoundaryModel extends Model
{
    protected $table = 'city_boundaries';
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

    public function getActiveBoundary(?string $slug = 'kota-medan'): ?array
    {
        $builder = $this->where('is_active', true)->orderBy('id', 'ASC');

        if ($slug !== null) {
            $builder->where('slug', $slug);
        }

        return $builder->first();
    }
}
