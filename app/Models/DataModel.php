<?php

namespace App\Models;

use App\Traits\CommonTrait;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class DataModel extends Model
{
    use CommonTrait;

    public $connection;

    public function isMergedVillage($dist_code, $subdiv_code, $cir_code, $mouza_pargona_code, $lot_no,  $vill_townprt_code) {
        $mergeExist = DB::connection($this->connection)->table('demarcation_villages')
        ->where('dist_code', $dist_code)
        ->where('subdiv_code', $subdiv_code)
        ->where('cir_code', $cir_code)
        ->where('mouza_pargona_code', $mouza_pargona_code)
        ->where('lot_no', $lot_no)
        ->where('vill_townprt_code', $vill_townprt_code)
        ->exists();

        if($mergeExist) {
            $mergeEntry = DB::connection($this->connection)->table('demarcation_villages')
            ->where('dist_code', $dist_code)
            ->where('subdiv_code', $subdiv_code)
            ->where('cir_code', $cir_code)
            ->where('mouza_pargona_code', $mouza_pargona_code)
            ->where('lot_no', $lot_no)
            ->where('vill_townprt_code', $vill_townprt_code)
            ->get();
            $is_merged = $mergeEntry[0]->is_merged;
            if($is_merged == 1) {
                return true;
            }
        }
        return false;
    }

    public function mergeTable($dist_code, $subdiv_code, $cir_code, $mouza_pargona_code, $lot_no,  $vill_townprt_code) 
    {   
        $locationExist = DB::connection($this->connection)->table('location')
        ->where('dist_code', $dist_code)
        ->where('subdiv_code', $subdiv_code)
        ->where('cir_code', $cir_code)
        ->where('mouza_pargona_code', $mouza_pargona_code)
        ->where('lot_no', $lot_no)
        ->where('vill_townprt_code', $vill_townprt_code)
        ->exists();

        if (!$locationExist) {
            $locationApi = callLandhubAPIMerge('POST', 'NicApiMerge/getLocation', [
                'dist_code' => $dist_code,
                'subdiv_code' => $subdiv_code,
                'cir_code' => $cir_code,
                'mouza_pargona_code' => $mouza_pargona_code,
                'lot_no' => $lot_no,
                'vill_townprt_code' => $vill_townprt_code
            ]);

            if (!$locationApi || $locationApi->responseType != 2) {
                return [
                    'status' => 'n',
                    'msg' => 'Could not retrieve location from API for merging!'
                ];
            }

            if (empty($locationApi->data)) {
                return [
                    'status' => 'n',
                    'msg' => 'Location does not exist in dharitree!'
                ];
            }

            //insert
            $insertLocationArr = [
                'dist_code' => $dist_code,
                'subdiv_code' => $subdiv_code,
                'cir_code' => $cir_code,
                'mouza_pargona_code' => $mouza_pargona_code,
                'lot_no' => $lot_no,
                'vill_townprt_code' => $vill_townprt_code,
                'loc_name' => $locationApi->data->loc_name,
                'unique_loc_code' => $locationApi->data->unique_loc_code,
                'locname_eng' => $locationApi->data->locname_eng,
                'cir_abbr' => $locationApi->data->cir_abbr,
                'dist_abbr' => $locationApi->data->dist_abbr,
                'rural_urban' => $locationApi->data->rural_urban,
                'uuid' => $locationApi->data->uuid,
                'is_gmc' => (isset($locationApi->data->is_gmc) && $locationApi->data->is_gmc != null) ? $locationApi->data->is_gmc : null,
                'lgd_code' => (isset($locationApi->data->lgd_code) && $locationApi->data->lgd_code != null) ? $locationApi->data->lgd_code : null,
                'village_status' => (isset($locationApi->data->village_status) && $locationApi->data->village_status != null) ? $locationApi->data->village_status : null,
                'is_map' => (isset($locationApi->data->is_map) && $locationApi->data->is_map != null) ? $locationApi->data->is_map : null,
                'created_date' => (isset($locationApi->data->created_date) && $locationApi->data->created_date != null) ? $locationApi->data->created_date : null,
                'updated_date' => (isset($locationApi->data->updated_date) && $locationApi->data->updated_date != null) ? $locationApi->data->updated_date : null,
                'user_code' => (isset($locationApi->data->user_code) && $locationApi->data->user_code != null) ? $locationApi->data->user_code : null,
                'status' => (isset($locationApi->data->status) && $locationApi->data->status != null) ? $locationApi->data->status : null,
                'nc_btad' => (isset($locationApi->data->nc_btad) && $locationApi->data->nc_btad != null) ? $locationApi->data->nc_btad : null,
                'is_periphary' => (isset($locationApi->data->is_periphary) && $locationApi->data->is_periphary != null) ? $locationApi->data->is_periphary : null,
                'is_tribal' => (isset($locationApi->data->is_tribal) && $locationApi->data->is_tribal != null) ? $locationApi->data->is_tribal : null,
                'district_headquater' => (isset($locationApi->data->district_headquater) && $locationApi->data->district_headquater != null) ? $locationApi->data->district_headquater : null
            ];
            
            $status = DB::connection($this->connection)->table('location')->insert($insertLocationArr);
            if (!$status) {
                return [
                    'status' => 'n',
                    'msg' => 'Could not insert location in chitha!'
                ];
                
            }
        }

        $dagsApi = callLandhubAPIMerge('POST', 'NicApiMerge/getDags', [
            'dist_code' => $dist_code,
            'subdiv_code' => $subdiv_code,
            'cir_code' => $cir_code,
            'mouza_pargona_code' => $mouza_pargona_code,
            'lot_no' => $lot_no,
            'vill_townprt_code' => $vill_townprt_code
        ]);

        if (!$dagsApi || $dagsApi->responseType != 2) {
            return [
                'status' => 'n',
                'msg' => 'Could not retrieve dags from API!'
            ];
        }

        if (!empty($dagsApi->data)) {
            foreach ($dagsApi->data as $dag) {
                //check in local database
                $dag_no = $dag->dag_no;
                $dag_exist = DB::connection($this->connection)->table('chitha_basic')
                ->where('dist_code', $dist_code)
                ->where('subdiv_code', $subdiv_code)
                ->where('cir_code', $cir_code)
                ->where('mouza_pargona_code', $mouza_pargona_code)
                ->where('lot_no', $lot_no)
                ->where('vill_townprt_code', $vill_townprt_code)
                ->where('dag_no', $dag_no)
                ->exists();
                if (!$dag_exist) {
                    //then insert into chitha
                    $insertChithaArr = [
                        'dist_code' => $dist_code,
                        'subdiv_code' => $subdiv_code,
                        'cir_code' => $cir_code,
                        'mouza_pargona_code' => $mouza_pargona_code,
                        'lot_no' => $lot_no,
                        'vill_townprt_code' => $vill_townprt_code,
                        'dag_no' => $dag_no,
                        'dag_no_int' => $dag->dag_no_int,
                        // 'alpha_dag' => 0,
                        'old_dag_no' => $dag->old_dag_no,
                        'patta_type_code' => $dag->patta_type_code,
                        'patta_no' => $dag->patta_no,
                        'land_class_code' => $dag->land_class_code,
                        'dag_area_b' => $dag->dag_area_b,
                        'dag_area_k' => $dag->dag_area_k,
                        'dag_area_lc' => $dag->dag_area_lc,
                        'dag_area_kr' => $dag->dag_area_kr,
                        'dag_area_g' => $dag->dag_area_g,
                        'dag_area_are' => $dag->dag_area_are,
                        'dag_revenue' => $dag->dag_revenue,
                        'dag_local_tax' => $dag->dag_local_tax,
                        'dag_n_desc' => $dag->dag_n_desc,
                        'dag_s_desc' => $dag->dag_s_desc,
                        'dag_e_desc' => $dag->dag_e_desc,
                        'dag_w_desc' => $dag->dag_w_desc,
                        'dag_n_dag_no' => $dag->dag_n_dag_no,
                        'dag_s_dag_no' => $dag->dag_s_dag_no,
                        'dag_e_dag_no' => $dag->dag_e_dag_no,
                        'dag_w_dag_no' => $dag->dag_w_dag_no,
                        'dag_nlrg_no' => (!empty($dag->dag_nlrg_no)) ? $dag->dag_nlrg_no : '',
                        'dp_flag_yn' => $dag->dp_flag_yn,
                        'user_code' => $dag->user_code, //
                        'date_entry' => $dag->date_entry, //
                        'old_patta_no' => $dag->old_patta_no,
                        'jama_yn' => $dag->jama_yn,
                        // 'survey_no' => $split_dag,
                        'operation' => $dag->operation, //
                        'status' => (isset($dag->status) && $dag->status != null) ? $dag->status : null,
                        'zonal_value' => (isset($dag->zonal_value) && $dag->zonal_value != null) ? $dag->zonal_value : null,
                        'police_station' => (isset($dag->police_station) && $dag->police_station != null) ? $dag->police_station : null,
                        'revenue_paid_upto' => (isset($dag->revenue_paid_upto) && $dag->revenue_paid_upto != null) ? $dag->revenue_paid_upto : null,
                        'block_code' => (isset($dag->block_code) && $dag->block_code != null) ? $dag->block_code : null,
                        'gp_code' => (isset($dag->gp_code) && $dag->gp_code != null) ? $dag->gp_code : null,
                        'category_id' => (isset($dag->category_id) && $dag->category_id != null) ? $dag->category_id : null
                    ];
                    $insertChithaStatus = DB::connection($this->connection)->table('chitha_basic')->insert($insertChithaArr);
                    if (!$insertChithaStatus) {
                        return [
                            'status' => 'n',
                            'msg' => 'Dag entry Failed in chitha basic!'
                        ];
                    }
                }
            }
        }

        //chitha_pattadars
        $chithaPattadars = callLandhubAPIMerge('POST', 'NicApiMerge/getChithaPattadars', [
            'dist_code' => $dist_code,
            'subdiv_code' => $subdiv_code,
            'cir_code' => $cir_code,
            'mouza_pargona_code' => $mouza_pargona_code,
            'lot_no' => $lot_no,
            'vill_townprt_code' => $vill_townprt_code
        ]);

        if (!$chithaPattadars || $chithaPattadars->responseType != 2) {
            return [
                'status' => 'n',
                'msg' => 'Could not retrieve from API!'
            ];
        }
        if (!empty($chithaPattadars->data)) {
            foreach ($chithaPattadars->data as $chithaPdar) {
                // $pdarCheck = $this->db->query("SELECT pdar_id, patta_no, patta_type_code FROM chitha_pattadar WHERE dist_code=? AND subdiv_code=? AND cir_code=? AND mouza_pargona_code=? AND lot_no=? AND vill_townprt_code=? AND patta_no=? AND patta_type_code=? AND pdar_id=?", [$dist_code, $subdiv_code, $cir_code, $mouza_pargona_code, $lot_no, $vill_townprt_code, $chithaPdar->patta_no, $chithaPdar->patta_type_code, $chithaPdar->pdar_id])->row();

                $pdarCheck = DB::connection($this->connection)->table('chitha_pattadar')
                ->where('dist_code', $dist_code)
                ->where('subdiv_code', $subdiv_code)
                ->where('cir_code', $cir_code)
                ->where('mouza_pargona_code', $mouza_pargona_code)
                ->where('lot_no', $lot_no)
                ->where('vill_townprt_code', $vill_townprt_code)
                ->where('patta_no', $chithaPdar->patta_no)
                ->where('patta_type_code', $chithaPdar->patta_type_code)
                ->where('pdar_id', $chithaPdar->pdar_id)
                ->exists();

                if (!$pdarCheck) {
                    //insert chitha_pattadar
                    $chithaPattadarArr = [
                        'dist_code' => $dist_code,
                        'subdiv_code' => $subdiv_code,
                        'cir_code' => $cir_code,
                        'mouza_pargona_code' => $mouza_pargona_code,
                        'lot_no' => $lot_no,
                        'vill_townprt_code' => $vill_townprt_code,
                        'pdar_id' => $chithaPdar->pdar_id,
                        'patta_no' => $chithaPdar->patta_no,
                        'patta_type_code' => $chithaPdar->patta_type_code,
                        'pdar_name' => $chithaPdar->pdar_name,
                        'pdar_guard_reln' => $chithaPdar->pdar_guard_reln,
                        'pdar_father' => $chithaPdar->pdar_father,
                        'pdar_add1' => (isset($chithaPdar->pdar_add1) && $chithaPdar->pdar_add1 != null) ? $chithaPdar->pdar_add1 : null,
                        'pdar_add2' => (isset($chithaPdar->pdar_add2) && $chithaPdar->pdar_add2 != null) ? $chithaPdar->pdar_add2 : null,
                        'pdar_add3' => (isset($chithaPdar->pdar_add3) && $chithaPdar->pdar_add3 != null) ? $chithaPdar->pdar_add3 : null,
                        'pdar_pan_no' => (isset($chithaPdar->pdar_pan_no) && $chithaPdar->pdar_pan_no != null) ? $chithaPdar->pdar_pan_no : null,
                        'pdar_citizen_no' => (isset($chithaPdar->pdar_citizen_no) && $chithaPdar->pdar_citizen_no != null) ? $chithaPdar->pdar_citizen_no : null,
                        'pdar_gender' => (isset($chithaPdar->pdar_gender) && $chithaPdar->pdar_gender != null) ? $chithaPdar->pdar_gender : null,
                        'user_code' => $chithaPdar->user_code,
                        'date_entry' => $chithaPdar->date_entry,
                        'operation' => $chithaPdar->operation,
                        'jama_yn' => $chithaPdar->jama_yn,
                    ];
                    if(Schema::connection($this->connection)->hasColumn('chitha_pattadar', 'pdar_relation') && isset($chithaPdar->pdar_relation) && $chithaPdar->pdar_relation != null) {
                        $chithaPattadarArr['pdar_relation'] = $chithaPdar->pdar_relation;
                    }
                    $chithaPdarStatus = DB::connection($this->connection)->table('chitha_pattadar')->insert($chithaPattadarArr);
                    if (!$chithaPdarStatus) {
                        return [
                            'status' => 'n',
                            'msg' => 'Chitha Pattadar entry Failed in chitha pattadar!'
                        ];
                    }
                }
            }
        }

        //chitha_dag_pattadar
        $chithaDagPattadars = callLandhubAPIMerge('POST', 'NicApiMerge/getChithaDagPattadars', [
            'dist_code' => $dist_code,
            'subdiv_code' => $subdiv_code,
            'cir_code' => $cir_code,
            'mouza_pargona_code' => $mouza_pargona_code,
            'lot_no' => $lot_no,
            'vill_townprt_code' => $vill_townprt_code
        ]);

        if (!$chithaDagPattadars || $chithaDagPattadars->responseType != 2) {
            return [
                'status' => 'n',
                'msg' => 'Could not retrieve dag pattadars from API!'
            ];
        }
        if (!empty($chithaDagPattadars->data)) {
            foreach ($chithaDagPattadars->data as $dagPdar) {
                $checkDagPdar = DB::connection($this->connection)->table('chitha_dag_pattadar')
                ->where('dist_code', $dist_code)
                ->where('subdiv_code', $subdiv_code)
                ->where('cir_code', $cir_code)
                ->where('mouza_pargona_code', $mouza_pargona_code)
                ->where('lot_no', $lot_no)
                ->where('vill_townprt_code', $vill_townprt_code)
                ->where('patta_no', $dagPdar->patta_no)
                ->where('patta_type_code', $dagPdar->patta_type_code)
                ->where('dag_no', $dagPdar->dag_no)
                ->where('pdar_id', $dagPdar->pdar_id)
                ->exists();
                $checkDagInChitha = DB::connection($this->connection)->table('chitha_basic')
                ->where('dist_code', $dist_code)
                ->where('subdiv_code', $subdiv_code)
                ->where('cir_code', $cir_code)
                ->where('mouza_pargona_code', $mouza_pargona_code)
                ->where('lot_no', $lot_no)
                ->where('vill_townprt_code', $vill_townprt_code)
                ->where('dag_no', $dagPdar->dag_no)
                ->exists();

                if (!$checkDagPdar && $checkDagInChitha) {
                    //insert into chitha_dag_pattadar
                    $dagPattadarArr = array(
                        'dist_code' => $dist_code,
                        'subdiv_code' => $subdiv_code,
                        'cir_code' => $cir_code,
                        'mouza_pargona_code' => $mouza_pargona_code,
                        'lot_no' => $lot_no,
                        'vill_townprt_code' => $vill_townprt_code,
                        'dag_no' => $dagPdar->dag_no,
                        'pdar_id' => $dagPdar->pdar_id,
                        'patta_no' => $dagPdar->patta_no,
                        'patta_type_code' => $dagPdar->patta_type_code,
                        'dag_por_b' => $dagPdar->dag_por_b,
                        'dag_por_k' => $dagPdar->dag_por_k,
                        'dag_por_lc' => $dagPdar->dag_por_lc,
                        'dag_por_g' => $dagPdar->dag_por_g,
                        'dag_por_kr' => (isset($dagPdar->dag_por_kr) && $dagPdar->dag_por_kr != null) ? $dagPdar->dag_por_kr : null,
                        'pdar_land_n' => (isset($dagPdar->pdar_land_n) && $dagPdar->pdar_land_n != null) ? $dagPdar->pdar_land_n : null,
                        'pdar_land_s' => (isset($dagPdar->pdar_land_s) && $dagPdar->pdar_land_s != null) ? $dagPdar->pdar_land_s : null,
                        'pdar_land_e' => (isset($dagPdar->pdar_land_e) && $dagPdar->pdar_land_e != null) ? $dagPdar->pdar_land_e : null,
                        'pdar_land_w' => (isset($dagPdar->pdar_land_w) && $dagPdar->pdar_land_w != null) ? $dagPdar->pdar_land_w : null,
                        'pdar_land_acre' => (isset($dagPdar->pdar_land_acre) && $dagPdar->pdar_land_acre != null) ? $dagPdar->pdar_land_acre : null,
                        'pdar_land_revenue' => (isset($dagPdar->pdar_land_revenue) && $dagPdar->pdar_land_revenue != null) ? $dagPdar->pdar_land_revenue : null,
                        'pdar_land_localtax' => (isset($dagPdar->pdar_land_localtax) && $dagPdar->pdar_land_localtax != null) ? $dagPdar->pdar_land_localtax : null,
                        'user_code' => $dagPdar->user_code,
                        'date_entry' => $dagPdar->date_entry,
                        'operation' => $dagPdar->operation,
                        'p_flag' => (isset($dagPdar->p_flag) && $dagPdar->p_flag != null) ? $dagPdar->p_flag : null,
                        'jama_yn' => (isset($dagPdar->jama_yn) && $dagPdar->jama_yn != null) ? $dagPdar->jama_yn : null,
                        'pdar_land_map' => (isset($dagPdar->pdar_land_map) && $dagPdar->pdar_land_map != null) ? $dagPdar->pdar_land_map : null,

                    );
                    $dagPattadarStatus = DB::connection($this->connection)->table('chitha_dag_pattadar')->insert($dagPattadarArr);
                    if (!$dagPattadarStatus) {
                        return [
                            'status' => 'n',
                            'msg' => 'Chitha Dag Pattadar entry Failed in chitha dag pattadar!'
                        ];
                    }
                }
            }
        }


        //chitha_rmk_lmnote
        $chithaLmNotes = callLandhubAPIMerge('POST', 'NicApiMerge/getChithaLmNote', [
            'dist_code' => $dist_code,
            'subdiv_code' => $subdiv_code,
            'cir_code' => $cir_code,
            'mouza_pargona_code' => $mouza_pargona_code,
            'lot_no' => $lot_no,
            'vill_townprt_code' => $vill_townprt_code
        ]);

        if (!$chithaLmNotes || $chithaLmNotes->responseType != 2) {
            return [
                'status' => 'n',
                'msg' => 'Could not retrieve lm notes from API!'
            ];
        }


        if (!empty($chithaLmNotes->data)) {
            foreach ($chithaLmNotes->data as $lmnote) {
                $checkLmNote = DB::connection($this->connection)->table('chitha_rmk_lmnote')
                ->where('dist_code', $dist_code)
                ->where('subdiv_code', $subdiv_code)
                ->where('cir_code', $cir_code)
                ->where('mouza_pargona_code', $mouza_pargona_code)
                ->where('lot_no', $lot_no)
                ->where('vill_townprt_code', $vill_townprt_code)
                ->where('dag_no', $lmnote->dag_no)
                ->where('lm_note_cron_no', $lmnote->lm_note_cron_no)
                ->where('rmk_type_hist_no', $lmnote->rmk_type_hist_no)
                ->exists();

                $checkDagInChitha = DB::connection($this->connection)->table('chitha_basic')
                ->where('dist_code', $dist_code)
                ->where('subdiv_code', $subdiv_code)
                ->where('cir_code', $cir_code)
                ->where('mouza_pargona_code', $mouza_pargona_code)
                ->where('lot_no', $lot_no)
                ->where('vill_townprt_code', $vill_townprt_code)
                ->where('dag_no', $lmnote->dag_no)
                ->exists();

                if (!$checkLmNote && $checkDagInChitha) {
                    $lmnoteArr = [
                        'dist_code' => $dist_code,
                        'subdiv_code' => $subdiv_code,
                        'cir_code' => $cir_code,
                        'mouza_pargona_code' => $mouza_pargona_code,
                        'lot_no' => $lot_no,
                        'vill_townprt_code' => $vill_townprt_code,
                        'dag_no' => $lmnote->dag_no,
                        'lm_note_cron_no' => $lmnote->lm_note_cron_no,
                        'rmk_type_hist_no' => $lmnote->rmk_type_hist_no,
                        'lm_note_lno' => $lmnote->lm_note_lno,
                        'lm_note' => $lmnote->lm_note,
                        'lm_note_date' => (isset($lmnote->lm_note_date) && $lmnote->lm_note_date != null) ? $lmnote->lm_note_date : null,
                        'lm_code' => (isset($lmnote->lm_code) && $lmnote->lm_code != null) ? $lmnote->lm_code : null,
                        'lm_sign' => $lmnote->lm_sign,
                        'co_approval' => $lmnote->co_approval,
                        'user_code' => $lmnote->user_code,
                        'date_entry' => $lmnote->date_entry,
                        'operation' => $lmnote->operation
                    ];
                    $lmnoteStatus = DB::connection($this->connection)->table('chitha_rmk_lmnote')->insert($lmnoteArr);
                    if (!$lmnoteStatus) {
                        return [
                            'status' => 'n',
                            'msg' => 'Chitha LM Note entry Failed in lmnote table!'
                        ];
                    }
                }
            }
        }

        $exist = DB::connection($this->connection)->table('demarcation_villages')
        ->where('dist_code', $dist_code)
        ->where('subdiv_code', $subdiv_code)
        ->where('cir_code', $cir_code)
        ->where('mouza_pargona_code', $mouza_pargona_code)
        ->where('lot_no', $lot_no)
        ->where('vill_townprt_code', $vill_townprt_code)
        ->exists();

        if(!$exist) {
            //insert
            $insertStatus = DB::connection($this->connection)->table('demarcation_villages')
            ->insert([
                'dist_code' => $dist_code,
                'subdiv_code' => $subdiv_code,
                'cir_code' => $cir_code,
                'mouza_pargona_code' => $mouza_pargona_code,
                'lot_no' => $lot_no,
                'vill_townprt_code' => $vill_townprt_code,
                'user_code' => '',
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
                'is_merged' => 1
            ]);
            if(!$insertStatus) {
                return [
                    'status' => 'n',
                    'msg' => 'Could not create merge entry!'
                ];
            }
        }
        else {
            //update
            $updStatus = DB::connection($this->connection)->table('demarcation_villages')
            ->where('dist_code', $dist_code)
            ->where('subdiv_code', $subdiv_code)
            ->where('cir_code', $cir_code)
            ->where('mouza_pargona_code', $mouza_pargona_code)
            ->where('lot_no', $lot_no)
            ->where('vill_townprt_code', $vill_townprt_code)
            ->update([
                'is_merged' => 1
            ]);
            if($updStatus < 1) {
                return [
                    'status' => 'n',
                    'msg' => 'Could not update merge entry!'
                ];
            }
        }

        return [
            'status' => 'y',
            'msg' => 'Successfully merged all dharitree data to chitha!'
        ];
    }
}
