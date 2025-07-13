<script>
    import AdminLayout from '../../Layouts/AdminLayout.svelte';
    import { page } from '@inertiajs/svelte';

    // Props
    export let technology;

    // Define breadcrumbs for this product
    const breadcrumbs = [
        {
            title: 'Technologies',
            url: route('admin.technologies.index'),
            active: false
        },
        {
            title: technology.name || 'Technology Details',
            url: route('admin.technologies.show', { technology: technology.id }),
            active: true
        }
    ];
    
    const pageTitle = 'Technology Details';

    // Product type badge colors
    function getProductTypeBadgeClass(type) {
        switch(type) {
            case 'raw_material':
                return 'kt-badge kt-badge-outline kt-badge-success';
            case 'component':
                return 'kt-badge kt-badge-outline kt-badge-warning';
            case 'finished_product':
                return 'kt-badge kt-badge-outline kt-badge-primary';
            default:
                return 'kt-badge kt-badge-outline';
        }
    }
</script>

<svelte:head>
    <title>Business Game - {pageTitle}</title>
</svelte:head>

<AdminLayout {breadcrumbs} {pageTitle}>
    <div class="kt-container-fixed">
        <div class="grid gap-5 lg:gap-7.5">
            <!-- Technology Header -->
            <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4">
                <div class="flex flex-col gap-1">
                    <h1 class="text-2xl font-bold text-mono">Technology Details</h1>
                    <p class="text-sm text-secondary-foreground">
                        View technology information and research requirements
                    </p>
                </div>
                <div class="flex items-center gap-3">
                    <a href="{route('admin.technologies.index')}" class="kt-btn kt-btn-outline">
                        <i class="ki-filled ki-arrow-left text-base"></i>
                        Back to Technologies
                    </a>
                    {#if hasPermission('admin.technologies.update')}
                    <a href="{route('admin.technologies.edit', { technology: technology.id })}" class="kt-btn kt-btn-primary">
                        <i class="ki-filled ki-pencil text-base"></i>
                        Edit Technology
                    </a>
                    {/if}
                </div>
            </div>

            <!-- Technology Information Card -->
            <div class="kt-card">
                <div class="kt-card-header">
                    <h4 class="kt-card-title">Basic Information</h4>
                </div>
                <div class="kt-card-content">
                     <!-- Technology Details -->
                     <div class="grid gap-4 w-full">
                        <!-- Technology Image -->
                        <div class="flex">
                            <figure class="figure">
                                <img 
                                    src={technology?.image_url} 
                                    alt={technology?.name}
                                    class="rounded-lg w-32 h-32 object-cover border"
                                />
                            </figure>
                        </div>

                        <!-- Technology Name -->
                        <div class="flex flex-col gap-2">
                            <h4 class="text-sm font-semibold text-mono">Technology Name</h4>
                            <p class="text-sm text-secondary-foreground">{technology?.name}</p>
                        </div>

                        <!-- Description -->
                        {#if technology?.description}
                        <div class="flex flex-col gap-2">
                            <h4 class="text-sm font-semibold text-mono">Description</h4>
                            <p class="text-sm text-secondary-foreground">{technology?.description}</p>
                        </div>
                        {/if}
                        
                        <!-- Technology Level -->
                        <div class="flex flex-col gap-2">
                            <h4 class="text-sm font-semibold text-mono">Technology Level</h4>
                            <div>
                                <span class="kt-badge kt-badge kt-badge-light-primary">
                                    {technology?.level}   
                                </span>
                            </div>
                        </div>

                        <!-- Created At -->
                        <div class="flex flex-col gap-2">
                            <h4 class="text-sm font-semibold text-mono">Created At</h4>
                            <p class="text-sm text-secondary-foreground">
                                {formatTimestamp(technology?.created_at)}
                            </p>
                        </div>

                        <!-- Updated At -->
                        <div class="flex flex-col gap-2">
                            <h4 class="text-sm font-semibold text-mono">Updated At</h4>
                            <p class="text-sm text-secondary-foreground">
                                {formatTimestamp(technology?.updated_at)}
                            </p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Technology Research Requirements Card -->
            <div class="kt-card">
                <div class="kt-card-header">
                    <h4 class="kt-card-title">Technology Research Requirements</h4>
                </div>
                <div class="kt-card-content">
                    <div class="grid gap-4 w-full">
                        <!-- Research Cost -->
                        <div class="flex flex-col gap-2">
                            <h4 class="text-sm font-semibold text-mono">Research Cost</h4>
                            <p class="text-sm text-secondary-foreground">
                                {technology?.research_cost}
                            </p>
                        </div>

                        <!-- Research Time (Days) -->
                        <div class="flex flex-col gap-2">
                            <h4 class="text-sm font-semibold text-mono">Research Time (Days)</h4>
                            <p class="text-sm text-secondary-foreground">
                                {technology?.research_time_days}
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</AdminLayout> 