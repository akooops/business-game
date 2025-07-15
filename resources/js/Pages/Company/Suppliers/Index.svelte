<script>
    import CompanyLayout from '../../Layouts/CompanyLayout.svelte';
    import Pagination from '../../Components/Pagination.svelte';
    import Select2 from '../../Components/Forms/Select2.svelte';
    import { onMount, tick } from 'svelte';
    import { page } from '@inertiajs/svelte'

    // Define breadcrumbs for this page
    const breadcrumbs = [
        {
            title: 'Suppliers',
            url: route('company.suppliers.index'),
            active: false
        },
        {
            title: 'Index',
            url: route('company.suppliers.index'),
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
    let countryIdFilter = '';
    let wilayaIdFilter = '';

    // Select2 component references
    let countrySelectComponent;
    let wilayaSelectComponent;

    // Drawer state
    let selectedSupplier = null;
    let showSupplierDrawer = false;

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
            if (countryIdFilter) params.append('country_id', countryIdFilter);
            if (wilayaIdFilter) params.append('wilaya_id', wilayaIdFilter);
            
            const response = await fetch(route('company.suppliers.index') + '?' + params.toString(), {
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
        countryIdFilter = '';
        wilayaIdFilter = '';
        if (countrySelectComponent) {
            countrySelectComponent.clear();
        }
        if (wilayaSelectComponent) {
            wilayaSelectComponent.clear();
        }
        currentPage = 1;
        fetchSuppliers();
    }

    // Toggle filters visibility
    function toggleFilters() {
        showFilters = !showFilters;
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

    // Open supplier drawer
    function openSupplierDrawer(supplier) {
        selectedSupplier = supplier;
        showSupplierDrawer = true;
        
        // Show drawer
        const toggleButton = document.querySelector('[data-kt-drawer-toggle="#supplier_drawer"]');
        if (toggleButton) {
            toggleButton.click();
        }
    }

    // Close supplier drawer
    function closeSupplierDrawer() {
        showSupplierDrawer = false;
        selectedSupplier = null;
    }

    // Format timestamp
    function formatTimestamp(timestamp) {
        if (!timestamp) return 'N/A';
        return new Date(timestamp).toLocaleString();
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
    <title>Business game - {pageTitle}</title>
</svelte:head>

<CompanyLayout {breadcrumbs} {pageTitle}>
    <!-- Container -->
    <div class="kt-container-fluid">
        <div class="grid gap-5 lg:gap-7.5">
            <!-- Suppliers Header -->
            <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4">
                <div class="flex flex-col gap-1">
                    <h1 class="text-2xl font-bold text-mono">Suppliers</h1>
                    <p class="text-sm text-secondary-foreground">
                        Browse available suppliers in the market
                    </p>
                </div>                      
            </div>

            <!-- Suppliers Grid -->
            <div class="kt-card">
                <div class="kt-card-header">
                    <div class="kt-card-toolbar">
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
                            {#if isInternationalFilter || countryIdFilter || wilayaIdFilter}
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
                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
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
                                        url: route('company.countries.index'),
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
                        </div>
                    </div>
                {/if}

                <div class="kt-card-content p-0">
                    {#if loading}
                        <!-- Loading skeleton -->
                        <div class="p-6">
                            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6 p-4">
                                {#each Array(perPage) as _, i}
                                    <div class="kt-card animate-pulse">
                                        <div class="kt-card-content flex flex-col items-center lg:pt-10">
                                            <div class="mb-3">
                                                <div class="size-20 relative">
                                                    <div class="kt-skeleton w-20 h-20 rounded-full"></div>
                                                    <div class="kt-skeleton w-2.5 h-2.5 rounded-full ring-2 ring-background absolute bottom-0.5 start-16 transform -translate-y-1/2"></div>
                                                </div>
                                            </div>
                                            <div class="flex items-center justify-center gap-1.5 mb-3">
                                                <div class="kt-skeleton h-5 w-24"></div>
                                            </div>
                                            <div class="kt-skeleton h-3 w-32 mb-4"></div>
                                            <div class="flex items-center gap-2.5">
                                                <div class="kt-skeleton w-6 h-6 rounded"></div>
                                                <div class="kt-skeleton w-6 h-6 rounded"></div>
                                                <div class="kt-skeleton w-6 h-6 rounded"></div>
                                            </div>
                                        </div>
                                    </div>
                                {/each}
                            </div>
                        </div>
                    {:else if suppliers.length === 0}
                        <!-- Empty state -->
                        <div class="p-10">
                            <div class="flex flex-col items-center justify-center text-center">
                                <div class="mb-4">
                                    <i class="ki-filled ki-ship text-4xl text-muted-foreground"></i>
                                </div>
                                <h3 class="text-lg font-semibold text-mono mb-2">No suppliers found</h3>
                                <p class="text-sm text-secondary-foreground mb-4">
                                    {search ? 'No suppliers match your search criteria.' : 'No suppliers available in the market.'}
                                </p>
                            </div>
                        </div>
                    {:else}
                        <!-- Suppliers Grid -->
                        <div class="p-6">
                            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6 p-4">
                                {#each suppliers as supplier}
                                    <div class="kt-card kt-card-hover cursor-pointer" on:click={() => openSupplierDrawer(supplier)}>
                                        <div class="kt-card-content flex flex-col items-center lg:pt-10">
                                            <div class="mb-3">
                                                <div class="size-20 relative">
                                                    {#if supplier.image_url}
                                                        <img 
                                                            class="rounded-full w-20 h-20 object-cover" 
                                                            src={supplier.image_url}
                                                            alt={supplier.name}
                                                        />
                                                    {:else}
                                                        <div class="w-20 h-20 rounded-full bg-accent/50 flex items-center justify-center">
                                                            <i class="ki-filled ki-supplier-1 text-2xl text-muted-foreground"></i>
                                                        </div>
                                                    {/if}
                                                </div>
                                            </div>
                                            <div class="flex items-center justify-center gap-1.5 mb-3">
                                                <a class="hover:text-primary text-base leading-5 font-medium text-mono" href="#">
                                                    {supplier.name}
                                                </a>
                                            </div>


                                            <div class="flex items-center gap-2.5">
                                                <span class="kt-badge kt-badge-{supplier.is_international ? 'warning' : 'info'} kt-badge-sm">
                                                    {supplier.is_international ? 'International' : 'Local'}
                                                </span>

                                                <span>
                                                    - 
                                                </span>

                                                <span class="text-secondary-foreground text-sm">
                                                    
                                                    <i class="ki-filled ki-map"></i>
                                                    {supplier.country ? supplier.country.name : supplier.wilaya ? supplier.wilaya.name : 'Unknown Location'}
                                                </span>
                                            </div>

                                            <span class="text-secondary-foreground text-sm mb-4">
                                                {supplier.products.length} products
                                            </span>

                                            {#if supplier.country}
                                                <div class="flex flex-col gap-1 text-xs text-secondary-foreground">
                                                    <div class="flex justify-center gap-1">
                                                        {#if supplier.country.allows_imports}
                                                            <i class="ki-filled ki-check text-green-500"></i>
                                                            <span>Imports Allowed</span>
                                                        {:else}
                                                            <i class="ki-filled ki-cross text-destructive"></i>
                                                            <span>Imports Blocked</span>
                                                        {/if}
                                                    </div>
                                                    <div class="flex justify-center gap-1">
                                                        <i class="ki-filled ki-dollar text-orange-500"></i>
                                                        <span>Custom Duties: {supplier.country.customs_duties_rate * 100}%</span>
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
    <button style="display:none" data-kt-drawer-toggle="#supplier_drawer"></button>

    <!-- Supplier Details Drawer -->
    <div class="hidden kt-drawer kt-drawer-end card flex-col max-w-[90%] w-[450px] top-5 bottom-5 end-5 rounded-xl border border-border" data-kt-drawer="true" data-kt-drawer-container="body" id="supplier_drawer">
        <div class="flex items-center justify-between gap-2.5 text-sm text-mono font-semibold px-5 py-2.5 border-b border-b-border">
            Supplier Details
            <button class="kt-btn kt-btn-sm kt-btn-icon kt-btn-dim shrink-0" data-kt-drawer-dismiss="true" on:click={closeSupplierDrawer}>
                <i class="ki-filled ki-cross"></i>
            </button>
        </div>
        
        <div class="kt-card-content flex flex-col space-y-3 p-5 kt-scrollable-y-auto">
            {#if selectedSupplier}
                <!-- Supplier Image -->
                <div class="kt-card relative items-center justify-center bg-accent/50 mb-6.5 h-[180px] shadow-none">
                    {#if selectedSupplier.image_url}
                        <img 
                            src={selectedSupplier.image_url} 
                            alt={selectedSupplier.name}
                            class="h-[180px] w-full object-cover rounded-sm"
                        />
                    {:else}
                        <div class="flex items-center justify-center h-full">
                            <i class="ki-filled ki-shop text-4xl text-muted-foreground"></i>
                        </div>
                    {/if}
                </div>

                <!-- Supplier Name -->
                <span class="text-base font-medium text-mono">
                    {selectedSupplier.name}
                </span>

                <!-- Supplier Details -->
                <div class="flex flex-col gap-2.5">
                    <div class="flex items-center gap-2.5">
                        <span class="text-xs font-normal text-foreground min-w-14 xl:min-w-24 shrink-0">
                            Type
                        </span>
                        <div>
                            <span class="kt-badge kt-badge-{selectedSupplier.is_international ? 'warning' : 'info'} kt-badge-sm">
                                {selectedSupplier.is_international ? 'International' : 'Local'}
                            </span>
                        </div>
                    </div>
                    <div class="flex items-center gap-2.5">
                        <span class="text-xs font-normal text-foreground min-w-14 xl:min-w-24 shrink-0">
                            Location
                        </span>
                        <div>
                            <span class="text-xs font-medium text-foreground">
                                {selectedSupplier.country ? selectedSupplier.country.name : selectedSupplier.wilaya ? selectedSupplier.wilaya.name : 'Unknown'}
                            </span>
                        </div>
                    </div>

                    {#if selectedSupplier.country}
                        <div class="flex items-center gap-2.5">
                            <span class="text-xs font-normal text-foreground min-w-14 xl:min-w-24 shrink-0">
                                Allow Imports
                            </span>
                            <div>
                                {#if selectedSupplier.country.allows_imports}
                                    <span class="text-xs font-medium text-green-500">
                                        <i class="ki-filled ki-check text-sm"></i>
                                        Yes
                                    </span>
                                {:else}
                                    <span class="text-xs font-medium text-destructive">
                                        <i class="ki-filled ki-cross text-sm"></i>
                                        No
                                    </span>
                                {/if}
                            </div>
                        </div>
                        
                        <div class="flex items-center gap-2.5">
                            <span class="text-xs font-normal text-foreground min-w-14 xl:min-w-24 shrink-0">
                                Custom Duties
                            </span>
                            <div>
                                <span class="text-xs font-medium text-foreground">
                                    {selectedSupplier.country.customs_duties_rate * 100}%
                                </span>
                            </div>
                        </div>
                    {/if}
                </div>

                <!-- Products Section -->
                {#if selectedSupplier.products && selectedSupplier.products.length > 0}
                    <div class="border-t border-border pt-4">
                        <h3 class="text-sm font-semibold text-mono mb-3">Available Products</h3>
                        <div class="space-y-3">
                            {#each selectedSupplier.products as product}
                                {#if product.is_researched}
                                    <div class="kt-card">
                                        <div class="kt-card-body p-3">
                                            <div class="flex items-center gap-3">
                                                <div class="flex-shrink-0 relative">
                                                    {#if product.image_url}
                                                        <img 
                                                            src={product.image_url} 
                                                            alt={product.name}
                                                            class="w-12 h-12 rounded-lg object-cover"
                                                        />
                                                    {:else}
                                                        <div class="w-12 h-12 rounded-lg bg-accent/50 flex items-center justify-center">
                                                            <i class="ki-filled ki-abstract-26 text-lg text-muted-foreground"></i>
                                                        </div>
                                                    {/if}
                                                </div>
                                                <div class="flex-1 min-w-0">
                                                    <h4 class="text-sm font-semibold text-mono mb-1 truncate">{product.name}</h4>
                                                    <p class="text-xs text-muted-foreground mb-1">{product.type_name}</p>
                                                    <span class="text-xs text-green-500 font-medium">Researched</span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                {:else}
                                    <div class="kt-card">
                                        <div class="kt-card-body p-3">
                                            <div class="flex items-center gap-3">
                                                <div class="flex-shrink-0 relative">
                                                    <div class="w-12 h-12 rounded-lg bg-muted animate-pulse"></div>
                                                    <div class="absolute inset-0 flex items-center justify-center" style="top: 50%; left: 50%; bottom: 50%; right: 50%;">
                                                        <i class="ki-filled ki-lock text-muted-foreground text-sm"></i>
                                                    </div>
                                                </div>
                                                <div class="flex-1 min-w-0">
                                                    <div class="kt-skeleton h-4 w-24 mb-1"></div>
                                                    <div class="kt-skeleton h-3 w-16 mb-1"></div>
                                                    <div class="flex items-center gap-1">
                                                        <i class="ki-filled ki-search text-orange-500 text-xs"></i>
                                                        <span class="text-xs text-orange-500 font-medium">Need Research to Unlock</span>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                {/if}
                            {/each}
                        </div>
                    </div>
                {:else}
                    <div class="border-t border-border pt-4">
                        <h3 class="text-sm font-semibold text-mono mb-3">Available Products</h3>
                        <div class="kt-card">
                            <div class="kt-card-body p-4">
                                <div class="flex flex-col items-center justify-center text-center">
                                    <i class="ki-filled ki-abstract-26 text-2xl text-muted-foreground mb-2"></i>
                                    <p class="text-xs text-muted-foreground">No products available</p>
                                </div>
                            </div>
                        </div>
                    </div>
                {/if}
            {/if}
        </div>
    </div>
</CompanyLayout> 