<?php

namespace App\Traits;

use Illuminate\Support\Facades\DB;

trait CommonTrait
{
    //

    protected $district;

    public function set_connection($dist_code = null) {
        $this->setDistrict($dist_code);
        return $this->dbswitch();
    }

    public function beginTransaction() {
        $connection = $this->switchConnection();
        return DB::connection($connection)->beginTransaction();
    }

    public function rollbackTransaction() {
        $connection = $this->switchConnection();
        return DB::connection($connection)->rollBack();
    }

    public function commitTransaction() {
        $connection = $this->switchConnection();
        DB::connection($connection)->commit();
    }

    private function setDistrict($district) {
        $this->district = $district;
    }

    private function switchConnection() {
        switch ($this->district) {
            case '17':
                $connection = 'pgsql_dibrugarh';
                break;
            case '21':
                $connection = 'pgsql_karimganj';
                break;
            case '22':
                $connection = 'pgsql_hailakandi';
                break;
            case '23':
                $connection = 'pgsql_cachar';
                break;
            case '02':
                $connection = 'pgsql_dhubri';
                break;
            case '25':
                $connection = 'pgsql_dhemaji';
                break;
            case '08':
                $connection = 'pgsql_darrang';
                break;
            case '12':
                $connection = 'pgsql_lakhimpur';
                break;
            case '07':
                $connection = 'pgsql_kamrup';
                break;
            case '24':
                $connection = 'pgsql_kamrupm';
                break;
            case '03':
                $connection = 'pgsql_goalpara';
                break;
            case '01':
                $connection = 'pgsql_kokrajhar';
                break;
            case '05':
                $connection = 'pgsql_barpeta';
                break;
            case '06':
                $connection = 'pgsql_nalbari';
                break;
            case '13':
                $connection = 'pgsql_bongaigaon';
                break;
            case '14':
                $connection = 'pgsql_golaghat';
                break;
            case '15':
                $connection = 'pgsql_jorhat';
                break;
            case '16':
                $connection = 'pgsql_sibsagar';
                break;
            case '27':
                $connection = 'pgsql_udalguri';
                break;
            case '32':
                $connection = 'pgsql_morigaon';
                break;
            case '33':
                $connection = 'pgsql_nagaon';
                break;
            case '34':
                $connection = 'pgsql_majuli';
                break;
            case '35':
                $connection = 'pgsql_biswanath';
                break;
            case '36':
                $connection = 'pgsql_hojai';
                break;
            case '37':
                $connection = 'pgsql_charaideo';
                break;
            case '38':
                $connection = 'pgsql_southsalmara';
                break;
            case '39':
                $connection = 'pgsql_bajali';
                break;
            case '10':
                $connection = 'pgsql_chirang';
                break;
            case '11':
                $connection = 'pgsql_sonitpur';
                break;
            case '18':
                $connection = 'pgsql_tinsukia';
                break;
            default:
                $connection = 'pgsql';
        }
        return $connection;
    }

    private function dbswitch()
    {
        $connection = $this->switchConnection();
        return $this->setConnection($connection)->newQuery();
    }

}
