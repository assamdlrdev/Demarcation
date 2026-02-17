<?php

namespace App\Http\Controllers\Api;
use App\Models\LoginModel;
use App\Http\Controllers\Controller;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class LoginController extends Controller
{

    private function refreshLm($loginModel, $lm_code) {
        $lmCodeExist = $loginModel->lmCodeExist($lm_code);
        if(!$lmCodeExist) {
            //insert into lm_code
            $insLmCode = $loginModel->insertLmCode($lm_code);
        }
        else {
            //update lm_code
            $updLmCode = $loginModel->updateLmCode($lm_code);
        }
        return $lmCodeExist;
    }

    private function refreshUser($loginModel, $user) {
        $userCodeExist = $loginModel->userCodeExist($user);
        if(!$userCodeExist) {
            //insert into users
            $insUser = $loginModel->insertUser($user);
        }
        else {
            //update _users
            // $updUser = $loginModel->updateUser($user);
        }
        return $userCodeExist;
    }

    public function addLoginLog(Request $request) {
        $api_key = $request->query('api_key') ? $request->query('api_key') : ($request->api_key ? $request->api_key : null);
        $dist_code = $request->query('dist_code') ? $request->query('dist_code') : ($request->dist_code ? $request->dist_code : null);
        $username = $request->query('username') ? $request->query('username') : ($request->username ? $request->username : null);

        if($api_key ==null || $dist_code == null || $username == null) {
            $response = [
                'message' => 'No Input Found.',
                'responseCode' => 0
            ];
            return response()->json(
                $response
            , 500);
        }

        if($api_key != "demarcation_application") {
            $response = [
                'message' => 'API Failed.',
                'responseCode' => 0
            ];
            return response()->json(
                $response
            , 500);
        }

        try {
            $form_data = [
                'dist_code' => $dist_code,
                'username' => $username,
                'id' => time(),
                'expired' => 0
            ];
            $encrypt = openssl_encrypt($form_data['id'], "AES-128-CTR", "singleENCRYPT", 0, '1234567893032221');
            $loginModel = new LoginModel();
            $loginModel->connection = $loginModel->dbswitch();
            // return response()->json(['dist_code'=>$dist_code, 'username'=>$username, 'connection'=>$loginModel->connection]);
            DB::beginTransaction();
            $inserted = $loginModel->addLoginLog($form_data);
            if(!$inserted) {
                DB::rollBack();
                $response = [
                    'message' => 'Unable to insert into addLoginLog',
                    'responseCode' => 0
                ];
                return response()->json(
                    $response
                , 500);
            }
            DB::commit();
            $response = [
                'message' => 'Successfully Added',
                'responseCode' => 1,
                'id' => $encrypt
            ];
            return response()->json(
                $response
            , 200);
        }
        catch (Exception $e) {
            $response = [
                'message' => 'Error while inserting.',
                'responseCode' => 0
            ];
            return response()->json(
                $response
            , 500);
        }
    }

    public function singleSignRedirect(Request $request) {

        $queryString = parse_str($request->demarcation_data, $output);
        $demarcation_data = $output;
        $district = $request->district;
        $id = $request->id;

        $is_lm = $demarcation_data['is_lm'];
        $user_desig_code = $demarcation_data['user_desig_code'];
        $login_user = $demarcation_data['login_user'];
        $user = $demarcation_data['user'];
        $password_change_flag = $login_user['password_change_flag'];
        $rnd_id = openssl_decrypt($id, "AES-128-CTR", "singleENCRYPT", 0, '1234567893032221');
        if($is_lm == 'y') {
            $lm_code = $demarcation_data['lm_code'];
        }

        $loginModel = new LoginModel();
        $loginModel->connection = $loginModel->dbswitch();
        $getLog = $loginModel->getLoginLog([
            'id' => $rnd_id,
            'expired' => 0
        ]);

        if(empty($getLog)) {
            $response = [
                'status' => 'n',
                'msg' => 'The page you requested was not found!'
            ];
            return response()->json(
                $response
            , 401);
        }
        $updateLog = $loginModel->updateLoginLog($rnd_id);

        if($updateLog < 1) {
            $response = [
                'status' => 'n',
                'msg' => 'Could not update Log!'
            ];
            return response()->json(
                $response
            , 401);
        }

        $logindetails = false;
        $dist = $login_user['dist_code'];
        $loginModel->connection = $loginModel->dbswitch($dist);
        $validateuserdetails = $loginModel->ValidateSingleSignUser($login_user);
        DB::beginTransaction();
        if($validateuserdetails) {
            if($is_lm == 'y') {
                $exist = $this->refreshLm($loginModel, $lm_code);
            }
            else {
                $exist = $this->refreshUser($loginModel, $user);
            }

            $logindetails = true;
        }
        else {
            //inser into loginuser_table
            $insLoginUser = $loginModel->insertLoginUser($login_user);
            if($is_lm == 'y') {
                $exist = $this->refreshLm($loginModel, $lm_code);
            }
            else {
                $exist = $this->refreshUser($loginModel, $user);
            }
            $logindetails = true;
        }

        if (!$logindetails) {
            DB::rollBack();
            $response = [
                'status' => 'n',
                'msg' => 'User Not Validated!'
            ];
            return response()->json(
                $response
            , 401);
        }

        if ($is_lm == 'y') {
            $user_desig_code = 'LM';
        } else {
            $user_desig_code = $user_desig_code;
        }

        $usertype = $loginModel->getRoleCodeFromDharCode($user_desig_code);
        if (!$usertype) {
            DB::rollBack();
            $response = [
                'status' => 'n',
                'msg' => 'Not Authorized for this UserType!'
            ];
            return response()->json(
                $response
            , 401);
        }
        
        DB::commit();

        $payload = [
            'usertype' => $usertype,
            'loggedin' => true,
            'usercode' => $login_user['user_code'],
            'dcode' => $login_user['dist_code'],
            'subdiv_code' => $login_user['subdiv_code'],
            'cir_code' => $login_user['cir_code'],
            'mouza_pargona_code' => $login_user['mouza_pargona_code'],
            'lot_no' => $login_user['lot_no'],
            'user_desig_code' => $user_desig_code,
            'is_password_changed' => ($password_change_flag == 1) ? '1' : null
        ];

        $userDetails = [
            'username' => $login_user['use_name'],
            'dist_code' => $login_user['dist_code'],
            'subdiv_code' => (isset($login_user['subdiv_code']) && $login_user['subdiv_code'] != '') ? $login_user['subdiv_code'] : '00',
            'cir_code' => (isset($login_user['cir_code']) && $login_user['cir_code'] != '') ? $login_user['cir_code'] : '00',
            'mouza_pargona_code' => (isset($login_user['mouza_pargona_code']) && $login_user['mouza_pargona_code'] != '') ? $login_user['mouza_pargona_code'] : '00',
            'lot_no' => (isset($login_user['lot_no']) && $login_user['lot_no'] != '') ? $login_user['lot_no'] : '00',
            'user_role' => isset($usertype) ? $usertype : '',
            'action' => $loginModel::$USER_ACTIVITY_LOGIN
        ];

        $loginModel->connection = $loginModel->dbswitch();
        $insUserActivity = $loginModel->UserActivity($userDetails, 'Login from ' . config('constants.SINGLESIGN_LINK'));

        $token = jwtencode($payload);

        $response = [
            'status' => 'y',
            'msg' => 'Successfully logged in!',
            'data' => $token,
            'usertype' => $usertype
        ];
        return response()->json(
            $response
        , 200);
    }
}
