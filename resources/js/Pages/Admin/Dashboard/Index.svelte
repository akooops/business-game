<script>
    import AdminLayout from '../../Layouts/AdminLayout.svelte';
    import LeaderboardTable from '../../Components/Leaderboard/LeaderboardTable.svelte';
    import { onMount, onDestroy } from 'svelte';

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

    // Inertia props for initial data
    export let companies = [];
    let loading = false;
    let fetchInterval = null;

    // Background refresh leaderboard data (optional)
    async function fetchLeaderboard() {
        loading = true;
        try {
            const response = await fetch(route('admin.dashboard.index'), {
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                }
            });
            const data = await response.json();
            companies = data.companies;
        } catch (error) {
            console.error('Error fetching leaderboard:', error);
        } finally {
            loading = false;
        }
    }

    // Only set up interval for background updates, not initial fetch
    onMount(() => {
        fetchInterval = setInterval(fetchLeaderboard, 30000);
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
    <div class="kt-container-fixed">
        <div class="grid gap-5 lg:gap-7.5">
            <!-- Dashboard Header -->
            <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4">
                <div class="flex flex-col gap-1">
                    <h1 class="text-2xl font-bold text-mono">Admin Dashboard</h1>
                    <p class="text-sm text-secondary-foreground">
                        Monitor company performance and game statistics
                    </p>
                </div>
            </div>

            <!-- Leaderboard Table -->
            <LeaderboardTable companies={companies} {loading} />
        </div>
    </div>
    <!-- End of Container -->
</AdminLayout>