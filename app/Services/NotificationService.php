<?php

namespace App\Services;

use App\Models\Company;
use App\Models\Notification;

class NotificationService
{
    public static function createFinanceFundsChangedNotification($company, $amount)
    {
        return Notification::create([
            'type' => Notification::TYPE_FINANCE_FUNDS_CHANGED,
            'title' => 'Funds Changed',
            'message' => "Funds changed by DZD " . $amount . ". New balance: DZD " . $company->funds,
            'url' => route('company.dashboard.index'),
            'user_id' => $company->user_id,
        ]);
    }

    public static function createTechnologyResearchStartedNotification($companyTechnology)
    {
        return Notification::create([
            'type' => Notification::TYPE_TECHNOLOGY_RESEARCH_STARTED,
            'title' => 'Technology Research Started',
            'message' => "Technology research started for {$companyTechnology->technology->name} at " . $companyTechnology->started_at->format('Y-m-d H:i:s'),
            'url' => route('company.technologies.index'),
            'user_id' => $companyTechnology->company->user_id,
        ]);
    }

    public static function createTechnologyResearchCompletedNotification($companyTechnology)
    {
        return Notification::create([
            'type' => Notification::TYPE_TECHNOLOGY_RESEARCH_COMPLETED,
            'title' => 'Technology Research Completed',
            'message' => "Technology research completed for {$companyTechnology->technology->name} at " . $companyTechnology->completed_at->format('Y-m-d H:i:s') . ". You have unlocked {$companyTechnology->technology->products->count()} products!",
            'url' => route('company.technologies.index'),
            'user_id' => $companyTechnology->company->user_id,
        ]);
    }

    public static function createPurchaseOrderedNotification($purchase){
        return Notification::create([
            'type' => Notification::TYPE_PURCHASE_ORDERED,
            'title' => 'Purchase Ordered',
            'message' => "Purchase ordered for {$purchase->product->name} at " . $purchase->ordered_at->format('Y-m-d H:i:s') . ". Estimated delivery date: " . $purchase->estimated_delivered_at->format('Y-m-d H:i:s') . ".",
            'url' => route('company.purchases.index'),
            'user_id' => $purchase->company->user_id,
        ]);
    }

    public static function createPurchaseDeliveredNotification($purchase){
        return Notification::create([
            'type' => Notification::TYPE_PURCHASE_DELIVERED,
            'title' => 'Purchase Delivered',
            'message' => "Purchase delivered for {$purchase->product->name} at " . $purchase->delivered_at->format('Y-m-d H:i:s') . ".",
            'url' => route('company.purchases.index'),
            'user_id' => $purchase->company->user_id,
        ]);
    }

    public static function createPurchaseCancelledNotification($purchase, $reason){
        return Notification::create([
            'type' => Notification::TYPE_PURCHASE_CANCELLED,
            'title' => 'Purchase Cancelled',
            'message' => "Purchase cancelled for {$purchase->product->name} at " . $purchase->cancelled_at->format('Y-m-d H:i:s') . ". Reason: " . $reason . ".",
            'url' => route('company.purchases.index'),
            'user_id' => $purchase->company->user_id,
        ]);
    }

    public static function createPurchaseDeliveryDelayedNotification($purchase){
        return Notification::create([
            'type' => Notification::TYPE_PURCHASE_DELIVERY_DELAYED,
            'title' => 'Purchase Delivery Delayed',
            'message' => "Purchase delivery delayed for {$purchase->product->name} until " . $purchase->real_delivered_at->format('Y-m-d H:i:s') . ".",
            'url' => route('company.purchases.index'),
            'user_id' => $purchase->company->user_id,
        ]);
    }

    public static function createCountriesImportBlockedNotification($countries){
        $companies = Company::get();

        foreach($companies as $company){
            Notification::create([
                'type' => Notification::TYPE_COUNTRIES_IMPORT_BLOCKED,
                'title' => 'Countries Import Blocked',
                'message' => "Countries import blocked for " . implode(', ', $countries) . ".",
                'url' => route('company.purchases.index'),
                'user_id' => $company->user_id,
            ]);
        }
    }

    public static function createCountriesImportAllowedNotification($countries){
        $companies = Company::get();
        
        foreach($companies as $company){
            Notification::create([
                'type' => Notification::TYPE_COUNTRIES_IMPORT_ALLOWED,
                'title' => 'Countries Import Allowed',
                'message' => "Countries import allowed for " . implode(', ', $countries) . ".",
                'url' => route('company.purchases.index'),
                'user_id' => $company->user_id,
            ]);
        }
    }

    public static function createCountriesCustomsDutiesRateRaisedNotification($countries, $rate){
        $companies = Company::get();

        foreach($companies as $company){
            Notification::create([
                'type' => Notification::TYPE_COUNTRIES_CUSTOMS_DUTIES_RATE_RAISED,
                'title' => 'Countries Customs Duties Rate Raised',
                'message' => "Countries customs duties rate raised for " . implode(', ', $countries) . " with " . $rate . "%.",
                'url' => route('company.purchases.index'),
                'user_id' => $company->user_id,
            ]);
        }
    }

    public static function createCountriesCustomsDutiesRateLoweredNotification($countries, $rate)
    {
        $companies = Company::get();

        foreach($companies as $company){
            Notification::create([
                'type' => Notification::TYPE_COUNTRIES_CUSTOMS_DUTIES_RATE_LOWERED,
                'title' => 'Countries Customs Duties Rate Lowered',
                'message' => "Countries customs duties rate lowered for " . implode(', ', $countries) . " with " . $rate . "%.",
                'url' => route('company.purchases.index'),
                'user_id' => $company->user_id,
            ]);
        }
    }

    public static function createOilPriceRaisedNotification($rate){
        $companies = Company::get();

        foreach($companies as $company){
            Notification::create([
                'type' => Notification::TYPE_OIL_PRICE_RAISED,
                'title' => 'Oil Price Raised',
                'message' => "Oil price raised with " . $rate . "%. This will affect the shipping costs of your products from suppliers and to customers.",
                'url' => route('company.purchases.index'),
                'user_id' => $company->user_id,
            ]);
        }
    }

    public static function createOilPriceLoweredNotification($rate)
    {
        $companies = Company::get();

        foreach($companies as $company){
            Notification::create([
                'type' => Notification::TYPE_OIL_PRICE_LOWERED,
                'title' => 'Oil Price Lowered',
                'message' => "Oil price lowered with " . $rate . "%. This will affect the shipping costs of your products from suppliers and to customers.",
                'url' => route('company.purchases.index'),
                'user_id' => $company->user_id,
            ]);
        }
    }

    public static function createSuezCanalClosedNotification($countries, $rate)
    {
        $companies = Company::get();

        foreach($companies as $company){
            Notification::create([
                'type' => Notification::TYPE_SUEZ_CANAL_CLOSED,
                'title' => 'Suez Canal Closed',
                'message' => "Suez canal closed for " . implode(', ', $countries) . ". This will affect the shipping costs and delivery time of your products from suppliers by " . $rate . "%.",
                'url' => route('company.purchases.index'),
                'user_id' => $company->user_id,
            ]);
        }
    }

    public static function createSuezCanalOpenedNotification($countries, $rate){
        $companies = Company::get();

        foreach($companies as $company){
            Notification::create([
                'type' => Notification::TYPE_SUEZ_CANAL_OPENED,
                'title' => 'Suez Canal Opened',
                'message' => "Suez canal opened for " . implode(', ', $countries) . ". This will affect the shipping costs and delivery time of your products from suppliers by " . $rate . "%.",
                'url' => route('company.purchases.index'),
                'user_id' => $company->user_id,
            ]);
        }
    }

    public static function createInventoryExpiredNotification($company, $product, $quantity){
        return Notification::create([
            'type' => Notification::TYPE_INVENTORY_EXPIRED,
            'title' => 'Inventory Expired',
            'message' => "Inventory expired for {$product->name} with quantity of {$quantity}.",
            'url' => route('company.inventory.index'),
            'user_id' => $company->user_id,
        ]);
    }

    public static function createInventoryDamagedNotification($company, $rate){
        return Notification::create([
            'type' => Notification::TYPE_INVENTORY_DAMAGED,
            'title' => 'Inventory Damaged',
            'message' => "Inventory damaged with {$rate}%. You will lose {$rate}% of your products in inventory.",
            'url' => route('company.inventory.index'),
            'user_id' => $company->user_id,
        ]);
    }

    public static function createSaleInitiatedNotification($company, $product, $numberOfSales){
        return Notification::create([
            'type' => Notification::TYPE_SALE_INITIATED,
            'title' => 'Sale Initiated',
            'message' => "{$numberOfSales} new sales for {$product->name}.",
            'url' => route('company.sales.index'),
            'user_id' => $company->user_id,
        ]);
    }

    public static function createSaleDeliveredNotification($sale){
        return Notification::create([
            'type' => Notification::TYPE_SALE_DELIVERED,
            'title' => 'Sale Delivered',
            'message' => "Sale delivered for {$sale->product->name} with quantity of {$sale->quantity}.",
            'url' => route('company.sales.index'),
            'user_id' => $sale->company->user_id,
        ]);
    }

    public static function createSaleCancelledNotification($sale){
        return Notification::create([
            'type' => Notification::TYPE_SALE_CANCELLED,
            'title' => 'Sale Cancelled',
            'message' => "Sale cancelled for {$sale->product->name} with quantity of {$sale->quantity}.",
            'url' => route('company.sales.index'),
            'user_id' => $sale->company->user_id,
        ]);
    }

    public static function createSaleDeliveryDelayedNotification($sale){
        return Notification::create([
            'type' => Notification::TYPE_SALE_DELIVERY_DELAYED,
            'title' => 'Sale Delivery Delayed',
            'message' => "Sale delivery delayed for {$sale->product->name} until " . $sale->real_delivered_at->format('Y-m-d H:i:s') . ".",
            'url' => route('company.sales.index'),
            'user_id' => $sale->company->user_id,
        ]);
    }

    public static function createInventoryCostsPaidNotification($company, $product, $quantity){
        return Notification::create([
            'type' => Notification::TYPE_INVENTORY_COSTS_PAID,
            'title' => 'Inventory Costs Paid',
            'message' => "Inventory costs paid for {$product->name} with quantity of {$quantity}.",
            'url' => route('company.inventory.index'),
            'user_id' => $company->user_id,
        ]);
    }

    public static function getUnreadCount()
    {
        return Notification::where('user_id', auth()->user()->id)->whereNull('read_at')->count();
    }

    /**
     * Mark all notifications as read
     */
    public static function markAllAsRead()
    {
        return Notification::where('user_id', auth()->user()->id)->whereNull('read_at')->update([
            'read_at' => now(),
        ]);
    }
} 