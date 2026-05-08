<?php

namespace App\Models;

use CodeIgniter\Model;

class VehicleTypeModel extends Model
{
    protected $table            = 'vehicle_types';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'object';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = ['type_name', 'description', 'status'];

    // Dates
    protected $useTimestamps = true;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';

    public function getActiveTypes()
    {
        return $this->where('status', 'Active')->findAll();
    }
}
