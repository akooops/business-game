<script>
    import CompanyLayout from '../../Layouts/CompanyLayout.svelte';
    import Pagination from '../../Components/Pagination.svelte';
    import { onMount, onDestroy, tick } from 'svelte';
    import { page } from '@inertiajs/svelte'

    // Define breadcrumbs for this page
    const breadcrumbs = [
        {
            title: 'Marketing',
            url: route('company.ads.index'),
            active: false
        },
        {
            title: 'Ads',
            url: route('company.ads.index'),
            active: true
        }
    ];
    
    const pageTitle = 'Ad Campaigns';

    // Reactive variables
    let ads = [];
    let loading = true;
    let fetchInterval = null;

    let pagination = {};
    let perPage = 10;
    let currentPage = 1;

    // Fetch ads data
    async function fetchAds() {
        if(ads.length == 0) loading = true;
        
        try {
            const params = new URLSearchParams({
                page: currentPage,
                perPage: perPage,
            });

            const response = await fetch(route('company.ads.index') + '?' + params.toString(), {
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                }
            });
            
            const data = await response.json();
            ads = data.ads;
            pagination = data.pagination;
            
            // Wait for DOM to update, then initialize menus
            await tick();
            if (window.KTMenu) {
                window.KTMenu.init();
            }
        } catch (error) {
            console.error('Error fetching ads:', error);
        } finally {
            loading = false;
        }
    }

    // Handle pagination
    function goToPage(page) {
        if (page && page !== currentPage) {
            currentPage = page;
            loading = true;
            fetchAds();
        }
    }

    // Handle per page change
    function handlePerPageChange(newPerPage) {
        perPage = newPerPage;
        currentPage = 1;
        loading = true;
        fetchAds();
    }

    // Calculate total duration
    function getTotalDuration(ad) {
        return ad.duration_days;
    }

    // Calculate completion date
    function getCompletionDate(ad) {
        const startDate = new Date(ad.started_at);
        const completionDate = new Date(startDate);
        completionDate.setDate(startDate.getDate() + ad.duration_days);
        return completionDate;
    }

    // Get status color
    function getStatusColor(status) {
        switch (status) {
            case 'active':
                return 'bg-green-100 text-green-800';
            case 'completed':
                return 'bg-gray-100 text-gray-800';
            default:
                return 'bg-gray-100 text-gray-800';
        }
    }

    // Get status text
    function getStatusText(status) {
        switch (status) {
            case 'active':
                return 'Active';
            case 'completed':
                return 'Completed';
            default:
                return 'Unknown';
        }
    }
    
    onMount(() => {
        fetchAds();
        fetchInterval = setInterval(() => {
            fetchAds();
        }, 60000);
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
    <title>Business game - {pageTitle}</title>
</svelte:head>

<CompanyLayout {breadcrumbs} {pageTitle}>
    <!-- Container -->
    <div class="kt-container-fixed">
        <div class="grid gap-5 lg:gap-7.5">
            <!-- Ads Header -->
            <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4">
                <div class="flex flex-col gap-1">
                    <h1 class="text-2xl font-bold text-mono">Ad Campaigns</h1>
                    <p class="text-sm text-secondary-foreground">
                        Manage your active and completed advertising campaigns
                    </p>
                </div>                      
            </div>

            <!-- Ads Table -->
            <div class="kt-card">
                <div class="kt-card-content p-0">
                    {#if loading}
                        <!-- Loading skeleton -->
                        <div class="p-6">
                            <div class="space-y-4">
                                {#each Array(5) as _, i}
                                    <div class="kt-card animate-pulse">
                                        <div class="kt-card-content p-4">
                                            <div class="flex items-center justify-between">
                                                <div class="flex items-center gap-4">
                                                    <div class="kt-skeleton w-12 h-12 rounded-full"></div>
                                                    <div class="space-y-2">
                                                        <div class="kt-skeleton h-4 w-32"></div>
                                                        <div class="kt-skeleton h-3 w-24"></div>
                                                    </div>
                                                </div>
                                                <div class="flex items-center gap-4">
                                                    <div class="kt-skeleton h-6 w-16"></div>
                                                    <div class="kt-skeleton h-6 w-20"></div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                {/each}
                            </div>
                        </div>
                    {:else if ads.length === 0}
                        <!-- Empty state -->
                        <div class="p-10">
                            <div class="flex flex-col items-center justify-center text-center">
                                <div class="mb-4">
                                    <i class="ki-filled ki-megaphone text-4xl text-muted-foreground"></i>
                                </div>
                                <h3 class="text-lg font-semibold text-mono mb-2">No ad campaigns found</h3>
                                <p class="text-sm text-secondary-foreground mb-4">
                                    You haven't created any advertising campaigns yet.
                                </p>
                                <a href={route('company.advertisers.index')} class="kt-btn kt-btn-primary">
                                    <i class="ki-filled ki-plus text-sm"></i>
                                    Create Your First Ad
                                </a>
                            </div>
                        </div>
                    {:else}
                        <!-- Ads List -->
                        <div class="p-6">
                            <div class="space-y-4">
                                {#each ads as ad}
                                    <div class="kt-card">
                                        <div class="kt-card-content p-4">
                                            <div class="flex items-center justify-between">
                                                <div class="flex items-center gap-4">
                                                    <!-- Advertiser Logo -->
                                                    <div class="flex-shrink-0">
                                                        {#if ad.advertiser.logo_url}
                                                            <img 
                                                                src={ad.advertiser.logo_url} 
                                                                alt={ad.advertiser.name}
                                                                class="w-12 h-12 rounded-full object-cover"
                                                            />
                                                        {:else}
                                                            <div class="w-12 h-12 rounded-full bg-accent/50 flex items-center justify-center">
                                                                <i class="ki-filled ki-megaphone text-lg text-muted-foreground"></i>
                                                            </div>
                                                        {/if}
                                                    </div>
                                                    
                                                    <!-- Ad Details -->
                                                    <div class="flex-1">
                                                        <div class="flex items-center gap-2 mb-1">
                                                            <h3 class="font-semibold text-mono">{ad.advertiser.name}</h3>
                                                            <span class="text-xs {getStatusColor(ad.status)} px-2 py-1 rounded-full">
                                                                {getStatusText(ad.status)}
                                                            </span>
                                                        </div>
                                                        <p class="text-sm text-secondary-foreground">
                                                            Advertising: <span class="font-medium">{ad.product.name}</span>
                                                        </p>
                                                        <div class="flex items-center gap-4 mt-2 text-xs text-secondary-foreground">
                                                            <span>
                                                                <i class="ki-filled ki-calendar text-blue-500 mr-1"></i>
                                                                Started: {formatTimestamp(ad.started_at)}
                                                            </span>
                                                            {#if ad.status === 'active'}
                                                                <span>
                                                                    <i class="ki-filled ki-check-circle text-blue-500 mr-1"></i>
                                                                    Completes: {formatTimestamp(getCompletionDate(ad))}
                                                                </span>
                                                            {:else if ad.status === 'completed'}
                                                                <span>
                                                                    <i class="ki-filled ki-check text-gray-500 mr-1"></i>
                                                                    Completed: {formatTimestamp(ad.completed_at)}
                                                                </span>
                                                            {/if}
                                                        </div>
                                                    </div>
                                                </div>
                                                
                                                <!-- Ad Stats -->
                                                <div class="flex items-center gap-6">
                                                    <div class="text-right">
                                                        <div class="text-sm font-medium text-mono">
                                                            {ad.price} DZD
                                                        </div>
                                                        <div class="text-xs text-secondary-foreground">
                                                            Cost
                                                        </div>
                                                    </div>
                                                    <div class="text-right">
                                                        <div class="text-sm font-medium text-mono">
                                                            {(ad.market_impact_percentage * 100).toFixed(2)}%
                                                        </div>
                                                        <div class="text-xs text-secondary-foreground">
                                                            Impact
                                                        </div>
                                                    </div>
                                                    <div class="text-right">
                                                        <div class="text-sm font-medium text-mono">
                                                            {getTotalDuration(ad)}
                                                        </div>
                                                        <div class="text-xs text-secondary-foreground">
                                                            Total Days
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                {/each}
                            </div>
                        </div>

                        <!-- Pagination -->
                        {#if pagination && pagination.total > 0}
                            <div class="border-t border-gray-200">
                                <Pagination 
                                    {pagination} 
                                    {perPage}
                                    onPageChange={goToPage} 
                                    onPerPageChange={handlePerPageChange}
                                />
                            </div>
                        {/if}
                    {/if}
                </div>
            </div>
        </div>
    </div>
    <!-- End of Container -->
</CompanyLayout>
