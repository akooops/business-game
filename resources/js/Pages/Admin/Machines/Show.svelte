<script>
    import AdminLayout from '../../Layouts/AdminLayout.svelte';

    // Props passed from controller
    export let machine;

    // Define breadcrumbs
    const breadcrumbs = [
        {
            title: 'Machines',
            url: route('admin.machines.index'),
            active: false
        },
        {
            title: machine.name || 'Machine Details',
            url: route('admin.machines.show', { machine: machine.id }),
            active: true
        }
    ];
    
    const pageTitle = 'Machine Details';

    // Map machine outputs for display
    const outputs = (machine.outputs || []).map(output => ({
        product_id: output.product.id,
        product_name: output.product.name,
        product_type_name: output.product.type_name,
        product_image: output.product.image_url,
        product_type: output.product.type
    }));

    // Get product type badge class
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
    <!-- Container -->
    <div class="kt-container-fixed">
        <div class="grid gap-5 lg:gap-7.5">
            <!-- Header -->
            <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4">
                <div class="flex flex-col gap-1">
                    <h1 class="text-2xl font-bold text-mono">Machine Details</h1>
                    <p class="text-sm text-secondary-foreground">
                        View machine specifications, performance metrics and operational requirements
                    </p>
                </div>
                <div class="flex items-center gap-3">
                    <a href="{route('admin.machines.index')}" class="kt-btn kt-btn-outline">
                        <i class="ki-filled ki-arrow-left text-base"></i>
                        Back to Machines
                    </a>
                    <a href="{route('admin.machines.edit', { machine: machine.id })}" class="kt-btn kt-btn-primary">
                        <i class="ki-filled ki-pencil text-base"></i>
                        Edit Machine
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
                        <!-- Machine Image -->
                        <div class="flex">
                            <figure class="figure">
                                <img 
                                    src={machine?.image_url} 
                                    alt={machine?.name}
                                    class="rounded-lg w-32 h-32 object-cover border"
                                />
                            </figure>
                        </div>

                        <!-- Machine Name -->
                        <div class="flex flex-col gap-2">
                            <h4 class="text-sm font-semibold text-mono">Machine Name</h4>
                            <p class="text-sm text-secondary-foreground">{machine.name}</p>
                        </div>

                        <!-- Model -->
                        <div class="flex flex-col gap-2">
                            <h4 class="text-sm font-semibold text-mono">Model</h4>
                            <p class="text-sm text-secondary-foreground">{machine.model}</p>
                        </div>

                        <!-- Manufacturer -->
                        <div class="flex flex-col gap-2">
                            <h4 class="text-sm font-semibold text-mono">Manufacturer</h4>
                            <p class="text-sm text-secondary-foreground">{machine.manufacturer}</p>
                        </div>

                        <!-- Description -->
                        <div class="flex flex-col gap-2">
                            <h4 class="text-sm font-semibold text-mono">Description</h4>
                            <p class="text-sm text-secondary-foreground">{machine.description}</p>
                        </div>

                        <!-- Cost to Acquire -->
                        <div class="flex flex-col gap-2">
                            <h4 class="text-sm font-semibold text-mono">Acquisition Cost</h4>
                            <p class="text-sm text-secondary-foreground font-bold">
                                {machine.cost_to_acquire} DZD
                            </p>
                        </div>

                        <!-- Loss on Sale -->

                        <!-- Loss on Sale -->
                        <div class="flex flex-col gap-2">
                            <h4 class="text-sm font-semibold text-mono">Loss on Sale</h4>
                            <p class="text-sm text-secondary-foreground">
                                {(machine.loss_on_sale_days * 100).toFixed(2)}% / day of acquisition cost
                            </p>
                        </div>

                        <!-- Created At -->
                        <div class="flex flex-col gap-2">
                            <h4 class="text-sm font-semibold text-mono">Created At</h4>
                            <p class="text-sm text-secondary-foreground">
                                {formatTimestamp(machine.created_at)}
                            </p>
                        </div>

                        <!-- Updated At -->
                        <div class="flex flex-col gap-2">
                            <h4 class="text-sm font-semibold text-mono">Updated At</h4>
                            <p class="text-sm text-secondary-foreground">
                                {formatTimestamp(machine.updated_at)}
                            </p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Performance Metrics Card -->
            <div class="kt-card">
                <div class="kt-card-header">
                    <h4 class="kt-card-title">Performance Metrics</h4>
                </div>
                <div class="kt-card-content">
                    <div class="grid gap-4 w-full">
                        <!-- Operation Cost -->
                        <div class="flex flex-col gap-2">
                            <h4 class="text-sm font-semibold text-mono">OperationS Cost</h4>
                            <p class="text-sm text-secondary-foreground">
                                {machine.operations_cost} DZD/week
                                <span class="text-xs text-muted-foreground ml-2">
                                    (Daily operational expenses)
                                </span>
                            </p>
                        </div>

                        <!-- Carbon Footprint -->
                        <div class="flex flex-col gap-2">
                            <h4 class="text-sm font-semibold text-mono">Carbon Footprint</h4>
                            <p class="text-sm text-secondary-foreground">
                                {machine.carbon_footprint} kg CO2/unit produced
                                <span class="text-xs text-muted-foreground ml-2">
                                    (Environmental impact per unit)
                                </span>
                            </p>
                        </div>

                        <!-- Quality Factor -->
                        <div class="flex flex-col gap-2">
                            <h4 class="text-sm font-semibold text-mono">Quality Factor</h4>
                            <p class="text-sm text-secondary-foreground">
                                {machine.quality_factor}
                                <span class="text-xs text-muted-foreground ml-2">
                                    (Quality output coefficient: 0.0 - 1.0)
                                </span>
                            </p>
                        </div>

                        <!-- Speed Range -->
                        <div class="flex flex-col gap-2">
                            <h4 class="text-sm font-semibold text-mono">Production Speed</h4>
                            <div class="grid grid-cols-2 gap-4">
                                <div class="flex flex-col gap-1">
                                    <span class="text-xs text-muted-foreground">Minimum</span>
                                    <p class="text-sm text-secondary-foreground">
                                        {machine.min_speed} units/day
                                    </p>
                                </div>
                                <div class="flex flex-col gap-1">
                                    <span class="text-xs text-muted-foreground">Maximum</span>
                                    <p class="text-sm text-secondary-foreground">
                                        {machine.max_speed} units/day
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Reliability & Maintenance Card -->
            <div class="kt-card">
                <div class="kt-card-header">
                    <h4 class="kt-card-title">Reliability & Maintenance</h4>
                </div>
                <div class="kt-card-content">
                    <div class="grid gap-4 w-full">
                        <!-- Reliability Decay -->
                        <div class="flex flex-col gap-2">
                            <h4 class="text-sm font-semibold text-mono">Reliability Decay</h4>
                            <p class="text-sm text-secondary-foreground">
                                {machine.reliability_decay_days} days
                                <span class="text-xs text-muted-foreground ml-2">
                                    (Time until reliability decreases)
                                </span>
                            </p>
                        </div>

                        <!-- Maintenance -->
                        <div class="flex flex-col gap-2">
                            <h4 class="text-sm font-semibold text-mono">Maintenance</h4>
                            <div class="grid grid-cols-2 gap-4">
                                <div class="flex flex-col gap-1">
                                    <span class="text-xs text-muted-foreground">Cost Range</span>
                                    <p class="text-sm text-secondary-foreground">
                                        {machine.min_maintenance_cost} - {machine.max_maintenance_cost} DZD
                                    </p>
                                </div>
                                <div class="flex flex-col gap-1">
                                    <span class="text-xs text-muted-foreground">Time Range</span>
                                    <p class="text-sm text-secondary-foreground">
                                        {machine.min_maintenance_time_days} - {machine.max_maintenance_time_days} days
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Employee Requirements Card -->
            <div class="kt-card">
                <div class="kt-card-header">
                    <h4 class="kt-card-title">Employee Requirements</h4>
                </div>
                <div class="kt-card-content">
                    <div class="grid gap-4">
                        <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
                                <div class="kt-card">
                                    <div class="kt-card-content p-4">
                                        <div class="flex items-start gap-3">
                                            <!-- Employee Info -->
                                            <div class="flex-1 space-y-2">
                                                <div class="flex items-center gap-2">
                                                    <h6 class="text-sm font-medium text-mono">
                                                        {machine.employee_profile.name}
                                                    </h6>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Machine Outputs Card -->
            <div class="kt-card">
                <div class="kt-card-header">
                    <h4 class="kt-card-title">Machine Outputs</h4>
                </div>
                <div class="kt-card-content">
                    <div class="grid gap-4">
                        {#if outputs.length === 0}
                            <div class="kt-card bg-muted/20 border-dashed">
                                <div class="kt-card-content text-center py-8">
                                    <i class="ki-filled ki-abstract-26 text-2xl text-muted-foreground mb-2"></i>
                                    <p class="text-sm text-muted-foreground">No products defined yet</p>
                                    <p class="text-xs text-muted-foreground mt-1">This machine has no product outputs configured</p>
                                </div>
                            </div>
                        {:else}
                            <div>
                                <h5 class="text-sm font-medium text-mono mb-3">
                                    Products ({outputs.length})
                                </h5>
                                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-4">
                                    {#each outputs as output}
                                        <div class="kt-card">
                                            <div class="kt-card-content flex flex-col p-3 gap-3">
                                                <!-- Product Image -->
                                                <div class="kt-card flex items-center justify-center relative bg-accent/50 w-full h-[120px] shadow-none rounded">
                                                    {#if output.product_image}
                                                        <img alt="" class="h-[100px] w-[100px] object-cover rounded" src="{output.product_image}"/>
                                                    {:else}
                                                        <i class="ki-filled ki-abstract-26 text-3xl text-muted-foreground"></i>
                                                    {/if}
                                                </div>
                                                
                                                <!-- Product Info -->
                                                <div class="flex flex-col gap-1">
                                                    <h6 class="text-sm font-medium text-mono leading-5 line-clamp-2" title="{output.product_name}">
                                                        {output.product_name}
                                                    </h6>
                                                    <span class={getProductTypeBadgeClass(output.product_type) + ' kt-badge-sm w-fit'}>
                                                        {output.product_type_name}
                                                    </span>
                                                </div>
                                            </div>
                                        </div>
                                    {/each}
                                </div>
                            </div>
                        {/if}
                    </div>
                </div>
            </div>
        </div>
    </div>
</AdminLayout> 