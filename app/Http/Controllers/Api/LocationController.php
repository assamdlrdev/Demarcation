<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\LocationModel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class LocationController extends Controller {

    public function getDistricts() {
        $districts = config('constants.DEMARCATION_DISTRICTS');

        return response()->json([
            'data' => [
                'status' => 200,
                'msg' => 'Successfully Retrieved Data!',
                'data' => $districts
            ]
        ], 200);
    }

    public function getSubdivs(Request $request) {
        $dist_code = $request->dist_code;
        $data = [];
        $url = config('constants.LANDHUB_BASE_URL') . "NicApi/getSubdivs";
        $method = 'POST';
        $data['dist_code'] = $dist_code;
        $data['apikey'] = "chithaentry_resurvey";

        $api_output = callApi($url, $method, $data);
        // var_dump($api_output);

        if ($api_output['status'] != 'y') {
            // log_message("error", 'LAND HUB API FAIL LMController');
            $response = [
                'status' => 'n',
                'msg' => 'API Failed!',
                'error' => $api_output['error_code'],
                'data' => $api_output['data']
            ];
            return response()->json([
                'data' => $response
            ], 500);
        }

        $subdivs = json_decode($api_output['data']);
        $response = [
            'status' => 'y',
            'msg' => 'Data successfully retreived!',
            'data' => $subdivs
        ];
        return response()->json([
            'data' => $response
        ], 200);

        // $locationModel = new LocationModel();
        // $db = $locationModel->set_connection($request->dist_code);
        // $locationModel->beginTransaction();

        // $subdivs = $locationModel->getSubdivs($db, $request->dist_code);

        // $locationModel->commitTransaction();

        // return response()->json([
        //     'data' => [
        //         'status' => 200,
        //         'msg' => 'Successfully Retrieved Data!',
        //         'data' => $subdivs
        //     ]
        // ], 200);
    }

    public function getCircles(Request $request) {
        $dist_code = $request->dist_code;
        $subdiv_code = $request->subdiv_code;
        $data = [];
        $url = config('constants.LANDHUB_BASE_URL') . "NicApi/getCircles";
        $method = 'POST';
        $data['dist_code'] = $dist_code;
        $data['subdiv_code'] = $subdiv_code;
        $data['apikey'] = "chithaentry_resurvey";
        $api_output = callApi($url, $method, $data);

        if ($api_output['status'] != 'y') {
            // log_message("error", 'LAND HUB API FAIL LMController');
            $response = [
                'status' => 'n',
                'msg' => 'API Failed!',
                'error' => $api_output['error_code'],
                'data' => $api_output['data']
            ];
            return response()->json([
                'data' => $response
            ], 500);
        }

        $circles = json_decode($api_output['data']);
        $response = [
            'status' => 'y',
            'msg' => 'Data successfully retreived!',
            'data' => $circles
        ];
        return response()->json([
            'data' => $response
        ], 200);

        // $locationModel = new LocationModel();
        // $db = $locationModel->set_connection($request->dist_code);
        // $locationModel->beginTransaction();

        // $circles = $locationModel->getCircles($db, $request->dist_code);

        // $locationModel->commitTransaction();

        // return response()->json([
        //     'data' => [
        //         'status' => 200,
        //         'msg' => 'Successfully Retrieved Data!',
        //         'data' => $circles
        //     ]
        // ], 200);
    }

    public function getMouzas(Request $request) {
        $dist_code = $request->dist_code;
        $subdiv_code = $request->subdiv_code;
        $cir_code = $request->cir_code;
        $data = [];
        $url = config('constants.LANDHUB_BASE_URL') . "NicApi/getMouzas";
        $method = 'POST';
        $data['dist_code'] = $dist_code;
        $data['subdiv_code'] = $subdiv_code;
        $data['cir_code'] = $cir_code;
        $data['apikey'] = "chithaentry_resurvey";
        $api_output = callApi($url, $method, $data);

        if ($api_output['status'] != 'y') {
            // log_message("error", 'LAND HUB API FAIL LMController');
            $response = [
                'status' => 'n',
                'msg' => 'API Failed!',
                'error' => $api_output['error_code'],
                'data' => $api_output['data']
            ];
            return response()->json([
                'data' => $response
            ], 500);
        }

        $mouzas = json_decode($api_output['data']);
        $response = [
            'status' => 'y',
            'msg' => 'Data successfully retreived!',
            'data' => $mouzas
        ];
        return response()->json([
            'data' => $response
        ], 200);



        // $locationModel = new LocationModel();
        // $db = $locationModel->set_connection($request->dist_code);
        // $locationModel->beginTransaction();

        // $mouzas = $locationModel->getMouzas($db, $request->dist_code, $request->subdiv_code, $request->cir_code);

        // $locationModel->commitTransaction();

        // return response()->json([
        //     'data' => [
        //         'status' => 200,
        //         'msg' => 'Successfully Retrieved Data!',
        //         'data' => $mouzas
        //     ]
        // ], 200);
    }

    public function getLots(Request $request) {
        $dist_code = $request->dist_code;
        $subdiv_code = $request->subdiv_code;
        $cir_code = $request->cir_code;
        $mouza_pargona_code = $request->mouza_pargona_code;
        $data = [];
        $url = config('constants.LANDHUB_BASE_URL') . "NicApi/getLots";
        $method = 'POST';
        $data['dist_code'] = $dist_code;
        $data['subdiv_code'] = $subdiv_code;
        $data['cir_code'] = $cir_code;
        $data['mouza_pargona_code'] = $mouza_pargona_code;
        $data['apikey'] = "chithaentry_resurvey";
        $api_output = callApi($url, $method, $data);

        if ($api_output['status'] != 'y') {
            // log_message("error", 'LAND HUB API FAIL LMController');
            $response = [
                'status' => 'n',
                'msg' => 'API Failed!',
                'error' => $api_output['error_code'],
                'data' => $api_output['data']
            ];
            return response()->json([
                'data' => $response
            ], 500);
        }

        $lots = json_decode($api_output['data']);
        $response = [
            'status' => 'y',
            'msg' => 'Data successfully retreived!',
            'data' => $lots
        ];
        return response()->json([
            'data' => $response
        ], 200);




        // $locationModel = new LocationModel();
        // $db = $locationModel->set_connection($request->dist_code);
        // $locationModel->beginTransaction();

        // $lots = $locationModel->getLots($db, $request->dist_code, $request->subdiv_code, $request->cir_code, $request->mouza_pargona_code);

        // $locationModel->commitTransaction();

        // return response()->json([
        //     'data' => [
        //         'status' => 200,
        //         'msg' => 'Successfully Retrieved Data!',
        //         'data' => $lots
        //     ]
        // ], 200);
    }

    public function getVills(Request $request) {
        $dist_code = $request->dist_code;
        $subdiv_code = $request->subdiv_code;
        $cir_code = $request->cir_code;
        $mouza_pargona_code = $request->mouza_pargona_code;
        $lot_no = $request->lot_no;
        $data = [];
        $url = config('constants.LANDHUB_BASE_URL') . "NicApi/getVillages";
        $method = 'POST';
        $data['dist_code'] = $dist_code;
        $data['subdiv_code'] = $subdiv_code;
        $data['cir_code'] = $cir_code;
        $data['mouza_pargona_code'] = $mouza_pargona_code;
        $data['lot_no'] = $lot_no;
        $data['apikey'] = "chithaentry_resurvey";
        $api_output = callApi($url, $method, $data);

        if ($api_output['status'] != 'y') {
            // log_message("error", 'LAND HUB API FAIL LMController');
            $response = [
                'status' => 'n',
                'msg' => 'API Failed!',
                'error' => $api_output['error_code'],
                'data' => $api_output['data']
            ];
            return response()->json([
                'data' => $response
            ], 500);
        }

        $vills = json_decode($api_output['data']);
        $response = [
            'status' => 'y',
            'msg' => 'Data successfully retreived!',
            'data' => $vills
        ];
        return response()->json([
            'data' => $response
        ], 200);



        // $locationModel = new LocationModel();
        // $db = $locationModel->set_connection($request->dist_code);
        // $locationModel->beginTransaction();

        // $vills = $locationModel->getVills($db, $request->dist_code, $request->subdiv_code, $request->cir_code, $request->mouza_pargona_code, $request->lot_no);

        // $locationModel->commitTransaction();

        // return response()->json([
        //     'data' => [
        //         'status' => 200,
        //         'msg' => 'Successfully Retrieved Data!',
        //         'data' => $vills
        //     ]
        // ], 200);
    }

}