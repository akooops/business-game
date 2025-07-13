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

    // Format currency
    function formatCurrency(amount) {
        return new Intl.NumberFormat('en-US', {
            style: 'currency',
            currency: 'USD',
            minimumFractionDigits: 0,
            maximumFractionDigits: 0
        }).format(amount);
    }

    // Format number with units
    function formatNumber(value, decimals = 1) {
        return parseFloat(value).toFixed(decimals);
    }

    // Format hours to days and hours
    function formatHours(hours) {
        if (hours >= 24) {
            const days = Math.floor(hours / 24);
            const remainingHours = hours % 24;
            return remainingHours > 0 ? `${days}d ${remainingHours}h` : `${days}d`;
        }
        return `${hours}h`;
    }

    // Map employee profiles for display
    const employeeRequirements = (machine.employee_profiles || []).map(profile => ({
        profile_id: profile.id,
        profile_name: profile.name,
        profile_description: profile.description,
        required_count: profile.pivot.required_count,
        recruitment_difficulty: profile.recruitment_difficulty,
        avg_salary: profile.avg_salary_month
    }));

    // Map machine outputs for display
    const outputs = (machine.products || []).map(product => ({
        product_id: product.id,
        product_name: product.name,
        product_type_name: product.type_name,
        product_image: product.image_url,
        product_type: product.type
    }));

    // Get recruitment difficulty badge class
    function getRecruitmentDifficultyBadgeClass(difficulty) {
        switch(difficulty) {
            case 'very_easy':
                return 'kt-badge kt-badge-outline kt-badge-success';
            case 'easy':
                return 'kt-badge kt-badge-outline kt-badge-success';
            case 'medium':
                return 'kt-badge kt-badge-outline kt-badge-warning';
            case 'hard':
                return 'kt-badge kt-badge-outline kt-badge-danger';
            case 'very_hard':
                return 'kt-badge kt-badge-outline kt-badge-danger';
            default:
                return 'kt-badge kt-badge-outline';
        }
    }

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
                    {#if hasPermission('admin.machines.update')}
                    <a href="{route('admin.machines.edit', { machine: machine.id })}" class="kt-btn kt-btn-primary">
                        <i class="ki-filled ki-pencil text-base"></i>
                        Edit Machine
                    </a>
                    {/if}
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

                        <!-- Cost to Acquire -->
                        <div class="flex flex-col gap-2">
                            <h4 class="text-sm font-semibold text-mono">Acquisition Cost</h4>
                            <p class="text-sm text-secondary-foreground font-bold">
                                {formatCurrency(machine.cost_to_acquire)}
                            </p>
                        </div>

                        <!-- Area Required -->
                        <div class="flex flex-col gap-2">
                            <h4 class="text-sm font-semibold text-mono">Area Required</h4>
                            <p class="text-sm text-secondary-foreground">
                                {machine.area_required} sq m
                                <span class="text-xs text-muted-foreground ml-2">
                                    (Floor space required)
                                </span>
                            </p>
                        </div>

                        <!-- Setup Time -->
                        <div class="flex flex-col gap-2">
                            <h4 class="text-sm font-semibold text-mono">Setup Time</h4>
                            <p class="text-sm text-secondary-foreground">
                                {machine.setup_time_days} days
                                <span class="text-xs text-muted-foreground ml-2">
                                    (Time to install and configure)
                                </span>
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
                        <!-- Energy Consumption -->
                        <div class="flex flex-col gap-2">
                            <h4 class="text-sm font-semibold text-mono">Energy Consumption</h4>
                            <p class="text-sm text-secondary-foreground">
                                {formatNumber(machine.energy_consumption_hour)} kWh/hour
                                <span class="text-xs text-muted-foreground ml-2">
                                    (Electricity usage per hour)
                                </span>
                            </p>
                        </div>

                        <!-- Carbon Emissions -->
                        <div class="flex flex-col gap-2">
                            <h4 class="text-sm font-semibold text-mono">Carbon Emissions</h4>
                            <p class="text-sm text-secondary-foreground">
                                {formatNumber(machine.carbon_emissions_hour)} kg CO2/hour
                                <span class="text-xs text-muted-foreground ml-2">
                                    (Environmental impact per hour)
                                </span>
                            </p>
                        </div>

                        <!-- Quality Factor -->
                        <div class="flex flex-col gap-2">
                            <h4 class="text-sm font-semibold text-mono">Quality Factor</h4>
                            <p class="text-sm text-secondary-foreground">
                                {formatNumber(machine.quality_factor, 2)}
                                <span class="text-xs text-muted-foreground ml-2">
                                    (Quality output coefficient: 0.0 - 1.0)
                                </span>
                            </p>
                        </div>

                        <!-- Speed Range -->
                        <div class="flex flex-col gap-2">
                            <h4 class="text-sm font-semibold text-mono">Production Speed</h4>
                            <div class="grid grid-cols-3 gap-4">
                                <div class="flex flex-col gap-1">
                                    <span class="text-xs text-muted-foreground">Minimum</span>
                                    <p class="text-sm text-secondary-foreground">
                                        {formatNumber(machine.min_speed_hour)} units/h
                                    </p>
                                </div>
                                <div class="flex flex-col gap-1">
                                    <span class="text-xs text-muted-foreground">Average</span>
                                    <p class="text-sm text-secondary-foreground font-medium">
                                        {formatNumber(machine.avg_speed_hour)} units/h
                                    </p>
                                </div>
                                <div class="flex flex-col gap-1">
                                    <span class="text-xs text-muted-foreground">Maximum</span>
                                    <p class="text-sm text-secondary-foreground">
                                        {formatNumber(machine.max_speed_hour)} units/h
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
                        <!-- Failure Rate -->
                        <div class="flex flex-col gap-2">
                            <h4 class="text-sm font-semibold text-mono">Failure Rate</h4>
                            <p class="text-sm text-secondary-foreground">
                                {formatNumber(machine.failure_chance_hour, 3)}% per hour
                                <span class="text-xs text-muted-foreground ml-2">
                                    (Probability of breakdown per hour)
                                </span>
                            </p>
                        </div>

                        <!-- Reliability Decay -->
                        <div class="flex flex-col gap-2">
                            <h4 class="text-sm font-semibold text-mono">Reliability Decay</h4>
                            <p class="text-sm text-secondary-foreground">
                                {formatNumber(machine.reliability_decay_hour, 3)}% per hour
                                <span class="text-xs text-muted-foreground ml-2">
                                    (Degradation of reliability over time)
                                </span>
                            </p>
                        </div>

                        <!-- Maintenance Interval -->
                        <div class="flex flex-col gap-2">
                            <h4 class="text-sm font-semibold text-mono">Maintenance Interval</h4>
                            <p class="text-sm text-secondary-foreground">
                                {machine.maintenance_interval_days} days
                                <span class="text-xs text-muted-foreground ml-2">
                                    (Scheduled maintenance frequency)
                                </span>
                            </p>
                        </div>

                        <!-- Predictive Maintenance -->
                        <div class="flex flex-col gap-2">
                            <h4 class="text-sm font-semibold text-mono">Predictive Maintenance (PERT)</h4>
                            <div class="grid grid-cols-2 gap-4">
                                <div class="flex flex-col gap-1">
                                    <span class="text-xs text-muted-foreground">Cost Range</span>
                                    <p class="text-sm text-secondary-foreground">
                                        {formatCurrency(machine.min_predictive_maintenance_cost)} - {formatCurrency(machine.max_predictive_maintenance_cost)}
                                    </p>
                                    <p class="text-xs text-muted-foreground">
                                        Avg: {formatCurrency(machine.avg_predictive_maintenance_cost)}
                                    </p>
                                </div>
                                <div class="flex flex-col gap-1">
                                    <span class="text-xs text-muted-foreground">Time Range</span>
                                    <p class="text-sm text-secondary-foreground">
                                        {formatHours(machine.min_predictive_maintenance_time_hours)} - {formatHours(machine.max_predictive_maintenance_time_hours)}
                                    </p>
                                    <p class="text-xs text-muted-foreground">
                                        Avg: {formatHours(machine.avg_predictive_maintenance_time_hours)}
                                    </p>
                                </div>
                            </div>
                        </div>

                        <!-- Corrective Maintenance -->
                        <div class="flex flex-col gap-2">
                            <h4 class="text-sm font-semibold text-mono">Corrective Maintenance (PERT)</h4>
                            <div class="grid grid-cols-2 gap-4">
                                <div class="flex flex-col gap-1">
                                    <span class="text-xs text-muted-foreground">Cost Range</span>
                                    <p class="text-sm text-secondary-foreground">
                                        {formatCurrency(machine.min_corrective_maintenance_cost)} - {formatCurrency(machine.max_corrective_maintenance_cost)}
                                    </p>
                                    <p class="text-xs text-muted-foreground">
                                        Avg: {formatCurrency(machine.avg_corrective_maintenance_cost)}
                                    </p>
                                </div>
                                <div class="flex flex-col gap-1">
                                    <span class="text-xs text-muted-foreground">Time Range</span>
                                    <p class="text-sm text-secondary-foreground">
                                        {formatHours(machine.min_corrective_maintenance_time_hours)} - {formatHours(machine.max_corrective_maintenance_time_hours)}
                                    </p>
                                    <p class="text-xs text-muted-foreground">
                                        Avg: {formatHours(machine.avg_corrective_maintenance_time_hours)}
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
                        {#if employeeRequirements.length === 0}
                            <div class="kt-card bg-muted/20 border-dashed">
                                <div class="kt-card-content text-center py-8">
                                    <i class="ki-filled ki-profile-user text-2xl text-muted-foreground mb-2"></i>
                                    <p class="text-sm text-muted-foreground">No employee requirements</p>
                                    <p class="text-xs text-muted-foreground mt-1">This machine can operate without dedicated staff</p>
                                </div>
                            </div>
                        {:else}
                            <div>
                                <h5 class="text-sm font-medium text-mono mb-3">
                                    Required Employee Profiles ({employeeRequirements.length})
                                </h5>
                                <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
                                    {#each employeeRequirements as requirement}
                                        <div class="kt-card">
                                            <div class="kt-card-content p-4">
                                                <div class="flex items-start gap-3">
                                                    <!-- Employee Icon -->
                                                    <div class="flex items-center justify-center w-10 h-10 bg-accent/50 rounded-lg">
                                                        <i class="ki-filled ki-users text-lg text-muted-foreground"></i>
                                                    </div>
                                                    
                                                    <!-- Employee Info -->
                                                    <div class="flex-1 space-y-2">
                                                        <div class="flex items-center gap-2">
                                                            <h6 class="text-sm font-medium text-mono">
                                                                {requirement.profile_name}
                                                            </h6>
                                                            <span class="kt-badge kt-badge-outline kt-badge-primary kt-badge-xs">
                                                                {requirement.required_count}x required
                                                            </span>
                                                        </div>
                                                        
                                                        {#if requirement.profile_description}
                                                        <p class="text-xs text-muted-foreground">
                                                            {requirement.profile_description}
                                                        </p>
                                                        {/if}
                                                        
                                                        <div class="flex items-center gap-4">
                                                            <div class="flex items-center gap-1">
                                                                <span class="text-xs text-muted-foreground">Difficulty:</span>
                                                                <span class={getRecruitmentDifficultyBadgeClass(requirement.recruitment_difficulty) + ' kt-badge-xs'}>
                                                                    {requirement.recruitment_difficulty.replace('_', ' ')}
                                                                </span>
                                                            </div>
                                                            <div class="flex items-center gap-1">
                                                                <span class="text-xs text-muted-foreground">Avg Salary:</span>
                                                                <span class="text-xs font-medium">
                                                                    {formatCurrency(requirement.avg_salary)}/mo
                                                                </span>
                                                            </div>
                                                        </div>
                                                    </div>
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