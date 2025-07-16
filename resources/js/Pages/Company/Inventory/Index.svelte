<script>
    import CompanyLayout from '../../Layouts/CompanyLayout.svelte';
    import Pagination from '../../Components/Pagination.svelte';
    import Select2 from '../../Components/Forms/Select2.svelte';
    import Flatpickr from '../../Components/Forms/Flatpickr.svelte';
    import { onMount, tick } from 'svelte';
    import { page } from '@inertiajs/svelte';

    // Define breadcrumbs for this page
    const breadcrumbs = [
        {
            title: 'Inventory',
            url: route('company.inventory.index'),
            active: false
        },
        {
            title: 'Index',
            url: route('company.inventory.index'),
            active: true
        }
    ];
    
    const pageTitle = 'Inventory Movements';

    // Reactive variables
    let inventoryMovements = [];
    let pagination = {};
    let loading = true;
    let search = '';
    let perPage = 10;
    let currentPage = 1;
    let searchTimeout;
    let showFilters = false;

    // Filter variables
    let productFilter = '';
    let movementTypeFilter = '';
    let referenceTypeFilter = '';
    let minMovedAt = '';
    let maxMovedAt = '';

    // Select2 component references
    let productSelectComponent;

    // Flatpickr component references
    let minMovedAtFlatpickr;
    let maxMovedAtFlatpickr;

    // Drawer state
    let selectedMovement = null;
    let showMovementDrawer = false;

    // Format number with units
    function formatNumber(value, decimals = 2) {
        return parseFloat(value).toFixed(decimals);
    }

    // Get movement type badge class
    function getMovementTypeClass(type) {
        switch (type) {
            case 'in':
                return 'kt-badge-success';
            case 'out':
                return 'kt-badge-warning';
            case 'expired':
            case 'damaged':
            case 'lost':
                return 'kt-badge-danger';
            default:
                return 'kt-badge-secondary';
        }
    }

    // Get movement type label
    function getMovementTypeLabel(type) {
        switch (type) {
            case 'in':
                return 'Incoming';
            case 'out':
                return 'Outgoing';
            case 'expired':
                return 'Expired';
            case 'damaged':
                return 'Damaged';
            case 'lost':
                return 'Lost';
            default:
                return type;
        }
    }

    // Fetch inventory movements data
    async function fetchInventoryMovements() {
        loading = true;
        try {
            const params = new URLSearchParams({
                page: currentPage,
                perPage: perPage,
                search: search
            });
            
            // Add filter parameters
            if (productFilter) {
                params.append('product_id', productFilter);
            }
            if (movementTypeFilter) {
                params.append('movement_type', movementTypeFilter);
            }
            if (referenceTypeFilter) {
                params.append('reference_type', referenceTypeFilter);
            }
            if (minMovedAt) {
                params.append('min_moved_at', minMovedAt);
            }
            if (maxMovedAt) {
                params.append('max_moved_at', maxMovedAt);
            }
            
            const response = await fetch(route('company.inventory.index') + '?' + params.toString(), {
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                }
            });
            
            const data = await response.json();
            inventoryMovements = data.inventoryMovements;
            pagination = data.pagination;
            
            // Wait for DOM to update, then initialize menus
            await tick();
            if (window.KTMenu) {
                window.KTMenu.init();
            }
        } catch (error) {
            console.error('Error fetching inventory movements:', error);
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
            fetchInventoryMovements();
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
            fetchInventoryMovements();
        }
    }

    // Handle per page change
    function handlePerPageChange(newPerPage) {
        perPage = newPerPage;
        currentPage = 1;
        fetchInventoryMovements();
    }

    // Handle filter changes
    function handleFilterChange() {
        currentPage = 1;
        fetchInventoryMovements();
    }

    // Handle product selection
    function handleProductSelect(event) {
        productFilter = event.detail.value;
        handleFilterChange();
    }

    // Handle product clear
    function handleProductClear() {
        productFilter = '';
        handleFilterChange();
    }

    // Handle date changes
    function handleMinMovedAtChange(event) {
        minMovedAt = event.detail.value;
        handleFilterChange();
    }

    function handleMaxMovedAtChange(event) {
        maxMovedAt = event.detail.value;
        handleFilterChange();
    }

    // Clear all filters
    function clearAllFilters() {
        productFilter = '';
        movementTypeFilter = '';
        referenceTypeFilter = '';
        minMovedAt = '';
        maxMovedAt = '';
        currentPage = 1;
        
        // Clear the Select2 components
        if (productSelectComponent) {
            productSelectComponent.clear();
        }
        
        // Clear Flatpickr components
        if (minMovedAtFlatpickr) minMovedAtFlatpickr.clear();
        if (maxMovedAtFlatpickr) maxMovedAtFlatpickr.clear();
        
        fetchInventoryMovements();
    }

    // Toggle filters visibility
    function toggleFilters() {
        showFilters = !showFilters;
    }

    // Open movement drawer
    function openMovementDrawer(movement) {
        selectedMovement = movement;
        showMovementDrawer = true;
        
        // Show drawer
        const toggleButton = document.querySelector('[data-kt-drawer-toggle="#movement_drawer"]');
        if (toggleButton) {
            toggleButton.click();
        }
    }

    // Close movement drawer
    function closeMovementDrawer() {
        showMovementDrawer = false;
        selectedMovement = null;
    }

    // Handle reference click
    function handleReferenceClick(movement) {
        if (!movement.reference_type || !movement.reference_id) {
            return;
        }

        let targetUrl = '';
        
        switch (movement.reference_type) {
            case 'purchase':
                targetUrl = route('company.purchases.index') + '?search=' + movement.reference_id;
                break;
            case 'sale':
                // Assuming you have a sales page, adjust the route as needed
                targetUrl = route('company.sales.index') + '?search=' + movement.reference_id;
                break;
            case 'production':
                // Assuming you have a production page, adjust the route as needed
                targetUrl = route('company.production.index') + '?search=' + movement.reference_id;
                break;
            default:
                return;
        }

        if (targetUrl) {
            window.open(targetUrl, '_blank');
        }
    }

    onMount(() => {
        fetchInventoryMovements();
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
    <div class="kt-container-fixed">
        <div class="grid gap-5 lg:gap-7.5">
            <!-- Inventory Header -->
            <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4">
                <div class="flex flex-col gap-1">
                    <h1 class="text-2xl font-bold text-mono">Inventory Movements</h1>
                    <p class="text-sm text-secondary-foreground">
                        Track all inventory movements and stock changes
                    </p>
                </div>
            </div>

            <!-- Inventory Table -->
            <div class="kt-card">
                <div class="kt-card-header">
                    <div class="kt-card-toolbar">
                        <div class="flex items-center gap-4">
                            <div class="kt-input max-w-64 w-64">
                                <i class="ki-filled ki-magnifier"></i>
                                <input 
                                    type="text" 
                                    class="kt-input" 
                                    placeholder="Search movements..." 
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
                            {#if productFilter || movementTypeFilter || referenceTypeFilter || minMovedAt || maxMovedAt}
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
                            <!-- Product Filter -->
                            <div class="space-y-2">
                                <h4 class="text-sm font-medium text-gray-700">Product</h4>
                                <Select2
                                    bind:this={productSelectComponent}
                                    id="product-filter"
                                    placeholder="Search products..."
                                    bind:value={productFilter}
                                    on:select={handleProductSelect}
                                    on:clear={handleProductClear}
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
                                                    text: `${product.product.name}`,
                                                    name: product.product.name,
                                                    type: product.product.type,
                                                    type_name: product.product.type_name,
                                                    image_url: product.product.image_url
                                                }))
                                            };
                                        },
                                        cache: true
                                    }}
                                    templateResult={function(data) {
                                        if (data.loading) return data.text;
                                        
                                        const $elem = globalThis.$('<div class="flex items-center gap-3">' +
                                            '<div class="flex items-center justify-center size-8 shrink-0 rounded bg-accent/50">' +
                                            (data.image_url ? '<img src="' + data.image_url + '" alt="" class="size-6 object-cover rounded">' : '<i class="ki-filled ki-abstract-26 text-xs text-muted-foreground"></i>') +
                                            '</div>' +
                                            '<div class="flex flex-col">' +
                                            '<span class="font-medium text-sm">' + data.name + '</span>' +
                                            '<span class="kt-badge kt-badge-outline kt-badge-primary kt-badge-xs w-fit">' + data.type_name + '</span>' +
                                            '</div>' +
                                            '</div>');
                                        return $elem;
                                    }}
                                    templateSelection={function(data) {
                                        if (!data.id) return data.text;
                                        return data.name;
                                    }}
                                />
                            </div>

                            <!-- Movement Type Filter -->
                            <div class="space-y-2">
                                <h4 class="text-sm font-medium text-gray-700">Movement Type</h4>
                                <select 
                                    class="kt-input w-full" 
                                    bind:value={movementTypeFilter}
                                    on:change={handleFilterChange}
                                >
                                    <option value="">All Types</option>
                                    <option value="in">Incoming</option>
                                    <option value="out">Outgoing</option>
                                    <option value="expired">Expired</option>
                                    <option value="damaged">Damaged</option>
                                    <option value="lost">Lost</option>
                                </select>
                            </div>

                            <!-- Reference Type Filter -->
                            <div class="space-y-2">
                                <h4 class="text-sm font-medium text-gray-700">Reference Type</h4>
                                <select 
                                    class="kt-input w-full" 
                                    bind:value={referenceTypeFilter}
                                    on:change={handleFilterChange}
                                >
                                    <option value="">All Types</option>
                                    <option value="purchase">Purchase</option>
                                    <option value="sale">Sale</option>
                                    <option value="production">Production</option>
                                </select>
                            </div>

                            <!-- Date Range -->
                            <div class="space-y-2">
                                <h4 class="text-sm font-medium text-gray-700">Movement Date Range</h4>
                                
                                <div class="flex gap-2">
                                    <Flatpickr
                                        bind:this={minMovedAtFlatpickr}
                                        id="min-moved-at"
                                        placeholder="From Date"
                                        config={{
                                            enableTime: true,
                                            dateFormat: 'Y-m-d H:i',
                                            time_24hr: true
                                        }}
                                        on:change={handleMinMovedAtChange}
                                    />
                                    <Flatpickr
                                        bind:this={maxMovedAtFlatpickr}
                                        id="max-moved-at"
                                        placeholder="To Date"
                                        config={{
                                            enableTime: true,
                                            dateFormat: 'Y-m-d H:i',
                                            time_24hr: true
                                        }}
                                        on:change={handleMaxMovedAtChange}
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
                                    <th class="min-w-[120px]">
                                        <span class="kt-table-col">
                                            <span class="kt-table-col-label">Movement</span>
                                        </span>
                                    </th>
                                    <th class="min-w-[100px]">
                                        <span class="kt-table-col">
                                            <span class="kt-table-col-label">Quantity</span>
                                        </span>
                                    </th>
                                    <th class="min-w-[150px]">
                                        <span class="kt-table-col">
                                            <span class="kt-table-col-label">Reference</span>
                                        </span>
                                    </th>
                                    <th class="min-w-[150px]">
                                        <span class="kt-table-col">
                                            <span class="kt-table-col-label">Date</span>
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
                                                        <div class="kt-skeleton w-32 h-4 rounded"></div>
                                                        <div class="kt-skeleton w-20 h-3 rounded"></div>
                                                    </div>
                                                </div>
                                            </td>
                                            <td class="p-4">
                                                <div class="kt-skeleton w-20 h-6 rounded"></div>
                                            </td>
                                            <td class="p-4">
                                                <div class="kt-skeleton w-16 h-4 rounded"></div>
                                            </td>
                                            <td class="p-4">
                                                <div class="kt-skeleton w-24 h-4 rounded"></div>
                                            </td>
                                            <td class="p-4">
                                                <div class="kt-skeleton w-20 h-4 rounded"></div>
                                            </td>
                                            <td class="p-4">
                                                <div class="kt-skeleton w-8 h-8 rounded"></div>
                                            </td>
                                        </tr>
                                    {/each}
                                {:else if inventoryMovements.length === 0}
                                    <!-- Empty state -->
                                    <tr>
                                        <td colspan="9" class="p-10">
                                            <div class="flex flex-col items-center justify-center text-center">
                                                <div class="mb-4">
                                                    <i class="ki-filled ki-box text-4xl text-muted-foreground"></i>
                                                </div>
                                                <h3 class="text-lg font-semibold text-mono mb-2">No inventory movements found</h3>
                                                <p class="text-sm text-secondary-foreground mb-4">
                                                    {search ? 'No movements match your search criteria.' : 'Inventory movements will appear here when you make purchases, sales, or other stock changes.'}
                                                </p>
                                            </div>
                                        </td>
                                    </tr>
                                {:else}
                                    <!-- Actual data rows -->
                                    {#each inventoryMovements as movement}
                                        <tr class="hover:bg-muted/50">
                                            <td>
                                                <input class="kt-checkbox kt-checkbox-sm" type="checkbox" value={movement.id}/>
                                            </td>
                                            <td>
                                                <span class="text-sm font-medium text-mono">#{movement.id}</span>
                                            </td>
                                            <td>
                                                <div class="flex items-center gap-3">
                                                    <div class="flex-shrink-0">
                                                        {#if movement.product && movement.product.image_url}
                                                            <img 
                                                                src={movement.product.image_url} 
                                                                alt={movement.product.name}
                                                                class="w-10 h-10 rounded-lg object-cover"
                                                            />
                                                        {:else}
                                                            <div class="w-10 h-10 rounded-lg bg-accent/50 flex items-center justify-center">
                                                                <i class="ki-filled ki-abstract-26 text-lg text-muted-foreground"></i>
                                                            </div>
                                                        {/if}
                                                    </div>
                                                    <div class="flex flex-col gap-1">
                                                        <span class="text-sm font-medium text-mono hover:text-primary">
                                                            {movement.product ? movement.product.name : 'Unknown Product'}
                                                        </span>
                                                        {#if movement.product}
                                                            <span class="text-xs text-muted-foreground">
                                                                {movement.product.type_name}
                                                            </span>
                                                        {/if}
                                                    </div>
                                                </div>
                                            </td>
                                            <td>
                                                <span class="kt-badge {getMovementTypeClass(movement.movement_type)} kt-badge-sm">
                                                    {getMovementTypeLabel(movement.movement_type)}
                                                </span>
                                            </td>
                                            <td>
                                                <span class="text-sm font-medium {movement.quantity > 0 ? 'text-success' : 'text-warning'}">
                                                    {formatNumber(movement.quantity)}
                                                </span>
                                            </td>
                                            <td>
                                                <div class="flex flex-col gap-1">
                                                    <span class="text-xs font-medium text-mono">
                                                        {movement.reference_type || 'N/A'}
                                                    </span>
                                                    {#if movement.reference_id}
                                                        <button 
                                                            class="text-xs text-primary hover:text-primary-dark cursor-pointer text-left hover:underline"
                                                            on:click={() => handleReferenceClick(movement)}
                                                            title="Click to view {movement.reference_type} details"
                                                        >
                                                            ID: {movement.reference_id}
                                                            <i class="ki-filled ki-arrow-right text-xs ml-1"></i>
                                                        </button>
                                                    {:else}
                                                        <span class="text-xs text-muted-foreground">
                                                            No ID
                                                        </span>
                                                    {/if}
                                                </div>
                                            </td>
                                            <td>
                                                <span class="text-sm text-muted-foreground">
                                                    {formatTimestamp(movement.moved_at)}
                                                </span>
                                            </td>
                                            <td class="text-center">
                                                <div class="kt-menu flex-inline" data-kt-menu="true">
                                                    <div class="kt-menu-item" data-kt-menu-item-offset="0, 10px" data-kt-menu-item-placement="bottom-end" data-kt-menu-item-placement-rtl="bottom-start" data-kt-menu-item-toggle="dropdown" data-kt-menu-item-trigger="click">
                                                        <button class="kt-menu-toggle kt-btn kt-btn-sm kt-btn-icon kt-btn-ghost">
                                                            <i class="ki-filled ki-dots-vertical text-lg"></i>
                                                        </button>
                                                        <div class="kt-menu-dropdown kt-menu-default w-full max-w-[175px]" data-kt-menu-dismiss="true">
                                                            <div class="kt-menu-item">
                                                                <button class="kt-menu-link" on:click={() => openMovementDrawer(movement)}>
                                                                    <span class="kt-menu-icon">
                                                                        <i class="ki-filled ki-search-list"></i>
                                                                    </span>
                                                                    <span class="kt-menu-title">View Details</span>
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

    <!-- Hidden button to trigger drawer -->
    <button style="display:none" data-kt-drawer-toggle="#movement_drawer"></button>

    <!-- Movement Details Drawer -->
    <div class="hidden kt-drawer kt-drawer-end card flex-col max-w-[90%] w-[450px] top-5 bottom-5 end-5 rounded-xl border border-border" data-kt-drawer="true" data-kt-drawer-container="body" id="movement_drawer">
        <div class="flex items-center justify-between gap-2.5 text-sm text-mono font-semibold px-5 py-2.5 border-b border-b-border">
            Movement Details
            <button class="kt-btn kt-btn-sm kt-btn-icon kt-btn-dim shrink-0" data-kt-drawer-dismiss="true" on:click={closeMovementDrawer}>
                <i class="ki-filled ki-cross"></i>
            </button>
        </div>
        
        <div class="kt-card-content flex flex-col space-y-3 p-5 kt-scrollable-y-auto">
            {#if selectedMovement}
                <!-- Movement Image -->
                <div class="kt-card relative items-center justify-center bg-accent/50 mb-6.5 h-[180px] shadow-none">
                    {#if selectedMovement.product && selectedMovement.product.image_url}
                        <img 
                            src={selectedMovement.product.image_url} 
                            alt={selectedMovement.product.name}
                            class="h-[180px] w-full object-cover rounded-sm"
                        />
                    {:else}
                        <div class="flex items-center justify-center h-full">
                            <i class="ki-filled ki-abstract-26 text-4xl text-muted-foreground"></i>
                        </div>
                    {/if}
                </div>

                <!-- Movement Name -->
                <span class="text-base font-medium text-mono">
                    {selectedMovement.product ? selectedMovement.product.name : 'Unknown Product'}
                </span>

                <!-- Movement Details -->
                <div class="flex flex-col gap-2.5">
                    <div class="flex items-center gap-2.5">
                        <span class="text-xs font-normal text-foreground min-w-14 xl:min-w-24 shrink-0">
                            Movement ID
                        </span>
                        <div>
                            <span class="text-xs font-medium text-foreground">
                                #{selectedMovement.id}
                            </span>
                        </div>
                    </div>
                    <div class="flex items-center gap-2.5">
                        <span class="text-xs font-normal text-foreground min-w-14 xl:min-w-24 shrink-0">
                            Type
                        </span>
                        <div>
                            <span class="kt-badge kt-badge-sm {getMovementTypeClass(selectedMovement.movement_type)}">
                                {getMovementTypeLabel(selectedMovement.movement_type)}
                            </span>
                        </div>
                    </div>
                    <div class="flex items-center gap-2.5">
                        <span class="text-xs font-normal text-foreground min-w-14 xl:min-w-24 shrink-0">
                            Quantity
                        </span>
                        <div>
                            <span class="text-xs font-medium {selectedMovement.quantity > 0 ? 'text-success' : 'text-warning'}">
                                {selectedMovement.quantity > 0 ? '+' : ''}{formatNumber(selectedMovement.quantity)}
                            </span>
                        </div>
                    </div>
                    <div class="flex items-center gap-2.5">
                        <span class="text-xs font-normal text-foreground min-w-14 xl:min-w-24 shrink-0">
                            Reference Type
                        </span>
                        <div>
                            <span class="text-xs font-medium text-foreground">
                                {selectedMovement.reference_type || 'N/A'}
                            </span>
                        </div>
                    </div>
                    {#if selectedMovement.reference_id}
                    <div class="flex items-center gap-2.5">
                        <span class="text-xs font-normal text-foreground min-w-14 xl:min-w-24 shrink-0">
                            Reference ID
                        </span>
                        <div>
                            <span class="text-xs font-medium text-foreground">
                                {selectedMovement.reference_id}
                            </span>
                        </div>
                    </div>
                    {/if}
                    <div class="flex items-center gap-2.5">
                        <span class="text-xs font-normal text-foreground min-w-14 xl:min-w-24 shrink-0">
                            Movement Date
                        </span>
                        <div>
                            <span class="text-xs font-medium text-foreground">
                                {formatTimestamp(selectedMovement.moved_at || selectedMovement.created_at)}
                            </span>
                        </div>
                    </div>
                    {#if selectedMovement.notes}
                    <div class="flex items-start gap-2.5">
                        <span class="text-xs font-normal text-foreground min-w-14 xl:min-w-24 shrink-0">
                            Notes
                        </span>
                        <div>
                            <span class="text-xs font-medium text-foreground">
                                {selectedMovement.notes}
                            </span>
                        </div>
                    </div>
                    {/if}
                </div>

                <div class="border-t border-border"></div>

                <!-- Product Information -->
                {#if selectedMovement.product}
                <div class="kt-card bg-accent/50">
                    <div class="kt-card-header px-5">
                        <h3 class="kt-card-title">
                            Product Information
                        </h3>
                    </div>
                    <div class="kt-card-content px-5 py-4 space-y-2">
                        <div class="flex justify-between items-center">
                            <span class="text-sm font-normal text-secondary-foreground">
                                Product Name
                            </span>
                            <span class="text-sm font-medium text-mono">
                                {selectedMovement.product.name}
                            </span>
                        </div>
                        <div class="flex justify-between items-center">
                            <span class="text-sm font-normal text-secondary-foreground">
                                Product Type
                            </span>
                            <span class="text-sm font-medium text-mono">
                                {selectedMovement.product.type_name}
                            </span>
                        </div>
                        <div class="flex justify-between items-center">
                            <span class="text-sm font-normal text-secondary-foreground">
                                Product ID
                            </span>
                            <span class="text-sm font-medium text-mono">
                                #{selectedMovement.product.id}
                            </span>
                        </div>
                    </div>
                </div>
                {/if}
                
            {/if}
        </div>
    </div>
</CompanyLayout> 