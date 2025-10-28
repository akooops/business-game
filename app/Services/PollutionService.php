<?php

namespace App\Services;

class PollutionService
{
    public static function releaseCarbonFootprint($company, $carbonFootprint){
        $company->update(['carbon_footprint' => $company->carbon_footprint + $carbonFootprint]);

        return $company->carbon_footprint;
    }
}