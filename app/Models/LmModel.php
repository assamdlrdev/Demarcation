<?php

namespace App\Models;

use App\Traits\CommonTrait;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class LmModel extends Model {

    use CommonTrait;

    protected $table = "";
    public $connection;

    public function getFinalApplications($dist_code, $subdiv_code, $cir_code, $mouza_pargona_code, $lot_no) {
        // $applications = DB::connection($this->connection)->table("citizen_applications as ca")
        // ->leftJoin('demarcation_dag_areas as dda', 'dda.citizen_application_id', '=', 'ca.id')
        // ->where('ca.dist_code', $dist_code)
        // ->where('ca.subdiv_code', $subdiv_code)
        // ->where('ca.cir_code', $cir_code)
        // ->where('ca.mouza_pargona_code', $mouza_pargona_code)
        // ->where('ca.lot_no', $lot_no)
        // ->where('ca.dist_code', $dist_code)
        // ->where('ca.final_submit', 1)
        // ->where('ca.status', 'Q')
        // ->get(['ca.application_no', 'ca.dist_code', 'ca.subdiv_code', 'ca.cir_code', 'ca.mouza_pargona_code', 'ca.lot_no', 'ca.vill_townprt_code', 'ca.aadhaar_verified', 'dda.dag_no', 'dda.patta_no', 'dda.patta_type_code', 'dda.pattadar_id', 'dda.pattadar_name', 'dda.dag_area_b', 'dda.dag_area_k', 'dda.dag_area_lc', 'dda.dag_area_g', 'dda.app_dag_area_b', 'dda.app_dag_area_k', 'dda.app_dag_area_lc', 'dda.app_dag_area_g']);

        $applications = DB::connection($this->connection)->table("citizen_applications as ca")
        ->where('ca.dist_code', $dist_code)
        ->where('ca.subdiv_code', $subdiv_code)
        ->where('ca.cir_code', $cir_code)
        ->where('ca.mouza_pargona_code', $mouza_pargona_code)
        ->where('ca.lot_no', $lot_no)
        ->where('ca.final_submit', 1)
        ->where('ca.status', 'Q')
        ->get(['ca.application_no', 'ca.dist_code', 'ca.subdiv_code', 'ca.cir_code', 'ca.mouza_pargona_code', 'ca.lot_no', 'ca.vill_townprt_code', 'ca.aadhaar_verified', 'ca.created_at', 'ca.status']);

        return $applications;
    }

    public function getSpecifiedApplication($dist_code, $subdiv_code, $cir_code, $mouza_pargona_code, $lot_no,  $vill_townprt_code, $application_no) {
        $application = DB::connection($this->connection)->table("citizen_applications as ca")
        ->where('ca.dist_code', $dist_code)
        ->where('ca.subdiv_code', $subdiv_code)
        ->where('ca.cir_code', $cir_code)
        ->where('ca.mouza_pargona_code', $mouza_pargona_code)
        ->where('ca.lot_no', $lot_no)
        ->where('ca.vill_townprt_code', $vill_townprt_code)
        ->where('ca.final_submit', 1)
        ->where('ca.status', 'Q')
        ->where('ca.application_no', $application_no)
        ->get(['ca.id', 'ca.application_no', 'ca.dist_code', 'ca.subdiv_code', 'ca.cir_code', 'ca.mouza_pargona_code', 'ca.lot_no', 'ca.vill_townprt_code', 'ca.aadhaar_verified', 'ca.created_at', 'ca.status']);

        if(empty($application)) {
            return [
                'status' => 'n',
                'msg' => 'No Application Found for this Lot!'
            ];
        }

        $dagDetails = DB::connection($this->connection)->table("demarcation_dag_areas")
        ->where('citizen_application_id', $application[0]->id)->get();

        // foreach ($dagDetails as $dagDetail) {
           
        // }

        $payload = [
            'application' => $application,
            'dag_details' => $dagDetails
        ];

        return [
            'status' => 'y',
            'msg' => 'Successfully retrieved data!',
            'data' => $payload
        ];
    }

    public function getPattadars($dist_code, $subdiv_code, $cir_code, $mouza_pargona_code, $lot_no, $vill_townprt_code, $dag_no) {
        $pattadars = DB::connection($this->connection)->table('chitha_dag_pattadar as cdp')
        ->join('chitha_pattadar as cp', function($join) {
            $join->on('cp.dist_code', '=', 'cdp.dist_code')
            ->on('cp.subdiv_code', '=', 'cdp.subdiv_code')
            ->on('cp.cir_code', '=', 'cdp.cir_code')
            ->on('cp.mouza_pargona_code', '=', 'cdp.mouza_pargona_code')
            ->on('cp.lot_no', '=', 'cdp.lot_no')
            ->on('cp.vill_townprt_code', '=', 'cdp.vill_townprt_code')
            ->on('cp.patta_no', '=', 'cdp.patta_no')
            ->on('cp.patta_type_code', '=', 'cdp.patta_type_code')
            ->on('cp.pdar_id', '=', 'cdp.pdar_id');
        })
        ->where('cdp.dist_code', $dist_code)
        ->where('cdp.subdiv_code', $subdiv_code)
        ->where('cdp.cir_code', $cir_code)
        ->where('cdp.mouza_pargona_code', $mouza_pargona_code)
        ->where('cdp.lot_no', $lot_no)
        ->where('cdp.vill_townprt_code', $vill_townprt_code)
        ->where('cdp.dag_no', $dag_no)
        ->get(['cdp.dist_code', 'cdp.subdiv_code', 'cdp.cir_code', 'cdp.mouza_pargona_code', 'cdp.lot_no', 'cdp.vill_townprt_code', 'cdp.dag_no', 'cdp.patta_type_code', 'cdp.patta_no', 'cdp.pdar_id', 'cp.pdar_name', 'cp.pdar_father']);

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

    public function getDetails($dist_code, $subdiv_code, $cir_code, $mouza_pargona_code, $lot_no, $application_no) {
        $appdetailsexist = DB::connection($this->connection)->table("citizen_applications")
        ->where('dist_code', $dist_code)
        ->where('subdiv_code', $subdiv_code)
        ->where('cir_code', $cir_code)
        ->where('mouza_pargona_code', $mouza_pargona_code)
        ->where('lot_no', $lot_no)
        ->where('application_no', $application_no)
        ->where('final_submit', 1)
        ->where('status', 'Q')
        ->exists();

        if(!$appdetailsexist) {
            return [
                'status' => 'n',
                'msg' => 'Application is not pending with LRA'
            ];
        }

        $appDetails = DB::connection($this->connection)->table("citizen_applications")
        ->where('dist_code', $dist_code)
        ->where('subdiv_code', $subdiv_code)
        ->where('cir_code', $cir_code)
        ->where('mouza_pargona_code', $mouza_pargona_code)
        ->where('lot_no', $lot_no)
        ->where('application_no', $application_no)
        ->where('final_submit', 1)
        ->where('status', 'Q')
        ->get();

        $dagDetails = DB::connection($this->connection)->table("demarcation_dag_areas")
        ->where('citizen_application_id', $appDetails[0]->id)
        ->get();

        return [
            'status' => 'y',
            'application_details' => $appDetails,
            'dag_details' => $dagDetails
        ];
    }

    public function insertApplicationDetails($data) {
        $application_details = $data['application_details'];
        $dag_details = $data['dag_details'];
        $user_code = $data['user_code'];
        $user_desig_code = $data['user_desig_code'];
        $applicant_contact = $data['applicant_contact'];
        $applicant_address = $data['applicant_address'];
        $pdar_contact = $data['pdar_contact'];
        $pdar_address = $data['pdar_address'];

        foreach ($application_details as $appdetail) {
            $demarcationBasicArr = [
                'dist_code' => $appdetail->dist_code,
                'subdiv_code' => $appdetail->subdiv_code,
                'cir_code' => $appdetail->cir_code,
                'mouza_pargona_code' => $appdetail->mouza_pargona_code,
                'lot_no' => $appdetail->lot_no,
                'vill_townprt_code' => $appdetail->vill_townprt_code,
                'application_no' => $appdetail->application_no,
                'aadhaar_verified' => $appdetail->aadhaar_verified,
                'status' => 'A',
                'user_code' => $user_code,
                'user_desig_code' => $user_desig_code,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s')
            ];
            $insertDemarcationBasic = DB::connection($this->connection)->table('demarcation_basic')->insertGetId($demarcationBasicArr);
            if(!$insertDemarcationBasic) {
                return [
                    'status' => 'n',
                    'msg' => 'Error in creating application entry'
                ];
            }
            
            foreach ($dag_details as $dagdetail) {
                $dagDetailsArr = [
                    'demarcation_basic_id' => $insertDemarcationBasic,
                    'dist_code' => $appdetail->dist_code,
                    'subdiv_code' => $appdetail->subdiv_code,
                    'cir_code' => $appdetail->cir_code,
                    'mouza_pargona_code' => $appdetail->mouza_pargona_code,
                    'lot_no' => $appdetail->lot_no,
                    'vill_townprt_code' => $appdetail->vill_townprt_code,
                    'dag_no' => $dagdetail->dag_no,
                    'patta_no' => $dagdetail->patta_no,
                    'patta_type_code' => $dagdetail->patta_type_code,
                    'land_class_code' => $dagdetail->land_class_code,
                    'dag_area_b' => $dagdetail->dag_area_b,
                    'dag_area_k' => $dagdetail->dag_area_k,
                    'dag_area_lc' => $dagdetail->dag_area_lc,
                    'dag_area_g' => $dagdetail->dag_area_g,
                    'app_dag_area_b' => $dagdetail->app_dag_area_b,
                    'app_dag_area_k' => $dagdetail->app_dag_area_k,
                    'app_dag_area_lc' => $dagdetail->app_dag_area_lc,
                    'app_dag_area_g' => $dagdetail->app_dag_area_g,
                    'created_at' => date('Y-m-d H:i:s'),
                    'updated_at' => date('Y-m-d H:i:s')
                ];
                $insertDemarcationDagDetails = DB::connection($this->connection)->table('demarcation_dag_details')->insert($dagDetailsArr);
                if(!$insertDemarcationDagDetails) {
                    return [
                        'status' => 'n',
                        'msg' => 'Error in creating application entry'
                    ];
                }
                $keyid = $appdetail->dist_code . '-' . $appdetail->subdiv_code . '-' . $appdetail->cir_code . '-' . $appdetail->mouza_pargona_code . '-' . $appdetail->lot_no . '-' . $appdetail->vill_townprt_code . '-' . $dagdetail->patta_type_code . '-' . $dagdetail->patta_no . '-' . $dagdetail->pattadar_id;
                $keyMobile = "";
                $keyAddress = "";
                foreach($applicant_contact as $appcontact) {
                    if($keyid == $appcontact->id)  {
                        $keyMobile = $appcontact->value;
                    }
                }

                foreach($applicant_address as $appaddress) {
                    if($keyid == $appaddress->id)  {
                        $keyAddress = $appaddress->value;
                    }
                }
                $demarcationApplicantArr = [
                    'demarcation_basic_id' => $insertDemarcationBasic,
                    'dist_code' => $appdetail->dist_code,
                    'subdiv_code' => $appdetail->subdiv_code,
                    'cir_code' => $appdetail->cir_code,
                    'mouza_pargona_code' => $appdetail->mouza_pargona_code,
                    'lot_no' => $appdetail->lot_no,
                    'vill_townprt_code' => $appdetail->vill_townprt_code,
                    'dag_no' => $dagdetail->dag_no,
                    'patta_no' => $dagdetail->patta_no,
                    'patta_type_code' => $dagdetail->patta_type_code,
                    'pdar_id' => $dagdetail->pattadar_id,
                    'pdar_name' => $dagdetail->pattadar_name,
                    'mobile_no' => $keyMobile,
                    'address' => $keyAddress,
                    'created_at' => date('Y-m-d H:i:s'),
                    'updated_at' => date('Y-m-d H:i:s')
                ];
                $insertApplicant = DB::connection($this->connection)->table("demarcation_applicants")->insert($demarcationApplicantArr);
                if(!$insertApplicant) {
                    return [
                        'status' => 'n',
                        'msg' => 'Error in creating application entry'
                    ];
                }
                //
                $pattadars = DB::connection($this->connection)->table('chitha_dag_pattadar as cdp')
                ->join('chitha_pattadar as cp', function($join) {
                    $join->on('cp.dist_code', '=', 'cdp.dist_code')
                    ->on('cp.subdiv_code', '=', 'cdp.subdiv_code')
                    ->on('cp.cir_code', '=', 'cdp.cir_code')
                    ->on('cp.mouza_pargona_code', '=', 'cdp.mouza_pargona_code')
                    ->on('cp.lot_no', '=', 'cdp.lot_no')
                    ->on('cp.vill_townprt_code', '=', 'cdp.vill_townprt_code')
                    ->on('cp.patta_no', '=', 'cdp.patta_no')
                    ->on('cp.patta_type_code', '=', 'cdp.patta_type_code')
                    ->on('cp.pdar_id', '=', 'cdp.pdar_id');
                })
                ->where('cdp.dist_code', $appdetail->dist_code)
                ->where('cdp.subdiv_code', $appdetail->subdiv_code)
                ->where('cdp.cir_code', $appdetail->cir_code)
                ->where('cdp.mouza_pargona_code', $appdetail->mouza_pargona_code)
                ->where('cdp.lot_no', $appdetail->lot_no)
                ->where('cdp.vill_townprt_code', $appdetail->vill_townprt_code)
                ->where('cdp.dag_no', $dagdetail->dag_no)
                ->get(['cdp.dist_code', 'cdp.subdiv_code', 'cdp.cir_code', 'cdp.mouza_pargona_code', 'cdp.lot_no', 'cdp.vill_townprt_code', 'cdp.dag_no', 'cdp.patta_type_code', 'cdp.patta_no', 'cdp.pdar_id', 'cp.pdar_name', 'cp.pdar_father']);
                foreach($pattadars as $pdar) {
                    $keyid = $pdar->dist_code . '-' . $pdar->subdiv_code . '-' . $pdar->cir_code . '-' . $pdar->mouza_pargona_code . '-' . $pdar->lot_no . '-' . $pdar->vill_townprt_code . '-' . $pdar->patta_type_code . '-' . $pdar->patta_no . '-' . $pdar->pdar_id;
                    
                    $exist = DB::connection($this->connection)->table("demarcation_pattadars")
                    ->where('dist_code', $pdar->dist_code)
                    ->where('subdiv_code', $pdar->subdiv_code)
                    ->where('cir_code', $pdar->cir_code)
                    ->where('mouza_pargona_code', $pdar->mouza_pargona_code)
                    ->where('lot_no', $pdar->lot_no)
                    ->where('vill_townprt_code', $pdar->vill_townprt_code)
                    ->where('dag_no', $pdar->dag_no)
                    ->where('patta_no', $pdar->patta_no)
                    ->where('patta_type_code', $pdar->patta_type_code)
                    ->where('pdar_id', $pdar->pdar_id)
                    ->exists();
                    if(!$exist) {
                        $keyMobile = "";
                        $keyAddress = "";

                        foreach($pdar_contact as $pContact) {
                            if($pContact->id == $keyid) {
                                $keyMobile = $pContact->value;
                            }
                        }
                        foreach($pdar_address as $pAddr) {
                            if($pAddr->id == $keyid) {
                                $keyAddress = $pAddr->value;
                            }
                        }
                        $insertDemarcationPdarArr = [
                            'demarcation_basic_id' => $insertDemarcationBasic,
                            'dist_code' => $pdar->dist_code,
                            'subdiv_code' => $pdar->subdiv_code,
                            'cir_code' => $pdar->cir_code,
                            'mouza_pargona_code' => $pdar->mouza_pargona_code,
                            'lot_no' => $pdar->lot_no,
                            'vill_townprt_code' => $pdar->vill_townprt_code,
                            'dag_no' => $pdar->dag_no,
                            'patta_no' => $pdar->patta_no,
                            'patta_type_code' => $pdar->patta_type_code,
                            'pdar_id' => $pdar->pdar_id,
                            'created_at' => date('Y-m-d H:i:s'),
                            'updated_at' => date('Y-m-d H:i:s'),
                            'mobile_no' => $keyMobile,
                            'address' => $keyAddress
                        ];
                        $insertPdar = DB::connection($this->connection)->table("demarcation_pattadars")->insert($insertDemarcationPdarArr);
                        if(!$insertPdar) {
                            return [
                                'status' => 'n',
                                'msg' => 'Could not enter pattadars!'
                            ];
                        }
                    }
                }
                //
            }
        }
        return [
            'status' => 'y',
            'msg' => 'Successfully Created Application Details!'
        ];
    }

    public function updateProceeding($data) {
        $application_details = $data['application_details'];
        $user_code = $data['user_code'];
        $user_desig_code = $data['user_desig_code'];
        foreach ($application_details as $appdetail) {
            $proceeding_id = $this->getProceedingId($appdetail->dist_code, $appdetail->subdiv_code, $appdetail->cir_code, $appdetail->mouza_pargona_code, $appdetail->lot_no, $appdetail->application_no);
            $insertProceedingArr = [
                'proceeding_id' => $proceeding_id,
                'dist_code' => $appdetail->dist_code,
                'subdiv_code' => $appdetail->subdiv_code,
                'cir_code' => $appdetail->cir_code,
                'mouza_pargona_code' => $appdetail->mouza_pargona_code,
                'lot_no' => $appdetail->lot_no,
                'application_no' => $appdetail->application_no,
                'user_code' => $user_code,
                'user_desig_code' => $user_desig_code,
                'remarks' => 'Forwarded to CO by LRA'
            ];
            $insertStatus = DB::connection($this->connection)->table("demarcation_proceeding")->insert($insertProceedingArr);
            if(!$insertStatus) {
                return [
                    'status' => 'n',
                    'msg' => 'Error in creating proceeding log!'
                ];
            }
        }
        return [
            'status' => 'y',
            'msg' => 'Successfully Updated Proceeding Log!'
        ];
    }

    public function updateDemarcation($data) {
        $application_details = $data['application_details'];
        foreach ($application_details as $appdetail) {
            $updateStatus = DB::connection($this->connection)->table("citizen_applications")
            ->where('dist_code', $appdetail->dist_code)
            ->where('subdiv_code', $appdetail->subdiv_code)
            ->where('cir_code', $appdetail->cir_code)
            ->where('mouza_pargona_code', $appdetail->mouza_pargona_code)
            ->where('lot_no', $appdetail->lot_no)
            ->where('vill_townprt_code', $appdetail->vill_townprt_code)
            ->where('application_no', $appdetail->application_no)
            ->update([
                'status' => 'A'
            ]);

            if($updateStatus < 1) {
                return [
                    'status' => 'n',
                    'msg' => 'Demarcation Status Could not be updated!'
                ];
            }
        }
        return [
            'status' => 'y',
            'msg' => 'Demarcation Status updated successfully!'
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
}