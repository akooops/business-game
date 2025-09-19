<script>
    import CompanyLayout from '../../Layouts/CompanyLayout.svelte';
    import StatsCard from '../../Components/Dashboard/StatsCard.svelte';
    import ApexLineChart from '../../Components/Dashboard/ApexLineChart.svelte';
    import ApexBarChart from '../../Components/Dashboard/ApexBarChart.svelte';
    import ApexDonutChart from '../../Components/Dashboard/ApexDonutChart.svelte';
    import { onMount, onDestroy } from 'svelte';

    // Props from server
    export let stats = {};
    export let trends = {};

    // Define breadcrumbs for this page
    const breadcrumbs = [
        {
            title: 'Dashboard',
            url: route('company.dashboard.index'),
            active: true
        }
    ];
    
    const pageTitle = 'Dashboard';

    // Reactive variables
    let loading = false;
    let fetchInterval = null;
    let currentStats = stats;
    let currentTrends = trends;

    // Format currency
    function formatCurrency(amount) {
        return new Intl.NumberFormat('en-US', {
            style: 'currency',
            currency: 'DZD',
            minimumFractionDigits: 0,
            maximumFractionDigits: 0
        }).format(amount || 0);
    }

    // Format number
    function formatNumber(number) {
        return new Intl.NumberFormat('en-US').format(number || 0);
    }

    // Format percentage
    function formatPercentage(percentage) {
        return new Intl.NumberFormat('en-US', {
            style: 'percent',
            minimumFractionDigits: 1,
            maximumFractionDigits: 1
        }).format((percentage || 0) / 100);
    }

    // Fetch current stats (for real-time updates)
    async function fetchCurrentStats() {
        try {
            const response = await fetch(route('company.dashboard.current'), {
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                }
            });
            
            const data = await response.json();
            currentStats = data.stats;
        } catch (error) {
            console.error('Error fetching current stats:', error);
        }
    }

    // Prepare chart data
    $: revenueTrendData = currentTrends.revenue_trend ? {
        series: [{
            name: 'Revenue',
            data: currentTrends.revenue_trend.map(item => item.total || 0)
        }],
        categories: currentTrends.revenue_trend.map(item => {
            return new Date(item.date).toLocaleDateString('en-US', { 
                month: 'short', 
                day: 'numeric' 
            });
        })
    } : { series: [], categories: [] };

    $: expenseTrendData = currentTrends.expense_trend ? {
        series: [{
            name: 'Expenses',
            data: currentTrends.expense_trend.map(item => item.total || 0)
        }],
        categories: currentTrends.expense_trend.map(item => {
            return new Date(item.date).toLocaleDateString('en-US', { 
                month: 'short', 
                day: 'numeric' 
            });
        })
    } : { series: [], categories: [] };

    $: revenueVsExpensesData = {
        series: [
            {
                name: 'Revenue',
                data: currentTrends.revenue_trend ? currentTrends.revenue_trend.map(item => item.total || 0) : []
            },
            {
                name: 'Expenses',
                data: currentTrends.expense_trend ? currentTrends.expense_trend.map(item => item.total || 0) : []
            }
        ],
        categories: currentTrends.revenue_trend ? currentTrends.revenue_trend.map(item => {
            return new Date(item.date).toLocaleDateString('en-US', { 
                month: 'short', 
                day: 'numeric' 
            });
        }) : []
    };

    $: salesTrendData = currentTrends.sales_trend ? {
        series: [
            {
                name: 'Sales Count',
                data: currentTrends.sales_trend.map(item => item.count || 0)
            },
            {
                name: 'Sales Revenue',
                data: currentTrends.sales_trend.map(item => item.revenue || 0)
            }
        ],
        categories: currentTrends.sales_trend.map(item => {
            return new Date(item.date).toLocaleDateString('en-US', { 
                month: 'short', 
                day: 'numeric' 
            });
        })
    } : { series: [], categories: [] };

    // Expense breakdown for donut chart
    $: expenseBreakdownData = currentStats.expenses ? {
        series: [
            currentStats.marketing?.active_ads_cost || 0,
            currentStats.employees?.total_monthly_salaries || 0,
            currentStats.loans?.total_monthly_payments || 0,
            currentStats.expenses?.expenses_today || 0
        ],
        labels: ['Marketing', 'Salaries', 'Loan Payments', 'Other Expenses']
    } : { series: [], labels: [] };

    onMount(() => {
        // Set up real-time updates every 60 seconds
        fetchInterval = setInterval(fetchCurrentStats, 60000);
    });

    onDestroy(() => {
        if (fetchInterval) {
            clearInterval(fetchInterval);
        }
    });

    // Flash message handling
    export let success;

    $: if (success) {
        showToast(success, 'success');
    }
</script>

<svelte:head>
    <title>Business Game - Dashboard</title>
</svelte:head>

<CompanyLayout {breadcrumbs} {pageTitle}>
    <!-- Container -->
    <div class="kt-container-fixed">
        <div class="grid gap-5 lg:gap-7.5">
            <!-- Dashboard Header -->
            <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4">
                <div class="flex flex-col gap-1">
                    <h1 class="text-2xl font-bold text-mono">Company Dashboard</h1>
                    <p class="text-sm text-secondary-foreground">
                        Overview of your company's performance and key metrics
                    </p>
                </div>
                <div class="flex items-center gap-2">
                    <div class="kt-badge kt-badge-light-success kt-badge-sm">
                        <i class="ki-filled ki-pulse text-xs me-1"></i>
                        Live Data
                    </div>
                    <span class="text-xs text-secondary-foreground">
                        Updates every day
                    </span>
                </div>
            </div>

            <!-- Main Stats Cards Grid -->
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5 lg:gap-7.5">
                <!-- Funds Card -->
                <StatsCard
                    title="Current Funds"
                    value={formatCurrency(currentStats.funds?.current_funds)}
                    subtitle="Available balance"
                    icon="ki-wallet"
                    iconColor="kt-badge-light-primary"
                    trend={currentStats.funds?.change_percentage ? {
                        value: currentStats.funds.change_percentage,
                        isPositive: currentStats.funds.change_percentage > 0
                    } : null}
                    {loading}
                />

                <!-- Loans Card -->
                <StatsCard
                    title="Total Loans"
                    value={formatCurrency(currentStats.loans?.total_unpaid_loans)}
                    subtitle="{formatNumber(currentStats.loans?.active_loans_count)} active loans"
                    icon="ki-bank"
                    iconColor="kt-badge-light-warning"
                    {loading}
                />

                <!-- Revenue Card -->
                <StatsCard
                    title="Revenue Today"
                    value={formatCurrency(currentStats.revenue?.revenue_today)}
                    subtitle="Daily income"
                    icon="ki-arrow-up"
                    iconColor="kt-badge-light-success"
                    {loading}
                />

                <!-- Expenses Card -->
                <StatsCard
                    title="Expenses Today"
                    value={formatCurrency(currentStats.expenses?.expenses_today)}
                    subtitle="Daily costs"
                    icon="ki-arrow-down"
                    iconColor="kt-badge-light-danger"
                    {loading}
                />
            </div>

            <!-- Secondary Stats Grid -->
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5 lg:gap-7.5">
                <!-- Employees Card -->
                <StatsCard
                    title="Employees"
                    value={formatNumber(currentStats.employees?.total_employees)}
                    subtitle={formatCurrency(currentStats.employees?.total_monthly_salaries) + " monthly"}
                    icon="ki-people"
                    iconColor="kt-badge-light-info"
                    {loading}
                />

                <!-- Production Card -->
                <StatsCard
                    title="Active Production"
                    value={formatNumber(currentStats.production?.active_production_orders)}
                    subtitle="{formatNumber(currentStats.production?.total_producing_quantity)} units"
                    icon="ki-setting-3"
                    iconColor="kt-badge-light-secondary"
                    {loading}
                />

                <!-- Inventory Card -->
                <StatsCard
                    title="Inventory Value"
                    value={formatCurrency(currentStats.inventory?.total_inventory_value)}
                    subtitle="{formatNumber(currentStats.inventory?.total_products)} products"
                    icon="ki-package"
                    iconColor="kt-badge-light-primary"
                    {loading}
                />

                <!-- Marketing Card -->
                <StatsCard
                    title="Active Ads"
                    value={formatNumber(currentStats.marketing?.active_ads_count)}
                    subtitle={formatCurrency(currentStats.marketing?.active_ads_cost) + " cost"}
                    icon="ki-chart-pie-simple"
                    iconColor="kt-badge-light-warning"
                    {loading}
                />
            </div>

            <!-- Charts Grid -->
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-5 lg:gap-7.5">
                <!-- Revenue vs Expenses Trend -->
                <ApexLineChart
                    title="Revenue vs Expenses Trend"
                    subtitle="Last 30 days performance"
                    series={revenueVsExpensesData.series}
                    categories={revenueVsExpensesData.categories}
                    colors={['#10b981', '#ef4444']}
                    {loading}
                />

                <!-- Sales Performance -->
                <ApexBarChart
                    title="Sales Performance"
                    subtitle="Daily sales count and revenue"
                    series={salesTrendData.series}
                    categories={salesTrendData.categories}
                    colors={['#3b82f6', '#10b981']}
                    {loading}
                />
            </div>

            <!-- Bottom Charts Grid -->
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-5 lg:gap-7.5">
                <!-- Revenue Trend -->
                <ApexLineChart
                    title="Revenue Trend"
                    subtitle="Daily revenue over time"
                    series={revenueTrendData.series}
                    categories={revenueTrendData.categories}
                    colors={['#10b981']}
                    height={300}
                    {loading}
                />

                <!-- Expenses Trend -->
                <ApexLineChart
                    title="Expenses Trend"
                    subtitle="Daily expenses over time"
                    series={expenseTrendData.series}
                    categories={expenseTrendData.categories}
                    colors={['#ef4444']}
                    height={300}
                    {loading}
                />

                <!-- Expense Breakdown -->
                <ApexDonutChart
                    title="Expense Breakdown"
                    subtitle="Current expense distribution"
                    series={expenseBreakdownData.series}
                    labels={expenseBreakdownData.labels}
                    colors={['#f59e0b', '#8b5cf6', '#ef4444', '#6b7280']}
                    height={300}
                    {loading}
                />
            </div>

            <!-- Quick Stats Summary -->
            <div class="kt-card">
                <div class="kt-card-header">
                    <h3 class="kt-card-title">Quick Summary</h3>
                </div>
                <div class="kt-card-content">
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-5">
                        <div class="flex flex-col gap-2">
                            <span class="text-xs font-medium text-secondary-foreground">Monthly Revenue</span>
                            <span class="text-lg font-bold text-success">
                                {formatCurrency(currentStats.revenue?.revenue_this_month)}
                            </span>
                        </div>
                        <div class="flex flex-col gap-2">
                            <span class="text-xs font-medium text-secondary-foreground">Monthly Expenses</span>
                            <span class="text-lg font-bold text-danger">
                                {formatCurrency(currentStats.expenses?.expenses_this_month)}
                            </span>
                        </div>
                        <div class="flex flex-col gap-2">
                            <span class="text-xs font-medium text-secondary-foreground">Net Profit</span>
                            <span class="text-lg font-bold {(currentStats.revenue?.revenue_this_month || 0) - (currentStats.expenses?.expenses_this_month || 0) >= 0 ? 'text-success' : 'text-danger'}">
                                {formatCurrency((currentStats.revenue?.revenue_this_month || 0) - (currentStats.expenses?.expenses_this_month || 0))}
                            </span>
                        </div>
                        <div class="flex flex-col gap-2">
                            <span class="text-xs font-medium text-secondary-foreground">Average Employee Efficiency</span>
                            <span class="text-lg font-bold text-info">
                                {formatPercentage(currentStats.employees?.average_efficiency)}
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- End of Container -->
</CompanyLayout>
