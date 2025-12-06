<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\LocationModel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class LocationController extends Controller {

    public function getDistricts() {

        $locationModel = new LocationModel();
        $db = $locationModel->set_connection('17');
        $locationModel->beginTransaction();

        $districts = $locationModel->getDistricts($db);

        $locationModel->commitTransaction();
        

        return response()->json([
            'data' => [
                'status' => 200,
                'msg' => 'Successfully Retrieved Data!',
                'data' => $districts
            ]
        ], 200);
    }

    public function getSubdivs(Request $request) {
        $locationModel = new LocationModel();
        $db = $locationModel->set_connection($request->dist_code);
        $locationModel->beginTransaction();

        $subdivs = $locationModel->getSubdivs($db, $request->dist_code);

        $locationModel->commitTransaction();

        return response()->json([
            'data' => [
                'status' => 200,
                'msg' => 'Successfully Retrieved Data!',
                'data' => $subdivs
            ]
        ], 200);
    }

    public function getCircles(Request $request) {
        $locationModel = new LocationModel();
        $db = $locationModel->set_connection($request->dist_code);
        $locationModel->beginTransaction();

        $circles = $locationModel->getCircles($db, $request->dist_code);

        $locationModel->commitTransaction();

        return response()->json([
            'data' => [
                'status' => 200,
                'msg' => 'Successfully Retrieved Data!',
                'data' => $circles
            ]
        ], 200);
    }

    public function getMouzas(Request $request) {
        $locationModel = new LocationModel();
        $db = $locationModel->set_connection($request->dist_code);
        $locationModel->beginTransaction();

        $mouzas = $locationModel->getMouzas($db, $request->dist_code, $request->subdiv_code, $request->cir_code);

        $locationModel->commitTransaction();

        return response()->json([
            'data' => [
                'status' => 200,
                'msg' => 'Successfully Retrieved Data!',
                'data' => $mouzas
            ]
        ], 200);
    }

    public function getLots(Request $request) {
        $locationModel = new LocationModel();
        $db = $locationModel->set_connection($request->dist_code);
        $locationModel->beginTransaction();

        $lots = $locationModel->getLots($db, $request->dist_code, $request->subdiv_code, $request->cir_code, $request->mouza_pargona_code);

        $locationModel->commitTransaction();

        return response()->json([
            'data' => [
                'status' => 200,
                'msg' => 'Successfully Retrieved Data!',
                'data' => $lots
            ]
        ], 200);
    }

    public function getVills(Request $request) {
        $locationModel = new LocationModel();
        $db = $locationModel->set_connection($request->dist_code);
        $locationModel->beginTransaction();

        $vills = $locationModel->getVills($db, $request->dist_code, $request->subdiv_code, $request->cir_code, $request->mouza_pargona_code, $request->lot_no);

        $locationModel->commitTransaction();

        return response()->json([
            'data' => [
                'status' => 200,
                'msg' => 'Successfully Retrieved Data!',
                'data' => $vills
            ]
        ], 200);
    }



    // public function insertTest() {
    //     $locationModel = new LocationModel();
    //     $db = $locationModel->set_connection();
    //     $locationModel->beginTransaction();

    //     $status = $locationModel->insertTest($db, [
    //         'dist_code' => '00',
    //         'subdiv_code' => '00',
    //         'cir_code' => '00',
    //         'mouza_pargona_code' => '00',
    //         'lot_no' => '00',
    //         'vill_townprt_code' => '00',
    //         'unique_loc_code' => '34534',
    //         'loc_name' => 'test'
    //     ]);

    //     $locationModel->rollbackTransaction();

    //     var_dump($status);
    //     die;


    // }

}