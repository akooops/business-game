<script>
    import AdminLayout from '../../Layouts/AdminLayout.svelte';
    import Pagination from '../../Components/Pagination.svelte';
    import { onMount, tick } from 'svelte';
    import { page } from '@inertiajs/svelte'

    // Define breadcrumbs for this page
    const breadcrumbs = [
        {
            title: 'Suppliers',
            url: route('admin.suppliers.index'),
            active: false
        },
        {
            title: 'Index',
            url: route('admin.suppliers.index'),
            active: true
        }
    ];
    
    const pageTitle = 'Suppliers';

    // Reactive variables
    let suppliers = [];
    let pagination = {};
    let loading = true;
    let search = '';
    let perPage = 10;
    let currentPage = 1;
    let searchTimeout;

    // Fetch suppliers data
    async function fetchSuppliers() {
        loading = true;
        try {
            const params = new URLSearchParams({
                page: currentPage,
                perPage: perPage,
                search: search
            });
            
            const response = await fetch(`${route('admin.suppliers.index')}?${params}`, {
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                }
            });
            
            const data = await response.json();
            suppliers = data.suppliers;
            pagination = data.pagination;
            
            // Wait for DOM to update, then initialize menus
            await tick();
            if (window.KTMenu) {
                window.KTMenu.init();
            }
        } catch (error) {
            console.error('Error fetching suppliers:', error);
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
            fetchSuppliers();
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
            fetchSuppliers();
        }
    }

    // Handle per page change
    function handlePerPageChange(newPerPage) {
        perPage = newPerPage;
        currentPage = 1;
        fetchSuppliers();
    }

    // Delete supplier
    async function deleteSupplier(supplierId) {
        if (!confirm('Are you sure you want to delete this supplier? This action cannot be undone.')) {
            return;
        }

        try {
            const formData = new FormData();
            formData.append('_method', 'DELETE');
            formData.append('_token', document.querySelector('meta[name="csrf-token"]')?.getAttribute('content'));

            const response = await fetch(route('admin.suppliers.destroy', { supplier: supplierId }), {
                method: 'POST',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: formData
            });

            if (response.ok) {
                // Show success toast
                showToast("Supplier deleted successfully!", 'success');

                // Refresh the suppliers list
                fetchSuppliers();
            } else {
                const errorData = await response.json().catch(() => ({}));
                const errorMessage = errorData.message || 'Error deleting supplier. Please try again.';
                
                showToast(errorMessage, 'error');
            }
        } catch (error) {
            console.error('Error deleting supplier:', error);
            showToast("Network error. Please check your connection and try again.", 'error');
        }
    }

    // Get supplier type badge class
    function getSupplierTypeBadgeClass(isInternational) {
        return isInternational 
            ? 'kt-badge kt-badge-outline kt-badge-primary'
            : 'kt-badge kt-badge-outline kt-badge-success';
    }

    onMount(() => {
        fetchSuppliers();
    });

    // Flash message handling
    export let success;

    $: if (success) {
        showToast(success, 'success');
    }
</script>

<svelte:head>
    <title>Business Game - {pageTitle}</title>
</svelte:head>

<AdminLayout {breadcrumbs} {pageTitle}>
    <!-- Container -->
    <div class="kt-container-fixed">
        <div class="grid gap-5 lg:gap-7.5">
            <!-- Suppliers Header -->
            <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4">
                <div class="flex flex-col gap-1">
                    <h1 class="text-2xl font-bold text-mono">Suppliers Management</h1>
                    <p class="text-sm text-secondary-foreground">
                        Manage your supply chain and vendor relationships
                    </p>
                </div>
                <div class="flex items-center gap-3">
                    <a href="{route('admin.suppliers.create')}" class="kt-btn kt-btn-primary">
                        <i class="ki-filled ki-plus text-base"></i>
                        Add Supplier
                    </a>
                </div>
            </div>

            <!-- Suppliers Table -->
            <div class="kt-card">
                <div class="kt-card-header">
                    <div class="kt-card-toolbar flex flex-col lg:flex-row lg:items-center gap-4 w-full">
                        <div class="kt-input max-w-64 w-64">
                            <i class="ki-filled ki-magnifier"></i>
                            <input 
                                type="text" 
                                class="kt-input" 
                                placeholder="Search suppliers..." 
                                bind:value={search}
                                on:input={handleSearchInput}
                            />
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
                                            <span class="kt-table-col-label">Supplier</span>
                                        </span>
                                    </th>
                                    <th>
                                        <span class="kt-table-col">
                                            <span class="kt-table-col-label">Location</span>
                                        </span>
                                    </th>
                                    <th>
                                        <span class="kt-table-col">
                                            <span class="kt-table-col-label">Shipping Cost</span>
                                        </span>
                                    </th>
                                    <th>
                                        <span class="kt-table-col">
                                            <span class="kt-table-col-label">Shipping Time</span>
                                        </span>
                                    </th>
                                    <th>
                                        <span class="kt-table-col">
                                            <span class="kt-table-col-label">Carbon Footprint</span>
                                        </span>
                                    </th>
                                    <th style="width: 80px;">
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
                                                <div class="kt-skeleton w-20 h-4 rounded"></div>
                                            </td>
                                            <td class="p-4">
                                                <div class="kt-skeleton w-16 h-6 rounded"></div>
                                            </td>
                                            <td class="p-4">
                                                <div class="kt-skeleton w-20 h-4 rounded"></div>
                                            </td>
                                            <td class="p-4">
                                                <div class="kt-skeleton w-16 h-6 rounded"></div>
                                            </td>
                                            <td class="p-4">
                                                <div class="kt-skeleton w-20 h-4 rounded"></div>
                                            </td>
                                            <td class="p-4">
                                                <div class="kt-skeleton w-20 h-4 rounded"></div>
                                            </td>
                                            <td class="p-4">
                                                <div class="kt-skeleton w-8 h-8 rounded"></div>
                                            </td>
                                        </tr>
                                    {/each}
                                {:else if suppliers.length === 0}
                                    <!-- Empty state -->
                                    <tr>
                                        <td colspan="10" class="p-10">
                                            <div class="flex flex-col items-center justify-center text-center">
                                                <div class="mb-4">
                                                    <i class="ki-filled ki-ship text-4xl text-muted-foreground"></i>
                                                </div>
                                                <h3 class="text-lg font-semibold text-mono mb-2">No suppliers found</h3>
                                                <p class="text-sm text-secondary-foreground mb-4">
                                                    {search ? 'No suppliers match your search criteria.' : 'No suppliers available. Create your first supplier to get started.'}
                                                </p>
                                                {#if !search}
                                                    <a href="{route('admin.suppliers.create')}" class="kt-btn kt-btn-primary">
                                                        <i class="ki-filled ki-plus text-sm"></i>
                                                        Add First Supplier
                                                    </a>
                                                {/if}
                                            </div>
                                        </td>
                                    </tr>
                                {:else}
                                    <!-- Actual data rows -->
                                    {#each suppliers as supplier}
                                        <tr class="hover:bg-muted/50">
                                            <td>
                                                <span class="text-sm font-medium text-mono">#{supplier.id}</span>
                                            </td>
                                            <td>
                                                <div class="flex items-center gap-3">
                                                    <div class="flex-shrink-0">
                                                        {#if supplier.image_url}
                                                            <img 
                                                                src={supplier.image_url} 
                                                                alt={supplier.name}
                                                                class="w-10 h-10 rounded-lg object-cover"
                                                            />
                                                        {:else}
                                                            <div class="w-10 h-10 rounded-lg bg-accent/50 flex items-center justify-center">
                                                                <i class="ki-filled ki-ship text-lg text-muted-foreground"></i>
                                                            </div>
                                                        {/if}
                                                    </div>
                                                    <div class="flex flex-col gap-1">
                                                        <span class="text-sm font-medium text-mono hover:text-primary">
                                                            {supplier.name}
                                                        </span>
                                                    </div>
                                                </div>
                                            </td>
                                            <td>
                                                <span class={getSupplierTypeBadgeClass(supplier.is_international)}>
                                                    {supplier.is_international ? 'International' : 'Local'} - { supplier.location_name}
                                                </span>
                                            </td>
                                            <td>
                                                <div class="flex flex-col gap-1">
                                                    <span class="text-xs text-muted-foreground">
                                                        ({supplier.min_shipping_cost} - {supplier.max_shipping_cost} DZD)
                                                    </span>
                                                </div>
                                            </td>
                                            <td>
                                                <div class="flex flex-col gap-1">
                                                    <span class="text-xs text-muted-foreground">
                                                        ({supplier.min_shipping_time_days} - {supplier.max_shipping_time_days} days)
                                                    </span>
                                                </div>
                                            </td>
                                            <td>
                                                <div class="flex flex-col gap-1">
                                                    <span class="text-xs text-muted-foreground">
                                                        ({supplier.carbon_footprint} kg CO2/unit)
                                                    </span>
                                                </div>
                                            </td>
                                            <td class="text-center">
                                                <div class="kt-menu flex-inline" data-kt-menu="true">
                                                    <div class="kt-menu-item" data-kt-menu-item-offset="0, 10px" data-kt-menu-item-placement="bottom-end" data-kt-menu-item-placement-rtl="bottom-start" data-kt-menu-item-toggle="dropdown" data-kt-menu-item-trigger="click">
                                                        <button class="kt-menu-toggle kt-btn kt-btn-sm kt-btn-icon kt-btn-ghost">
                                                            <i class="ki-filled ki-dots-vertical text-lg"></i>
                                                        </button>
                                                        <div class="kt-menu-dropdown kt-menu-default w-full max-w-[175px]" data-kt-menu-dismiss="true">
                                                            <div class="kt-menu-item">
                                                                <a class="kt-menu-link" href={route('admin.suppliers.show', { supplier: supplier.id })}>
                                                                    <span class="kt-menu-icon">
                                                                        <i class="ki-filled ki-search-list"></i>
                                                                    </span>
                                                                    <span class="kt-menu-title">View</span>
                                                                </a>
                                                            </div>

                                                            <div class="kt-menu-item">
                                                                <a class="kt-menu-link" href={route('admin.suppliers.edit', { supplier: supplier.id })}>
                                                                    <span class="kt-menu-icon">
                                                                        <i class="ki-filled ki-pencil"></i>
                                                                    </span>
                                                                    <span class="kt-menu-title">Edit</span>
                                                                </a>
                                                            </div>

                                                            <div class="kt-menu-separator"></div>
                                                            
                                                            <div class="kt-menu-item">
                                                                <button class="kt-menu-link" on:click={() => deleteSupplier(supplier.id)}>
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
