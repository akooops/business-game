<?php

namespace App\Services;

use App\Models\Setting;
use Carbon\Carbon;

class SalesService
{
    // Get the current gameweek product market price
    public static function getCurrentGameweekProductMarketPrice($product)
    {
        $productDemand = $product->demands()->where('gameweek', SettingsService::getCurrentGameWeek())->first();

        return ($productDemand) ? $productDemand->market_price : 0;
    }
}