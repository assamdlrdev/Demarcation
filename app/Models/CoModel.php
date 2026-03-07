<?php

namespace App\Models;

use App\Traits\CommonTrait;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class CoModel extends Model
{
    //
    use CommonTrait;

    protected $table = "";
    public $connection;

    public function getApplications($dist_code, $subdiv_code, $cir_code) {
        $applications = DB::connection($this->connection)->table("demarcation_basic")
        ->where('dist_code', $dist_code)
        ->where('subdiv_code', $subdiv_code)
        ->where('cir_code', $cir_code)
        ->where('status', 'A')
        ->get();

        return $applications;
    }

    public function getApplication($dist_code, $subdiv_code, $cir_code, $mouza_pargona_code, $lot_no, $vill_townprt_code, $application_no) {
        $application = DB::connection($this->connection)->table("demarcation_basic as db")
        ->join('demarcation_dag_details as ddd', function($join) {
            $join->on("ddd.demarcation_basic_id", "=", "db.id")
            ->on('db.dist_code', '=', 'ddd.dist_code')
            ->on('db.subdiv_code', '=', 'ddd.subdiv_code')
            ->on('db.cir_code', '=', 'ddd.cir_code')
            ->on('db.mouza_pargona_code', '=', 'ddd.mouza_pargona_code')
            ->on('db.lot_no', '=', 'ddd.lot_no')
            ->on('db.vill_townprt_code', '=', 'ddd.vill_townprt_code');
        })
        ->where('db.dist_code', $dist_code)
        ->where('db.subdiv_code', $subdiv_code)
        ->where('db.cir_code', $cir_code)
        ->where('db.mouza_pargona_code', $mouza_pargona_code)
        ->where('db.lot_no', $lot_no)
        ->where('db.vill_townprt_code', $vill_townprt_code)
        ->where('db.application_no', $application_no)
        ->where('db.status', 'A')
        ->get(['ddd.dag_no', 'db.dist_code', 'db.subdiv_code', 'db.cir_code', 'db.mouza_pargona_code', 'db.lot_no', 'db.vill_townprt_code', 'db.application_no', 'ddd.patta_no', 'ddd.patta_type_code', 'ddd.dag_area_b', 'ddd.dag_area_k', 'ddd.dag_area_lc', 'ddd.dag_area_g', 'ddd.app_dag_area_b', 'ddd.app_dag_area_k', 'ddd.app_dag_area_lc', 'ddd.app_dag_area_g', 'db.id']);

        return $application;
    }

    public function getApplicants($id) {
        $applicants = DB::connection($this->connection)->table("demarcation_applicants")
        ->where('demarcation_basic_id', $id)
        ->get(['dist_code', 'subdiv_code', 'cir_code', 'mouza_pargona_code', 'lot_no', 'vill_townprt_code', 'dag_no', 'patta_no', 'patta_type_code', 'pdar_id', 'pdar_name', 'mobile_no', 'address']);
        return $applicants;
    }

    public function getPattadars($id) {
        $pattadars = DB::connection($this->connection)->table("demarcation_pattadars")
        ->where('demarcation_basic_id', $id)
        ->get(['dist_code', 'subdiv_code', 'cir_code', 'mouza_pargona_code', 'lot_no', 'vill_townprt_code', 'dag_no', 'patta_no', 'patta_type_code', 'pdar_id', 'mobile_no', 'address']);
        return $pattadars;
    }

    public function pattaTypeName($patta_type_code) {
         $pattaCode = DB::connection($this->connection)->table("patta_code")
        ->where('type_code', $patta_type_code)
        ->get();

        if(empty($pattaCode)) {
            return false;
        }
        return $pattaCode[0]->patta_type;
    }
}
