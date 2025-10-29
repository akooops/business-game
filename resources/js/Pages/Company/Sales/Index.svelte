<script>
    import CompanyLayout from '../../Layouts/CompanyLayout.svelte';
    import Pagination from '../../Components/Pagination.svelte';
    import { onMount, onDestroy, tick } from 'svelte';
    import { page } from '@inertiajs/svelte'

    // Define breadcrumbs for this page
    const breadcrumbs = [
        {
            title: 'Sales',
            url: route('company.sales.index'),
            active: false
        },
        {
            title: 'Index',
            url: route('company.sales.index'),
            active: true
        }
    ];
    
    const pageTitle = 'My Sales';

    // Reactive variables
    let sales = [];
    let pagination = {};
    let loading = true;
    let fetchInterval = null;
    let perPage = 10;
    let currentPage = 1;
    let searchTerm = '';
    let searchTimeout = null;

    // Additional filtering variables
    let filterType = '';
    let filterStatus = '';
    let sortBy = '';

    // Modal state
    let selectedSale = null;
    let showSaleModal = false;
    let deliveringSale = false;

    // Drawer state for delivered sales
    let selectedDeliveredSale = null;
    let showSaleDrawer = false;

    // Fetch sales data
    async function fetchSales() {
        if(sales.length == 0) loading = true;
        
        try {
            const params = new URLSearchParams({
                page: currentPage,
                perPage: perPage,
            });
            
            if (searchTerm) {
                params.append('search', searchTerm);
            }
            if (filterType) {
                params.append('type', filterType);
            }
            if (filterStatus) {
                params.append('status', filterStatus);
            }
            if (sortBy) {
                params.append('sort', sortBy);
            }

            const response = await fetch(route('company.sales.index') + '?' + params.toString(), {
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                }
            });
            
            const data = await response.json();
            sales = data.sales;
            pagination = data.pagination;
            
            // Wait for DOM to update, then initialize menus
            await tick();
            if (window.KTMenu) {
                window.KTMenu.init();
            }
        } catch (error) {
            console.error('Error fetching sales:', error);
        } finally {
            loading = false;
        }
    }

    // Handle pagination
    function goToPage(page) {
        if (page && page !== currentPage) {
            currentPage = page;
            loading = true;
            fetchSales();
        }
    }
    
    // Handle search
    function handleSearch(event) {
        searchTerm = event.target.value;
        currentPage = 1;
        
        // Debounce search
        clearTimeout(searchTimeout);
        searchTimeout = setTimeout(() => {
            fetchSales();
        }, 300);
    }

    // Handle filter changes
    function handleFilterChange() {
        currentPage = 1;
        fetchSales();
    }

    // Reactive updates for filters
    $: if (filterType !== undefined) {
        handleFilterChange();
    }

    $: if (filterStatus !== undefined) {
        handleFilterChange();
    }

    $: if (sortBy !== undefined) {
        handleFilterChange();
    }

    // Handle per page change
    function handlePerPageChange(newPerPage) {
        perPage = newPerPage;
        currentPage = 1;
        loading = true;
        fetchSales();
    }

    // Open sale modal
    function openSaleModal(sale) {
        selectedSale = sale;
        showSaleModal = true;
        
        // Show modal
        const toggleButton = document.querySelector('[data-kt-modal-toggle="#sale_modal"]');
        if (toggleButton) {
            toggleButton.click();
        }
    }

    // Close sale modal
    function closeSaleModal() {
        showSaleModal = false;
        selectedSale = null;
        deliveringSale = false;
        
        // Simulate button click to use KT framework logic
        const dismissButton = document.querySelector('[data-kt-modal-dismiss="#sale_modal"]');
        if (dismissButton) {
            dismissButton.click();
        }
    }

    // Open sale drawer for delivered sales
    function openSaleDrawer(sale) {
        selectedDeliveredSale = sale;
        showSaleDrawer = true;
        
        // Show drawer
        const toggleButton = document.querySelector('[data-kt-drawer-toggle="#sale_drawer"]');
        if (toggleButton) {
            toggleButton.click();
        }
    }

    // Close sale drawer
    function closeSaleDrawer() {
        showSaleDrawer = false;
        selectedDeliveredSale = null;
    }

    // Deliver sale
    async function confirmSale() {
        if (!selectedSale) return;

        deliveringSale = true;
        try {
            const response = await fetch(route('company.sales.store', selectedSale.id), {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content'),
                    'X-Requested-With': 'XMLHttpRequest'
                }
            });

            if (response.ok) {
                const data = await response.json();
                
                // Show success toast
                showToast(data.message || 'Sale delivered successfully!', 'success');
                
                // Close modal
                closeSaleModal();
                
                // Refresh sales data
                fetchSales();
            } else {
                const errorData = await response.json();
                showToast(errorData.message || 'Error confirming sale. Please try again.', 'error');
            }
        } catch (error) {
            console.error('Error confirming sale:', error);
            showToast('Network error. Please check your connection and try again.', 'error');
        } finally {
            deliveringSale = false;
        }
    }

    // Get status badge class
    function getStatusBadgeClass(status) {
        switch (status) {
            case 'initiated':
                return 'kt-badge-warning';
            case 'delivered':
                return 'kt-badge-success';
            case 'cancelled':
                return 'kt-badge-destructive';
            default:
                return 'kt-badge-outline';
        }
    }

    onMount(() => {
        fetchSales();
        fetchInterval = setInterval(fetchSales, 60000);
    });
    
    onDestroy(() => {
        clearInterval(fetchInterval);
    });

    // Flash message handling
    export let success;
    export let error;

    $: if (success) {
        showToast(success, 'success');
    }

    $: if (error) {
        showToast(error, 'error');
    }
</script>

<svelte:head>
    <title>Business game - {pageTitle}</title>
</svelte:head>

<CompanyLayout {breadcrumbs} {pageTitle}>
    <!-- Container -->
    <div class="kt-container-fixed">
        <div class="grid gap-5 lg:gap-7.5">
            <!-- Sales Header -->
            <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4">
                <div class="flex flex-col gap-1">
                    <h1 class="text-2xl font-bold text-mono">My Sales</h1>
                    <p class="text-sm text-secondary-foreground">
                        Track your sales orders and deliveries
                    </p>
                </div>   
            </div>

            <!-- Sales Grid -->
            <div class="kt-card">
                <div class="kt-card-header">
                    <div class="kt-card-toolbar">
                        <div class="flex flex-col gap-2 w-full">
                            <!-- Filter Buttons Row -->
                            <div class="flex items-center gap-2 justify-between flex-wrap">
                                <!-- Product Type Filters -->
                                <div class="flex gap-2 flex-wrap">
                                    <button 
                                        class="kt-btn kt-btn-sm {filterType === '' ? 'kt-btn-primary' : 'kt-btn-light'}"
                                        on:click={() => filterType = ''}
                                    >
                                        All
                                    </button>
                                    <button 
                                        class="kt-btn kt-btn-sm {filterType === 'raw_material' ? 'kt-btn-primary' : 'kt-btn-light'}"
                                        on:click={() => filterType = 'raw_material'}
                                    >
                                        Raw
                                    </button>
                                    <button 
                                        class="kt-btn kt-btn-sm {filterType === 'component' ? 'kt-btn-primary' : 'kt-btn-light'}"
                                        on:click={() => filterType = 'component'}
                                    >
                                        Component
                                    </button>
                                    <button 
                                        class="kt-btn kt-btn-sm {filterType === 'finished_product' ? 'kt-btn-primary' : 'kt-btn-light'}"
                                        on:click={() => filterType = 'finished_product'}
                                    >
                                        Finished
                                    </button>
                                </div>
                                
                                <!-- Status Filters -->
                                <div class="flex gap-2 flex-wrap">
                                    <button 
                                        class="kt-btn kt-btn-sm {filterStatus === '' ? 'kt-btn-success' : 'kt-btn-light'}"
                                        on:click={() => filterStatus = ''}
                                    >
                                        All Status
                                    </button>
                                    <button 
                                        class="kt-btn kt-btn-sm {filterStatus === 'initiated' ? 'kt-btn-success' : 'kt-btn-light'}"
                                        on:click={() => filterStatus = 'initiated'}
                                    >
                                        Initiated
                                    </button>
                                    <button 
                                        class="kt-btn kt-btn-sm {filterStatus === 'confirmed' ? 'kt-btn-success' : 'kt-btn-light'}"
                                        on:click={() => filterStatus = 'confirmed'}
                                    >
                                        Confirmed
                                    </button>
                                    <button 
                                        class="kt-btn kt-btn-sm {filterStatus === 'delivered' ? 'kt-btn-success' : 'kt-btn-light'}"
                                        on:click={() => filterStatus = 'delivered'}
                                    >
                                        Delivered
                                    </button>
                                    <button 
                                        class="kt-btn kt-btn-sm {filterStatus === 'cancelled' ? 'kt-btn-success' : 'kt-btn-light'}"
                                        on:click={() => filterStatus = 'cancelled'}
                                    >
                                        Cancelled
                                    </button>
                                </div>
                            </div>
                            
                            <!-- Sort and Search Row -->
                            <div class="flex items-center gap-2 justify-end">
                                <!-- Sort Dropdown - Compact -->
                                <select 
                                    class="kt-input h-8 text-xs px-2" 
                                    style="width: 70px;"
                                    bind:value={sortBy}
                                >
                                    <option value="">Sort</option>
                                    <option value="date_desc">New</option>
                                    <option value="date_asc">Old</option>
                                    <option value="quantity_desc">Qty ↓</option>
                                    <option value="quantity_asc">Qty ↑</option>
                                    <option value="price_desc">Price ↓</option>
                                    <option value="price_asc">Price ↑</option>
                                </select>
                                
                                <!-- Search Bar - Compact -->
                                <div class="kt-input" style="width: 180px;">
                                    <i class="ki-filled ki-magnifier text-xs"></i>
                                    <input 
                                        type="text" 
                                        class="kt-input h-8 text-xs" 
                                        placeholder="Search..." 
                                        bind:value={searchTerm}
                                        on:input={handleSearch}
                                    />
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="kt-card-content p-0">
                    {#if loading}
                        <!-- Loading skeleton -->
                        <div class="p-6">
                            <div class="grid grid-cols-1 grid-cols-md-2 gap-6 p-4grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6 p-4">
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
                    {:else if sales.length === 0}
                        <!-- Empty state -->
                        <div class="p-10">
                            <div class="flex flex-col items-center justify-center text-center">
                                <div class="mb-4">
                                    <i class="ki-filled ki-handcart text-4xl text-muted-foreground"></i>
                                </div>
                                <h3 class="text-lg font-semibold text-mono mb-2">No sales found</h3>
                                <p class="text-sm text-secondary-foreground mb-4">
                                    No sales available. Sales will appear here when generated.
                                </p>
                            </div>
                        </div>
                    {:else}
                        <!-- Sales Grid -->
                        <div class="p-6">
                            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6 p-4">
                                {#each sales as sale}
                                    <div class="kt-card kt-card-hover cursor-pointer" on:click={() => {
                                        if (sale.status === 'initiated') {
                                            openSaleModal(sale);
                                        } else {
                                            openSaleDrawer(sale);
                                        }
                                    }}>
                                        <div class="kt-card-header justify-start bg-muted/70 gap-9 h-auto py-5">
                                            <div class="flex flex-col gap-1.5">
                                                <span class="text-xs font-normal text-secondary-foreground">
                                                    Sale ID
                                                </span>
                                                <span class="text-sm font-medium text-mono">
                                                    #{sale.id}
                                                </span>
                                            </div>
                                            <div class="flex flex-col gap-1.5">
                                                <span class="text-xs font-normal text-secondary-foreground">
                                                    Status
                                                </span>
                                                <span class="kt-badge kt-badge-sm {getStatusBadgeClass(sale.status)}">
                                                    {sale.status}
                                                </span>
                                            </div>
                                            <div class="flex flex-col gap-1.5">
                                                <span class="text-xs font-normal text-secondary-foreground">
                                                    Initiated
                                                </span>
                                                <span class="text-sm font-medium text-mono">
                                                    {formatTimestamp(sale.initiated_at)}
                                                </span>
                                            </div>
                                            {#if sale.delivered_at}
                                            <div class="flex flex-col gap-1.5">
                                                <span class="text-xs font-normal text-secondary-foreground">
                                                    Delivered
                                                </span>
                                                <span class="text-sm font-medium text-mono">
                                                    {formatTimestamp(sale.delivered_at)}
                                                </span>
                                            </div>
                                            {/if}
                                            {#if sale.status === 'initiated'}
                                            <div class="flex flex-col gap-1.5 ml-auto">
                                                <button 
                                                    class="kt-btn kt-btn-sm kt-btn-primary"
                                                    on:click|stopPropagation={() => openSaleModal(sale)}
                                                >
                                                    <i class="ki-filled ki-check text-sm"></i>
                                                    Deliver
                                                </button>
                                            </div>
                                            {/if}
                                        </div>
                                        <div class="kt-card-content p-5 lg:p-7.5 space-y-5">
                                            <div class="kt-card">
                                                <div class="kt-card-content flex items-center flex-wrap justify-between gap-4.5 p-2 pe-5">
                                                    <div class="flex items-center gap-3.5">
                                                        <div class="kt-card flex items-center justify-center bg-accent/50 h-[70px] w-[90px] shadow-none">
                                                            {#if sale.product.image_url}
                                                                <img 
                                                                    alt={sale.product.name}
                                                                    class="cursor-pointer h-[70px] object-cover rounded-sm" 
                                                                    src={sale.product.image_url}
                                                                />
                                                            {:else}
                                                                <i class="ki-filled ki-abstract-26 text-2xl text-muted-foreground"></i>
                                                            {/if}
                                                        </div>
                                                        <div class="flex flex-col gap-1">
                                                            <a class="hover:text-primary text-sm font-medium text-mono leading-5.5" href="#">
                                                                {sale.product.name}
                                                            </a>
                                                            <div class="flex items-center gap-1.5">
                                                                <span class="text-xs font-normal text-secondary-foreground uppercase">
                                                                    Wilaya:
                                                                    <span class="text-xs font-medium text-foreground">
                                                                        {sale.wilaya.name}
                                                                    </span>
                                                                </span>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="flex flex-col gap-1.5">
                                                        <span class="text-xs font-normal text-secondary-foreground text-end">
                                                            {sale.quantity} x
                                                        </span>
                                                        <div class="flex items-center flex-wrap gap-1.5">
                                                            <span class="text-sm font-semibold text-mono">
                                                                DZD {sale.sale_price}
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

    <!-- Hidden button to trigger modal -->
    <button style="display:none" data-kt-modal-toggle="#sale_modal"></button>

    <!-- Hidden button to trigger drawer -->
    <button style="display:none" data-kt-drawer-toggle="#sale_drawer"></button>

    <!-- Sale Deliveration Modal -->
    <div class="kt-modal" data-kt-modal="true" id="sale_modal">
        <div class="kt-modal-content max-w-[600px] top-[5%]">
            <div class="kt-modal-header">
                <h3 class="kt-modal-title">Deliver Sale</h3>
                <button
                    type="button"
                    class="kt-modal-close"
                    aria-label="Close modal"
                    data-kt-modal-dismiss="#sale_modal"
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
                {#if selectedSale}
                    <div class="space-y-4">
                        <!-- Product and Wilaya Info -->
                        <div class="flex items-center gap-4">
                            <div class="flex-shrink-0">
                                {#if selectedSale.product.image_url}
                                    <img 
                                        src={selectedSale.product.image_url} 
                                        alt={selectedSale.product.name}
                                        class="w-16 h-16 rounded-lg object-cover"
                                    />
                                {:else}
                                    <div class="w-16 h-16 rounded-lg bg-accent/50 flex items-center justify-center">
                                        <i class="ki-filled ki-abstract-26 text-xl text-muted-foreground"></i>
                                    </div>
                                {/if}
                            </div>
                            <div class="flex-1">
                                <h4 class="font-semibold text-mono mb-1">{selectedSale.product.name}</h4>
                                <p class="text-sm text-muted-foreground mb-1">Wilaya: {selectedSale.wilaya.name}</p>
                            </div>
                        </div>

                        <!-- Sale Details -->
                        <div class="kt-card">
                            <div class="kt-card-body p-4">
                                <h5 class="font-medium text-mono mb-3">Sale Details</h5>
                                <div class="space-y-2">
                                    <div class="flex justify-between">
                                        <span class="text-sm text-muted-foreground">Quantity:</span>
                                        <span class="font-medium">{selectedSale.quantity}</span>
                                    </div>
                                    <div class="flex justify-between">
                                        <span class="text-sm text-muted-foreground">Sale Price:</span>
                                        <span class="font-medium">DZD {selectedSale.sale_price}</span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Price Breakdown -->
                        <div class="kt-card bg-accent/50">
                            <div class="kt-card-header px-5">
                                <h3 class="kt-card-title">
                                    Price Breakdown
                                </h3>
                            </div>
                            <div class="kt-card-content px-5 py-4 space-y-2">
                                <div class="flex justify-between items-center">
                                    <span class="text-sm font-normal text-secondary-foreground">
                                        Subtotal
                                    </span>
                                    <span class="text-sm font-medium text-mono">
                                        DZD {(selectedSale.sale_price * selectedSale.quantity).toFixed(3)}
                                    </span>
                                </div>
                            </div>
                        </div>

                        <!-- Important Notice -->
                        <div class="kt-card bg-warning/10 border-warning/20">
                            <div class="kt-card-body p-4">
                                <div class="flex items-start gap-3">
                                    <i class="ki-filled ki-information text-warning text-lg mt-0.5"></i>
                                    <div class="space-y-2">
                                        <h5 class="font-medium text-warning">Important Notice</h5>
                                        <div class="text-sm text-warning/80 space-y-1">
                                            <p>• You will receive <strong>DZD {(selectedSale.sale_price * selectedSale.quantity).toFixed(3)}</strong> only after delivery is completed</p>
                                            <p>• Make sure you have sufficient inventory before confirming</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Deliveration Message -->
                        <div class="text-sm text-muted-foreground">
                            Are you sure you want to deliver this sale?.
                        </div>
                    </div>
                {/if}
            </div>
            <div class="kt-modal-footer">
                <div></div>
                <div class="flex gap-4">
                    <button
                        class="kt-btn kt-btn-secondary"
                        data-kt-modal-dismiss="#sale_modal"
                        on:click={closeSaleModal}
                        disabled={deliveringSale}
                    >
                        Cancel
                    </button>
                    <button 
                        class="kt-btn kt-btn-primary"
                        on:click={confirmSale}
                        disabled={deliveringSale}
                    >
                        {#if deliveringSale}
                            <i class="ki-filled ki-loading animate-spin text-sm"></i>
                            Delivering...
                        {:else}
                            <i class="ki-filled ki-check text-sm"></i>
                            Deliver Sale
                        {/if}
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Sale Details Drawer -->
    <div class="hidden kt-drawer kt-drawer-end card flex-col max-w-[90%] w-[450px] top-5 bottom-5 end-5 rounded-xl border border-border" data-kt-drawer="true" data-kt-drawer-container="body" id="sale_drawer">
        <div class="flex items-center justify-between gap-2.5 text-sm text-mono font-semibold px-5 py-2.5 border-b border-b-border">
            Sale Details
            <button class="kt-btn kt-btn-sm kt-btn-icon kt-btn-dim shrink-0" data-kt-drawer-dismiss="true" on:click={closeSaleDrawer}>
                <i class="ki-filled ki-cross"></i>
            </button>
        </div>
        
        <div class="kt-card-content flex flex-col space-y-3 p-5 kt-scrollable-y-auto">
            {#if selectedDeliveredSale}
                <!-- Sale Image -->
                <div class="kt-card relative items-center justify-center bg-accent/50 mb-6.5 h-[180px] shadow-none">
                    {#if selectedDeliveredSale.product.image_url}
                        <img 
                            src={selectedDeliveredSale.product.image_url} 
                            alt={selectedDeliveredSale.product.name}
                            class="h-[180px] w-full object-cover rounded-sm"
                        />
                    {:else}
                        <div class="flex items-center justify-center h-full">
                            <i class="ki-filled ki-abstract-26 text-4xl text-muted-foreground"></i>
                        </div>
                    {/if}
                </div>

                <!-- Sale Name -->
                <span class="text-base font-medium text-mono">
                    {selectedDeliveredSale.product.name}
                </span>

                <!-- Sale Details -->
                <div class="flex flex-col gap-2.5">
                    <div class="flex items-center gap-2.5">
                        <span class="text-xs font-normal text-foreground min-w-14 xl:min-w-24 shrink-0">
                            Sale ID
                        </span>
                        <div>
                            <span class="text-xs font-medium text-foreground">
                                #{selectedDeliveredSale.id}
                            </span>
                        </div>
                    </div>
                    <div class="flex items-center gap-2.5">
                        <span class="text-xs font-normal text-foreground min-w-14 xl:min-w-24 shrink-0">
                            Status
                        </span>
                        <div>
                            <span class="kt-badge kt-badge-sm {getStatusBadgeClass(selectedDeliveredSale.status)}">
                                {selectedDeliveredSale.status}
                            </span>
                        </div>
                    </div>
                    <div class="flex items-center gap-2.5">
                        <span class="text-xs font-normal text-foreground min-w-14 xl:min-w-24 shrink-0">
                            Wilaya
                        </span>
                        <div>
                            <span class="text-xs font-medium text-foreground">
                                {selectedDeliveredSale.wilaya.name}
                            </span>
                        </div>
                    </div>
                    <div class="flex items-center gap-2.5">
                        <span class="text-xs font-normal text-foreground min-w-14 xl:min-w-24 shrink-0">
                            Quantity
                        </span>
                        <div>
                            <span class="text-xs font-medium text-foreground">
                                {selectedDeliveredSale.quantity}
                            </span>
                        </div>
                    </div>
                    <div class="flex items-center gap-2.5">
                        <span class="text-xs font-normal text-foreground min-w-14 xl:min-w-24 shrink-0">
                            Sale Price
                        </span>
                        <div>
                            <span class="text-xs font-medium text-foreground">
                                DZD {selectedDeliveredSale.sale_price}
                            </span>
                        </div>
                    </div>
                    <div class="flex items-center gap-2.5">
                        <span class="text-xs font-normal text-foreground min-w-14 xl:min-w-24 shrink-0">
                            Initiated At
                        </span>
                        <div>
                            <span class="text-xs font-medium text-foreground">
                                {formatTimestamp(selectedDeliveredSale.initiated_at)}
                            </span>
                        </div>
                    </div>
                    {#if selectedDeliveredSale.delivered_at}
                        <div class="flex items-center gap-2.5">
                            <span class="text-xs font-normal text-foreground min-w-14 xl:min-w-24 shrink-0">
                                Delivered At
                            </span>
                            <div>
                                <span class="text-xs font-medium text-foreground">
                                    {formatTimestamp(selectedDeliveredSale.delivered_at)}
                                </span>
                            </div>
                        </div>
                    {/if}
                </div>

                <div class="border-t border-border"></div>

                <!-- Sale Summary -->
                <div class="kt-card bg-accent/50">
                    <div class="kt-card-header px-5">
                        <h3 class="kt-card-title">
                            Sale Summary
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
                                DZD {(selectedDeliveredSale.sale_price * selectedDeliveredSale.quantity).toFixed(3)}
                            </span>
                        </div>
                    </div>
                </div>
                
            {/if}
        </div>
    </div>
</CompanyLayout> 