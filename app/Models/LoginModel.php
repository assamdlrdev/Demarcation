<?php

namespace App\Models;

use App\Traits\CommonTrait;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class LoginModel extends Model
{
    use CommonTrait;
    protected $table = "loginuser_table";
    public $connection;

    public static $DEO_CODE = '00';
    public static $ADMIN_CODE = '1'; //district admin
    public static $SUPERADMIN_CODE = '2';
    public static $LM_CODE = '3'; //LRA
    public static $CO_CODE = '4';
    public static $SK_CODE = '5'; //Lrs
    public static $ADC_CODE = '6';
    public static $DC_CODE = '7';
    public static $SDO_CODE = '8';
    public static $GUEST_CODE = '9';
    public static $SUPERVISOR_CODE = '10';
    public static $SURVEYOR_CODE = '11';
    public static $SPMU_CODE = '12';
    public static $SURVEY_SUPER_ADMIN_CODE = '13';
    public static $SURVEY_GIS_ASSISTANT_CODE = '14'; //circle
    public static $STATE_GIS_CODE = '15'; // state

    public static $USER_ACTIVITY_LOGIN = 'LOGIN';
    public static $USER_ACTIVITY_PW_CHANGED = 'PASSWORD_UPDATED';
    public static $USER_ACTIVITY_MOBILE_CHANGED = 'MOBILE_NUMBER_UPDATED';

    public function addLoginLog($form_data) {
        $inserted = DB::connection($this->connection)->table('login_log')->insert($form_data);
        return $inserted;
    }

    public function getLoginLog($data) {
        $get = DB::connection($this->connection)->table('login_log')
        ->where('id', $data['id'])
        ->where('expired', $data['expired'])
        ->get();

        return $get;
    }

    public function updateLoginLog($id) {
        $updated = DB::connection($this->connection)->table('login_log')
        ->where('id', $id)
        ->update([
            'expired' => 1
        ]);

        return $updated;
    }

    public function ValidateSingleSignUser($login_user) {
        $user = DB::connection($this->connection)->table($this->table)
        ->where('use_name', $login_user['use_name'])
        ->where('dist_code', $login_user['dist_code'])
        ->where('subdiv_code', $login_user['subdiv_code'])
        ->where('cir_code', $login_user['cir_code'])
        ->where('mouza_pargona_code', $login_user['mouza_pargona_code'])
        ->where('lot_no', $login_user['lot_no'])
        ->where('user_code', $login_user['user_code'])
        ->get();

        if(empty($user)) {
            return null;
        }

        return $user[0];
    }

    public function lmCodeExist($lm_code) {
        $exist = DB::connection($this->connection)->table('lm_code')
        // ->where('lm_name', $lm_code['lm_name'])
        ->where('dist_code', $lm_code['dist_code'])
        ->where('subdiv_code', $lm_code['subdiv_code'])
        ->where('cir_code', $lm_code['cir_code'])
        ->where('mouza_pargona_code', $lm_code['mouza_pargona_code'])
        ->where('lot_no', $lm_code['lot_no'])
        ->where('lm_code', $lm_code['lm_code'])
        ->exists();

        return $exist;
    }

    public function userCodeExist($user) {
        $exist = DB::connection($this->connection)->table('users')
        // ->where('lm_name', $lm_code['lm_name'])
        ->where('dist_code', $user['dist_code'])
        ->where('subdiv_code', $user['subdiv_code'])
        ->where('cir_code', $user['cir_code'])
        ->where('user_code', $user['user_code'])
        ->exists();

        return $exist;
    }

    public function insertLmCode($lm_code) {
        $insertStatus = DB::connection($this->connection)->table('lm_code')->insert($lm_code);
        return $insertStatus;
    }

    public function insertUser($user) {
        $insertStatus = DB::connection($this->connection)->table('users')->insert($user);
        return $insertStatus;
    }

    public function updateLmCode($lm_code) {
         $updated = DB::connection($this->connection)->table('lm_code')
        ->where('dist_code', $lm_code['dist_code'])
        ->where('subdiv_code', $lm_code['subdiv_code'])
        ->where('cir_code', $lm_code['cir_code'])
        ->where('mouza_pargona_code', $lm_code['mouza_pargona_code'])
        ->where('lot_no', $lm_code['lot_no'])
        ->where('lm_code', $lm_code['lm_code'])
        ->update($lm_code);
        return $updated;
    }



    public function UserActivity($userdetails, $description = '') {
        $insertStatus = DB::connection($this->connection)->table('user_activity')->insert([
            'username'=>$userdetails['username'],
            'action'=>$userdetails['action'],
            'created_at'=>date('Y-m-d H:i:s'),
            'ip'=>$_SERVER['REMOTE_ADDR'],
            'dist_code'=>$userdetails['dist_code'],
            'subdiv_code'=>(isset($userdetails['subdiv_code'])) ? $userdetails['subdiv_code'] : '00',
            'cir_code'=>(isset($userdetails['cir_code'])) ? $userdetails['cir_code'] : '00',
            'mouza_pargona_code'=>(isset($userdetails['mouza_pargona_code'])) ? $userdetails['mouza_pargona_code'] : '00',
            'lot_no'=>(isset($userdetails['lot_no'])) ? $userdetails['lot_no'] : '00',
            'user_type'=>(isset($userdetails['user_role'])) ? $userdetails['user_role'] : '',
            'description'=>$description
        ]);

        return $insertStatus;
    }

    public function getRoleCodeFromDharCode($user_desig_code) {
        $code = '';
        switch ($user_desig_code) {
            case 'DEO':
                $code = self::$DEO_CODE;
                break;
            case 'LM':
                $code = self::$LM_CODE;
                break;
            case 'CO':
                $code = self::$CO_CODE;
                break;
            case 'SK':
                $code = self::$SK_CODE;
                break;
            case 'ADC':
                $code = self::$ADC_CODE;
                break;
            case 'DC':
                $code = self::$DC_CODE;
                break;
            case 'SDO':
                $code = self::$SDO_CODE;
                break;
            case 'SPVR':
                $code = self::$SUPERVISOR_CODE;
                break;
            case 'SVR':
                $code = self::$SURVEYOR_CODE;
                break;
            case 'SPMU':
                $code = self::$SPMU_CODE;
                break;
            case 'ADMIN':
                $code = self::$ADMIN_CODE;
                break;
            case 'SADM':
                $code = self::$SUPERADMIN_CODE;
                break;
            case 'GUEST':
                $code = self::$GUEST_CODE;
                break;
            case 'SSADM':
                $code = self::$SURVEY_SUPER_ADMIN_CODE;
                break;
            case 'CGISA':
                $code = self::$SURVEY_GIS_ASSISTANT_CODE;
                break;
            case 'SGISA':
                $code = self::$STATE_GIS_CODE;
                break;
            default:
                $code = '';
                break;
        }
        return $code;
    }
    
}
