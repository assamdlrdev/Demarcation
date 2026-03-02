<?php

namespace App\Models;

use App\Traits\CommonTrait;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class CoModel extends Model
{
    //
    use CommonTrait;

    protected $table = "";
    public $connection;

    public function getApplications($dist_code, $subdiv_code, $cir_code) {
        $applications = DB::connection($this->connection)->table("demarcation_basic")
        ->where('dist_code', $dist_code)
        ->where('subdiv_code', $subdiv_code)
        ->where('cir_code', $cir_code)
        ->where('status', 'A')
        ->get();

        return $applications;
    }
}
