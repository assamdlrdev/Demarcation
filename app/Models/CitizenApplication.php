<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CitizenApplication extends Model
{
    public function attachment()
    {
        return $this->hasOne(Attachments::class, 'citizen_application_id');
    }

    public function demarcationdagareas()
    {
        return $this->hasOne(DemarcationDagArea::class, 'citizen_application_id');
    }
}
