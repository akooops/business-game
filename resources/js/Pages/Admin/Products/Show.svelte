<script>
    import AdminLayout from '../../Layouts/AdminLayout.svelte';
    import { page } from '@inertiajs/svelte';

    // Props
    export let product;

    // Define breadcrumbs for this product
    const breadcrumbs = [
        {
            title: 'Products',
            url: route('admin.products.index'),
            active: false
        },
        {
            title: product.name || 'Product Details',
            url: route('admin.products.show', { product: product.id }),
            active: true
        }
    ];
    
    const pageTitle = 'Product Details';

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
            <!-- Product Header -->
            <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4">
                <div class="flex flex-col gap-1">
                    <h1 class="text-2xl font-bold text-mono">Product Details</h1>
                    <p class="text-sm text-secondary-foreground">
                        View product information and business properties
                    </p>
                </div>
                <div class="flex items-center gap-3">
                    <a href="{route('admin.products.index')}" class="kt-btn kt-btn-outline">
                        <i class="ki-filled ki-arrow-left text-base"></i>
                        Back to Products
                    </a>
                    {#if hasPermission('admin.products.update')}
                    <a href="{route('admin.products.edit', { product: product.id })}" class="kt-btn kt-btn-primary">
                        <i class="ki-filled ki-pencil text-base"></i>
                        Edit Product
                    </a>
                    {/if}
                </div>
            </div>

            <!-- Product Information Card -->
            <div class="kt-card">
                <div class="kt-card-header">
                    <h4 class="kt-card-title">Basic Information</h4>
                </div>
                <div class="kt-card-content">
                     <!-- Product Details -->
                     <div class="grid gap-4 w-full">
                        <!-- Product Image -->
                        <div class="flex">
                            <figure class="figure">
                                <img 
                                    src={product?.image_url} 
                                    alt={product?.name}
                                    class="rounded-lg w-32 h-32 object-cover border"
                                />
                            </figure>
                        </div>

                        <!-- Product Name -->
                        <div class="flex flex-col gap-2">
                            <h4 class="text-sm font-semibold text-mono">Product Name</h4>
                            <p class="text-sm text-secondary-foreground">{product?.name}</p>
                        </div>

                        <!-- Description -->
                        {#if product?.description}
                        <div class="flex flex-col gap-2">
                            <h4 class="text-sm font-semibold text-mono">Description</h4>
                            <p class="text-sm text-secondary-foreground">{product?.description}</p>
                        </div>
                        {/if}
                        
                        <!-- Product Type -->
                        <div class="flex flex-col gap-2">
                            <h4 class="text-sm font-semibold text-mono">Product Type</h4>
                            <div>
                                <span class={getProductTypeBadgeClass(product?.type)}>
                                    {product?.type_name || product?.type}
                                </span>
                            </div>
                        </div>

                        <!-- Measurement Unit -->
                        <div class="flex flex-col gap-2">
                            <h4 class="text-sm font-semibold text-mono">Measurement Unit</h4>
                            <p class="text-sm text-secondary-foreground">{product?.measurement_unit}</p>
                        </div>

                        <!-- Created At -->
                        <div class="flex flex-col gap-2">
                            <h4 class="text-sm font-semibold text-mono">Created At</h4>
                            <p class="text-sm text-secondary-foreground">
                                {formatTimestamp(product?.created_at)}
                            </p>
                        </div>

                        <!-- Updated At -->
                        <div class="flex flex-col gap-2">
                            <h4 class="text-sm font-semibold text-mono">Updated At</h4>
                            <p class="text-sm text-secondary-foreground">
                                {formatTimestamp(product?.updated_at)}
                            </p>
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
                    <div class="grid gap-4 w-full">
                        <!-- Elasticity Coefficient -->
                        <div class="flex flex-col gap-2">
                            <h4 class="text-sm font-semibold text-mono">Elasticity Coefficient</h4>
                            <p class="text-sm text-secondary-foreground">
                                {product?.elasticity_coefficient}
                                <span class="text-xs text-muted-foreground ml-2">
                                    (Price sensitivity factor)
                                </span>
                            </p>
                        </div>

                        <!-- Has Expiration -->
                        <div class="flex flex-col gap-2">
                            <h4 class="text-sm font-semibold text-mono">Product Expiration</h4>
                            <div class="flex items-center gap-2">
                                {#if product?.has_expiration}
                                    <span class="kt-badge kt-badge-destructive kt-badge-sm">Expires</span>
                                    <span class="text-sm text-secondary-foreground">
                                        This product has an expiration date
                                    </span>
                                {:else}
                                    <span class="kt-badge kt-badge-outline kt-badge-sm">No Expiration</span>
                                    <span class="text-sm text-secondary-foreground">
                                        This product does not expire
                                    </span>
                                {/if}
                            </div>
                        </div>

                        <!-- Shelf Life Days -->
                        {#if product?.has_expiration && product?.shelf_life_days}
                        <div class="flex flex-col gap-2">
                            <h4 class="text-sm font-semibold text-mono">Shelf Life</h4>
                            <p class="text-sm text-secondary-foreground">
                                {product?.shelf_life_days} days
                            </p>
                        </div>
                        {/if}

                        <!-- Technology Requirement -->
                        <div class="flex flex-col gap-2">
                            <h4 class="text-sm font-semibold text-mono">Technology Requirement</h4>
                            <div class="flex items-center gap-2">
                                {#if product?.need_technology}
                                    <span class="kt-badge kt-badge-warning kt-badge-sm">Required</span>
                                    <span class="text-sm text-secondary-foreground">
                                        This product requires a technology to be produced
                                    </span>
                                {:else}
                                    <span class="kt-badge kt-badge-outline kt-badge-sm">Not Required</span>
                                    <span class="text-sm text-secondary-foreground">
                                        No technology required for production
                                    </span>
                                {/if}
                            </div>
                        </div>

                        <!-- Assigned Technology -->
                        {#if product?.need_technology}
                            <div class="flex flex-col gap-2">
                                <h4 class="text-sm font-semibold text-mono">Assigned Technology</h4>
                                {#if product?.technology}
                                    <div class="kt-card border">
                                        <div class="kt-card-content p-3">
                                            <div class="flex items-center gap-3">
                                                <div class="flex items-center justify-center size-12 shrink-0 rounded bg-accent/50">
                                                    {#if product.technology.image_url}
                                                        <img src={product.technology.image_url} alt="" class="size-10 object-cover rounded" />
                                                    {:else}
                                                        <i class="ki-filled ki-technology-1 text-xl text-muted-foreground"></i>
                                                    {/if}
                                                </div>
                                                <div class="flex flex-col gap-1">
                                                    <span class="text-sm font-medium text-mono">{product.technology.name}</span>
                                                    <div class="flex items-center gap-3 text-xs text-muted-foreground">
                                                        <span class="kt-badge kt-badge-light-primary kt-badge-sm">Level {product.technology.level}</span>
                                                        <span>Cost: {product.technology.research_cost}</span>
                                                        <span>Time: {product.technology.research_time_days} days</span>
                                                    </div>
                                                    {#if product.technology.description}
                                                        <p class="text-xs text-muted-foreground mt-1">{product.technology.description}</p>
                                                    {/if}
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                {:else}
                                    <div class="kt-card border border-dashed border-warning/50 bg-warning/5">
                                        <div class="kt-card-content p-3">
                                            <div class="flex items-center gap-3">
                                                <div class="flex items-center justify-center size-10 shrink-0 rounded bg-warning/20">
                                                    <i class="ki-filled ki-warning-2 text-lg text-warning"></i>
                                                </div>
                                                <div class="flex flex-col">
                                                    <span class="text-sm font-medium text-mono text-warning">No Technology Assigned</span>
                                                    <span class="text-xs text-muted-foreground">
                                                        This product requires a technology but none is currently assigned
                                                    </span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                {/if}
                            </div>
                        {/if}
                    </div>
                </div>
            </div>
        </div>
    </div>
</AdminLayout> 