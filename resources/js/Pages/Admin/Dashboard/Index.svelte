<script>
    import AdminLayout from '../../Layouts/AdminLayout.svelte';
    import LeaderboardTable from '../../Components/Leaderboard/LeaderboardTable.svelte';
    import LeaderboardStats from '../../Components/Leaderboard/LeaderboardStats.svelte';
    import { onMount, onDestroy } from 'svelte';

    // Props from server
    export let leaderboard = [];
    export let stats = null;

    // Define breadcrumbs for this page
    const breadcrumbs = [
        {
            title: 'Admin',
            url: route('admin.dashboard.index'),
            active: false
        },
        {
            title: 'Dashboard',
            url: route('admin.dashboard.index'),
            active: true
        }
    ];
    
    const pageTitle = 'Admin Dashboard';

    // Reactive variables
    let loading = false;
    let fetchInterval = null;
    let currentLeaderboard = leaderboard;
    let currentStats = stats;
    let withWeights = true;

    // Fetch leaderboard data (for real-time updates)
    async function fetchLeaderboard() {
        try {
            const response = await fetch(route('admin.dashboard.leaderboard') + `?weights=${withWeights}`, {
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                }
            });
            
            const data = await response.json();
            currentLeaderboard = data.leaderboard;
        } catch (error) {
            console.error('Error fetching leaderboard:', error);
        }
    }

    // Fetch stats data
    async function fetchStats() {
        try {
            const response = await fetch(route('admin.dashboard.stats'), {
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                }
            });
            
            const data = await response.json();
            currentStats = data.stats;
        } catch (error) {
            console.error('Error fetching stats:', error);
        }
    }

    // Toggle weights and refresh data
    function toggleWeights() {
        withWeights = !withWeights;
        fetchLeaderboard();
    }

    // Refresh all data
    async function refreshData() {
        loading = true;
        try {
            await Promise.all([fetchLeaderboard(), fetchStats()]);
        } finally {
            loading = false;
        }
    }

    onMount(() => {
        // Set up real-time updates every 30 seconds
        fetchInterval = setInterval(refreshData, 30000);
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
    <title>Business Game - {pageTitle}</title>
</svelte:head>

<AdminLayout {breadcrumbs} {pageTitle}>
    <!-- Container -->
    <div class="kt-container-fluid">
        <div class="grid gap-5 lg:gap-7.5">
            <!-- Dashboard Header -->
            <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4">
                <div class="flex flex-col gap-1">
                    <h1 class="text-2xl font-bold text-mono">Admin Dashboard</h1>
                    <p class="text-sm text-secondary-foreground">
                        Monitor company performance and game statistics
                    </p>
                </div>
                <div class="flex items-center gap-2">
                    <!-- Weights Toggle -->
                    <button 
                        class="kt-btn kt-btn-sm {withWeights ? 'kt-btn-primary' : 'kt-btn-secondary'}"
                        on:click={toggleWeights}
                    >
                        <i class="ki-filled ki-setting-3 text-xs me-1"></i>
                        {withWeights ? 'Weighted' : 'Simple'} Score
                    </button>
                    
                    <!-- Refresh Button -->
                    <button 
                        class="kt-btn kt-btn-sm kt-btn-light-primary"
                        on:click={refreshData}
                        disabled={loading}
                    >
                        <i class="ki-filled ki-arrows-circle {loading ? 'animate-spin' : ''} text-xs me-1"></i>
                        Refresh
                    </button>
                    
                    <!-- Live Status -->
                    <div class="kt-badge kt-badge-light-success kt-badge-sm">
                        <i class="ki-filled ki-pulse text-xs me-1"></i>
                        Live Data
                    </div>
                    <span class="text-xs text-secondary-foreground">
                        Updates every 30s
                    </span>
                </div>
            </div>

            <!-- Stats Overview -->
            <LeaderboardStats {stats} {loading} />

            <!-- Leaderboard Table -->
            <LeaderboardTable leaderboard={currentLeaderboard} {loading} />

            <!-- Scoring Formula Info -->
            <div class="kt-card">
                <div class="kt-card-header">
                    <h3 class="kt-card-title">
                        <i class="ki-filled ki-information-2 text-info me-2"></i>
                        Scoring Formula
                    </h3>
                </div>
                <div class="kt-card-content">
                    <div class="grid grid-cols-1 lg:grid-cols-2 gap-5">
                        <div>
                            <h4 class="text-sm font-semibold mb-2">Normalization</h4>
                            <ul class="text-sm text-secondary-foreground space-y-1">
                                <li>• <strong>Funds:</strong> company_funds / max_company_funds</li>
                                <li>• <strong>Loans:</strong> company_loans / max_company_loans</li>
                                <li>• <strong>Carbon:</strong> company_carbon / max_company_carbon</li>
                                <li>• <strong>Research:</strong> research_level / max_research_level</li>
                            </ul>
                        </div>
                        <div>
                            <h4 class="text-sm font-semibold mb-2">Final Score</h4>
                            {#if withWeights}
                                <p class="text-sm text-secondary-foreground">
                                    <strong>Weighted:</strong> (funds × 0.35) - (loans × 0.25) - (carbon × 0.20) + (research × 0.20)
                                </p>
                            {:else}
                                <p class="text-sm text-secondary-foreground">
                                    <strong>Simple:</strong> normalized_funds - normalized_loans - normalized_carbon + normalized_research
                                </p>
                            {/if}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- End of Container -->
</AdminLayout>