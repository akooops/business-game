<script>
    import AdminLayout from '../../Layouts/AdminLayout.svelte';

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
            url: route('admin.suppliers.show', { supplier: supplier.id }),
            active: true
        }
    ];
    
    const pageTitle = 'Supplier Details';

    // Format number with units
    function formatNumber(value, decimals = 1) {
        return parseFloat(value).toFixed(decimals);
    }

    // Map supplier products for display
    const supplierProducts = (supplier.products || []).map(product => ({
        product_id: product.id,
        product_name: product.name,
        product_type: product.type,
        product_type_name: product.type_name,
        product_image: product.image_url,
        min_sale_price: product.pivot.min_sale_price,
        avg_sale_price: product.pivot.avg_sale_price,
        max_sale_price: product.pivot.max_sale_price,
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
            <!-- Supplier Header -->
            <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4">
                <div class="flex flex-col gap-1">
                    <h1 class="text-2xl font-bold text-mono">Supplier Details</h1>
                    <p class="text-sm text-secondary-foreground">
                        View supplier information and procurement details
                    </p>
                </div>
                <div class="flex items-center gap-3">
                    <a href="{route('admin.suppliers.index')}" class="kt-btn kt-btn-outline">
                        <i class="ki-filled ki-arrow-left text-base"></i>
                        Back to Suppliers
                    </a>

                    <a href="{route('admin.suppliers.edit', { supplier: supplier.id })}" class="kt-btn kt-btn-primary">
                        <i class="ki-filled ki-pencil text-base"></i>
                        Edit Supplier
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
                        <!-- Supplier Image -->
                        <div class="flex">
                            <figure class="figure">
                                <img 
                                    src={supplier?.image_url} 
                                    alt={supplier?.name}
                                    class="rounded-lg w-32 h-32 object-cover border"
                                />
                            </figure>
                        </div>

                        <!-- Supplier Name -->
                        <div class="flex flex-col gap-2">
                            <h4 class="text-sm font-semibold text-mono">Supplier Name</h4>
                            <p class="text-sm text-secondary-foreground">{supplier.name}</p>
                        </div>

                        <!-- Supplier Type -->
                        <div class="flex flex-col gap-2">
                            <h4 class="text-sm font-semibold text-mono">Supplier Type</h4>
                            <div class="flex items-center gap-2">
                                {#if supplier.is_international}
                                    <span class="kt-badge kt-badge-outline kt-badge-primary">International</span>
                                    <span class="text-sm text-secondary-foreground">
                                        This is an international supplier
                                    </span>
                                {:else}
                                    <span class="kt-badge kt-badge-outline kt-badge-success">Local</span>
                                    <span class="text-sm text-secondary-foreground">
                                        This is a local supplier
                                    </span>
                                {/if}
                            </div>
                        </div>

                        <!-- Location -->
                        <div class="flex flex-col gap-2">
                            <h4 class="text-sm font-semibold text-mono">Location</h4>
                            {#if supplier.is_international}
                                {#if supplier.country}
                                    <div class="flex items-center gap-2">
                                        <i class="ki-filled ki-map text-sm text-muted-foreground"></i>
                                        <span class="text-sm text-secondary-foreground">{supplier.country.name}</span>
                                    </div>
                                {:else}
                                    <span class="text-sm text-muted-foreground">No country specified</span>
                                {/if}
                            {:else}
                                {#if supplier.wilaya}
                                    <div class="flex items-center gap-2">
                                        <i class="ki-filled ki-map text-sm text-muted-foreground"></i>
                                        <span class="text-sm text-secondary-foreground">{supplier.wilaya.name}</span>
                                    </div>
                                {:else}
                                    <span class="text-sm text-muted-foreground">No wilaya specified</span>
                                {/if}
                            {/if}
                        </div>

                        <!-- Created At -->
                        <div class="flex flex-col gap-2">
                            <h4 class="text-sm font-semibold text-mono">Created At</h4>
                            <p class="text-sm text-secondary-foreground">
                                {formatTimestamp(supplier.created_at)}
                            </p>
                        </div>

                        <!-- Updated At -->
                        <div class="flex flex-col gap-2">
                            <h4 class="text-sm font-semibold text-mono">Updated At</h4>
                            <p class="text-sm text-secondary-foreground">
                                {formatTimestamp(supplier.updated_at)}
                            </p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Shipping Information Card -->
            <div class="kt-card">
                <div class="kt-card-header">
                    <h4 class="kt-card-title">Shipping Information</h4>
                </div>
                <div class="kt-card-content">
                    <div class="grid gap-4 w-full">
                        <!-- Shipping Costs -->
                        <div class="flex flex-col gap-2">
                            <h4 class="text-sm font-semibold text-mono">Shipping Costs</h4>
                            <div class="grid grid-cols-3 gap-4">
                                <div class="flex flex-col gap-1">
                                    <span class="text-xs text-muted-foreground">Minimum</span>
                                    <p class="text-sm text-secondary-foreground">
                                        {supplier.min_shipping_cost}
                                    </p>
                                </div>
                                <div class="flex flex-col gap-1">
                                    <span class="text-xs text-muted-foreground">Average</span>
                                    <p class="text-sm text-secondary-foreground font-medium">
                                        {supplier.avg_shipping_cost}
                                    </p>
                                </div>
                                <div class="flex flex-col gap-1">
                                    <span class="text-xs text-muted-foreground">Maximum</span>
                                    <p class="text-sm text-secondary-foreground">
                                        {supplier.max_shipping_cost}
                                    </p>
                                </div>
                            </div>
                        </div>

                        <!-- Shipping Times -->
                        <div class="flex flex-col gap-2">
                            <h4 class="text-sm font-semibold text-mono">Shipping Times</h4>
                            <div class="grid grid-cols-3 gap-4">
                                <div class="flex flex-col gap-1">
                                    <span class="text-xs text-muted-foreground">Minimum</span>
                                    <p class="text-sm text-secondary-foreground">
                                        {supplier.min_shipping_time_days} days
                                    </p>
                                </div>
                                <div class="flex flex-col gap-1">
                                    <span class="text-xs text-muted-foreground">Average</span>
                                    <p class="text-sm text-secondary-foreground font-medium">
                                        {supplier.avg_shipping_time_days} days
                                    </p>
                                </div>
                                <div class="flex flex-col gap-1">
                                    <span class="text-xs text-muted-foreground">Maximum</span>
                                    <p class="text-sm text-secondary-foreground">
                                        {supplier.max_shipping_time_days} days
                                    </p>
                                </div>
                            </div>
                        </div>

                        <!-- Carbon Footprint -->
                        <div class="flex flex-col gap-2">
                            <h4 class="text-sm font-semibold text-mono">Carbon Footprint</h4>
                            <p class="text-sm text-secondary-foreground">
                                {formatNumber(supplier.carbon_footprint)} kg CO2
                                <span class="text-xs text-muted-foreground ml-2">
                                    (Environmental impact measurement)
                                </span>
                            </p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Supplier Products Card -->
            <div class="kt-card">
                <div class="kt-card-header">
                    <h4 class="kt-card-title">Supplier Products</h4>
                </div>
                <div class="kt-card-content">
                    <div class="grid gap-4">
                        {#if supplierProducts.length === 0}
                            <div class="kt-card bg-muted/20 border-dashed">
                                <div class="kt-card-content text-center py-8">
                                    <i class="ki-filled ki-package text-2xl text-muted-foreground mb-2"></i>
                                    <p class="text-sm text-muted-foreground">No products available</p>
                                    <p class="text-xs text-muted-foreground mt-1">This supplier has no products configured</p>
                                </div>
                            </div>
                        {:else}
                            <div>
                                <h5 class="text-sm font-medium text-mono mb-3">
                                    Available Products ({supplierProducts.length})
                                </h5>
                                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-4">
                                    {#each supplierProducts as product}
                                        <div class="kt-card">
                                            <div class="kt-card-content flex flex-col p-3 gap-3">
                                                <!-- Product Image -->
                                                <div class="kt-card flex items-center justify-center relative bg-accent/50 w-full h-[120px] shadow-none rounded">
                                                    {#if product.product_image}
                                                        <img alt="" class="h-[100px] w-[100px] object-cover rounded" src="{product.product_image}"/>
                                                    {:else}
                                                        <i class="ki-filled ki-package text-3xl text-muted-foreground"></i>
                                                    {/if}
                                                </div>
                                                
                                                <!-- Product Info -->
                                                <div class="flex flex-col gap-2">
                                                    <h6 class="text-sm font-medium text-mono leading-5 line-clamp-2" title="{product.product_name}">
                                                        {product.product_name}
                                                    </h6>
                                                    <span class={getProductTypeBadgeClass(product.product_type) + ' kt-badge-sm w-fit'}>
                                                        {product.product_type_name}
                                                    </span>
                                                    
                                                    <!-- Pricing Information -->
                                                    <div class="space-y-1">
                                                        <div class="flex justify-between text-xs">
                                                            <span class="text-muted-foreground">Min Price:</span>
                                                            <span class="font-medium">{product.min_sale_price}</span>
                                                        </div>
                                                        <div class="flex justify-between text-xs">
                                                            <span class="text-muted-foreground">Avg Price:</span>
                                                            <span class="font-medium">{product.avg_sale_price}</span>
                                                        </div>
                                                        <div class="flex justify-between text-xs">
                                                            <span class="text-muted-foreground">Max Price:</span>
                                                            <span class="font-medium">{product.max_sale_price}</span>
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
        </div>
    </div>
</AdminLayout> 