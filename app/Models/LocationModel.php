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

    public function getCircles($db, $dist_code, $subdiv_code=null) {
        $circles = $db->where('dist_code', $dist_code);
        if($subdiv_code) {
            $circles->where('subdiv_code', $subdiv_code);
        }
        else {
            $circles->where('subdiv_code', '!=', '00');
        }
        return $circles->where('cir_code', '!=', '00')->where('mouza_pargona_code', '00')->get();
    }

    // public function insertTest($db, $arr) {
    //     $insertStatus = $db->insert($arr);

    //     return $insertStatus;


    // }
}