<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use App\Models\CitizenApplication;
use App\Models\DemarcationDagArea;
use App\Models\Attachments;

class CitizenApplications extends Controller
{
    /**
     * Store a newly created citizen application.
     */
    public function store(Request $request){
        $rules = [
            'dist_code'             => 'required|string',
            'subdiv_code'           => 'required|string',
            'cir_code'              => 'required|string',
            'mouza_pargona_code'    => 'required|string',
            'lot_no'                => 'required|string',
            'vill_townprt_code'     => 'required|string',
            'pattadar_id'           => 'required|numeric',
            'dag_no'                => 'required|string',
            'dag_area_b'            => 'required|numeric',
            'dag_area_k'            => 'required|numeric',
            'dag_area_lc'           => 'required|numeric',
            'app_dag_area_b'        => 'required|numeric',
            'app_dag_area_k'        => 'required|numeric',
            'app_dag_area_lc'       => 'required|numeric',
            'patta_type_code'       => 'required|string',
            'patta_no'              => 'required|string',
            'land_class_code'       => 'required|string',
            'land_photo'            => 'required|file|max:5120|mimes:jpg,jpeg,png,pdf'
        ];

        $validator = Validator::make($request->all(), $rules);
        if ($validator->passes()) {
            $duplicate = DemarcationDagArea::join('citizen_applications', 'citizen_applications.id', '=', 'demarcation_dag_areas.citizen_application_id')
                                            ->where('citizen_applications.dist_code', $request->dist_code)
                                            ->where('citizen_applications.subdiv_code', $request->subdiv_code)
                                            ->where('citizen_applications.cir_code', $request->cir_code)
                                            ->where('citizen_applications.mouza_pargona_code', $request->mouza_pargona_code)
                                            ->where('citizen_applications.lot_no', $request->lot_no)
                                            ->where('citizen_applications.vill_townprt_code', $request->vill_townprt_code)
                                            ->where('demarcation_dag_areas.pattadar_id', $request->pattadar_id)
                                            ->where('demarcation_dag_areas.dag_no', $request->dag_no)
                                            ->exists();

            if($duplicate){
                return response()->json([
                    'data' => [
                        'message' => 'Duplicate application found.',
                        'status' => 409
                    ]
                ], 409);
            }

            if($request->app_dag_area_b > $request->dag_area_b){
                return response()->json([
                    'data' => [
                        'message' => 'Applied dag area cannot be greater than existing dag area.1',
                        'status' => 422
                    ]
                ], 422);
            } 
            elseif($request->dag_area_b == $request->app_dag_area_b && $request->app_dag_area_k > $request->dag_area_k){
                return response()->json([
                    'data' => [
                        'message' => 'Applied dag area cannot be greater than existing dag area.2',
                        'status' => 422
                    ]
                ], 422);
            } 
            elseif($request->dag_area_b == $request->app_dag_area_b && $request->dag_area_k == $request->app_dag_area_k && $request->app_dag_area_lc > $request->dag_area_lc){
                return response()->json([
                    'data' => [
                        'message' => 'Applied dag area cannot be greater than existing dag area.3',
                        'status' => 422
                    ]
                ], 422);
            }
            else{
                DB::beginTransaction();
                try{
                    $citizenApplication = new CitizenApplication();

                    $citizenApplication->dist_code              = $request->dist_code;
                    $citizenApplication->subdiv_code            = $request->subdiv_code;
                    $citizenApplication->cir_code               = $request->cir_code;
                    $citizenApplication->mouza_pargona_code     = $request->mouza_pargona_code;
                    $citizenApplication->lot_no                 = $request->lot_no;
                    $citizenApplication->vill_townprt_code      = $request->vill_townprt_code;
                    if($citizenApplication->save()){
                        $demarcationDagArea = new DemarcationDagArea();
                        
                        $demarcationDagArea->citizen_application_id     = $citizenApplication->id;
                        $demarcationDagArea->pattadar_id                = $request->pattadar_id;
                        $demarcationDagArea->dag_no                     = $request->dag_no;
                        $demarcationDagArea->dag_area_b                 = $request->dag_area_b;
                        $demarcationDagArea->dag_area_k                 = $request->dag_area_k;
                        $demarcationDagArea->dag_area_lc                = $request->dag_area_lc;
                        $demarcationDagArea->app_dag_area_b             = $request->app_dag_area_b;
                        $demarcationDagArea->app_dag_area_k             = $request->app_dag_area_k;
                        $demarcationDagArea->app_dag_area_lc            = $request->app_dag_area_lc;
                        $demarcationDagArea->patta_type_code            = $request->patta_type_code;
                        $demarcationDagArea->patta_no                   = $request->patta_no;
                        $demarcationDagArea->land_class_code            = $request->land_class_code;
                        if($demarcationDagArea->save()){
                            $dataForUpload = [
                                'citizen_application_id'    => $citizenApplication->id,
                                'land_photo'                => $request->land_photo,
                                'dist_code'                 => $request->dist_code,
                                'subdiv_code'               => $request->subdiv_code,
                                'cir_code'                  => $request->cir_code,
                                'mouza_pargona_code'        => $request->mouza_pargona_code,
                                'lot_no'                    => $request->lot_no,
                                'vill_townprt_code'         => $request->vill_townprt_code
                            ];
                            $uploadResponse = $this->uploadAttachment($dataForUpload);
                            if($uploadResponse->getStatusCode() != 200){
                                DB::rollback();
                                return $uploadResponse;
                            }

                            DB::commit();
                            return response()->json([
                                'data' => [
                                    'message' => 'Application saved successfully.',
                                    'status' => 200
                                ]
                            ], 200);
                        }

                        DB::rollback();
                        return response()->json([
                            'data' => [
                                'message' => 'Failed to save application.',
                                'status' => 500
                            ]
                        ], 500);
                        
                    } else {
                        DB::rollback();
                        return response()->json([
                            'data' => [
                                'message' => 'Failed to save application.',
                                'status' => 500
                            ]
                        ], 500);
                    }
                } catch (\Exception $e) {
                    DB::rollback();
                    return response()->json([
                        'data' => [
                            'message' => $e->getMessage(),
                            'status' => 500
                        ]
                    ], 500);
                }
            }
        } else{
            return response()->json([
                'data' => [
                    'status'    => 500,
                    'errors'    => $validator->errors()
                ]
            ], 500);
        }
    }

    /**
     * Save file attachments to the attachments table
     */
    private function uploadAttachment($uploadData)
    {
        $citizen_application_id    = $uploadData['citizen_application_id'];
        $land_photo                = $uploadData['land_photo'];
        $dist_code                 = $uploadData['dist_code'];
        $subdiv_code               = $uploadData['subdiv_code'];
        $cir_code                  = $uploadData['cir_code'];
        $mouza_pargona_code        = $uploadData['mouza_pargona_code'];
        $lot_no                    = $uploadData['lot_no'];
        $vill_townprt_code         = $uploadData['vill_townprt_code'];
        $land_photo                = $uploadData['land_photo'];
        $filename = $dist_code.'_'.$subdiv_code.'_'.$cir_code.'_'.$mouza_pargona_code.'_'.$lot_no.'_'.$vill_townprt_code.'_'.$citizen_application_id;

        $extension = $land_photo->getClientOriginalExtension();
        $filename = $dist_code.'_'.$subdiv_code.'_'.$cir_code.'_'.$mouza_pargona_code.'_'.$lot_no.'_'.$vill_townprt_code.'_'.$citizen_application_id.'.'.$extension;

        $path = $land_photo->storeAs(
            'uploads/land_application',
            $filename,
            'public'
        );

        if (!$path) {
            return response()->json([
                'status'  => 500,
                'message' => 'Failed to upload the file.'
            ], 500);
        }

        $demarcationDagArea = new Attachments();
        $demarcationDagArea->citizen_application_id = $citizen_application_id;
        $demarcationDagArea->file_name = $filename;
        $demarcationDagArea->file_type = $extension;
        $demarcationDagArea->file_path = $path;
        if($demarcationDagArea->save()){
            return response()->json([
                'message'  => 'File uploaded successfully.',
                'filename' => $filename,
                'url'      => asset('storage/uploads/land_application/'.$filename),
                'status'   => 200
            ], 200);
        } else{
            return response()->json([
                'status'  => 500,
                'message' => 'Failed to save file information.'
            ], 500);
        }

    }
}
