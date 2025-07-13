<script>
    import AdminLayout from '../../Layouts/AdminLayout.svelte';
    import { page } from '@inertiajs/svelte';

    // Props
    export let country;

    // Define breadcrumbs for this country
    const breadcrumbs = [
        {
            title: 'Countries',
            url: route('admin.countries.index'),
            active: false
        },
        {
            title: country.name || 'Country Details',
            url: route('admin.countries.show', { country: country.id }),
            active: true
        }
    ];
    
    const pageTitle = 'Country Details';

    // Format percentage
    function formatPercentage(value) {
        return `${(value * 100).toFixed(2)}%`;
    }
</script>

<svelte:head>
    <title>Business Game - {pageTitle}</title>
</svelte:head>

<AdminLayout {breadcrumbs} {pageTitle}>
    <div class="kt-container-fixed">
        <div class="grid gap-5 lg:gap-7.5">
            <!-- Country Header -->
            <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4">
                <div class="flex flex-col gap-1">
                    <h1 class="text-2xl font-bold text-mono">Country Details</h1>
                    <p class="text-sm text-secondary-foreground">
                        View country information and import regulations
                    </p>
                </div>
                <div class="flex items-center gap-3">
                    <a href="{route('admin.countries.index')}" class="kt-btn kt-btn-outline">
                        <i class="ki-filled ki-arrow-left text-base"></i>
                        Back to Countries
                    </a>
                    {#if hasPermission('admin.countries.update')}
                    <a href="{route('admin.countries.edit', { country: country.id })}" class="kt-btn kt-btn-primary">
                        <i class="ki-filled ki-pencil text-base"></i>
                        Edit Country
                    </a>
                    {/if}
                </div>
            </div>

            <!-- Country Information Card -->
            <div class="kt-card">
                <div class="kt-card-header">
                    <h4 class="kt-card-title">Basic Information</h4>
                </div>
                <div class="kt-card-content">
                     <!-- Country Details -->
                     <div class="grid gap-4 w-full">
                        <!-- Country Flag -->
                        <div class="flex">
                            <figure class="figure">
                                <img 
                                    src={country?.flag_url} 
                                    alt={country?.name}
                                    class="rounded-lg w-32 h-32 object-cover border"
                                />
                            </figure>
                        </div>

                        <!-- Country Name -->
                        <div class="flex flex-col gap-2">
                            <h4 class="text-sm font-semibold text-mono">Country Name</h4>
                            <p class="text-sm text-secondary-foreground">{country?.name}</p>
                        </div>
                        
                        <!-- Import Status -->
                        <div class="flex flex-col gap-2">
                            <h4 class="text-sm font-semibold text-mono">Import Status</h4>
                            <div>
                                {#if country?.allows_imports}
                                    <span class="kt-badge kt-badge-success kt-badge-sm">
                                        <i class="ki-filled ki-check text-xs"></i>
                                        Imports Allowed
                                    </span>
                                {:else}
                                    <span class="kt-badge kt-badge-destructive kt-badge-sm">
                                        <i class="ki-filled ki-cross text-xs"></i>
                                        Imports Restricted
                                    </span>
                                {/if}
                            </div>
                        </div>

                        <!-- Created At -->
                        <div class="flex flex-col gap-2">
                            <h4 class="text-sm font-semibold text-mono">Created At</h4>
                            <p class="text-sm text-secondary-foreground">
                                {formatTimestamp(country?.created_at)}
                            </p>
                        </div>

                        <!-- Updated At -->
                        <div class="flex flex-col gap-2">
                            <h4 class="text-sm font-semibold text-mono">Updated At</h4>
                            <p class="text-sm text-secondary-foreground">
                                {formatTimestamp(country?.updated_at)}
                            </p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Import Regulations Card -->
            <div class="kt-card">
                <div class="kt-card-header">
                    <h4 class="kt-card-title">Import Regulations</h4>
                </div>
                <div class="kt-card-content">
                    <div class="grid gap-4 w-full">
                        <!-- Customs Duties Rate -->
                        <div class="flex flex-col gap-2">
                            <h4 class="text-sm font-semibold text-mono">Customs Duties Rate</h4>
                            <p class="text-sm text-secondary-foreground">
                                {formatPercentage(country?.customs_duties_rate)}
                                <span class="text-xs text-muted-foreground ml-2">
                                    (Applied on total value of imported goods)
                                </span>
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</AdminLayout> 