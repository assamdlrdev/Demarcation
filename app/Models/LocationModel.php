<?php

namespace App\Models;

use App\Traits\CommonTrait;
use Illuminate\Database\Eloquent\Model;

class LocationModel extends Model {

    use CommonTrait;

    protected $table = "location";

    public function getDistricts($db) {
        return $db->where('dist_code', '!=', '00')
        ->where('subdiv_code', '00')
        ->get();
    }
}