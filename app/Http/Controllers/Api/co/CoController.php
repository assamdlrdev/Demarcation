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
}
