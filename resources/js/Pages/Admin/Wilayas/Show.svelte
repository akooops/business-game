<script>
    import AdminLayout from '../../Layouts/AdminLayout.svelte';
    import { page } from '@inertiajs/svelte';

    // Props
    export let wilaya;

    // Define breadcrumbs for this wilaya
    const breadcrumbs = [
        {
            title: 'Wilayas',
            url: route('admin.wilayas.index'),
            active: false
        },
        {
            title: wilaya.name || 'Wilaya Details',
            url: route('admin.wilayas.show', { wilaya: wilaya.id }),
            active: true
        }
    ];
    
    const pageTitle = 'Wilaya Details';
</script>

<svelte:head>
    <title>Business Game - {pageTitle}</title>
</svelte:head>

<AdminLayout {breadcrumbs} {pageTitle}>
    <div class="kt-container-fixed">
        <div class="grid gap-5 lg:gap-7.5">
            <!-- Wilaya Header -->
            <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4">
                <div class="flex flex-col gap-1">
                    <h1 class="text-2xl font-bold text-mono">Wilaya Details</h1>
                    <p class="text-sm text-secondary-foreground">
                        View wilaya information and shipping details
                    </p>
                </div>
                <div class="flex items-center gap-3">
                    <a href="{route('admin.wilayas.index')}" class="kt-btn kt-btn-outline">
                        <i class="ki-filled ki-arrow-left text-base"></i>
                        Back to Wilayas
                    </a>
                    {#if hasPermission('admin.wilayas.update')}
                    <a href="{route('admin.wilayas.edit', { wilaya: wilaya.id })}" class="kt-btn kt-btn-primary">
                        <i class="ki-filled ki-pencil text-base"></i>
                        Edit Wilaya
                    </a>
                    {/if}
                </div>
            </div>

            <!-- Wilaya Information Card -->
            <div class="kt-card">
                <div class="kt-card-header">
                    <h4 class="kt-card-title">Basic Information</h4>
                </div>
                <div class="kt-card-content">
                     <!-- Wilaya Details -->
                     <div class="grid gap-4 w-full">
                        <!-- Wilaya Name -->
                        <div class="flex flex-col gap-2">
                            <h4 class="text-sm font-semibold text-mono">Wilaya Name</h4>
                            <p class="text-sm text-secondary-foreground">{wilaya?.name}</p>
                        </div>

                        <!-- Created At -->
                        <div class="flex flex-col gap-2">
                            <h4 class="text-sm font-semibold text-mono">Created At</h4>
                            <p class="text-sm text-secondary-foreground">
                                {formatTimestamp(wilaya?.created_at)}
                            </p>
                        </div>

                        <!-- Updated At -->
                        <div class="flex flex-col gap-2">
                            <h4 class="text-sm font-semibold text-mono">Updated At</h4>
                            <p class="text-sm text-secondary-foreground">
                                {formatTimestamp(wilaya?.updated_at)}
                            </p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Shipping Costs Card -->
            <div class="kt-card">
                <div class="kt-card-header">
                    <h4 class="kt-card-title">Shipping Costs</h4>
                </div>
                <div class="kt-card-content">
                    <div class="grid gap-4 w-full lg:grid-cols-3">
                        <!-- Minimum Shipping Cost -->
                        <div class="flex flex-col gap-2">
                            <h4 class="text-sm font-semibold text-mono">Minimum Shipping Cost</h4>
                            <p class="text-sm text-secondary-foreground">
                                {wilaya?.min_shipping_cost}
                            </p>
                        </div>

                        <!-- Average Shipping Cost -->
                        <div class="flex flex-col gap-2">
                            <h4 class="text-sm font-semibold text-mono">Average Shipping Cost</h4>
                            <p class="text-sm text-secondary-foreground">
                                {wilaya?.avg_shipping_cost}
                            </p>
                        </div>

                        <!-- Maximum Shipping Cost -->
                        <div class="flex flex-col gap-2">
                            <h4 class="text-sm font-semibold text-mono">Maximum Shipping Cost</h4>
                            <p class="text-sm text-secondary-foreground">
                                {wilaya?.max_shipping_cost}
                            </p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Shipping Times Card -->
            <div class="kt-card">
                <div class="kt-card-header">
                    <h4 class="kt-card-title">Shipping Times</h4>
                </div>
                <div class="kt-card-content">
                    <div class="grid gap-4 w-full lg:grid-cols-3">
                        <!-- Minimum Shipping Time -->
                        <div class="flex flex-col gap-2">
                            <h4 class="text-sm font-semibold text-mono">Minimum Shipping Time</h4>
                            <p class="text-sm text-secondary-foreground">
                                {wilaya?.min_shipping_time_days} days
                            </p>
                        </div>

                        <!-- Average Shipping Time -->
                        <div class="flex flex-col gap-2">
                            <h4 class="text-sm font-semibold text-mono">Average Shipping Time</h4>
                            <p class="text-sm text-secondary-foreground">
                                {wilaya?.avg_shipping_time_days} days
                            </p>
                        </div>

                        <!-- Maximum Shipping Time -->
                        <div class="flex flex-col gap-2">
                            <h4 class="text-sm font-semibold text-mono">Maximum Shipping Time</h4>
                            <p class="text-sm text-secondary-foreground">
                                {wilaya?.max_shipping_time_days} days
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</AdminLayout> 