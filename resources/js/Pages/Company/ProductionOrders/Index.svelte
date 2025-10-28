<script>
    import CompanyLayout from '../../Layouts/CompanyLayout.svelte';
    import Pagination from '../../Components/Pagination.svelte';
    import { onMount, onDestroy, tick } from 'svelte';
    import { page } from '@inertiajs/svelte'

    // Define breadcrumbs for this page
    const breadcrumbs = [
        {
            title: 'Production Orders',
            url: route('company.production-orders.index'),
            active: false
        },
        {
            title: 'Index',
            url: route('company.production-orders.index'),
            active: true
        }
    ];
    
    const pageTitle = 'My Production Orders';

    // Reactive variables
    let productionOrders = [];
    let loading = true;
    let fetchInterval = null;
    let pagination = {};
    let perPage = 10;
    let currentPage = 1;

    // Drawer state
    let selectedProductionOrder = null;
    let showProductionOrderDrawer = false;

    // Modal state
    let selectedProductionOrderToCancel = null;
    let showCancelModal = false;
    let cancelling = false;

    // Fetch production orders data
    async function fetchProductionOrders() {
        if(productionOrders.length == 0) loading = true;

        try {
            const params = new URLSearchParams({
                page: currentPage,
                perPage: perPage,
            });

            const response = await fetch(route('company.production-orders.index') + '?' + params.toString(), {
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                }
            });
            
            const data = await response.json();
            productionOrders = data.productionOrders;
            pagination = data.pagination;
            
            // Wait for DOM to update, then initialize menus
            await tick();
            if (window.KTMenu) {
                window.KTMenu.init();
            }
        } catch (error) {
            console.error('Error fetching production orders:', error);
        } finally {
            loading = false;
        }
    }

    // Handle pagination
    function goToPage(page) {
        if (page && page !== currentPage) {
            currentPage = page;
            loading = true;
            fetchProductionOrders();
        }
    }

    // Handle per page change
    function handlePerPageChange(newPerPage) {
        perPage = newPerPage;
        currentPage = 1;
        loading = true;
        fetchProductionOrders();
    }

    // Open production order drawer
    function openProductionOrderDrawer(productionOrder) {
        selectedProductionOrder = productionOrder;
        showProductionOrderDrawer = true;
        
        // Show drawer
        const toggleButton = document.querySelector('[data-kt-drawer-toggle="#production_order_drawer"]');
        if (toggleButton) {
            toggleButton.click();
        }
    }

    // Close production order drawer
    function closeProductionOrderDrawer() {
        showProductionOrderDrawer = false;
        selectedProductionOrder = null;
    }

    // Open cancel modal
    function openCancelModal(productionOrder) {
        selectedProductionOrderToCancel = productionOrder;
        showCancelModal = true;
        
        // Show modal
        const toggleButton = document.querySelector('[data-kt-modal-toggle="#cancel_production_modal"]');
        if (toggleButton) {
            toggleButton.click();
        }
    }

    // Close cancel modal
    function closeCancelModal() {
        showCancelModal = false;
        selectedProductionOrderToCancel = null;
        cancelling = false;
        
        // Simulate button click to use KT framework logic
        const dismissButton = document.querySelector('[data-kt-modal-dismiss="#cancel_production_modal"]');
        if (dismissButton) {
            dismissButton.click();
        }
    }

    // Cancel production order
    async function cancelProductionOrder() {
        if (!selectedProductionOrderToCancel) return;

        cancelling = true;
        try {
            const response = await fetch(route('company.production-orders.cancel', selectedProductionOrderToCancel.id), {
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
                showToast(data.message || 'Production order cancelled successfully!', 'success');
                
                // Close modal
                closeCancelModal();
                
                // Refresh production orders data
                fetchProductionOrders();
            } else {
                const errorData = await response.json();
                showToast(errorData.message || 'Error cancelling production order. Please try again.', 'error');
            }
        } catch (error) {
            console.error('Error cancelling production order:', error);
            showToast('Network error. Please check your connection and try again.', 'error');
        } finally {
            cancelling = false;
        }
    }

    // Get status badge class
    function getStatusBadgeClass(status) {
        switch (status) {
            case 'in_progress':
                return 'kt-badge-warning';
            case 'completed':
                return 'kt-badge-success';
            case 'cancelled':
                return 'kt-badge-destructive';
            default:
                return 'kt-badge-outline';
        }
    }

    onMount(() => {
        fetchProductionOrders();
        fetchInterval = setInterval(fetchProductionOrders, 60000);
    });

    onDestroy(() => {
        if (fetchInterval) {
            clearInterval(fetchInterval);
        }
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

<CompanyLayout {breadcrumbs} {pageTitle}>
    <!-- Container -->
    <div class="kt-container-fixed">
        <div class="grid gap-5 lg:gap-7.5">
            <!-- Production Orders Header -->
            <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4">
                <div class="flex flex-col gap-1">
                    <h1 class="text-2xl font-bold text-mono">My Production Orders</h1>
                    <p class="text-sm text-secondary-foreground">
                        Track your production orders and manufacturing progress
                    </p>
                </div>   
                
                <div class="flex items-center gap-3">
                    <a href="{route('company.machines.index')}" class="kt-btn kt-btn-primary">
                        <i class="ki-filled ki-setting-3 text-base"></i>
                        Manage Machines
                    </a>
                </div>  
            </div>

            <!-- Production Orders Grid -->
            <div class="kt-card">
                <div class="kt-card-content p-0">
                    {#if loading}
                        <!-- Loading skeleton -->
                        <div class="p-6">
                            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6 p-4">
                                {#each Array(10) as _, i}
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
                    {:else if productionOrders.length === 0}
                        <!-- Empty state -->
                        <div class="p-10">
                            <div class="flex flex-col items-center justify-center text-center">
                                <div class="mb-4">
                                    <i class="ki-filled ki-setting-3 text-4xl text-muted-foreground"></i>
                                </div>
                                <h3 class="text-lg font-semibold text-mono mb-2">No production orders found</h3>
                                <p class="text-sm text-secondary-foreground mb-4">
                                    No production orders available. Start production on your machines to see them here.
                                </p>
                                <a href="{route('company.machines.index')}" class="kt-btn kt-btn-primary">
                                    <i class="ki-filled ki-setting-3 text-base"></i>
                                    Manage Machines
                                </a>
                            </div>
                        </div>
                    {:else}
                        <!-- Production Orders Grid -->
                        <div class="p-6">
                            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6 p-4">
                                {#each productionOrders as productionOrder}
                                    <div class="kt-card kt-card-hover cursor-pointer" on:click={() => openProductionOrderDrawer(productionOrder)}>
                                        <div class="kt-card-header justify-start bg-muted/70 gap-9 h-auto py-5">
                                            <div class="flex flex-col gap-1.5">
                                                <span class="text-xs font-normal text-secondary-foreground">
                                                    Order ID
                                                </span>
                                                <span class="text-sm font-medium text-mono">
                                                    #{productionOrder.id}
                                                </span>
                                            </div>
                                            <div class="flex flex-col gap-1.5">
                                                <span class="text-xs font-normal text-secondary-foreground">
                                                    Status
                                                </span>
                                                <span class="kt-badge kt-badge-sm {getStatusBadgeClass(productionOrder.status)}">
                                                    {productionOrder.status}
                                                </span>
                                            </div>
                                            <div class="flex flex-col gap-1.5">
                                                <span class="text-xs font-normal text-secondary-foreground">
                                                    Started
                                                </span>
                                                <span class="text-sm font-medium text-mono">
                                                    {formatTimestamp(productionOrder.started_at)}
                                                </span>
                                            </div>
                                            {#if productionOrder.completed_at}
                                            <div class="flex flex-col gap-1.5">
                                                <span class="text-xs font-normal text-secondary-foreground">
                                                    Completed At
                                                </span>
                                                <span class="text-sm font-medium text-mono">
                                                    {formatTimestamp(productionOrder.completed_at)}
                                                </span>
                                            </div>
                                            {/if}
                                            {#if productionOrder.status === 'in_progress' && productionOrder.time_to_complete}
                                            <div class="flex flex-col gap-1.5">
                                                <span class="text-xs font-normal text-secondary-foreground">
                                                    Time needed
                                                </span>
                                                <span class="text-sm font-medium text-mono">
                                                    {productionOrder.time_to_complete} days
                                                </span>
                                            </div>
                                            {/if}
                                            {#if productionOrder.status === 'in_progress'}
                                            <div class="flex flex-col gap-1.5 ml-auto">
                                                <button 
                                                    class="kt-btn kt-btn-sm kt-btn-destructive"
                                                    on:click|stopPropagation={() => openCancelModal(productionOrder)}
                                                >
                                                    <i class="ki-filled ki-cross text-sm"></i>
                                                    Cancel
                                                </button>
                                            </div>
                                            {/if}
                                        </div>
                                        <div class="kt-card-content p-5 lg:p-7.5 space-y-5">
                                            <div class="kt-card">
                                                <div class="kt-card-content flex items-center flex-wrap justify-between gap-4.5 p-2 pe-5">
                                                    <div class="flex items-center gap-3.5">
                                                        <div class="kt-card flex items-center justify-center bg-accent/50 h-[70px] w-[90px] shadow-none">
                                                            {#if productionOrder.product.image_url}
                                                                <img 
                                                                    alt={productionOrder.product.name}
                                                                    class="cursor-pointer h-[70px] object-cover rounded-sm" 
                                                                    src={productionOrder.product.image_url}
                                                                />
                                                            {:else}
                                                                <i class="ki-filled ki-abstract-26 text-2xl text-muted-foreground"></i>
                                                            {/if}
                                                        </div>
                                                        <div class="flex flex-col gap-1">
                                                            <a class="hover:text-primary text-sm font-medium text-mono leading-5.5" href="#">
                                                                {productionOrder.product.name}
                                                            </a>
                                                            <div class="flex items-center gap-1.5">
                                                                <span class="text-xs font-normal text-secondary-foreground uppercase">
                                                                    Machine:
                                                                    <span class="text-xs font-medium text-foreground">
                                                                        {productionOrder.company_machine.machine.name}
                                                                    </span>
                                                                </span>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="flex flex-col gap-1.5">
                                                        <span class="text-xs font-normal text-secondary-foreground text-end">
                                                            {productionOrder.quantity} x
                                                        </span>
                                                    </div>
                                                </div>
                                            </div>

                                            <!-- Production Progress -->
                                            {#if productionOrder.status === 'in_progress'}
                                                <div class="kt-card bg-accent/50">
                                                    <div class="kt-card-header px-5">
                                                        <h3 class="kt-card-title">
                                                            Production Progress
                                                        </h3>
                                                    </div>
                                                    <div class="kt-card-content px-5 py-4">
                                                        <div class="flex items-center gap-3">
                                                            <div class="flex-1">
                                                                <div class="w-full bg-gray-200 rounded-full h-3">
                                                                    <div class="kt-progress kt-progress-primary">
                                                                        <div class="kt-progress-indicator" style="width: {productionOrder.producing_progress}%"></div>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                            <span class="text-sm font-medium text-mono">
                                                                {productionOrder.producing_progress.toFixed(1)}%
                                                            </span>
                                                        </div>
                                                    </div>
                                                </div>
                                            {/if}
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
    <button style="display:none" data-kt-drawer-toggle="#production_order_drawer"></button>

    <!-- Hidden button to trigger modal -->
    <button style="display:none" data-kt-modal-toggle="#cancel_production_modal"></button>

    <!-- Production Order Details Drawer -->
    <div class="hidden kt-drawer kt-drawer-end card flex-col max-w-[90%] w-[450px] top-5 bottom-5 end-5 rounded-xl border border-border" data-kt-drawer="true" data-kt-drawer-container="body" id="production_order_drawer">
        <div class="flex items-center justify-between gap-2.5 text-sm text-mono font-semibold px-5 py-2.5 border-b border-b-border">
            Production Order Details
            <button class="kt-btn kt-btn-sm kt-btn-icon kt-btn-dim shrink-0" data-kt-drawer-dismiss="true" on:click={closeProductionOrderDrawer}>
                <i class="ki-filled ki-cross"></i>
            </button>
        </div>
        
        <div class="kt-card-content flex flex-col space-y-3 p-5 kt-scrollable-y-auto">
            {#if selectedProductionOrder}
                <!-- Product Image -->
                <div class="kt-card relative items-center justify-center bg-accent/50 mb-6.5 h-[180px] shadow-none">
                    {#if selectedProductionOrder.product.image_url}
                        <img 
                            src={selectedProductionOrder.product.image_url} 
                            alt={selectedProductionOrder.product.name}
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
                    {selectedProductionOrder.product.name}
                </span>

                
                <!-- Production Order Details -->
                <div class="flex flex-col gap-2.5">
                    <div class="flex items-center gap-2.5">
                        <span class="text-xs font-normal text-foreground min-w-14 xl:min-w-24 shrink-0">
                            Order ID
                        </span>
                        <div>
                            <span class="text-xs font-medium text-foreground">
                                #{selectedProductionOrder.id}
                            </span>
                        </div>
                    </div>
                    <div class="flex items-center gap-2.5">
                        <span class="text-xs font-normal text-foreground min-w-14 xl:min-w-24 shrink-0">
                            Status
                        </span>
                        <div>
                            <span class="kt-badge kt-badge-sm {getStatusBadgeClass(selectedProductionOrder.status)}">
                                {selectedProductionOrder.status}
                            </span>
                        </div>
                    </div>
                    <div class="flex items-center gap-2.5">
                        <span class="text-xs font-normal text-foreground min-w-14 xl:min-w-24 shrink-0">
                            Machine
                        </span>
                        <div>
                            <span class="text-xs font-medium text-foreground">
                                {selectedProductionOrder.company_machine.machine.name}
                            </span>
                        </div>
                    </div>
                    <div class="flex items-center gap-2.5">
                        <span class="text-xs font-normal text-foreground min-w-14 xl:min-w-24 shrink-0">
                            Quantity
                        </span>
                        <div>
                            <span class="text-xs font-medium text-foreground">
                                {selectedProductionOrder.quantity}
                            </span>
                        </div>
                    </div>
                    <div class="flex items-center gap-2.5">
                        <span class="text-xs font-normal text-foreground min-w-14 xl:min-w-24 shrink-0">
                            Quality Factor
                        </span>
                        <div>
                            <span class="text-xs font-medium text-foreground">
                                {(selectedProductionOrder.quality_factor * 100).toFixed(2)}%
                            </span>
                        </div>
                    </div>
                    <div class="flex items-center gap-2.5">
                        <span class="text-xs font-normal text-foreground min-w-14 xl:min-w-24 shrink-0">
                            Employee Efficiency
                        </span>
                        <div>
                            <span class="text-xs font-medium text-foreground">
                                x{selectedProductionOrder.employee_efficiency_factor}
                            </span>
                        </div>
                    </div>
                    <div class="flex items-center gap-2.5">
                        <span class="text-xs font-normal text-foreground min-w-14 xl:min-w-24 shrink-0">
                            Carbon Footprint
                        </span>
                        <div>
                            <span class="text-xs font-medium text-foreground">
                                {selectedProductionOrder.carbon_footprint} kg CO2/unit
                            </span>
                        </div>
                    </div>
                    <div class="flex items-center gap-2.5">
                        <span class="text-xs font-normal text-foreground min-w-14 xl:min-w-24 shrink-0">
                            Started At
                        </span>
                        <div>
                            <span class="text-xs font-medium text-foreground">
                                {formatTimestamp(selectedProductionOrder.started_at)}
                            </span>
                        </div>
                    </div>
                    {#if selectedProductionOrder.status === 'in_progress'}
                        <div class="flex items-center gap-2.5">
                            <span class="text-xs font-normal text-foreground min-w-14 xl:min-w-24 shrink-0">
                                Time needed
                            </span>
                            <div>
                                <span class="text-xs font-medium text-foreground">
                                    {selectedProductionOrder.time_to_complete} days
                                </span>
                            </div>
                        </div>
                    {/if}
                    {#if selectedProductionOrder.completed_at}
                        <div class="flex items-center gap-2.5">
                            <span class="text-xs font-normal text-foreground min-w-14 xl:min-w-24 shrink-0">
                                Completed At
                            </span>
                            <div>
                                <span class="text-xs font-medium text-foreground">
                                    {formatTimestamp(selectedProductionOrder.completed_at)}
                                </span>
                            </div>
                        </div>
                    {/if}
                </div>

                <div class="border-t border-border"></div>

                <!-- Production Progress -->
                {#if selectedProductionOrder.is_producing}
                    <div class="kt-card bg-accent/50">
                        <div class="kt-card-header px-5">
                            <h3 class="kt-card-title">
                                Production Progress
                            </h3>
                        </div>
                        <div class="kt-card-content px-5 py-4">
                            <div class="flex items-center gap-3">
                                <div class="flex-1">
                                    <div class="w-full bg-gray-200 rounded-full h-3">
                                        <div class="kt-progress kt-progress-primary">
                                            <div class="kt-progress-indicator" style="width: {selectedProductionOrder.producing_progress}%"></div>
                                        </div>
                                    </div>
                                </div>
                                <span class="text-sm font-medium text-mono">
                                    {selectedProductionOrder.producing_progress.toFixed(1)}%
                                </span>
                            </div>
                        </div>
                    </div>
                {/if}

                <!-- Expected Output -->
                <div class="kt-card bg-accent/50">
                    <div class="kt-card-header px-5">
                        <h3 class="kt-card-title">
                            Expected Output
                        </h3>
                    </div>
                    <div class="kt-card-content px-5 py-4 space-y-2">
                        <div class="flex justify-between items-center">
                            <span class="text-sm font-normal text-secondary-foreground">
                                Total Quantity
                            </span>
                            <span class="text-sm font-medium text-mono">
                                {selectedProductionOrder.quantity} units
                            </span>
                        </div>
                        <div class="flex justify-between items-center">
                            <span class="text-sm font-normal text-secondary-foreground">
                                Quality Adjusted Output
                            </span>
                            <span class="text-sm font-medium text-mono">
                                {(selectedProductionOrder.quantity * selectedProductionOrder.quality_factor).toFixed(3)} units
                            </span>
                        </div>
                        <div class="flex justify-between items-center">
                            <span class="text-sm font-normal text-secondary-foreground">
                                Carbon Footprint
                            </span>
                            <span class="text-sm font-medium text-mono">
                                {selectedProductionOrder.carbon_footprint} kg CO2/unit
                            </span>
                        </div>
                    </div>
                </div>
                
            {/if}
        </div>
    </div>

    <!-- Cancel Production Order Confirmation Modal -->
    <div class="kt-modal" data-kt-modal="true" id="cancel_production_modal">
        <div class="kt-modal-content max-w-[600px] top-[5%]">
            <div class="kt-modal-header">
                <h3 class="kt-modal-title">Cancel Production Order</h3>
                <button
                    type="button"
                    class="kt-modal-close"
                    aria-label="Close modal"
                    data-kt-modal-dismiss="#cancel_production_modal"
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
                {#if selectedProductionOrderToCancel}
                    <div class="space-y-4">
                        <!-- Product and Machine Info -->
                        <div class="flex items-center gap-4">
                            <div class="flex-shrink-0">
                                {#if selectedProductionOrderToCancel.product.image_url}
                                    <img 
                                        src={selectedProductionOrderToCancel.product.image_url} 
                                        alt={selectedProductionOrderToCancel.product.name}
                                        class="w-16 h-16 rounded-lg object-cover"
                                    />
                                {:else}
                                    <div class="w-16 h-16 rounded-lg bg-accent/50 flex items-center justify-center">
                                        <i class="ki-filled ki-abstract-26 text-xl text-muted-foreground"></i>
                                    </div>
                                {/if}
                            </div>
                            <div class="flex-1">
                                <h4 class="font-semibold text-mono mb-1">{selectedProductionOrderToCancel.product.name}</h4>
                                <p class="text-sm text-muted-foreground mb-1">Machine: {selectedProductionOrderToCancel.company_machine.machine.name}</p>
                            </div>
                        </div>

                        <!-- Production Details -->
                        <div class="kt-card">
                            <div class="kt-card-body p-4">
                                <h5 class="font-medium text-mono mb-3">Production Details</h5>
                                <div class="space-y-2">
                                    <div class="flex justify-between">
                                        <span class="text-sm text-muted-foreground">Quantity:</span>
                                        <span class="font-medium">{selectedProductionOrderToCancel.quantity}</span>
                                    </div>
                                    <div class="flex justify-between">
                                        <span class="text-sm text-muted-foreground">Progress:</span>
                                        <span class="font-medium">{selectedProductionOrderToCancel.producing_progress.toFixed(1)}%</span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Warning Notice -->
                        <div class="kt-card bg-destructive/10 border-destructive/20">
                            <div class="kt-card-body p-4">
                                <div class="flex items-start gap-3">
                                    <i class="ki-filled ki-information text-destructive text-lg mt-0.5"></i>
                                    <div class="space-y-2">
                                        <h5 class="font-medium text-destructive">Warning</h5>
                                        <div class="text-sm text-destructive/80 space-y-1">
                                            <p>• All raw materials used in this production will be lost</p>
                                            <p>• All products produced so far will be lost</p>
                                            <p>• The machine will be freed up for other production orders</p>
                                            <p>• This action cannot be undone</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Confirmation Message -->
                        <div class="text-sm text-muted-foreground">
                            Are you sure you want to cancel this production order? This will permanently stop the production and lose all materials used so far.
                        </div>
                    </div>
                {/if}
            </div>
            <div class="kt-modal-footer">
                <div></div>
                <div class="flex gap-4">
                    <button
                        class="kt-btn kt-btn-secondary"
                        data-kt-modal-dismiss="#cancel_production_modal"
                        on:click={closeCancelModal}
                        disabled={cancelling}
                    >
                        Cancel
                    </button>
                    <button 
                        class="kt-btn kt-btn-destructive"
                        on:click={cancelProductionOrder}
                        disabled={cancelling}
                    >
                        {#if cancelling}
                            <i class="ki-filled ki-loading animate-spin text-sm"></i>
                            Cancelling...
                        {:else}
                            <i class="ki-filled ki-cross text-sm"></i>
                            Cancel Production
                        {/if}
                    </button>
                </div>
            </div>
        </div>
    </div>
</CompanyLayout>
