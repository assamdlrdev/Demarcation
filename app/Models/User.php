<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Traits\CommonTrait;

class User extends Model
{
    //
    use CommonTrait;
    protected $table = "users";

    public function loginSubmit($db, $email, $password) {
        $user = $db->where('email', $email)->get();
        if(!$user) {
            return [
                'status' => 'n',
                'msg' => 'No User found!'
            ];
        }
        

        
    }
}
