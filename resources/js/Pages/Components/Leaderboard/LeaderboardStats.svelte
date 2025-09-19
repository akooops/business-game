<script>
    export let stats = null;
    export let loading = false;

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
</script>

<div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5 lg:gap-7.5">
    <!-- Total Companies -->
    <div class="kt-card h-full">
        <div class="kt-card-content p-6">
            <div class="flex items-center justify-between">
                <div class="flex flex-col">
                    <div class="flex items-center gap-2 mb-2">
                        <div class="kt-badge kt-badge-light-primary kt-badge-circle size-6">
                            <i class="ki-filled ki-profile-user text-sm"></i>
                        </div>
                        <span class="text-sm font-medium text-secondary-foreground">
                            Total Companies
                        </span>
                    </div>
                    
                    {#if loading}
                        <div class="kt-skeleton w-20 h-6 rounded mb-1"></div>
                    {:else}
                        <div class="text-2xl font-bold text-mono mb-1">
                            {formatNumber(stats?.total_companies)}
                        </div>
                        <span class="text-xs text-secondary-foreground">
                            Active players
                        </span>
                    {/if}
                </div>
            </div>
        </div>
    </div>

    <!-- Total Market Value -->
    <div class="kt-card h-full">
        <div class="kt-card-content p-6">
            <div class="flex items-center justify-between">
                <div class="flex flex-col">
                    <div class="flex items-center gap-2 mb-2">
                        <div class="kt-badge kt-badge-light-success kt-badge-circle size-6">
                            <i class="ki-filled ki-wallet text-sm"></i>
                        </div>
                        <span class="text-sm font-medium text-secondary-foreground">
                            Total Market Value
                        </span>
                    </div>
                    
                    {#if loading}
                        <div class="kt-skeleton w-20 h-6 rounded mb-1"></div>
                    {:else}
                        <div class="text-2xl font-bold text-mono mb-1">
                            {formatCurrency(stats?.total_funds)}
                        </div>
                        <span class="text-xs text-secondary-foreground">
                            Combined funds
                        </span>
                    {/if}
                </div>
            </div>
        </div>
    </div>

    <!-- Total Debt -->
    <div class="kt-card h-full">
        <div class="kt-card-content p-6">
            <div class="flex items-center justify-between">
                <div class="flex flex-col">
                    <div class="flex items-center gap-2 mb-2">
                        <div class="kt-badge kt-badge-light-danger kt-badge-circle size-6">
                            <i class="ki-filled ki-bank text-sm"></i>
                        </div>
                        <span class="text-sm font-medium text-secondary-foreground">
                            Total Debt
                        </span>
                    </div>
                    
                    {#if loading}
                        <div class="kt-skeleton w-20 h-6 rounded mb-1"></div>
                    {:else}
                        <div class="text-2xl font-bold text-mono mb-1">
                            {formatCurrency(stats?.total_loans)}
                        </div>
                        <span class="text-xs text-secondary-foreground">
                            Outstanding loans
                        </span>
                    {/if}
                </div>
            </div>
        </div>
    </div>

    <!-- Total Carbon Footprint -->
    <div class="kt-card h-full">
        <div class="kt-card-content p-6">
            <div class="flex items-center justify-between">
                <div class="flex flex-col">
                    <div class="flex items-center gap-2 mb-2">
                        <div class="kt-badge kt-badge-light-warning kt-badge-circle size-6">
                            <i class="ki-filled ki-delivery-3 text-sm"></i>
                        </div>
                        <span class="text-sm font-medium text-secondary-foreground">
                            Total Carbon
                        </span>
                    </div>
                    
                    {#if loading}
                        <div class="kt-skeleton w-20 h-6 rounded mb-1"></div>
                    {:else}
                        <div class="text-2xl font-bold text-mono mb-1">
                            {formatNumber(stats?.total_carbon)}
                        </div>
                        <span class="text-xs text-secondary-foreground">
                            tons CO₂
                        </span>
                    {/if}
                </div>
            </div>
        </div>
    </div>
</div>

{#if stats && !loading}
    <!-- Top Performers Section -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-5 lg:gap-7.5 mt-5">
        <!-- Richest Company -->
        <div class="kt-card">
            <div class="kt-card-header">
                <h3 class="kt-card-title">
                    <i class="ki-filled ki-crown-2 text-warning me-2"></i>
                    Richest Company
                </h3>
            </div>
            <div class="kt-card-content">
                {#if stats.top_funded_company}
                    <div class="flex items-center gap-3">
                        <div class="kt-badge kt-badge-light-warning kt-badge-circle size-12">
                            <i class="ki-filled ki-crown-2 text-lg"></i>
                        </div>
                        <div class="flex flex-col">
                            <span class="text-lg font-bold text-mono">
                                {stats.top_funded_company.name}
                            </span>
                            <span class="text-sm text-secondary-foreground">
                                {formatCurrency(stats.top_funded_company.funds)}
                            </span>
                        </div>
                    </div>
                {:else}
                    <p class="text-sm text-secondary-foreground">No data available</p>
                {/if}
            </div>
        </div>

        <!-- Most Researched Company -->
        <div class="kt-card">
            <div class="kt-card-header">
                <h3 class="kt-card-title">
                    <i class="ki-filled ki-flask text-info me-2"></i>
                    Most Researched Company
                </h3>
            </div>
            <div class="kt-card-content">
                {#if stats.most_research}
                    <div class="flex items-center gap-3">
                        <div class="kt-badge kt-badge-light-info kt-badge-circle size-12">
                            <i class="ki-filled ki-flask text-lg"></i>
                        </div>
                        <div class="flex flex-col">
                            <span class="text-lg font-bold text-mono">
                                {stats.most_research.name}
                            </span>
                            <span class="text-sm text-secondary-foreground">
                                Advanced research level
                            </span>
                        </div>
                    </div>
                {:else}
                    <p class="text-sm text-secondary-foreground">No data available</p>
                {/if}
            </div>
        </div>
    </div>
{/if}
