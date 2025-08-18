<?php

namespace App\Services;

class CalculationsService
{
    // PERT Distribution Methods
    public static function calculatePertValue($min, $avg, $max)
    {
        return self::calcaulteRandomBetweenMinMax($min, $max);
    }

    public static function calcaulteRandomBetweenMinMax($min, $max)
    {
        $avg = ($min + $max) / 2;

        $expectedValue = ($min + 4 * $avg + $max) / 6;
        $standardDeviation = ($max - $min) / 6;
        
        // Add some randomness using normal distribution
        $u1 = rand(0, 100000) / 100000;
        $u2 = rand(0, 100000) / 100000;
        
        $z = sqrt(-2 * log($u1)) * cos(2 * pi() * $u2);
        
        $result = $expectedValue + ($z * $standardDeviation);
        
        return round(max($min, min($max, $result)));
    }
}