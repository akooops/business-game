<script>
    import AdminLayout from '../../Layouts/AdminLayout.svelte';

    // Props passed from controller
    export let advertiser;

    // Define breadcrumbs
    const breadcrumbs = [
        {
            title: 'Advertisers',
            url: route('admin.advertisers.index'),
            active: false
        },
        {
            title: advertiser.name || 'Advertiser Details',
            url: route('admin.advertisers.show', { advertiser: advertiser.id }),
            active: true
        }
    ];
    
    const pageTitle = 'Advertiser Details';
</script>

<svelte:head>
    <title>Business Game - {pageTitle}</title>
</svelte:head>

<AdminLayout {breadcrumbs} {pageTitle}>
    <!-- Container -->
    <div class="kt-container-fixed">
        <div class="grid gap-5 lg:gap-7.5">
            <!-- Header -->
            <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4">
                <div class="flex flex-col gap-1">
                    <h1 class="text-2xl font-bold text-mono">Advertiser Details</h1>
                    <p class="text-sm text-secondary-foreground">
                        View advertiser information and lending terms
                    </p>
                </div>
                <div class="flex items-center gap-3">
                    <a href="{route('admin.advertisers.index')}" class="kt-btn kt-btn-outline">
                        <i class="ki-filled ki-arrow-left text-base"></i>
                        Back to Advertisers
                    </a>
                    <a href="{route('admin.advertisers.edit', { advertiser: advertiser.id })}" class="kt-btn kt-btn-primary">
                        <i class="ki-filled ki-pencil text-base"></i>
                        Edit Advertiser
                    </a>
                </div>
            </div>

            <!-- Basic Information Card -->
            <div class="kt-card">
                <div class="kt-card-header">
                    <h4 class="kt-card-title">Basic Information</h4>
                </div>
                <div class="kt-card-content">
                    <div class="grid gap-4 w-full">
                        <!-- Advertiser Logo -->
                        <div class="flex">
                            <figure class="figure">
                                <img 
                                    src={advertiser?.logo_url} 
                                    alt={advertiser?.name}
                                    class="rounded-lg w-32 h-32 object-cover border"
                                />
                            </figure>
                        </div>

                        <!-- Advertiser Name -->
                        <div class="flex flex-col gap-2">
                            <h4 class="text-sm font-semibold text-mono">Advertiser Name</h4>
                            <p class="text-sm text-secondary-foreground">{advertiser.name}</p>
                        </div>

                        <!-- Created At -->
                        <div class="flex flex-col gap-2">
                            <h4 class="text-sm font-semibold text-mono">Created At</h4>
                            <p class="text-sm text-secondary-foreground">
                                {formatTimestamp(advertiser.created_at)}
                            </p>
                        </div>

                        <!-- Updated At -->
                        <div class="flex flex-col gap-2">
                            <h4 class="text-sm font-semibold text-mono">Updated At</h4>
                            <p class="text-sm text-secondary-foreground">
                                {formatTimestamp(advertiser.updated_at)}
                            </p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Advertiser Terms Card -->
            <div class="kt-card">
                <div class="kt-card-header">
                    <h4 class="kt-card-title">Advertiser Terms</h4>
                </div>
                <div class="kt-card-content">
                    <div class="grid gap-4 w-full">
                        <!-- Price Range -->
                        <div class="flex flex-col gap-2">
                            <h4 class="text-sm font-semibold text-mono">Price Range</h4>
                            <p class="text-sm text-secondary-foreground">
                                {advertiser.min_price} - {advertiser.max_price} DZD
                            </p>
                        </div>

                        <!-- Market Impact -->
                        <div class="flex flex-col gap-2">
                            <h4 class="text-sm font-semibold text-mono">Market Impact</h4>
                            <p class="text-sm text-secondary-foreground font-bold">
                                {(advertiser.min_market_impact_percentage * 100).toFixed(2)}% - {(advertiser.max_market_impact_percentage * 100).toFixed(2)}%
                            </p>
                        </div>

                        <!-- Duration -->
                        <div class="flex flex-col gap-2">
                            <h4 class="text-sm font-semibold text-mono">Duration</h4>
                            <p class="text-sm text-secondary-foreground font-bold">
                                {advertiser.duration_days} days
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</AdminLayout>