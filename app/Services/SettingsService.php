<?php

namespace App\Services;

use App\Models\Setting;
use Carbon\Carbon;

class SettingsService
{
    // Get the game status
    public static function getGameStatus()
    {
        $setting = Setting::where('key', 'game_status')->first();
        
        return ($setting) ? $setting->value : 'stopped';
    }

    // Set the game status
    public static function setGameStatus($status)
    {
        Setting::updateOrCreate(
            ['key' => 'game_status'],
            ['value' => $status]
        );
    }

    // Check if the game is running
    public static function isRunning()
    {
        return self::getGameStatus() == 'running';
    }

    // Check if the game is stopped
    public static function isStopped()
    {
        return self::getGameStatus() == 'stopped';
    }

    // Get the game start timestamp
    public static function getGameStartTimestamp()
    {
        $setting = Setting::where('key', 'game_start_timestamp')->first();
        
        return ($setting) ? Carbon::parse($setting->value) : now();
    }

    // Get the current timestamp 
    public static function getCurrentTimestamp()
    {
        $setting = Setting::where('key', 'current_timestamp')->first();
        
        return ($setting) ? Carbon::parse($setting->value) : now();
    }

    // Set the current timestamp
    public static function setCurrentTimestamp(Carbon $timestamp)
    {
        Setting::updateOrCreate(
            ['key' => 'current_timestamp'],
            ['value' => $timestamp->toDateTimeString()]
        );
    }

    // Get the current game week
    public static function getCurrentGameWeek()
    {
        $gameStartTimestamp = self::getGameStartTimestamp();
        $currentTimestamp = self::getCurrentTimestamp();

        $diffInDays = $currentTimestamp->diffInDays($gameStartTimestamp);

        return round($diffInDays / 7) + 1;
    }

    // Get the game speed
    public static function getGameSpeed()
    {
        $setting = Setting::where('key', 'game_speed')->first();
        
        switch($setting->value){
            case '0.25x':
                return 0.25;
            case '0.5x':
                return 0.5;
            case '1x':
                return 1;
            case '2x':
                return 2;
            case '4x':
                return 4;
            default:
                return 1;
        }
    }

    // Get the demand visibility ahead weeks
    public static function getDemandVisibilityAheadWeeks()
    {
        $setting = Setting::where('key', 'demand_visiblity_ahead_weeks')->first();
        
        return ($setting) ? $setting->value : 1;
    }

    // Get the ability to sell machines
    public static function getAbilityToSellMachines()
    {
        $setting = Setting::where('key', 'ability_to_sell_machines')->first();
        
        return ($setting) ? ($setting->value == 'yes') : true;
    }

    // Get the min loss on sale days percentage
    public static function getMinLossOnSaleDaysPercentage()
    {
        $setting = Setting::where('key', 'min_loss_on_sale_days_percentage')->first();
        
        return ($setting) ? $setting->value : 0.35;
    }

    // Get the inital funds
    public static function getInitalFunds()
    {
        $setting = Setting::where('key', 'inital_funds')->first();
        
        return ($setting) ? $setting->value : 5000000;
    }
}