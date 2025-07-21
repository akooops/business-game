<script>
    import CompanyLayout from '../../Layouts/CompanyLayout.svelte';
    import Pagination from '../../Components/Pagination.svelte';
    import { onMount, tick } from 'svelte';
    import { page } from '@inertiajs/svelte';

    // Define breadcrumbs for this page
    const breadcrumbs = [
        {
            title: 'Machines',
            url: route('company.machines.index'),
            active: true
        }
    ];
    
    const pageTitle = 'My Machines';

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
    let reliabilityMin = '';
    let reliabilityMax = '';
    let statusFilter = '';
    let productFilter = '';
    let employeeProfileFilter = '';

    // Select2 component references
    let productSelectComponent;
    let employeeProfileSelectComponent;

    // Drawer state
    let selectedMachine = null;
    let showMachineDrawer = false;

    // Machine status mapping
    const machineStatuses = {
        'active': 'Active',
        'inactive': 'Inactive',
        'setup': 'Setup',
        'maintenance': 'Maintenance',
        'broken': 'Broken'
    };
    function getMachineStatusBadgeClass(status) {
        switch(status) {
            case 'active':
                return 'kt-badge kt-badge-outline kt-badge-success kt-badge-sm';
            case 'setup':
                return 'kt-badge kt-badge-outline kt-badge-warning kt-badge-sm';
            case 'maintenance':
                return 'kt-badge kt-badge-outline kt-badge-info kt-badge-sm';
            case 'broken':
                return 'kt-badge kt-badge-outline kt-badge-danger kt-badge-sm';
            default:
                return 'kt-badge kt-badge-outline kt-badge-secondary kt-badge-sm';
        }
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
            if (manufacturerFilter) params.append('manufacturer', manufacturerFilter);
            if (priceMin) params.append('price_min', priceMin);
            if (priceMax) params.append('price_max', priceMax);
            if (operationCostMin) params.append('operation_cost_min', operationCostMin);
            if (operationCostMax) params.append('operation_cost_max', operationCostMax);
            if (speedMin) params.append('min_speed', speedMin);
            if (speedMax) params.append('max_speed', speedMax);
            if (qualityMin) params.append('quality_factor_min', qualityMin);
            if (qualityMax) params.append('quality_factor_max', qualityMax);
            if (carbonFootprintMin) params.append('carbon_footprint_min', carbonFootprintMin);
            if (carbonFootprintMax) params.append('carbon_footprint_max', carbonFootprintMax);
            if (reliabilityMin) params.append('reliability_min', reliabilityMin);
            if (reliabilityMax) params.append('reliability_max', reliabilityMax);
            if (statusFilter) params.append('status', statusFilter);
            if (productFilter) params.append('product_id', productFilter);
            if (employeeProfileFilter) params.append('employee_profile_id', employeeProfileFilter);

            const response = await fetch(route('company.machines.index') + '?' + params.toString(), {
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
        if (searchTimeout) clearTimeout(searchTimeout);
        searchTimeout = setTimeout(() => {
            currentPage = 1;
            fetchMachines();
        }, 500);
    }
    function handleSearchInput(event) {
        search = event.target.value;
        handleSearch();
    }
    function handleFilterChange() {
        currentPage = 1;
        fetchMachines();
    }
    function goToPage(page) {
        if (page && page !== currentPage) {
            currentPage = page;
            fetchMachines();
        }
    }
    function handlePerPageChange(newPerPage) {
        perPage = newPerPage;
        currentPage = 1;
        fetchMachines();
    }
    function handleProductSelect(event) {
        productFilter = event.detail.value;
        handleFilterChange();
    }
    function handleProductClear() {
        productFilter = '';
        handleFilterChange();
    }
    function handleEmployeeProfileSelect(event) {
        employeeProfileFilter = event.detail.value;
        handleFilterChange();
    }
    function handleEmployeeProfileClear() {
        employeeProfileFilter = '';
        handleFilterChange();
    }
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
        reliabilityMin = '';
        reliabilityMax = '';
        statusFilter = '';
        productFilter = '';
        employeeProfileFilter = '';
        currentPage = 1;
        if (productSelectComponent) productSelectComponent.clear();
        if (employeeProfileSelectComponent) employeeProfileSelectComponent.clear();
        fetchMachines();
    }
    function toggleFilters() {
        showFilters = !showFilters;
    }
    function openMachineDrawer(machine) {
        selectedMachine = machine;
        showMachineDrawer = true;
        const toggleButton = document.querySelector('[data-kt-drawer-toggle="#machine_drawer"]');
        if (toggleButton) toggleButton.click();
    }
    function closeMachineDrawer() {
        showMachineDrawer = false;
        selectedMachine = null;
    }
    function formatTimestamp(timestamp) {
        if (!timestamp) return 'N/A';
        return new Date(timestamp).toLocaleString();
    }
    onMount(() => {
        fetchMachines();
    });
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
    <div class="kt-container-fluid">
        <div class="grid gap-5 lg:gap-7.5">
            <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4">
                <div class="flex flex-col gap-1">
                    <h1 class="text-2xl font-bold text-mono">My Machines</h1>
                    <p class="text-sm text-secondary-foreground">
                        View and manage your acquired machines
                    </p>
                </div>
                <div class="flex items-center gap-3">
                    <a href="{route('company.machines.setup-page')}" class="kt-btn kt-btn-primary">
                        <i class="ki-filled ki-plus text-base"></i>
                        Setup New Machine
                    </a>
                </div>
            </div>
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
                            <button 
                                class="kt-btn kt-btn-outline"
                                on:click={toggleFilters}
                            >
                                <i class="ki-filled ki-filter text-sm"></i>
                                {showFilters ? 'Hide Filters' : 'Show Filters'}
                            </button>
                            {#if manufacturerFilter || priceMin || priceMax || operationCostMin || operationCostMax || speedMin || speedMax || qualityMin || qualityMax || carbonFootprintMin || carbonFootprintMax || reliabilityMin || reliabilityMax || statusFilter || productFilter || employeeProfileFilter}
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
                            <!-- Reliability Range -->
                            <div class="space-y-2">
                                <h4 class="text-sm font-medium text-gray-700">Reliability (%)</h4>
                                <div class="flex gap-2">
                                    <input 
                                        type="number" 
                                        class="kt-input flex-1" 
                                        placeholder="Min Reliability" 
                                        bind:value={reliabilityMin}
                                        on:input={handleFilterChange}
                                        step="0.01"
                                        min="0"
                                        max="100"
                                    />
                                    <input 
                                        type="number" 
                                        class="kt-input flex-1" 
                                        placeholder="Max Reliability" 
                                        bind:value={reliabilityMax}
                                        on:input={handleFilterChange}
                                        step="0.01"
                                        min="0"
                                        max="100"
                                    />
                                </div>
                            </div>
                            <!-- Status Filter -->
                            <div class="space-y-2">
                                <h4 class="text-sm font-medium text-gray-700">Status</h4>
                                <select 
                                    class="kt-select w-full" 
                                    bind:value={statusFilter}
                                    on:change={handleFilterChange}
                                >
                                    <option value="">All Statuses</option>
                                    <option value="active">Active</option>
                                    <option value="inactive">Inactive</option>
                                    <option value="setup">Setup</option>
                                    <option value="maintenance">Maintenance</option>
                                    <option value="broken">Broken</option>
                                </select>
                            </div>
                        </div>
                    </div>
                {/if}
                <div class="kt-card-content p-0">
                    {#if loading}
                        <div class="p-6">
                            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 p-4">
                                {#each Array(perPage) as _, i}
                                    <div class="kt-card animate-pulse">
                                        <div class="kt-card-body p-4">
                                            <div class="flex items-center justify-center mb-4">
                                                <div class="kt-skeleton h-[180px] w-full rounded-sm"></div>
                                            </div>
                                            <div class="mb-4">
                                                <div class="kt-skeleton h-6 w-3/4 mb-2"></div>
                                                <div class="kt-skeleton h-4 w-1/2 mb-2"></div>
                                                <div class="kt-skeleton h-3 w-full"></div>
                                            </div>
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
                        <div class="p-10">
                            <div class="flex flex-col items-center justify-center text-center">
                                <div class="mb-4">
                                    <i class="ki-filled ki-setting-3 text-4xl text-muted-foreground"></i>
                                </div>
                                <h3 class="text-lg font-semibold text-mono mb-2">No machines found</h3>
                                <p class="text-sm text-secondary-foreground mb-4">
                                    {search ? 'No machines match your search criteria.' : 'You have not acquired any machines yet.'}
                                </p>
                                <a href="{route('company.machines.setup-page')}" class="kt-btn kt-btn-primary">
                                    <i class="ki-filled ki-plus text-base"></i>
                                    Setup New Machine
                                </a>
                            </div>
                        </div>
                    {:else}
                        <div class="p-6">
                            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 p-4">
                                {#each machines as companyMachine}
                                    <div class="kt-card kt-card-hover">
                                        <div class="kt-card-body p-4">
                                            <div class="flex items-center justify-center mb-4 cursor-pointer" on:click={() => openMachineDrawer(companyMachine)}>
                                                {#if companyMachine.machine?.image_url}
                                                    <img 
                                                        src={companyMachine.machine.image_url} 
                                                        alt={companyMachine.machine.name}
                                                        class="h-[180px] w-full object-cover rounded-sm"
                                                    />
                                                {:else}
                                                    <div class="h-[180px] w-full bg-accent/50 flex items-center justify-center rounded-sm">
                                                        <i class="ki-filled ki-setting-3 text-4xl text-muted-foreground"></i>
                                                    </div>
                                                {/if}
                                            </div>
                                            <div class="mb-4 cursor-pointer" on:click={() => openMachineDrawer(companyMachine)}>
                                                <h3 class="text-lg font-semibold text-mono mb-1">
                                                    {companyMachine.machine?.name}
                                                </h3>
                                                <p class="text-sm text-muted-foreground mb-2">
                                                    {companyMachine.machine?.model} - {companyMachine.machine?.manufacturer}
                                                </p>
                                            </div>
                                            <div class="flex items-center gap-2 mb-2">
                                                <span class={getMachineStatusBadgeClass(companyMachine.status)}>
                                                    {machineStatuses[companyMachine.status] || companyMachine.status}
                                                </span>
                                            </div>
                                            <div class="flex items-center gap-2 mb-2">
                                                <span class="text-xs text-muted-foreground">Reliability:</span>
                                                <div class="w-full bg-gray-200 rounded-full h-2">
                                                    <div class="kt-progress {companyMachine.current_reliability > 0.7 ? 'kt-progress-primary' : companyMachine.current_reliability > 0.4 ? 'kt-progress-warning' : 'kt-progress-destructive'}">
                                                        <div class="kt-progress-indicator" style="width: {(companyMachine.current_reliability * 100).toFixed(0)}%"></div>
                                                    </div>
                                                </div>
                                                <span class="text-xs text-muted-foreground">{(companyMachine.current_reliability * 100).toFixed(0)}%</span>
                                            </div>
                                            <div class="text-xs text-muted-foreground space-y-1 cursor-pointer" on:click={() => openMachineDrawer(companyMachine)}>
                                                <div class="flex justify-between">
                                                    <span>Operation Cost:</span>
                                                    <span class="font-medium">DZD {companyMachine.machine?.operation_cost}/day</span>
                                                </div>
                                                <div class="flex justify-between">
                                                    <span>Carbon Footprint:</span>
                                                    <span class="font-medium">{companyMachine.machine?.carbon_footprint} kg CO2/unit</span>
                                                </div>
                                                <div class="flex justify-between">
                                                    <span>Cost to Acquire:</span>
                                                    <span class="font-medium">DZD {companyMachine.machine?.cost_to_acquire}</span>
                                                </div>
                                            </div>
                                            <div class="flex items-center justify-between mt-4 pt-3 border-t border-border">
                                                <button 
                                                    class="kt-btn kt-btn-sm kt-btn-outline kt-btn-primary"
                                                    on:click={() => openMachineDrawer(companyMachine)}
                                                >
                                                    <i class="ki-filled ki-eye text-sm"></i>
                                                    View Details
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                {/each}
                            </div>
                        </div>
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
    <!-- Hidden button to trigger drawer -->
    <button style="display:none" data-kt-drawer-toggle="#machine_drawer"></button>
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
                    {#if selectedMachine.machine?.image_url}
                        <img 
                            src={selectedMachine.machine.image_url} 
                            alt={selectedMachine.machine?.name}
                            class="h-[180px] w-full object-cover rounded-sm"
                        />
                    {:else}
                        <div class="flex items-center justify-center h-full">
                            <i class="ki-filled ki-setting-3 text-4xl text-muted-foreground"></i>
                        </div>
                    {/if}
                </div>
                <span class="text-base font-medium text-mono">
                    {selectedMachine.machine?.name}
                </span>
                <div class="flex items-center gap-2 mb-2">
                    <span class={getMachineStatusBadgeClass(selectedMachine.status)}>
                        {machineStatuses[selectedMachine.status] || selectedMachine.status}
                    </span>
                </div>
                <div class="flex items-center gap-2 mb-2">
                    <span class="text-xs text-muted-foreground">Reliability:</span>
                    <div class="w-full bg-gray-200 rounded-full h-2">
                        <div class="kt-progress {selectedMachine.current_reliability > 0.7 ? 'kt-progress-primary' : selectedMachine.current_reliability > 0.4 ? 'kt-progress-warning' : 'kt-progress-destructive'}">
                            <div class="kt-progress-indicator" style="width: {(selectedMachine.current_reliability * 100).toFixed(0)}%"></div>
                        </div>
                    </div>
                    <span class="text-xs text-muted-foreground">{(selectedMachine.current_reliability * 100).toFixed(0)}%</span>
                </div>
                <div class="flex flex-col gap-2.5">
                    <div class="flex items-center gap-2.5">
                        <span class="text-xs font-normal text-foreground min-w-14 xl:min-w-24 shrink-0">
                            Model
                        </span>
                        <div>
                            <span class="text-xs font-medium text-foreground">
                                {selectedMachine.machine?.model}
                            </span>
                        </div>
                    </div>
                    <div class="flex items-center gap-2.5">
                        <span class="text-xs font-normal text-foreground min-w-14 xl:min-w-24 shrink-0">
                            Manufacturer
                        </span>
                        <div>
                            <span class="text-xs font-medium text-foreground">
                                {selectedMachine.machine?.manufacturer}
                            </span>
                        </div>
                    </div>
                </div>
                <!-- Timestamps Section -->
                <div class="border-t border-border pt-4">
                    <h3 class="text-sm font-semibold text-mono mb-3">Timestamps</h3>
                    <div class="space-y-2">
                        <div class="flex justify-between">
                            <span class="text-xs text-muted-foreground">Setup At:</span>
                            <span class="text-xs font-medium">{formatTimestamp(selectedMachine.setup_at)}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-xs text-muted-foreground">Last Maintenance:</span>
                            <span class="text-xs font-medium">{formatTimestamp(selectedMachine.last_maintenance_at)}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-xs text-muted-foreground">Last Broken:</span>
                            <span class="text-xs font-medium">{formatTimestamp(selectedMachine.last_broken_at)}</span>
                        </div>
                    </div>
                </div>
                <!-- Performance Section -->
                <div class="border-t border-border pt-4">
                    <h3 class="text-sm font-semibold text-mono mb-3">Performance</h3>
                    <div class="space-y-2">
                        <div class="flex justify-between">
                            <span class="text-xs text-muted-foreground">Operation Cost:</span>
                            <span class="text-xs font-medium">DZD {selectedMachine.machine?.operation_cost}/day</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-xs text-muted-foreground">Carbon Footprint:</span>
                            <span class="text-xs font-medium">{selectedMachine.machine?.carbon_footprint} kg CO2/unit</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-xs text-muted-foreground">Cost to Acquire:</span>
                            <span class="text-xs font-medium">DZD {selectedMachine.machine?.cost_to_acquire}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-xs text-muted-foreground">Quality Factor:</span>
                            <span class="text-xs font-medium">{selectedMachine.machine?.quality_factor}</span>
                        </div>
                    </div>
                </div>
                <!-- Speed Section -->
                <div class="border-t border-border pt-4">
                    <h3 class="text-sm font-semibold text-mono mb-3">Speed</h3>
                    <div class="space-y-2">
                        <div class="flex justify-between">
                            <span class="text-xs text-muted-foreground">Min Speed:</span>
                            <span class="text-xs font-medium">{selectedMachine.machine?.min_speed} units/day</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-xs text-muted-foreground">Average Speed:</span>
                            <span class="text-xs font-medium">{selectedMachine.machine?.avg_speed} units/day</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-xs text-muted-foreground">Max Speed:</span>
                            <span class="text-xs font-medium">{selectedMachine.machine?.max_speed} units/day</span>
                        </div>
                    </div>
                </div>
                <!-- Maintenance Section -->
                <div class="border-t border-border pt-4">
                    <h3 class="text-sm font-semibold text-mono mb-3">Maintenance</h3>
                    <div class="space-y-2">
                        <div class="flex justify-between">
                            <span class="text-xs text-muted-foreground">Maintenance Interval:</span>
                            <span class="text-xs font-medium">{selectedMachine.machine?.maintenance_interval_days} days</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-xs text-muted-foreground">Reliability Decay:</span>
                            <span class="text-xs font-medium">{selectedMachine.machine?.reliability_decay_days}%/day</span>
                        </div>
                    </div>
                </div>
            {/if}
        </div>
    </div>
</CompanyLayout> 