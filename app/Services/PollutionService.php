<?php

namespace App\Services;

class PollutionService
{
    public static function releaseCarbonFootprint($company, $carbonFootprint){
        $company->update(['footprint' => $company->footprint + $carbonFootprint]);

        return $company->footprint;
    }
}