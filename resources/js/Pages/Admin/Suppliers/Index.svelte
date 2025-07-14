<script>
    import AdminLayout from '../../Layouts/AdminLayout.svelte';
    import Pagination from '../../Components/Pagination.svelte';
    import Select2 from '../../Components/Forms/Select2.svelte';
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
    let showFilters = false;

    // Filter variables
    let isInternationalFilter = '';
    let needsResearchFilter = '';
    let countryIdFilter = '';
    let wilayaIdFilter = '';
    let minShippingCostFilter = '';
    let maxShippingCostFilter = '';
    let minShippingTimeFilter = '';
    let maxShippingTimeFilter = '';
    let carbonFootprintMinFilter = '';
    let carbonFootprintMaxFilter = '';

    // Select2 component references
    let countrySelectComponent;
    let wilayaSelectComponent;
    let productSelectComponent;

    // Supplier types options
    const supplierTypes = {
        'true': 'International',
        'false': 'Local'
    };

    // Research status options
    const researchStatus = {
        'true': 'Required',
        'false': 'Not Required'
    };

    // Fetch suppliers data
    async function fetchSuppliers() {
        loading = true;
        try {
            const params = new URLSearchParams({
                page: currentPage,
                perPage: perPage,
                search: search
            });

            // Add filters to params
            if (isInternationalFilter !== '') params.append('is_international', isInternationalFilter);
            if (needsResearchFilter !== '') params.append('needs_research', needsResearchFilter);
            if (countryIdFilter) params.append('country_id', countryIdFilter);
            if (wilayaIdFilter) params.append('wilaya_id', wilayaIdFilter);
            if (minShippingCostFilter) params.append('min_shipping_cost', minShippingCostFilter);
            if (maxShippingCostFilter) params.append('max_shipping_cost', maxShippingCostFilter);
            if (minShippingTimeFilter) params.append('min_shipping_time', minShippingTimeFilter);
            if (maxShippingTimeFilter) params.append('max_shipping_time', maxShippingTimeFilter);
            if (carbonFootprintMinFilter) params.append('carbon_footprint_min', carbonFootprintMinFilter);
            if (carbonFootprintMaxFilter) params.append('carbon_footprint_max', carbonFootprintMaxFilter);
            
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

    // Handle filter changes
    function handleFilterChange() {
        currentPage = 1;
        fetchSuppliers();
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

    // Clear all filters
    function clearAllFilters() {
        isInternationalFilter = '';
        needsResearchFilter = '';
        countryIdFilter = '';
        wilayaIdFilter = '';
        minShippingCostFilter = '';
        maxShippingCostFilter = '';
        minShippingTimeFilter = '';
        maxShippingTimeFilter = '';
        carbonFootprintMinFilter = '';
        carbonFootprintMaxFilter = '';
        if (countrySelectComponent) {
            countrySelectComponent.clear();
        }
        if (wilayaSelectComponent) {
            wilayaSelectComponent.clear();
        }
        if (productSelectComponent) {
            productSelectComponent.clear();
        }
        currentPage = 1;
        fetchSuppliers();
    }

    // Toggle filters visibility
    function toggleFilters() {
        showFilters = !showFilters;
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
                KTToast.show({
                    icon: `<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-info-icon lucide-info"><circle cx="12" cy="12" r="10"/><path d="M12 16v-4"/><path d="M12 8h.01"/></svg>`,
                    message: "Supplier deleted successfully!",
                    variant: "success",
                    position: "bottom-right",
                });

                // Refresh the suppliers list
                fetchSuppliers();
            } else {
                const errorData = await response.json().catch(() => ({}));
                const errorMessage = errorData.message || 'Error deleting supplier. Please try again.';
                
                KTToast.show({
                    icon: `<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-info-icon lucide-info"><circle cx="12" cy="12" r="10"/><path d="M12 16v-4"/><path d="M12 8h.01"/></svg>`,
                    message: errorMessage,
                    variant: "destructive",
                    position: "bottom-right",
                });
            }
        } catch (error) {
            console.error('Error deleting supplier:', error);
            
            KTToast.show({
                icon: `<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-info-icon lucide-info"><circle cx="12" cy="12" r="10"/><path d="M12 16v-4"/><path d="M12 8h.01"/></svg>`,
                message: "Network error. Please check your connection and try again.",
                variant: "destructive",
                position: "bottom-right",
            });
        }
    }

    // Handle country selection
    function handleCountrySelect(event) {
        countryIdFilter = event.detail.value;
        handleFilterChange();
    }

    // Handle wilaya selection
    function handleWilayaSelect(event) {
        wilayaIdFilter = event.detail.value;
        handleFilterChange();
    }

    // Get supplier type badge class
    function getSupplierTypeBadgeClass(isInternational) {
        return isInternational 
            ? 'kt-badge kt-badge-outline kt-badge-primary'
            : 'kt-badge kt-badge-outline kt-badge-success';
    }

    // Get research status badge class
    function getResearchStatusBadgeClass(needsResearch) {
        return needsResearch
            ? 'kt-badge kt-badge-outline kt-badge-warning'
            : 'kt-badge kt-badge-outline kt-badge-info';
    }

    onMount(() => {
        fetchSuppliers();
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
                            {#if isInternationalFilter || needsResearchFilter || countryIdFilter || wilayaIdFilter || minShippingCostFilter || maxShippingCostFilter || minShippingTimeFilter || maxShippingTimeFilter || carbonFootprintMinFilter || carbonFootprintMaxFilter}
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
                            <!-- Supplier Properties -->
                            <div class="space-y-2">
                                <h4 class="text-sm font-medium text-gray-700">Supplier Properties</h4>
                                <!-- Supplier Type -->
                                <select 
                                    class="kt-select w-full" 
                                    bind:value={isInternationalFilter}
                                    on:change={handleFilterChange}
                                >
                                    <option value="">All Types</option>
                                    <option value="true">International</option>
                                    <option value="false">Local</option>
                                </select>
                            </div>

                            <!-- Research Status -->
                            <div class="space-y-2">
                                <h4 class="text-sm font-medium text-gray-700">Research Required</h4>
                                <select 
                                    class="kt-select w-full" 
                                    bind:value={needsResearchFilter}
                                    on:change={handleFilterChange}
                                >
                                    <option value="">All Statuses</option>
                                    <option value="true">Required</option>
                                    <option value="false">Not Required</option>
                                </select>
                            </div>

                            <!-- Country -->
                            <div class="space-y-2">
                                <h4 class="text-sm font-medium text-gray-700">Country</h4>
                                <Select2
                                    bind:this={countrySelectComponent}
                                    id="country-filter"
                                    placeholder="All Countries"
                                    allowClear={true}
                                    on:select={handleCountrySelect}
                                    on:clear={() => {
                                        countryIdFilter = '';
                                        handleFilterChange();
                                    }}
                                    ajax={{
                                        url: route('admin.countries.index'),
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
                                                results: data.countries.map(country => ({
                                                    id: country.id,
                                                    text: country.name,
                                                    name: country.name,
                                                }))
                                            };
                                        },
                                        cache: true
                                    }}
                                    templateResult={function(data) {
                                        if (data.loading) return data.text;
                                        
                                        const $elem = globalThis.$('<div class="flex items-center gap-2">' +
                                            '<i class="ki-filled ki-globe text-sm text-muted-foreground"></i>' +
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
                                        url: route('admin.wilayas.index'),
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
                                            '<i class="ki-filled ki-map-pin text-sm text-muted-foreground"></i>' +
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

                            <!-- Shipping Cost Range -->
                            <div class="space-y-2">
                                <h4 class="text-sm font-medium text-gray-700">Shipping Cost Range</h4>
                                <div class="flex gap-2">
                                    <input 
                                        type="number" 
                                        class="kt-input flex-1" 
                                        placeholder="Min Cost" 
                                        bind:value={minShippingCostFilter}
                                        on:input={handleFilterChange}
                                        min="0"
                                        step="0.01"
                                    />
                                    <input 
                                        type="number" 
                                        class="kt-input flex-1" 
                                        placeholder="Max Cost" 
                                        bind:value={maxShippingCostFilter}
                                        on:input={handleFilterChange}
                                        min="0"
                                        step="0.01"
                                    />
                                </div>
                            </div>

                            <!-- Shipping Time Range -->
                            <div class="space-y-2">
                                <h4 class="text-sm font-medium text-gray-700">Shipping Time (Days)</h4>
                                <div class="flex gap-2">
                                    <input 
                                        type="number" 
                                        class="kt-input flex-1" 
                                        placeholder="Min Days" 
                                        bind:value={minShippingTimeFilter}
                                        on:input={handleFilterChange}
                                        min="1"
                                    />
                                    <input 
                                        type="number" 
                                        class="kt-input flex-1" 
                                        placeholder="Max Days" 
                                        bind:value={maxShippingTimeFilter}
                                        on:input={handleFilterChange}
                                        min="1"
                                    />
                                </div>
                            </div>

                            <!-- Carbon Footprint Range -->
                            <div class="space-y-2">
                                <h4 class="text-sm font-medium text-gray-700">Carbon Footprint</h4>
                                <div class="flex gap-2">
                                    <input 
                                        type="number" 
                                        class="kt-input flex-1" 
                                        placeholder="Min" 
                                        bind:value={carbonFootprintMinFilter}
                                        on:input={handleFilterChange}
                                        min="0"
                                        step="0.01"
                                    />
                                    <input 
                                        type="number" 
                                        class="kt-input flex-1" 
                                        placeholder="Max" 
                                        bind:value={carbonFootprintMaxFilter}
                                        on:input={handleFilterChange}
                                        min="0"
                                        step="0.01"
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
                                            <span class="kt-table-col-label">Supplier</span>
                                        </span>
                                    </th>
                                    <th class="min-w-[120px]">
                                        <span class="kt-table-col">
                                            <span class="kt-table-col-label">Type</span>
                                        </span>
                                    </th>
                                    <th class="min-w-[150px]">
                                        <span class="kt-table-col">
                                            <span class="kt-table-col-label">Location</span>
                                        </span>
                                    </th>
                                    <th class="min-w-[120px]">
                                        <span class="kt-table-col">
                                            <span class="kt-table-col-label">Research</span>
                                        </span>
                                    </th>
                                    <th class="min-w-[120px]">
                                        <span class="kt-table-col">
                                            <span class="kt-table-col-label">Shipping Cost</span>
                                        </span>
                                    </th>
                                    <th class="min-w-[120px]">
                                        <span class="kt-table-col">
                                            <span class="kt-table-col-label">Shipping Time</span>
                                        </span>
                                    </th>
                                    <th class="min-w-[100px]">
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
                                                <div class="kt-skeleton w-20 h-4 rounded"></div>
                                            </td>
                                            <td class="p-4">
                                                <div class="kt-skeleton w-24 h-4 rounded"></div>
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
                                                <div class="kt-skeleton w-12 h-4 rounded"></div>
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
                                                <input class="kt-checkbox kt-checkbox-sm" type="checkbox" value={supplier.id}/>
                                            </td>
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
                                                    {supplierTypes[supplier.is_international.toString()]}
                                                </span>
                                            </td>
                                            <td>
                                                <span class="text-sm text-secondary-foreground">
                                                    {supplier.is_international ? (supplier.country?.name || 'N/A') : (supplier.wilaya?.name || 'N/A')}
                                                </span>
                                            </td>
                                            <td>
                                                <span class={getResearchStatusBadgeClass(supplier.needs_research)}>
                                                    {researchStatus[supplier.needs_research.toString()]}
                                                </span>
                                            </td>
                                            <td>
                                                <div class="flex flex-col gap-1">
                                                    <div class="flex items-center gap-1">
                                                        <span class="text-xs text-muted-foreground">Min:</span>
                                                        <span class="text-xs font-medium">{supplier.min_shipping_cost}</span>
                                                    </div>
                                                    <div class="flex items-center gap-1">
                                                        <span class="text-xs text-muted-foreground">Avg:</span>
                                                        <span class="text-xs font-medium">{supplier.avg_shipping_cost}</span>
                                                    </div>
                                                    <div class="flex items-center gap-1">
                                                        <span class="text-xs text-muted-foreground">Max:</span>
                                                        <span class="text-xs font-medium">{supplier.max_shipping_cost}</span>
                                                    </div>
                                                </div>
                                            </td>
                                            <td>
                                                <div class="flex flex-col gap-1">
                                                    <div class="flex items-center gap-1">
                                                        <span class="text-xs text-muted-foreground">Min:</span>
                                                        <span class="text-xs font-medium">{supplier.min_shipping_time_days} days</span>
                                                    </div>
                                                    <div class="flex items-center gap-1">
                                                        <span class="text-xs text-muted-foreground">Avg:</span>
                                                        <span class="text-xs font-medium">{supplier.avg_shipping_time_days} days</span>
                                                    </div>
                                                    <div class="flex items-center gap-1">
                                                        <span class="text-xs text-muted-foreground">Max:</span>
                                                        <span class="text-xs font-medium">{supplier.max_shipping_time_days} days</span>
                                                    </div>
                                                </div>
                                            </td>
                                            <td>
                                                <span class="kt-badge kt-badge-outline kt-badge-info">
                                                    {supplier.products?.length || 0}
                                                </span>
                                            </td>
                                            <td class="text-center">
                                                <div class="kt-menu flex-inline" data-kt-menu="true">
                                                    <div class="kt-menu-item" data-kt-menu-item-offset="0, 10px" data-kt-menu-item-placement="bottom-end" data-kt-menu-item-placement-rtl="bottom-start" data-kt-menu-item-toggle="dropdown" data-kt-menu-item-trigger="click">
                                                        <button class="kt-menu-toggle kt-btn kt-btn-sm kt-btn-icon kt-btn-ghost">
                                                            <i class="ki-filled ki-dots-vertical text-lg"></i>
                                                        </button>
                                                        <div class="kt-menu-dropdown kt-menu-default w-full max-w-[175px]" data-kt-menu-dismiss="true">
                                                            {#if hasPermission('admin.suppliers.show')}
                                                            <div class="kt-menu-item">
                                                                <a class="kt-menu-link" href={route('admin.suppliers.show', { supplier: supplier.id })}>
                                                                    <span class="kt-menu-icon">
                                                                        <i class="ki-filled ki-search-list"></i>
                                                                    </span>
                                                                    <span class="kt-menu-title">View</span>
                                                                </a>
                                                            </div>
                                                            {/if}
                                                            {#if hasPermission('admin.suppliers.update')}
                                                            <div class="kt-menu-item">
                                                                <a class="kt-menu-link" href={route('admin.suppliers.edit', { supplier: supplier.id })}>
                                                                    <span class="kt-menu-icon">
                                                                        <i class="ki-filled ki-pencil"></i>
                                                                    </span>
                                                                    <span class="kt-menu-title">Edit</span>
                                                                </a>
                                                            </div>
                                                            {/if}
                                                            {#if hasPermission('admin.suppliers.destroy')}
                                                                <div class="kt-menu-separator"></div>
                                                                <div class="kt-menu-item">
                                                                    <button class="kt-menu-link" on:click={() => deleteSupplier(supplier.id)}>
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
