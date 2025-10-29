<script>
    import CompanyLayout from '../../Layouts/CompanyLayout.svelte';
    import Pagination from '../../Components/Pagination.svelte';
    import { onMount, onDestroy, tick } from 'svelte';

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
    let loading = true;
    let fetchInterval = null;
    let pagination = {};
    let perPage = 10;
    let currentPage = 1;

    let selectedMovement = null;
    let showMovementDrawer = false;

    // Filtering variables
    let searchTerm = '';
    let filterType = '';
    let sortBy = '';
    let searchTimeout; 

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

    // Fetch inventory movements data
    async function fetchInventoryMovements() {
        if(inventoryMovements.length == 0) loading = true;
        
        try {
            const params = new URLSearchParams({
                page: currentPage,
                perPage: perPage,
            });

            // Add search parameters
            if (searchTerm) params.append('search', searchTerm);
            if (filterType) params.append('type', filterType);
            if (sortBy) params.append('sort', sortBy);

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

    // Handle pagination
    function goToPage(page) {
        if (page && page !== currentPage) {
            currentPage = page;
            loading = true;
            fetchInventoryMovements();
        }
    }

    // Handle per page change
    function handlePerPageChange(newPerPage) {
        perPage = newPerPage;
        currentPage = 1;
        loading = true;
        fetchInventoryMovements();
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

    // Search handler with debouncing
    function handleSearch() {
        clearTimeout(searchTimeout);
        searchTimeout = setTimeout(() => {
            currentPage = 1;
            fetchInventoryMovements();
        }, 300);
    }

    // Handle filter changes
    function handleFilterChange() {
        currentPage = 1;
        fetchInventoryMovements();
    }

    // Reactive updates for filters
    $: if (searchTerm !== undefined) {
        handleSearch();
    }

    $: if (filterType !== undefined) {
        handleFilterChange();
    }

    $: if (sortBy !== undefined) {
        handleFilterChange();
    }

    onMount(() => {
        fetchInventoryMovements();
        fetchInterval = setInterval(fetchInventoryMovements, 60000);
    });

    onDestroy(() => {
        clearInterval(fetchInterval);
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
                        <div class="flex items-center gap-2 justify-between w-full">
                            <!-- Filter Buttons -->
                            <div class="flex gap-2 flex-wrap">
                                <button 
                                    class="kt-btn kt-btn-sm {filterType === '' ? 'kt-btn-primary' : 'kt-btn-light'}"
                                    on:click={() => filterType = ''}
                                >
                                    All
                                </button>
                                <button 
                                    class="kt-btn kt-btn-sm {filterType === 'in' ? 'kt-btn-primary' : 'kt-btn-light'}"
                                    on:click={() => filterType = 'in'}
                                >
                                    In
                                </button>
                                <button 
                                    class="kt-btn kt-btn-sm {filterType === 'out' ? 'kt-btn-primary' : 'kt-btn-light'}"
                                    on:click={() => filterType = 'out'}
                                >
                                    Out
                                </button>
                                <button 
                                    class="kt-btn kt-btn-sm {filterType === 'expired' ? 'kt-btn-primary' : 'kt-btn-light'}"
                                    on:click={() => filterType = 'expired'}
                                >
                                    Expired
                                </button>
                                <button 
                                    class="kt-btn kt-btn-sm {filterType === 'damaged' ? 'kt-btn-primary' : 'kt-btn-light'}"
                                    on:click={() => filterType = 'damaged'}
                                >
                                    Damaged
                                </button>
                            </div>
                            
                            <!-- Sort and Search -->
                            <div class="flex items-center gap-2">
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
                                    <option value="product_asc">A-Z</option>
                                    <option value="product_desc">Z-A</option>
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
                    <div class="kt-scrollable-x-auto">
                        <table class="kt-table kt-table-border table-fixed">
                            <thead>
                                <tr>
                                    <th style="width: 80px;">
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
                                            <span class="kt-table-col-label">Movement</span>
                                        </span>
                                    </th>
                                    <th>
                                        <span class="kt-table-col">
                                            <span class="kt-table-col-label">Quantity</span>
                                        </span>
                                    </th>
                                </tr>
                            </thead>
                            <tbody>
                                {#if loading}
                                    <!-- Loading skeleton rows -->
                                    {#each Array(10) as _, i}
                                        <tr>
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
                                                    Inventory movements will appear here when you make purchases, sales, or other stock changes.
                                                </p>
                                            </div>
                                        </td>
                                    </tr>
                                {:else}
                                    <!-- Actual data rows -->
                                    {#each inventoryMovements as movement}
                                        <tr class="hover:bg-muted/50 cursor-pointer" on:click={() => openMovementDrawer(movement)}>
                                            <td>
                                                <span class="text-sm font-medium text-mono">#{movement.id}</span>
                                            </td>
                                            <td>
                                                <div class="flex items-center gap-3">
                                                    <div class="flex-shrink-0">
                                                        <!-- Product Image -->
                                                        <div class="size-10">
                                                            <img 
                                                                class="rounded-lg w-full h-full object-cover bg-gray-100" 
                                                                src={movement.product.image_url}
                                                                alt={movement.product.name}
                                                            />
                                                        </div>
                                                    </div>
                                                    <div class="flex flex-col gap-1">
                                                        <span class="text-sm font-medium text-mono hover:text-primary">
                                                            {movement.product ? movement.product.name : 'Unknown Product'}
                                                        </span>

                                                    </div>
                                                </div>
                                            </td>
                                            <td>
                                                <span class="kt-badge {getMovementTypeClass(movement.movement_type)} kt-badge-sm">
                                                    {movement.movement_type} {movement.reference_type ? '(' + movement.reference_type + ')' : ''}
                                                </span>
                                            </td>
                                            <td>
                                                <span class="text-sm font-medium text-success">
                                                    {movement.original_quantity}
                                                </span>
                                            </td>
                                        </tr>
                                    {/each}
                                {/if}
                            </tbody>
                        </table>
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
                                {selectedMovement.movement_type}
                            </span>
                        </div>
                    </div>
                    <div class="flex items-center gap-2.5">
                        <span class="text-xs font-normal text-foreground min-w-14 xl:min-w-24 shrink-0">
                            Quantity
                        </span>
                        <div>
                            <span class="text-xs font-medium text-success">
                                {selectedMovement.original_quantity}
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