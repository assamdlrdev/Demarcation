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
        ->whereIn('status', ['A', 'B'])
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
        ->whereIn('db.status', ['A', 'B'])
        ->get(['ddd.dag_no', 'db.dist_code', 'db.subdiv_code', 'db.cir_code', 'db.mouza_pargona_code', 'db.lot_no', 'db.vill_townprt_code', 'db.application_no', 'ddd.patta_no', 'ddd.patta_type_code', 'ddd.dag_area_b', 'ddd.dag_area_k', 'ddd.dag_area_lc', 'ddd.dag_area_g', 'ddd.app_dag_area_b', 'ddd.app_dag_area_k', 'ddd.app_dag_area_lc', 'ddd.app_dag_area_g', 'db.id', 'db.status']);

        return $application;
    }

    public function getApplicants($id) {
        $applicants = DB::connection($this->connection)->table("demarcation_applicants")
        ->where('demarcation_basic_id', $id)
        ->get(['dist_code', 'subdiv_code', 'cir_code', 'mouza_pargona_code', 'lot_no', 'vill_townprt_code', 'dag_no', 'patta_no', 'patta_type_code', 'pdar_id', 'pdar_name', 'mobile_no', 'address']);
        return $applicants;
    }

    public function getPattadars($id) {
        $pattadars = DB::connection($this->connection)->table("demarcation_pattadars as dp")
        ->join('chitha_pattadar as cp', function($join) {
            $join->on('dp.dist_code', '=', 'cp.dist_code')
            ->on('dp.subdiv_code', '=', 'cp.subdiv_code')
            ->on('dp.cir_code', '=', 'cp.cir_code')
            ->on('dp.mouza_pargona_code', '=', 'cp.mouza_pargona_code')
            ->on('dp.lot_no', '=', 'cp.lot_no')
            ->on('dp.vill_townprt_code', '=', 'cp.vill_townprt_code')
            ->on('dp.patta_type_code', '=', 'cp.patta_type_code')
            ->on('dp.patta_no', '=', 'cp.patta_no')
            ->on('dp.pdar_id', '=', 'cp.pdar_id');
        })
        ->where('dp.demarcation_basic_id', $id)
        ->get(['dp.dist_code', 'dp.subdiv_code', 'dp.cir_code', 'dp.mouza_pargona_code', 'dp.lot_no', 'dp.vill_townprt_code', 'dp.dag_no', 'dp.patta_no', 'dp.patta_type_code', 'dp.pdar_id', 'dp.mobile_no', 'dp.address', 'cp.pdar_name']);
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

    public function updateApplication($dist_code, $subdiv_code, $cir_code, $application_no, $hearing_date, $user_code, $user_desig_code) {
        $exist = DB::connection($this->connection)->table('demarcation_basic')
        ->where('dist_code', $dist_code)
        ->where('subdiv_code', $subdiv_code)
        ->where('cir_code', $cir_code)
        ->where('application_no', $application_no)
        ->where('status', 'A')
        ->exists();

        if(!$exist) {
            return [
                'status' => 'n',
                'msg' => 'Could not find application no for this circle'
            ];
        }

        $update = DB::connection($this->connection)->table('demarcation_basic')
        ->where('dist_code', $dist_code)
        ->where('subdiv_code', $subdiv_code)
        ->where('cir_code', $cir_code)
        ->where('application_no', $application_no)
        ->update([
            'status' => 'B',
            'co_code' => $user_code,
            'user_desig_code' => $user_desig_code,
            'hearing_date' => $hearing_date,
            'updated_at' => date('Y-m-d H:i:s')
        ]);

        if($update < 1) {
            return [
                'status' => 'n',
                'msg' => 'Could not update application for this circle'
            ];
        }

        $applicationData = DB::connection($this->connection)->table('demarcation_basic')
        ->where('dist_code', $dist_code)
        ->where('subdiv_code', $subdiv_code)
        ->where('cir_code', $cir_code)
        ->where('application_no', $application_no)
        ->get();

        $proceedingId = $this->getProceedingId($dist_code, $subdiv_code, $cir_code, $applicationData[0]->mouza_pargona_code, $applicationData[0]->lot_no, $application_no);
        $insertProceeding = DB::connection($this->connection)->table('demarcation_proceeding')
        ->insert([
            'proceeding_id' => $proceedingId,
            'dist_code' => $dist_code,
            'subdiv_code' => $subdiv_code,
            'cir_code' => $cir_code,
            'mouza_pargona_code' => $applicationData[0]->mouza_pargona_code,
            'lot_no' => $applicationData[0]->lot_no,
            'application_no' => $application_no,
            'user_code' => $user_code,
            'user_desig_code' => $user_desig_code,
            'remarks' => 'Issue Notice by CO',
            'hearing_date' => $hearing_date
        ]);
        if(!$insertProceeding) {
            return [
                'status' => 'n',
                'msg' => 'Could not update proceeding!'
            ];
        }

        return [
            'status' => 'y',
            'msg' => 'Successfully Updated application!'
        ];
    }

    private function getProceedingId($dist_code, $subdiv_code, $cir_code, $mouza_pargona_code, $lot_no, $application_no) {
        $maxId = DB::connection($this->connection)->table("demarcation_proceeding")
        ->where('dist_code', $dist_code)
        ->where('subdiv_code', $subdiv_code)
        ->where('cir_code', $cir_code)
        ->where('mouza_pargona_code', $mouza_pargona_code)
        ->where('lot_no', $lot_no)
        ->where('application_no', $application_no)
        ->max('proceeding_id') ?? 0;
        return $maxId + 1;
    }

    public function demarcationUpdate($dist_code, $subdiv_code, $cir_code, $application_no) {
        $updateStatus = DB::connection($this->connection)->table("citizen_applications")
        ->where('dist_code', $dist_code)
        ->where('subdiv_code', $subdiv_code)
        ->where('cir_code', $cir_code)
        ->where('application_no', $application_no)
        ->update([
            'status' => 'B',
            'updated_at' => date('Y-m-d H:i:s')
        ]);
        if($updateStatus < 1) {
            return [
                'status' => 'n',
                'msg' => 'Demarcation Status Could not be updated!'
            ];
        }
        return [
            'status' => 'y',
            'msg' => 'Demarcation Status updated successfully!'
        ];
    }
}
