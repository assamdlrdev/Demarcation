<?php

namespace App\Http\Controllers\Api\lm;

use App\Http\Controllers\Controller;
use App\Models\DataModel;
use App\Models\LmModel;
use App\Models\LocationModel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class LmController extends Controller
{
    //

    public function getFinalApplications(Request $request) {
        $decodedToken = jwtdecode($request->bearerToken());

        $dist_code = $decodedToken->dcode;
        $subdiv_code = $decodedToken->subdiv_code;
        $cir_code = $decodedToken->cir_code;
        $mouza_pargona_code = $decodedToken->mouza_pargona_code;
        $lot_no = $decodedToken->lot_no;

        $lmModel = new LmModel();
        $lmModel->connection = $lmModel->dbswitch('demarcation');

        $getFinalApplications = $lmModel->getFinalApplications($dist_code, $subdiv_code, $cir_code, $mouza_pargona_code, $lot_no);

        if($getFinalApplications->isEmpty()) {
            return response()->json([
                'status' => 'n',
                'msg' => 'No Application Found!'
            ], 500);
        }

        $locationModel = new LocationModel();
        $locationModel->connection = $locationModel->dbswitch($dist_code);

        $serial = 0;
        foreach ($getFinalApplications as $finalApplication) {
            $unique_id = $finalApplication->dist_code . '-' . $finalApplication->subdiv_code . '-' . $finalApplication->cir_code . '-' . $finalApplication->mouza_pargona_code . '-' . $finalApplication->lot_no . '-' . $finalApplication->vill_townprt_code . '-' . $finalApplication->application_no;
            $locations = $locationModel->getLocationNames($finalApplication->dist_code, $finalApplication->subdiv_code, $finalApplication->cir_code, $finalApplication->mouza_pargona_code, $finalApplication->lot_no, $finalApplication->vill_townprt_code);
            // $finalApplication->location = $locations;
            $finalApplication->serial_no = ++$serial;
            $finalApplication->village = $locations['dist_name'] . ' district, ' . $locations['subdiv_name'] . ' subdivision, ' . $locations['cir_name'] . ' circle, ' . $locations['mouza_name'] . ' mouza, ' . $locations['lot_name'] . ' lot, ' . $locations['vill_name'] . ' village';
            $finalApplication->status_name = $finalApplication->status == 'Q' ? 'Pending' : '';
            $finalApplication->action = jwtencode(['uid' => $unique_id]);
        }

        
        return response()->json([
            'status' => 'y',
            'msg' => 'Successfully Retrieved Data!',
            'data' => $getFinalApplications
        ], 200);
    }

    public function getSpecifiedApplication(Request $request) {
        $decodedToken = jwtdecode($request->bearerToken());

        $dist_code = $decodedToken->dcode;
        $subdiv_code = $decodedToken->subdiv_code;
        $cir_code = $decodedToken->cir_code;
        $mouza_pargona_code = $decodedToken->mouza_pargona_code;
        $lot_no = $decodedToken->lot_no;

        $app_no = jwtdecode($request->id)->uid;
        $app_no_arr = explode('-', $app_no);

        $vill_townprt_code = $app_no_arr[5];
        $application_no = $app_no_arr[6];

        $dataModel = new DataModel();
        $dataModel->connection = $dataModel->dbswitch($dist_code);
        $isMergedVillage = $dataModel->isMergedVillage($dist_code, $subdiv_code, $cir_code, $mouza_pargona_code, $lot_no,  $vill_townprt_code);

        if(!$isMergedVillage) {
            DB::beginTransaction();
            $mergeTable = $dataModel->mergeTable($dist_code, $subdiv_code, $cir_code, $mouza_pargona_code, $lot_no,  $vill_townprt_code);
            if($mergeTable['status'] != 'y') {
                DB::rollBack();
                return response()->json($mergeTable, 500);
            }
            DB::commit();
        }

        $lmModel = new LmModel();
        $lmModel->connection = $lmModel->dbswitch('demarcation');

        $applicationDetails = $lmModel->getSpecifiedApplication($dist_code, $subdiv_code, $cir_code, $mouza_pargona_code, $lot_no,  $vill_townprt_code, $application_no);

        if($applicationDetails['status'] != 'y') {
            return response()->json($applicationDetails, 500);
        }

        $application = $applicationDetails['data']['application'];
        $dag_details = $applicationDetails['data']['dag_details'];

        $locationModel = new LocationModel();
        $locationModel->connection = $locationModel->dbswitch($dist_code);

        $locationNames = $locationModel->getLocationNames($dist_code, $subdiv_code, $cir_code, $mouza_pargona_code, $lot_no, $vill_townprt_code);

        $location_data = [
            'dist_code' => $application[0]->dist_code,
            'subdiv_code' => $application[0]->subdiv_code,
            'cir_code' => $application[0]->cir_code,
            'mouza_pargona_code' => $application[0]->mouza_pargona_code,
            'lot_no' => $application[0]->lot_no,
            'vill_townprt_code' => $application[0]->vill_townprt_code,
            'dist_name' => $locationNames['dist_name'],
            'subdiv_name' => $locationNames['subdiv_name'],
            'cir_name' => $locationNames['cir_name'],
            'mouza_pargona_name' => $locationNames['mouza_name'],
            'lot_name' => $locationNames['lot_name'],
            'vill_townprt_name' => $locationNames['vill_name'],
        ];

        $application_data = [
            'application_no' => $application[0]->application_no,
            'aadhaar_verified' => $application[0]->aadhaar_verified,
            'status' => ($application[0]->status == "Q") ? "Pending with LRA" : "",
            // 'bhunaksha_available' => ($respData == true) ? 1 : 0
        ];

        $dag_data = [];
        $applicant_data = [];
        $pattadar_data = [];

        $bhunaksha_dag_availability = [];

        if(!empty($dag_details)) {
            foreach ($dag_details as $dag) {
                $dag_no = $dag->dag_no;
                $data = [
                    'locationCode' => $dist_code . $subdiv_code . $cir_code . $mouza_pargona_code . $lot_no . $vill_townprt_code,
                    'plotNo' => $dag_no
                ];
                $response = callLandhubAPIWithHeader('POST', 'NicApi/IsPlotExist', $data);
                if($response["error"] != "") {
                    return response()->json([
                        'status' => 'n',
                        'msg' => $response["error"],
                    ], 500);
                }
                $resp = json_decode($response["data"]);
                $status = $resp->status;
                if($status != "200") {
                    return response()->json([
                        'status' => 'n',
                        'msg' => "Bhunaksha API Error!",
                    ], 500);
                }
                $respData = $resp->data;

                $dag->dist_code = $application[0]->dist_code;
                $dag->subdiv_code = $application[0]->subdiv_code;
                $dag->cir_code = $application[0]->cir_code;
                $dag->mouza_pargona_code = $application[0]->mouza_pargona_code;
                $dag->lot_no = $application[0]->lot_no;
                $dag->vill_townprt_code = $application[0]->vill_townprt_code;
                $dag->bhunaksha_available = ($respData == true) ? 1 : 0;
                
                $bhunaksha_dag_availability[] = $dag->bhunaksha_available;
                
                if(in_array($dist_code, config('constants.BARAK_VALLEY'))) {
                    $dag->dag_area = $dag->dag_area_b . ' B - ' . $dag->dag_area_k . ' K - ' . $dag->dag_area_lc . ' C - ' . $dag->dag_area_g . ' G';
                    $dag->app_dag_area = $dag->app_dag_area_b . ' B - ' . $dag->app_dag_area_k . ' K - ' . $dag->app_dag_area_lc . ' C - ' . $dag->app_dag_area_g . ' G';
                }
                else {
                    $dag->dag_area = $dag->dag_area_b . ' B - ' . $dag->dag_area_k . ' K - ' . $dag->dag_area_lc . ' L';
                    $dag->app_dag_area = $dag->app_dag_area_b . ' B - ' . $dag->app_dag_area_k . ' K - ' . $dag->app_dag_area_lc . ' L';
                }
            }

            $lmModel->connection = $lmModel->dbswitch($dist_code);
            foreach($dag_details as $dag) {
                $dag->patta_type_name = $lmModel->pattaTypeName($dag->patta_type_code);
                $dag_data[] = $dag;
                $applicant_data[] = $dag;
            }
            $pattadar_data = $lmModel->getPattadars($application[0]->dist_code, $application[0]->subdiv_code, $application[0]->cir_code, $application[0]->mouza_pargona_code, $application[0]->lot_no, $application[0]->vill_townprt_code, $dag_details[0]->dag_no);
        }

        if(in_array(0, $bhunaksha_dag_availability)) {
            $application_data['bhunaksha_available'] = 0;
        }
        else {
            $application_data['bhunaksha_available'] = 1;
        }

        $payload = [
            'location_data' => $location_data,
            'application_data' => $application_data,
            'dag_data' => $dag_data,
            'applicant_data' => $applicant_data,
            'pattadar_data' => $pattadar_data
        ];

        return response()->json([
            'status' => 'y',
            'msg' => 'Successfully Retrieved Data!',
            'data' => $payload
        ], 200);
    }

    public function submitLmFirstProceeding(Request $request) {
        $decodedToken = jwtdecode($request->bearerToken());
        $dist_code = $decodedToken->dcode;
        $subdiv_code = $decodedToken->subdiv_code;
        $cir_code = $decodedToken->cir_code;
        $mouza_pargona_code = $decodedToken->mouza_pargona_code;
        $lot_no = $decodedToken->lot_no;
        $user_code = $decodedToken->usercode;
        $user_desig_code = $decodedToken->user_desig_code;

        $application_details = json_decode($request->application_details);

        $applicant_contact = json_decode($request->applicant_contact);
        $applicant_address = json_decode($request->applicant_address);
        $pdar_contact = json_decode($request->pdar_contact);
        $pdar_address = json_decode($request->pdar_address);
        $remarks = $request->remarks;

        $lmModel = new LmModel();
        $lmModel->connection = $lmModel->dbswitch('demarcation');
        $getDetails = $lmModel->getDetails($dist_code, $subdiv_code, $cir_code, $mouza_pargona_code, $lot_no, $application_details[0]->application_no);

        if($getDetails['status'] != 'y') {
            return response()->json($getDetails, 401);
        }

        $dag_details = $getDetails['dag_details'];
        $application_details = $getDetails['application_details'];

        $districtConnection = $lmModel->connection = $lmModel->dbswitch($dist_code);
        DB::connection($districtConnection)->beginTransaction();
        $data = [
            'application_details' => $application_details,
            'dag_details' => $dag_details,
            'applicant_contact' => $applicant_contact,
            'applicant_address' => $applicant_address,
            'pdar_contact' => $pdar_contact,
            'pdar_address' => $pdar_address,
            'user_code' => $user_code,
            'user_desig_code' => $user_desig_code
        ];

        //insert application details
        $insertDetails = $lmModel->insertApplicationDetails($data);
        if($insertDetails['status'] != 'y') {
            DB::connection($districtConnection)->rollBack();
            return response()->json(
                $insertDetails
            , 500);
        }

        //update proceeding log
        $updateProceeding = $lmModel->updateProceeding($data);
        if($updateProceeding['status'] != 'y') {
            DB::connection($districtConnection)->rollBack();
            return response()->json(
                $updateProceeding
            , 500);
        }
        DB::connection($districtConnection)->commit();

        //update demarcation db status
        $demarcationConnection = $lmModel->connection = $lmModel->dbswitch("demarcation");
        DB::connection($demarcationConnection)->beginTransaction();
        $updateDemarcation = $lmModel->updateDemarcation($data);
        if($updateDemarcation['status'] != 'y') {
            DB::connection($demarcationConnection)->rollBack();
            return response()->json(
                $updateDemarcation
            , 500);
        }
        DB::connection($demarcationConnection)->commit();


        return response()->json([
            'status' => 'y',
            'msg' => 'Application Successfuly Generated!'
        ], 200);
        

        // return response()->json([
        //     'status' => 'n',
        //     'application_details' => $application_details,
        //     'applicant_contact' => $applicant_contact,
        //     'applicant_address' => $applicant_address,
        //     'pdar_contact' => $pdar_contact,
        //     'pdar_address' => $pdar_address
        // ], 200);
    }
}
