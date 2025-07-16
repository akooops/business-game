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
}