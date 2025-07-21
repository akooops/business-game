<script>
    import CompanyLayout from '../../Layouts/CompanyLayout.svelte';
    import Pagination from '../../Components/Pagination.svelte';
    import Select2 from '../../Components/Forms/Select2.svelte';
    import { onMount, tick } from 'svelte';
    import { page } from '@inertiajs/svelte'

    // Define breadcrumbs for this page
    const breadcrumbs = [
        {
            title: 'Machines',
            url: route('company.machines.index'),
            active: false
        },
        {
            title: 'Setup Machine',
            url: route('company.machines.setup-page'),
            active: true
        }
    ];
    
    const pageTitle = 'Setup Machine';

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
    let operationCostMin = '';
    let operationCostMax = '';
    let speedMin = '';
    let speedMax = '';
    let qualityMin = '';
    let qualityMax = '';
    let carbonFootprintMin = '';
    let carbonFootprintMax = '';
    let reliabilityDecayDaysMin = '';
    let reliabilityDecayDaysMax = '';
    let productFilter = '';
    let employeeProfileFilter = '';

    // Select2 component references
    let productSelectComponent;
    let employeeProfileSelectComponent;

    // Drawer state
    let selectedMachine = null;
    let showMachineDrawer = false;

    // Modal state
    let showSetupModal = false;
    let setupData = null;

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
            if (operationCostMin) {
                params.append('operation_cost_min', operationCostMin);
            }
            if (operationCostMax) {
                params.append('operation_cost_max', operationCostMax);
            }
            if (speedMin) {
                params.append('min_speed', speedMin);
            }
            if (speedMax) {
                params.append('max_speed', speedMax);
            }
            if (qualityMin) {
                params.append('quality_factor_min', qualityMin);
            }
            if (qualityMax) {
                params.append('quality_factor_max', qualityMax);
            }
            if (carbonFootprintMin) {
                params.append('carbon_footprint_min', carbonFootprintMin);
            }
            if (carbonFootprintMax) {
                params.append('carbon_footprint_max', carbonFootprintMax);
            }
            if (reliabilityDecayDaysMin) {
                params.append('reliability_decay_days_min', reliabilityDecayDaysMin);
            }
            if (reliabilityDecayDaysMax) {
                params.append('reliability_decay_days_max', reliabilityDecayDaysMax);
            }
            if (productFilter) {
                params.append('product_id', productFilter);
            }
            if (employeeProfileFilter) {
                params.append('employee_profile_id', employeeProfileFilter);
            }
            
            const response = await fetch(route('company.machines.setup-page') + '?' + params.toString(), {
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

    // Handle filter changes
    function handleFilterChange() {
        currentPage = 1;
        fetchMachines();
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
        operationCostMin = '';
        operationCostMax = '';
        speedMin = '';
        speedMax = '';
        qualityMin = '';
        qualityMax = '';
        carbonFootprintMin = '';
        carbonFootprintMax = '';
        reliabilityDecayDaysMin = '';
        reliabilityDecayDaysMax = '';
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

    // Open machine drawer
    function openMachineDrawer(machine) {
        selectedMachine = machine;
        showMachineDrawer = true;
        
        // Show drawer
        const toggleButton = document.querySelector('[data-kt-drawer-toggle="#machine_drawer"]');
        if (toggleButton) {
            toggleButton.click();
        }
    }

    // Close machine drawer
    function closeMachineDrawer() {
        showMachineDrawer = false;
        selectedMachine = null;
    }

    // Open setup modal
    function openSetupModal(machine) {
        selectedMachine = machine;
        
        // Calculate setup data
        const setupCost = machine.cost_to_acquire;
        const totalEmployeeCost = machine.employee_profiles ? machine.employee_profiles.reduce((total, profile) => {
            return total + (profile.avg_salary_month * profile.pivot.required_count);
        }, 0) : 0;

        const totalSetupCost = setupCost + totalEmployeeCost;

        setupData = {
            machine: machine,
            employeeProfiles: machine.employee_profiles || [],
            setupCost: setupCost,
            employeeCost: totalEmployeeCost,
            totalSetupCost: totalSetupCost,
            operationCost: machine.operation_cost,
            carbonFootprint: machine.carbon_footprint
        };

        showSetupModal = true;
        
        // Show modal
        const toggleButton = document.querySelector('[data-kt-modal-toggle="#setup_modal"]');
        if (toggleButton) {
            toggleButton.click();
        }
    }

    // Close setup modal
    function closeSetupModal() {
        // Simulate button click to use KT framework logic
        const dismissButton = document.querySelector('[data-kt-modal-dismiss="#setup_modal"]');
        if (dismissButton) {
            dismissButton.click();
        }
        
        showSetupModal = false;
        setupData = null;
    }

    // Setup machine
    async function setupMachine() {
        if (!setupData) return;

        try {
            const response = await fetch(route('company.machines.setup', setupData.machine.id), {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content'),
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: JSON.stringify({
                    machine_id: setupData.machine.id,
                    employee_profiles: setupData.employeeProfiles.map(profile => ({
                        id: profile.id,
                        required_count: profile.pivot.required_count
                    }))
                })
            });

            if (response.ok) {
                const data = await response.json();
                
                // Show success toast
                showToast(data.message || 'Machine setup completed successfully!', 'success');
                
                // Close modal
                closeSetupModal();
                
                // Reset form
                selectedMachine = null;
                setupData = null;
                
                if (productSelectComponent) {
                    productSelectComponent.clear();
                }
            } else {
                const errorData = await response.json();
                showToast(errorData.message || 'Error setting up machine. Please try again.', 'error');
            }
        } catch (error) {
            console.error('Error setting up machine:', error);
            showToast('Network error. Please check your connection and try again.', 'error');
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

    onMount(() => {
        fetchMachines();
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
    <div class="kt-container-fluid">
        <div class="grid gap-5 lg:gap-7.5">
            <!-- Machines Header -->
            <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4">
                <div class="flex flex-col gap-1">
                    <h1 class="text-2xl font-bold text-mono">Setup Machines</h1>
                    <p class="text-sm text-secondary-foreground">
                        Browse and set up available manufacturing machines and equipment
                    </p>
                </div>
                <div class="flex items-center gap-3">
                    <a href="{route('company.machines.index')}" class="kt-btn kt-btn-outline">
                        <i class="ki-filled ki-arrow-left text-base"></i>
                        Back to Machines
                    </a>
                </div>                      
            </div>

            <!-- Machines Grid -->
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
                            {#if manufacturerFilter || priceMin || priceMax || operationCostMin || operationCostMax || speedMin || speedMax || qualityMin || qualityMax || carbonFootprintMin || carbonFootprintMax || reliabilityDecayDaysMin || reliabilityDecayDaysMax || productFilter || employeeProfileFilter}
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

                            <!-- Carbon Footprint Range -->
                            <div class="space-y-2">
                                <h4 class="text-sm font-medium text-gray-700">Carbon Footprint (kg CO2/unit)</h4>
                                
                                <div class="flex gap-2">
                                    <input 
                                        type="number" 
                                        class="kt-input flex-1" 
                                        placeholder="Min Carbon Footprint" 
                                        bind:value={carbonFootprintMin}
                                        on:input={handleFilterChange}
                                        step="0.1"
                                        min="0"
                                    />
                                    <input 
                                        type="number" 
                                        class="kt-input flex-1" 
                                        placeholder="Max Carbon Footprint" 
                                        bind:value={carbonFootprintMax}
                                        on:input={handleFilterChange}
                                        step="0.1"
                                        min="0"
                                    />
                                </div>
                            </div>

                            <!-- Energy Consumption Range -->
                            <div class="space-y-2">
                                <h4 class="text-sm font-medium text-gray-700">Operation Cost (DZD/day)</h4>
                                
                                <div class="flex gap-2">
                                    <input 
                                        type="number" 
                                        class="kt-input flex-1" 
                                        placeholder="Min Operation Cost" 
                                        bind:value={operationCostMin}
                                        on:input={handleFilterChange}
                                        step="0.1"
                                        min="0"
                                    />
                                    <input 
                                        type="number" 
                                        class="kt-input flex-1" 
                                        placeholder="Max Operation Cost" 
                                        bind:value={operationCostMax}
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
                                    />
                                    <input 
                                        type="number" 
                                        class="kt-input flex-1" 
                                        placeholder="Max Quality" 
                                        bind:value={qualityMax}
                                        on:input={handleFilterChange}
                                        step="0.1"
                                        min="0"
                                    />
                                </div>
                            </div>

                            <!-- Speed Range -->
                            <div class="space-y-2">
                                <h4 class="text-sm font-medium text-gray-700">Average Speed (units/day)</h4>
                                
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

                            <!-- Reliability Decay Days Range -->
                            <div class="space-y-2">
                                <h4 class="text-sm font-medium text-gray-700">Reliability Decay Days</h4>
                                
                                <div class="flex gap-2">
                                    <input 
                                        type="number" 
                                        class="kt-input flex-1" 
                                        placeholder="Min Reliability Decay Days" 
                                        bind:value={reliabilityDecayDaysMin}
                                        on:input={handleFilterChange}
                                        step="0.1"
                                        min="0"
                                    />
                                    <input 
                                        type="number" 
                                        class="kt-input flex-1" 
                                        placeholder="Max Reliability Decay Days" 
                                        bind:value={reliabilityDecayDaysMax}
                                        on:input={handleFilterChange}
                                        step="0.1"
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
                                    allowClear={true}
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

                            <!-- Employee Profile Filter -->
                            <div class="space-y-2">
                                <h4 class="text-sm font-medium text-gray-700">Employee Profile</h4>
                                <Select2
                                    bind:this={employeeProfileSelectComponent}
                                    id="employee-profile-filter"
                                    placeholder="Search employee profiles..."
                                    allowClear={true}
                                    on:select={handleEmployeeProfileSelect}
                                    on:clear={handleEmployeeProfileClear}
                                    ajax={{
                                        url: route('company.employee-profiles.index'),
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
                    {#if loading}
                        <!-- Loading skeleton -->
                        <div class="p-6">
                            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 p-4">
                                {#each Array(perPage) as _, i}
                                    <div class="kt-card animate-pulse">
                                        <div class="kt-card-body p-4">
                                            <!-- Machine Image Skeleton -->
                                            <div class="flex items-center justify-center mb-4">
                                                <div class="kt-skeleton h-[180px] w-full rounded-sm"></div>
                                            </div>

                                            <!-- Machine Info Skeleton -->
                                            <div class="mb-4">
                                                <div class="kt-skeleton h-6 w-3/4 mb-2"></div>
                                                <div class="kt-skeleton h-4 w-1/2 mb-2"></div>
                                                <div class="kt-skeleton h-3 w-full"></div>
                                            </div>

                                            <!-- Performance Details Skeleton -->
                                            <div class="space-y-1">
                                                <div class="flex justify-between">
                                                    <div class="kt-skeleton h-3 w-20"></div>
                                                    <div class="kt-skeleton h-3 w-16"></div>
                                                </div>
                                                <div class="flex justify-between">
                                                    <div class="kt-skeleton h-3 w-24"></div>
                                                    <div class="kt-skeleton h-3 w-12"></div>
                                                </div>
                                                <div class="flex justify-between">
                                                    <div class="kt-skeleton h-3 w-16"></div>
                                                    <div class="kt-skeleton h-3 w-20"></div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                {/each}
                            </div>
                        </div>
                    {:else if machines.length === 0}
                        <!-- Empty state -->
                        <div class="p-10">
                            <div class="flex flex-col items-center justify-center text-center">
                                <div class="mb-4">
                                    <i class="ki-filled ki-setting-3 text-4xl text-muted-foreground"></i>
                                </div>
                                <h3 class="text-lg font-semibold text-mono mb-2">No machines found</h3>
                                <p class="text-sm text-secondary-foreground mb-4">
                                    {search ? 'No machines match your search criteria.' : 'No machines available for setup.'}
                                </p>
                            </div>
                        </div>
                    {:else}
                        <!-- Machines Grid -->
                        <div class="p-6">
                            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 p-4">
                                {#each machines as machine}
                                    <div class="kt-card kt-card-hover">
                                        <div class="kt-card-body p-4">
                                            <!-- Machine Image -->
                                            <div class="flex items-center justify-center mb-4 cursor-pointer" on:click={() => openMachineDrawer(machine)}>
                                                {#if machine.image_url}
                                                    <img 
                                                        src={machine.image_url} 
                                                        alt={machine.name}
                                                        class="h-[180px] w-full object-cover rounded-sm"
                                                    />
                                                {:else}
                                                    <div class="h-[180px] w-full bg-accent/50 flex items-center justify-center rounded-sm">
                                                        <i class="ki-filled ki-setting-3 text-4xl text-muted-foreground"></i>
                                                    </div>
                                                {/if}
                                            </div>

                                            <!-- Machine Info -->
                                            <div class="mb-4 cursor-pointer" on:click={() => openMachineDrawer(machine)}>
                                                <h3 class="text-lg font-semibold text-mono mb-1">
                                                    {machine.name}
                                                </h3>
                                                <p class="text-sm text-muted-foreground mb-2">
                                                    {machine.model} - {machine.manufacturer}
                                                </p>
                                            </div>

                                            <!-- Performance Details -->
                                            <div class="text-xs text-muted-foreground space-y-1 cursor-pointer" on:click={() => openMachineDrawer(machine)}>
                                                <div class="flex justify-between">
                                                    <span>Speed:</span>
                                                    <span class="font-medium">{machine.avg_speed} units/day</span>
                                                </div>
                                                <div class="flex justify-between">
                                                    <span>Quality Factor:</span>
                                                    <span class="font-medium">{machine.quality_factor}%</span>
                                                </div>
                                                <div class="flex justify-between">
                                                    <span>Operation Cost:</span>
                                                    <span class="font-medium">DZD {machine.operation_cost}/day</span>
                                                </div>
                                                <div class="flex justify-between">
                                                    <span>Carbon Footprint:</span>
                                                    <span class="font-medium">{machine.carbon_footprint} kg CO2/unit</span>
                                                </div>
                                                <div class="flex justify-between">
                                                    <span>Cost to Acquire:</span>
                                                    <span class="font-medium">DZD {machine.cost_to_acquire}</span>
                                                </div>
                                            </div>

                                            <!-- Action Buttons -->
                                            <div class="flex items-center justify-between mt-4 pt-3 border-t border-border">
                                                <button 
                                                    class="kt-btn kt-btn-sm kt-btn-outline kt-btn-primary"
                                                    on:click={() => openMachineDrawer(machine)}
                                                >
                                                    <i class="ki-filled ki-eye text-sm"></i>
                                                    View Details
                                                </button>
                                                <button 
                                                    class="kt-btn kt-btn-sm kt-btn-primary"
                                                    on:click={() => openSetupModal(machine)}
                                                >
                                                    <i class="ki-filled ki-setting-3 text-sm"></i>
                                                    Setup Machine
                                                </button>
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

    <!-- Hidden button to trigger drawer -->
    <button style="display:none" data-kt-drawer-toggle="#machine_drawer"></button>

    <!-- Hidden button to trigger modal -->
    <button style="display:none" data-kt-modal-toggle="#setup_modal"></button>

    <!-- Machine Details Drawer -->
    <div class="hidden kt-drawer kt-drawer-end card flex-col max-w-[90%] w-[450px] top-5 bottom-5 end-5 rounded-xl border border-border" data-kt-drawer="true" data-kt-drawer-container="body" id="machine_drawer">
        <div class="flex items-center justify-between gap-2.5 text-sm text-mono font-semibold px-5 py-2.5 border-b border-b-border">
            Machine Details
            <button class="kt-btn kt-btn-sm kt-btn-icon kt-btn-dim shrink-0" data-kt-drawer-dismiss="true" on:click={closeMachineDrawer}>
                <i class="ki-filled ki-cross"></i>
            </button>
        </div>
        
        <div class="kt-card-content flex flex-col space-y-3 p-5 kt-scrollable-y-auto">
            {#if selectedMachine}
                <!-- Machine Image -->
                <div class="kt-card relative items-center justify-center bg-accent/50 mb-6.5 h-[180px] shadow-none">
                    {#if selectedMachine.image_url}
                        <img 
                            src={selectedMachine.image_url} 
                            alt={selectedMachine.name}
                            class="h-[180px] w-full object-cover rounded-sm"
                        />
                    {:else}
                        <div class="flex items-center justify-center h-full">
                            <i class="ki-filled ki-setting-3 text-4xl text-muted-foreground"></i>
                        </div>
                    {/if}
                </div>

                <!-- Machine Name -->
                <span class="text-base font-medium text-mono">
                    {selectedMachine.name}
                </span>

                <!-- Machine Details -->
                <div class="flex flex-col gap-2.5">
                    <div class="flex items-center gap-2.5">
                        <span class="text-xs font-normal text-foreground min-w-14 xl:min-w-24 shrink-0">
                            Model
                        </span>
                        <div>
                            <span class="text-xs font-medium text-foreground">
                                {selectedMachine.model}
                            </span>
                        </div>
                    </div>
                    <div class="flex items-center gap-2.5">
                        <span class="text-xs font-normal text-foreground min-w-14 xl:min-w-24 shrink-0">
                            Manufacturer
                        </span>
                        <div>
                            <span class="text-xs font-medium text-foreground">
                                {selectedMachine.manufacturer}
                            </span>
                        </div>
                    </div>
                </div>

                <!-- Performance Information -->
                <div class="border-t border-border pt-4">
                    <h3 class="text-sm font-semibold text-mono mb-3">Performance</h3>
                    <div class="space-y-2">
                        <div class="flex justify-between">
                            <span class="text-xs text-muted-foreground">Speed:</span>
                            <span class="text-xs font-medium">{selectedMachine.avg_speed} units/day</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-xs text-muted-foreground">Quality Factor:</span>
                            <span class="text-xs font-medium">{selectedMachine.quality_factor}%</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-xs text-muted-foreground">Operation Cost:</span>
                            <span class="text-xs font-medium">DZD {selectedMachine.operation_cost}/day</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-xs text-muted-foreground">Carbon Footprint:</span>
                            <span class="text-xs font-medium">{selectedMachine.carbon_footprint} kg CO2/unit</span>
                        </div>
                    </div>
                </div>

                <!-- Setup Information -->
                <div class="border-t border-border pt-4">
                    <h3 class="text-sm font-semibold text-mono mb-3">Setup & Cost</h3>
                    <div class="space-y-2">
                        <div class="flex justify-between">
                            <span class="text-xs text-muted-foreground">Cost to Acquire:</span>
                            <span class="text-xs font-medium">DZD {selectedMachine.cost_to_acquire}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-xs text-muted-foreground">Reliability Decay:</span>
                            <span class="text-xs font-medium">{selectedMachine.reliability_decay_days}%/day</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-xs text-muted-foreground">Maintenance Interval:</span>
                            <span class="text-xs font-medium">{selectedMachine.maintenance_interval_days} days</span>
                        </div>
                    </div>
                </div>

                <!-- Employee Requirements Section -->
                {#if selectedMachine.employee_profiles && selectedMachine.employee_profiles.length > 0}
                    <div class="border-t border-border pt-4">
                        <h3 class="text-sm font-semibold text-mono mb-3">Employee Requirements</h3>
                        <div class="space-y-3">
                            {#each selectedMachine.employee_profiles as profile}
                                <div class="kt-card">
                                    <div class="kt-card-body p-3">
                                        <div class="flex items-center gap-3">
                                            <div class="flex-1 min-w-0">
                                                <h4 class="text-sm font-semibold text-mono mb-1 truncate">
                                                    {profile.name}
                                                </h4>
                                                <p class="text-xs text-muted-foreground mb-1">
                                                    Required: {profile.pivot.required_count} employees
                                                </p>
                                                {#if profile.description}
                                                    <p class="text-xs text-muted-foreground">
                                                        {profile.description}
                                                    </p>
                                                {/if}
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            {/each}
                        </div>
                    </div>
                {:else}
                    <div class="border-t border-border pt-4">
                        <h3 class="text-sm font-semibold text-mono mb-3">Employee Requirements</h3>
                        <div class="kt-card">
                            <div class="kt-card-body p-4">
                                <div class="flex flex-col items-center justify-center text-center">
                                    <i class="ki-filled ki-profile-user text-2xl text-muted-foreground mb-2"></i>
                                    <p class="text-xs text-muted-foreground">No employee requirements</p>
                                </div>
                            </div>
                        </div>
                    </div>
                {/if}

                <!-- Output Products Section -->
                {#if selectedMachine.products && selectedMachine.products.length > 0}
                    <div class="border-t border-border pt-4">
                        <h3 class="text-sm font-semibold text-mono mb-3">Output Products</h3>
                        <div class="space-y-3">
                            {#each selectedMachine.products as product}
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
                        <h3 class="text-sm font-semibold text-mono mb-3">Output Products</h3>
                        <div class="kt-card">
                            <div class="kt-card-body p-4">
                                <div class="flex flex-col items-center justify-center text-center">
                                    <i class="ki-filled ki-abstract-26 text-2xl text-muted-foreground mb-2"></i>
                                    <p class="text-xs text-muted-foreground">No output products</p>
                                </div>
                            </div>
                        </div>
                    </div>
                {/if}
            {/if}
        </div>
    </div>

    <!-- Setup Confirmation Modal -->
    <div class="kt-modal" data-kt-modal="true" id="setup_modal">
        <div class="kt-modal-content max-w-[600px] top-[5%]">
            <div class="kt-modal-header">
                <h3 class="kt-modal-title">Confirm Machine Setup</h3>
                <button
                    type="button"
                    class="kt-modal-close"
                    aria-label="Close modal"
                    data-kt-modal-dismiss="#setup_modal"
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
                {#if setupData}
                    <div class="space-y-4">
                        <!-- Machine Info -->
                        <div class="flex items-center gap-4">
                            <div class="flex-shrink-0">
                                {#if setupData.machine.image_url}
                                    <img 
                                        src={setupData.machine.image_url} 
                                        alt={setupData.machine.name}
                                        class="w-16 h-16 rounded-lg object-cover"
                                    />
                                {:else}
                                    <div class="w-16 h-16 rounded-lg bg-accent/50 flex items-center justify-center">
                                        <i class="ki-filled ki-setting-3 text-xl text-muted-foreground"></i>
                                    </div>
                                {/if}
                            </div>
                            <div class="flex-1">
                                <h4 class="font-semibold text-mono mb-1">{setupData.machine.name}</h4>
                                <p class="text-sm text-muted-foreground mb-1">{setupData.machine.model} - {setupData.machine.manufacturer}</p>
                            </div>
                        </div>

                        <!-- Setup Details -->
                        <div class="kt-card">
                            <div class="kt-card-body p-4">
                                <h5 class="font-medium text-mono mb-3">Setup Details</h5>
                                <div class="space-y-2">
                                    <div class="flex justify-between">
                                        <span class="text-sm text-muted-foreground">Operation Cost:</span>
                                        <span class="font-medium">DZD {setupData.operationCost}/day</span>
                                    </div>
                                    <div class="flex justify-between">
                                        <span class="text-sm text-muted-foreground">Carbon Footprint:</span>
                                        <span class="font-medium">{setupData.carbonFootprint} kg CO2/unit</span>
                                    </div>
                                    <div class="flex justify-between">
                                        <span class="text-sm text-muted-foreground">Speed:</span>
                                        <span class="font-medium">{setupData.machine.avg_speed} units/day</span>
                                    </div>
                                    <div class="flex justify-between">
                                        <span class="text-sm text-muted-foreground">Quality Factor:</span>
                                        <span class="font-medium">{setupData.machine.quality_factor}%</span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Cost Breakdown -->
                        <div class="kt-card bg-accent/50">
                            <div class="kt-card-header px-5">
                                <h3 class="kt-card-title">
                                    Cost Breakdown
                                </h3>
                            </div>
                            <div class="kt-card-content px-5 py-4 space-y-2">
                                <div class="flex justify-between items-center">
                                    <span class="text-sm font-normal text-secondary-foreground">
                                        Machine Acquisition
                                    </span>
                                    <span class="text-sm font-medium text-mono">
                                        DZD {setupData.setupCost}
                                    </span>
                                </div>
                            </div>
                        </div>

                        <!-- Confirmation Message -->
                        <div class="text-sm text-muted-foreground">
                            Are you sure you want to set up this machine? This will cost <strong>DZD {setupData.setupCost}</strong> to complete.
                        </div>
                    </div>
                {/if}
            </div>
            <div class="kt-modal-footer">
                <div></div>
                <div class="flex gap-4">
                    <button
                        class="kt-btn kt-btn-secondary"
                        data-kt-modal-dismiss="#setup_modal"
                        on:click={closeSetupModal}
                    >
                        Cancel
                    </button>
                    <button 
                        class="kt-btn kt-btn-primary"
                        on:click={setupMachine}
                    >
                        Confirm Setup
                    </button>
                </div>
            </div>
        </div>
    </div>
</CompanyLayout>
