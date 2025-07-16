<script>
    import CompanyLayout from '../../Layouts/CompanyLayout.svelte';
    import Pagination from '../../Components/Pagination.svelte';
    import { onMount, tick } from 'svelte';
    import { page } from '@inertiajs/svelte'

    // Define breadcrumbs for this page
    const breadcrumbs = [
        {
            title: 'Products',
            url: route('company.products.index'),
            active: false
        },        
        {
            title: 'Index',
            url: route('company.technologies.index'),
            active: true
        }
    ];
    
    const pageTitle = 'My Products';

    // Reactive variables
    let products = [];
    let pagination = {};
    let loading = true;
    let search = '';
    let perPage = 10;
    let currentPage = 1;
    let searchTimeout;

    // Drawer state
    let selectedProduct = null;
    let showProductDrawer = false;

    // Modal state
    let showPriceModal = false;
    let selectedProductForPrice = null;
    let newSalePrice = 0;
    let loadingPriceUpdate = false;

    // Fetch products data
    async function fetchProducts() {
        loading = true;
        try {
            const params = new URLSearchParams({
                page: currentPage,
                perPage: perPage,
                search: search
            });
            
            const response = await fetch(route('company.products.index') + '?' + params.toString(), {
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

    // Open product drawer
    function openProductDrawer(product) {
        selectedProduct = product;
        showProductDrawer = true;
        
        // Show drawer
        const toggleButton = document.querySelector('[data-kt-drawer-toggle="#product_drawer"]');
        if (toggleButton) {
            toggleButton.click();
        }
    }

    // Close product drawer
    function closeProductDrawer() {
        showProductDrawer = false;
        selectedProduct = null;
    }

    // Open price modal
    function openPriceModal(product) {
        selectedProductForPrice = product;
        newSalePrice = product.sale_price;
        showPriceModal = true;
        
        // Show modal
        const toggleButton = document.querySelector('[data-kt-modal-toggle="#price_modal"]');
        if (toggleButton) {
            toggleButton.click();
        }
    }

    // Close price modal
    function closePriceModal() {
        // Simulate button click to use KT framework logic
        const dismissButton = document.querySelector('[data-kt-modal-dismiss="#price_modal"]');
        if (dismissButton) {
            dismissButton.click();
        }
        
        showPriceModal = false;
        selectedProductForPrice = null;
        newSalePrice = 0;
    }

    // Update sale price
    async function updateSalePrice() {
        if (!selectedProductForPrice || newSalePrice <= 0) {
            showToast('Please enter a valid sale price', 'error');
            return;
        }

        loadingPriceUpdate = true;
        try {
            const response = await fetch(route('company.products.fix-sale-price', selectedProductForPrice.product.id), {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content'),
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: JSON.stringify({
                    sale_price: newSalePrice
                })
            });

            if (response.ok) {
                const data = await response.json();
                
                // Show success toast
                showToast(data.message || 'Sale price updated successfully!', 'success');
                
                // Close modal
                closePriceModal();
                
                // Refresh products data
                fetchProducts();
            } else {
                const errorData = await response.json();
                showToast(errorData.message || 'Error updating sale price. Please try again.', 'error');
            }
        } catch (error) {
            console.error('Error updating sale price:', error);
            showToast('Network error. Please check your connection and try again.', 'error');
        } finally {
            loadingPriceUpdate = false;
        }
    }

    // Show toast notification
    function showToast(message, type = 'success') {
        if (window.KTToast) {
            KTToast.show({
                icon: `<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-info-icon lucide-info"><circle cx="12" cy="12" r="10"/><path d="M12 16v-4"/><path d="M12 8h.01"/></svg>`,
                message: message,
                variant: type === 'success' ? 'success' : 'destructive',
                position: 'bottom-right',
            });
        }
    }

    // Format timestamp
    function formatTimestamp(timestamp) {
        if (!timestamp) return 'N/A';
        return new Date(timestamp).toLocaleString();
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

<CompanyLayout {breadcrumbs} {pageTitle}>
    <!-- Container -->
    <div class="kt-container-fluid">
        <div class="grid gap-5 lg:gap-7.5">
            <!-- Products Header -->
            <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4">
                <div class="flex flex-col gap-1">
                    <h1 class="text-2xl font-bold text-mono">My Products</h1>
                    <p class="text-sm text-secondary-foreground">
                        View your unlocked products from researched technologies
                    </p>
                </div>
            </div>

            <!-- Products Grid -->
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
                    {#if loading}
                        <!-- Loading skeleton -->
                        <div class="p-6">
                            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 p-4">
                                {#each Array(perPage) as _, i}
                                    <div class="kt-card animate-pulse">
                                        <div class="kt-card-body p-4">
                                            <!-- Product Image Skeleton -->
                                            <div class="flex items-center justify-center mb-4">
                                                <div class="kt-skeleton h-[180px] w-full rounded-sm"></div>
                                            </div>

                                            <!-- Product Info Skeleton -->
                                            <div class="mb-4">
                                                <div class="kt-skeleton h-6 w-3/4 mb-2"></div>
                                                <div class="kt-skeleton h-4 w-1/2 mb-2"></div>
                                                <div class="kt-skeleton h-3 w-full"></div>
                                            </div>

                                            <!-- Stock Details Skeleton -->
                                            <div class="space-y-1">
                                                <div class="flex justify-between">
                                                    <div class="kt-skeleton h-3 w-20"></div>
                                                    <div class="kt-skeleton h-3 w-16"></div>
                                                </div>
                                                <div class="flex justify-between">
                                                    <div class="kt-skeleton h-3 w-24"></div>
                                                    <div class="kt-skeleton h-3 w-12"></div>
                                                </div>
                                                <div class="flex justify-between">
                                                    <div class="kt-skeleton h-3 w-16"></div>
                                                    <div class="kt-skeleton h-3 w-20"></div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                {/each}
                            </div>
                        </div>
                    {:else if products.length === 0}
                        <!-- Empty state -->
                        <div class="p-10">
                            <div class="flex flex-col items-center justify-center text-center">
                                <div class="mb-4">
                                    <i class="ki-filled ki-abstract-26 text-4xl text-muted-foreground"></i>
                                </div>
                                <h3 class="text-lg font-semibold text-mono mb-2">No products found</h3>
                                <p class="text-sm text-secondary-foreground mb-4">
                                    {search ? 'No products match your search criteria.' : 'Research technologies to unlock products.'}
                                </p>
                                <a href="{route('company.technologies.research-page')}" class="kt-btn kt-btn-primary">
                                    <i class="ki-filled ki-plus text-base"></i>
                                    Research Technologies
                                </a>
                            </div>
                        </div>
                    {:else}
                        <!-- Products Grid -->
                        <div class="p-6">
                            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 p-4">
                                {#each products as product}
                                    <div class="kt-card kt-card-hover">
                                        <div class="kt-card-body p-4">
                                            <!-- Product Image -->
                                            <div class="flex items-center justify-center mb-4 cursor-pointer" on:click={() => openProductDrawer(product)}>
                                                {#if product.product.image_url}
                                                    <img 
                                                        src={product.product.image_url} 
                                                        alt={product.product.name}
                                                        class="h-[180px] w-full object-cover rounded-sm"
                                                    />
                                                {:else}
                                                    <div class="w-16 h-16 rounded-lg bg-accent/50 flex items-center justify-center">
                                                        <i class="ki-filled ki-abstract-26 text-2xl text-muted-foreground"></i>
                                                    </div>
                                                {/if}
                                            </div>

                                            <!-- Product Info -->
                                            <div class="mb-4 cursor-pointer" on:click={() => openProductDrawer(product)}>
                                                <h3 class="text-lg font-semibold text-mono mb-1">
                                                    {product.product.name}
                                                </h3>
                                                <p class="text-sm text-muted-foreground mb-2">
                                                    {product.product.type_name}
                                                </p>
                                                {#if product.product.description}
                                                    <p class="text-xs text-muted-foreground line-clamp-2">
                                                        {product.product.description}
                                                    </p>
                                                {/if}
                                            </div>

                                            <!-- Stock Details -->
                                            <div class="text-xs text-muted-foreground space-y-1 cursor-pointer" on:click={() => openProductDrawer(product)}>
                                                <div class="flex justify-between">
                                                    <span>Available Stock:</span>
                                                    <span class="font-medium">{product.available_stock}</span>
                                                </div>
                                                <div class="flex justify-between">
                                                    <span>Sale Price:</span>
                                                    <span class="font-medium">DZD {product.sale_price}</span>
                                                </div>
                                            </div>

                                            <!-- Action Buttons -->
                                            <div class="flex items-center justify-between mt-4 pt-3 border-t border-border">
                                                <button 
                                                    class="kt-btn kt-btn-sm kt-btn-outline kt-btn-primary"
                                                    on:click={() => openProductDrawer(product)}
                                                >
                                                    <i class="ki-filled ki-eye text-sm"></i>
                                                    View Details
                                                </button>
                                                <button 
                                                    class="kt-btn kt-btn-sm kt-btn-primary"
                                                    on:click={() => openPriceModal(product)}
                                                >
                                                    <i class="ki-filled ki-dollar text-sm"></i>
                                                    Set Price
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                {/each}
                            </div>
                        </div>

                        <!-- Pagination -->
                        {#if pagination && pagination.total > 0}
                            <div class="border-t border-gray-200">
                                <Pagination 
                                    {pagination} 
                                    {perPage}
                                    onPageChange={goToPage} 
                                    onPerPageChange={handlePerPageChange}
                                />
                            </div>
                        {/if}
                    {/if}
                </div>
            </div>
        </div>
    </div>
    <!-- End of Container -->

    <!-- Hidden button to trigger drawer -->
    <button style="display:none" data-kt-drawer-toggle="#product_drawer"></button>

    <!-- Hidden button to trigger modal -->
    <button style="display:none" data-kt-modal-toggle="#price_modal"></button>

    <!-- Product Details Drawer -->
    <div class="hidden kt-drawer kt-drawer-end card flex-col max-w-[90%] w-[450px] top-5 bottom-5 end-5 rounded-xl border border-border" data-kt-drawer="true" data-kt-drawer-container="body" id="product_drawer">
        <div class="flex items-center justify-between gap-2.5 text-sm text-mono font-semibold px-5 py-2.5 border-b border-b-border">
            Product Details
            <button class="kt-btn kt-btn-sm kt-btn-icon kt-btn-dim shrink-0" data-kt-drawer-dismiss="true" on:click={closeProductDrawer}>
                <i class="ki-filled ki-cross"></i>
            </button>
        </div>
        
        <div class="kt-card-content flex flex-col space-y-3 p-5 kt-scrollable-y-auto">
            {#if selectedProduct}
                <!-- Product Image -->
                <div class="kt-card relative items-center justify-center bg-accent/50 mb-6.5 h-[180px] shadow-none">
                    {#if selectedProduct.product.image_url}
                        <img 
                            src={selectedProduct.product.image_url} 
                            alt={selectedProduct.product.name}
                            class="h-[180px] w-full object-cover rounded-sm"
                        />
                    {:else}
                        <div class="flex items-center justify-center h-full">
                            <i class="ki-filled ki-abstract-26 text-4xl text-muted-foreground"></i>
                        </div>
                    {/if}
                </div>

                <!-- Product Name -->
                <span class="text-base font-medium text-mono">
                    {selectedProduct.product.name}
                </span>

                <!-- Product Description -->
                <span class="text-sm font-normal text-foreground block mb-7">
                    {#if selectedProduct.product.description}
                        {selectedProduct.product.description}
                    {:else}
                        No description available for this product.
                    {/if}
                </span>

                <!-- Product Details -->
                <div class="flex flex-col gap-2.5">
                    <div class="flex items-center gap-2.5">
                        <span class="text-xs font-normal text-foreground min-w-14 xl:min-w-24 shrink-0">
                            Type
                        </span>
                        <div>
                            <span class="kt-badge kt-badge-{selectedProduct.product.type === 'finished_product' ? 'success' : selectedProduct.product.type === 'component' ? 'warning' : 'info'} kt-badge-sm">
                                {selectedProduct.product.type_name}
                            </span>
                        </div>
                    </div>
                    <div class="flex items-center gap-2.5">
                        <span class="text-xs font-normal text-foreground min-w-14 xl:min-w-24 shrink-0">
                            Unit
                        </span>
                        <div>
                            <span class="text-xs font-medium text-foreground">
                                {selectedProduct.product.measurement_unit}
                            </span>
                        </div>
                    </div>
                    <div class="flex items-center gap-2.5">
                        <span class="text-xs font-normal text-foreground min-w-14 xl:min-w-24 shrink-0">
                            Elasticity
                        </span>
                        <div>
                            <span class="text-xs font-medium text-foreground">
                                {selectedProduct.product.elasticity_coefficient}
                            </span>
                        </div>
                    </div>
                    {#if selectedProduct.product.has_expiration}
                        <div class="flex items-center gap-2.5">
                            <span class="text-xs font-normal text-foreground min-w-14 xl:min-w-24 shrink-0">
                                Shelf Life
                            </span>
                            <div>
                                <span class="text-xs font-medium text-foreground">
                                    {selectedProduct.product.shelf_life_days} days
                                </span>
                            </div>
                        </div>
                    {/if}
                </div>

                <!-- Stock Information -->
                <div class="border-t border-border pt-4">
                    <h3 class="text-sm font-semibold text-mono mb-3">Stock Information</h3>
                    <div class="space-y-2">
                        <div class="flex justify-between">
                            <span class="text-xs text-muted-foreground">Available Stock:</span>
                            <span class="text-xs font-medium">{selectedProduct.available_stock}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-xs text-muted-foreground">Sale Price:</span>
                            <span class="text-xs font-medium">DZD {selectedProduct.sale_price}</span>
                        </div>
                    </div>
                </div>

                <!-- Recipe Section -->
                {#if selectedProduct.product.recipes && selectedProduct.product.recipes.length > 0}
                    <div class="border-t border-border pt-4">
                        <h3 class="text-sm font-semibold text-mono mb-3">Recipe</h3>
                        <div class="space-y-3">
                            {#each selectedProduct.product.recipes as recipe}
                                <div class="kt-card">
                                    <div class="kt-card-body p-3">
                                        <div class="flex items-center gap-3">
                                            <div class="flex-shrink-0">
                                                {#if recipe.material && recipe.material.image_url}
                                                    <img 
                                                        src={recipe.material.image_url} 
                                                        alt={recipe.material.name}
                                                        class="w-12 h-12 rounded-lg object-cover"
                                                    />
                                                {:else}
                                                    <div class="w-12 h-12 rounded-lg bg-accent/50 flex items-center justify-center">
                                                        <i class="ki-filled ki-abstract-26 text-lg text-muted-foreground"></i>
                                                    </div>
                                                {/if}
                                            </div>
                                            <div class="flex-1 min-w-0">
                                                <h4 class="text-sm font-semibold text-mono mb-1 truncate">
                                                    {recipe.material ? recipe.material.name : 'Unknown Material'}
                                                </h4>
                                                <p class="text-xs text-muted-foreground">
                                                    Quantity: {recipe.quantity} {recipe.material ? recipe.material.measurement_unit : 'units'}
                                                </p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            {/each}
                        </div>
                    </div>
                {:else}
                    <div class="border-t border-border pt-4">
                        <h3 class="text-sm font-semibold text-mono mb-3">Recipe</h3>
                        <div class="kt-card">
                            <div class="kt-card-body p-4">
                                <div class="flex flex-col items-center justify-center text-center">
                                    <i class="ki-filled ki-abstract-26 text-2xl text-muted-foreground mb-2"></i>
                                    <p class="text-xs text-muted-foreground">No recipe available</p>
                                </div>
                            </div>
                        </div>
                    </div>
                {/if}
            {/if}
        </div>
    </div>

    <!-- Price Update Modal -->
    <div class="kt-modal" data-kt-modal="true" id="price_modal">
        <div class="kt-modal-content max-w-[500px] top-[5%]">
            <div class="kt-modal-header">
                <h3 class="kt-modal-title">Update Sale Price</h3>
                <button
                    type="button"
                    class="kt-modal-close"
                    aria-label="Close modal"
                    data-kt-modal-dismiss="#price_modal"
                >
                    <svg
                        xmlns="http://www.w3.org/2000/svg"
                        width="24"
                        height="24"
                        viewBox="0 0 24 24"
                        fill="none"
                        stroke="currentColor"
                        stroke-width="2"
                        stroke-linecap="round"
                        stroke-linejoin="round"
                        class="lucide lucide-x"
                        aria-hidden="true"
                    >
                        <path d="M18 6 6 18"></path>
                        <path d="m6 6 12 12"></path>
                    </svg>
                </button>
            </div>
            <div class="kt-modal-body">
                {#if selectedProductForPrice}
                    <div class="space-y-4">
                        <!-- Product Info -->
                        <div class="flex items-center gap-4">
                            <div class="flex-shrink-0">
                                {#if selectedProductForPrice.product.image_url}
                                    <img 
                                        src={selectedProductForPrice.product.image_url} 
                                        alt={selectedProductForPrice.product.name}
                                        class="w-16 h-16 rounded-lg object-cover"
                                    />
                                {:else}
                                    <div class="w-16 h-16 rounded-lg bg-accent/50 flex items-center justify-center">
                                        <i class="ki-filled ki-abstract-26 text-xl text-muted-foreground"></i>
                                    </div>
                                {/if}
                            </div>
                            <div class="flex-1">
                                <h4 class="font-semibold text-mono mb-1">{selectedProductForPrice.product.name}</h4>
                                <p class="text-sm text-muted-foreground mb-1">{selectedProductForPrice.product.type_name}</p>
                                <p class="text-xs text-muted-foreground">Current Price: DZD {selectedProductForPrice.sale_price}</p>
                            </div>
                        </div>

                        <!-- Alert -->
                        <div class="kt-alert kt-alert-warning-light">
                            <div class="kt-alert-icon">
                                <i class="ki-filled ki-information-1"></i>
                            </div>
                            <div class="kt-alert-content">
                                <h4 class="kt-alert-title">Price Impact Warning</h4>
                                <p class="kt-alert-text">
                                    Changing the sale price will affect the demand for this product using the elasticity coefficient ({selectedProductForPrice.product.elasticity_coefficient}). 
                                    <strong>Higher prices typically reduce demand, while lower prices increase demand.</strong>
                                </p>
                            </div>
                        </div>

                        <!-- Price Input -->
                        <div class="kt-card">
                            <div class="kt-card-body p-4">
                                <h5 class="font-medium text-mono mb-3">New Sale Price</h5>
                                <div class="space-y-2">
                                    <label class="text-sm font-medium text-mono">Price (DZD)</label>
                                    <input 
                                        type="number" 
                                        class="kt-input w-full" 
                                        bind:value={newSalePrice}
                                        min="0.001"
                                        step="0.001"
                                        placeholder="Enter new sale price..."
                                    />
                                    <p class="text-xs text-muted-foreground">
                                        Enter the new sale price in Algerian Dinars (DZD)
                                    </p>
                                </div>
                            </div>
                        </div>

                        <!-- Price Change Preview -->
                        {#if newSalePrice !== selectedProductForPrice.sale_price}
                            <div class="kt-card bg-accent/50">
                                <div class="kt-card-body p-4">
                                    <h5 class="font-medium text-mono mb-3">Price Change Preview</h5>
                                    <div class="space-y-2">
                                        <div class="flex justify-between">
                                            <span class="text-sm text-muted-foreground">Current Price:</span>
                                            <span class="text-sm font-medium">DZD {selectedProductForPrice.sale_price}</span>
                                        </div>
                                        <div class="flex justify-between">
                                            <span class="text-sm text-muted-foreground">New Price:</span>
                                            <span class="text-sm font-medium">DZD {newSalePrice}</span>
                                        </div>
                                        <div class="flex justify-between">
                                            <span class="text-sm text-muted-foreground">Change:</span>
                                            <span class="text-sm font-medium {newSalePrice > selectedProductForPrice.sale_price ? 'text-destructive' : 'text-green-600'}">
                                                {newSalePrice > selectedProductForPrice.sale_price ? '+' : ''}DZD {(newSalePrice - selectedProductForPrice.sale_price).toFixed(3)}
                                            </span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        {/if}
                    </div>
                {/if}
            </div>
            <div class="kt-modal-footer">
                <div></div>
                <div class="flex gap-4">
                    <button
                        class="kt-btn kt-btn-secondary"
                        data-kt-modal-dismiss="#price_modal"
                        on:click={closePriceModal}
                        disabled={loadingPriceUpdate}
                    >
                        Cancel
                    </button>
                    <button 
                        class="kt-btn kt-btn-primary"
                        on:click={updateSalePrice}
                        disabled={loadingPriceUpdate || newSalePrice <= 0}
                    >
                        {#if loadingPriceUpdate}
                            <i class="ki-filled ki-loading ki-spin text-sm"></i>
                            Updating...
                        {:else}
                            <i class="ki-filled ki-check text-sm"></i>
                            Update Price
                        {/if}
                    </button>
                </div>
            </div>
        </div>
    </div>
</CompanyLayout> 