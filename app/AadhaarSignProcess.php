<?php

namespace App;
use Illuminate\Http\Request;

interface AadhaarSignProcess
{
    public function setConfig();
    public function esignProcess(Request $request);
    public function esignResponse(Request $request);
}
