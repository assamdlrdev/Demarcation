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
        

        return response()->json([
            'data' => [
                'status' => 200,
                'msg' => 'Successfully Retrieved Data!',
                'data' => $districts
            ]
        ], 200);
    }
}