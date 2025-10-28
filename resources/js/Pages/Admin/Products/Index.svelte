<script>
    import AdminLayout from '../../Layouts/AdminLayout.svelte';
    import Pagination from '../../Components/Pagination.svelte';
    import { onMount, tick } from 'svelte';
    import { page } from '@inertiajs/svelte'
    import Select2 from '../../Components/Forms/Select2.svelte';

    // Define breadcrumbs for this page
    const breadcrumbs = [
        {
            title: 'Products',
            url: route('admin.products.index'),
            active: false
        },
        {
            title: 'Index',
            url: route('admin.products.index'),
            active: true
        }
    ];
    
    const pageTitle = 'Products';

    // Reactive variables
    let products = [];
    let pagination = {};
    let loading = true;
    let search = '';
    let perPage = 10;
    let currentPage = 1;
    let searchTimeout;

    // Select2 component reference
    let technologySelectComponent;

    // Product type badge colors
    function getProductTypeBadgeClass(type) {
        switch(type) {
            case 'raw_material':
                return 'kt-badge kt-badge-outline kt-badge-success kt-badge-sm';
            case 'component':
                return 'kt-badge kt-badge-outline kt-badge-warning kt-badge-sm';
            case 'finished_product':
                return 'kt-badge kt-badge-outline kt-badge-primary kt-badge-sm';
            default:
                return 'kt-badge kt-badge-outline kt-badge-sm';
        }
    }


    // Fetch products data
    async function fetchProducts() {
        loading = true;
        try {
            const params = new URLSearchParams({
                page: currentPage,
                perPage: perPage,
                search: search
            });

            const response = await fetch(route('admin.products.index') + '?' + params.toString(), {
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                }
            });
            
            const data = await response.json();
            products = data.products;
            pagination = data.pagination;
            
            // Wait for DOM to update, then initialize menus
            await tick();
            if (window.KTMenu) {
                window.KTMenu.init();
            }
        } catch (error) {
            console.error('Error fetching products:', error);
        } finally {
            loading = false;
        }
    }

    // Handle search with debouncing
    function handleSearch() {
        // Clear existing timeout
        if (searchTimeout) {
            clearTimeout(searchTimeout);
        }
        
        // Set new timeout for 500ms
        searchTimeout = setTimeout(() => {
            currentPage = 1;
            fetchProducts();
        }, 500);
    }

    // Handle search input change
    function handleSearchInput(event) {
        search = event.target.value;
        handleSearch();
    }

    // Handle pagination
    function goToPage(page) {
        if (page && page !== currentPage) {
            currentPage = page;
            fetchProducts();
        }
    }

    // Handle per page change
    function handlePerPageChange(newPerPage) {
        perPage = newPerPage;
        currentPage = 1;
        fetchProducts();
    }

    // Delete product
    async function deleteProduct(productId) {
        if (!confirm('Are you sure you want to delete this product? This action cannot be undone.')) {
            return;
        }

        try {
            const formData = new FormData();
            formData.append('_method', 'DELETE');
            formData.append('_token', document.querySelector('meta[name="csrf-token"]')?.getAttribute('content'));

            const response = await fetch(route('admin.products.destroy', { product: productId }), {
                method: 'POST',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: formData
            });

            if (response.ok) {
                showToast("Product deleted successfully!", 'success');

                // Refresh the products list
                fetchProducts();
            } else {
                const errorData = await response.json().catch(() => ({}));
                const errorMessage = errorData.message || 'Error deleting product. Please try again.';
                
                showToast(errorMessage, 'destructive');
            }
        } catch (error) {
            console.error('Error deleting product:', error);
            
            showToast("Network error. Please check your connection and try again.", 'destructive');
        }
    }

    onMount(() => {
        fetchProducts();
    });

    // Flash message handling
    export let success;

    $: if (success) {
        showToast(success, 'success');  
    }
</script>

<svelte:head>
    <title>Business game - {pageTitle}</title>
</svelte:head>

<AdminLayout {breadcrumbs} {pageTitle}>
    <!-- Container -->
    <div class="kt-container-fixed">
        <div class="grid gap-5 lg:gap-7.5">
            <!-- Product Header -->
            <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4">
                <div class="flex flex-col gap-1">
                    <h1 class="text-2xl font-bold text-mono">Products Management</h1>
                    <p class="text-sm text-secondary-foreground">
                        Manage your simulation products inventory
                    </p>
                </div>
                <div class="flex items-center gap-3">
                    <a href="{route('admin.products.create')}" class="kt-btn kt-btn-primary">
                        <i class="ki-filled ki-plus text-base"></i>
                        Add New Product
                    </a>
                </div>                      
            </div>

            <!-- Products Table -->
            <div class="kt-card">
                <div class="kt-card-header">
                    <div class="kt-card-toolbar">
                        <div class="flex items-center gap-4">
                            <div class="kt-input max-w-64 w-64">
                                <i class="ki-filled ki-magnifier"></i>
                                <input 
                                    type="text" 
                                    class="kt-input" 
                                    placeholder="Search products..." 
                                    bind:value={search}
                                    on:input={handleSearchInput}
                                />
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="kt-card-content p-0">
                    <div class="kt-scrollable-x-auto">
                        <table class="kt-table kt-table-border table-fixed">
                            <thead>
                                <tr>
                                    <th style="width: 75px;">
                                        <span class="kt-table-col">
                                            <span class="kt-table-col-label">ID</span>
                                        </span>
                                    </th>
                                    <th>
                                        <span class="kt-table-col">
                                            <span class="kt-table-col-label">Product</span>
                                        </span>
                                    </th>
                                    <th>
                                        <span class="kt-table-col">
                                            <span class="kt-table-col-label">Type</span>
                                        </span>
                                    </th>
                                    <th>
                                        <span class="kt-table-col">
                                            <span class="kt-table-col-label">Technology</span>
                                        </span>
                                    </th>
                                    <th style="width: 70px;">
                                        <span class="kt-table-col">
                                            <span class="kt-table-col-label">Actions</span>
                                        </span>
                                    </th>
                                </tr>
                            </thead>
                            <tbody>
                                {#if loading}
                                    <!-- Loading skeleton rows -->
                                    {#each Array(perPage) as _, i}
                                        <tr>
                                            <td class="p-4">
                                                <div class="kt-skeleton w-8 h-4 rounded"></div>
                                            </td>
                                            <td class="p-4">
                                                <div class="flex items-center gap-3">
                                                    <div class="kt-skeleton w-10 h-10 rounded-lg"></div>
                                                    <div class="flex flex-col gap-1">
                                                        <div class="kt-skeleton w-24 h-4 rounded"></div>
                                                        <div class="kt-skeleton w-16 h-3 rounded"></div>
                                                    </div>
                                                </div>
                                            </td>
                                            <td class="p-4">
                                                <div class="kt-skeleton w-16 h-6 rounded"></div>
                                            </td>
                                            <td class="p-4">
                                                <div class="kt-skeleton w-20 h-6 rounded"></div>
                                            </td>
                                            <td class="p-4">
                                                <div class="kt-skeleton w-8 h-8 rounded"></div>
                                            </td>
                                        </tr>
                                    {/each}
                                {:else if products.length === 0}
                                    <!-- Empty state -->
                                    <tr>
                                        <td colspan="6" class="p-10">
                                            <div class="flex flex-col items-center justify-center text-center">
                                                <div class="mb-4">
                                                    <i class="ki-filled ki-package text-4xl text-muted-foreground"></i>
                                                </div>
                                                <h3 class="text-lg font-semibold text-mono mb-2">No products found</h3>
                                                <p class="text-sm text-secondary-foreground mb-4">
                                                    {search ? 'No products match your search criteria.' : 'Get started by creating your first product.'}
                                                </p>

                                                <a href="{route('admin.products.create')}" class="kt-btn kt-btn-primary">
                                                    <i class="ki-filled ki-plus text-base"></i>
                                                    Create First Product
                                                </a>
                                            </div>
                                        </td>
                                    </tr>
                                {:else}
                                    <!-- Actual data rows -->
                                    {#each products as product}
                                        <tr class="hover:bg-muted/50">
                                            <td>
                                                <span class="text-sm font-medium text-mono">#{product.id}</span>
                                            </td>
                                            <td>
                                                <div class="flex items-center">
                                                    {#if product.image_url}
                                                        <img 
                                                            src={product.image_url} 
                                                            alt={product.name}
                                                            class="rounded-lg object-cover"
                                                            style="min-width: 36px; min-height: 36px; max-width: 36px; max-height: 36px;"
                                                        />
                                                    {:else}
                                                        <div class="w-10 h-10 rounded-lg bg-accent/50 flex items-center justify-center">
                                                            <i class="ki-filled ki-package text-lg text-muted-foreground"></i>
                                                        </div>
                                                    {/if}

                                                    <div class="flex flex-col gap-1">
                                                        <span class="text-sm font-medium text-mono">
                                                            {product.name}
                                                        </span>

                                                        {#if product.description}
                                                            <span class="text-xs text-muted-foreground">
                                                                {product.description}
                                                            </span>
                                                        {/if}
                                                    </div>
                                                </div>
                                            </td>

                                            <td>
                                                <span class={getProductTypeBadgeClass(product.type)}>
                                                    {product.type_name}
                                                </span>
                                            </td>
                                            <td>
                                                {#if product.technology}
                                                    <div class="flex items-center gap-2">
                                                        <div class="flex items-center justify-center size-8 shrink-0 rounded bg-accent/50">
                                                            {#if product.technology.image_url}
                                                                <img src={product.technology.image_url} alt="" class="size-6 object-cover rounded" />
                                                            {:else}
                                                                <i class="ki-filled ki-technology-1 text-sm text-muted-foreground"></i>
                                                            {/if}
                                                        </div>
                                                        <div class="flex flex-col">
                                                            <span class="text-xs font-medium text-mono">{product.technology.name}</span>
                                                            <span class="text-xs text-muted-foreground">Level {product.technology.level}</span>
                                                        </div>
                                                    </div>
                                                {:else}
                                                    <span class="text-xs text-muted-foreground">
                                                        No research required
                                                    </span>
                                                {/if}
                                            </td>
                                            <td class="text-center">
                                                <div class="kt-menu flex-inline" data-kt-menu="true">
                                                    <div class="kt-menu-item" data-kt-menu-item-offset="0, 10px" data-kt-menu-item-placement="bottom-end" data-kt-menu-item-placement-rtl="bottom-start" data-kt-menu-item-toggle="dropdown" data-kt-menu-item-trigger="click">
                                                        <button class="kt-menu-toggle kt-btn kt-btn-sm kt-btn-icon kt-btn-ghost">
                                                            <i class="ki-filled ki-dots-vertical text-lg"></i>
                                                        </button>
                                                        <div class="kt-menu-dropdown kt-menu-default w-full max-w-[175px]" data-kt-menu-dismiss="true">
                                                            <div class="kt-menu-item">
                                                                <a class="kt-menu-link" href={route('admin.product-demand.index', { product_id: product.id, product_name: product.name })}>
                                                                    <span class="kt-menu-icon">
                                                                        <i class="ki-filled ki-chart-line"></i>
                                                                    </span>
                                                                    <span class="kt-menu-title">Demand</span>
                                                                </a>
                                                            </div>

                                                            <div class="kt-menu-separator"></div>

                                                            <div class="kt-menu-item">
                                                                <a class="kt-menu-link" href={route('admin.product-recipes.index', { product_id: product.id, product_name: product.name })}>
                                                                    <span class="kt-menu-icon">
                                                                        <i class="ki-filled ki-element-11"></i>
                                                                    </span>
                                                                    <span class="kt-menu-title">Recipes</span>
                                                                </a>
                                                            </div>

                                                            <div class="kt-menu-separator"></div>
                                                                
                                                            <div class="kt-menu-item">
                                                                <a class="kt-menu-link" href={route('admin.products.show', { product: product.id })}>
                                                                    <span class="kt-menu-icon">
                                                                        <i class="ki-filled ki-search-list"></i>
                                                                    </span>
                                                                    <span class="kt-menu-title">View</span>
                                                                </a>
                                                            </div>

                                                            <div class="kt-menu-item">
                                                                <a class="kt-menu-link" href={route('admin.products.edit', { product: product.id })}>
                                                                    <span class="kt-menu-icon">
                                                                        <i class="ki-filled ki-pencil"></i>
                                                                    </span>
                                                                    <span class="kt-menu-title">Edit</span>
                                                                </a>
                                                            </div>

                                                            <div class="kt-menu-separator"></div>
                                                            
                                                            <div class="kt-menu-item">
                                                                <button class="kt-menu-link" on:click={() => deleteProduct(product.id)}>
                                                                    <span class="kt-menu-icon">
                                                                        <i class="ki-filled ki-trash"></i>
                                                                    </span>
                                                                    <span class="kt-menu-title">Delete</span>
                                                                </button>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </td>
                                        </tr>
                                    {/each}
                                {/if}
                            </tbody>
                        </table>
                    </div>

                    <!-- Pagination -->
                    {#if pagination && pagination.total > 0}
                        <Pagination 
                            {pagination} 
                            {perPage}
                            onPageChange={goToPage} 
                            onPerPageChange={handlePerPageChange}
                        />
                    {/if}
                </div>
            </div>
        </div>
    </div>
    <!-- End of Container -->
</AdminLayout>

