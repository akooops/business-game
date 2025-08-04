<?php

namespace App\Services;

use App\Models\Ad;

class AdsService
{
    public static function createAdPackage($company, $advertiser, $product){

        $ad = Ad::create([
            'company_id' => $company->id,
            'advertiser_id' => $advertiser->id,
            'product_id' => $product->id,
            'price' => $advertiser->real_price,
            'duration_days' => $advertiser->duration_days,
            'market_impact_percentage' => CalculationsService::calcaulteRandomBetweenMinMax($advertiser->min_market_impact_percentage, $advertiser->max_market_impact_percentage),
            'status' => Ad::STATUS_ACTIVE,
            'started_at' => SettingsService::getCurrentTimestamp(),
        ]);

        FinanceService::payAdPackage($company, $ad);
        NotificationService::createAdPackageCreatedNotification($company, $ad);
    }

    public static function getAdMarketImpactPercentage($company, $product){
        $adMarketImpactPercentage = $company->ads()->where('product_id', $product->id)->where('status', Ad::STATUS_ACTIVE)->sum('market_impact_percentage');

        return $adMarketImpactPercentage;
    }

    public static function processAdPackageCompletion($company){
        $ads = $company->ads()->where('status', Ad::STATUS_ACTIVE)->get();

        foreach($ads as $ad){
            $currentTimestamp = SettingsService::getCurrentTimestamp();
            $completedAt = $ad->started_at->copy()->addDays($ad->duration_days);

            if($completedAt <= $currentTimestamp){
                $ad->update([
                    'status' => Ad::STATUS_COMPLETED,
                    'completed_at' => $currentTimestamp,
                ]);

                NotificationService::createAdPackageCompletedNotification($company, $ad);
            }
        }
    }
}
