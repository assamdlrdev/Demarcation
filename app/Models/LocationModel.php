<?php

namespace App\Models;

use App\Traits\CommonTrait;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class LocationModel extends Model {

    use CommonTrait;

    protected $table = "location";
    public $connection;



    
    

    

    // public function setConnect($dist_code) {
    //     $this->district = $dist_code;
    //     $connection = $this->switchConnection();
    //     $this->connect = $this->setConnection($connection);
    // }

    // public function getDistricts($db) {
    //     return $db->where('dist_code', '!=', '00')
    //     ->where('subdiv_code', '00')
    //     ->get();
    // }

    // public function getSubdivs($db, $dist_code) {
    //     return $db->where('dist_code', $dist_code)
    //     ->where('subdiv_code', '!=', '00')
    //     ->where('cir_code', '00')
    //     ->get();
    // }

    // public function getCircles($db, $dist_code, $subdiv_code=null) {
    //     $circles = $db->where('dist_code', $dist_code);
    //     if($subdiv_code) {
    //         $circles->where('subdiv_code', $subdiv_code);
    //     }
    //     else {
    //         $circles->where('subdiv_code', '!=', '00');
    //     }
    //     return $circles->where('cir_code', '!=', '00')->where('mouza_pargona_code', '00')->get();
    // }

    // public function getMouzas($db, $dist_code, $subdiv_code, $cir_code) {
    //     $mouzas = $db->where('dist_code', $dist_code)
    //     ->where('subdiv_code', $subdiv_code)
    //     ->where('cir_code', $cir_code)
    //     ->where('mouza_pargona_code', '!=', '00')
    //     ->where('lot_no', '00')
    //     ->get();

    //     return $mouzas;

    // }

    // public function getLots($db, $dist_code, $subdiv_code, $cir_code, $mouza_pargona_code) {
    //     $lots = $db->where('dist_code', $dist_code)
    //     ->where('subdiv_code', $subdiv_code)
    //     ->where('cir_code', $cir_code)
    //     ->where('mouza_pargona_code', $mouza_pargona_code)
    //     ->where('lot_no', '!=', '00')
    //     ->where('vill_townprt_code', '00000')
    //     ->get();

    //     return $lots;

    // }

    // public function getVills($db, $dist_code, $subdiv_code, $cir_code, $mouza_pargona_code, $lot_no) {
    //     $vills = $db->where('dist_code', $dist_code)
    //     ->where('subdiv_code', $subdiv_code)
    //     ->where('cir_code', $cir_code)
    //     ->where('mouza_pargona_code', $mouza_pargona_code)
    //     ->where('lot_no', $lot_no)
    //     ->where('vill_townprt_code', '!=', '00000')
    //     ->get();

    //     return $vills;

    // }

    // public function getDistName($db, $dist_code) {
    //     $dist = $db->where('dist_code', $dist_code)
    //     ->where('subdiv_code', '00')
    //     ->where('cir_code', '00')
    //     ->where('mouza_pargona_code', '00')
    //     ->where('lot_no', '00')
    //     ->where('vill_townprt_code', '00000')
    //     ->get();

    //     if(!empty($dist)) {
    //         return $dist;
    //     }
    //     else {
    //         return [];
    //     }
    // }

    // public function getSubdivName($db, $dist_code, $subdiv_code) {
    //     $subdiv = $db->where('dist_code', $dist_code)
    //     ->where('subdiv_code', $subdiv_code)
    //     ->where('cir_code', '00')
    //     ->where('mouza_pargona_code', '00')
    //     ->where('lot_no', '00')
    //     ->where('vill_townprt_code', '00000')
    //     ->get();

    //     if(!empty($subdiv)) {
    //         return $subdiv;
    //     }
    //     else {
    //         return [];
    //     }
    // }

    // public function getCirName($db, $dist_code, $subdiv_code, $cir_code) {
    //     $loc = $db->where('dist_code', $dist_code)
    //     ->where('subdiv_code', $subdiv_code)
    //     ->where('cir_code', $cir_code)
    //     ->where('mouza_pargona_code', '00')
    //     ->where('lot_no', '00')
    //     ->where('vill_townprt_code', '00000')
    //     ->get();

    //     if(!empty($loc)) {
    //         return $loc;
    //     }
    //     else {
    //         return [];
    //     }
    // }

    // public function getMouzaName($db, $dist_code, $subdiv_code, $cir_code, $mouza_pargona_code) {
    //     $loc = $db->where('dist_code', $dist_code)
    //     ->where('subdiv_code', $subdiv_code)
    //     ->where('cir_code', $cir_code)
    //     ->where('mouza_pargona_code', $mouza_pargona_code)
    //     ->where('lot_no', '00')
    //     ->where('vill_townprt_code', '00000')
    //     ->get();

    //     if(!empty($loc)) {
    //         return $loc;
    //     }
    //     else {
    //         return [];
    //     }
    // }

    // public function getLotName($db, $dist_code, $subdiv_code, $cir_code, $mouza_pargona_code, $lot_no) {
    //     $loc = $db->where('dist_code', $dist_code)
    //     ->where('subdiv_code', $subdiv_code)
    //     ->where('cir_code', $cir_code)
    //     ->where('mouza_pargona_code', $mouza_pargona_code)
    //     ->where('lot_no', $lot_no)
    //     ->where('vill_townprt_code', '00000')
    //     ->get();

    //     if(!empty($loc)) {
    //         return $loc;
    //     }
    //     else {
    //         return [];
    //     }
    // }

    // public function getVillName($db, $dist_code, $subdiv_code, $cir_code, $mouza_pargona_code, $lot_no, $vill_townprt_code) {
    //     $loc = $db->where('dist_code', $dist_code)
    //     ->where('subdiv_code', $subdiv_code)
    //     ->where('cir_code', $cir_code)
    //     ->where('mouza_pargona_code', $mouza_pargona_code)
    //     ->where('lot_no', $lot_no)
    //     ->where('vill_townprt_code', $vill_townprt_code)
    //     ->get();

    //     if(!empty($loc)) {
    //         return $loc;
    //     }
    //     else {
    //         return [];
    //     }
    // }

    public function getLocationNames($dist_code, $subdiv_code, $cir_code, $mouza_pargona_code, $lot_no, $vill_townprt_code) {
        $dist = DB::connection($this->connection)->table($this->table)->where('dist_code', $dist_code)
        ->where('subdiv_code', '00')
        ->where('cir_code', '00')
        ->where('mouza_pargona_code', '00')
        ->where('lot_no', '00')
        ->where('vill_townprt_code', '00000')
        ->get();

        $subdiv = DB::connection($this->connection)->table($this->table)->where('dist_code', $dist_code)
        ->where('subdiv_code', $subdiv_code)
        ->where('cir_code', '00')
        ->where('mouza_pargona_code', '00')
        ->where('lot_no', '00')
        ->where('vill_townprt_code', '00000')
        ->get();

        $cir = DB::connection($this->connection)->table($this->table)->where('dist_code', $dist_code)
        ->where('subdiv_code', $subdiv_code)
        ->where('cir_code', $cir_code)
        ->where('mouza_pargona_code', '00')
        ->where('lot_no', '00')
        ->where('vill_townprt_code', '00000')
        ->get();

        $mouza = DB::connection($this->connection)->table($this->table)->where('dist_code', $dist_code)
        ->where('subdiv_code', $subdiv_code)
        ->where('cir_code', $cir_code)
        ->where('mouza_pargona_code', $mouza_pargona_code)
        ->where('lot_no', '00')
        ->where('vill_townprt_code', '00000')
        ->get();

        $lot =DB::connection($this->connection)->table($this->table)->where('dist_code', $dist_code)
        ->where('subdiv_code', $subdiv_code)
        ->where('cir_code', $cir_code)
        ->where('mouza_pargona_code', $mouza_pargona_code)
        ->where('lot_no', $lot_no)
        ->where('vill_townprt_code', '00000')
        ->get();

        $vill = DB::connection($this->connection)->table($this->table)->where('dist_code', $dist_code)
        ->where('subdiv_code', $subdiv_code)
        ->where('cir_code', $cir_code)
        ->where('mouza_pargona_code', $mouza_pargona_code)
        ->where('lot_no', $lot_no)
        ->where('vill_townprt_code', $vill_townprt_code)
        ->get();

        return [
            'dist_name' => $dist[0]->loc_name ?? '',
            'subdiv_name' => $subdiv[0]->loc_name ?? '',
            'cir_name' => $cir[0]->loc_name ?? '',
            'mouza_name' => $mouza[0]->loc_name ?? '',
            'lot_name' => $lot[0]->loc_name ?? '',
            'vill_name' => $vill[0]->loc_name ?? ''
        ];
    }

    // public function insertTest($db, $arr) {
    //     $insertStatus = $db->insert($arr);

    //     return $insertStatus;


    // }
}