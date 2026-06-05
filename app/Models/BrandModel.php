<?php

namespace App\Models;

use CodeIgniter\Model;

class BrandModel extends Model
{
    protected $table = 'brands';
    protected $primaryKey = 'id';
    protected $returnType = 'array';
    protected $allowedFields = [
        'name',
        'slug',
        'description',
        'accent_color',
        'is_active',
    ];
    protected $useTimestamps = false;

    public function getActive(): array
    {
        return $this->orderBy('name', 'ASC')->findAll();
    }
}
