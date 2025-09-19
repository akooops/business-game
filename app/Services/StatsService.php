<?php

namespace App\Services;

use App\Models\Company;
use App\Models\Transaction;
use App\Models\Sale;
use App\Models\Purchase;
use App\Models\Employee;
use App\Models\Ad;
use App\Models\ProductionOrder;
use App\Models\CompanyProduct;
use App\Models\Loan;
use Carbon\Carbon;

class StatsService
{
    /**
     * Get comprehensive stats for a company (OPTIMIZED)
     */
    public static function getCompanyStats($company)
    {
        // Preload all related data in fewer queries
        $preloadedData = self::preloadCompanyData($company);
        
        return [
            'current' => [
                'funds' => self::getFundsStatsOptimized($company, $preloadedData),
                'loans' => self::getLoansStatsOptimized($company, $preloadedData),
                'marketing' => self::getMarketingStatsOptimized($company, $preloadedData),
                'production' => self::getProductionStatsOptimized($company, $preloadedData),
                'sales' => self::getSalesStatsOptimized($company, $preloadedData),
                'inventory' => self::getInventoryStatsOptimized($company, $preloadedData),
                'employees' => self::getEmployeeStatsOptimized($company, $preloadedData),
                'revenue' => self::getRevenueStatsOptimized($company, $preloadedData),
                'expenses' => self::getExpenseStatsOptimized($company, $preloadedData)
            ],
            'trends' => self::getTrendDataOptimized($company, 15) // Reduced from 30 to 15 days
        ];
    }

    /**
     * Preload all company data in optimized queries
     */
    private static function preloadCompanyData($company)
    {
        $today = Carbon::today();
        $thisMonth = Carbon::now()->startOfMonth();
        
        return [
            // All loans in one query
            'loans' => Loan::where('company_id', $company->id)->whereNull('paid_at')->get(),
            
            // All active ads in one query
            'active_ads' => Ad::where('company_id', $company->id)->where('status', Ad::STATUS_ACTIVE)->get(),
            
            // All active employees in one query
            'employees' => Employee::where('company_id', $company->id)->where('status', Employee::STATUS_ACTIVE)->get(),
            
            // All inventory in one query
            'inventory' => CompanyProduct::where('company_id', $company->id)->get(),
            
            // Production orders through company machines (optimized with join)
            'production_orders' => ProductionOrder::join('company_machines', 'production_orders.company_machine_id', '=', 'company_machines.id')
                ->where('company_machines.company_id', $company->id)
                ->select('production_orders.*')
                ->get(),
            
            // Today's transactions in one query
            'transactions_today' => Transaction::where('company_id', $company->id)
                ->whereDate('created_at', $today)
                ->get(),
            
            // This month's transactions in one query  
            'transactions_month' => Transaction::where('company_id', $company->id)
                ->where('created_at', '>=', $thisMonth)
                ->get(),
                
            // Sales data
            'sales_today' => Sale::where('company_id', $company->id)->whereDate('created_at', $today)->get(),
            'sales_month' => Sale::where('company_id', $company->id)->where('created_at', '>=', $thisMonth)->get(),
        ];
    }

    /**
     * Get current funds information
     */
    public static function getFundsStats($company)
    {
        return [
            'current_funds' => $company->funds,
            'previous_day_funds' => self::getPreviousDayFunds($company),
            'change_percentage' => self::calculateFundsChangePercentage($company)
        ];
    }

    /**
     * Get loans information
     */
    public static function getLoansStats($company)
    {
        $activeLoans = Loan::where('company_id', $company->id)
            ->whereNull('paid_at')
            ->get();

        return [
            'total_unpaid_loans' => $company->unpaid_loans,
            'active_loans_count' => $activeLoans->count(),
            'total_monthly_payments' => $activeLoans->sum('monthly_payment'),
            'average_interest_rate' => $activeLoans->avg('interest_rate') ?: 0
        ];
    }

    /**
     * Get marketing/advertising stats
     */
    public static function getMarketingStats($company)
    {
        $activeAds = Ad::where('company_id', $company->id)
            ->where('status', Ad::STATUS_ACTIVE)
            ->get();

        $marketingSpendToday = Transaction::where('company_id', $company->id)
            ->where('type', Transaction::TYPE_MARKETING)
            ->whereDate('created_at', Carbon::today())
            ->sum('amount');

        return [
            'active_ads_count' => $activeAds->count(),
            'active_ads_cost' => $activeAds->sum('price'),
            'marketing_spend_today' => $marketingSpendToday,
            'total_market_impact' => $activeAds->sum('market_impact_percentage')
        ];
    }

    /**
     * Get production stats
     */
    public static function getProductionStats($company)
    {
        // Get production orders through company machines relationship
        $activeProduction = ProductionOrder::whereHas('companyMachine', function($query) use ($company) {
            $query->where('company_id', $company->id);
        })
        ->where('status', ProductionOrder::STATUS_IN_PROGRESS)
        ->get();

        $completedToday = ProductionOrder::whereHas('companyMachine', function($query) use ($company) {
            $query->where('company_id', $company->id);
        })
        ->where('status', ProductionOrder::STATUS_COMPLETED)
        ->whereDate('completed_at', Carbon::today())
        ->count();

        return [
            'active_production_orders' => $activeProduction->count(),
            'total_producing_quantity' => $activeProduction->sum('quantity'),
            'completed_orders_today' => $completedToday,
            'average_production_progress' => $activeProduction->avg('producing_progress') ?: 0
        ];
    }

    /**
     * Get sales stats
     */
    public static function getSalesStats($company)
    {
        $salesToday = Sale::where('company_id', $company->id)
            ->whereDate('created_at', Carbon::today())
            ->get();

        $salesThisMonth = Sale::where('company_id', $company->id)
            ->whereMonth('created_at', Carbon::now()->month)
            ->whereYear('created_at', Carbon::now()->year)
            ->get();

        return [
            'sales_today_count' => $salesToday->count(),
            'sales_today_revenue' => $salesToday->sum('total_sale_price'),
            'sales_month_count' => $salesThisMonth->count(),
            'sales_month_revenue' => $salesThisMonth->sum('total_sale_price'),
            'average_sale_value' => $salesThisMonth->avg('total_sale_price') ?: 0
        ];
    }

    /**
     * Get inventory stats
     */
    public static function getInventoryStats($company)
    {
        $inventory = CompanyProduct::where('company_id', $company->id)->get();
        
        $totalValue = $inventory->sum(function($item) {
            return $item->available_stock * $item->sale_price;
        });

        return [
            'total_products' => $inventory->count(),
            'total_inventory_value' => $totalValue,
            'total_stock_quantity' => $inventory->sum('available_stock'),
            'out_of_stock_products' => $inventory->where('available_stock', '<=', 0)->count()
        ];
    }

    /**
     * Get employee stats
     */
    public static function getEmployeeStats($company)
    {
        $activeEmployees = Employee::where('company_id', $company->id)
            ->where('status', Employee::STATUS_ACTIVE)
            ->get();

        return [
            'total_employees' => $activeEmployees->count(),
            'total_monthly_salaries' => $activeEmployees->sum('salary_month'),
            'average_salary' => $activeEmployees->avg('salary_month') ?: 0,
            'average_mood' => $activeEmployees->avg('current_mood') ?: 0,
            'average_efficiency' => $activeEmployees->avg('efficiency_factor') ?: 0
        ];
    }

    /**
     * Get revenue stats (positive transactions)
     */
    public static function getRevenueStats($company)
    {
        $revenueTypes = [
            Transaction::TYPE_SALE_PAYMENT,
            Transaction::TYPE_MACHINE_SOLD,
            Transaction::TYPE_LOAN_RECEIVED
        ];

        $revenueToday = Transaction::where('company_id', $company->id)
            ->whereIn('type', $revenueTypes)
            ->whereDate('created_at', Carbon::today())
            ->sum('amount');

        $revenueThisMonth = Transaction::where('company_id', $company->id)
            ->whereIn('type', $revenueTypes)
            ->whereMonth('created_at', Carbon::now()->month)
            ->whereYear('created_at', Carbon::now()->year)
            ->sum('amount');

        return [
            'revenue_today' => $revenueToday,
            'revenue_this_month' => $revenueThisMonth,
            'average_daily_revenue' => $revenueThisMonth / Carbon::now()->day
        ];
    }

    /**
     * Get expense stats (negative transactions)
     */
    public static function getExpenseStats($company)
    {
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

        $expensesToday = Transaction::where('company_id', $company->id)
            ->whereIn('type', $expenseTypes)
            ->whereDate('created_at', Carbon::today())
            ->sum('amount');

        $expensesThisMonth = Transaction::where('company_id', $company->id)
            ->whereIn('type', $expenseTypes)
            ->whereMonth('created_at', Carbon::now()->month)
            ->whereYear('created_at', Carbon::now()->year)
            ->sum('amount');

        return [
            'expenses_today' => $expensesToday,
            'expenses_this_month' => $expensesThisMonth,
            'average_daily_expenses' => $expensesThisMonth / max(Carbon::now()->day, 1)
        ];
    }

    /**
     * OPTIMIZED STAT METHODS USING PRELOADED DATA
     */
    
    private static function getFundsStatsOptimized($company, $preloadedData)
    {
        return [
            'current_funds' => $company->funds,
            'previous_day_funds' => 0, // Simplified for performance
            'change_percentage' => 0   // Simplified for performance
        ];
    }

    private static function getLoansStatsOptimized($company, $preloadedData)
    {
        $loans = $preloadedData['loans'];
        
        return [
            'total_unpaid_loans' => $company->unpaid_loans,
            'active_loans_count' => $loans->count(),
            'total_monthly_payments' => $loans->sum('monthly_payment'),
            'average_interest_rate' => $loans->avg('interest_rate') ?: 0
        ];
    }

    private static function getMarketingStatsOptimized($company, $preloadedData)
    {
        $activeAds = $preloadedData['active_ads'];
        $marketingSpendToday = $preloadedData['transactions_today']
            ->where('type', Transaction::TYPE_MARKETING)
            ->sum('amount');

        return [
            'active_ads_count' => $activeAds->count(),
            'active_ads_cost' => $activeAds->sum('price'),
            'marketing_spend_today' => $marketingSpendToday,
            'total_market_impact' => $activeAds->sum('market_impact_percentage')
        ];
    }

    private static function getProductionStatsOptimized($company, $preloadedData)
    {
        $allProduction = $preloadedData['production_orders'];
        $activeProduction = $allProduction->where('status', ProductionOrder::STATUS_IN_PROGRESS);
        $completedToday = $allProduction->where('status', ProductionOrder::STATUS_COMPLETED)
            ->filter(function($order) {
                return $order->completed_at && $order->completed_at->isToday();
            });

        return [
            'active_production_orders' => $activeProduction->count(),
            'total_producing_quantity' => $activeProduction->sum('quantity'),
            'completed_orders_today' => $completedToday->count(),
            'average_production_progress' => 0 // Simplified for performance
        ];
    }

    private static function getSalesStatsOptimized($company, $preloadedData)
    {
        $salesToday = $preloadedData['sales_today'];
        $salesMonth = $preloadedData['sales_month'];

        return [
            'sales_today_count' => $salesToday->count(),
            'sales_today_revenue' => $salesToday->sum('total_sale_price'),
            'sales_month_count' => $salesMonth->count(),
            'sales_month_revenue' => $salesMonth->sum('total_sale_price'),
            'average_sale_value' => $salesMonth->avg('total_sale_price') ?: 0
        ];
    }

    private static function getInventoryStatsOptimized($company, $preloadedData)
    {
        $inventory = $preloadedData['inventory'];
        
        $totalValue = $inventory->sum(function($item) {
            return $item->available_stock * $item->sale_price;
        });

        return [
            'total_products' => $inventory->count(),
            'total_inventory_value' => $totalValue,
            'total_stock_quantity' => $inventory->sum('available_stock'),
            'out_of_stock_products' => $inventory->where('available_stock', '<=', 0)->count()
        ];
    }

    private static function getEmployeeStatsOptimized($company, $preloadedData)
    {
        $employees = $preloadedData['employees'];

        return [
            'total_employees' => $employees->count(),
            'total_monthly_salaries' => $employees->sum('salary_month'),
            'average_salary' => $employees->avg('salary_month') ?: 0,
            'average_mood' => $employees->avg('current_mood') ?: 0,
            'average_efficiency' => $employees->avg('efficiency_factor') ?: 0
        ];
    }

    private static function getRevenueStatsOptimized($company, $preloadedData)
    {
        $revenueTypes = [
            Transaction::TYPE_SALE_PAYMENT,
            Transaction::TYPE_MACHINE_SOLD,
            Transaction::TYPE_LOAN_RECEIVED
        ];

        $revenueToday = $preloadedData['transactions_today']
            ->whereIn('type', $revenueTypes)
            ->sum('amount');

        $revenueThisMonth = $preloadedData['transactions_month']
            ->whereIn('type', $revenueTypes)
            ->sum('amount');

        return [
            'revenue_today' => $revenueToday,
            'revenue_this_month' => $revenueThisMonth,
            'average_daily_revenue' => $revenueThisMonth / max(Carbon::now()->day, 1)
        ];
    }

    private static function getExpenseStatsOptimized($company, $preloadedData)
    {
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

        $expensesToday = $preloadedData['transactions_today']
            ->whereIn('type', $expenseTypes)
            ->sum('amount');

        $expensesThisMonth = $preloadedData['transactions_month']
            ->whereIn('type', $expenseTypes)
            ->sum('amount');

        return [
            'expenses_today' => $expensesToday,
            'expenses_this_month' => $expensesThisMonth,
            'average_daily_expenses' => $expensesThisMonth / max(Carbon::now()->day, 1)
        ];
    }

    /**
     * Get trend data for charts (OPTIMIZED - fewer days, simpler queries)
     */
    public static function getTrendDataOptimized($company, $days = 15)
    {
        $startDate = Carbon::now()->subDays($days);
        
        // Single query for all trend data
        $allTransactions = Transaction::where('company_id', $company->id)
            ->where('created_at', '>=', $startDate)
            ->select('type', 'amount', 'created_at')
            ->get()
            ->groupBy(function($transaction) {
                return $transaction->created_at->format('Y-m-d');
            });

        $allSales = Sale::where('company_id', $company->id)
            ->where('created_at', '>=', $startDate)
            ->select('quantity', 'sale_price', 'created_at')
            ->get()
            ->groupBy(function($sale) {
                return $sale->created_at->format('Y-m-d');
            });

        // Process data into chart format
        $revenueTrend = [];
        $expenseTrend = [];
        $salesTrend = [];
        $marketingTrend = [];

        for ($i = 0; $i < $days; $i++) {
            $date = Carbon::now()->subDays($days - 1 - $i)->format('Y-m-d');
            $dayTransactions = $allTransactions->get($date, collect());
            $daySales = $allSales->get($date, collect());
            
            $revenueTrend[] = [
                'date' => $date,
                'total' => $dayTransactions->whereIn('type', [
                    Transaction::TYPE_SALE_PAYMENT,
                    Transaction::TYPE_MACHINE_SOLD,
                    Transaction::TYPE_LOAN_RECEIVED
                ])->sum('amount')
            ];
            
            $expenseTrend[] = [
                'date' => $date,
                'total' => $dayTransactions->whereNotIn('type', [
                    Transaction::TYPE_SALE_PAYMENT,
                    Transaction::TYPE_MACHINE_SOLD,
                    Transaction::TYPE_LOAN_RECEIVED
                ])->sum('amount')
            ];
            
            $salesTrend[] = [
                'date' => $date,
                'count' => $daySales->count(),
                'revenue' => $daySales->sum(function($sale) {
                    return $sale->quantity * $sale->sale_price;
                })
            ];
            
            $marketingTrend[] = [
                'date' => $date,
                'total' => $dayTransactions->where('type', Transaction::TYPE_MARKETING)->sum('amount')
            ];
        }

        return [
            'revenue_trend' => $revenueTrend,
            'expense_trend' => $expenseTrend,
            'sales_trend' => $salesTrend,
            'marketing_trend' => $marketingTrend
        ];
    }

    /**
     * Get trend data for charts (last N days)
     */
    public static function getTrendData($company, $days = 30)
    {
        $startDate = Carbon::now()->subDays($days);
        
        // Revenue trend (daily)
        $revenueTrend = Transaction::where('company_id', $company->id)
            ->whereIn('type', [
                Transaction::TYPE_SALE_PAYMENT,
                Transaction::TYPE_MACHINE_SOLD,
                Transaction::TYPE_LOAN_RECEIVED
            ])
            ->where('created_at', '>=', $startDate)
            ->selectRaw('DATE(created_at) as date, SUM(amount) as total')
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        // Expense trend (daily)
        $expenseTrend = Transaction::where('company_id', $company->id)
            ->whereIn('type', [
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
            ])
            ->where('created_at', '>=', $startDate)
            ->selectRaw('DATE(created_at) as date, SUM(amount) as total')
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        // Sales trend
        $salesTrend = Sale::where('company_id', $company->id)
            ->where('created_at', '>=', $startDate)
            ->selectRaw('DATE(created_at) as date, COUNT(*) as count, SUM(quantity * sale_price) as revenue')
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        // Marketing spend trend
        $marketingTrend = Transaction::where('company_id', $company->id)
            ->where('type', Transaction::TYPE_MARKETING)
            ->where('created_at', '>=', $startDate)
            ->selectRaw('DATE(created_at) as date, SUM(amount) as total')
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        return [
            'revenue_trend' => $revenueTrend,
            'expense_trend' => $expenseTrend,
            'sales_trend' => $salesTrend,
            'marketing_trend' => $marketingTrend
        ];
    }

    /**
     * Get previous day funds for comparison
     */
    private static function getPreviousDayFunds($company)
    {
        $yesterday = Carbon::yesterday();
        
        // Get transactions from yesterday to calculate funds at end of day
        $yesterdayTransactions = Transaction::where('company_id', $company->id)
            ->whereDate('created_at', $yesterday)
            ->get();

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

        $todayRevenue = $yesterdayTransactions->whereIn('type', $revenueTypes)->sum('amount');
        $todayExpenses = $yesterdayTransactions->whereIn('type', $expenseTypes)->sum('amount');

        return $company->funds - $todayRevenue + $todayExpenses;
    }

    /**
     * Calculate funds change percentage from previous day
     */
    private static function calculateFundsChangePercentage($company)
    {
        $previousFunds = self::getPreviousDayFunds($company);
        $currentFunds = $company->funds;

        if ($previousFunds == 0) {
            return $currentFunds > 0 ? 100 : 0;
        }

        return round((($currentFunds - $previousFunds) / $previousFunds) * 100, 2);
    }
}
