<?php

namespace App\Services;

use App\Models\Company;
use App\Models\Transaction;
use App\Models\Sale;
use App\Models\Purchase;
use App\Models\Employee;
use App\Models\ProductionOrder;
use App\Services\SettingsService;
use App\Services\LeaderboardService;
use Carbon\Carbon;

class StatsService
{
    /**
     * Get comprehensive stats for a company
     */
    public static function getCompanyStats($company)
    {
        $currentDate = SettingsService::getCurrentTimestamp();
        $thisMonth = $currentDate->copy()->startOfMonth();
        
        // Basic company stats
        $basicStats = [
            'funds' => $company->funds,
            'unpaid_loans' => $company->unpaid_loans,
            'carbon_footprint' => $company->carbon_footprint,
            'research_level' => $company->research_level
        ];

        // Get this month's transactions
        $monthlyTransactions = Transaction::where('company_id', $company->id)
            ->where('transaction_at', '>=', $thisMonth)
            ->get();

        // Calculate revenue and expenses
        $revenueTypes = [
            Transaction::TYPE_SALE_PAYMENT,
            Transaction::TYPE_MACHINE_SOLD,
            Transaction::TYPE_LOAN_RECEIVED
        ];

        $expenseTypes = [
            Transaction::TYPE_TECHNOLOGY,
            Transaction::TYPE_PURCHASE,
            Transaction::TYPE_INVENTORY,
            Transaction::TYPE_SALE_SHIPPING,
            Transaction::TYPE_EMPLOYEE_RECRUITMENT,
            Transaction::TYPE_EMPLOYEE_SALARY,
            Transaction::TYPE_MACHINE_SETUP,
            Transaction::TYPE_MACHINE_OPERATIONS,
            Transaction::TYPE_MAINTENANCE,
            Transaction::TYPE_MARKETING,
            Transaction::TYPE_LOAN_PAYMENT
        ];

        $revenueThisMonth = $monthlyTransactions->whereIn('type', $revenueTypes)->sum('amount');
        $expensesThisMonth = $monthlyTransactions->whereIn('type', $expenseTypes)->sum('amount');

        // Current active counts
        $activeEmployees = Employee::where('company_id', $company->id)
            ->where('status', Employee::STATUS_ACTIVE)
            ->count();

        $activeProductionOrders = ProductionOrder::whereHas('companyMachine', function($query) use ($company) {
            $query->where('company_id', $company->id);
        })->where('status', ProductionOrder::STATUS_IN_PROGRESS)->count();

        // Monthly trends (30 days)
        $trends = self::getMonthlyTrends($company, 30);

        // Revenue and expense breakdowns
        $revenueBreakdown = self::getRevenueBreakdown($monthlyTransactions, $revenueTypes);
        $expenseBreakdown = self::getExpenseBreakdown($monthlyTransactions, $expenseTypes);

        // Get leaderboard
        $leaderboard = LeaderboardService::getLeaderboard();

        return [
            'basic' => $basicStats,
            'financial' => [
                'revenue_this_month' => $revenueThisMonth,
                'expenses_this_month' => $expensesThisMonth,
            ],
            'operations' => [
                'active_employees' => $activeEmployees,
                'active_production_orders' => $activeProductionOrders,
            ],
            'trends' => $trends,
            'breakdowns' => [
                'revenue' => $revenueBreakdown,
                'expenses' => $expenseBreakdown
            ],
            'leaderboard' => $leaderboard
        ];
    }

    /**
     * Get monthly trends for revenue, expenses, sales, and purchases
     */
    private static function getMonthlyTrends($company, $days = 30)
    {
        $currentDate = SettingsService::getCurrentTimestamp();
        $startDate = $currentDate->copy()->subDays($days);
        
        $revenueTypes = [
            Transaction::TYPE_SALE_PAYMENT,
            Transaction::TYPE_MACHINE_SOLD,
            Transaction::TYPE_LOAN_RECEIVED
        ];

        $expenseTypes = [
            Transaction::TYPE_TECHNOLOGY,
            Transaction::TYPE_PURCHASE,
            Transaction::TYPE_INVENTORY,
            Transaction::TYPE_SALE_SHIPPING,
            Transaction::TYPE_EMPLOYEE_RECRUITMENT,
            Transaction::TYPE_EMPLOYEE_SALARY,
            Transaction::TYPE_MACHINE_SETUP,
            Transaction::TYPE_MACHINE_OPERATIONS,
            Transaction::TYPE_MAINTENANCE,
            Transaction::TYPE_MARKETING,
            Transaction::TYPE_LOAN_PAYMENT
        ];

        // Get all transactions for the period
        $allTransactions = Transaction::where('company_id', $company->id)
            ->where('transaction_at', '>=', $startDate)
            ->select('type', 'amount', 'transaction_at')
            ->get()
            ->groupBy(function($transaction) {
                return $transaction->transaction_at->format('Y-m-d');
            });

        // Get sales for the period
        $allSales = Sale::where('company_id', $company->id)
            ->where('initiated_at', '>=', $startDate)
            ->get()
            ->groupBy(function($sale) {
                return $sale->initiated_at->format('Y-m-d');
            });

        // Get purchases for the period
        $allPurchases = Purchase::where('company_id', $company->id)
            ->where('ordered_at', '>=', $startDate)
            ->get()
            ->groupBy(function($purchase) {
                return $purchase->ordered_at->format('Y-m-d');
            });

        $revenueTrend = [];
        $expenseTrend = [];
        $salesTrend = [];
        $purchaseTrend = [];

        for ($i = 0; $i < $days; $i++) {
            $date = $currentDate->copy()->subDays($days - 1 - $i)->format('Y-m-d');
            $dayTransactions = $allTransactions->get($date, collect());
            $daySales = $allSales->get($date, collect());
            $dayPurchases = $allPurchases->get($date, collect());
            
            $revenueTrend[] = [
                'date' => $date,
                'total' => $dayTransactions->whereIn('type', $revenueTypes)->sum('amount')
            ];
            
            $expenseTrend[] = [
                'date' => $date,
                'total' => $dayTransactions->whereIn('type', $expenseTypes)->sum('amount')
            ];
            
            $salesTrend[] = [
                'date' => $date,
                'count' => $daySales->count(),
            ];
            
            $purchaseTrend[] = [
                'date' => $date,
                'count' => $dayPurchases->count(),
                'total' => $dayPurchases->sum('total_cost')
            ];
        }

        return [
            'revenue' => $revenueTrend,
            'expenses' => $expenseTrend,
            'sales' => $salesTrend,
            'purchases' => $purchaseTrend
        ];
    }

    /**
     * Get revenue breakdown by type
     */
    private static function getRevenueBreakdown($transactions, $revenueTypes)
    {
        $revenueTransactions = $transactions->whereIn('type', $revenueTypes);
        
        return [
            'sale_payments' => $revenueTransactions->where('type', Transaction::TYPE_SALE_PAYMENT)->sum('amount'),
            'machine_sales' => $revenueTransactions->where('type', Transaction::TYPE_MACHINE_SOLD)->sum('amount'),
            'loans_received' => $revenueTransactions->where('type', Transaction::TYPE_LOAN_RECEIVED)->sum('amount')
        ];
    }

    /**
     * Get expense breakdown by type
     */
    private static function getExpenseBreakdown($transactions, $expenseTypes)
    {
        $expenseTransactions = $transactions->whereIn('type', $expenseTypes);
        
        return [
            'technology' => $expenseTransactions->where('type', Transaction::TYPE_TECHNOLOGY)->sum('amount'),
            'purchases' => $expenseTransactions->where('type', Transaction::TYPE_PURCHASE)->sum('amount'),
            'inventory' => $expenseTransactions->where('type', Transaction::TYPE_INVENTORY)->sum('amount'),
            'shipping' => $expenseTransactions->where('type', Transaction::TYPE_SALE_SHIPPING)->sum('amount'),
            'employee_recruitment' => $expenseTransactions->where('type', Transaction::TYPE_EMPLOYEE_RECRUITMENT)->sum('amount'),
            'employee_salaries' => $expenseTransactions->where('type', Transaction::TYPE_EMPLOYEE_SALARY)->sum('amount'),
            'machine_setup' => $expenseTransactions->where('type', Transaction::TYPE_MACHINE_SETUP)->sum('amount'),
            'machine_operations' => $expenseTransactions->where('type', Transaction::TYPE_MACHINE_OPERATIONS)->sum('amount'),
            'maintenance' => $expenseTransactions->where('type', Transaction::TYPE_MAINTENANCE)->sum('amount'),
            'marketing' => $expenseTransactions->where('type', Transaction::TYPE_MARKETING)->sum('amount'),
            'loan_payments' => $expenseTransactions->where('type', Transaction::TYPE_LOAN_PAYMENT)->sum('amount')
        ];
    }
}