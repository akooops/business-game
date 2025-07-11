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
                    </div>
                </div>
            </div>
        </div>
    </div>
</AdminLayout> 