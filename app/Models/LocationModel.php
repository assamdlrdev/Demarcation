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

    public function getSubdivs($db, $dist_code) {
        return $db->where('dist_code', $dist_code)
        ->where('subdiv_code', '!=', '00')
        ->where('cir_code', '00')
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

    public function getMouzas($db, $dist_code, $subdiv_code, $cir_code) {
        $mouzas = $db->where('dist_code', $dist_code)
        ->where('subdiv_code', $subdiv_code)
        ->where('cir_code', $cir_code)
        ->where('mouza_pargona_code', '!=', '00')
        ->where('lot_no', '00')
        ->get();

        return $mouzas;

    }

    public function getLots($db, $dist_code, $subdiv_code, $cir_code, $mouza_pargona_code) {
        $lots = $db->where('dist_code', $dist_code)
        ->where('subdiv_code', $subdiv_code)
        ->where('cir_code', $cir_code)
        ->where('mouza_pargona_code', $mouza_pargona_code)
        ->where('lot_no', '!=', '00')
        ->where('vill_townprt_code', '00000')
        ->get();

        return $lots;

    }

    public function getVills($db, $dist_code, $subdiv_code, $cir_code, $mouza_pargona_code, $lot_no) {
        $vills = $db->where('dist_code', $dist_code)
        ->where('subdiv_code', $subdiv_code)
        ->where('cir_code', $cir_code)
        ->where('mouza_pargona_code', $mouza_pargona_code)
        ->where('lot_no', $lot_no)
        ->where('vill_townprt_code', '!=', '00000')
        ->get();

        return $vills;

    }

    // public function insertTest($db, $arr) {
    //     $insertStatus = $db->insert($arr);

    //     return $insertStatus;


    // }
}