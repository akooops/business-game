<script>
    import CompanyLayout from '../../Layouts/CompanyLayout.svelte';
    import Pagination from '../../Components/Pagination.svelte';
    import Select2 from '../../Components/Forms/Select2.svelte';
    import Flatpickr from '../../Components/Forms/Flatpickr.svelte';
    import { onMount, tick } from 'svelte';
    import { page } from '@inertiajs/svelte'

    // Define breadcrumbs for this page
    const breadcrumbs = [
        {
            title: 'Purchases',
            url: route('company.purchases.index'),
            active: false
        },
        {
            title: 'Index',
            url: route('company.purchases.index'),
            active: true
        }
    ];
    
    const pageTitle = 'My Purchases';

    // Reactive variables
    let purchases = [];
    let pagination = {};
    let loading = true;
    let search = '';
    let perPage = 10;
    let currentPage = 1;
    let searchTimeout;
    let showFilters = false;

    // Filter variables
    let supplierIdFilter = '';
    let productIdFilter = '';
    let statusFilter = '';
    let minTotalCostFilter = '';
    let maxTotalCostFilter = '';
    let minOrderedAtFilter = '';
    let maxOrderedAtFilter = '';
    let minEstimatedDeliveredAtFilter = '';
    let maxEstimatedDeliveredAtFilter = '';
    let minDeliveredAtFilter = '';
    let maxDeliveredAtFilter = '';

    // Select2 component references
    let supplierSelectComponent;
    let productSelectComponent;

    // Flatpickr component references
    let minOrderedAtFlatpickr;
    let maxOrderedAtFlatpickr;
    let minEstimatedDeliveredAtFlatpickr;
    let maxEstimatedDeliveredAtFlatpickr;
    let minDeliveredAtFlatpickr;
    let maxDeliveredAtFlatpickr;

    // Drawer state
    let selectedPurchase = null;
    let showPurchaseDrawer = false;

    // Fetch purchases data
    async function fetchPurchases() {
        loading = true;
        try {
            const params = new URLSearchParams({
                page: currentPage,
                perPage: perPage,
                search: search
            });

            // Add filters to params
            if (supplierIdFilter) params.append('supplier_id', supplierIdFilter);
            if (productIdFilter) params.append('product_id', productIdFilter);
            if (statusFilter) params.append('status', statusFilter);
            if (minTotalCostFilter) params.append('min_total_cost', minTotalCostFilter);
            if (maxTotalCostFilter) params.append('max_total_cost', maxTotalCostFilter);
            if (minOrderedAtFilter) params.append('min_ordered_at', minOrderedAtFilter);
            if (maxOrderedAtFilter) params.append('max_ordered_at', maxOrderedAtFilter);
            if (minEstimatedDeliveredAtFilter) params.append('min_estimated_delivered_at', minEstimatedDeliveredAtFilter);
            if (maxEstimatedDeliveredAtFilter) params.append('max_estimated_delivered_at', maxEstimatedDeliveredAtFilter);
            if (minDeliveredAtFilter) params.append('min_delivered_at', minDeliveredAtFilter);
            if (maxDeliveredAtFilter) params.append('max_delivered_at', maxDeliveredAtFilter);
            
            const response = await fetch(route('company.purchases.index') + '?' + params.toString(), {
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                }
            });
            
            const data = await response.json();
            purchases = data.purchases;
            pagination = data.pagination;
            
            // Wait for DOM to update, then initialize menus
            await tick();
            if (window.KTMenu) {
                window.KTMenu.init();
            }
        } catch (error) {
            console.error('Error fetching purchases:', error);
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
            fetchPurchases();
        }, 500);
    }

    // Handle search input change
    function handleSearchInput(event) {
        search = event.target.value;
        handleSearch();
    }

    // Handle filter changes
    function handleFilterChange() {
        currentPage = 1;
        fetchPurchases();
    }

    // Handle pagination
    function goToPage(page) {
        if (page && page !== currentPage) {
            currentPage = page;
            fetchPurchases();
        }
    }

    // Handle per page change
    function handlePerPageChange(newPerPage) {
        perPage = newPerPage;
        currentPage = 1;
        fetchPurchases();
    }

    // Clear all filters
    function clearAllFilters() {
        supplierIdFilter = '';
        productIdFilter = '';
        statusFilter = '';
        minTotalCostFilter = '';
        maxTotalCostFilter = '';
        minOrderedAtFilter = '';
        maxOrderedAtFilter = '';
        minEstimatedDeliveredAtFilter = '';
        maxEstimatedDeliveredAtFilter = '';
        minDeliveredAtFilter = '';
        maxDeliveredAtFilter = '';
        
        if (supplierSelectComponent) {
            supplierSelectComponent.clear();
        }
        if (productSelectComponent) {
            productSelectComponent.clear();
        }
        
        // Clear Flatpickr components
        if (minOrderedAtFlatpickr) minOrderedAtFlatpickr.clear();
        if (maxOrderedAtFlatpickr) maxOrderedAtFlatpickr.clear();
        if (minEstimatedDeliveredAtFlatpickr) minEstimatedDeliveredAtFlatpickr.clear();
        if (maxEstimatedDeliveredAtFlatpickr) maxEstimatedDeliveredAtFlatpickr.clear();
        if (minDeliveredAtFlatpickr) minDeliveredAtFlatpickr.clear();
        if (maxDeliveredAtFlatpickr) maxDeliveredAtFlatpickr.clear();
        
        currentPage = 1;
        fetchPurchases();
    }

    // Toggle filters visibility
    function toggleFilters() {
        showFilters = !showFilters;
    }

    // Handle supplier selection
    function handleSupplierSelect(event) {
        supplierIdFilter = event.detail.value;
        handleFilterChange();
    }

    // Handle product selection
    function handleProductSelect(event) {
        productIdFilter = event.detail.value;
        handleFilterChange();
    }

    // Handle date changes
    function handleMinOrderedAtChange(event) {
        minOrderedAtFilter = event.detail.value;
        handleFilterChange();
    }

    function handleMaxOrderedAtChange(event) {
        maxOrderedAtFilter = event.detail.value;
        handleFilterChange();
    }

    function handleMinEstimatedDeliveredAtChange(event) {
        minEstimatedDeliveredAtFilter = event.detail.value;
        handleFilterChange();
    }

    function handleMaxEstimatedDeliveredAtChange(event) {
        maxEstimatedDeliveredAtFilter = event.detail.value;
        handleFilterChange();
    }

    function handleMinDeliveredAtChange(event) {
        minDeliveredAtFilter = event.detail.value;
        handleFilterChange();
    }

    function handleMaxDeliveredAtChange(event) {
        maxDeliveredAtFilter = event.detail.value;
        handleFilterChange();
    }

    // Open purchase drawer
    function openPurchaseDrawer(purchase) {
        selectedPurchase = purchase;
        showPurchaseDrawer = true;
        
        // Show drawer
        const toggleButton = document.querySelector('[data-kt-drawer-toggle="#purchase_drawer"]');
        if (toggleButton) {
            toggleButton.click();
        }
    }

    // Close purchase drawer
    function closePurchaseDrawer() {
        showPurchaseDrawer = false;
        selectedPurchase = null;
    }

    // Get status badge class
    function getStatusBadgeClass(status) {
        switch (status) {
            case 'pending':
                return 'kt-badge-warning';
            case 'ordered':
                return 'kt-badge-primary';
            case 'delivered':
                return 'kt-badge-success';
            case 'cancelled':
                return 'kt-badge-destructive';
            default:
                return 'kt-badge-outline';
        }
    }

    // Get status text
    function getStatusText(status) {
        switch (status) {
            case 'pending':
                return 'Pending';
            case 'ordered':
                return 'Ordered';
            case 'delivered':
                return 'Delivered';
            case 'cancelled':
                return 'Cancelled';
            default:
                return status;
        }
    }

    onMount(() => {
        fetchPurchases();
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
            <!-- Purchases Header -->
            <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4">
                <div class="flex flex-col gap-1">
                    <h1 class="text-2xl font-bold text-mono">My Purchases</h1>
                    <p class="text-sm text-secondary-foreground">
                        Track your purchase orders and deliveries
                    </p>
                </div>   
                
                <div class="flex items-center gap-3">
                    <a href="{route('company.purchases.purchase-page')}" class="kt-btn kt-btn-primary">
                        <i class="ki-filled ki-plus text-base"></i>
                        Make Purchase
                    </a>
                </div>  
            </div>

            <!-- Purchases Grid -->
            <div class="kt-card">
                <div class="kt-card-header">
                    <div class="kt-card-toolbar">
                        <div class="kt-input max-w-64 w-64">
                            <i class="ki-filled ki-magnifier"></i>
                            <input 
                                type="text" 
                                class="kt-input" 
                                placeholder="Search purchases..." 
                                bind:value={search}
                                on:input={handleSearchInput}
                            />
                        </div>
                        
                        <!-- Filters Toggle -->
                        <div class="flex items-center gap-3 ml-auto">
                            <!-- Filter Toggle Button -->
                            <button 
                                class="kt-btn kt-btn-outline"
                                on:click={toggleFilters}
                            >
                                <i class="ki-filled ki-filter text-sm"></i>
                                {showFilters ? 'Hide Filters' : 'Show Filters'}
                            </button>
                            
                            <!-- Clear Filters Button -->
                            {#if supplierIdFilter || productIdFilter || statusFilter || minTotalCostFilter || maxTotalCostFilter || minOrderedAtFilter || maxOrderedAtFilter || minEstimatedDeliveredAtFilter || maxEstimatedDeliveredAtFilter || minDeliveredAtFilter || maxDeliveredAtFilter}
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
                            <!-- Purchase Properties -->
                            <div class="space-y-2">
                                <h4 class="text-sm font-medium text-gray-700">Purchase Properties</h4>
                                <!-- Status -->
                                <select 
                                    class="kt-select w-full" 
                                    bind:value={statusFilter}
                                    on:change={handleFilterChange}
                                >
                                    <option value="">All Statuses</option>
                                    <option value="pending">Pending</option>
                                    <option value="ordered">Ordered</option>
                                    <option value="delivered">Delivered</option>
                                    <option value="cancelled">Cancelled</option>
                                </select>
                            </div>

                            <!-- Supplier -->
                            <div class="space-y-2">
                                <h4 class="text-sm font-medium text-gray-700">Supplier</h4>
                                <Select2
                                    bind:this={supplierSelectComponent}
                                    id="supplier-filter"
                                    placeholder="All Suppliers"
                                    allowClear={true}
                                    on:select={handleSupplierSelect}
                                    on:clear={() => {
                                        supplierIdFilter = '';
                                        handleFilterChange();
                                    }}
                                    ajax={{
                                        url: route('company.suppliers.index'),
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
                                                results: data.suppliers.map(supplier => ({
                                                    id: supplier.id,
                                                    text: supplier.name,
                                                    name: supplier.name,
                                                }))
                                            };
                                        },
                                        cache: true
                                    }}
                                    templateResult={function(data) {
                                        if (data.loading) return data.text;
                                        
                                        const $elem = globalThis.$('<div class="flex items-center gap-2">' +
                                            '<i class="ki-filled ki-shop text-sm text-muted-foreground"></i>' +
                                            '<span class="font-medium text-sm">' + data.name + '</span>' +
                                            '</div>');
                                        return $elem;
                                    }}
                                    templateSelection={function(data) {
                                        if (!data.id) return data.text;
                                        return data.name;
                                    }}
                                />
                            </div>

                            <!-- Product -->
                            <div class="space-y-2">
                                <h4 class="text-sm font-medium text-gray-700">Product</h4>
                                <Select2
                                    bind:this={productSelectComponent}
                                    id="product-filter"
                                    placeholder="All Products"
                                    allowClear={true}
                                    on:select={handleProductSelect}
                                    on:clear={() => {
                                        productIdFilter = '';
                                        handleFilterChange();
                                    }}
                                    ajax={{
                                        url: route('company.products.index'),
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
                                                    id: product.product.id,
                                                    text: product.product.name,
                                                    name: product.product.name,
                                                }))
                                            };
                                        },
                                        cache: true
                                    }}
                                    templateResult={function(data) {
                                        if (data.loading) return data.text;
                                        
                                        const $elem = globalThis.$('<div class="flex items-center gap-2">' +
                                            '<i class="ki-filled ki-abstract-26 text-sm text-muted-foreground"></i>' +
                                            '<span class="font-medium text-sm">' + data.name + '</span>' +
                                            '</div>');
                                        return $elem;
                                    }}
                                    templateSelection={function(data) {
                                        if (!data.id) return data.text;
                                        return data.name;
                                    }}
                                />
                            </div>

                            <!-- Total Cost Range -->
                            <div class="space-y-2">
                                <h4 class="text-sm font-medium text-gray-700">Total Cost Range</h4>
                                <div class="flex gap-2">
                                    <input 
                                        type="number" 
                                        class="kt-input flex-1" 
                                        placeholder="Min Cost" 
                                        bind:value={minTotalCostFilter}
                                        on:input={handleFilterChange}
                                        min="0"
                                        step="0.01"
                                    />
                                    <input 
                                        type="number" 
                                        class="kt-input flex-1" 
                                        placeholder="Max Cost" 
                                        bind:value={maxTotalCostFilter}
                                        on:input={handleFilterChange}
                                        min="0"
                                        step="0.01"
                                    />
                                </div>
                            </div>

                            <!-- Ordered Date Range -->
                            <div class="space-y-2">
                                <h4 class="text-sm font-medium text-gray-700">Ordered Date Range</h4>
                                <div class="flex gap-2">
                                    <Flatpickr
                                        bind:this={minOrderedAtFlatpickr}
                                        id="min-ordered-at"
                                        placeholder="Min Date"
                                        config={{
                                            enableTime: true,
                                            dateFormat: 'Y-m-d H:i',
                                            time_24hr: true
                                        }}
                                        on:change={handleMinOrderedAtChange}
                                    />
                                    <Flatpickr
                                        bind:this={maxOrderedAtFlatpickr}
                                        id="max-ordered-at"
                                        placeholder="Max Date"
                                        config={{
                                            enableTime: true,
                                            dateFormat: 'Y-m-d H:i',
                                            time_24hr: true
                                        }}
                                        on:change={handleMaxOrderedAtChange}
                                    />
                                </div>
                            </div>

                            <!-- Estimated Delivery Range -->
                            <div class="space-y-2">
                                <h4 class="text-sm font-medium text-gray-700">Estimated Delivery Range</h4>
                                <div class="flex gap-2">
                                    <Flatpickr
                                        bind:this={minEstimatedDeliveredAtFlatpickr}
                                        id="min-estimated-delivered-at"
                                        placeholder="Min Date"
                                        config={{
                                            enableTime: true,
                                            dateFormat: 'Y-m-d H:i',
                                            time_24hr: true
                                        }}
                                        on:change={handleMinEstimatedDeliveredAtChange}
                                    />
                                    <Flatpickr
                                        bind:this={maxEstimatedDeliveredAtFlatpickr}
                                        id="max-estimated-delivered-at"
                                        placeholder="Max Date"
                                        config={{
                                            enableTime: true,
                                            dateFormat: 'Y-m-d H:i',
                                            time_24hr: true
                                        }}
                                        on:change={handleMaxEstimatedDeliveredAtChange}
                                    />
                                </div>
                            </div>

                            <!-- Delivered Date Range -->
                            <div class="space-y-2">
                                <h4 class="text-sm font-medium text-gray-700">Delivered Date Range</h4>
                                <div class="flex gap-2">
                                    <Flatpickr
                                        bind:this={minDeliveredAtFlatpickr}
                                        id="min-delivered-at"
                                        placeholder="Min Date"
                                        config={{
                                            enableTime: true,
                                            dateFormat: 'Y-m-d H:i',
                                            time_24hr: true
                                        }}
                                        on:change={handleMinDeliveredAtChange}
                                    />
                                    <Flatpickr
                                        bind:this={maxDeliveredAtFlatpickr}
                                        id="max-delivered-at"
                                        placeholder="Max Date"
                                        config={{
                                            enableTime: true,
                                            dateFormat: 'Y-m-d H:i',
                                            time_24hr: true
                                        }}
                                        on:change={handleMaxDeliveredAtChange}
                                    />
                                </div>
                            </div>
                        </div>
                    </div>
                {/if}

                <div class="kt-card-content p-0">
                    {#if loading}
                        <!-- Loading skeleton -->
                        <div class="p-6">
                            <div class="grid grid-cols-1 gap-6 p-4">
                                {#each Array(perPage) as _, i}
                                    <div class="kt-card animate-pulse">
                                        <div class="kt-card-header justify-start bg-muted/70 gap-9 h-auto py-5">
                                            <div class="kt-skeleton h-4 w-20"></div>
                                            <div class="kt-skeleton h-4 w-24"></div>
                                            <div class="kt-skeleton h-4 w-16"></div>
                                        </div>
                                        <div class="kt-card-content p-5 lg:p-7.5 space-y-5">
                                            <div class="kt-card">
                                                <div class="kt-card-content flex items-center flex-wrap justify-between gap-4.5 p-2 pe-5">
                                                    <div class="flex items-center gap-3.5">
                                                        <div class="kt-skeleton w-[90px] h-[70px] rounded"></div>
                                                        <div class="flex flex-col gap-1">
                                                            <div class="kt-skeleton h-4 w-32"></div>
                                                            <div class="kt-skeleton h-3 w-24"></div>
                                                        </div>
                                                    </div>
                                                    <div class="flex flex-col gap-1.5">
                                                        <div class="kt-skeleton h-3 w-8"></div>
                                                        <div class="kt-skeleton h-4 w-16"></div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                {/each}
                            </div>
                        </div>
                    {:else if purchases.length === 0}
                        <!-- Empty state -->
                        <div class="p-10">
                            <div class="flex flex-col items-center justify-center text-center">
                                <div class="mb-4">
                                    <i class="ki-filled ki-handcart text-4xl text-muted-foreground"></i>
                                </div>
                                <h3 class="text-lg font-semibold text-mono mb-2">No purchases found</h3>
                                <p class="text-sm text-secondary-foreground mb-4">
                                    {search ? 'No purchases match your search criteria.' : 'No purchases available. Start making purchases to see them here.'}
                                </p>
                            </div>
                        </div>
                    {:else}
                        <!-- Purchases Grid -->
                        <div class="p-6">
                            <div class="grid grid-cols-1 gap-6 p-4">
                                {#each purchases as purchase}
                                    <div class="kt-card kt-card-hover cursor-pointer" on:click={() => openPurchaseDrawer(purchase)}>
                                        <div class="kt-card-header justify-start bg-muted/70 gap-9 h-auto py-5">
                                            <div class="flex flex-col gap-1.5">
                                                <span class="text-xs font-normal text-secondary-foreground">
                                                    Order ID
                                                </span>
                                                <span class="text-sm font-medium text-mono">
                                                    #{purchase.id}
                                                </span>
                                            </div>
                                            <div class="flex flex-col gap-1.5">
                                                <span class="text-xs font-normal text-secondary-foreground">
                                                    Status
                                                </span>
                                                <span class="kt-badge kt-badge-sm {getStatusBadgeClass(purchase.status)}">
                                                    {getStatusText(purchase.status)}
                                                </span>
                                            </div>
                                            <div class="flex flex-col gap-1.5">
                                                <span class="text-xs font-normal text-secondary-foreground">
                                                    Order placed
                                                </span>
                                                <span class="text-sm font-medium text-mono">
                                                    {formatTimestamp(purchase.ordered_at)}
                                                </span>
                                            </div>
                                            <div class="flex flex-col gap-1.5">
                                                <span class="text-xs font-normal text-secondary-foreground">
                                                    Estimated Delivery
                                                </span>
                                                <span class="text-sm font-medium text-mono">
                                                    {formatTimestamp(purchase.estimated_delivered_at)}
                                                </span>
                                            </div>
                                            {#if purchase.delivered_at}
                                            <div class="flex flex-col gap-1.5">
                                                <span class="text-xs font-normal text-secondary-foreground">
                                                    Delivered At
                                                </span>
                                                <span class="text-sm font-medium text-mono">
                                                    {formatTimestamp(purchase.delivered_at)}
                                                </span>
                                            </div>
                                            {/if}
                                        </div>
                                        <div class="kt-card-content p-5 lg:p-7.5 space-y-5">
                                            <div class="kt-card">
                                                <div class="kt-card-content flex items-center flex-wrap justify-between gap-4.5 p-2 pe-5">
                                                    <div class="flex items-center gap-3.5">
                                                        <div class="kt-card flex items-center justify-center bg-accent/50 h-[70px] w-[90px] shadow-none">
                                                            {#if purchase.product.image_url}
                                                                <img 
                                                                    alt={purchase.product.name}
                                                                    class="cursor-pointer h-[70px] object-cover rounded-sm" 
                                                                    src={purchase.product.image_url}
                                                                />
                                                            {:else}
                                                                <i class="ki-filled ki-abstract-26 text-2xl text-muted-foreground"></i>
                                                            {/if}
                                                        </div>
                                                        <div class="flex flex-col gap-1">
                                                            <a class="hover:text-primary text-sm font-medium text-mono leading-5.5" href="#">
                                                                {purchase.product.name}
                                                            </a>
                                                            <div class="flex items-center gap-1.5">
                                                                <span class="text-xs font-normal text-secondary-foreground uppercase">
                                                                    Supplier:
                                                                    <span class="text-xs font-medium text-foreground">
                                                                        {purchase.supplier.name}
                                                                    </span>
                                                                </span>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="flex flex-col gap-1.5">
                                                        <span class="text-xs font-normal text-secondary-foreground text-end">
                                                            {purchase.quantity} x
                                                        </span>
                                                        <div class="flex items-center flex-wrap gap-1.5">
                                                            <span class="text-sm font-semibold text-mono">
                                                                DZD {purchase.sale_price}
                                                            </span>
                                                        </div>
                                                    </div>
                                                </div>
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
    <button style="display:none" data-kt-drawer-toggle="#purchase_drawer"></button>

    <!-- Purchase Details Drawer -->
    <div class="hidden kt-drawer kt-drawer-end card flex-col max-w-[90%] w-[450px] top-5 bottom-5 end-5 rounded-xl border border-border" data-kt-drawer="true" data-kt-drawer-container="body" id="purchase_drawer">
        <div class="flex items-center justify-between gap-2.5 text-sm text-mono font-semibold px-5 py-2.5 border-b border-b-border">
            Purchase Details
            <button class="kt-btn kt-btn-sm kt-btn-icon kt-btn-dim shrink-0" data-kt-drawer-dismiss="true" on:click={closePurchaseDrawer}>
                <i class="ki-filled ki-cross"></i>
            </button>
        </div>
        
        <div class="kt-card-content flex flex-col space-y-3 p-5 kt-scrollable-y-auto">
            {#if selectedPurchase}
                <!-- Purchase Image -->
                <div class="kt-card relative items-center justify-center bg-accent/50 mb-6.5 h-[180px] shadow-none">
                    {#if selectedPurchase.product.image_url}
                        <img 
                            src={selectedPurchase.product.image_url} 
                            alt={selectedPurchase.product.name}
                            class="h-[180px] w-full object-cover rounded-sm"
                        />
                    {:else}
                        <div class="flex items-center justify-center h-full">
                            <i class="ki-filled ki-abstract-26 text-4xl text-muted-foreground"></i>
                        </div>
                    {/if}
                </div>

                <!-- Purchase Name -->
                <span class="text-base font-medium text-mono">
                    {selectedPurchase.product.name}
                </span>

                
                <!-- Purchase Details -->
                <div class="flex flex-col gap-2.5">
                    <div class="flex items-center gap-2.5">
                        <span class="text-xs font-normal text-foreground min-w-14 xl:min-w-24 shrink-0">
                            Order ID
                        </span>
                        <div>
                            <span class="text-xs font-medium text-foreground">
                                #{selectedPurchase.id}
                            </span>
                        </div>
                    </div>
                    <div class="flex items-center gap-2.5">
                        <span class="text-xs font-normal text-foreground min-w-14 xl:min-w-24 shrink-0">
                            Status
                        </span>
                        <div>
                            <span class="kt-badge kt-badge-sm {getStatusBadgeClass(selectedPurchase.status)}">
                                {getStatusText(selectedPurchase.status)}
                            </span>
                        </div>
                    </div>
                    <div class="flex items-center gap-2.5">
                        <span class="text-xs font-normal text-foreground min-w-14 xl:min-w-24 shrink-0">
                            Supplier
                        </span>
                        <div>
                            <span class="text-xs font-medium text-foreground">
                                {selectedPurchase.supplier.name}
                            </span>
                        </div>
                    </div>
                    <div class="flex items-center gap-2.5">
                        <span class="text-xs font-normal text-foreground min-w-14 xl:min-w-24 shrink-0">
                            Quantity
                        </span>
                        <div>
                            <span class="text-xs font-medium text-foreground">
                                {selectedPurchase.quantity}
                            </span>
                        </div>
                    </div>
                    <div class="flex items-center gap-2.5">
                        <span class="text-xs font-normal text-foreground min-w-14 xl:min-w-24 shrink-0">
                            Sale Price
                        </span>
                        <div>
                            <span class="text-xs font-medium text-foreground">
                                DZD {selectedPurchase.sale_price}
                            </span>
                        </div>
                    </div>
                    <div class="flex items-center gap-2.5">
                        <span class="text-xs font-normal text-foreground min-w-14 xl:min-w-24 shrink-0">
                            Shipping Time
                        </span>
                        <div>
                            <span class="text-xs font-medium text-foreground">
                                {selectedPurchase.supplier.shipping_time || 0} days
                            </span>
                        </div>
                    </div>
                    <div class="flex items-center gap-2.5">
                        <span class="text-xs font-normal text-foreground min-w-14 xl:min-w-24 shrink-0">
                            Carbon Footprint
                        </span>
                        <div>
                            <span class="text-xs font-medium text-foreground">
                                {selectedPurchase.carbon_footprint * selectedPurchase.quantity} kg CO2
                            </span>
                        </div>
                    </div>
                    <div class="flex items-center gap-2.5">
                        <span class="text-xs font-normal text-foreground min-w-14 xl:min-w-24 shrink-0">
                            Ordered At
                        </span>
                        <div>
                            <span class="text-xs font-medium text-foreground">
                                {formatTimestamp(selectedPurchase.ordered_at)}
                            </span>
                        </div>
                    </div>
                    <div class="flex items-center gap-2.5">
                        <span class="text-xs font-normal text-foreground min-w-14 xl:min-w-24 shrink-0">
                            Estimated Delivery
                        </span>
                        <div>
                            <span class="text-xs font-medium text-foreground">
                                {formatTimestamp(selectedPurchase.estimated_delivered_at)}
                            </span>
                        </div>
                    </div>
                    {#if selectedPurchase.delivered_at}
                        <div class="flex items-center gap-2.5">
                            <span class="text-xs font-normal text-foreground min-w-14 xl:min-w-24 shrink-0">
                                Delivered At
                            </span>
                            <div>
                                <span class="text-xs font-medium text-foreground">
                                    {formatTimestamp(selectedPurchase.delivered_at)}
                                </span>
                            </div>
                        </div>
                    {/if}
                </div>

                <div class="border-t border-border"></div>

                <!-- Order Summary -->
                <div class="kt-card bg-accent/50">
                    <div class="kt-card-header px-5">
                        <h3 class="kt-card-title">
                            Order Summary
                        </h3>
                    </div>
                    <div class="kt-card-content px-5 py-4 space-y-2">
                        <h4 class="text-sm font-medium text-mono mb-3.5">
                            Price Details
                        </h4>
                        <div class="flex justify-between items-center">
                            <span class="text-sm font-normal text-secondary-foreground">
                                Subtotal
                            </span>
                            <span class="text-sm font-medium text-mono">
                                DZD {(selectedPurchase.total_sale_price)}
                            </span>
                        </div>
                        <div class="flex justify-between items-center">
                            <span class="text-sm font-normal text-secondary-foreground">
                                Shipping
                            </span>
                            <span class="text-sm font-medium text-mono">
                                DZD {(selectedPurchase.total_shipping_cost)}
                            </span>
                        </div>
                        {#if selectedPurchase.supplier.country}
                            <div class="flex justify-between items-center">
                                <span class="text-sm font-normal text-secondary-foreground">
                                    Customs Duties
                                </span>
                                <span class="text-sm font-medium text-mono">
                                    DZD {selectedPurchase.total_customs_duties}
                                </span>
                            </div>
                        {/if}
                    </div>
                    <div class="kt-card-footer flex justify-between items-center px-5">
                        <span class="text-sm font-normal text-secondary-foreground">
                            Total
                        </span>
                        <span class="text-base font-semibold text-mono">
                            DZD {selectedPurchase.total_cost}
                        </span>
                    </div>
                </div>
                
            {/if}
        </div>
    </div>
</CompanyLayout> 