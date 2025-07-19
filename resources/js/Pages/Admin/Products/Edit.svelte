<script>
    import AdminLayout from '../../Layouts/AdminLayout.svelte';
    import { onMount, tick } from 'svelte';
    import { router } from '@inertiajs/svelte';
    import Select2 from '../../Components/Forms/Select2.svelte';

    // Props from the server
    export let product;

    // Define breadcrumbs for this page
    const breadcrumbs = [
        {
            title: 'Products',
            url: route('admin.products.index'),
            active: false
        },
        {
            title: product.name || 'Product Details',
            url: route('admin.products.edit', { product: product.id }),
            active: true
        }
    ];
    
    const pageTitle = 'Edit Product';

    // Product types options
    const productTypes = [
        { value: 'raw_material', label: 'Raw Material' },
        { value: 'component', label: 'Component' },
        { value: 'finished_product', label: 'Finished Product' }
    ];

    // Form data - pre-populate with product data
    let form = {
        name: product.name || '',
        description: product.description || '',
        type: product.type || 'raw_material',
        elasticity_coefficient: product.elasticity_coefficient || 0,
        storage_cost: product.storage_cost || 0,
        shelf_life_days: product.shelf_life_days || '',
        has_expiration: product.has_expiration || false,
        measurement_unit: product.measurement_unit || '',
        need_technology: product.need_technology || false,
        technology_id: product.technology_id || null,
        file: null
    };

    // Form errors
    let errors = {};

    // Loading state
    let loading = false;

    // File preview
    let filePreview = null;

    // Selected technology
    let selectedTechnology = null;

    // Select2 component reference
    let technologySelectComponent;

    // Handle file input change
    function handleFileChange(event) {
        const file = event.target.files[0];
        if (file && file.type.startsWith('image/')) {
            form.file = file;
            const reader = new FileReader();
            reader.onload = function(e) {
                filePreview = e.target.result;
            };
            reader.readAsDataURL(file);
        }
    }

    // Handle expiration toggle
    function handleExpirationToggle() {
        form.has_expiration = !form.has_expiration;
        if (!form.has_expiration) {
            form.shelf_life_days = '';
        }
    }

    // Handle need technology toggle
    function handleNeedTechnologyToggle() {
        form.need_technology = !form.need_technology;
        if (!form.need_technology) {
            form.technology_id = null;
            selectedTechnology = null;
            if (technologySelectComponent) {
                technologySelectComponent.clear();
            }
        }
    }

    // Handle technology selection
    function handleTechnologySelect(event) {
        form.technology_id = event.detail.value;
        selectedTechnology = event.detail.data;
    }

    // Handle form submission
    function handleSubmit() {
        loading = true;
        
        const formData = new FormData();
        
        // Add form fields
        Object.keys(form).forEach(key => {
            if (form[key] !== null && form[key] !== '') {
                if (key === 'file' && form.file) {
                    formData.append(key, form.file);
                } else if (key === 'has_expiration' || key === 'need_technology') {
                    formData.append(key, form[key] ? '1' : '0');
                } else if (key !== 'file') {
                    formData.append(key, form[key]);
                }
            }
        });

        // Add _method for PATCH request
        formData.append('_method', 'PATCH');

        router.post(route('admin.products.update', { product: product.id }), formData, {
            onError: (err) => {
                errors = err;
                loading = false;
            },
            onFinish: () => {
                loading = false;
            }
        });
    }

    // Initialize components after mount
    onMount(async () => {
        await tick();
        
        // Pre-select technology if product has one
        if (product.technology && form.need_technology) {
            selectedTechnology = product.technology;
        }
    });
</script>

<svelte:head>
    <title>Business Game - {pageTitle}</title>
</svelte:head>

<AdminLayout {breadcrumbs} {pageTitle}>
    <!-- Container -->
    <div class="kt-container-fixed">
        <div class="grid gap-5 lg:gap-7.5">
            <!-- Product Header -->
            <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4">
                <div class="flex flex-col gap-1">
                    <h1 class="text-2xl font-bold text-mono">Edit Product</h1>
                    <p class="text-sm text-secondary-foreground">
                        Update product information and properties
                    </p>
                </div>
                <div class="flex items-center gap-3">
                    <a href="{route('admin.products.index')}" class="kt-btn kt-btn-outline">
                        <i class="ki-filled ki-arrow-left text-base"></i>
                        Back to Products
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
                            <!-- Product Name -->
                            <div class="flex flex-col gap-2">
                                <label class="text-sm font-medium text-mono" for="name">
                                    Product Name <span class="text-destructive">*</span>
                                </label>
                                <input
                                    id="name"
                                    type="text"
                                    class="kt-input {errors.name ? 'kt-input-error' : ''}"
                                    placeholder="Enter product name"
                                    bind:value={form.name}
                                />
                                {#if errors.name}
                                    <p class="text-sm text-destructive">{errors.name}</p>
                                {/if}
                            </div>

                            <!-- Description -->
                            <div class="flex flex-col gap-2">
                                <label class="text-sm font-medium text-mono" for="description">
                                    Description
                                </label>
                                <textarea
                                    id="description"
                                    class="kt-textarea {errors.description ? 'kt-input-error' : ''}"
                                    placeholder="Enter product description"
                                    rows="3"
                                    bind:value={form.description}
                                ></textarea>
                                {#if errors.description}
                                    <p class="text-sm text-destructive">{errors.description}</p>
                                {/if}
                            </div>

                            <!-- Product Type -->
                            <div class="flex flex-col gap-2">
                                <label class="text-sm font-medium text-mono" for="type">
                                    Product Type <span class="text-destructive">*</span>
                                </label>
                                <select
                                    id="type"
                                    class="kt-select {errors.type ? 'kt-select-error' : ''}"
                                    bind:value={form.type}
                                >
                                    {#each productTypes as type}
                                        <option value={type.value}>{type.label}</option>
                                    {/each}
                                </select>
                                {#if errors.type}
                                    <p class="text-sm text-destructive">{errors.type}</p>
                                {/if}
                            </div>

                            <!-- Measurement Unit -->
                            <div class="flex flex-col gap-2">
                                <label class="text-sm font-medium text-mono" for="measurement_unit">
                                    Measurement Unit <span class="text-destructive">*</span>
                                </label>
                                <input
                                    id="measurement_unit"
                                    type="text"
                                    class="kt-input {errors.measurement_unit ? 'kt-input-error' : ''}"
                                    placeholder="e.g., kg, liter, piece, ton"
                                    bind:value={form.measurement_unit}
                                />
                                {#if errors.measurement_unit}
                                    <p class="text-sm text-destructive">{errors.measurement_unit}</p>
                                {/if}
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Business Properties Card -->
                <div class="kt-card">
                    <div class="kt-card-header">
                        <h4 class="kt-card-title">Business Properties</h4>
                    </div>
                    <div class="kt-card-content">
                        <div class="grid gap-4">
                            <!-- Elasticity Coefficient -->
                            <div class="flex flex-col gap-2">
                                <label class="text-sm font-medium text-mono" for="elasticity_coefficient">
                                    Elasticity Coefficient <span class="text-destructive">*</span>
                                </label>
                                <input
                                    id="elasticity_coefficient"
                                    type="number"
                                    step="0.001"
                                    min="-1"
                                    max="1"
                                    class="kt-input {errors.elasticity_coefficient ? 'kt-input-error' : ''}"
                                    placeholder="1.0"
                                    bind:value={form.elasticity_coefficient}
                                />
                                <p class="text-xs text-secondary-foreground">
                                    Demand/Price sensitivity factor (negative = higer prices lead to lower demand and vice versa)
                                </p>
                                {#if errors.elasticity_coefficient}
                                    <p class="text-sm text-destructive">{errors.elasticity_coefficient}</p>
                                {/if}
                            </div>

                            <!-- Storage Cost -->
                            <div class="flex flex-col gap-2">
                                <label class="text-sm font-medium text-mono" for="storage_cost">
                                    Storage Cost <span class="text-destructive">*</span>
                                </label>
                                <input
                                    id="storage_cost"
                                    type="number"
                                    step="0.001"
                                    min="0"
                                    class="kt-input {errors.storage_cost ? 'kt-input-error' : ''}"
                                    placeholder="1.0"
                                    bind:value={form.storage_cost}
                                />
                                <p class="text-xs text-secondary-foreground">
                                    Storage cost per day per unit
                                </p>
                                {#if errors.storage_cost}
                                    <p class="text-sm text-destructive">{errors.storage_cost}</p>
                                {/if}
                            </div>

                            <!-- Has Expiration -->
                            <div class="flex flex-col gap-2">
                                <label class="text-sm font-medium text-mono">
                                    Product Expiration
                                </label>
                                <div class="flex items-center gap-3">
                                    <input 
                                        class="kt-switch" 
                                        type="checkbox" 
                                        id="has_expiration" 
                                        bind:checked={form.has_expiration}
                                    />
                                    <label class="kt-label cursor-pointer" for="has_expiration">
                                        This product has an expiration date
                                    </label>
                                </div>
                                {#if errors.has_expiration}
                                    <p class="text-sm text-destructive">{errors.has_expiration}</p>
                                {/if}
                            </div>

                            <!-- Shelf Life Days (conditional) -->
                            {#if form.has_expiration}
                                <div class="flex flex-col gap-2">
                                    <label class="text-sm font-medium text-mono" for="shelf_life_days">
                                        Shelf Life (Days) <span class="text-destructive">*</span>
                                    </label>
                                    <input
                                        id="shelf_life_days"
                                        type="number"
                                        min="1"
                                        class="kt-input {errors.shelf_life_days ? 'kt-input-error' : ''}"
                                        placeholder="30"
                                        bind:value={form.shelf_life_days}
                                    />
                                    {#if errors.shelf_life_days}
                                        <p class="text-sm text-destructive">{errors.shelf_life_days}</p>
                                    {/if}
                                </div>
                            {/if}

                            <!-- Need Technology -->
                            <div class="flex flex-col gap-2">
                                <label class="text-sm font-medium text-mono">
                                    This product requires a specific technology
                                </label>
                                <div class="flex items-center gap-3">
                                    <input 
                                        class="kt-switch" 
                                        type="checkbox" 
                                        id="need_technology" 
                                        bind:checked={form.need_technology}
                                    />
                                    <label class="kt-label cursor-pointer" for="need_technology">
                                        This product needs a technology to be produced
                                    </label>
                                </div>
                                {#if errors.need_technology}
                                    <p class="text-sm text-destructive">{errors.need_technology}</p>
                                {/if}
                            </div>

                            <!-- Technology Selection (conditional) -->
                            {#if form.need_technology}
                                <div class="flex flex-col gap-2">
                                    <label class="text-sm font-medium text-mono" for="technology_id">
                                        Technology <span class="text-destructive">*</span>
                                    </label>
                                    <Select2
                                        bind:this={technologySelectComponent}
                                        id="technology_id"
                                        placeholder="Search and select technology..."
                                        on:select={handleTechnologySelect}
                                        ajax={{
                                            url: route('admin.technologies.index'),
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
                                                    results: data.technologies.map(technology => ({
                                                        id: technology.id,
                                                        text: technology.name,
                                                        name: technology.name,
                                                        level: technology.level,
                                                        research_cost: technology.research_cost,
                                                        research_time_days: technology.research_time_days,
                                                        image_url: technology.image_url
                                                    }))
                                                };
                                            },
                                            cache: true
                                        }}
                                        templateResult={function(data) {
                                            if (data.loading) return data.text;
                                            
                                            const $elem = globalThis.$('<div class="flex items-center gap-3">' +
                                                '<div class="flex items-center justify-center size-12 shrink-0 rounded bg-accent/50">' +
                                                (data.image_url ? '<img src="' + data.image_url + '" alt="" class="size-10 object-cover rounded">' : '<i class="ki-filled ki-technology-1 text-lg text-muted-foreground"></i>') +
                                                '</div>' +
                                                '<div class="flex flex-col">' +
                                                '<span class="font-medium text-sm">' + data.name + '</span>' +
                                                '<span class="text-xs text-muted-foreground">Level: ' + data.level + ' | Cost: ' + data.research_cost + ' | Time: ' + data.research_time_days + ' days</span>' +
                                                '</div>' +
                                                '</div>');
                                            return $elem;
                                        }}
                                        templateSelection={function(data) {
                                            if (!data.id) return data.text;
                                            return data.name;
                                        }}
                                    />
                                    {#if errors.technology_id}
                                        <p class="text-sm text-destructive">{errors.technology_id}</p>
                                    {/if}
                                </div>
                            {/if}
                        </div>
                    </div>
                </div>

                <!-- Product Image Card -->
                <div class="kt-card">
                    <div class="kt-card-header">
                        <h4 class="kt-card-title">Product Image</h4>
                    </div>
                    <div class="kt-card-content">
                        <div class="grid gap-4">
                            <!-- Current Image -->
                            {#if product.image}
                                <div class="flex flex-col gap-2">
                                    <label class="text-sm font-medium text-mono">Current Image</label>
                                    <div class="flex items-center gap-4">
                                        <img 
                                            src={product.image_url} 
                                            alt="Current product image" 
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
                                {#if errors.file}
                                    <p class="text-sm text-destructive">{errors.file}</p>
                                {/if}
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Form Actions -->
                <div class="flex items-center justify-end gap-3">
                    <a href="{route('admin.products.index')}" class="kt-btn kt-btn-outline">
                        Cancel
                    </a>
                    <button
                        type="submit"
                        class="kt-btn kt-btn-primary"
                        disabled={loading}
                    >
                        {#if loading}
                            <i class="ki-outline ki-loading text-base animate-spin"></i>
                            Updating...
                        {:else}
                            <i class="ki-filled ki-check text-base"></i>
                            Update Product
                        {/if}
                    </button>
                </div>
            </form>
        </div>
    </div>
</AdminLayout> 