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
    let search = '';
    let perPage = 10;
    let currentPage = 1;
    let searchTimeout;
    let showFilters = false;

    // Filter variables
    let wilayaIdFilter = '';
    let productIdFilter = '';
    let statusFilter = '';
    let minInitiatedAtFilter = '';
    let maxInitiatedAtFilter = '';
    let minConfirmedAtFilter = '';
    let maxConfirmedAtFilter = '';
    let minDeliveredAtFilter = '';
    let maxDeliveredAtFilter = '';

    // Select2 component references
    let wilayaSelectComponent;
    let productSelectComponent;

    // Flatpickr component references
    let minInitiatedAtFlatpickr;
    let maxInitiatedAtFlatpickr;
    let minConfirmedAtFlatpickr;
    let maxConfirmedAtFlatpickr;
    let minDeliveredAtFlatpickr;
    let maxDeliveredAtFlatpickr;

    // Modal state
    let selectedSale = null;
    let showSaleModal = false;
    let confirmingSale = false;

    // Drawer state for confirmed sales
    let selectedConfirmedSale = null;
    let showSaleDrawer = false;

    // Fetch sales data
    async function fetchSales() {
        loading = true;
        try {
            const params = new URLSearchParams({
                page: currentPage,
                perPage: perPage,
                search: search
            });

            // Add filters to params
            if (wilayaIdFilter) params.append('wilaya_id', wilayaIdFilter);
            if (productIdFilter) params.append('product_id', productIdFilter);
            if (statusFilter) params.append('status', statusFilter);
            if (minInitiatedAtFilter) params.append('min_initiated_at', minInitiatedAtFilter);
            if (maxInitiatedAtFilter) params.append('max_initiated_at', maxInitiatedAtFilter);
            if (minConfirmedAtFilter) params.append('min_confirmed_at', minConfirmedAtFilter);
            if (maxConfirmedAtFilter) params.append('max_confirmed_at', maxConfirmedAtFilter);
            if (minDeliveredAtFilter) params.append('min_delivered_at', minDeliveredAtFilter);
            if (maxDeliveredAtFilter) params.append('max_delivered_at', maxDeliveredAtFilter);
            
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

    // Handle search with debouncing
    function handleSearch() {
        // Clear existing timeout
        if (searchTimeout) {
            clearTimeout(searchTimeout);
        }
        
        // Set new timeout for 500ms
        searchTimeout = setTimeout(() => {
            currentPage = 1;
            fetchSales();
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
        fetchSales();
    }

    // Handle pagination
    function goToPage(page) {
        if (page && page !== currentPage) {
            currentPage = page;
            fetchSales();
        }
    }

    // Handle per page change
    function handlePerPageChange(newPerPage) {
        perPage = newPerPage;
        currentPage = 1;
        fetchSales();
    }

    // Clear all filters
    function clearAllFilters() {
        wilayaIdFilter = '';
        productIdFilter = '';
        statusFilter = '';
        minInitiatedAtFilter = '';
        maxInitiatedAtFilter = '';
        minConfirmedAtFilter = '';
        maxConfirmedAtFilter = '';
        minDeliveredAtFilter = '';
        maxDeliveredAtFilter = '';
        
        if (wilayaSelectComponent) {
            wilayaSelectComponent.clear();
        }
        if (productSelectComponent) {
            productSelectComponent.clear();
        }
        
        // Clear Flatpickr components
        if (minInitiatedAtFlatpickr) minInitiatedAtFlatpickr.clear();
        if (maxInitiatedAtFlatpickr) maxInitiatedAtFlatpickr.clear();
        if (minConfirmedAtFlatpickr) minConfirmedAtFlatpickr.clear();
        if (maxConfirmedAtFlatpickr) maxConfirmedAtFlatpickr.clear();
        if (minDeliveredAtFlatpickr) minDeliveredAtFlatpickr.clear();
        if (maxDeliveredAtFlatpickr) maxDeliveredAtFlatpickr.clear();
        
        currentPage = 1;
        fetchSales();
    }

    // Toggle filters visibility
    function toggleFilters() {
        showFilters = !showFilters;
    }

    // Handle wilaya selection
    function handleWilayaSelect(event) {
        wilayaIdFilter = event.detail.value;
        handleFilterChange();
    }

    // Handle product selection
    function handleProductSelect(event) {
        productIdFilter = event.detail.value;
        handleFilterChange();
    }

    // Handle date changes
    function handleMinInitiatedAtChange(event) {
        minInitiatedAtFilter = event.detail.value;
        handleFilterChange();
    }

    function handleMaxInitiatedAtChange(event) {
        maxInitiatedAtFilter = event.detail.value;
        handleFilterChange();
    }

    function handleMinConfirmedAtChange(event) {
        minConfirmedAtFilter = event.detail.value;
        handleFilterChange();
    }

    function handleMaxConfirmedAtChange(event) {
        maxConfirmedAtFilter = event.detail.value;
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
        confirmingSale = false;
        
        // Simulate button click to use KT framework logic
        const dismissButton = document.querySelector('[data-kt-modal-dismiss="#sale_modal"]');
        if (dismissButton) {
            dismissButton.click();
        }
    }

    // Open sale drawer for confirmed sales
    function openSaleDrawer(sale) {
        selectedConfirmedSale = sale;
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
        selectedConfirmedSale = null;
    }

    // Confirm sale
    async function confirmSale() {
        if (!selectedSale) return;

        confirmingSale = true;
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
                showToast(data.message || 'Sale confirmed successfully!', 'success');
                
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
            confirmingSale = false;
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

    // Get status badge class
    function getStatusBadgeClass(status) {
        switch (status) {
            case 'initiated':
                return 'kt-badge-warning';
            case 'confirmed':
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
            case 'initiated':
                return 'Initiated';
            case 'confirmed':
                return 'Confirmed';
            case 'delivered':
                return 'Delivered';
            case 'cancelled':
                return 'Cancelled';
            default:
                return status;
        }
    }

    onMount(() => {
        fetchSales();
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
    <div class="kt-container-fluid">
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
                        <div class="kt-input max-w-64 w-64">
                            <i class="ki-filled ki-magnifier"></i>
                            <input 
                                type="text" 
                                class="kt-input" 
                                placeholder="Search sales..." 
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
                            {#if wilayaIdFilter || productIdFilter || statusFilter || minInitiatedAtFilter || maxInitiatedAtFilter || minConfirmedAtFilter || maxConfirmedAtFilter || minDeliveredAtFilter || maxDeliveredAtFilter}
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
                            <!-- Sale Properties -->
                            <div class="space-y-2">
                                <h4 class="text-sm font-medium text-gray-700">Sale Properties</h4>
                                <!-- Status -->
                                <select 
                                    class="kt-select w-full" 
                                    bind:value={statusFilter}
                                    on:change={handleFilterChange}
                                >
                                    <option value="">All Statuses</option>
                                    <option value="initiated">Initiated</option>
                                    <option value="confirmed">Confirmed</option>
                                    <option value="delivered">Delivered</option>
                                    <option value="cancelled">Cancelled</option>
                                </select>
                            </div>

                            <!-- Wilaya -->
                            <div class="space-y-2">
                                <h4 class="text-sm font-medium text-gray-700">Wilaya</h4>
                                <Select2
                                    bind:this={wilayaSelectComponent}
                                    id="wilaya-filter"
                                    placeholder="All Wilayas"
                                    allowClear={true}
                                    on:select={handleWilayaSelect}
                                    on:clear={() => {
                                        wilayaIdFilter = '';
                                        handleFilterChange();
                                    }}
                                    ajax={{
                                        url: route('company.wilayas.index'),
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
                                                    name: wilaya.name,
                                                }))
                                            };
                                        },
                                        cache: true
                                    }}
                                    templateResult={function(data) {
                                        if (data.loading) return data.text;
                                        
                                        const $elem = globalThis.$('<div class="flex items-center gap-2">' +
                                            '<i class="ki-filled ki-location text-sm text-muted-foreground"></i>' +
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

                            <!-- Initiated Date Range -->
                            <div class="space-y-2">
                                <h4 class="text-sm font-medium text-gray-700">Initiated Date Range</h4>
                                <div class="flex gap-2">
                                    <Flatpickr
                                        bind:this={minInitiatedAtFlatpickr}
                                        id="min-initiated-at"
                                        placeholder="Min Date"
                                        config={{
                                            enableTime: true,
                                            dateFormat: 'Y-m-d H:i',
                                            time_24hr: true
                                        }}
                                        on:change={handleMinInitiatedAtChange}
                                    />
                                    <Flatpickr
                                        bind:this={maxInitiatedAtFlatpickr}
                                        id="max-initiated-at"
                                        placeholder="Max Date"
                                        config={{
                                            enableTime: true,
                                            dateFormat: 'Y-m-d H:i',
                                            time_24hr: true
                                        }}
                                        on:change={handleMaxInitiatedAtChange}
                                    />
                                </div>
                            </div>

                            <!-- Confirmed Date Range -->
                            <div class="space-y-2">
                                <h4 class="text-sm font-medium text-gray-700">Confirmed Date Range</h4>
                                <div class="flex gap-2">
                                    <Flatpickr
                                        bind:this={minConfirmedAtFlatpickr}
                                        id="min-confirmed-at"
                                        placeholder="Min Date"
                                        config={{
                                            enableTime: true,
                                            dateFormat: 'Y-m-d H:i',
                                            time_24hr: true
                                        }}
                                        on:change={handleMinConfirmedAtChange}
                                    />
                                    <Flatpickr
                                        bind:this={maxConfirmedAtFlatpickr}
                                        id="max-confirmed-at"
                                        placeholder="Max Date"
                                        config={{
                                            enableTime: true,
                                            dateFormat: 'Y-m-d H:i',
                                            time_24hr: true
                                        }}
                                        on:change={handleMaxConfirmedAtChange}
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
                    {:else if sales.length === 0}
                        <!-- Empty state -->
                        <div class="p-10">
                            <div class="flex flex-col items-center justify-center text-center">
                                <div class="mb-4">
                                    <i class="ki-filled ki-handcart text-4xl text-muted-foreground"></i>
                                </div>
                                <h3 class="text-lg font-semibold text-mono mb-2">No sales found</h3>
                                <p class="text-sm text-secondary-foreground mb-4">
                                    {search ? 'No sales match your search criteria.' : 'No sales available. Sales will appear here when generated.'}
                                </p>
                            </div>
                        </div>
                    {:else}
                        <!-- Sales Grid -->
                        <div class="p-6">
                            <div class="grid grid-cols-1 gap-6 p-4">
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
                                                    {getStatusText(sale.status)}
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
                                            {#if sale.confirmed_at}
                                            <div class="flex flex-col gap-1.5">
                                                <span class="text-xs font-normal text-secondary-foreground">
                                                    Confirmed
                                                </span>
                                                <span class="text-sm font-medium text-mono">
                                                    {formatTimestamp(sale.confirmed_at)}
                                                </span>
                                            </div>
                                            {/if}
                                            {#if sale.estimated_delivered_at}
                                            <div class="flex flex-col gap-1.5">
                                                <span class="text-xs font-normal text-secondary-foreground">
                                                    Estimated Delivery
                                                </span>
                                                <span class="text-sm font-medium text-mono">
                                                    {formatTimestamp(sale.estimated_delivered_at)}
                                                </span>
                                            </div>
                                            {/if}
                                            {#if sale.delivered_at}
                                            <div class="flex flex-col gap-1.5">
                                                <span class="text-xs font-normal text-secondary-foreground">
                                                    Delivered At
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
                                                    Confirm
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

    <!-- Sale Confirmation Modal -->
    <div class="kt-modal" data-kt-modal="true" id="sale_modal">
        <div class="kt-modal-content max-w-[600px] top-[5%]">
            <div class="kt-modal-header">
                <h3 class="kt-modal-title">Confirm Sale</h3>
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
                                    <div class="flex justify-between">
                                        <span class="text-sm text-muted-foreground">Shipping Cost:</span>
                                        <span class="font-medium">DZD {selectedSale.shipping_cost}</span>
                                    </div>
                                    <div class="flex justify-between">
                                        <span class="text-sm text-muted-foreground">Shipping Time:</span>
                                        <span class="font-medium">{selectedSale.shipping_time_days} days</span>
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
                                <div class="flex justify-between items-center">
                                    <span class="text-sm font-normal text-secondary-foreground">
                                        Shipping
                                    </span>
                                    <span class="text-sm font-medium text-mono">
                                        DZD {(selectedSale.shipping_cost * selectedSale.quantity).toFixed(3)}
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
                                            <p>• You will pay <strong>DZD {(selectedSale.shipping_cost * selectedSale.quantity).toFixed(3)}</strong> in shipping costs immediately</p>
                                            <p>• You will receive <strong>DZD {(selectedSale.sale_price * selectedSale.quantity).toFixed(3)}</strong> only after delivery is completed</p>
                                            <p>• Make sure you have sufficient inventory and funds before confirming</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Confirmation Message -->
                        <div class="text-sm text-muted-foreground">
                            Are you sure you want to confirm this sale? This will deduct shipping costs from your funds and reserve inventory for delivery.
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
                        disabled={confirmingSale}
                    >
                        Cancel
                    </button>
                    <button 
                        class="kt-btn kt-btn-primary"
                        on:click={confirmSale}
                        disabled={confirmingSale}
                    >
                        {#if confirmingSale}
                            <i class="ki-filled ki-loading animate-spin text-sm"></i>
                            Confirming...
                        {:else}
                            <i class="ki-filled ki-check text-sm"></i>
                            Confirm Sale
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
            {#if selectedConfirmedSale}
                <!-- Sale Image -->
                <div class="kt-card relative items-center justify-center bg-accent/50 mb-6.5 h-[180px] shadow-none">
                    {#if selectedConfirmedSale.product.image_url}
                        <img 
                            src={selectedConfirmedSale.product.image_url} 
                            alt={selectedConfirmedSale.product.name}
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
                    {selectedConfirmedSale.product.name}
                </span>

                <!-- Sale Details -->
                <div class="flex flex-col gap-2.5">
                    <div class="flex items-center gap-2.5">
                        <span class="text-xs font-normal text-foreground min-w-14 xl:min-w-24 shrink-0">
                            Sale ID
                        </span>
                        <div>
                            <span class="text-xs font-medium text-foreground">
                                #{selectedConfirmedSale.id}
                            </span>
                        </div>
                    </div>
                    <div class="flex items-center gap-2.5">
                        <span class="text-xs font-normal text-foreground min-w-14 xl:min-w-24 shrink-0">
                            Status
                        </span>
                        <div>
                            <span class="kt-badge kt-badge-sm {getStatusBadgeClass(selectedConfirmedSale.status)}">
                                {getStatusText(selectedConfirmedSale.status)}
                            </span>
                        </div>
                    </div>
                    <div class="flex items-center gap-2.5">
                        <span class="text-xs font-normal text-foreground min-w-14 xl:min-w-24 shrink-0">
                            Wilaya
                        </span>
                        <div>
                            <span class="text-xs font-medium text-foreground">
                                {selectedConfirmedSale.wilaya.name}
                            </span>
                        </div>
                    </div>
                    <div class="flex items-center gap-2.5">
                        <span class="text-xs font-normal text-foreground min-w-14 xl:min-w-24 shrink-0">
                            Quantity
                        </span>
                        <div>
                            <span class="text-xs font-medium text-foreground">
                                {selectedConfirmedSale.quantity}
                            </span>
                        </div>
                    </div>
                    <div class="flex items-center gap-2.5">
                        <span class="text-xs font-normal text-foreground min-w-14 xl:min-w-24 shrink-0">
                            Sale Price
                        </span>
                        <div>
                            <span class="text-xs font-medium text-foreground">
                                DZD {selectedConfirmedSale.sale_price}
                            </span>
                        </div>
                    </div>
                    <div class="flex items-center gap-2.5">
                        <span class="text-xs font-normal text-foreground min-w-14 xl:min-w-24 shrink-0">
                            Shipping Cost
                        </span>
                        <div>
                            <span class="text-xs font-medium text-foreground">
                                DZD {selectedConfirmedSale.shipping_cost}
                            </span>
                        </div>
                    </div>
                    <div class="flex items-center gap-2.5">
                        <span class="text-xs font-normal text-foreground min-w-14 xl:min-w-24 shrink-0">
                            Shipping Time
                        </span>
                        <div>
                            <span class="text-xs font-medium text-foreground">
                                {selectedConfirmedSale.shipping_time_days} days
                            </span>
                        </div>
                    </div>
                    <div class="flex items-center gap-2.5">
                        <span class="text-xs font-normal text-foreground min-w-14 xl:min-w-24 shrink-0">
                            Initiated At
                        </span>
                        <div>
                            <span class="text-xs font-medium text-foreground">
                                {formatTimestamp(selectedConfirmedSale.initiated_at)}
                            </span>
                        </div>
                    </div>
                    {#if selectedConfirmedSale.confirmed_at}
                        <div class="flex items-center gap-2.5">
                            <span class="text-xs font-normal text-foreground min-w-14 xl:min-w-24 shrink-0">
                                Confirmed At
                            </span>
                            <div>
                                <span class="text-xs font-medium text-foreground">
                                    {formatTimestamp(selectedConfirmedSale.confirmed_at)}
                                </span>
                            </div>
                        </div>
                    {/if}
                    {#if selectedConfirmedSale.estimated_delivered_at}
                        <div class="flex items-center gap-2.5">
                            <span class="text-xs font-normal text-foreground min-w-14 xl:min-w-24 shrink-0">
                                Estimated Delivery
                            </span>
                            <div>
                                <span class="text-xs font-medium text-foreground">
                                    {formatTimestamp(selectedConfirmedSale.estimated_delivered_at)}
                                </span>
                            </div>
                        </div>
                    {/if}
                    {#if selectedConfirmedSale.delivered_at}
                        <div class="flex items-center gap-2.5">
                            <span class="text-xs font-normal text-foreground min-w-14 xl:min-w-24 shrink-0">
                                Delivered At
                            </span>
                            <div>
                                <span class="text-xs font-medium text-foreground">
                                    {formatTimestamp(selectedConfirmedSale.delivered_at)}
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
                                DZD {(selectedConfirmedSale.sale_price * selectedConfirmedSale.quantity).toFixed(3)}
                            </span>
                        </div>
                        <div class="flex justify-between items-center">
                            <span class="text-sm font-normal text-secondary-foreground">
                                Shipping
                            </span>
                            <span class="text-sm font-medium text-mono">
                                DZD {(selectedConfirmedSale.shipping_cost * selectedConfirmedSale.quantity).toFixed(3)}
                            </span>
                        </div>
                    </div>
                </div>
                
            {/if}
        </div>
    </div>
</CompanyLayout> 