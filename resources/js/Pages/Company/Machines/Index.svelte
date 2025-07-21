<script>
    // --- Config & Constants ---
    import CompanyLayout from '../../Layouts/CompanyLayout.svelte';
    import Pagination from '../../Components/Pagination.svelte';
    import { onMount, tick } from 'svelte';
    import { page } from '@inertiajs/svelte';
    import Select2 from '../../Components/Forms/Select2.svelte';

    // Breadcrumbs and page title
    const breadcrumbs = [
        {
            title: 'Machines',
            url: route('company.machines.index'),
            active: false
        },
        {
            title: 'Index',
            url: route('company.machines.index'),
            active: true
        }
    ];
    const pageTitle = 'My Machines';

    // --- Reactive Variables ---
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

    // --- Component References ---
    let productSelectComponent;
    let employeeProfileSelectComponent;
    let employeeSelectComponent;

    // --- Modal/Drawer State ---
    let selectedMachine = null;
    let showMachineDrawer = false;
    let showAssignModal = false;
    let assignEmployeeId = null;
    let loadingAssign = false;
    let assignEmployeeData = null;
    let selectedEmployeeId = '';
// Unassign modal state
let showUnassignModal = false;
let unassigning = false;
let machineToUnassign = null;

// Production modal state
let showProductionModal = false;
let startingProduction = false;
let machineToProduce = null;
let selectedProductId = '';
let productionQuantity = 1;
let availableProducts = [];

    // --- Machine Status Mapping ---
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

    // --- Fetch Functions ---
    async function fetchMachines() {
        loading = true;
        try {
            const params = new URLSearchParams({
                page: currentPage,
                perPage: perPage,
                search: search
            });
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

    // --- Search/Filter Handlers ---
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

    // --- Pagination Handlers ---
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

    // --- Modal/Drawer Handlers ---
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
    function openAssignModal(machine) {
        selectedMachine = machine;
        assignEmployeeData = {
            machine: machine,
            employee: machine.employee || null,
        };
        selectedEmployeeId = '';
        showAssignModal = true;
        const toggleButton = document.querySelector('[data-kt-modal-toggle="#assign_employee_modal"]');
        if (toggleButton) {
            toggleButton.click();
        }
    }
    function closeAssignModal() {
        const dismissButton = document.querySelector('[data-kt-modal-dismiss="#assign_employee_modal"]');
        if (dismissButton) {
            dismissButton.click();
        }
        showAssignModal = false;
        assignEmployeeData = null;
        selectedEmployeeId = '';
    }
    
    function openUnassignModal(machine) {
        machineToUnassign = machine;
        showUnassignModal = true;
        const toggleButton = document.querySelector('[data-kt-modal-toggle="#unassign_employee_modal"]');
        if (toggleButton) toggleButton.click();
    }

    function closeUnassignModal() {
        const dismissButton = document.querySelector('[data-kt-modal-dismiss="#unassign_employee_modal"]');
        if (dismissButton) dismissButton.click();
        showUnassignModal = false;
        machineToUnassign = null;
    }

    function openProductionModal(machine) {
        machineToProduce = machine;
        selectedProductId = '';
        productionQuantity = 1;
        // Get available products from the machine
        availableProducts = machine.machine?.products?.filter(product => product.is_researched) || [];
        showProductionModal = true;
        const toggleButton = document.querySelector('[data-kt-modal-toggle="#production_modal"]');
        if (toggleButton) toggleButton.click();
    }

    function closeProductionModal() {
        const dismissButton = document.querySelector('[data-kt-modal-dismiss="#production_modal"]');
        if (dismissButton) dismissButton.click();
        showProductionModal = false;
        machineToProduce = null;
        selectedProductId = '';
        productionQuantity = 1;
        availableProducts = [];
    }

    // --- Action Handlers ---
    async function assignEmployee() {
        if (!selectedMachine || !selectedEmployeeId) return;
        try {
            const response = await fetch(route('company.machines.assign-employee', selectedMachine.id), {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content'),
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: JSON.stringify({
                    employee_id: selectedEmployeeId
                })
            });
            if (response.ok) {
                const data = await response.json();
                showToast(data.message || 'Employee assigned successfully!', 'success');
                closeAssignModal();
                selectedMachine = null;
                assignEmployeeData = null;
                fetchMachines();
            } else {
                const errorData = await response.json();
                showToast(errorData.message || 'Error assigning employee. Please try again.', 'error');
            }
        } catch (error) {
            console.error('Error assigning employee:', error);
            showToast('Network error. Please check your connection and try again.', 'error');
        }
    }
    async function unassignEmployee() {
        if (!machineToUnassign) return;
        unassigning = true;
        try {
            const response = await fetch(route('company.machines.unassign-employee', machineToUnassign.id), {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content'),
                    'X-Requested-With': 'XMLHttpRequest'
                }
            });
            if (response.ok) {
                const data = await response.json();
                showToast(data.message || 'Employee unassigned successfully!', 'success');
                closeUnassignModal();
                fetchMachines();
            } else {
                const errorData = await response.json();
                showToast(errorData.message || 'Error unassigning employee. Please try again.', 'error');
            }
        } catch (error) {
            console.error('Error unassigning employee:', error);
            showToast('Network error. Please check your connection and try again.', 'error');
        } finally {
            unassigning = false;
        }
    }

    async function startProduction() {
        if (!machineToProduce || !selectedProductId || !productionQuantity) return;
        startingProduction = true;
        try {
            const response = await fetch(route('company.production-orders.store', machineToProduce.id), {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content'),
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: JSON.stringify({
                    product_id: selectedProductId,
                    quantity: productionQuantity
                })
            });
            if (response.ok) {
                const data = await response.json();
                showToast(data.message || 'Production started successfully!', 'success');
                closeProductionModal();
                fetchMachines();
            } else {
                const errorData = await response.json();
                showToast(errorData.message || 'Error starting production. Please try again.', 'error');
            }
        } catch (error) {
            console.error('Error starting production:', error);
            showToast('Network error. Please check your connection and try again.', 'error');
        } finally {
            startingProduction = false;
        }
    }

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
    function formatTimestamp(timestamp) {
        if (!timestamp) return 'N/A';
        return new Date(timestamp).toLocaleString();
    }

    // --- Svelte Lifecycle ---
    onMount(() => {
        fetchMachines();
    });
    export let success;
    $: if (success) {
        showToast(success, 'success');
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
                                                {#if companyMachine.employee}
                                                    <div class="flex justify-between">
                                                        <span>Assigned Employee:</span>
                                                        <span class="font-medium">{companyMachine.employee.name}</span>
                                                    </div>
                                                {/if}
                                            </div>

                                            <!-- Action Buttons -->
                                            <div class="flex items-center justify-between mt-4 pt-3 border-t border-border">
                                                <button 
                                                    class="kt-btn kt-btn-sm kt-btn-outline kt-btn-primary"
                                                    on:click={() => openMachineDrawer(companyMachine)}
                                                >
                                                    <i class="ki-filled ki-eye text-sm"></i>
                                                    View Details
                                                </button>
                                                {#if companyMachine.employee === null}
                                                    <button 
                                                        class="kt-btn kt-btn-sm kt-btn-primary"
                                                        on:click={() => openAssignModal(companyMachine)}
                                                    >
                                                        <i class="ki-filled ki-setting-3 text-sm"></i>
                                                        Assign Employee
                                                    </button>
                                                {:else if companyMachine.status === 'inactive' }
                                                    <button 
                                                        class="kt-btn kt-btn-sm kt-btn-primary"
                                                        on:click={() => openProductionModal(companyMachine)}
                                                    >
                                                        <i class="ki-filled ki-play text-sm"></i>
                                                        Start Production
                                                    </button>                      
                                                {:else}
                                                    <button 
                                                        class="kt-btn kt-btn-sm kt-btn-destructive"
                                                        on:click={() => openUnassignModal(companyMachine)}
                                                    >
                                                        <i class="ki-filled ki-cross text-sm"></i>
                                                        Unassign Employee
                                                    </button>
                                                {/if}
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

    <!-- Hidden button to trigger modal -->
    <button style="display:none" data-kt-modal-toggle="#assign_employee_modal"></button>

    <!-- Hidden button to trigger modal -->
    <button style="display:none" data-kt-modal-toggle="#unassign_employee_modal"></button>        

    <!-- Hidden button to trigger production modal -->
    <button style="display:none" data-kt-modal-toggle="#production_modal"></button>

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
                <!-- Employee Assignment Section -->
                <div class="border-t border-border pt-4">
                    <h3 class="text-sm font-semibold text-mono mb-3">Assigned Employee</h3>
                    {#if selectedMachine.employee}
                        <div class="flex flex-col gap-1">
                            <span class="text-xs font-medium">{selectedMachine.employee.name}</span>
                            <span class="text-xs text-muted-foreground">{selectedMachine.employee.employee_profile?.name}</span>
                        </div>
                    {:else}
                        <span class="text-xs text-muted-foreground">No employee assigned</span>
                    {/if}
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

    <!-- Assign Employee Confirmation Modal -->
    <div class="kt-modal" data-kt-modal="true" id="assign_employee_modal">
        <div class="kt-modal-content max-w-[600px] top-[5%]">
            <div class="kt-modal-header">
                <h3 class="kt-modal-title">Assign Employee</h3>
                <button
                    type="button"
                    class="kt-modal-close"
                    aria-label="Close modal"
                    data-kt-modal-dismiss="#assign_employee_modal"
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
                <label class="text-sm font-medium text-mono mb-2 block">Select Employee</label>
                <Select2
                    bind:this={employeeSelectComponent}
                    id="employee-select"
                    placeholder="Choose an employee..."
                    bind:value={selectedEmployeeId}
                    on:select={e => selectedEmployeeId = e.detail.value}
                    on:clear={() => selectedEmployeeId = ''}
                    ajax={{
                        url: route('company.employees.index') + '?status=active',
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
                                results: data.employees.map(employee => ({
                                    id: employee.id,
                                    text: employee.name,
                                    name: employee.name,
                                    profile: employee.employee_profile?.name
                                }))
                            };
                        },
                        cache: true
                    }}
                    templateResult={function(data) {
                        if (data.loading) return data.text;
                        const $elem = globalThis.$('<div class="flex flex-col">' +
                            '<span class="font-medium">' + data.name + '</span>' +
                            (data.profile ? '<span class="kt-badge kt-badge-outline kt-badge-info kt-badge-sm w-fit mt-1">' + data.profile + '</span>' : '') +
                            '</div>');
                        return $elem;
                    }}
                    templateSelection={function(data) {
                        if (!data.id) return data.text;
                        return data.name + (data.profile ? ' (' + data.profile + ')' : '');
                    }}
                />
            </div>
            <div class="kt-modal-footer">
                <div></div>
                <div class="flex gap-4">
                    <button
                        class="kt-btn kt-btn-secondary"
                        data-kt-modal-dismiss="#assign_employee_modal"
                        on:click={closeAssignModal}
                    >
                        Cancel
                    </button>
                    <button 
                        class="kt-btn kt-btn-primary"
                        on:click={assignEmployee}
                        disabled={!selectedEmployeeId || loadingAssign}
                    >
                        {loadingAssign ? 'Assigning...' : 'Assign Employee'}
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Unassign Employee Confirmation Modal -->
    <div class="kt-modal" data-kt-modal="true" id="unassign_employee_modal">
        <div class="kt-modal-content max-w-[600px] top-[5%]">
            <div class="kt-modal-header">
                <h3 class="kt-modal-title">Unassign Employee</h3>
                <button
                    type="button"
                    class="kt-modal-close"
                    aria-label="Close modal"
                    data-kt-modal-dismiss="#unassign_employee_modal"
                    on:click={closeUnassignModal}
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
                <p>Are you sure you want to unassign the employee from this machine?</p>
            </div>
            <div class="kt-modal-footer">
                <div></div>
                <div class="flex gap-4">
                    <button
                        class="kt-btn kt-btn-secondary"
                        data-kt-modal-dismiss="#unassign_employee_modal"
                        on:click={closeUnassignModal}
                    >
                        Cancel
                    </button>
                    <button 
                        class="kt-btn kt-btn-destructive"
                        on:click={unassignEmployee}
                        disabled={unassigning}
                    >
                        {unassigning ? 'Unassigning...' : 'Unassign Employee'}
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Production Modal -->
    <div class="kt-modal" data-kt-modal="true" id="production_modal">
        <div class="kt-modal-content max-w-[600px] top-[5%]">
            <div class="kt-modal-header">
                <h3 class="kt-modal-title">Start Production</h3>
                <button
                    type="button"
                    class="kt-modal-close"
                    aria-label="Close modal"
                    data-kt-modal-dismiss="#production_modal"
                    on:click={closeProductionModal}
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
                {#if machineToProduce}
                    <div class="space-y-4">
                        <!-- Machine Info -->
                        <div class="flex items-center gap-4">
                            <div class="flex-shrink-0">
                                {#if machineToProduce.machine?.image_url}
                                    <img 
                                        src={machineToProduce.machine.image_url} 
                                        alt={machineToProduce.machine.name}
                                        class="w-16 h-16 rounded-lg object-cover"
                                    />
                                {:else}
                                    <div class="w-16 h-16 rounded-lg bg-accent/50 flex items-center justify-center">
                                        <i class="ki-filled ki-setting-3 text-xl text-muted-foreground"></i>
                                    </div>
                                {/if}
                            </div>
                            <div class="flex-1">
                                <h4 class="font-semibold text-mono mb-1">{machineToProduce.machine?.name}</h4>
                                <p class="text-sm text-muted-foreground">{machineToProduce.machine?.model} - {machineToProduce.machine?.manufacturer}</p>
                            </div>
                        </div>

                        <!-- Product Selection -->
                        <div class="space-y-2">
                            <label class="text-sm font-medium text-mono">Select Product</label>
                            {#if availableProducts.length > 0}
                                <select 
                                    class="kt-select w-full" 
                                    bind:value={selectedProductId}
                                >
                                    <option value="">Choose a product...</option>
                                    {#each availableProducts as product}
                                        <option value={product.id}>{product.name} ({product.type_name})</option>
                                    {/each}
                                </select>
                            {:else}
                                <div class="kt-card bg-muted/50">
                                    <div class="kt-card-body p-4">
                                        <div class="flex flex-col items-center justify-center text-center">
                                            <i class="ki-filled ki-abstract-26 text-2xl text-muted-foreground mb-2"></i>
                                            <p class="text-sm text-muted-foreground">No products available for this machine</p>
                                        </div>
                                    </div>
                                </div>
                            {/if}
                        </div>

                        <!-- Quantity Input -->
                        <div class="space-y-2">
                            <label class="text-sm font-medium text-mono">Production Quantity</label>
                            <input 
                                type="number" 
                                class="kt-input w-full" 
                                bind:value={productionQuantity}
                                min="1"
                                step="1"
                                placeholder="Enter quantity"
                            />
                        </div>

                        <!-- Production Details -->
                        {#if selectedProductId && productionQuantity > 0}
                            <div class="kt-card bg-accent/50">
                                <div class="kt-card-header px-5">
                                    <h3 class="kt-card-title">Production Details</h3>
                                </div>
                                <div class="kt-card-content px-5 py-4 space-y-2">
                                    <div class="flex justify-between items-center">
                                        <span class="text-sm font-normal text-secondary-foreground">Speed:</span>
                                        <span class="text-sm font-medium text-mono">{machineToProduce.machine?.avg_speed} units/day</span>
                                    </div>
                                    <div class="flex justify-between items-center">
                                        <span class="text-sm font-normal text-secondary-foreground">Quality Factor:</span>
                                        <span class="text-sm font-medium text-mono">{machineToProduce.machine?.quality_factor}%</span>
                                    </div>
                                    <div class="flex justify-between items-center">
                                        <span class="text-sm font-normal text-secondary-foreground">Carbon Footprint:</span>
                                        <span class="text-sm font-medium text-mono">{machineToProduce.machine?.carbon_footprint} kg CO2/unit</span>
                                    </div>
                                </div>
                            </div>
                        {/if}
                    </div>
                {/if}
            </div>
            <div class="kt-modal-footer">
                <div></div>
                <div class="flex gap-4">
                    <button
                        class="kt-btn kt-btn-secondary"
                        data-kt-modal-dismiss="#production_modal"
                        on:click={closeProductionModal}
                    >
                        Cancel
                    </button>
                    <button 
                        class="kt-btn kt-btn-primary"
                        on:click={startProduction}
                        disabled={!selectedProductId || !productionQuantity || startingProduction}
                    >
                        {startingProduction ? 'Starting Production...' : 'Start Production'}
                    </button>
                </div>
            </div>
        </div>
    </div>
</CompanyLayout> 