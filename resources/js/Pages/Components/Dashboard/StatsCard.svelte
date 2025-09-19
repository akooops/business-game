<script>
    export let title = '';
    export let value = '';
    export let subtitle = '';
    export let icon = '';
    export let iconColor = 'kt-badge-light-primary';
    export let trend = null; // { value: number, isPositive: boolean }
    export let loading = false;
</script>

<div class="kt-card h-full">
    <div class="kt-card-content p-6">
        <div class="flex items-center justify-between">
            <div class="flex flex-col">
                <div class="flex items-center gap-2 mb-2">
                    {#if icon}
                        <div class="kt-badge {iconColor} kt-badge-circle size-6">
                            <i class="ki-filled {icon} text-sm"></i>
                        </div>
                    {/if}
                    <span class="text-sm font-medium text-secondary-foreground">
                        {title}
                    </span>
                </div>
                
                {#if loading}
                    <div class="kt-skeleton w-20 h-6 rounded mb-1"></div>
                    <div class="kt-skeleton w-16 h-4 rounded"></div>
                {:else}
                    <div class="text-2xl font-bold text-mono mb-1">
                        {value}
                    </div>
                    {#if subtitle}
                        <span class="text-xs text-secondary-foreground">
                            {subtitle}
                        </span>
                    {/if}
                {/if}
            </div>
            
            {#if trend && !loading}
                <div class="flex flex-col items-end">
                    <div class="kt-badge {trend.isPositive ? 'kt-badge-light-success' : 'kt-badge-light-danger'} kt-badge-sm">
                        <i class="ki-filled {trend.isPositive ? 'ki-arrow-up' : 'ki-arrow-down'} text-xs me-1"></i>
                        {Math.abs(trend.value)}%
                    </div>
                    <span class="text-xs text-secondary-foreground mt-1">
                        vs yesterday
                    </span>
                </div>
            {/if}
        </div>
    </div>
</div>
