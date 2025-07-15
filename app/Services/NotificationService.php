<?php

namespace App\Services;

use App\Models\Notification;

class NotificationService
{
    public static function createFinanceFundsChangedNotification($company, $amount)
    {
        return Notification::create([
            'type' => Notification::TYPE_FINANCE_FUNDS_CHANGED,
            'title' => 'Funds Changed',
            'message' => "Funds deducted by DZD " . $amount . ". New balance: DZD " . $company->funds,
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