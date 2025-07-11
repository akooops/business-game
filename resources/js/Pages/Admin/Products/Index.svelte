<script>
    import AdminLayout from '../../Layouts/AdminLayout.svelte';
    import Pagination from '../../Components/Pagination.svelte';
    import { onMount, tick } from 'svelte';
    import { page } from '@inertiajs/svelte'

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
    let showFilters = false;

    // Filter variables
    let typeFilter = '';
    let hasExpirationFilter = '';
    let elasticityMin = '';
    let elasticityMax = '';
    let shelfLifeMin = '';
    let shelfLifeMax = '';

    // Product types mapping
    const productTypes = {
        'raw_material': 'Raw Material',
        'component': 'Component',
        'finished_product': 'Finished Product'
    };

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
            
            // Add filter parameters
            if (typeFilter) {
                params.append('type', typeFilter);
            }
            if (hasExpirationFilter) {
                params.append('has_expiration', hasExpirationFilter);
            }
            if (elasticityMin) {
                params.append('elasticity_min', elasticityMin);
            }
            if (elasticityMax) {
                params.append('elasticity_max', elasticityMax);
            }
            if (shelfLifeMin) {
                params.append('shelf_life_min', shelfLifeMin);
            }
            if (shelfLifeMax) {
                params.append('shelf_life_max', shelfLifeMax);
            }
            
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

    // Handle filter changes
    function handleFilterChange() {
        currentPage = 1;
        fetchProducts();
    }

    // Clear all filters
    function clearAllFilters() {
        typeFilter = '';
        hasExpirationFilter = '';
        elasticityMin = '';
        elasticityMax = '';
        shelfLifeMin = '';
        shelfLifeMax = '';
        currentPage = 1;
        fetchProducts();
    }

    // Toggle filters visibility
    function toggleFilters() {
        showFilters = !showFilters;
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
                // Show success toast
                KTToast.show({
                    icon: `<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-info-icon lucide-info"><circle cx="12" cy="12" r="10"/><path d="M12 16v-4"/><path d="M12 8h.01"/></svg>`,
                    message: "Product deleted successfully!",
                    variant: "success",
                    position: "bottom-right",
                });

                // Refresh the products list
                fetchProducts();
            } else {
                const errorData = await response.json().catch(() => ({}));
                const errorMessage = errorData.message || 'Error deleting product. Please try again.';
                
                KTToast.show({
                    icon: `<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-info-icon lucide-info"><circle cx="12" cy="12" r="10"/><path d="M12 16v-4"/><path d="M12 8h.01"/></svg>`,
                    message: errorMessage,
                    variant: "destructive",
                    position: "bottom-right",
                });
            }
        } catch (error) {
            console.error('Error deleting product:', error);
            
            KTToast.show({
                    icon: `<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-info-icon lucide-info"><circle cx="12" cy="12" r="10"/><path d="M12 16v-4"/><path d="M12 8h.01"/></svg>`,
                    message: "Network error. Please check your connection and try again.",
                    variant: "destructive",
                    position: "bottom-right",
            });
        }
    }

    onMount(() => {
        fetchProducts();
    });

    // Flash message handling
    export let success;

    $: if (success) {
        KTToast.show({
            icon: `<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-info-icon lucide-info"><circle cx="12" cy="12" r="10"/><path d="M12 16v-4"/><path d="M12 8h.01"/></svg>`,
            message: success,
            variant: "success",
            position: "bottom-right",
        });
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
                    {#if hasPermission('admin.products.store')}
                    <a href="{route('admin.products.create')}" class="kt-btn kt-btn-primary">
                        <i class="ki-filled ki-plus text-base"></i>
                        Add New Product
                    </a>
                    {/if}
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
                            
                            <!-- Filter Toggle Button -->
                            <button 
                                class="kt-btn kt-btn-outline"
                                on:click={toggleFilters}
                            >
                                <i class="ki-filled ki-filter text-sm"></i>
                                {showFilters ? 'Hide Filters' : 'Show Filters'}
                            </button>
                            
                            <!-- Clear Filters Button -->
                            {#if typeFilter || hasExpirationFilter || elasticityMin || elasticityMax || shelfLifeMin || shelfLifeMax}
                                <button 
                                    class="kt-btn kt-btn-ghost kt-btn-sm"
                                    on:click={clearAllFilters}
                                >
                                    <i class="ki-filled ki-cross text-sm"></i>
                                    Clear All
                                </button>
                            {/if}
                        </div>
                    </div>
                </div>
                
                <!-- Advanced Filters Section -->
                {#if showFilters}
                    <div class="kt-card-body border-t border-gray-200 p-4">
                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-4">
                            <!-- Product Properties -->
                            <div class="space-y-2">
                                <h4 class="text-sm font-medium text-gray-700">Product Properties</h4>                                    
                                <!-- Product type -->
                                <select 
                                    class="kt-select w-full" 
                                    bind:value={typeFilter}
                                    on:change={handleFilterChange}
                                >
                                    <option value="">All Products</option>
                                    <option value="raw_material">Raw Materials</option>
                                    <option value="component">Components</option>
                                    <option value="finished_product">Finished Products</option>
                                </select>
                            </div>

                            <!-- Has expiration -->
                            <div class="space-y-2">
                                <h4 class="text-sm font-medium text-gray-700">Has expiration</h4>                                    
                                <!-- Has expiration -->
                                <select 
                                    class="kt-select w-full" 
                                    bind:value={hasExpirationFilter}
                                    on:change={handleFilterChange}
                                >
                                    <option value="">All Products</option>
                                    <option value="1">Yes</option>
                                    <option value="0">No</option>
                                </select>
                            </div>                     
                            
                            <!-- Shelf Life Range -->
                            <div class="space-y-2">
                                <h4 class="text-sm font-medium text-gray-700">Shelf Life (Days)</h4>
                                
                                <div class="flex gap-2">
                                    <input 
                                        type="number" 
                                        class="kt-input flex-1" 
                                        placeholder="Min Days" 
                                        bind:value={shelfLifeMin}
                                        on:input={handleFilterChange}
                                        min="1"
                                    />
                                    <input 
                                        type="number" 
                                        class="kt-input flex-1" 
                                        placeholder="Max Days" 
                                        bind:value={shelfLifeMax}
                                        on:input={handleFilterChange}
                                        min="1"
                                    />
                                </div>
                            </div>

                            <!-- Elasticity Range -->
                            <div class="space-y-2">
                                <h4 class="text-sm font-medium text-gray-700">Elasticity Coefficient</h4>
                                
                                <div class="flex gap-2">
                                    <input 
                                        type="number" 
                                        class="kt-input flex-1" 
                                        placeholder="Min" 
                                        bind:value={elasticityMin}
                                        on:input={handleFilterChange}
                                        step="0.1"
                                        min="0"
                                    />
                                    <input 
                                        type="number" 
                                        class="kt-input flex-1" 
                                        placeholder="Max" 
                                        bind:value={elasticityMax}
                                        on:input={handleFilterChange}
                                        step="0.1"
                                        min="0"
                                    />
                                </div>
                            </div>
                        </div>
                    </div>
                {/if}
                
                <div class="kt-card-content p-0">
                    <div class="kt-scrollable-x-auto">
                        <table class="kt-table kt-table-border table-fixed">
                            <thead>
                                <tr>
                                    <th class="w-[50px]">
                                        <input class="kt-checkbox kt-checkbox-sm" type="checkbox"/>
                                    </th>
                                    <th class="w-[80px]">
                                        <span class="kt-table-col">
                                            <span class="kt-table-col-label">ID</span>
                                        </span>
                                    </th>
                                    <th class="min-w-[200px]">
                                        <span class="kt-table-col">
                                            <span class="kt-table-col-label">Product</span>
                                        </span>
                                    </th>
                                    <th class="min-w-[150px]">
                                        <span class="kt-table-col">
                                            <span class="kt-table-col-label">Type</span>
                                        </span>
                                    </th>
                                    <th class="w-[80px]">
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
                                                <div class="kt-skeleton w-4 h-4 rounded"></div>
                                            </td>
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
                                                <div class="kt-skeleton w-8 h-8 rounded"></div>
                                            </td>
                                        </tr>
                                    {/each}
                                {:else if products.length === 0}
                                    <!-- Empty state -->
                                    <tr>
                                        <td colspan="5" class="p-10">
                                            <div class="flex flex-col items-center justify-center text-center">
                                                <div class="mb-4">
                                                    <i class="ki-filled ki-package text-4xl text-muted-foreground"></i>
                                                </div>
                                                <h3 class="text-lg font-semibold text-mono mb-2">No products found</h3>
                                                <p class="text-sm text-secondary-foreground mb-4">
                                                    {search ? 'No products match your search criteria.' : 'Get started by creating your first product.'}
                                                </p>
                                                {#if hasPermission('admin.products.store')}
                                                <a href="{route('admin.products.create')}" class="kt-btn kt-btn-primary">
                                                    <i class="ki-filled ki-plus text-base"></i>
                                                    Create First Product
                                                </a>
                                                {/if}
                                            </div>
                                        </td>
                                    </tr>
                                {:else}
                                    <!-- Actual data rows -->
                                    {#each products as product}
                                        <tr class="hover:bg-muted/50">
                                            <td>
                                                <input class="kt-checkbox kt-checkbox-sm" type="checkbox" value={product.id}/>
                                            </td>
                                            <td>
                                                <span class="text-sm font-medium text-mono">#{product.id}</span>
                                            </td>
                                            <td>
                                                <div class="flex items-center gap-3">
                                                    <div class="flex-shrink-0">
                                                        {#if product.image_url}
                                                            <img 
                                                                src={product.image_url} 
                                                                alt={product.name}
                                                                class="w-10 h-10 rounded-lg object-cover"
                                                            />
                                                        {:else}
                                                            <div class="w-10 h-10 rounded-lg bg-accent/50 flex items-center justify-center">
                                                                <i class="ki-filled ki-package text-lg text-muted-foreground"></i>
                                                            </div>
                                                        {/if}
                                                    </div>
                                                    <div class="flex flex-col gap-1">
                                                        <span class="text-sm font-medium text-mono hover:text-primary">
                                                            {product.name}
                                                        </span>
                                                    </div>
                                                </div>
                                            </td>

                                            <td>
                                                <span class={getProductTypeBadgeClass(product.type)}>
                                                    {productTypes[product.type] || product.type}
                                                </span>
                                            </td>
                                            <td class="text-center">
                                                <div class="kt-menu flex-inline" data-kt-menu="true">
                                                    <div class="kt-menu-item" data-kt-menu-item-offset="0, 10px" data-kt-menu-item-placement="bottom-end" data-kt-menu-item-placement-rtl="bottom-start" data-kt-menu-item-toggle="dropdown" data-kt-menu-item-trigger="click">
                                                        <button class="kt-menu-toggle kt-btn kt-btn-sm kt-btn-icon kt-btn-ghost">
                                                            <i class="ki-filled ki-dots-vertical text-lg"></i>
                                                        </button>
                                                        <div class="kt-menu-dropdown kt-menu-default w-full max-w-[175px]" data-kt-menu-dismiss="true">
                                                            {#if hasPermission('admin.product-demand.index')}
                                                            <div class="kt-menu-item">
                                                                <a class="kt-menu-link" href={route('admin.product-demand.index', { product_id: product.id, product_name: product.name })}>
                                                                    <span class="kt-menu-icon">
                                                                        <i class="ki-filled ki-chart-line"></i>
                                                                    </span>
                                                                    <span class="kt-menu-title">Demand</span>
                                                                </a>
                                                            </div>

                                                            <div class="kt-menu-separator"></div>
                                                            {/if}

                                                            {#if hasPermission('admin.product-recipes.index')}
                                                            <div class="kt-menu-item">
                                                                <a class="kt-menu-link" href={route('admin.product-recipes.index', { product_id: product.id, product_name: product.name })}>
                                                                    <span class="kt-menu-icon">
                                                                        <i class="ki-filled ki-abstract"></i>
                                                                    </span>
                                                                    <span class="kt-menu-title">Recipes</span>
                                                                </a>
                                                            </div>

                                                            <div class="kt-menu-separator"></div>
                                                            {/if}
                                                                
                                                            {#if hasPermission('admin.products.show')}
                                                            <div class="kt-menu-item">
                                                                <a class="kt-menu-link" href={route('admin.products.show', { product: product.id })}>
                                                                    <span class="kt-menu-icon">
                                                                        <i class="ki-filled ki-search-list"></i>
                                                                    </span>
                                                                    <span class="kt-menu-title">View</span>
                                                                </a>
                                                            </div>
                                                            {/if}
                                                            {#if hasPermission('admin.products.update')}
                                                            <div class="kt-menu-item">
                                                                <a class="kt-menu-link" href={route('admin.products.edit', { product: product.id })}>
                                                                    <span class="kt-menu-icon">
                                                                        <i class="ki-filled ki-pencil"></i>
                                                                    </span>
                                                                    <span class="kt-menu-title">Edit</span>
                                                                </a>
                                                            </div>
                                                            {/if}
                                                            {#if hasPermission('admin.products.destroy')}
                                                                <div class="kt-menu-separator"></div>
                                                                <div class="kt-menu-item">
                                                                    <button class="kt-menu-link" on:click={() => deleteProduct(product.id)}>
                                                                        <span class="kt-menu-icon">
                                                                            <i class="ki-filled ki-trash"></i>
                                                                        </span>
                                                                        <span class="kt-menu-title">Delete</span>
                                                                    </button>
                                                                </div>
                                                            {/if}
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

