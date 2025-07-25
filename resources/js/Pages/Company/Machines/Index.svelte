<script>
    // --- Config & Constants ---
    import CompanyLayout from '../../Layouts/CompanyLayout.svelte';
    import { onMount, onDestroy, tick } from 'svelte';
    import Select2 from '../../Components/Forms/Select2.svelte';
    import { page } from '@inertiajs/svelte';

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

    let machines = [];
    let loading = true;
    let fetchInterval = null;

    let employeeSelectComponent = null;

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

    // Maintenance modal state
    let showMaintenanceModal = false;
    let maintenanceType = '';
    let maintenanceLoading = false;
    let maintenanceMachine = null;
    let maintenanceCost = 0;
    let maintenanceTime = 0;

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
        if(machines.length == 0) loading = true;

        try {
            const response = await fetch(route('company.machines.index'), {
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                }
            });
            const data = await response.json();
            machines = data.machines;
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
        availableProducts = [];
        for(let output of machine.machine?.outputs){
            if(output.product.is_researched){
                availableProducts = [...availableProducts, output.product];
            }
        }

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

    function openMaintenanceModal(machine, type) {
        maintenanceMachine = machine;
        maintenanceType = type;
        showMaintenanceModal = true;

        maintenanceCost = machine.maintenance_cost || 0;
        maintenanceTime = machine.maintenance_time_days || 1;

        const toggleButton = document.querySelector('[data-kt-modal-toggle="#maintenance_modal"]');
        if (toggleButton) toggleButton.click();
    }

    function closeMaintenanceModal() {
        const dismissButton = document.querySelector('[data-kt-modal-dismiss="#maintenance_modal"]');
        if (dismissButton) dismissButton.click();
        showMaintenanceModal = false;
        maintenanceMachine = null;
        maintenanceType = '';
        maintenanceCost = 0;
        maintenanceTime = 0;
    }

    async function startMaintenance() {
        if (!maintenanceMachine) return;
        maintenanceLoading = true;
        try {
            const response = await fetch(route('company.machines.start-maintenance', maintenanceMachine.id), {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content'),
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: JSON.stringify({})
            });
            if (response.ok) {
                const data = await response.json();
                showToast(data.message || 'Maintenance started successfully!', 'success');
                closeMaintenanceModal();
                fetchMachines();
            } else {
                const errorData = await response.json();
                showToast(errorData.message || 'Error starting maintenance. Please try again.', 'error');
            }
        } catch (error) {
            console.error('Error starting maintenance:', error);
            showToast('Network error. Please check your connection and try again.', 'error');
        } finally {
            maintenanceLoading = false;
        }
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


    // --- Svelte Lifecycle ---
    onMount(() => {
        fetchMachines();
        fetchInterval = setInterval(fetchMachines, 60000);
    });

    onDestroy(() => {
        if (fetchInterval) {
            clearInterval(fetchInterval);
        }
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
    <div class="kt-container-fixed">
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
                <div class="kt-card-content p-0">
                    {#if loading}
                        <div class="p-6">
                            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 p-4">
                                {#each Array(10) as _, i}
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
                                    You have not acquired any machines yet.
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
                                    <div class="kt-card kt-card-hover cursor-pointer" on:click={() => openMachineDrawer(companyMachine)}>
                                        <div class="kt-card-body p-4">
                                            <!-- Machine Image -->
                                            <div class="size-20 mb-4">
                                                {#if companyMachine.machine?.image_url}
                                                    <img 
                                                        class="rounded-lg w-full h-full object-cover bg-gray-100" 
                                                        src={companyMachine.machine.image_url}
                                                        alt={companyMachine.machine.name}
                                                    />
                                                {:else}
                                                    <div class="rounded-lg w-full h-full bg-accent/50 flex items-center justify-center">
                                                        <i class="ki-filled ki-setting-3 text-2xl text-muted-foreground"></i>
                                                    </div>
                                                {/if}
                                            </div>

                                            <!-- Machine Info -->
                                            <div class="mb-4 cursor-pointer">
                                                <h3 class="text-lg font-semibold text-mono mb-1">
                                                    {companyMachine.machine?.name}
                                                </h3>
                                                <p class="text-sm text-muted-foreground mb-2">
                                                    {companyMachine.machine?.model} - {companyMachine.machine?.manufacturer}
                                                </p>
                                            </div>

                                            <!-- Status and Reliability -->
                                            <div class="mb-3">
                                                <div class="flex items-center justify-between mb-2">
                                                    <span class="text-xs font-medium text-muted-foreground">Status</span>
                                                    <span class={getMachineStatusBadgeClass(companyMachine.status)}>
                                                        {companyMachine.status}
                                                    </span>
                                                </div>
                                                <div class="w-full bg-gray-200 rounded-full h-2">
                                                    <div class="kt-progress kt-progress-primary {companyMachine.current_reliability > 0.7 ? 'kt-progress-primary' : companyMachine.current_reliability > 0.4 ? 'kt-progress-warning' : 'kt-progress-destructive'}">
                                                        <div class="kt-progress-indicator" style="width: {companyMachine.current_reliability * 100}%"></div>
                                                    </div>
                                                </div>
                                            </div>

                                            <!-- Performance Details -->
                                            <div class="text-xs text-muted-foreground space-y-1 cursor-pointer" on:click={() => openMachineDrawer(companyMachine)}>
                                                <div class="flex justify-between">
                                                    <span>Speed:</span>
                                                    <span class="font-medium">{companyMachine.machine?.min_speed} - {companyMachine.machine?.max_speed} units/day</span>
                                                </div>
                                                <div class="flex justify-between">
                                                    <span>Quality Factor:</span>
                                                    <span class="font-medium">{companyMachine.quality_factor * 100}%</span>
                                                </div>
                                                <div class="flex justify-between">
                                                    <span>Operation Cost:</span>
                                                    <span class="font-medium">DZD {companyMachine.operations_cost}/day</span>
                                                </div>
                                                <div class="flex justify-between">
                                                    <span>Carbon Footprint:</span>
                                                    <span class="font-medium">{companyMachine.carbon_footprint} kg CO2/unit</span>
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

                                            {#if companyMachine.ongoing_production_order}
                                                <div class="mt-4 pt-3 border-t border-border">
                                                    <h3 class="text-sm font-semibold text-mono mb-4">Ongoing Production</h3>
                                                    <div class="flex items-center gap-3">
                                                            <div class="flex-shrink-0 relative">
                                                                {#if companyMachine.ongoing_production_order.product.image_url}
                                                                    <img 
                                                                        src={companyMachine.ongoing_production_order.product.image_url} 
                                                                        alt={companyMachine.ongoing_production_order.product.name}
                                                                        class="w-12 h-12 rounded-lg object-cover"
                                                                    />
                                                                {:else}
                                                                    <div class="w-12 h-12 rounded-lg bg-accent/50 flex items-center justify-center">
                                                                        <i class="ki-filled ki-abstract-26 text-lg text-muted-foreground"></i>
                                                                    </div>
                                                                {/if}
                                                            </div>
                                                            <div class="flex-1 min-w-0">
                                                                <h4 class="text-sm font-semibold text-mono mb-2 truncate">{companyMachine.ongoing_production_order.product.name}</h4>
                                                                <p class="text-xs text-muted-foreground mb-2">x{companyMachine.ongoing_production_order.quantity}</p>
                                                                <div class="w-full bg-gray-200 rounded-full h-2">
                                                                    <div class="kt-progress kt-progress-primary">
                                                                        <div class="kt-progress-indicator" style="width: {companyMachine.ongoing_production_order.producing_progress}%"></div>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                    </div>
                                                </div>
                                            {/if}
                                            
                                            <!-- Employee Assignment Section -->
                                            <div class="mt-4 pt-3 border-t border-border">
                                                <h3 class="text-sm font-semibold text-mono mb-4">Employee Assignment</h3>
                                                {#if companyMachine.employee}
                                                    <div class="kt-card">
                                                        <div class="kt-card-body p-3">
                                                            <div class="flex items-center gap-3">
                                                                <div class="flex-1 min-w-0">
                                                                    <h4 class="text-sm font-semibold text-mono mb-1 truncate">
                                                                        {companyMachine.employee.name}
                                                                    </h4>
                                                                    {#if companyMachine.employee.employee_profile}
                                                                        <p class="text-xs text-muted-foreground">
                                                                            {companyMachine.employee.employee_profile.name}
                                                                        </p>
                                                                    {/if}
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                {:else}
                                                    <div class="kt-card">
                                                        <div class="kt-card-body p-4">
                                                            <div class="flex flex-col items-center justify-center text-center">
                                                                <i class="ki-filled ki-profile-user text-2xl text-muted-foreground mb-2"></i>
                                                                <p class="text-xs text-muted-foreground">No employee assigned</p>
                                                            </div>
                                                        </div>
                                                    </div>
                                                {/if}
                                            </div>

                                            <!-- Action Buttons -->
                                            <div class="mt-4 pt-3 border-t border-border">
                                                {#if companyMachine.employee === null}
                                                    <div class="flex flex-col gap-2">
                                                        <button 
                                                            class="kt-btn kt-btn-primary w-full"
                                                            on:click|stopPropagation={() => openAssignModal(companyMachine)}
                                                        >
                                                            <i class="fa-solid fa-user-plus text-base"></i>
                                                            Assign Employee
                                                        </button>
                                                        {#if companyMachine.status === 'broken'}
                                                            <button 
                                                                class="kt-btn kt-btn-destructive w-full"
                                                                on:click|stopPropagation={() => openMaintenanceModal(companyMachine, 'corrective')}
                                                            >
                                                                <i class="fa-solid fa-wrench text-base"></i>
                                                                Start Corrective Maintenance
                                                            </button>
                                                        {:else if companyMachine.status === 'inactive'}
                                                            <button 
                                                                class="kt-btn kt-btn-outline kt-btn-info w-full"
                                                                on:click|stopPropagation={() => openMaintenanceModal(companyMachine, 'predictive')}
                                                            >
                                                                <i class="fa-solid fa-wrench text-base"></i>
                                                                Start Predictive Maintenance
                                                            </button>
                                                        {/if}
                                                    </div>
                                                {:else if companyMachine.status === 'broken'}
                                                    <div class="flex flex-col gap-2">
                                                        <button 
                                                            class="kt-btn kt-btn-destructive w-full"
                                                            on:click|stopPropagation={() => openMaintenanceModal(companyMachine, 'corrective')}
                                                        >
                                                            <i class="fa-solid fa-wrench text-base"></i>
                                                            Start Corrective Maintenance
                                                        </button>
                                                        <button 
                                                            class="kt-btn kt-btn-outline kt-btn-secondary w-full"
                                                            on:click|stopPropagation={() => openUnassignModal(companyMachine)}
                                                        >
                                                            <i class="fa-solid fa-user-minus text-base"></i>
                                                            Unassign Employee
                                                        </button>
                                                    </div>
                                                {:else if companyMachine.status === 'inactive'}
                                                    <div class="flex flex-col gap-2">
                                                        <button 
                                                            class="kt-btn kt-btn-primary w-full"
                                                            on:click|stopPropagation={() => openProductionModal(companyMachine)}
                                                        >
                                                            <i class="fa-solid fa-play text-base"></i>
                                                            Start Production
                                                        </button>
                                                        <button 
                                                            class="kt-btn kt-btn-outline kt-btn-info w-full"
                                                            on:click|stopPropagation={() => openMaintenanceModal(companyMachine, 'predictive')}
                                                        >
                                                            <i class="fa-solid fa-wrench text-base"></i>
                                                            Start Predictive Maintenance
                                                        </button>
                                                        <button 
                                                            class="kt-btn kt-btn-outline kt-btn-secondary w-full"
                                                            on:click|stopPropagation={() => openUnassignModal(companyMachine)}
                                                        >
                                                            <i class="fa-solid fa-user-minus text-base"></i>
                                                            Unassign Employee
                                                        </button>
                                                    </div>
                                                {:else}
                                                    <button 
                                                        class="kt-btn kt-btn-outline kt-btn-secondary w-full"
                                                        on:click|stopPropagation={() => openUnassignModal(companyMachine)}
                                                    >
                                                        <i class="fa-solid fa-user-minus text-base"></i>
                                                        Unassign Employee
                                                    </button>
                                                {/if}
                                            </div>
                                        </div>
                                    </div>
                                {/each}
                            </div>
                        </div>
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

    <!-- Hidden button to trigger maintenance modal -->
    <button style="display:none" data-kt-modal-toggle="#maintenance_modal"></button>

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
                        {selectedMachine.status}
                    </span>
                </div>

                <h3 class="text-sm font-semibold text-mono mb-3">Reliability</h3>
                <div class="space-y-2">
                    <div class="flex items-center gap-2">
                        <span class="text-xs font-medium">{(selectedMachine.current_reliability * 100).toFixed(0)}%</span>

                        <div class="w-full bg-gray-200 rounded-full h-2 mt-2">
                            <div class="kt-progress kt-progress-primary {selectedMachine.current_reliability > 0.7 ? 'kt-progress-primary' : selectedMachine.current_reliability > 0.4 ? 'kt-progress-warning' : 'kt-progress-destructive'}">
                                <div class="kt-progress-indicator" style="width: {(selectedMachine.current_reliability * 100)}%"></div>
                            </div>
                        </div>
                    </div>
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
                        {#if selectedMachine.last_maintenance_at}
                        <div class="flex justify-between">
                            <span class="text-xs text-muted-foreground">Last Maintenance:</span>
                            <span class="text-xs font-medium">{formatTimestamp(selectedMachine.last_maintenance_at)}</span>
                        </div>
                        {/if}
                        {#if selectedMachine.last_broken_at}
                        <div class="flex justify-between">
                            <span class="text-xs text-muted-foreground">Last Broken:</span>
                            <span class="text-xs font-medium">{formatTimestamp(selectedMachine.last_broken_at)}</span>
                        </div>
                        {/if}
                    </div>
                </div>
                <!-- Performance Section -->
                <div class="border-t border-border pt-4">
                    <h3 class="text-sm font-semibold text-mono mb-3">Performance</h3>
                    <div class="space-y-2">
                        <div class="flex justify-between">
                            <span class="text-xs text-muted-foreground">Speed:</span>
                            <span class="text-xs font-medium">{selectedMachine.machine?.min_speed} - {selectedMachine.machine?.max_speed} units/day</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-xs text-muted-foreground">Quality Factor:</span>
                            <span class="text-xs font-medium">{selectedMachine.machine?.quality_factor * 100}%</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-xs text-muted-foreground">Operation Cost:</span>
                            <span class="text-xs font-medium">DZD {selectedMachine.operations_cost}/day</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-xs text-muted-foreground">Carbon Footprint:</span>
                            <span class="text-xs font-medium">{selectedMachine.carbon_footprint} kg CO2/unit</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-xs text-muted-foreground">Acquisition Cost:</span>
                            <span class="text-xs font-medium">DZD {selectedMachine.machine?.cost_to_acquire}</span>
                        </div>
                    </div>
                </div>
                <!-- Maintenance Section -->
                <div class="border-t border-border pt-4">
                    <h3 class="text-sm font-semibold text-mono mb-3">Maintenance</h3>
                    <div class="space-y-2">
                        <div class="flex justify-between">
                            <span class="text-xs text-muted-foreground">Reliability Decay:</span>
                            <span class="text-xs font-medium">{selectedMachine.machine?.reliability_decay_days * 100}%/day</span>
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
                                min="0.001"
                                step="0.001"
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
                                        <span class="text-sm font-medium text-mono">{machineToProduce.machine?.min_speed} - {machineToProduce.machine?.max_speed} units/day</span>
                                    </div>
                                    <div class="flex justify-between items-center">
                                        <span class="text-sm font-normal text-secondary-foreground">Employee Efficiency Factor:</span>
                                        <span class="text-sm font-medium text-mono">x{machineToProduce.employee?.efficiency_factor}</span>
                                    </div>
                                    <div class="flex justify-between items-center">
                                        <span class="text-sm font-normal text-secondary-foreground">Quality Factor:</span>
                                        <span class="text-sm font-medium text-mono">{machineToProduce.machine?.quality_factor * 100}% (Expect as output: {productionQuantity * machineToProduce.machine?.quality_factor} units)</span>
                                    </div>
                                    <div class="flex justify-between items-center">
                                        <span class="text-sm font-normal text-secondary-foreground">Carbon Footprint:</span>
                                        <span class="text-sm font-medium text-mono">{machineToProduce.machine?.carbon_footprint * productionQuantity} kg CO2</span>
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

    <!-- Maintenance Modal -->
    <div class="kt-modal" data-kt-modal="true" id="maintenance_modal">
        <div class="kt-modal-content max-w-[600px] top-[5%]">
            <div class="kt-modal-header">
                <h3 class="kt-modal-title">
                    {maintenanceType === 'corrective' ? 'Start Corrective Maintenance' : 'Start Predictive Maintenance'}
                </h3>
                <button
                    type="button"
                    class="kt-modal-close"
                    aria-label="Close modal"
                    data-kt-modal-dismiss="#maintenance_modal"
                    on:click={closeMaintenanceModal}
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
                {#if maintenanceMachine}
                    <div class="space-y-4">
                        <!-- Machine Info -->
                        <div class="flex items-center gap-4">
                            <div class="flex-shrink-0">
                                {#if maintenanceMachine.machine?.image_url}
                                    <img 
                                        src={maintenanceMachine.machine.image_url} 
                                        alt={maintenanceMachine.machine.name}
                                        class="w-16 h-16 rounded-lg object-cover"
                                    />
                                {:else}
                                    <div class="w-16 h-16 rounded-lg bg-accent/50 flex items-center justify-center">
                                        <i class="ki-filled ki-setting-3 text-xl text-muted-foreground"></i>
                                    </div>
                                {/if}
                            </div>
                            <div class="flex-1">
                                <h4 class="font-semibold text-mono mb-1">{maintenanceMachine.machine?.name}</h4>
                                <p class="text-sm text-muted-foreground">{maintenanceMachine.machine?.model} - {maintenanceMachine.machine?.manufacturer}</p>
                            </div>
                        </div>
                        <!-- Maintenance Details -->
                        <div class="kt-card bg-accent/50">
                            <div class="kt-card-header px-5">
                                <h3 class="kt-card-title">Maintenance Details</h3>
                            </div>
                            <div class="kt-card-content px-5 py-4 space-y-2">
                                <div class="flex justify-between items-center">
                                    <span class="text-sm font-normal text-secondary-foreground">Cost:</span>
                                    <span class="text-sm font-medium text-mono">DZD {maintenanceCost}</span>
                                </div>
                                <div class="flex justify-between items-center">
                                    <span class="text-sm font-normal text-secondary-foreground">Estimated Time:</span>
                                    <span class="text-sm font-medium text-mono">{maintenanceTime} days</span>
                                </div>
                            </div>
                        </div>
                        <div class="text-sm text-muted-foreground">
                            Are you sure you want to start {maintenanceType === 'corrective' ? 'corrective' : 'predictive'} maintenance for this machine?
                        </div>
                    </div>
                {/if}
            </div>
            <div class="kt-modal-footer">
                <div></div>
                <div class="flex gap-4">
                    <button
                        class="kt-btn kt-btn-secondary"
                        data-kt-modal-dismiss="#maintenance_modal"
                        on:click={closeMaintenanceModal}
                    >
                        Cancel
                    </button>
                    <button 
                        class="kt-btn kt-btn-primary"
                        on:click={startMaintenance}
                        disabled={maintenanceLoading}
                    >
                        {maintenanceLoading ? 'Starting...' : 'Start Maintenance'}
                    </button>
                </div>
            </div>
        </div>
    </div>
</CompanyLayout> 