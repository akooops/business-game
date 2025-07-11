<script>
    import AdminLayout from '../../Layouts/AdminLayout.svelte';
    import Pagination from '../../Components/Pagination.svelte';
    import Select2 from '../../Components/Forms/Select2.svelte';
    import { onMount, tick } from 'svelte';
    import { page } from '@inertiajs/svelte'

    // Define breadcrumbs for this page
    const breadcrumbs = [
        {
            title: 'Production Lines',
            url: route('admin.production-lines.index'),
            active: false
        },
        {
            title: 'Index',
            url: route('admin.production-lines.index'),
            active: true
        }
    ];
    
    const pageTitle = 'Production Lines';

    // Reactive variables
    let productionLines = [];
    let pagination = {};
    let loading = true;
    let search = '';
    let perPage = 10;
    let currentPage = 1;
    let searchTimeout;
    let showFilters = false;

    // Filter variables
    let areaMin = '';
    let areaMax = '';
    let productFilter = '';
    let stepsMin = '';
    let stepsMax = '';

    // Select2 component reference
    let productSelectComponent;

    // Fetch production lines data
    async function fetchProductionLines() {
        loading = true;
        try {
            const params = new URLSearchParams({
                page: currentPage,
                perPage: perPage,
                search: search
            });
            
            // Add filter parameters
            if (areaMin) {
                params.append('area_min', areaMin);
            }
            if (areaMax) {
                params.append('area_max', areaMax);
            }
            if (productFilter) {
                params.append('product_id', productFilter);
            }
            if (stepsMin) {
                params.append('steps_min', stepsMin);
            }
            if (stepsMax) {
                params.append('steps_max', stepsMax);
            }
            
            const response = await fetch(route('admin.production-lines.index') + '?' + params.toString(), {
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                }
            });
            
            const data = await response.json();
            productionLines = data.productionLines;
            pagination = data.pagination;
            
            // Wait for DOM to update, then initialize menus
            await tick();
            if (window.KTMenu) {
                window.KTMenu.init();
            }
        } catch (error) {
            console.error('Error fetching production lines:', error);
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
            fetchProductionLines();
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
            fetchProductionLines();
        }
    }

    // Handle per page change
    function handlePerPageChange(newPerPage) {
        perPage = newPerPage;
        currentPage = 1;
        fetchProductionLines();
    }

    // Handle filter changes
    function handleFilterChange() {
        currentPage = 1;
        fetchProductionLines();
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

    // Clear all filters
    function clearAllFilters() {
        areaMin = '';
        areaMax = '';
        productFilter = '';
        stepsMin = '';
        stepsMax = '';
        currentPage = 1;
        
        // Clear the Select2 component
        if (productSelectComponent) {
            productSelectComponent.clear();
        }
        
        fetchProductionLines();
    }

    // Toggle filters visibility
    function toggleFilters() {
        showFilters = !showFilters;
    }

    // Delete production line
    async function deleteProductionLine(productionLineId) {
        if (!confirm('Are you sure you want to delete this production line? This action cannot be undone.')) {
            return;
        }

        try {
            const formData = new FormData();
            formData.append('_method', 'DELETE');
            formData.append('_token', document.querySelector('meta[name="csrf-token"]')?.getAttribute('content'));

            const response = await fetch(route('admin.production-lines.destroy', { productionLine: productionLineId }), {
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
                    message: "Production line deleted successfully!",
                    variant: "success",
                    position: "bottom-right",
                });

                // Refresh the production lines list
                fetchProductionLines();
            } else {
                const errorData = await response.json().catch(() => ({}));
                const errorMessage = errorData.message || 'Error deleting production line. Please try again.';
                
                KTToast.show({
                    icon: `<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-info-icon lucide-info"><circle cx="12" cy="12" r="10"/><path d="M12 16v-4"/><path d="M12 8h.01"/></svg>`,
                    message: errorMessage,
                    variant: "destructive",
                    position: "bottom-right",
                });
            }
        } catch (error) {
            console.error('Error deleting production line:', error);
            
            KTToast.show({
                icon: `<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-info-icon lucide-info"><circle cx="12" cy="12" r="10"/><path d="M12 16v-4"/><path d="M12 8h.01"/></svg>`,
                message: "Network error. Please check your connection and try again.",
                variant: "destructive",
                position: "bottom-right",
            });
        }
    }

    onMount(() => {
        fetchProductionLines();
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
            <!-- Production Lines Header -->
            <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4">
                <div class="flex flex-col gap-1">
                    <h1 class="text-2xl font-bold text-mono">Production Lines Management</h1>
                    <p class="text-sm text-secondary-foreground">
                        Manage your production lines and manufacturing processes
                    </p>
                </div>
                <div class="flex items-center gap-3">
                    {#if hasPermission('admin.production-lines.store')}
                    <a href="{route('admin.production-lines.create')}" class="kt-btn kt-btn-primary">
                        <i class="ki-filled ki-plus text-base"></i>
                        Add New Production Line
                    </a>
                    {/if}
                </div>                      
            </div>

            <!-- Production Lines Table -->
            <div class="kt-card">
                <div class="kt-card-header">
                    <div class="kt-card-toolbar">
                        <div class="flex items-center gap-4">
                            <div class="kt-input max-w-64 w-64">
                                <i class="ki-filled ki-magnifier"></i>
                                <input 
                                    type="text" 
                                    class="kt-input" 
                                    placeholder="Search production lines..." 
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
                            {#if areaMin || areaMax || productFilter || stepsMin || stepsMax}
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
                            <!-- Area Range -->
                            <div class="space-y-2">
                                <h4 class="text-sm font-medium text-gray-700">Area Required (sq m)</h4>
                                
                                <div class="flex gap-2">
                                    <input 
                                        type="number" 
                                        class="kt-input flex-1" 
                                        placeholder="Min Area" 
                                        bind:value={areaMin}
                                        on:input={handleFilterChange}
                                        step="0.1"
                                        min="0"
                                    />
                                    <input 
                                        type="number" 
                                        class="kt-input flex-1" 
                                        placeholder="Max Area" 
                                        bind:value={areaMax}
                                        on:input={handleFilterChange}
                                        step="0.1"
                                        min="0"
                                    />
                                </div>
                            </div>

                            <!-- Steps Count Range -->
                            <div class="space-y-2">
                                <h4 class="text-sm font-medium text-gray-700">Number of Steps</h4>
                                
                                <div class="flex gap-2">
                                    <input 
                                        type="number" 
                                        class="kt-input flex-1" 
                                        placeholder="Min Steps" 
                                        bind:value={stepsMin}
                                        on:input={handleFilterChange}
                                        min="1"
                                    />
                                    <input 
                                        type="number" 
                                        class="kt-input flex-1" 
                                        placeholder="Max Steps" 
                                        bind:value={stepsMax}
                                        on:input={handleFilterChange}
                                        min="1"
                                    />
                                </div>
                            </div>

                            <!-- Product Filter -->
                            <div class="space-y-2">
                                <h4 class="text-sm font-medium text-gray-700">Product Output</h4>
                                <Select2
                                    bind:this={productSelectComponent}
                                    id="product-filter"
                                    placeholder="Search products..."
                                    bind:value={productFilter}
                                    on:select={handleProductSelect}
                                    on:clear={handleProductClear}
                                    ajax={{
                                        url: route('admin.products.index'),
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
                                                    id: product.id,
                                                    text: `${product.name}`,
                                                    name: product.name,
                                                    type: product.type,
                                                    type_name: product.type_name,
                                                    image_url: product.image_url
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
                                            <span class="kt-table-col-label">Production Line</span>
                                        </span>
                                    </th>
                                    <th class="min-w-[120px]">
                                        <span class="kt-table-col">
                                            <span class="kt-table-col-label">Area (sq m)</span>
                                        </span>
                                    </th>
                                    <th class="min-w-[100px]">
                                        <span class="kt-table-col">
                                            <span class="kt-table-col-label">Steps</span>
                                        </span>
                                    </th>
                                    <th class="min-w-[150px]">
                                        <span class="kt-table-col">
                                            <span class="kt-table-col-label">Products</span>
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
                                                <div class="flex flex-col gap-1">
                                                    <div class="kt-skeleton w-32 h-4 rounded"></div>
                                                    <div class="kt-skeleton w-20 h-3 rounded"></div>
                                                </div>
                                            </td>
                                            <td class="p-4">
                                                <div class="kt-skeleton w-16 h-4 rounded"></div>
                                            </td>
                                            <td class="p-4">
                                                <div class="kt-skeleton w-12 h-4 rounded"></div>
                                            </td>
                                            <td class="p-4">
                                                <div class="kt-skeleton w-20 h-6 rounded"></div>
                                            </td>
                                            <td class="p-4">
                                                <div class="kt-skeleton w-8 h-8 rounded"></div>
                                            </td>
                                        </tr>
                                    {/each}
                                {:else if productionLines.length === 0}
                                    <!-- Empty state -->
                                    <tr>
                                        <td colspan="7" class="p-10">
                                            <div class="flex flex-col items-center justify-center text-center">
                                                <div class="mb-4">
                                                    <i class="ki-filled ki-abstract text-4xl text-muted-foreground"></i>
                                                </div>
                                                <h3 class="text-lg font-semibold text-mono mb-2">No production lines found</h3>
                                                <p class="text-sm text-secondary-foreground mb-4">
                                                    {search ? 'No production lines match your search criteria.' : 'Get started by creating your first production line.'}
                                                </p>
                                                {#if hasPermission('admin.production-lines.store')}
                                                <a href="{route('admin.production-lines.create')}" class="kt-btn kt-btn-primary">
                                                    <i class="ki-filled ki-plus text-base"></i>
                                                    Create First Production Line
                                                </a>
                                                {/if}
                                            </div>
                                        </td>
                                    </tr>
                                {:else}
                                    <!-- Actual data rows -->
                                    {#each productionLines as productionLine}
                                        <tr class="hover:bg-muted/50">
                                            <td>
                                                <input class="kt-checkbox kt-checkbox-sm" type="checkbox" value={productionLine.id}/>
                                            </td>
                                            <td>
                                                <span class="text-sm font-medium text-mono">#{productionLine.id}</span>
                                            </td>
                                            <td>
                                                <div class="flex flex-col gap-1">
                                                    <span class="text-sm font-medium text-mono hover:text-primary">
                                                        {productionLine.name}
                                                    </span>
                                                    {#if productionLine.description}
                                                        <span class="text-xs text-muted-foreground">
                                                            {productionLine.description.slice(0, 50)}{productionLine.description.length > 50 ? '...' : ''}
                                                        </span>
                                                    {/if}
                                                </div>
                                            </td>
                                            <td>
                                                <span class="text-sm font-medium">{productionLine.area_required}</span>
                                            </td>
                                            <td>
                                                <span class="kt-badge kt-badge-outline kt-badge-primary kt-badge-sm">
                                                    {productionLine.steps?.length || 0} steps
                                                </span>
                                            </td>
                                            <td>
                                                <div class="flex flex-wrap gap-1">
                                                    {#each (productionLine.products || []).slice(0, 2) as product}
                                                        <span class="kt-badge kt-badge-outline kt-badge-success kt-badge-sm">
                                                            {product.name}
                                                        </span>
                                                    {/each}
                                                    {#if (productionLine.products || []).length > 2}
                                                        <span class="kt-badge kt-badge-outline kt-badge-secondary kt-badge-sm">
                                                            +{(productionLine.products || []).length - 2} more
                                                        </span>
                                                    {/if}
                                                </div>
                                            </td>
                                            <td class="text-center">
                                                <div class="kt-menu flex-inline" data-kt-menu="true">
                                                    <div class="kt-menu-item" data-kt-menu-item-offset="0, 10px" data-kt-menu-item-placement="bottom-end" data-kt-menu-item-placement-rtl="bottom-start" data-kt-menu-item-toggle="dropdown" data-kt-menu-item-trigger="click">
                                                        <button class="kt-menu-toggle kt-btn kt-btn-sm kt-btn-icon kt-btn-ghost">
                                                            <i class="ki-filled ki-dots-vertical text-lg"></i>
                                                        </button>
                                                        <div class="kt-menu-dropdown kt-menu-default w-full max-w-[175px]" data-kt-menu-dismiss="true">
                                                            {#if hasPermission('admin.production-lines.show')}
                                                            <div class="kt-menu-item">
                                                                <a class="kt-menu-link" href={route('admin.production-lines.show', { productionLine: productionLine.id })}>
                                                                    <span class="kt-menu-icon">
                                                                        <i class="ki-filled ki-search-list"></i>
                                                                    </span>
                                                                    <span class="kt-menu-title">View</span>
                                                                </a>
                                                            </div>
                                                            {/if}
                                                            {#if hasPermission('admin.production-lines.update')}
                                                            <div class="kt-menu-item">
                                                                <a class="kt-menu-link" href={route('admin.production-lines.edit', { productionLine: productionLine.id })}>
                                                                    <span class="kt-menu-icon">
                                                                        <i class="ki-filled ki-pencil"></i>
                                                                    </span>
                                                                    <span class="kt-menu-title">Edit</span>
                                                                </a>
                                                            </div>
                                                            {/if}
                                                            {#if hasPermission('admin.production-lines.destroy')}
                                                                <div class="kt-menu-separator"></div>
                                                                <div class="kt-menu-item">
                                                                    <button class="kt-menu-link" on:click={() => deleteProductionLine(productionLine.id)}>
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