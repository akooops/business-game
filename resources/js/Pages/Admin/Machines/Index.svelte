<script>
    import AdminLayout from '../../Layouts/AdminLayout.svelte';
    import Pagination from '../../Components/Pagination.svelte';
    import Select2 from '../../Components/Forms/Select2.svelte';
    import { onMount, tick } from 'svelte';
    import { page } from '@inertiajs/svelte';

    // Define breadcrumbs for this page
    const breadcrumbs = [
        {
            title: 'Machines',
            url: route('admin.machines.index'),
            active: false
        },
        {
            title: 'Index',
            url: route('admin.machines.index'),
            active: true
        }
    ];
    
    const pageTitle = 'Machines';

    // Reactive variables
    let machines = [];
    let pagination = {};
    let loading = true;
    let search = '';
    let perPage = 10;
    let currentPage = 1;
    let searchTimeout;
    let showFilters = false;

    // Filter variables
    let manufacturerFilter = '';
    let priceMin = '';
    let priceMax = '';
    let energyMin = '';
    let energyMax = '';
    let speedMin = '';
    let speedMax = '';
    let qualityMin = '';
    let qualityMax = '';
    let areaMin = '';
    let areaMax = '';
    let setupTimeMin = '';
    let setupTimeMax = '';
    let carbonMin = '';
    let carbonMax = '';
    let failureMin = '';
    let failureMax = '';
    let decayMin = '';
    let decayMax = '';
    let maintenanceMin = '';
    let maintenanceMax = '';
    let productFilter = '';
    let employeeProfileFilter = '';

    // Select2 component references
    let productSelectComponent;
    let employeeProfileSelectComponent;

    // Format number with units
    function formatNumber(value, decimals = 1) {
        return parseFloat(value).toFixed(decimals);
    }

    // Fetch machines data
    async function fetchMachines() {
        loading = true;
        try {
            const params = new URLSearchParams({
                page: currentPage,
                perPage: perPage,
                search: search
            });
            
            // Add filter parameters
            if (manufacturerFilter) {
                params.append('manufacturer', manufacturerFilter);
            }
            if (priceMin) {
                params.append('price_min', priceMin);
            }
            if (priceMax) {
                params.append('price_max', priceMax);
            }
            if (energyMin) {
                params.append('energy_min', energyMin);
            }
            if (energyMax) {
                params.append('energy_max', energyMax);
            }
            if (speedMin) {
                params.append('speed_min', speedMin);
            }
            if (speedMax) {
                params.append('speed_max', speedMax);
            }
            if (qualityMin) {
                params.append('quality_min', qualityMin);
            }
            if (qualityMax) {
                params.append('quality_max', qualityMax);
            }
            if (areaMin) {
                params.append('area_min', areaMin);
            }
            if (areaMax) {
                params.append('area_max', areaMax);
            }
            if (setupTimeMin) {
                params.append('setup_time_min', setupTimeMin);
            }
            if (setupTimeMax) {
                params.append('setup_time_max', setupTimeMax);
            }
            if (carbonMin) {
                params.append('carbon_min', carbonMin);
            }
            if (carbonMax) {
                params.append('carbon_max', carbonMax);
            }
            if (failureMin) {
                params.append('failure_min', failureMin);
            }
            if (failureMax) {
                params.append('failure_max', failureMax);
            }
            if (decayMin) {
                params.append('decay_min', decayMin);
            }
            if (decayMax) {
                params.append('decay_max', decayMax);
            }
            if (maintenanceMin) {
                params.append('maintenance_min', maintenanceMin);
            }
            if (maintenanceMax) {
                params.append('maintenance_max', maintenanceMax);
            }
            if (productFilter) {
                params.append('product_id', productFilter);
            }
            if (employeeProfileFilter) {
                params.append('employee_profile_id', employeeProfileFilter);
            }
            
            const response = await fetch(route('admin.machines.index') + '?' + params.toString(), {
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                }
            });
            
            const data = await response.json();
            machines = data.machines;
            pagination = data.pagination;
            
            // Wait for DOM to update, then initialize menus
            await tick();
            if (window.KTMenu) {
                window.KTMenu.init();
            }
        } catch (error) {
            console.error('Error fetching machines:', error);
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
            fetchMachines();
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
            fetchMachines();
        }
    }

    // Handle per page change
    function handlePerPageChange(newPerPage) {
        perPage = newPerPage;
        currentPage = 1;
        fetchMachines();
    }

    // Handle filter changes
    function handleFilterChange() {
        currentPage = 1;
        fetchMachines();
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

    // Handle employee profile selection
    function handleEmployeeProfileSelect(event) {
        employeeProfileFilter = event.detail.value;
        handleFilterChange();
    }

    // Handle employee profile clear
    function handleEmployeeProfileClear() {
        employeeProfileFilter = '';
        handleFilterChange();
    }

    // Clear all filters
    function clearAllFilters() {
        manufacturerFilter = '';
        priceMin = '';
        priceMax = '';
        energyMin = '';
        energyMax = '';
        speedMin = '';
        speedMax = '';
        qualityMin = '';
        qualityMax = '';
        areaMin = '';
        areaMax = '';
        setupTimeMin = '';
        setupTimeMax = '';
        carbonMin = '';
        carbonMax = '';
        failureMin = '';
        failureMax = '';
        decayMin = '';
        decayMax = '';
        maintenanceMin = '';
        maintenanceMax = '';
        productFilter = '';
        employeeProfileFilter = '';
        currentPage = 1;
        
        // Clear the Select2 components
        if (productSelectComponent) {
            productSelectComponent.clear();
        }
        if (employeeProfileSelectComponent) {
            employeeProfileSelectComponent.clear();
        }
        
        fetchMachines();
    }

    // Toggle filters visibility
    function toggleFilters() {
        showFilters = !showFilters;
    }

    // Delete machine
    async function deleteMachine(machineId) {
        if (!confirm('Are you sure you want to delete this machine? This action cannot be undone.')) {
            return;
        }

        try {
            const formData = new FormData();
            formData.append('_method', 'DELETE');
            formData.append('_token', document.querySelector('meta[name="csrf-token"]')?.getAttribute('content'));

            const response = await fetch(route('admin.machines.destroy', { machine: machineId }), {
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
                    message: "Machine deleted successfully!",
                    variant: "success",
                    position: "bottom-right",
                });

                // Refresh the machines list
                fetchMachines();
            } else {
                const errorData = await response.json().catch(() => ({}));
                const errorMessage = errorData.message || 'Error deleting machine. Please try again.';
                
                KTToast.show({
                    icon: `<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-info-icon lucide-info"><circle cx="12" cy="12" r="10"/><path d="M12 16v-4"/><path d="M12 8h.01"/></svg>`,
                    message: errorMessage,
                    variant: "destructive",
                    position: "bottom-right",
                });
            }
        } catch (error) {
            console.error('Error deleting machine:', error);
            
            KTToast.show({
                icon: `<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-info-icon lucide-info"><circle cx="12" cy="12" r="10"/><path d="M12 16v-4"/><path d="M12 8h.01"/></svg>`,
                message: "Network error. Please check your connection and try again.",
                variant: "destructive",
                position: "bottom-right",
            });
        }
    }

    onMount(() => {
        fetchMachines();
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
            <!-- Machines Header -->
            <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4">
                <div class="flex flex-col gap-1">
                    <h1 class="text-2xl font-bold text-mono">Machines Management</h1>
                    <p class="text-sm text-secondary-foreground">
                        Manage your manufacturing machines and equipment
                    </p>
                </div>
                <div class="flex items-center gap-3">
                    {#if hasPermission('admin.machines.store')}
                    <a href="{route('admin.machines.create')}" class="kt-btn kt-btn-primary">
                        <i class="ki-filled ki-plus text-base"></i>
                        Add New Machine
                    </a>
                    {/if}
                </div>                      
            </div>

            <!-- Machines Table -->
            <div class="kt-card">
                <div class="kt-card-header">
                    <div class="kt-card-toolbar">
                        <div class="flex items-center gap-4">
                            <div class="kt-input max-w-64 w-64">
                                <i class="ki-filled ki-magnifier"></i>
                                <input 
                                    type="text" 
                                    class="kt-input" 
                                    placeholder="Search machines..." 
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
                            {#if manufacturerFilter || priceMin || priceMax || energyMin || energyMax || speedMin || speedMax || qualityMin || qualityMax || areaMin || areaMax || setupTimeMin || setupTimeMax || carbonMin || carbonMax || failureMin || failureMax || decayMin || decayMax || maintenanceMin || maintenanceMax || productFilter || employeeProfileFilter}
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
                            <!-- Manufacturer Filter -->
                            <div class="space-y-2">
                                <h4 class="text-sm font-medium text-gray-700">Manufacturer</h4>
                                <input 
                                    type="text" 
                                    class="kt-input w-full" 
                                    placeholder="Enter manufacturer" 
                                    bind:value={manufacturerFilter}
                                    on:input={handleFilterChange}
                                />
                            </div>

                            <!-- Price Range -->
                            <div class="space-y-2">
                                <h4 class="text-sm font-medium text-gray-700">Acquisition Cost (DZD)</h4>
                                
                                <div class="flex gap-2">
                                    <input 
                                        type="number" 
                                        class="kt-input flex-1" 
                                        placeholder="Min Price" 
                                        bind:value={priceMin}
                                        on:input={handleFilterChange}
                                        step="1000"
                                        min="0"
                                    />
                                    <input 
                                        type="number" 
                                        class="kt-input flex-1" 
                                        placeholder="Max Price" 
                                        bind:value={priceMax}
                                        on:input={handleFilterChange}
                                        step="1000"
                                        min="0"
                                    />
                                </div>
                            </div>

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

                            <!-- Setup Time Range -->
                            <div class="space-y-2">
                                <h4 class="text-sm font-medium text-gray-700">Setup Time (days)</h4>
                                
                                <div class="flex gap-2">
                                    <input 
                                        type="number" 
                                        class="kt-input flex-1" 
                                        placeholder="Min Days" 
                                        bind:value={setupTimeMin}
                                        on:input={handleFilterChange}
                                        min="0"
                                    />
                                    <input 
                                        type="number" 
                                        class="kt-input flex-1" 
                                        placeholder="Max Days" 
                                        bind:value={setupTimeMax}
                                        on:input={handleFilterChange}
                                        min="0"
                                    />
                                </div>
                            </div>

                            <!-- Energy Consumption Range -->
                            <div class="space-y-2">
                                <h4 class="text-sm font-medium text-gray-700">Energy Consumption (kWh)</h4>
                                
                                <div class="flex gap-2">
                                    <input 
                                        type="number" 
                                        class="kt-input flex-1" 
                                        placeholder="Min Energy" 
                                        bind:value={energyMin}
                                        on:input={handleFilterChange}
                                        step="0.1"
                                        min="0"
                                    />
                                    <input 
                                        type="number" 
                                        class="kt-input flex-1" 
                                        placeholder="Max Energy" 
                                        bind:value={energyMax}
                                        on:input={handleFilterChange}
                                        step="0.1"
                                        min="0"
                                    />
                                </div>
                            </div>

                            <!-- Carbon Emissions Range -->
                            <div class="space-y-2">
                                <h4 class="text-sm font-medium text-gray-700">Carbon Emissions (kg CO2/h)</h4>
                                
                                <div class="flex gap-2">
                                    <input 
                                        type="number" 
                                        class="kt-input flex-1" 
                                        placeholder="Min Carbon" 
                                        bind:value={carbonMin}
                                        on:input={handleFilterChange}
                                        step="0.1"
                                        min="0"
                                    />
                                    <input 
                                        type="number" 
                                        class="kt-input flex-1" 
                                        placeholder="Max Carbon" 
                                        bind:value={carbonMax}
                                        on:input={handleFilterChange}
                                        step="0.1"
                                        min="0"
                                    />
                                </div>
                            </div>

                            <!-- Speed Range -->
                            <div class="space-y-2">
                                <h4 class="text-sm font-medium text-gray-700">Average Speed (units/h)</h4>
                                
                                <div class="flex gap-2">
                                    <input 
                                        type="number" 
                                        class="kt-input flex-1" 
                                        placeholder="Min Speed" 
                                        bind:value={speedMin}
                                        on:input={handleFilterChange}
                                        step="0.1"
                                        min="0"
                                    />
                                    <input 
                                        type="number" 
                                        class="kt-input flex-1" 
                                        placeholder="Max Speed" 
                                        bind:value={speedMax}
                                        on:input={handleFilterChange}
                                        step="0.1"
                                        min="0"
                                    />
                                </div>
                            </div>

                            <!-- Quality Factor Range -->
                            <div class="space-y-2">
                                <h4 class="text-sm font-medium text-gray-700">Quality Factor</h4>
                                
                                <div class="flex gap-2">
                                    <input 
                                        type="number" 
                                        class="kt-input flex-1" 
                                        placeholder="Min Quality" 
                                        bind:value={qualityMin}
                                        on:input={handleFilterChange}
                                        step="0.1"
                                        min="0"
                                        max="1"
                                    />
                                    <input 
                                        type="number" 
                                        class="kt-input flex-1" 
                                        placeholder="Max Quality" 
                                        bind:value={qualityMax}
                                        on:input={handleFilterChange}
                                        step="0.1"
                                        min="0"
                                        max="1"
                                    />
                                </div>
                            </div>

                            <!-- Failure Rate Range -->
                            <div class="space-y-2">
                                <h4 class="text-sm font-medium text-gray-700">Failure Rate (%/h)</h4>
                                
                                <div class="flex gap-2">
                                    <input 
                                        type="number" 
                                        class="kt-input flex-1" 
                                        placeholder="Min Failure" 
                                        bind:value={failureMin}
                                        on:input={handleFilterChange}
                                        step="0.001"
                                        min="0"
                                    />
                                    <input 
                                        type="number" 
                                        class="kt-input flex-1" 
                                        placeholder="Max Failure" 
                                        bind:value={failureMax}
                                        on:input={handleFilterChange}
                                        step="0.001"
                                        min="0"
                                    />
                                </div>
                            </div>

                            <!-- Reliability Decay Range -->
                            <div class="space-y-2">
                                <h4 class="text-sm font-medium text-gray-700">Reliability Decay (%/h)</h4>
                                
                                <div class="flex gap-2">
                                    <input 
                                        type="number" 
                                        class="kt-input flex-1" 
                                        placeholder="Min Decay" 
                                        bind:value={decayMin}
                                        on:input={handleFilterChange}
                                        step="0.001"
                                        min="0"
                                    />
                                    <input 
                                        type="number" 
                                        class="kt-input flex-1" 
                                        placeholder="Max Decay" 
                                        bind:value={decayMax}
                                        on:input={handleFilterChange}
                                        step="0.001"
                                        min="0"
                                    />
                                </div>
                            </div>

                            <!-- Maintenance Interval Range -->
                            <div class="space-y-2">
                                <h4 class="text-sm font-medium text-gray-700">Maintenance Interval (days)</h4>
                                
                                <div class="flex gap-2">
                                    <input 
                                        type="number" 
                                        class="kt-input flex-1" 
                                        placeholder="Min Interval" 
                                        bind:value={maintenanceMin}
                                        on:input={handleFilterChange}
                                        min="0"
                                    />
                                    <input 
                                        type="number" 
                                        class="kt-input flex-1" 
                                        placeholder="Max Interval" 
                                        bind:value={maintenanceMax}
                                        on:input={handleFilterChange}
                                        min="0"
                                    />
                                </div>
                            </div>

                            <!-- Product Output Filter -->
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

                            <!-- Employee Profile Filter -->
                            <div class="space-y-2">
                                <h4 class="text-sm font-medium text-gray-700">Employee Profile</h4>
                                <Select2
                                    bind:this={employeeProfileSelectComponent}
                                    id="employee-profile-filter"
                                    placeholder="Search employee profiles..."
                                    bind:value={employeeProfileFilter}
                                    on:select={handleEmployeeProfileSelect}
                                    on:clear={handleEmployeeProfileClear}
                                    ajax={{
                                        url: route('admin.employee-profiles.index'),
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
                                                results: data.employeeProfiles.map(profile => ({
                                                    id: profile.id,
                                                    text: `${profile.name}`,
                                                    name: profile.name,
                                                    description: profile.description,
                                                    recruitment_difficulty: profile.recruitment_difficulty,
                                                    avg_salary: profile.avg_salary_month
                                                }))
                                            };
                                        },
                                        cache: true
                                    }}
                                    templateResult={function(data) {
                                        if (data.loading) return data.text;
                                        
                                        const $elem = globalThis.$('<div class="flex items-center gap-3">' +
                                            '<div class="flex items-center justify-center size-8 shrink-0 rounded bg-accent/50">' +
                                            '<i class="ki-filled ki-profile-user text-xs text-muted-foreground"></i>' +
                                            '</div>' +
                                            '<div class="flex flex-col">' +
                                            '<span class="font-medium text-sm">' + data.name + '</span>' +
                                            '<span class="text-xs text-muted-foreground">' + (data.description || '').substring(0, 30) + (data.description && data.description.length > 30 ? '...' : '') + '</span>' +
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
                                    <th class="min-w-[250px]">
                                        <span class="kt-table-col">
                                            <span class="kt-table-col-label">Machine</span>
                                        </span>
                                    </th>
                                    <th class="min-w-[140px]">
                                        <span class="kt-table-col">
                                            <span class="kt-table-col-label">Performance</span>
                                        </span>
                                    </th>
                                    <th class="min-w-[120px]">
                                        <span class="kt-table-col">
                                            <span class="kt-table-col-label">Setup Requirements</span>
                                        </span>
                                    </th>
                                    <th class="min-w-[150px]">
                                        <span class="kt-table-col">
                                            <span class="kt-table-col-label">Employees Required</span>
                                        </span>
                                    </th>
                                    <th class="min-w-[150px]">
                                        <span class="kt-table-col">
                                            <span class="kt-table-col-label">Outputs</span>
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
                                                <div class="kt-skeleton w-20 h-4 rounded"></div>
                                            </td>
                                            <td class="p-4">
                                                <div class="kt-skeleton w-16 h-4 rounded"></div>
                                            </td>
                                            <td class="p-4">
                                                <div class="kt-skeleton w-20 h-6 rounded"></div>
                                            </td>
                                            <td class="p-4">
                                                <div class="kt-skeleton w-20 h-6 rounded"></div>
                                            </td>
                                            <td class="p-4">
                                                <div class="kt-skeleton w-8 h-8 rounded"></div>
                                            </td>
                                        </tr>
                                    {/each}
                                {:else if machines.length === 0}
                                    <!-- Empty state -->
                                    <tr>
                                        <td colspan="8" class="p-10">
                                            <div class="flex flex-col items-center justify-center text-center">
                                                <div class="mb-4">
                                                    <i class="ki-filled ki-setting-3 text-4xl text-muted-foreground"></i>
                                                </div>
                                                <h3 class="text-lg font-semibold text-mono mb-2">No machines found</h3>
                                                <p class="text-sm text-secondary-foreground mb-4">
                                                    {search ? 'No machines match your search criteria.' : 'Get started by adding your first machine.'}
                                                </p>
                                                {#if hasPermission('admin.machines.store')}
                                                <a href="{route('admin.machines.create')}" class="kt-btn kt-btn-primary">
                                                    <i class="ki-filled ki-plus text-base"></i>
                                                    Add First Machine
                                                </a>
                                                {/if}
                                            </div>
                                        </td>
                                    </tr>
                                {:else}
                                    <!-- Actual data rows -->
                                    {#each machines as machine}
                                        <tr class="hover:bg-muted/50">
                                            <td>
                                                <input class="kt-checkbox kt-checkbox-sm" type="checkbox" value={machine.id}/>
                                            </td>
                                            <td>
                                                <span class="text-sm font-medium text-mono">#{machine.id}</span>
                                            </td>
                                            <td>
                                                <div class="flex items-center gap-3">
                                                    <div class="flex-shrink-0">
                                                        {#if machine.image_url}
                                                            <img 
                                                                src={machine.image_url} 
                                                                alt={machine.name}
                                                                class="w-10 h-10 rounded-lg object-cover"
                                                            />
                                                        {:else}
                                                            <div class="w-10 h-10 rounded-lg bg-accent/50 flex items-center justify-center">
                                                                <i class="ki-filled ki-technology text-lg text-muted-foreground"></i>
                                                            </div>
                                                        {/if}
                                                    </div>
                                                    <div class="flex flex-col gap-1">
                                                        <span class="text-sm font-medium text-mono hover:text-primary">
                                                            {machine.name}
                                                        </span>
                                                        <div class="flex flex-col gap-0.5">
                                                            <span class="text-xs text-muted-foreground">
                                                                {machine.model}
                                                            </span>
                                                            <span class="text-xs text-muted-foreground">
                                                                {machine.manufacturer}
                                                            </span>
                                                        </div>
                                                    </div>
                                                </div>
                                            </td>
                                            <td>
                                                <div class="flex flex-col gap-1">
                                                    <div class="flex items-center gap-1">
                                                        <span class="text-xs text-muted-foreground">Speed:</span>
                                                        <span class="text-xs font-medium">{formatNumber(machine.avg_speed_hour)}/h</span>
                                                    </div>
                                                    <div class="flex items-center gap-1">
                                                        <span class="text-xs text-muted-foreground">Quality:</span>
                                                        <span class="text-xs font-medium">{formatNumber(machine.quality_factor)}%</span>
                                                    </div>
                                                    <div class="flex items-center gap-1">
                                                        <span class="text-xs text-muted-foreground">Energy:</span>
                                                        <span class="text-xs font-medium">{formatNumber(machine.energy_consumption_hour)} kWh</span>
                                                    </div>
                                                    <div class="flex items-center gap-1">
                                                        <span class="text-xs text-muted-foreground">Carbon:</span>
                                                        <span class="text-xs font-medium">{formatNumber(machine.carbon_emissions_hour)} kg/h</span>
                                                    </div>
                                                </div>
                                            </td>
                                            <td>
                                                <div class="flex items-center gap-1">
                                                    <span class="text-xs text-muted-foreground">Area Required:</span>
                                                    <span class="text-xs font-medium">{formatNumber(machine.area_required)} m²</span>
                                                </div>
                                                <div class="flex items-center gap-1">
                                                    <span class="text-xs text-muted-foreground">Setup Time:</span>
                                                    <span class="text-xs font-medium">{machine.setup_time_days} days</span>
                                                </div>
                                                <div class="flex items-center gap-1">
                                                    <span class="text-xs text-muted-foreground">Cost:</span>
                                                    <span class="text-sm font-bold">DZD{machine.cost_to_acquire}</span>
                                                </div>
                                            </td>
                                            <td>
                                                <div class="flex flex-col gap-1">
                                                    {#if machine.employee_profiles && machine.employee_profiles.length > 0}
                                                        <div class="flex flex-wrap gap-1">
                                                            {#each machine.employee_profiles.slice(0, 2) as profile}
                                                                <span class="kt-badge kt-badge-outline kt-badge-primary kt-badge-xs">
                                                                    {profile.pivot.required_count}x {profile.name}
                                                                </span>
                                                            {/each}
                                                            {#if machine.employee_profiles.length > 2}
                                                                <span class="kt-badge kt-badge-outline kt-badge-secondary kt-badge-xs">
                                                                    +{machine.employee_profiles.length - 2} more
                                                                </span>
                                                            {/if}
                                                        </div>
                                                    {:else}
                                                        <span class="text-xs text-muted-foreground">No requirements</span>
                                                    {/if}
                                                </div>
                                            </td>
                                            <td>
                                                <div class="flex flex-col gap-1">
                                                    {#if machine.products && machine.products.length > 0}
                                                        <div class="flex flex-wrap gap-1">
                                                            {#each machine.products.slice(0, 2) as product}
                                                                <span class="kt-badge kt-badge-outline kt-badge-success kt-badge-xs">
                                                                    {product.name}
                                                                </span>
                                                            {/each}
                                                            {#if machine.products.length > 2}
                                                                <span class="kt-badge kt-badge-outline kt-badge-secondary kt-badge-xs">
                                                                    +{machine.products.length - 2} more
                                                                </span>
                                                            {/if}
                                                        </div>
                                                    {:else}
                                                        <span class="text-xs text-muted-foreground">No outputs</span>
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
                                                            {#if hasPermission('admin.machines.show')}
                                                            <div class="kt-menu-item">
                                                                <a class="kt-menu-link" href={route('admin.machines.show', { machine: machine.id })}>
                                                                    <span class="kt-menu-icon">
                                                                        <i class="ki-filled ki-search-list"></i>
                                                                    </span>
                                                                    <span class="kt-menu-title">View</span>
                                                                </a>
                                                            </div>
                                                            {/if}
                                                            {#if hasPermission('admin.machines.update')}
                                                            <div class="kt-menu-item">
                                                                <a class="kt-menu-link" href={route('admin.machines.edit', { machine: machine.id })}>
                                                                    <span class="kt-menu-icon">
                                                                        <i class="ki-filled ki-pencil"></i>
                                                                    </span>
                                                                    <span class="kt-menu-title">Edit</span>
                                                                </a>
                                                            </div>
                                                            {/if}
                                                            {#if hasPermission('admin.machines.destroy')}
                                                                <div class="kt-menu-separator"></div>
                                                                <div class="kt-menu-item">
                                                                    <button class="kt-menu-link" on:click={() => deleteMachine(machine.id)}>
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