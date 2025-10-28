<script>
    import AdminLayout from '../../Layouts/AdminLayout.svelte';
    import { router } from '@inertiajs/svelte';
    import Select2 from '../../Components/Forms/Select2.svelte';

    // Define breadcrumbs
    const breadcrumbs = [
        {
            title: 'Machines',
            url: route('admin.machines.index'),
            active: false
        },
        {
            title: 'Create',
            url: route('admin.machines.create'),
            active: true
        }
    ];
    
    const pageTitle = 'Create Machine';

    // Form data
    let formData = {
        name: '',
        model: '',
        manufacturer: '',
        cost_to_acquire: '',
        loss_on_sale_days: '',
        operations_cost: '',
        carbon_footprint: '',
        quality_factor: '',
        min_speed: '',
        max_speed: '',
        reliability_decay_days: '',
        maintenance_interval_days: '',
        min_maintenance_cost: '',
        max_maintenance_cost: '',
        min_maintenance_time_days: '',
        max_maintenance_time_days: '',
        file: null,
        employee_profile_id: '',
        outputs: []
    };

    // Form errors
    let errors = {};

    // Loading state
    let loading = false;

    // File input reference
    let fileInput;

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
        
        // Check if profile is already added
        if (!formData.employee_profile_id) {
            formData.employee_profile_id = profileId;
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
            router.post(route('admin.machines.store'), formData, {
                onError: (err) => {
                    errors = err;
                    loading = false;
                },
                onSuccess: () => {
                    loading = false;
                }
            });
        } catch (error) {
            console.error('Error submitting form:', error);
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
                    <h1 class="text-2xl font-bold text-mono">Create Machine</h1>
                    <p class="text-sm text-secondary-foreground">
                        Add a new machine to your production equipment inventory
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
                                    Acquisition Cost (DZD) <span class="text-destructive">*</span>
                                </label>
                                <input 
                                    id="cost_to_acquire"
                                    type="number" 
                                    bind:value={formData.cost_to_acquire}
                                    class="kt-input {errors.cost_to_acquire ? 'kt-input-error' : ''}"
                                    placeholder="Enter acquisition cost"
                                    min="0"
                                    step="0.001"
                                    required
                                />
                                {#if errors.cost_to_acquire}
                                    <p class="text-sm text-destructive">{errors.cost_to_acquire}</p>
                                {/if}
                            </div>

                            <!-- Loss on Sale -->
                            <div class="flex flex-col gap-2">
                                <label class="text-sm font-medium text-mono" for="loss_on_sale_days">
                                    Loss on Sale (%) <span class="text-destructive">*</span>
                                </label>
                                <input 
                                    id="loss_on_sale_days"
                                    type="number" 
                                    bind:value={formData.loss_on_sale_days}
                                    class="kt-input {errors.loss_on_sale_days ? 'kt-input-error' : ''}"
                                    placeholder="Enter loss on sale (%) / day of cost to acquire"
                                    min="0"
                                    max="1"
                                    step="0.001"
                                    required
                                />
                                {#if errors.loss_on_sale_days}
                                    <p class="text-sm text-destructive">{errors.loss_on_sale_days}</p>
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
                            <!-- File Upload Section -->
                            <div class="flex flex-col gap-2">
                                <label class="text-sm font-medium text-mono" for="file">
                                    Upload Image
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
                            <!-- Speed Ranges -->
                            <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
                                <div class="flex flex-col gap-2">
                                    <label class="text-sm font-medium text-mono" for="min_speed">
                                        Minimum Speed (units/day) <span class="text-destructive">*</span>
                                    </label>
                                    <input 
                                        id="min_speed"
                                        type="number" 
                                        bind:value={formData.min_speed}
                                        class="kt-input {errors.min_speed ? 'kt-input-error' : ''}"
                                        placeholder="Enter minimum speed"
                                        min="0"
                                        step="0.001"
                                        required
                                    />
                                    {#if errors.min_speed}
                                        <p class="text-sm text-destructive">{errors.min_speed}</p>
                                    {/if}
                                </div>

                                <div class="flex flex-col gap-2">
                                    <label class="text-sm font-medium text-mono" for="max_speed">
                                        Maximum Speed (units/day) <span class="text-destructive">*</span>
                                    </label>
                                    <input 
                                        id="max_speed"
                                        type="number" 
                                        bind:value={formData.max_speed}
                                        class="kt-input {errors.max_speed ? 'kt-input-error' : ''}"
                                        placeholder="Enter maximum speed"
                                        min="0"
                                        step="0.001"
                                        required
                                    />
                                    {#if errors.max_speed}
                                        <p class="text-sm text-destructive">{errors.max_speed}</p>
                                    {/if}
                                </div>
                            </div>

                            <!-- Operations Cost, Carbon Footprint, and Quality Factor -->
                            <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
                                <div class="flex flex-col gap-2">
                                    <label class="text-sm font-medium text-mono" for="operations_cost">
                                        Operations Cost (DZD/week) <span class="text-destructive">*</span>
                                    </label>
                                    <input 
                                        id="operations_cost"
                                        type="number" 
                                        bind:value={formData.operations_cost}
                                        class="kt-input {errors.operations_cost ? 'kt-input-error' : ''}"
                                        placeholder="Enter operation cost"
                                        min="0"
                                        step="0.001"
                                        required
                                    />
                                    {#if errors.operations_cost}
                                        <p class="text-sm text-destructive">{errors.operations_cost}</p>
                                    {/if}
                                </div>

                                <div class="flex flex-col gap-2">
                                    <label class="text-sm font-medium text-mono" for="carbon_footprint">
                                        Carbon Footprint (kg CO2/unit) <span class="text-destructive">*</span>
                                    </label>
                                    <input 
                                        id="carbon_footprint"
                                        type="number" 
                                        bind:value={formData.carbon_footprint}
                                        class="kt-input {errors.carbon_footprint ? 'kt-input-error' : ''}"
                                        placeholder="Enter carbon footprint"
                                        min="0"
                                        step="any"
                                        required
                                    />
                                    {#if errors.carbon_footprint}
                                        <p class="text-sm text-destructive">{errors.carbon_footprint}</p>
                                    {/if}
                                </div>
                            </div>

                            <!-- Reliability -->
                            <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
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
                                        step="0.001"
                                        required
                                    />
                                    {#if errors.quality_factor}
                                        <p class="text-sm text-destructive">{errors.quality_factor}</p>
                                    {/if}
                                </div>

                                <div class="flex flex-col gap-2">
                                    <label class="text-sm font-medium text-mono" for="reliability_decay_days">
                                        Reliability Decay Days <span class="text-destructive">*</span>
                                    </label>
                                    <input 
                                        id="reliability_decay_days"
                                        type="number" 
                                        bind:value={formData.reliability_decay_days}
                                        class="kt-input {errors.reliability_decay_days ? 'kt-input-error' : ''}"
                                        placeholder="Enter failure chance"
                                        min="0"
                                        max="1"
                                        step="0.001"
                                        required
                                    />
                                    {#if errors.reliability_decay_days}
                                        <p class="text-sm text-destructive">{errors.reliability_decay_days}</p>
                                    {/if}
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Maintenance Card -->
                <div class="kt-card">
                    <div class="kt-card-header">
                        <h4 class="kt-card-title">Maintenance</h4>
                    </div>
                    <div class="kt-card-content">
                        <div class="grid gap-4">
                            <!-- Maintenance Costs -->
                            <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
                                <div class="flex flex-col gap-2">
                                    <label class="text-sm font-medium text-mono" for="min_maintenance_cost">
                                        Minimum Cost (DZD) <span class="text-destructive">*</span>
                                    </label>
                                    <input 
                                        id="min_maintenance_cost"
                                        type="number" 
                                        bind:value={formData.min_maintenance_cost}
                                        class="kt-input {errors.min_maintenance_cost ? 'kt-input-error' : ''}"
                                        placeholder="Enter minimum cost"
                                        min="0"
                                        step="0.001"
                                        required
                                    />
                                    {#if errors.min_maintenance_cost}
                                        <p class="text-sm text-destructive">{errors.min_maintenance_cost}</p>
                                    {/if}
                                </div>

                                <div class="flex flex-col gap-2">
                                    <label class="text-sm font-medium text-mono" for="max_maintenance_cost">
                                        Maximum Cost (DZD) <span class="text-destructive">*</span>
                                    </label>
                                    <input 
                                        id="max_maintenance_cost"
                                        type="number" 
                                        bind:value={formData.max_maintenance_cost}
                                        class="kt-input {errors.max_maintenance_cost ? 'kt-input-error' : ''}"
                                        placeholder="Enter maximum cost"
                                        min="0"
                                        step="0.001"
                                        required
                                    />
                                    {#if errors.max_maintenance_cost}
                                        <p class="text-sm text-destructive">{errors.max_maintenance_cost}</p>
                                    {/if}
                                </div>
                            </div>

                            <!-- Maintenance Times -->
                            <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
                                <div class="flex flex-col gap-2">
                                    <label class="text-sm font-medium text-mono" for="min_maintenance_time_days">
                                        Minimum Time (days) <span class="text-destructive">*</span>
                                    </label>
                                    <input 
                                        id="min_maintenance_time_days"
                                        type="number" 
                                        bind:value={formData.min_maintenance_time_days}
                                        class="kt-input {errors.min_maintenance_time_days ? 'kt-input-error' : ''}"
                                        placeholder="Enter minimum time"
                                        min="1"
                                        step="1"
                                        required
                                    />
                                    {#if errors.min_maintenance_time_days}
                                        <p class="text-sm text-destructive">{errors.min_maintenance_time_days}</p>
                                    {/if}
                                </div>

                                <div class="flex flex-col gap-2">
                                    <label class="text-sm font-medium text-mono" for="max_maintenance_time_days">
                                        Maximum Time (days) <span class="text-destructive">*</span>
                                    </label>
                                    <input 
                                        id="max_maintenance_time_days"
                                        type="number" 
                                        bind:value={formData.max_maintenance_time_days}
                                        class="kt-input {errors.max_maintenance_time_days ? 'kt-input-error' : ''}"
                                        placeholder="Enter maximum time"
                                        min="1"
                                        step="1"
                                        required
                                    />
                                    {#if errors.max_maintenance_time_days}
                                        <p class="text-sm text-destructive">{errors.max_maintenance_time_days}</p>
                                    {/if}
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Employee Requirements Card -->
                <div class="kt-card">
                    <div class="kt-card-header">
                        <h4 class="kt-card-title">Employee Profile</h4>
                    </div>
                    <div class="kt-card-content">
                        <div class="grid gap-4">
                            <!-- Employee Profile Selector -->
                            <div>
                                <label class="text-sm font-medium text-mono" for="employee-profile-selector">
                                    Add Employee Profile
                                </label>
                                <p class="text-xs text-secondary-foreground mb-2">
                                    Search and select employee profile required to operate this machine
                                </p>
                                <Select2
                                    bind:this={employeeProfileSelectComponent}
                                    id="employee-profile-selector"
                                    placeholder="Search and select employee profile..."
                                    on:select={handleEmployeeProfileSelect}
                                    ajax={{
                                        url: route('admin.employee-profiles.index'),
                                        dataType: 'json',
                                        time: 300,
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
                                                }))
                                            };
                                        },
                                        cache: true
                                    }}
                                    templateResult={function(data) {
                                        if (data.loading) return data.text;
                                        
                                        const $elem = globalThis.$('<div class="flex items-center gap-3">' +
                                            '<div class="flex flex-col">' +
                                            '<span class="font-medium text-sm">' + data.name + '</span>' +
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
                                        time: 300,
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
                            Creating...
                        {:else}
                            <i class="ki-filled ki-plus text-base"></i>
                            Create Machine
                        {/if}
                    </button>
                </div>
            </form>
        </div>
    </div>
</AdminLayout> 