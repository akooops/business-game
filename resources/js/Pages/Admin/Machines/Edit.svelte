<script>
    import AdminLayout from '../../Layouts/AdminLayout.svelte';
    import { router } from '@inertiajs/svelte';
    import Select2 from '../../Components/Forms/Select2.svelte';

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
            title: machine.name,
            url: route('admin.machines.show', { machine: machine.id }),
            active: false
        },
        {
            title: 'Edit',
            url: route('admin.machines.edit', { machine: machine.id }),
            active: true
        }
    ];
    
    const pageTitle = 'Edit Machine';

    // Form data - pre-populate with existing data
    let formData = {
        name: machine.name || '',
        model: machine.model || '',
        manufacturer: machine.manufacturer || '',
        cost_to_acquire: machine.cost_to_acquire || '',
        area_required: machine.area_required || '',
        setup_time_days: machine.setup_time_days || '',
        description: machine.description || '',
        hourly_energy_consumption: machine.hourly_energy_consumption || '',
        hourly_carbon_emissions: machine.hourly_carbon_emissions || '',
        quality_factor: machine.quality_factor || '',
        hourly_speed_min: machine.hourly_speed_min || '',
        hourly_speed_avg: machine.hourly_speed_avg || '',
        hourly_speed_max: machine.hourly_speed_max || '',
        hourly_failure_chance: machine.hourly_failure_chance || '',
        hourly_reliability_decay: machine.hourly_reliability_decay || '',
        maintenance_interval_days: machine.maintenance_interval_days || '',
        predictive_maintenance_cost_min: machine.predictive_maintenance_cost_min || '',
        predictive_maintenance_cost_avg: machine.predictive_maintenance_cost_avg || '',
        predictive_maintenance_cost_max: machine.predictive_maintenance_cost_max || '',
        predictive_maintenance_delay_min_hours: machine.predictive_maintenance_delay_min_hours || '',
        predictive_maintenance_delay_avg_hours: machine.predictive_maintenance_delay_avg_hours || '',
        predictive_maintenance_delay_max_hours: machine.predictive_maintenance_delay_max_hours || '',
        corrective_maintenance_cost_min: machine.corrective_maintenance_cost_min || '',
        corrective_maintenance_cost_avg: machine.corrective_maintenance_cost_avg || '',
        corrective_maintenance_cost_max: machine.corrective_maintenance_cost_max || '',
        corrective_maintenance_delay_min_hours: machine.corrective_maintenance_delay_min_hours || '',
        corrective_maintenance_delay_avg_hours: machine.corrective_maintenance_delay_avg_hours || '',
        corrective_maintenance_delay_max_hours: machine.corrective_maintenance_delay_max_hours || '',
        file: null,
        employee_profiles: (machine.employee_profiles || []).map(profile => ({
            employee_profile_id: profile.id,
            employee_profile_name: profile.name,
            required_count: profile.pivot.required_count
        })),
        outputs: (machine.products || []).map(product => ({
            product_id: product.id,
            product_name: product.name,
            product_type_name: product.type_name,
            product_image: product.image_url
        }))
    };

    // Form errors
    let errors = {};

    // Loading state
    let loading = false;

    // File preview
    let filePreview = null;

    // Handle file input change
    function handleFileChange(event) {
        const file = event.target.files[0];
        if (file && file.type.startsWith('image/')) {
            formData.file = file;
            const reader = new FileReader();
            reader.onload = function(e) {
                filePreview = e.target.result;
            };
            reader.readAsDataURL(file);
        }
    }

    // Handle employee profile selection
    function handleEmployeeProfileSelect(event) {
        const profileId = event.detail.value;
        const profileData = event.detail.data;
        
        // Check if profile is already added
        const exists = formData.employee_profiles.find(profile => profile.employee_profile_id === profileId);
        if (!exists) {
            formData.employee_profiles = [...formData.employee_profiles, { 
                employee_profile_id: profileId,
                employee_profile_name: profileData.name,
                required_count: 1
            }];
        }
        
        // Clear the select2
        if (employeeProfileSelectComponent) {
            employeeProfileSelectComponent.clear();
        }
    }

    // Handle product output selection
    function handleProductSelect(event) {
        const productId = event.detail.value;
        const productData = event.detail.data;
        
        // Check if product is already added
        const exists = formData.outputs.find(output => output.product_id === productId);
        if (!exists) {
            formData.outputs = [...formData.outputs, { 
                product_id: productId,
                product_name: productData.name,
                product_type_name: productData.type_name,
                product_image: productData.image_url || null
            }];
        }
        
        // Clear the select2
        if (productSelectComponent) {
            productSelectComponent.clear();
        }
    }

    // Remove employee profile
    function removeEmployeeProfile(index) {
        formData.employee_profiles = formData.employee_profiles.filter((_, i) => i !== index);
    }

    // Remove output
    function removeOutput(index) {
        formData.outputs = formData.outputs.filter((_, i) => i !== index);
    }

    // Handle form submission
    async function handleSubmit() {
        if (loading) return;

        loading = true;
        errors = {};

        try {
            formData._method = 'PATCH';

            router.post(route('admin.machines.update', { machine: machine.id }), formData, {
                onError: (err) => {
                    errors = err;
                    loading = false;
                },
                onSuccess: () => {
                    loading = false;
                }
            });
        } catch (error) {
            console.error('Error updating machine:', error);
            loading = false;
        }
    }

    // Select2 component references
    let employeeProfileSelectComponent;
    let productSelectComponent;
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
                    <h1 class="text-2xl font-bold text-mono">Edit Machine</h1>
                    <p class="text-sm text-secondary-foreground">
                        Update machine specifications, performance metrics and operational requirements
                    </p>
                </div>
                <div class="flex items-center gap-3">
                    <a href="{route('admin.machines.index')}" class="kt-btn kt-btn-outline">
                        <i class="ki-filled ki-black-left text-base"></i>
                        Back to Machines
                    </a>
                </div>
            </div>

            <!-- Form -->
            <form on:submit|preventDefault={handleSubmit} class="grid gap-5 lg:gap-7.5">
                <!-- Basic Information Card -->
                <div class="kt-card">
                    <div class="kt-card-header">
                        <h4 class="kt-card-title">Basic Information</h4>
                    </div>
                    <div class="kt-card-content">
                        <div class="grid gap-4">
                            <!-- Name -->
                            <div class="flex flex-col gap-2">
                                <label class="text-sm font-medium text-mono" for="name">
                                    Machine Name <span class="text-destructive">*</span>
                                </label>
                                <input 
                                    id="name"
                                    type="text" 
                                    bind:value={formData.name}
                                    class="kt-input {errors.name ? 'kt-input-error' : ''}"
                                    placeholder="Enter machine name"
                                    required
                                />
                                {#if errors.name}
                                    <p class="text-sm text-destructive">{errors.name}</p>
                                {/if}
                            </div>

                            <!-- Model and Manufacturer -->
                            <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
                                <div class="flex flex-col gap-2">
                                    <label class="text-sm font-medium text-mono" for="model">
                                        Model <span class="text-destructive">*</span>
                                    </label>
                                    <input 
                                        id="model"
                                        type="text" 
                                        bind:value={formData.model}
                                        class="kt-input {errors.model ? 'kt-input-error' : ''}"
                                        placeholder="Enter machine model"
                                        required
                                    />
                                    {#if errors.model}
                                        <p class="text-sm text-destructive">{errors.model}</p>
                                    {/if}
                                </div>

                                <div class="flex flex-col gap-2">
                                    <label class="text-sm font-medium text-mono" for="manufacturer">
                                        Manufacturer <span class="text-destructive">*</span>
                                    </label>
                                    <input 
                                        id="manufacturer"
                                        type="text" 
                                        bind:value={formData.manufacturer}
                                        class="kt-input {errors.manufacturer ? 'kt-input-error' : ''}"
                                        placeholder="Enter manufacturer name"
                                        required
                                    />
                                    {#if errors.manufacturer}
                                        <p class="text-sm text-destructive">{errors.manufacturer}</p>
                                    {/if}
                                </div>
                            </div>

                            <!-- Area Required and Setup Time -->
                            <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
                                <div class="flex flex-col gap-2">
                                    <label class="text-sm font-medium text-mono" for="area_required">
                                        Area Required (sq m) <span class="text-destructive">*</span>
                                    </label>
                                    <input 
                                        id="area_required"
                                        type="number" 
                                        bind:value={formData.area_required}
                                        class="kt-input {errors.area_required ? 'kt-input-error' : ''}"
                                        placeholder="Enter area in square meters"
                                        min="0"
                                        step="0.1"
                                        required
                                    />
                                    <p class="text-xs text-secondary-foreground">
                                        Floor space required for this machine
                                    </p>
                                    {#if errors.area_required}
                                        <p class="text-sm text-destructive">{errors.area_required}</p>
                                    {/if}
                                </div>

                                <div class="flex flex-col gap-2">
                                    <label class="text-sm font-medium text-mono" for="setup_time_days">
                                        Setup Time (days) <span class="text-destructive">*</span>
                                    </label>
                                    <input 
                                        id="setup_time_days"
                                        type="number" 
                                        bind:value={formData.setup_time_days}
                                        class="kt-input {errors.setup_time_days ? 'kt-input-error' : ''}"
                                        placeholder="Enter setup time in days"
                                        min="0"
                                        step="1"
                                        required
                                    />
                                    <p class="text-xs text-secondary-foreground">
                                        Time required to set up this machine
                                    </p>
                                    {#if errors.setup_time_days}
                                        <p class="text-sm text-destructive">{errors.setup_time_days}</p>
                                    {/if}
                                </div>
                            </div>

                            <!-- Description -->
                            <div class="flex flex-col gap-2">
                                <label class="text-sm font-medium text-mono" for="description">
                                    Description
                                </label>
                                <textarea 
                                    id="description"
                                    bind:value={formData.description}
                                    class="kt-textarea {errors.description ? 'kt-input-error' : ''}"
                                    placeholder="Enter machine description"
                                    rows="3"
                                ></textarea>
                                {#if errors.description}
                                    <p class="text-sm text-destructive">{errors.description}</p>
                                {/if}
                            </div>

                            <!-- Acquisition Cost -->
                            <div class="flex flex-col gap-2">
                                <label class="text-sm font-medium text-mono" for="cost_to_acquire">
                                    Acquisition Cost ($) <span class="text-destructive">*</span>
                                </label>
                                <input 
                                    id="cost_to_acquire"
                                    type="number" 
                                    bind:value={formData.cost_to_acquire}
                                    class="kt-input {errors.cost_to_acquire ? 'kt-input-error' : ''}"
                                    placeholder="Enter acquisition cost"
                                    min="0"
                                    step="0.01"
                                    required
                                />
                                {#if errors.cost_to_acquire}
                                    <p class="text-sm text-destructive">{errors.cost_to_acquire}</p>
                                {/if}
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Machine Image Card -->
                <div class="kt-card">
                    <div class="kt-card-header">
                        <h4 class="kt-card-title">Machine Image</h4>
                    </div>
                    <div class="kt-card-content">
                        <div class="grid gap-4">
                            <!-- Current Image -->
                            {#if machine.image}
                                <div class="flex flex-col gap-2">
                                    <label class="text-sm font-medium text-mono">Current Image</label>
                                    <div class="flex items-center gap-4">
                                        <img 
                                            src={machine.image_url} 
                                            alt="Current machine image" 
                                            class="w-32 h-32 object-cover rounded-lg border" 
                                        />
                                    </div>
                                </div>
                            {/if}

                            <!-- File Upload Section -->
                            <div class="flex flex-col gap-2">
                                <label class="text-sm font-medium text-mono" for="file">
                                    Upload New Image <span class="text-secondary-foreground">(Keep empty if you don't want to change it)</span>
                                </label>
                                <input
                                    id="file"
                                    type="file"
                                    class="kt-input"
                                    accept="image/*"
                                    on:change={handleFileChange}
                                />
                                <p class="text-xs text-secondary-foreground">
                                    Supported formats: JPG, JPEG, PNG, WebP
                                </p>
                                {#if filePreview}
                                    <div class="mt-2">
                                        <img src={filePreview} alt="Preview" class="w-32 h-32 object-cover rounded-lg border" />
                                    </div>
                                {/if}
                                {#if errors.image}
                                    <p class="text-sm text-destructive">{errors.image}</p>
                                {/if}
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
                        <div class="grid gap-4">
                            <!-- Energy Consumption, Carbon Emissions, and Quality Factor -->
                            <div class="grid grid-cols-1 lg:grid-cols-3 gap-4">
                                <div class="flex flex-col gap-2">
                                    <label class="text-sm font-medium text-mono" for="hourly_energy_consumption">
                                        Hourly Energy Consumption (kWh) <span class="text-destructive">*</span>
                                    </label>
                                    <input 
                                        id="hourly_energy_consumption"
                                        type="number" 
                                        bind:value={formData.hourly_energy_consumption}
                                        class="kt-input {errors.hourly_energy_consumption ? 'kt-input-error' : ''}"
                                        placeholder="Enter hourly energy consumption"
                                        min="0"
                                        step="0.01"
                                        required
                                    />
                                    {#if errors.hourly_energy_consumption}
                                        <p class="text-sm text-destructive">{errors.hourly_energy_consumption}</p>
                                    {/if}
                                </div>

                                <div class="flex flex-col gap-2">
                                    <label class="text-sm font-medium text-mono" for="hourly_carbon_emissions">
                                        Hourly Carbon Emissions (CO2) <span class="text-destructive">*</span>
                                    </label>
                                    <input 
                                        id="hourly_carbon_emissions"
                                        type="number" 
                                        bind:value={formData.hourly_carbon_emissions}
                                        class="kt-input {errors.hourly_carbon_emissions ? 'kt-input-error' : ''}"
                                        placeholder="Enter hourly carbon emissions"
                                        min="0"
                                        step="0.01"
                                        required
                                    />
                                    {#if errors.hourly_carbon_emissions}
                                        <p class="text-sm text-destructive">{errors.hourly_carbon_emissions}</p>
                                    {/if}
                                </div>

                                <div class="flex flex-col gap-2">
                                    <label class="text-sm font-medium text-mono" for="quality_factor">
                                        Quality Factor <span class="text-destructive">*</span>
                                    </label>
                                    <input 
                                        id="quality_factor"
                                        type="number" 
                                        bind:value={formData.quality_factor}
                                        class="kt-input {errors.quality_factor ? 'kt-input-error' : ''}"
                                        placeholder="Enter quality factor"
                                        min="0"
                                        max="1"
                                        step="0.1"
                                        required
                                    />
                                    {#if errors.quality_factor}
                                        <p class="text-sm text-destructive">{errors.quality_factor}</p>
                                    {/if}
                                </div>
                            </div>

                            <!-- Speed Ranges -->
                            <div class="grid grid-cols-1 lg:grid-cols-3 gap-4">
                                <div class="flex flex-col gap-2">
                                    <label class="text-sm font-medium text-mono" for="hourly_speed_min">
                                        Minimum Speed (units/h) <span class="text-destructive">*</span>
                                    </label>
                                    <input 
                                        id="hourly_speed_min"
                                        type="number" 
                                        bind:value={formData.hourly_speed_min}
                                        class="kt-input {errors.hourly_speed_min ? 'kt-input-error' : ''}"
                                        placeholder="Enter minimum speed"
                                        min="0"
                                        step="0.1"
                                        required
                                    />
                                    {#if errors.hourly_speed_min}
                                        <p class="text-sm text-destructive">{errors.hourly_speed_min}</p>
                                    {/if}
                                </div>

                                <div class="flex flex-col gap-2">
                                    <label class="text-sm font-medium text-mono" for="hourly_speed_avg">
                                        Average Speed (units/h) <span class="text-destructive">*</span>
                                    </label>
                                    <input 
                                        id="hourly_speed_avg"
                                        type="number" 
                                        bind:value={formData.hourly_speed_avg}
                                        class="kt-input {errors.hourly_speed_avg ? 'kt-input-error' : ''}"
                                        placeholder="Enter average speed"
                                        min="0"
                                        step="0.1"
                                        required
                                    />
                                    {#if errors.hourly_speed_avg}
                                        <p class="text-sm text-destructive">{errors.hourly_speed_avg}</p>
                                    {/if}
                                </div>

                                <div class="flex flex-col gap-2">
                                    <label class="text-sm font-medium text-mono" for="hourly_speed_max">
                                        Maximum Speed (units/h) <span class="text-destructive">*</span>
                                    </label>
                                    <input 
                                        id="hourly_speed_max"
                                        type="number" 
                                        bind:value={formData.hourly_speed_max}
                                        class="kt-input {errors.hourly_speed_max ? 'kt-input-error' : ''}"
                                        placeholder="Enter maximum speed"
                                        min="0"
                                        step="0.1"
                                        required
                                    />
                                    {#if errors.hourly_speed_max}
                                        <p class="text-sm text-destructive">{errors.hourly_speed_max}</p>
                                    {/if}
                                </div>
                            </div>

                            <!-- Reliability -->
                            <div class="grid grid-cols-1 lg:grid-cols-3 gap-4">
                                <div class="flex flex-col gap-2">
                                    <label class="text-sm font-medium text-mono" for="hourly_failure_chance">
                                        Hourly Failure Chance (0-1) <span class="text-destructive">*</span>
                                    </label>
                                    <input 
                                        id="hourly_failure_chance"
                                        type="number" 
                                        bind:value={formData.hourly_failure_chance}
                                        class="kt-input {errors.hourly_failure_chance ? 'kt-input-error' : ''}"
                                        placeholder="Enter failure chance"
                                        min="0"
                                        max="1"
                                        step="0.00001"
                                        required
                                    />
                                    {#if errors.hourly_failure_chance}
                                        <p class="text-sm text-destructive">{errors.hourly_failure_chance}</p>
                                    {/if}
                                </div>

                                <div class="flex flex-col gap-2">
                                    <label class="text-sm font-medium text-mono" for="hourly_reliability_decay">
                                        Hourly Reliability Decay (0-1) <span class="text-destructive">*</span>
                                    </label>
                                    <input 
                                        id="hourly_reliability_decay"
                                        type="number" 
                                        bind:value={formData.hourly_reliability_decay}
                                        class="kt-input {errors.hourly_reliability_decay ? 'kt-input-error' : ''}"
                                        placeholder="Enter reliability decay"
                                        min="0"
                                        max="1"
                                        step="0.00001"
                                        required
                                    />
                                    {#if errors.hourly_reliability_decay}
                                        <p class="text-sm text-destructive">{errors.hourly_reliability_decay}</p>
                                    {/if}
                                </div>

                                <div class="flex flex-col gap-2">
                                    <label class="text-sm font-medium text-mono" for="maintenance_interval_days">
                                        Maintenance Interval (days) <span class="text-destructive">*</span>
                                    </label>
                                    <input 
                                        id="maintenance_interval_days"
                                        type="number" 
                                        bind:value={formData.maintenance_interval_days}
                                        class="kt-input {errors.maintenance_interval_days ? 'kt-input-error' : ''}"
                                        placeholder="Enter maintenance interval"
                                        min="1"
                                        step="1"
                                        required
                                    />
                                    {#if errors.maintenance_interval_days}
                                        <p class="text-sm text-destructive">{errors.maintenance_interval_days}</p>
                                    {/if}
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Predictive Maintenance Card -->
                <div class="kt-card">
                    <div class="kt-card-header">
                        <h4 class="kt-card-title">Predictive Maintenance (PERT Distribution)</h4>
                    </div>
                    <div class="kt-card-content">
                        <div class="grid gap-4">
                            <!-- Predictive Maintenance Costs -->
                            <div class="grid grid-cols-1 lg:grid-cols-3 gap-4">
                                <div class="flex flex-col gap-2">
                                    <label class="text-sm font-medium text-mono" for="predictive_maintenance_cost_min">
                                        Minimum Cost ($) <span class="text-destructive">*</span>
                                    </label>
                                    <input 
                                        id="predictive_maintenance_cost_min"
                                        type="number" 
                                        bind:value={formData.predictive_maintenance_cost_min}
                                        class="kt-input {errors.predictive_maintenance_cost_min ? 'kt-input-error' : ''}"
                                        placeholder="Enter minimum cost"
                                        min="0"
                                        step="0.01"
                                        required
                                    />
                                    {#if errors.predictive_maintenance_cost_min}
                                        <p class="text-sm text-destructive">{errors.predictive_maintenance_cost_min}</p>
                                    {/if}
                                </div>

                                <div class="flex flex-col gap-2">
                                    <label class="text-sm font-medium text-mono" for="predictive_maintenance_cost_avg">
                                        Average Cost ($) <span class="text-destructive">*</span>
                                    </label>
                                    <input 
                                        id="predictive_maintenance_cost_avg"
                                        type="number" 
                                        bind:value={formData.predictive_maintenance_cost_avg}
                                        class="kt-input {errors.predictive_maintenance_cost_avg ? 'kt-input-error' : ''}"
                                        placeholder="Enter average cost"
                                        min="0"
                                        step="0.01"
                                        required
                                    />
                                    {#if errors.predictive_maintenance_cost_avg}
                                        <p class="text-sm text-destructive">{errors.predictive_maintenance_cost_avg}</p>
                                    {/if}
                                </div>

                                <div class="flex flex-col gap-2">
                                    <label class="text-sm font-medium text-mono" for="predictive_maintenance_cost_max">
                                        Maximum Cost ($) <span class="text-destructive">*</span>
                                    </label>
                                    <input 
                                        id="predictive_maintenance_cost_max"
                                        type="number" 
                                        bind:value={formData.predictive_maintenance_cost_max}
                                        class="kt-input {errors.predictive_maintenance_cost_max ? 'kt-input-error' : ''}"
                                        placeholder="Enter maximum cost"
                                        min="0"
                                        step="0.01"
                                        required
                                    />
                                    {#if errors.predictive_maintenance_cost_max}
                                        <p class="text-sm text-destructive">{errors.predictive_maintenance_cost_max}</p>
                                    {/if}
                                </div>
                            </div>

                            <!-- Predictive Maintenance Delays -->
                            <div class="grid grid-cols-1 lg:grid-cols-3 gap-4">
                                <div class="flex flex-col gap-2">
                                    <label class="text-sm font-medium text-mono" for="predictive_maintenance_delay_min_hours">
                                        Minimum Delay (hours) <span class="text-destructive">*</span>
                                    </label>
                                    <input 
                                        id="predictive_maintenance_delay_min_hours"
                                        type="number" 
                                        bind:value={formData.predictive_maintenance_delay_min_hours}
                                        class="kt-input {errors.predictive_maintenance_delay_min_hours ? 'kt-input-error' : ''}"
                                        placeholder="Enter minimum delay"
                                        min="0"
                                        step="1"
                                        required
                                    />
                                    {#if errors.predictive_maintenance_delay_min_hours}
                                        <p class="text-sm text-destructive">{errors.predictive_maintenance_delay_min_hours}</p>
                                    {/if}
                                </div>

                                <div class="flex flex-col gap-2">
                                    <label class="text-sm font-medium text-mono" for="predictive_maintenance_delay_avg_hours">
                                        Average Delay (hours) <span class="text-destructive">*</span>
                                    </label>
                                    <input 
                                        id="predictive_maintenance_delay_avg_hours"
                                        type="number" 
                                        bind:value={formData.predictive_maintenance_delay_avg_hours}
                                        class="kt-input {errors.predictive_maintenance_delay_avg_hours ? 'kt-input-error' : ''}"
                                        placeholder="Enter average delay"
                                        min="0"
                                        step="1"
                                        required
                                    />
                                    {#if errors.predictive_maintenance_delay_avg_hours}
                                        <p class="text-sm text-destructive">{errors.predictive_maintenance_delay_avg_hours}</p>
                                    {/if}
                                </div>

                                <div class="flex flex-col gap-2">
                                    <label class="text-sm font-medium text-mono" for="predictive_maintenance_delay_max_hours">
                                        Maximum Delay (hours) <span class="text-destructive">*</span>
                                    </label>
                                    <input 
                                        id="predictive_maintenance_delay_max_hours"
                                        type="number" 
                                        bind:value={formData.predictive_maintenance_delay_max_hours}
                                        class="kt-input {errors.predictive_maintenance_delay_max_hours ? 'kt-input-error' : ''}"
                                        placeholder="Enter maximum delay"
                                        min="0"
                                        step="1"
                                        required
                                    />
                                    {#if errors.predictive_maintenance_delay_max_hours}
                                        <p class="text-sm text-destructive">{errors.predictive_maintenance_delay_max_hours}</p>
                                    {/if}
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Corrective Maintenance Card -->
                <div class="kt-card">
                    <div class="kt-card-header">
                        <h4 class="kt-card-title">Corrective Maintenance (PERT Distribution)</h4>
                    </div>
                    <div class="kt-card-content">
                        <div class="grid gap-4">
                            <!-- Corrective Maintenance Costs -->
                            <div class="grid grid-cols-1 lg:grid-cols-3 gap-4">
                                <div class="flex flex-col gap-2">
                                    <label class="text-sm font-medium text-mono" for="corrective_maintenance_cost_min">
                                        Minimum Cost ($) <span class="text-destructive">*</span>
                                    </label>
                                    <input 
                                        id="corrective_maintenance_cost_min"
                                        type="number" 
                                        bind:value={formData.corrective_maintenance_cost_min}
                                        class="kt-input {errors.corrective_maintenance_cost_min ? 'kt-input-error' : ''}"
                                        placeholder="Enter minimum cost"
                                        min="0"
                                        step="0.01"
                                        required
                                    />
                                    {#if errors.corrective_maintenance_cost_min}
                                        <p class="text-sm text-destructive">{errors.corrective_maintenance_cost_min}</p>
                                    {/if}
                                </div>

                                <div class="flex flex-col gap-2">
                                    <label class="text-sm font-medium text-mono" for="corrective_maintenance_cost_avg">
                                        Average Cost ($) <span class="text-destructive">*</span>
                                    </label>
                                    <input 
                                        id="corrective_maintenance_cost_avg"
                                        type="number" 
                                        bind:value={formData.corrective_maintenance_cost_avg}
                                        class="kt-input {errors.corrective_maintenance_cost_avg ? 'kt-input-error' : ''}"
                                        placeholder="Enter average cost"
                                        min="0"
                                        step="0.01"
                                        required
                                    />
                                    {#if errors.corrective_maintenance_cost_avg}
                                        <p class="text-sm text-destructive">{errors.corrective_maintenance_cost_avg}</p>
                                    {/if}
                                </div>

                                <div class="flex flex-col gap-2">
                                    <label class="text-sm font-medium text-mono" for="corrective_maintenance_cost_max">
                                        Maximum Cost ($) <span class="text-destructive">*</span>
                                    </label>
                                    <input 
                                        id="corrective_maintenance_cost_max"
                                        type="number" 
                                        bind:value={formData.corrective_maintenance_cost_max}
                                        class="kt-input {errors.corrective_maintenance_cost_max ? 'kt-input-error' : ''}"
                                        placeholder="Enter maximum cost"
                                        min="0"
                                        step="0.01"
                                        required
                                    />
                                    {#if errors.corrective_maintenance_cost_max}
                                        <p class="text-sm text-destructive">{errors.corrective_maintenance_cost_max}</p>
                                    {/if}
                                </div>
                            </div>

                            <!-- Corrective Maintenance Delays -->
                            <div class="grid grid-cols-1 lg:grid-cols-3 gap-4">
                                <div class="flex flex-col gap-2">
                                    <label class="text-sm font-medium text-mono" for="corrective_maintenance_delay_min_hours">
                                        Minimum Delay (hours) <span class="text-destructive">*</span>
                                    </label>
                                    <input 
                                        id="corrective_maintenance_delay_min_hours"
                                        type="number" 
                                        bind:value={formData.corrective_maintenance_delay_min_hours}
                                        class="kt-input {errors.corrective_maintenance_delay_min_hours ? 'kt-input-error' : ''}"
                                        placeholder="Enter minimum delay"
                                        min="0"
                                        step="1"
                                        required
                                    />
                                    {#if errors.corrective_maintenance_delay_min_hours}
                                        <p class="text-sm text-destructive">{errors.corrective_maintenance_delay_min_hours}</p>
                                    {/if}
                                </div>

                                <div class="flex flex-col gap-2">
                                    <label class="text-sm font-medium text-mono" for="corrective_maintenance_delay_avg_hours">
                                        Average Delay (hours) <span class="text-destructive">*</span>
                                    </label>
                                    <input 
                                        id="corrective_maintenance_delay_avg_hours"
                                        type="number" 
                                        bind:value={formData.corrective_maintenance_delay_avg_hours}
                                        class="kt-input {errors.corrective_maintenance_delay_avg_hours ? 'kt-input-error' : ''}"
                                        placeholder="Enter average delay"
                                        min="0"
                                        step="1"
                                        required
                                    />
                                    {#if errors.corrective_maintenance_delay_avg_hours}
                                        <p class="text-sm text-destructive">{errors.corrective_maintenance_delay_avg_hours}</p>
                                    {/if}
                                </div>

                                <div class="flex flex-col gap-2">
                                    <label class="text-sm font-medium text-mono" for="corrective_maintenance_delay_max_hours">
                                        Maximum Delay (hours) <span class="text-destructive">*</span>
                                    </label>
                                    <input 
                                        id="corrective_maintenance_delay_max_hours"
                                        type="number" 
                                        bind:value={formData.corrective_maintenance_delay_max_hours}
                                        class="kt-input {errors.corrective_maintenance_delay_max_hours ? 'kt-input-error' : ''}"
                                        placeholder="Enter maximum delay"
                                        min="0"
                                        step="1"
                                        required
                                    />
                                    {#if errors.corrective_maintenance_delay_max_hours}
                                        <p class="text-sm text-destructive">{errors.corrective_maintenance_delay_max_hours}</p>
                                    {/if}
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
                            <!-- Employee Profile Selector -->
                            <div>
                                <label class="text-sm font-medium text-mono" for="employee-profile-selector">
                                    Add Employee Profile Requirements
                                </label>
                                <p class="text-xs text-secondary-foreground mb-2">
                                    Search and select employee profiles required to operate this machine
                                </p>
                                <Select2
                                    bind:this={employeeProfileSelectComponent}
                                    id="employee-profile-selector"
                                    placeholder="Search and select employee profiles..."
                                    on:select={handleEmployeeProfileSelect}
                                    ajax={{
                                        url: route('admin.employee-profiles.index'),
                                        dataType: 'json',
                                        delay: 300,
                                        data: function(params) {
                                            return {
                                                search: params.term,
                                                perPage: 10
                                            };
                                        },
                                        processResults: function(data) {
                                            return {
                                                results: data.employeeProfiles.map(profile => ({
                                                    id: profile.id,
                                                    text: profile.name,
                                                    name: profile.name,
                                                    recruitment_difficulty: profile.recruitment_difficulty
                                                }))
                                            };
                                        },
                                        cache: true
                                    }}
                                    templateResult={function(data) {
                                        if (data.loading) return data.text;
                                        
                                        const $elem = globalThis.$('<div class="flex items-center gap-3">' +
                                            '<div class="flex items-center justify-center size-12 shrink-0 rounded bg-accent/50">' +
                                            '<i class="ki-filled ki-profile-circle text-lg text-muted-foreground"></i>' +
                                            '</div>' +
                                            '<div class="flex flex-col">' +
                                            '<span class="font-medium text-sm">' + data.name + '</span>' +
                                            '<span class="text-xs text-muted-foreground">Difficulty: ' + data.recruitment_difficulty + '</span>' +
                                            '</div>' +
                                            '</div>');
                                        return $elem;
                                    }}
                                    templateSelection={function(data) {
                                        if (!data.id) return data.text;
                                        return data.name;
                                    }}
                                />
                            </div>
                            
                            <!-- Selected Employee Profiles -->
                            <div>
                                {#if formData.employee_profiles.length === 0}
                                    <div class="kt-card bg-muted/20 border-dashed">
                                        <div class="kt-card-content text-center py-8">
                                            <i class="ki-filled ki-profile-circle text-2xl text-muted-foreground mb-2"></i>
                                            <p class="text-sm text-muted-foreground">No employee profiles required yet</p>
                                            <p class="text-xs text-muted-foreground mt-1">Use the search above to add employee requirements</p>
                                        </div>
                                    </div>
                                {:else}
                                    <div class="space-y-3">
                                        {#each formData.employee_profiles as profile, index}
                                            <div class="kt-card border">
                                                <div class="kt-card-content p-4">
                                                    <div class="flex items-center gap-4">
                                                        <div class="flex items-center gap-3 flex-1">
                                                            <div class="flex items-center justify-center size-10 shrink-0 rounded bg-accent/50">
                                                                <i class="ki-filled ki-profile-circle text-lg text-muted-foreground"></i>
                                                            </div>
                                                            <div class="flex flex-col">
                                                                <span class="text-sm font-medium text-mono">{profile.employee_profile_name}</span>
                                                            </div>
                                                        </div>
                                                        
                                                        <div class="flex items-center gap-2">
                                                            <label class="text-sm font-medium text-mono" for="required-count-{index}">
                                                                Required Count:
                                                            </label>
                                                            <input 
                                                                id="required-count-{index}"
                                                                type="number" 
                                                                bind:value={profile.required_count}
                                                                class="kt-input w-20 {errors[`employee_profiles.${index}.required_count`] ? 'kt-input-error' : ''}"
                                                                min="1"
                                                                required
                                                            />
                                                        </div>
                                                        
                                                        <button 
                                                            type="button" 
                                                            class="kt-btn kt-btn-sm kt-btn-icon kt-btn-ghost text-destructive"
                                                            on:click={() => removeEmployeeProfile(index)}
                                                            title="Remove requirement"
                                                        >
                                                            <i class="ki-filled ki-trash"></i>
                                                        </button>
                                                    </div>
                                                </div>
                                            </div>
                                        {/each}
                                    </div>
                                {/if}

                                {#if errors.employee_profiles}
                                    <p class="text-sm text-destructive mt-2">{errors.employee_profiles}</p>
                                {/if}
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
                            <!-- Product Selector -->
                            <div>
                                <label class="text-sm font-medium text-mono" for="product-selector">
                                    Add Product Output
                                </label>
                                <p class="text-xs text-secondary-foreground mb-2">
                                    Search and select products that this machine can produce
                                </p>
                                <Select2
                                    bind:this={productSelectComponent}
                                    id="product-selector"
                                    placeholder="Search and select products to add..."
                                    on:select={handleProductSelect}
                                    ajax={{
                                        url: route('admin.products.index'),
                                        dataType: 'json',
                                        delay: 300,
                                        data: function(params) {
                                            return {
                                                search: params.term,
                                                perPage: 10
                                            };
                                        },
                                        processResults: function(data) {
                                            return {
                                                results: data.products.map(product => ({
                                                    id: product.id,
                                                    text: `${product.name}`,
                                                    name: product.name,
                                                    type: product.type,
                                                    type_name: product.type_name,
                                                    image_url: product.image_url
                                                }))
                                            };
                                        },
                                        cache: true
                                    }}
                                    templateResult={function(data) {
                                        if (data.loading) return data.text;
                                        
                                        const $elem = globalThis.$('<div class="flex items-center gap-3">' +
                                            '<div class="flex items-center justify-center size-12 shrink-0 rounded bg-accent/50">' +
                                            (data.image_url ? '<img src="' + data.image_url + '" alt="" class="size-10 object-cover rounded">' : '<i class="ki-filled ki-abstract-26 text-lg text-muted-foreground"></i>') +
                                            '</div>' +
                                            '<div class="flex flex-col">' +
                                            '<span class="font-medium text-sm">' + data.name + '</span>' +
                                            '<span class="kt-badge kt-badge-outline kt-badge-primary kt-badge-sm w-fit mt-1">' + data.type_name + '</span>' +
                                            '</div>' +
                                            '</div>');
                                        return $elem;
                                    }}
                                    templateSelection={function(data) {
                                        if (!data.id) return data.text;
                                        return data.name;
                                    }}
                                />
                            </div>
                            
                            <!-- Selected Products Grid -->
                            <div>
                                {#if formData.outputs.length === 0}
                                    <div class="kt-card bg-muted/20 border-dashed">
                                        <div class="kt-card-content text-center py-8">
                                            <i class="ki-filled ki-abstract-26 text-2xl text-muted-foreground mb-2"></i>
                                            <p class="text-sm text-muted-foreground">No products selected yet</p>
                                            <p class="text-xs text-muted-foreground mt-1">Use the search above to add product outputs</p>
                                        </div>
                                    </div>
                                {:else}
                                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-4">
                                        {#each formData.outputs as output, index}
                                            <div class="kt-card relative">
                                                <div class="kt-card-content flex flex-col p-3 gap-3">
                                                    <!-- Remove button -->
                                                    <button 
                                                        type="button" 
                                                        class="absolute top-2 right-2 kt-btn kt-btn-xs kt-btn-icon kt-btn-ghost text-destructive hover:bg-destructive hover:text-destructive-foreground z-10"
                                                        on:click={() => removeOutput(index)}
                                                        title="Remove product"
                                                    >
                                                        <i class="ki-filled ki-cross text-xs"></i>
                                                    </button>
                                                    
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
                                                        <span class="kt-badge kt-badge-outline kt-badge-primary kt-badge-sm w-fit">
                                                            {output.product_type_name}
                                                        </span>
                                                    </div>
                                                </div>
                                            </div>
                                        {/each}
                                    </div>
                                {/if}

                                {#if errors.outputs}
                                    <p class="text-sm text-destructive mt-2">{errors.outputs}</p>
                                {/if}
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Form Actions -->
                <div class="flex items-center justify-end gap-3">
                    <a href="{route('admin.machines.index')}" class="kt-btn kt-btn-outline">
                        Cancel
                    </a>
                    <button type="submit" class="kt-btn kt-btn-primary" disabled={loading}>
                        {#if loading}
                            <i class="ki-outline ki-loading text-base animate-spin"></i>
                            Updating...
                        {:else}
                            <i class="ki-filled ki-check text-base"></i>
                            Update Machine
                        {/if}
                    </button>
                </div>
            </form>
        </div>
    </div>
</AdminLayout> 