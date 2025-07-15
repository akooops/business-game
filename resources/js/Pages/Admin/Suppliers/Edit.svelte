<script>
    import AdminLayout from '../../Layouts/AdminLayout.svelte';
    import { onMount, tick } from 'svelte';
    import { router } from '@inertiajs/svelte';
    import Select2 from '../../Components/Forms/Select2.svelte';

    // Props from the server
    export let supplier;

    // Define breadcrumbs for this page
    const breadcrumbs = [
        {
            title: 'Suppliers',
            url: route('admin.suppliers.index'),
            active: false
        },
        {
            title: supplier.name || 'Supplier Details',
            url: route('admin.suppliers.edit', { supplier: supplier.id }),
            active: true
        }
    ];
    
    const pageTitle = 'Edit Supplier';

    // Form data - pre-populate with supplier data
    let form = {
        name: supplier.name || '',
        is_international: supplier.is_international || false,
        min_shipping_cost: supplier.min_shipping_cost || '',
        max_shipping_cost: supplier.max_shipping_cost || '',
        avg_shipping_cost: supplier.avg_shipping_cost || '',
        min_shipping_time_days: supplier.min_shipping_time_days || '',
        avg_shipping_time_days: supplier.avg_shipping_time_days || '',
        max_shipping_time_days: supplier.max_shipping_time_days || '',
        carbon_footprint: supplier.carbon_footprint || '',
        country_id: supplier.country_id || null,
        wilaya_id: supplier.wilaya_id || null,
        products: [],
        file: null
    };

    // Form errors
    let errors = {};

    // Loading state
    let loading = false;

    // File preview
    let filePreview = null;

    // Selected location data
    let selectedCountry = supplier.country || null;
    let selectedWilaya = supplier.wilaya || null;

    // Select2 component references
    let countrySelectComponent;
    let wilayaSelectComponent;
    let productSelectComponent;

    // Products array for the supplier - pre-populate with existing data
    let supplierProducts = (supplier.products || []).map(product => ({
        product_id: product.id,
        product_name: product.name,
        min_sale_price: product.pivot.min_sale_price || '',
        avg_sale_price: product.pivot.avg_sale_price || '',
        max_sale_price: product.pivot.max_sale_price || '',
        minimum_order_qty: product.pivot.minimum_order_qty || ''
    }));

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

    // Handle international toggle
    function handleInternationalToggle() {
        form.is_international = !form.is_international;
        // Clear location data when switching
        form.country_id = null;
        form.wilaya_id = null;
        selectedCountry = null;
        selectedWilaya = null;
        if (countrySelectComponent) {
            countrySelectComponent.clear();
        }
        if (wilayaSelectComponent) {
            wilayaSelectComponent.clear();
        }
    }

    // Handle country selection
    function handleCountrySelect(event) {
        form.country_id = event.detail.value;
        selectedCountry = event.detail.data;
    }

    // Handle wilaya selection
    function handleWilayaSelect(event) {
        form.wilaya_id = event.detail.value;
        selectedWilaya = event.detail.data;
    }

    // Handle product selection
    function handleProductSelect(event) {
        const selectedProduct = event.detail.data;
        
        // Check if product is already added
        const existingProduct = supplierProducts.find(p => p.product_id === selectedProduct.id);
        if (existingProduct) {
            alert('This product is already added to the supplier.');
            if (productSelectComponent) {
                productSelectComponent.clear();
            }
            return;
        }

        // Add product to the list
        supplierProducts = [...supplierProducts, {
            product_id: selectedProduct.id,
            product_name: selectedProduct.name,
            min_sale_price: '',
            avg_sale_price: '',
            max_sale_price: '',
            minimum_order_qty: ''
        }];

        // Clear the select
        if (productSelectComponent) {
            productSelectComponent.clear();
        }
    }

    // Remove product from supplier
    function removeProduct(index) {
        supplierProducts = supplierProducts.filter((_, i) => i !== index);
    }

    // Update product data
    function updateProduct(index, field, value) {
        supplierProducts[index][field] = value;
        supplierProducts = [...supplierProducts]; // Trigger reactivity
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
                } else if (key === 'is_international') {
                    formData.append(key, form[key] ? '1' : '0');
                } else if (key !== 'file' && key !== 'products') {
                    formData.append(key, form[key]);
                }
            }
        });

        // Add products array
        supplierProducts.forEach((product, index) => {
            formData.append(`products[${index}][product_id]`, product.product_id);
            formData.append(`products[${index}][min_sale_price]`, product.min_sale_price);
            formData.append(`products[${index}][avg_sale_price]`, product.avg_sale_price);
            formData.append(`products[${index}][max_sale_price]`, product.max_sale_price);
            formData.append(`products[${index}][minimum_order_qty]`, product.minimum_order_qty);
        });

        // Add _method for PATCH request
        formData.append('_method', 'PATCH');

        router.post(route('admin.suppliers.update', { supplier: supplier.id }), formData, {
            onError: (err) => {
                errors = err;
                console.log(errors);
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
    });
</script>

<svelte:head>
    <title>Business Game - {pageTitle}</title>
</svelte:head>

<AdminLayout {breadcrumbs} {pageTitle}>
    <!-- Container -->
    <div class="kt-container-fixed">
        <div class="grid gap-5 lg:gap-7.5">
            <!-- Supplier Header -->
            <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4">
                <div class="flex flex-col gap-1">
                    <h1 class="text-2xl font-bold text-mono">Edit Supplier</h1>
                    <p class="text-sm text-secondary-foreground">
                        Update supplier information and procurement details
                    </p>
                </div>
                <div class="flex items-center gap-3">
                    <a href="{route('admin.suppliers.index')}" class="kt-btn kt-btn-outline">
                        <i class="ki-filled ki-arrow-left text-base"></i>
                        Back to Suppliers
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
                            <!-- Supplier Name -->
                            <div class="flex flex-col gap-2">
                                <label class="text-sm font-medium text-mono" for="name">
                                    Supplier Name <span class="text-destructive">*</span>
                                </label>
                                <input
                                    id="name"
                                    type="text"
                                    class="kt-input {errors.name ? 'kt-input-error' : ''}"
                                    placeholder="Enter supplier name"
                                    bind:value={form.name}
                                />
                                {#if errors.name}
                                    <p class="text-sm text-destructive">{errors.name}</p>
                                {/if}
                            </div>

                            <!-- Supplier Type -->
                            <div class="flex flex-col gap-2">
                                <label class="text-sm font-medium text-mono">
                                    Supplier Type
                                </label>
                                <div class="flex items-center gap-3">
                                    <input 
                                        class="kt-switch" 
                                        type="checkbox" 
                                        id="is_international" 
                                        bind:checked={form.is_international}
                                    />
                                    <label class="kt-label cursor-pointer" for="is_international">
                                        This is an international supplier
                                    </label>
                                </div>
                                {#if errors.is_international}
                                    <p class="text-sm text-destructive">{errors.is_international}</p>
                                {/if}
                            </div>

                            <!-- Location Selection -->
                            {#if form.is_international}
                                <!-- Country Selection -->
                                <div class="flex flex-col gap-2">
                                    <label class="text-sm font-medium text-mono" for="country_id">
                                        Country <span class="text-destructive">*</span>
                                    </label>
                                    <Select2
                                        bind:this={countrySelectComponent}
                                        id="country_id"
                                        placeholder="Search and select country..."
                                        on:select={handleCountrySelect}
                                        ajax={{
                                            url: route('admin.countries.index'),
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
                                                    results: data.countries.map(country => ({
                                                        id: country.id,
                                                        text: country.name,
                                                        name: country.name
                                                    }))
                                                };
                                            },
                                            cache: true
                                        }}
                                        templateResult={function(data) {
                                            if (data.loading) return data.text;
                                            
                                            const $elem = globalThis.$('<div class="flex items-center gap-2">' +
                                                '<i class="ki-filled ki-globe text-sm text-muted-foreground"></i>' +
                                                '<span class="font-medium text-sm">' + data.name + '</span>' +
                                                '</div>');
                                            return $elem;
                                        }}
                                        templateSelection={function(data) {
                                            if (!data.id) return data.text;
                                            return data.name;
                                        }}
                                    />
                                    {#if errors.country_id}
                                        <p class="text-sm text-destructive">{errors.country_id}</p>
                                    {/if}
                                </div>
                            {:else}
                                <!-- Wilaya Selection -->
                                <div class="flex flex-col gap-2">
                                    <label class="text-sm font-medium text-mono" for="wilaya_id">
                                        Wilaya <span class="text-destructive">*</span>
                                    </label>
                                    <Select2
                                        bind:this={wilayaSelectComponent}
                                        id="wilaya_id"
                                        placeholder="Search and select wilaya..."
                                        on:select={handleWilayaSelect}
                                        ajax={{
                                            url: route('admin.wilayas.index'),
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
                                                    results: data.wilayas.map(wilaya => ({
                                                        id: wilaya.id,
                                                        text: wilaya.name,
                                                        name: wilaya.name
                                                    }))
                                                };
                                            },
                                            cache: true
                                        }}
                                        templateResult={function(data) {
                                            if (data.loading) return data.text;
                                            
                                            const $elem = globalThis.$('<div class="flex items-center gap-2">' +
                                                '<i class="ki-filled ki-map-pin text-sm text-muted-foreground"></i>' +
                                                '<span class="font-medium text-sm">' + data.name + '</span>' +
                                                '</div>');
                                            return $elem;
                                        }}
                                        templateSelection={function(data) {
                                            if (!data.id) return data.text;
                                            return data.name;
                                        }}
                                    />
                                    {#if errors.wilaya_id}
                                        <p class="text-sm text-destructive">{errors.wilaya_id}</p>
                                    {/if}
                                </div>
                            {/if}
                        </div>
                    </div>
                </div>

                <!-- Shipping Information Card -->
                <div class="kt-card">
                    <div class="kt-card-header">
                        <h4 class="kt-card-title">Shipping Information</h4>
                    </div>
                    <div class="kt-card-content">
                        <div class="grid gap-4">
                            <!-- Shipping Costs -->
                            <div class="grid grid-cols-3 gap-4">
                                <!-- Min Shipping Cost -->
                                <div class="flex flex-col gap-2">
                                    <label class="text-sm font-medium text-mono" for="min_shipping_cost">
                                        Min Shipping Cost <span class="text-destructive">*</span>
                                    </label>
                                    <input
                                        id="min_shipping_cost"
                                        type="number"
                                        step="0.001"
                                        min="0"
                                        class="kt-input {errors.min_shipping_cost ? 'kt-input-error' : ''}"
                                        placeholder="0.000"
                                        bind:value={form.min_shipping_cost}
                                    />
                                    {#if errors.min_shipping_cost}
                                        <p class="text-sm text-destructive">{errors.min_shipping_cost}</p>
                                    {/if}
                                </div>

                                <!-- Avg Shipping Cost -->
                                <div class="flex flex-col gap-2">
                                    <label class="text-sm font-medium text-mono" for="avg_shipping_cost">
                                        Avg Shipping Cost <span class="text-destructive">*</span>
                                    </label>
                                    <input
                                        id="avg_shipping_cost"
                                        type="number"
                                        step="0.001"
                                        min="0"
                                        class="kt-input {errors.avg_shipping_cost ? 'kt-input-error' : ''}"
                                        placeholder="0.000"
                                        bind:value={form.avg_shipping_cost}
                                    />
                                    {#if errors.avg_shipping_cost}
                                        <p class="text-sm text-destructive">{errors.avg_shipping_cost}</p>
                                    {/if}
                                </div>

                                <!-- Max Shipping Cost -->
                                <div class="flex flex-col gap-2">
                                    <label class="text-sm font-medium text-mono" for="max_shipping_cost">
                                        Max Shipping Cost <span class="text-destructive">*</span>
                                    </label>
                                    <input
                                        id="max_shipping_cost"
                                        type="number"
                                        step="0.001"
                                        min="0"
                                        class="kt-input {errors.max_shipping_cost ? 'kt-input-error' : ''}"
                                        placeholder="0.000"
                                        bind:value={form.max_shipping_cost}
                                    />
                                    {#if errors.max_shipping_cost}
                                        <p class="text-sm text-destructive">{errors.max_shipping_cost}</p>
                                    {/if}
                                </div>
                            </div>

                            <!-- Shipping Times -->
                            <div class="grid grid-cols-3 gap-4">
                                <!-- Min Shipping Time -->
                                <div class="flex flex-col gap-2">
                                    <label class="text-sm font-medium text-mono" for="min_shipping_time_days">
                                        Min Shipping Time (Days) <span class="text-destructive">*</span>
                                    </label>
                                    <input
                                        id="min_shipping_time_days"
                                        type="number"
                                        min="1"
                                        class="kt-input {errors.min_shipping_time_days ? 'kt-input-error' : ''}"
                                        placeholder="1"
                                        bind:value={form.min_shipping_time_days}
                                    />
                                    {#if errors.min_shipping_time_days}
                                        <p class="text-sm text-destructive">{errors.min_shipping_time_days}</p>
                                    {/if}
                                </div>

                                <!-- Avg Shipping Time -->
                                <div class="flex flex-col gap-2">
                                    <label class="text-sm font-medium text-mono" for="avg_shipping_time_days">
                                        Avg Shipping Time (Days) <span class="text-destructive">*</span>
                                    </label>
                                    <input
                                        id="avg_shipping_time_days"
                                        type="number"
                                        min="1"
                                        class="kt-input {errors.avg_shipping_time_days ? 'kt-input-error' : ''}"
                                        placeholder="1"
                                        bind:value={form.avg_shipping_time_days}
                                    />
                                    {#if errors.avg_shipping_time_days}
                                        <p class="text-sm text-destructive">{errors.avg_shipping_time_days}</p>
                                    {/if}
                                </div>

                                <!-- Max Shipping Time -->
                                <div class="flex flex-col gap-2">
                                    <label class="text-sm font-medium text-mono" for="max_shipping_time_days">
                                        Max Shipping Time (Days) <span class="text-destructive">*</span>
                                    </label>
                                    <input
                                        id="max_shipping_time_days"
                                        type="number"
                                        min="1"
                                        class="kt-input {errors.max_shipping_time_days ? 'kt-input-error' : ''}"
                                        placeholder="1"
                                        bind:value={form.max_shipping_time_days}
                                    />
                                    {#if errors.max_shipping_time_days}
                                        <p class="text-sm text-destructive">{errors.max_shipping_time_days}</p>
                                    {/if}
                                </div>
                            </div>

                            <!-- Carbon Footprint -->
                            <div class="flex flex-col gap-2">
                                <label class="text-sm font-medium text-mono" for="carbon_footprint">
                                    Carbon Footprint <span class="text-destructive">*</span>
                                </label>
                                <input
                                    id="carbon_footprint"
                                    type="number"
                                    step="0.001"
                                    min="0"
                                    class="kt-input {errors.carbon_footprint ? 'kt-input-error' : ''}"
                                    placeholder="0.000"
                                    bind:value={form.carbon_footprint}
                                />
                                <p class="text-xs text-secondary-foreground">
                                    Environmental impact measurement
                                </p>
                                {#if errors.carbon_footprint}
                                    <p class="text-sm text-destructive">{errors.carbon_footprint}</p>
                                {/if}
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Products Card -->
                <div class="kt-card">
                    <div class="kt-card-header">
                        <h4 class="kt-card-title">Supplier Products</h4>
                    </div>
                    <div class="kt-card-content">
                        <div class="grid gap-4">
                            <!-- Add Product -->
                            <div class="flex flex-col gap-2">
                                <label class="text-sm font-medium text-mono" for="product_select">
                                    Add Product <span class="text-destructive">*</span>
                                </label>
                                <Select2
                                    bind:this={productSelectComponent}
                                    id="product_select"
                                    placeholder="Search and select product..."
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
                                                    text: product.name,
                                                    name: product.name,
                                                    type: product.type,
                                                    measurement_unit: product.measurement_unit
                                                }))
                                            };
                                        },
                                        cache: true
                                    }}
                                    templateResult={function(data) {
                                        if (data.loading) return data.text;
                                        
                                        const $elem = globalThis.$('<div class="flex items-center gap-3">' +
                                            '<div class="flex items-center justify-center size-12 shrink-0 rounded bg-accent/50">' +
                                            '<i class="ki-filled ki-package text-lg text-muted-foreground"></i>' +
                                            '</div>' +
                                            '<div class="flex flex-col">' +
                                            '<span class="font-medium text-sm">' + data.name + '</span>' +
                                            '<span class="text-xs text-muted-foreground">' + data.type + ' | ' + data.measurement_unit + '</span>' +
                                            '</div>' +
                                            '</div>');
                                        return $elem;
                                    }}
                                    templateSelection={function(data) {
                                        if (!data.id) return data.text;
                                        return data.name;
                                    }}
                                />
                                {#if errors.products}
                                    <p class="text-sm text-destructive">{errors.products}</p>
                                {/if}
                            </div>

                            <!-- Products List -->
                            {#if supplierProducts.length > 0}
                                <div class="space-y-4">
                                    <h5 class="text-sm font-medium text-mono">Added Products</h5>
                                    {#each supplierProducts as product, index}
                                        <div class="kt-card border border-gray-200">
                                            <div class="kt-card-header">
                                                <h6 class="kt-card-title text-sm">{product.product_name}</h6>
                                                <div class="kt-card-toolbar">
                                                    <button 
                                                        type="button"
                                                        class="kt-btn kt-btn-sm kt-btn-icon kt-btn-ghost text-destructive"
                                                        on:click={() => removeProduct(index)}
                                                    >
                                                        <i class="ki-filled ki-trash text-sm"></i>
                                                    </button>
                                                </div>
                                            </div>
                                            <div class="kt-card-content">
                                                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                                                    <!-- Min Sale Price -->
                                                    <div class="flex flex-col gap-2">
                                                        <label class="text-xs font-medium text-mono">
                                                            Min Sale Price <span class="text-destructive">*</span>
                                                        </label>
                                                        <input
                                                            type="number"
                                                            step="0.001"
                                                            min="0"
                                                            class="kt-input kt-input-sm"
                                                            placeholder="0.000"
                                                            bind:value={product.min_sale_price}
                                                            on:input={() => updateProduct(index, 'min_sale_price', product.min_sale_price)}
                                                        />
                                                    </div>

                                                    <!-- Avg Sale Price -->
                                                    <div class="flex flex-col gap-2">
                                                        <label class="text-xs font-medium text-mono">
                                                            Avg Sale Price <span class="text-destructive">*</span>
                                                        </label>
                                                        <input
                                                            type="number"
                                                            step="0.001"
                                                            min="0"
                                                            class="kt-input kt-input-sm"
                                                            placeholder="0.000"
                                                            bind:value={product.avg_sale_price}
                                                            on:input={() => updateProduct(index, 'avg_sale_price', product.avg_sale_price)}
                                                        />
                                                    </div>

                                                    <!-- Max Sale Price -->
                                                    <div class="flex flex-col gap-2">
                                                        <label class="text-xs font-medium text-mono">
                                                            Max Sale Price <span class="text-destructive">*</span>
                                                        </label>
                                                        <input
                                                            type="number"
                                                            step="0.001"
                                                            min="0"
                                                            class="kt-input kt-input-sm"
                                                            placeholder="0.000"
                                                            bind:value={product.max_sale_price}
                                                            on:input={() => updateProduct(index, 'max_sale_price', product.max_sale_price)}
                                                        />
                                                    </div>

                                                    <!-- Minimum Order Qty -->
                                                    <div class="flex flex-col gap-2">
                                                        <label class="text-xs font-medium text-mono">
                                                            Min Order Qty <span class="text-destructive">*</span>
                                                        </label>
                                                        <input
                                                            type="number"
                                                            min="1"
                                                            class="kt-input kt-input-sm"
                                                            placeholder="1"
                                                            bind:value={product.minimum_order_qty}
                                                            on:input={() => updateProduct(index, 'minimum_order_qty', product.minimum_order_qty)}
                                                        />
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    {/each}
                                </div>
                            {/if}
                        </div>
                    </div>
                </div>

                <!-- Supplier Image Card -->
                <div class="kt-card">
                    <div class="kt-card-header">
                        <h4 class="kt-card-title">Supplier Image</h4>
                    </div>
                    <div class="kt-card-content">
                        <div class="grid gap-4">
                            <!-- Current Image -->
                            {#if supplier.image}
                                <div class="flex flex-col gap-2">
                                    <label class="text-sm font-medium text-mono">Current Image</label>
                                    <div class="flex items-center gap-4">
                                        <img 
                                            src={supplier.image_url} 
                                            alt="Current supplier image" 
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
                                    Supported formats: JPG, JPEG, PNG
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
                    <a href="{route('admin.suppliers.index')}" class="kt-btn kt-btn-outline">
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
                            Update Supplier
                        {/if}
                    </button>
                </div>
            </form>
        </div>
    </div>
</AdminLayout>



