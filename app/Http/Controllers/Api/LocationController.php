<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\LocationModel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class LocationController extends Controller
{

    public function getDistricts()
    {
        $districts = config('constants.DEMARCATION_DISTRICTS');

        return response()->json([
            'data' => [
                'status' => 'y',
                'msg' => 'Successfully Retrieved Data!',
                'data' => $districts
            ]
        ], 200);
    }

    public function getSubdivs(Request $request)
    {
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

        $subdivData = json_decode($api_output['data']);
        if ($subdivData->responseType != 2) {
            $response = [
                'status' => 'n',
                'msg' => $subdivData->data
            ];
            return response()->json([
                'data' => $response
            ], 500);
        }
        $response = [
            'status' => 'y',
            'msg' => 'Data successfully retreived!',
            'data' => $subdivData->data
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

    public function getCircles(Request $request)
    {
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

        $circleData = json_decode($api_output['data']);
        if ($circleData->responseType != 2) {
            $response = [
                'status' => 'n',
                'msg' => $circleData->data
            ];
            return response()->json([
                'data' => $response
            ], 500);
        }
        $response = [
            'status' => 'y',
            'msg' => 'Data successfully retreived!',
            'data' => $circleData->data
        ];
        return response()->json([
            'data' => $response
        ], 200);
    }

    public function getMouzas(Request $request)
    {
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

        $mouzaData = json_decode($api_output['data']);
        if ($mouzaData->responseType != 2) {
            $response = [
                'status' => 'n',
                'msg' => $mouzaData->data
            ];
            return response()->json([
                'data' => $response
            ], 500);
        }
        $response = [
            'status' => 'y',
            'msg' => 'Data successfully retreived!',
            'data' => $mouzaData->data
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

    public function getLots(Request $request)
    {
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

        $lotData = json_decode($api_output['data']);
        if ($lotData->responseType != 2) {
            $response = [
                'status' => 'n',
                'msg' => $lotData->data
            ];
            return response()->json([
                'data' => $response
            ], 500);
        }
        $response = [
            'status' => 'y',
            'msg' => 'Data successfully retreived!',
            'data' => $lotData->data
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

    public function getVills(Request $request)
    {
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

        $villData = json_decode($api_output['data']);
        if ($villData->responseType != 2) {
            $response = [
                'status' => 'n',
                'msg' => $villData->data
            ];
            return response()->json([
                'data' => $response
            ], 500);
        }
        $response = [
            'status' => 'y',
            'msg' => 'Data successfully retreived!',
            'data' => $villData->data
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

    public function getPattaTypesLandClasses(Request $request)
    {
        $dist_code = $request->dist_code;
        $data = [];
        $url = config('constants.LANDHUB_BASE_URL') . "NicApi/getLandClassesAndPattaTypes";
        $method = 'POST';
        $data['dist_code'] = $dist_code;
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

        $landClassesAndPattaTypesData = json_decode($api_output['data']);
        if ($landClassesAndPattaTypesData->status != 'y') {
            $response = [
                'status' => 'n',
                'msg' => $landClassesAndPattaTypesData->msg
            ];
            return response()->json([
                'data' => $response
            ], 500);
        }

        $land_classes = $landClassesAndPattaTypesData->data->land_classes;
        $patta_types = $landClassesAndPattaTypesData->data->patta_types;
        $land_groups = $landClassesAndPattaTypesData->data->land_groups;
        $response = [
            'status' => 'y',
            'msg' => 'Data successfully retreived!',
            'data' => [
                'patta_types' => $patta_types,
                'land_classes' => $land_classes,
                'land_groups' => $land_groups
            ]
        ];
        return response()->json([
            'data' => $response
        ], 200);
    }

    public function getPattaNos(Request $request)
    {
        $dist_code = $request->dist_code;
        $subdiv_code = $request->subdiv_code;
        $cir_code = $request->cir_code;
        $mouza_pargona_code = $request->mouza_pargona_code;
        $lot_no = $request->lot_no;
        $vill_townprt_code = $request->vill_townprt_code;
        $patta_type_code = $request->patta_type_code;

        $data = [];
        $url = config('constants.LANDHUB_BASE_URL') . "NicApi/getPattaNos";
        $method = 'POST';
        $data['dist_code'] = $dist_code;
        $data['subdiv_code'] = $subdiv_code;
        $data['cir_code'] = $cir_code;
        $data['mouza_pargona_code'] = $mouza_pargona_code;
        $data['lot_no'] = $lot_no;
        $data['vill_townprt_code'] = $vill_townprt_code;
        $data['patta_type_code'] = $patta_type_code;
        $data['apikey'] = "chithaentry_resurvey";
        $api_output = callApi($url, $method, $data);

        if (!$api_output || $api_output['status'] != 'y') {
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

        $pattaNoData = json_decode($api_output['data']);
        if ($pattaNoData->status != 'y') {
            $response = [
                'status' => 'n',
                'msg' => $pattaNoData->msg,
                'error' => ''
            ];
            return response()->json([
                'data' => $response
            ], 500);
        }
        $pattaNos = $pattaNoData->data;
        if (empty($pattaNos)) {
            $response = [
                'status' => 'n',
                'msg' => 'no data available!'
            ];
            return response()->json([
                'data' => $response
            ], 500);
        }
        $response = [
            'status' => 'y',
            'msg' => 'Successfully retreived data!',
            'data' => $pattaNos
        ];

        return response()->json([
            'data' => $response
        ], 200);
    }

    public function getDags(Request $request)
    {
        $dist_code = $request->dist_code;
        $subdiv_code = $request->subdiv_code;
        $cir_code = $request->cir_code;
        $mouza_pargona_code = $request->mouza_pargona_code;
        $lot_no = $request->lot_no;
        $vill_townprt_code = $request->vill_townprt_code;

        $data = [];
        $url = config('constants.LANDHUB_BASE_URL') . "NicApi/getDags";
        $method = 'POST';
        $data['dist_code'] = $dist_code;
        $data['subdiv_code'] = $subdiv_code;
        $data['cir_code'] = $cir_code;
        $data['mouza_pargona_code'] = $mouza_pargona_code;
        $data['lot_no'] = $lot_no;
        $data['vill_code'] = $vill_townprt_code;
        $data['apikey'] = "chithaentry_resurvey";
        $api_output = callApi($url, $method, $data);

        if (!$api_output || $api_output['status'] != 'y') {
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

        $dagResponse = json_decode($api_output['data']);
        if ($dagResponse->responseType != 2) {
            $response = [
                'status' => 'n',
                'msg' => $dagResponse->data
            ];
            return response()->json([
                'data' => $response
            ], 500);
        }

        if ($dagResponse->data == 'N') {
            $response = [
                'status' => 'n',
                'msg' => 'No dags available!'
            ];
            return response()->json([
                'data' => $response
            ], 500);
        }

        $response = [
            'status' => 'y',
            'msg' => 'Successfully retrieved data!',
            'data' => $dagResponse->data
        ];
        return response()->json([
            'data' => $response
        ], 200);

    }

    public function getPattadarList(Request $request)
    {
        $data['dist_code'] = $request->dist_code;
        $data['subdiv_code'] = $request->subdiv_code;
        $data['cir_code'] = $request->cir_code;
        $data['mouza_pargona_code'] = $request->mouza_pargona_code; //
        $data['lot_no'] = $request->lot_no;
        $data['vill_townprt_code'] = $request->vill_townprt_code;
        $data['patta_no'] = $request->patta_no;
        $data['patta_type_code'] = $request->patta_type_code;

        $url = config('constants.LANDHUB_BASE_URL') . "NicApi/getPattadarForDemarcation";
        $method = 'POST';

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

        $subdivData = json_decode($api_output['data']);

        if ($subdivData->responseType != 2) {
            $response = [
                'status' => 'n',
                'msg' => $subdivData->data
            ];
            return response()->json([
                'data' => $response
            ], 500);
        }

        $response = [
            'status' => 'y',
            'msg' => 'Data successfully retreived!',
            'data' => $subdivData->data
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

    function test($data, $exit=1)  {
        
        $exit && exit('<pre>'.print_r($data,1).'</pre>');
        return '<pre>'.print_r($data,1).'</pre>';
    }

}