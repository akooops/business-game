<script>
    import AdminLayout from '../../Layouts/AdminLayout.svelte';
    import Pagination from '../../Components/Pagination.svelte';
    import { onMount, tick } from 'svelte';
    import { page } from '@inertiajs/svelte'

    // Define breadcrumbs for this page
    const breadcrumbs = [
        {
            title: 'Countries',
            url: route('admin.countries.index'),
            active: false
        },
        {
            title: 'Index',
            url: route('admin.countries.index'),
            active: true
        }
    ];
    
    const pageTitle = 'Countries';

    // Reactive variables
    let countries = [];
    let pagination = {};
    let loading = true;
    let search = '';
    let perPage = 10;
    let currentPage = 1;
    let searchTimeout;
    let showFilters = false;

    // Filter variables
    let codeFilter = '';
    let allowsImportsFilter = '';
    let customsDutiesMin = '';
    let customsDutiesMax = '';
    let tvaMin = '';
    let tvaMax = '';
    let insuranceMin = '';
    let insuranceMax = '';
    let freightMin = '';
    let freightMax = '';
    let handlingMin = '';
    let handlingMax = '';
    let shippingCostMin = '';
    let shippingCostMax = '';
    let shippingTimeMin = '';
    let shippingTimeMax = '';

    // Import status badge colors
    function getImportStatusBadgeClass(allowsImports) {
        return allowsImports 
            ? 'kt-badge kt-badge-outline kt-badge-success kt-badge-sm'
            : 'kt-badge kt-badge-outline kt-badge-danger kt-badge-sm';
    }

    // Format percentage for display
    function formatPercentage(value) {
        return (value * 100).toFixed(2) + '%';
    }

    // Format currency
    function formatCurrency(value) {
        return new Intl.NumberFormat('en-US', {
            style: 'currency',
            currency: 'DZD'
        }).format(value);
    }

    // Fetch countries data
    async function fetchCountries() {
        loading = true;
        try {
            const params = new URLSearchParams({
                page: currentPage,
                perPage: perPage,
                search: search
            });
            
            // Add filter parameters
            if (codeFilter) {
                params.append('code', codeFilter);
            }
            if (allowsImportsFilter) {
                params.append('allows_imports', allowsImportsFilter);
            }
            if (customsDutiesMin) {
                params.append('customs_duties_min', customsDutiesMin);
            }
            if (customsDutiesMax) {
                params.append('customs_duties_max', customsDutiesMax);
            }
            if (tvaMin) {
                params.append('tva_min', tvaMin);
            }
            if (tvaMax) {
                params.append('tva_max', tvaMax);
            }
            if (insuranceMin) {
                params.append('insurance_min', insuranceMin);
            }
            if (insuranceMax) {
                params.append('insurance_max', insuranceMax);
            }
            if (freightMin) {
                params.append('freight_min', freightMin);
            }
            if (freightMax) {
                params.append('freight_max', freightMax);
            }
            if (handlingMin) {
                params.append('handling_min', handlingMin);
            }
            if (handlingMax) {
                params.append('handling_max', handlingMax);
            }
            if (shippingCostMin) {
                params.append('min_shipping_cost', shippingCostMin);
            }
            if (shippingCostMax) {
                params.append('max_shipping_cost', shippingCostMax);
            }
            if (shippingTimeMin) {
                params.append('min_shipping_time_days', shippingTimeMin);
            }
            if (shippingTimeMax) {
                params.append('max_shipping_time_days', shippingTimeMax);
            }
            
            const response = await fetch(route('admin.countries.index') + '?' + params.toString(), {
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                }
            });
            
            const data = await response.json();
            countries = data.countries;
            pagination = data.pagination;
            
            // Wait for DOM to update, then initialize menus
            await tick();
            if (window.KTMenu) {
                window.KTMenu.init();
            }
        } catch (error) {
            console.error('Error fetching countries:', error);
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
            fetchCountries();
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
            fetchCountries();
        }
    }

    // Handle per page change
    function handlePerPageChange(newPerPage) {
        perPage = newPerPage;
        currentPage = 1;
        fetchCountries();
    }

    // Handle filter changes
    function handleFilterChange() {
        currentPage = 1;
        fetchCountries();
    }

    // Clear all filters
    function clearAllFilters() {
        codeFilter = '';
        allowsImportsFilter = '';
        customsDutiesMin = '';
        customsDutiesMax = '';
        tvaMin = '';
        tvaMax = '';
        insuranceMin = '';
        insuranceMax = '';
        freightMin = '';
        freightMax = '';
        handlingMin = '';
        handlingMax = '';
        shippingCostMin = '';
        shippingCostMax = '';
        shippingTimeMin = '';
        shippingTimeMax = '';
        currentPage = 1;
        fetchCountries();
    }

    // Toggle filters visibility
    function toggleFilters() {
        showFilters = !showFilters;
    }

    // Delete country
    async function deleteCountry(countryId) {
        if (!confirm('Are you sure you want to delete this country? This action cannot be undone.')) {
            return;
        }

        try {
            const formData = new FormData();
            formData.append('_method', 'DELETE');
            formData.append('_token', document.querySelector('meta[name="csrf-token"]')?.getAttribute('content'));

            const response = await fetch(route('admin.countries.destroy', { country: countryId }), {
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
                    message: "Country deleted successfully!",
                    variant: "success",
                    position: "bottom-right",
                });

                // Refresh the countries list
                fetchCountries();
            } else {
                const errorData = await response.json().catch(() => ({}));
                const errorMessage = errorData.message || 'Error deleting country. Please try again.';
                
                KTToast.show({
                    icon: `<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-info-icon lucide-info"><circle cx="12" cy="12" r="10"/><path d="M12 16v-4"/><path d="M12 8h.01"/></svg>`,
                    message: errorMessage,
                    variant: "destructive",
                    position: "bottom-right",
                });
            }
        } catch (error) {
            console.error('Error deleting country:', error);
            
            KTToast.show({
                    icon: `<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-info-icon lucide-info"><circle cx="12" cy="12" r="10"/><path d="M12 16v-4"/><path d="M12 8h.01"/></svg>`,
                    message: "Network error. Please check your connection and try again.",
                    variant: "destructive",
                    position: "bottom-right",
            });
        }
    }

    onMount(() => {
        fetchCountries();
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
            <!-- Country Header -->
            <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4">
                <div class="flex flex-col gap-1">
                    <h1 class="text-2xl font-bold text-mono">Countries Management</h1>
                    <p class="text-sm text-secondary-foreground">
                        Manage import/export countries and their tax rates
                    </p>
                </div>
                <div class="flex items-center gap-3">
                    {#if hasPermission('admin.countries.store')}
                    <a href="{route('admin.countries.create')}" class="kt-btn kt-btn-primary">
                        <i class="ki-filled ki-plus text-base"></i>
                        Add New Country
                    </a>
                    {/if}
                </div>                      
            </div>

            <!-- Countries Table -->
            <div class="kt-card">
                <div class="kt-card-header">
                    <div class="kt-card-toolbar">
                        <div class="flex items-center gap-4">
                            <div class="kt-input max-w-64 w-64">
                                <i class="ki-filled ki-magnifier"></i>
                                <input 
                                    type="text" 
                                    class="kt-input" 
                                    placeholder="Search countries..." 
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
                            {#if codeFilter || allowsImportsFilter || customsDutiesMin || customsDutiesMax || tvaMin || tvaMax || insuranceMin || insuranceMax || freightMin || freightMax || handlingMin || handlingMax}
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
                            <!-- Country Code -->
                            <div class="space-y-2">
                                <h4 class="text-sm font-medium text-gray-700">Country Code</h4>                                    
                                <input 
                                    type="text" 
                                    class="kt-input w-full" 
                                    placeholder="e.g., USA, CHN" 
                                    bind:value={codeFilter}
                                    on:input={handleFilterChange}
                                />
                            </div>

                            <!-- Allows Imports -->
                            <div class="space-y-2">
                                <h4 class="text-sm font-medium text-gray-700">Allows Imports</h4>                                    
                                <select 
                                    class="kt-select w-full" 
                                    bind:value={allowsImportsFilter}
                                    on:change={handleFilterChange}
                                >
                                    <option value="">All Countries</option>
                                    <option value="true">Yes</option>
                                    <option value="false">No</option>
                                </select>
                            </div>                     
                            
                            <!-- Customs Duties Range -->
                            <div class="space-y-2">
                                <h4 class="text-sm font-medium text-gray-700">Customs Duties (%)</h4>
                                
                                <div class="flex gap-2">
                                    <input 
                                        type="number" 
                                        class="kt-input flex-1" 
                                        placeholder="Min" 
                                        bind:value={customsDutiesMin}
                                        on:input={handleFilterChange}
                                        step="0.01"
                                        min="0"
                                    />
                                    <input 
                                        type="number" 
                                        class="kt-input flex-1" 
                                        placeholder="Max" 
                                        bind:value={customsDutiesMax}
                                        on:input={handleFilterChange}
                                        step="0.01"
                                        min="0"
                                    />
                                </div>
                            </div>

                            <!-- TVA Range -->
                            <div class="space-y-2">
                                <h4 class="text-sm font-medium text-gray-700">TVA Rate (%)</h4>
                                
                                <div class="flex gap-2">
                                    <input 
                                        type="number" 
                                        class="kt-input flex-1" 
                                        placeholder="Min" 
                                        bind:value={tvaMin}
                                        on:input={handleFilterChange}
                                        step="0.01"
                                        min="0"
                                    />
                                    <input 
                                        type="number" 
                                        class="kt-input flex-1" 
                                        placeholder="Max" 
                                        bind:value={tvaMax}
                                        on:input={handleFilterChange}
                                        step="0.01"
                                        min="0"
                                    />
                                </div>
                            </div>

                            <!-- Insurance Range -->
                            <div class="space-y-2">
                                <h4 class="text-sm font-medium text-gray-700">Insurance Rate (%)</h4>
                                
                                <div class="flex gap-2">
                                    <input 
                                        type="number" 
                                        class="kt-input flex-1" 
                                        placeholder="Min" 
                                        bind:value={insuranceMin}
                                        on:input={handleFilterChange}
                                        step="0.01"
                                        min="0"
                                    />
                                    <input 
                                        type="number" 
                                        class="kt-input flex-1" 
                                        placeholder="Max" 
                                        bind:value={insuranceMax}
                                        on:input={handleFilterChange}
                                        step="0.01"
                                        min="0"
                                    />
                                </div>
                            </div>

                            <!-- Freight Cost Range -->
                            <div class="space-y-2">
                                <h4 class="text-sm font-medium text-gray-700">Freight Cost (DZD)</h4>
                                
                                <div class="flex gap-2">
                                    <input 
                                        type="number" 
                                        class="kt-input flex-1" 
                                        placeholder="Min" 
                                        bind:value={freightMin}
                                        on:input={handleFilterChange}
                                        step="0.01"
                                        min="0"
                                    />
                                    <input 
                                        type="number" 
                                        class="kt-input flex-1" 
                                        placeholder="Max" 
                                        bind:value={freightMax}
                                        on:input={handleFilterChange}
                                        step="0.01"
                                        min="0"
                                    />
                                </div>
                            </div>

                            <!-- Port Handling Range -->
                            <div class="space-y-2">
                                <h4 class="text-sm font-medium text-gray-700">Port Handling (DZD)</h4>
                                
                                <div class="flex gap-2">
                                    <input 
                                        type="number" 
                                        class="kt-input flex-1" 
                                        placeholder="Min" 
                                        bind:value={handlingMin}
                                        on:input={handleFilterChange}
                                        step="0.01"
                                        min="0"
                                    />
                                    <input 
                                        type="number" 
                                        class="kt-input flex-1" 
                                        placeholder="Max" 
                                        bind:value={handlingMax}
                                        on:input={handleFilterChange}
                                        step="0.01"
                                        min="0"
                                    />
                                </div>
                            </div>

                            <!-- Shipping Cost Range -->
                            <div class="space-y-2">
                                <h4 class="text-sm font-medium text-gray-700">Avg Shipping Cost (DZD)</h4>
                                
                                <div class="flex gap-2">
                                    <input 
                                        type="number" 
                                        class="kt-input flex-1" 
                                        placeholder="Min" 
                                        bind:value={shippingCostMin}
                                        on:input={handleFilterChange}
                                        step="0.01"
                                        min="0"
                                    />
                                    <input 
                                        type="number" 
                                        class="kt-input flex-1" 
                                        placeholder="Max" 
                                        bind:value={shippingCostMax}
                                        on:input={handleFilterChange}
                                        step="0.01"
                                        min="0"
                                    />
                                </div>
                            </div>

                            <!-- Shipping Time Range -->
                            <div class="space-y-2">
                                <h4 class="text-sm font-medium text-gray-700">Avg Shipping Time (Days)</h4>
                                
                                <div class="flex gap-2">
                                    <input 
                                        type="number" 
                                        class="kt-input flex-1" 
                                        placeholder="Min" 
                                        bind:value={shippingTimeMin}
                                        on:input={handleFilterChange}
                                        step="1"
                                        min="0"
                                    />
                                    <input 
                                        type="number" 
                                        class="kt-input flex-1" 
                                        placeholder="Max" 
                                        bind:value={shippingTimeMax}
                                        on:input={handleFilterChange}
                                        step="1"
                                        min="0"
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
                                            <span class="kt-table-col-label">Country</span>
                                        </span>
                                    </th>
                                    <th class="min-w-[120px]">
                                        <span class="kt-table-col">
                                            <span class="kt-table-col-label">Code</span>
                                        </span>
                                    </th>
                                    <th class="min-w-[120px]">
                                        <span class="kt-table-col">
                                            <span class="kt-table-col-label">Customs</span>
                                        </span>
                                    </th>
                                    <th class="min-w-[100px]">
                                        <span class="kt-table-col">
                                            <span class="kt-table-col-label">TVA</span>
                                        </span>
                                    </th>
                                    <th class="min-w-[120px]">
                                        <span class="kt-table-col">
                                            <span class="kt-table-col-label">Imports</span>
                                        </span>
                                    </th>
                                    <th class="min-w-[140px]">
                                        <span class="kt-table-col">
                                            <span class="kt-table-col-label">Shipping Cost</span>
                                        </span>
                                    </th>
                                    <th class="min-w-[120px]">
                                        <span class="kt-table-col">
                                            <span class="kt-table-col-label">Shipping Time</span>
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
                                                    <div class="kt-skeleton w-10 h-6 rounded"></div>
                                                    <div class="flex flex-col gap-1">
                                                        <div class="kt-skeleton w-24 h-4 rounded"></div>
                                                    </div>
                                                </div>
                                            </td>
                                            <td class="p-4">
                                                <div class="kt-skeleton w-12 h-6 rounded"></div>
                                            </td>
                                            <td class="p-4">
                                                <div class="kt-skeleton w-16 h-4 rounded"></div>
                                            </td>
                                            <td class="p-4">
                                                <div class="kt-skeleton w-16 h-4 rounded"></div>
                                            </td>
                                            <td class="p-4">
                                                <div class="kt-skeleton w-12 h-6 rounded"></div>
                                            </td>
                                            <td class="p-4">
                                                <div class="flex flex-col gap-1">
                                                    <div class="kt-skeleton w-16 h-3 rounded"></div>
                                                    <div class="kt-skeleton w-16 h-3 rounded"></div>
                                                    <div class="kt-skeleton w-16 h-3 rounded"></div>
                                                </div>
                                            </td>
                                            <td class="p-4">
                                                <div class="flex flex-col gap-1">
                                                    <div class="kt-skeleton w-8 h-3 rounded"></div>
                                                    <div class="kt-skeleton w-8 h-3 rounded"></div>
                                                    <div class="kt-skeleton w-8 h-3 rounded"></div>
                                                </div>
                                            </td>
                                            <td class="p-4">
                                                <div class="kt-skeleton w-8 h-8 rounded"></div>
                                            </td>
                                        </tr>
                                    {/each}
                                {:else if countries.length === 0}
                                    <!-- Empty state -->
                                    <tr>
                                        <td colspan="10" class="p-10">
                                            <div class="flex flex-col items-center justify-center text-center">
                                                <div class="mb-4">
                                                    <i class="ki-filled ki-flag text-4xl text-muted-foreground"></i>
                                                </div>
                                                <h3 class="text-lg font-semibold text-mono mb-2">No countries found</h3>
                                                <p class="text-sm text-secondary-foreground mb-4">
                                                    {search ? 'No countries match your search criteria.' : 'Get started by creating your first country.'}
                                                </p>
                                                {#if hasPermission('admin.countries.store')}
                                                <a href="{route('admin.countries.create')}" class="kt-btn kt-btn-primary">
                                                    <i class="ki-filled ki-plus text-base"></i>
                                                    Create First Country
                                                </a>
                                                {/if}
                                            </div>
                                        </td>
                                    </tr>
                                {:else}
                                    <!-- Actual data rows -->
                                    {#each countries as country}
                                        <tr class="hover:bg-muted/50">
                                            <td>
                                                <input class="kt-checkbox kt-checkbox-sm" type="checkbox" value={country.id}/>
                                            </td>
                                            <td>
                                                <span class="text-sm font-medium text-mono">#{country.id}</span>
                                            </td>
                                            <td>
                                                <div class="flex items-center gap-3">
                                                    <div class="flex-shrink-0">
                                                        <div class="w-10 h-10 rounded border overflow-hidden">
                                                            <img 
                                                                src={country.flag_url} 
                                                                alt={country.name + " flag"}
                                                                class="w-full h-full object-cover"
                                                            />
                                                        </div>
                                                    </div>
                                                    <div class="flex flex-col gap-1">
                                                        <span class="text-sm font-medium text-mono hover:text-primary">
                                                            {country.name}
                                                        </span>
                                                    </div>
                                                </div>
                                            </td>

                                            <td>
                                                <span class="kt-badge kt-badge-outline kt-badge-primary kt-badge-sm">
                                                    {country.code}
                                                </span>
                                            </td>
                                            <td>
                                                <span class="text-sm">{formatPercentage(country.customs_duties_rate)}</span>
                                            </td>
                                            <td>
                                                <span class="text-sm">{formatPercentage(country.tva_rate)}</span>
                                            </td>
                                            <td>
                                                <span class={getImportStatusBadgeClass(country.allows_imports)}>
                                                    {country.allows_imports ? 'Yes' : 'No'}
                                                </span>
                                            </td>
                                            <td>
                                                <div class="flex flex-col gap-1 text-xs">
                                                    <span class="text-success">Min: {formatCurrency(country.min_shipping_cost)}</span>
                                                    <span class="text-gray-600">Avg: {formatCurrency(country.avg_shipping_cost)}</span>
                                                    <span class="text-destructive">Max: {formatCurrency(country.max_shipping_cost)}</span>
                                                </div>
                                            </td>
                                            <td>
                                                <div class="flex flex-col gap-1 text-xs">
                                                    <span class="text-success">{country.min_shipping_time_days}d</span>
                                                    <span class="text-gray-600">{country.avg_shipping_time_days}d</span>
                                                    <span class="text-destructive">{country.max_shipping_time_days}d</span>
                                                </div>
                                            </td>
                                            <td class="text-center">
                                                <div class="kt-menu flex-inline" data-kt-menu="true">
                                                    <div class="kt-menu-item" data-kt-menu-item-offset="0, 10px" data-kt-menu-item-placement="bottom-end" data-kt-menu-item-placement-rtl="bottom-start" data-kt-menu-item-toggle="dropdown" data-kt-menu-item-trigger="click">
                                                        <button class="kt-menu-toggle kt-btn kt-btn-sm kt-btn-icon kt-btn-ghost">
                                                            <i class="ki-filled ki-dots-vertical text-lg"></i>
                                                        </button>
                                                        <div class="kt-menu-dropdown kt-menu-default w-full max-w-[175px]" data-kt-menu-dismiss="true">
                                                            {#if hasPermission('admin.countries.show')}
                                                            <div class="kt-menu-item">
                                                                <a class="kt-menu-link" href={route('admin.countries.show', { country: country.id })}>
                                                                    <span class="kt-menu-icon">
                                                                        <i class="ki-filled ki-search-list"></i>
                                                                    </span>
                                                                    <span class="kt-menu-title">View</span>
                                                                </a>
                                                            </div>
                                                            {/if}
                                                            {#if hasPermission('admin.countries.update')}
                                                            <div class="kt-menu-item">
                                                                <a class="kt-menu-link" href={route('admin.countries.edit', { country: country.id })}>
                                                                    <span class="kt-menu-icon">
                                                                        <i class="ki-filled ki-pencil"></i>
                                                                    </span>
                                                                    <span class="kt-menu-title">Edit</span>
                                                                </a>
                                                            </div>
                                                            {/if}
                                                            {#if hasPermission('admin.countries.destroy')}
                                                                <div class="kt-menu-separator"></div>
                                                                <div class="kt-menu-item">
                                                                    <button class="kt-menu-link" on:click={() => deleteCountry(country.id)}>
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