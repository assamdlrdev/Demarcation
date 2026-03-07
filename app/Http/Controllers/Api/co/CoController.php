<?php

namespace App\Http\Controllers\Api\co;

use App\Http\Controllers\Controller;
use App\Models\CoModel;
use App\Models\LocationModel;
use Illuminate\Http\Request;

class CoController extends Controller
{
    //
    public function getCoFirstCases(Request $request) {
        $decodedToken = jwtdecode($request->bearerToken());

        $dist_code = $decodedToken->dcode;
        $subdiv_code = $decodedToken->subdiv_code;
        $cir_code = $decodedToken->cir_code;
        $mouza_pargona_code = $decodedToken->mouza_pargona_code;
        $lot_no = $decodedToken->lot_no;
        $user_code = $decodedToken->usercode;
        $user_desig_code = $decodedToken->user_desig_code;

        $coModel = new CoModel();

        $coModel->connection = $coModel->dbswitch($dist_code);

        $applications = $coModel->getApplications($dist_code, $subdiv_code, $cir_code);

        if($applications->isEmpty()) {
            return response()->json([
                'status' => 'n',
                'msg' => 'No Application Found!'
            ], 500);
        }

        $locationModel = new LocationModel();
        $locationModel->connection = $locationModel->dbswitch($dist_code);

        $serial = 0;
        foreach ($applications as $finalApplication) {
            $locations = $locationModel->getLocationNames($finalApplication->dist_code, $finalApplication->subdiv_code, $finalApplication->cir_code, $finalApplication->mouza_pargona_code, $finalApplication->lot_no, $finalApplication->vill_townprt_code);
            // $finalApplication->location = $locations;
            $finalApplication->serial_no = ++$serial;
            $finalApplication->village = $locations['dist_name'] . ' district, ' . $locations['subdiv_name'] . ' subdivision, ' . $locations['cir_name'] . ' circle, ' . $locations['mouza_name'] . ' mouza, ' . $locations['lot_name'] . ' lot, ' . $locations['vill_name'] . ' village';
            $finalApplication->status = 'Active';
            $finalApplication->action = $finalApplication->dist_code . '-' . $finalApplication->subdiv_code . '-' . $finalApplication->cir_code . '-' . $finalApplication->mouza_pargona_code . '-' . $finalApplication->lot_no . '-' . $finalApplication->vill_townprt_code . '-' . $finalApplication->application_no;
        }

        return response()->json([
            'status' => 'y',
            'msg' => 'Successfully retrieved!',
            'data' => $applications
        ], 200);
    }

    public function getCoSpecifiedApplication(Request $request) {
        $decodedToken = jwtdecode($request->bearerToken());

        $dist_code = $decodedToken->dcode;
        $subdiv_code = $decodedToken->subdiv_code;
        $cir_code = $decodedToken->cir_code;
        // $mouza_pargona_code = $decodedToken->mouza_pargona_code;
        // $lot_no = $decodedToken->lot_no;
        $user_code = $decodedToken->usercode;
        $user_desig_code = $decodedToken->user_desig_code;

        $id = $request->id;
        $idArr = explode('-', $id);
        $mouza_pargona_code = $idArr[3];
        $lot_no = $idArr[4];
        $vill_townprt_code = $idArr[5];
        $application_no = $idArr[6];

        $coModel = new CoModel();
        $coModel->connection = $coModel->dbswitch($dist_code);
        $application = $coModel->getApplication($dist_code, $subdiv_code, $cir_code, $mouza_pargona_code, $lot_no, $vill_townprt_code, $application_no);

        foreach ($application as $app) {
            $dag_no = $app->dag_no;
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
            
            $applicants = $coModel->getApplicants($app->id);
            $pattadars = $coModel->getPattadars($app->id);

            foreach ($applicants as $applicant) {
                $applicant->patta_type_name = $coModel->pattaTypeName($applicant->patta_type_code);
            }

            foreach ($pattadars as $pattadar) {
                $pattadar->patta_type_name = $coModel->pattaTypeName($pattadar->patta_type_code);
            }

            $locationModel = new LocationModel();
            $locationModel->connection = $locationModel->dbswitch($dist_code);

            $locationNames = $locationModel->getLocationNames($dist_code, $subdiv_code, $cir_code, $mouza_pargona_code, $lot_no, $vill_townprt_code);

            $app->dist_name = $locationNames['dist_name'];
            $app->subdiv_name = $locationNames['subdiv_name'];
            $app->cir_name = $locationNames['cir_name'];
            $app->mouza_pargona_name = $locationNames['mouza_name'];
            $app->lot_name = $locationNames['lot_name'];
            $app->vill_townprt_name = $locationNames['vill_name'];
            $app->bhunaksha_available = ($respData == true) ? 1 : 0;
            $app->applicants = $applicants;
            $app->pattadars = $pattadars;

        }

        return response()->json([
            'status' => 'y',
            'msg' => 'Successfully retrieved!',
            'data' =>  $application
        ], 200);
    }

    public function getMap(Request $request) {
        $decodedToken = jwtdecode($request->bearerToken());

        $id = $request->id;
        $idArr = explode('-', $id);
        $dist_code = $idArr[0];
        $subdiv_code = $idArr[1];
        $cir_code = $idArr[2];
        $mouza_pargona_code = $idArr[3];
        $lot_no = $idArr[4];
        $vill_townprt_code = $idArr[5];

        $data = [
           'location' => $dist_code . $subdiv_code . $cir_code . $mouza_pargona_code . $lot_no . $vill_townprt_code
        ];
        $map_geojon = callApiMap(config('constants.MAP_API'), $data);
        if(!$map_geojon) {
            return response()->json([
                'status' => 'n',
                'msg' => 'Map could not be retrieved!'
            ], 500);
        }
        $map_geojon_decoded = json_decode($map_geojon);
        if(!isset($map_geojon_decoded->features)) {
            return response()->json([
                'status' => 'n',
                'msg' => 'Map Features could not be retrieved!'
            ], 500);
        }

        return response()->json([
            'status' => 'y',
            'msg' => 'Successfully retrieved Map!',
            'data' => $map_geojon_decoded
        ], 200);
    }
}
