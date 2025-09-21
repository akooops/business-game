<script>
    import CompanyLayout from '../../Layouts/CompanyLayout.svelte';
    import StatsCard from '../../Components/Dashboard/StatsCard.svelte';
    import ApexLineChart from '../../Components/Dashboard/ApexLineChart.svelte';
    import ApexDonutChart from '../../Components/Dashboard/ApexDonutChart.svelte';
    import LeaderboardTable from '../../Components/Leaderboard/LeaderboardTable.svelte';
    import { onMount, onDestroy } from 'svelte';

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
    let loading = true;
    let fetchInterval = null;
    let stats = {
        basic: {},
        financial: {},
        operations: {},
        trends: { revenue: [], expenses: [], sales: [], purchases: [] },
        breakdowns: { revenue: {}, expenses: {} },
        leaderboard: []
    };

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

    // Fetch dashboard data
    async function fetchDashboardData() {
        try {
            const response = await fetch(route('company.dashboard.index'), {
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                }
            });
            
            const data = await response.json();
            stats = data.stats;
        } catch (error) {
            console.error('Error fetching dashboard data:', error);
        } finally {
            loading = false;
        }
    }

    // Prepare chart data
    $: revenueTrendData = stats.trends?.revenue ? {
        series: [{
            name: 'Revenue',
            data: stats.trends.revenue.map(item => item.total || 0)
        }],
        categories: stats.trends.revenue.map(item => {
            return new Date(item.date).toLocaleDateString('en-US', { 
                month: 'short', 
                day: 'numeric' 
            });
        })
    } : { series: [], categories: [] };

    $: expenseTrendData = stats.trends?.expenses ? {
        series: [{
            name: 'Expenses',
            data: stats.trends.expenses.map(item => item.total || 0)
        }],
        categories: stats.trends.expenses.map(item => {
            return new Date(item.date).toLocaleDateString('en-US', { 
                month: 'short', 
                day: 'numeric' 
            });
        })
    } : { series: [], categories: [] };

    $: salesTrendData = stats.trends?.sales ? {
        series: [{
            name: 'Sales Count',
            data: stats.trends.sales.map(item => item.count || 0)
        }],
        categories: stats.trends.sales.map(item => {
            return new Date(item.date).toLocaleDateString('en-US', { 
                month: 'short', 
                day: 'numeric' 
            });
        })
    } : { series: [], categories: [] };

    $: purchaseTrendData = stats.trends?.purchases ? {
        series: [{
            name: 'Purchase Amount',
            data: stats.trends.purchases.map(item => item.count || 0)
        }],
        categories: stats.trends.purchases.map(item => {
            return new Date(item.date).toLocaleDateString('en-US', { 
                month: 'short', 
                day: 'numeric' 
            });
        })
    } : { series: [], categories: [] };

    // Revenue breakdown for donut chart
    $: revenueBreakdownData = stats.breakdowns?.revenue ? {
        series: [
            stats.breakdowns.revenue.sale_payments || 0,
            stats.breakdowns.revenue.machine_sales || 0,
            stats.breakdowns.revenue.loans_received || 0
        ],
        labels: ['Sale Payments', 'Machine Sales', 'Loans Received']
    } : { series: [], labels: [] };

    // Expense breakdown for donut chart
    $: expenseBreakdownData = stats.breakdowns?.expenses ? {
        series: [
            stats.breakdowns.expenses.employee_salaries || 0,
            stats.breakdowns.expenses.marketing || 0,
            stats.breakdowns.expenses.purchases || 0,
            stats.breakdowns.expenses.machine_operations || 0,
            stats.breakdowns.expenses.maintenance || 0,
            (stats.breakdowns.expenses.technology || 0) + 
            (stats.breakdowns.expenses.inventory || 0) + 
            (stats.breakdowns.expenses.shipping || 0) + 
            (stats.breakdowns.expenses.employee_recruitment || 0) + 
            (stats.breakdowns.expenses.machine_setup || 0) + 
            (stats.breakdowns.expenses.loan_payments || 0)
        ],
        labels: ['Employee Salaries', 'Marketing', 'Purchases', 'Machine Operations', 'Maintenance', 'Other']
    } : { series: [], labels: [] };

    onMount(() => {
        fetchDashboardData();
        // Set up real-time updates every 60 seconds
        fetchInterval = setInterval(fetchDashboardData, 60000);
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
            </div>

            <!-- Basic Stats Cards Grid -->
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5 lg:gap-7.5">
                <!-- Funds Card -->
                <StatsCard
                    title="Current Funds"
                    value={formatCurrency(stats.basic?.funds)}
                    subtitle="Available balance"
                    icon="ki-wallet"
                    iconColor="kt-badge-light-primary"
                    {loading}
                />

                <!-- Unpaid Loans Card -->
                <StatsCard
                    title="Unpaid Loans"
                    value={formatCurrency(stats.basic?.unpaid_loans)}
                    subtitle="Outstanding debt"
                    icon="ki-bank"
                    iconColor="kt-badge-light-warning"
                    {loading}
                />

                <!-- Carbon Footprint Card -->
                <StatsCard
                    title="Carbon Footprint"
                    value={formatNumber(stats.basic?.carbon_footprint)}
                    subtitle="KG CO₂"
                    icon="ki-delivery-3"
                    iconColor="kt-badge-light-danger"
                    {loading}
                />

                <!-- Research Level Card -->
                <StatsCard
                    title="Research Level"
                    value={formatNumber(stats.basic?.research_level)}
                    subtitle="current level"
                    icon="ki-flask"
                    iconColor="kt-badge-light-info"
                    {loading}
                />
            </div>

            <!-- Financial Stats Grid -->
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5 lg:gap-7.5">
                <!-- Revenue Card -->
                <StatsCard
                    title="Revenue This Month"
                    value={formatCurrency(stats.financial?.revenue_this_month)}
                    subtitle="Monthly income"
                    icon="ki-arrow-up"
                    iconColor="kt-badge-light-success"
                    {loading}
                />

                <!-- Expenses Card -->
                <StatsCard
                    title="Expenses This Month"
                    value={formatCurrency(stats.financial?.expenses_this_month)}
                    subtitle="Monthly costs"
                    icon="ki-arrow-down"
                    iconColor="kt-badge-light-danger"
                    {loading}
                />
            </div>

            <!-- Operations Stats Grid -->
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5 lg:gap-7.5">
                <!-- Employees Card -->
                <StatsCard
                    title="Active Employees"
                    value={formatNumber(stats.operations?.active_employees)}
                    subtitle="currently working"
                    icon="ki-people"
                    iconColor="kt-badge-light-info"
                    {loading}
                />

                <!-- Production Card -->
                <StatsCard
                    title="Active Production"
                    value={formatNumber(stats.operations?.active_production_orders)}
                    subtitle="orders in progress"
                    icon="ki-setting-3"
                    iconColor="kt-badge-light-secondary"
                    {loading}
                />
            </div>

            <!-- Trend Charts Grid -->
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-5 lg:gap-7.5">
                <!-- Revenue Trend -->
                <ApexLineChart
                    title="Revenue Trend"
                    subtitle="Daily revenue over the last 30 days"
                    series={revenueTrendData.series}
                    categories={revenueTrendData.categories}
                    colors={['#10b981']}
                    {loading}
                />

                <!-- Expenses Trend -->
                <ApexLineChart
                    title="Expenses Trend"
                    subtitle="Daily expenses over the last 30 days"
                    series={expenseTrendData.series}
                    categories={expenseTrendData.categories}
                    colors={['#ef4444']}
                    {loading}
                />
            </div>

            <!-- Sales and Purchase Trends -->
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-5 lg:gap-7.5">
                <!-- Sales Trend -->
                <ApexLineChart
                    title="Sales Trend"
                    subtitle="Daily sales count over the last 30 days"
                    series={salesTrendData.series}
                    categories={salesTrendData.categories}
                    colors={['#3b82f6']}
                    currency={null}
                    {loading}
                />

                <!-- Purchase Trend -->
                <ApexLineChart
                    title="Purchase Trend"
                    subtitle="Daily purchase amounts over the last 30 days"
                    series={purchaseTrendData.series}
                    categories={purchaseTrendData.categories}
                    colors={['#f59e0b']}
                    currency={null}
                    {loading}
                />
            </div>

            <!-- Breakdown Pie Charts -->
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-5 lg:gap-7.5">
                <!-- Revenue Breakdown -->
                <ApexDonutChart
                    title="Revenue Structure"
                    subtitle="Revenue sources this month"
                    series={revenueBreakdownData.series}
                    labels={revenueBreakdownData.labels}
                    colors={['#10b981', '#3b82f6', '#f59e0b']}
                    {loading}
                />

                <!-- Expense Breakdown -->
                <ApexDonutChart
                    title="Expense Structure"
                    subtitle="Expense categories this month"
                    series={expenseBreakdownData.series}
                    labels={expenseBreakdownData.labels}
                    colors={['#8b5cf6', '#ef4444', '#f59e0b', '#6b7280', '#14b8a6', '#ec4899']}
                    {loading}
                />
            </div>

            <LeaderboardTable companies={stats.leaderboard || []} {loading} />

        </div>
    </div>
    <!-- End of Container -->
</CompanyLayout>