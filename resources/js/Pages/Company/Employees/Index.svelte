<script>
    import CompanyLayout from '../../Layouts/CompanyLayout.svelte';
    import Pagination from '../../Components/Pagination.svelte';
    import { onMount, onDestroy, tick } from 'svelte';
    import { page } from '@inertiajs/svelte'
    import Select2 from '../../Components/Forms/Select2.svelte';

    // Define breadcrumbs for this page
    const breadcrumbs = [
        {
            title: 'Employees',
            url: route('company.employees.index'),
            active: true
        }
    ];
    
    const pageTitle = 'Employees Management';

    // Reactive variables
    let employees = [];
    let loading = true;
    let fetchInterval = null;

    // Drawer state
    let selectedEmployee = null;
    let showEmployeeDrawer = false;

    // Modal state
    let showPromoteModal = false;
    let promoteData = null;
    let newSalary = 0;
    let loadingPromote = false;
    let showFireModal = false;
    let fireData = null;
    let loadingFire = false;

    // Filter variables
    let employeeProfileFilter = '';
    let statusFilter = '';
    let currentMoodMin = '';
    let currentMoodMax = '';
    let efficiencyFactorMin = '';
    let efficiencyFactorMax = '';
    let moodDecayRateMin = '';
    let moodDecayRateMax = '';

    // Select2 component references
    let employeeProfileSelectComponent;


    // Employee status badge colors
    function getEmployeeStatusBadgeClass(status) {
        switch(status) {
            case 'active':
                return 'kt-badge kt-badge-outline kt-badge-success kt-badge-sm';
            case 'applied':
                return 'kt-badge kt-badge-outline kt-badge-warning kt-badge-sm';
            case 'fired':
                return 'kt-badge kt-badge-outline kt-badge-danger kt-badge-sm';
            case 'resigned':
                return 'kt-badge kt-badge-outline kt-badge-secondary kt-badge-sm';
            default:
                return 'kt-badge kt-badge-outline kt-badge-sm';
        }
    }

    // Fetch employees data
    async function fetchEmployees() {
        if(employees.length == 0) loading = true;

        try {
            const response = await fetch(route('company.employees.index'), {
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                }
            });
            
            const data = await response.json();
            employees = data.employees;
            
            // Wait for DOM to update, then initialize menus
            await tick();
            if (window.KTMenu) {
                window.KTMenu.init();
            }
        } catch (error) {
            console.error('Error fetching employees:', error);
        } finally {
            loading = false;
        }
    }

    // Open employee drawer
    function openEmployeeDrawer(employee) {
        selectedEmployee = employee;
        showEmployeeDrawer = true;
        
        // Show drawer
        const toggleButton = document.querySelector('[data-kt-drawer-toggle="#employee_drawer"]');
        if (toggleButton) {
            toggleButton.click();
        }
    }

    // Close employee drawer
    function closeEmployeeDrawer() {
        showEmployeeDrawer = false;
        selectedEmployee = null;
    }

    // Open promote modal
    function openPromoteModal(employee) {
        if (!employee) {
            showToast('Please select an employee to promote', 'error');
            return;
        }

        promoteData = {
            employee: employee,
            currentSalary: employee.salary_month,
            efficiencyFactor: employee.efficiency_factor,
            currentMood: employee.current_mood
        };

        newSalary = employee.salary_month;
        showPromoteModal = true;
        
        // Show modal
        const toggleButton = document.querySelector('[data-kt-modal-toggle="#promote_modal"]');
        if (toggleButton) {
            toggleButton.click();
        }
    }

    // Close promote modal
    function closePromoteModal() {
        // Simulate button click to use KT framework logic
        const dismissButton = document.querySelector('[data-kt-modal-dismiss="#promote_modal"]');
        if (dismissButton) {
            dismissButton.click();
        }
        
        showPromoteModal = false;
        promoteData = null;
        newSalary = 0;
    }

    // Promote employee
    async function promoteEmployee() {
        if (!promoteData || newSalary <= promoteData.currentSalary) {
            showToast('Please enter a valid salary higher than the current salary', 'error');
            return;
        }

        loadingPromote = true;
        try {
            const response = await fetch(route('company.employees.promote', promoteData.employee.id), {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content'),
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: JSON.stringify({
                    new_salary: newSalary
                })
            });

            if (response.ok) {
                const data = await response.json();
                
                // Show success toast
                showToast(data.message || 'Employee promoted successfully!', 'success');
                
                // Close modal
                closePromoteModal();
                
                // Refresh the employees list
                fetchEmployees();
            } else {
                const errorData = await response.json();
                showToast(errorData.message || 'Error promoting employee. Please try again.', 'error');
            }
        } catch (error) {
            console.error('Error promoting employee:', error);
            showToast('Network error. Please check your connection and try again.', 'error');
        } finally {
            loadingPromote = false;
        }
    }

    // Open fire modal
    function openFireModal(employee) {
        if (!employee) {
            showToast('Please select an employee to fire', 'error');
            return;
        }

        fireData = {
            employee: employee,
            currentSalary: employee.salary_month,
            efficiencyFactor: employee.efficiency_factor,
            currentMood: employee.current_mood,
            employeeProfile: employee.employee_profile?.name || 'Unknown'
        };

        showFireModal = true;
        
        // Show modal
        const toggleButton = document.querySelector('[data-kt-modal-toggle="#fire_modal"]');
        if (toggleButton) {
            toggleButton.click();
        }
    }

    // Close fire modal
    function closeFireModal() {
        // Simulate button click to use KT framework logic
        const dismissButton = document.querySelector('[data-kt-modal-dismiss="#fire_modal"]');
        if (dismissButton) {
            dismissButton.click();
        }
        
        showFireModal = false;
        fireData = null;
    }

    // Fire employee
    async function fireEmployee() {
        if (!fireData) return;

        loadingFire = true;
        try {
            const formData = new FormData();
            formData.append('_token', document.querySelector('meta[name="csrf-token"]')?.getAttribute('content'));

            const response = await fetch(route('company.employees.fire', { employee: fireData.employee.id }), {
                method: 'POST',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: formData
            });

            if (response.ok) {
                // Show success toast
                showToast("Employee fired successfully!", 'success');
                
                // Close modal
                closeFireModal();
                
                // Refresh the employees list
                fetchEmployees();
            } else {
                const errorData = await response.json().catch(() => ({}));
                const errorMessage = errorData.message || 'Error firing employee. Please try again.';
                showToast(errorMessage, 'error');
            }
        } catch (error) {
            console.error('Error firing employee:', error);
            showToast('Network error. Please check your connection and try again.', 'error');
        } finally {
            loadingFire = false;
        }
    }

    onMount(() => {
        fetchEmployees();
        fetchInterval = setInterval(fetchEmployees, 60000);
    });

    onDestroy(() => {
        if (fetchInterval) {
            clearInterval(fetchInterval);
        }
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
    <div class="kt-container-fixed">
        <div class="grid gap-5 lg:gap-7.5">
            <!-- Employee Header -->
            <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4">
                <div class="flex flex-col gap-1">
                    <h1 class="text-2xl font-bold text-mono">Employees Management</h1>
                    <p class="text-sm text-secondary-foreground">
                        Manage your company employees and their performance
                    </p>
                </div>
                <div class="flex items-center gap-3">
                    <a href="{route('company.employees.recruit-page')}" class="kt-btn kt-btn-primary">
                        <i class="ki-filled ki-plus text-base"></i>
                        Recruit Employees
                    </a>
                </div>                      
            </div>

            <!-- Employees Grid -->
            <div class="kt-card">
                <div class="kt-card-content p-0">
                    {#if loading}
                        <!-- Loading skeleton -->
                        <div class="p-6">
                            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6 p-4">
                                {#each Array(10) as _, i}
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
                    {:else if employees.length === 0}
                        <!-- Empty state -->
                        <div class="p-10">
                            <div class="flex flex-col items-center justify-center text-center">
                                <div class="mb-4">
                                    <i class="ki-filled ki-user text-4xl text-muted-foreground"></i>
                                </div>
                                <h3 class="text-lg font-semibold text-mono mb-2">No employees found</h3>
                                <p class="text-sm text-secondary-foreground mb-4">
                                    Get started by recruiting your first employee.
                                </p>
                                <a href="{route('company.employees.recruit-page')}" class="kt-btn kt-btn-primary">
                                    <i class="ki-filled ki-plus text-base"></i>
                                    Recruit First Employee
                                </a>
                            </div>
                        </div>
                    {:else}
                        <!-- Employees Grid -->
                        <div class="p-6">
                            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6 p-4">
                                {#each employees as employee}
                                    <div class="kt-card kt-card-hover cursor-pointer" on:click={() => openEmployeeDrawer(employee)}>
                                        <div class="kt-card-content flex flex-col items-center lg:pt-10">
                                            <div class="mb-3">
                                                <div class="size-20 relative">
                                                    <div class="w-20 h-20 rounded-full bg-accent/50 flex items-center justify-center">
                                                        <i class="ki-filled ki-user text-2xl text-muted-foreground"></i>
                                                    </div>
                                                    <div class="w-2.5 h-2.5 rounded-full ring-2 ring-background absolute bottom-0.5 start-16 transform -translate-y-1/2 {employee.status === 'active' ? 'bg-green-500' : employee.status === 'applied' ? 'bg-yellow-500' : employee.status === 'fired' ? 'bg-red-500' : 'bg-gray-500'}"></div>
                                                </div>
                                            </div>
                                            <div class="flex items-center justify-center gap-1.5 mb-3">
                                                <a class="hover:text-primary text-center text-base leading-5 font-medium text-mono" href="#">
                                                    {employee.name} 
                                                </a>
                                            </div>

                                            <div class="flex items-center gap-2.5 mb-2">
                                                <span class="kt-badge kt-badge-outline kt-badge-sm">
                                                    {employee.employee_profile?.name || 'Unknown'}
                                                </span>

                                                <span class={getEmployeeStatusBadgeClass(employee.status)}>
                                                    {employee.status}
                                                </span>
                                            </div>

                                            <div class="flex flex-col gap-1 text-xs text-secondary-foreground">
                                                <div class="flex justify-center gap-1">
                                                    <i class="fa-solid fa-dollar-sign text-green-500"></i>
                                                    <span>DZD {employee.salary_month}</span>
                                                </div>
                                                <div class="flex justify-center gap-1">
                                                    <i class="fa-solid fa-chart-line text-primary"></i>
                                                    <span>{employee.efficiency_factor}x Efficiency</span>
                                                </div>
                                                <div class="flex justify-center gap-1">
                                                    <i class="fa-solid fa-heart text-destructive"></i>
                                                    <span>{(employee.current_mood * 100).toFixed(0)}% Mood</span>
                                                </div>
                                            </div>

                                            <!-- Mood Progress Bar -->
                                            <div class="w-full bg-gray-200 rounded-full h-2 mt-2">
                                                <div class="kt-progress kt-progress-primary {employee.current_mood > 0.7 ? 'kt-progress-success' : employee.current_mood > 0.4 ? 'kt-progress-warning' : 'kt-progress-destructive'}">
                                                    <div class="kt-progress-indicator" style="width: {(employee.current_mood * 100)}%"></div>
                                                </div>
                                            </div>

                                            <!-- Machine Assignment -->
                                            {#if employee.company_machine}
                                                <div class="flex items-center gap-1 text-xs text-secondary-foreground mt-2">
                                                    <i class="ki-filled ki-gear text-blue-500"></i>
                                                    <span>{employee.company_machine.machine.name}</span>
                                                </div>
                                            {:else}
                                                <div class="flex items-center gap-1 text-xs text-secondary-foreground mt-2">
                                                    <i class="ki-filled ki-cross text-gray-500"></i>
                                                    <span>No Machine</span>
                                                </div>
                                            {/if}

                                            <!-- Action Buttons -->
                                            <div class="flex items-center gap-2 mt-4">
                                                {#if employee.status === 'active'}
                                                    <button 
                                                        class="kt-btn kt-btn-sm kt-btn-primary"
                                                        on:click|stopPropagation={() => openPromoteModal(employee)}
                                                    >
                                                        <i class="ki-filled ki-arrow-up text-sm"></i>
                                                        Promote
                                                    </button>
                                                    <button 
                                                        class="kt-btn kt-btn-sm kt-btn-destructive"
                                                        on:click|stopPropagation={() => openFireModal(employee)}
                                                    >
                                                        <i class="ki-filled ki-trash text-sm"></i>
                                                        Fire
                                                    </button>
                                                {:else if employee.status === 'applied'}
                                                    <a 
                                                        class="kt-btn kt-btn-sm kt-btn-success"
                                                        href={route('company.employees.recruit-page')}
                                                    >
                                                        <i class="ki-filled ki-check text-sm"></i>
                                                        Recruit
                                                    </a>
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
    <!-- End of Container -->

    <!-- Hidden button to trigger drawer -->
    <button style="display:none" data-kt-drawer-toggle="#employee_drawer"></button>

    <!-- Employee Details Drawer -->
    <div class="hidden kt-drawer kt-drawer-end card flex-col max-w-[90%] w-[450px] top-5 bottom-5 end-5 rounded-xl border border-border" data-kt-drawer="true" data-kt-drawer-container="body" id="employee_drawer">
        <div class="flex items-center justify-between gap-2.5 text-sm text-mono font-semibold px-5 py-2.5 border-b border-b-border">
            Employee Details
            <button class="kt-btn kt-btn-sm kt-btn-icon kt-btn-dim shrink-0" data-kt-drawer-dismiss="true" on:click={closeEmployeeDrawer}>
                <i class="ki-filled ki-cross"></i>
            </button>
        </div>
        
        <div class="kt-card-content flex flex-col space-y-3 p-5 kt-scrollable-y-auto">
            {#if selectedEmployee}
                <!-- Employee Name -->
                <span class="text-base font-medium text-mono">
                    {selectedEmployee.name}
                </span>

                <!-- Employee Status -->
                <div class="flex items-center gap-2">
                    <span class={getEmployeeStatusBadgeClass(selectedEmployee.status)}>
                        {selectedEmployee.status}
                    </span>
                </div>

                <!-- Employee Details -->
                <div class="flex flex-col gap-2.5">
                    <div class="flex items-center gap-2.5">
                        <span class="text-xs font-normal text-foreground min-w-14 xl:min-w-24 shrink-0">
                            Profile
                        </span>
                        <div>
                            <span class="text-xs font-medium text-foreground">
                                {selectedEmployee.employee_profile?.name || 'Unknown'}
                            </span>
                        </div>
                    </div>
                    <div class="flex items-center gap-2.5">
                        <span class="text-xs font-normal text-foreground min-w-14 xl:min-w-24 shrink-0">
                            Salary
                        </span>
                        <div>
                            <span class="text-xs font-medium text-foreground">
                                DZD {selectedEmployee.salary_month}
                            </span>
                        </div>
                    </div>
                    <div class="flex items-center gap-2.5">
                        <span class="text-xs font-normal text-foreground min-w-14 xl:min-w-24 shrink-0">
                            Efficiency
                        </span>
                        <div>
                            <span class="kt-badge kt-badge-{selectedEmployee.efficiency_factor > 1 ? 'success' : 'warning'} kt-badge-sm">
                                {selectedEmployee.efficiency_factor}x
                            </span>
                        </div>
                    </div>
                    <div class="flex items-center gap-2.5">
                        <span class="text-xs font-normal text-foreground min-w-14 xl:min-w-24 shrink-0">
                            Mood Decay
                        </span>
                        <div>
                            <span class="text-xs font-medium text-foreground">
                                {(selectedEmployee.mood_decay_rate_days * 100).toFixed(2)}%/day
                            </span>
                        </div>
                    </div>
                </div>

                <!-- Current Mood Section -->
                <div class="border-t border-border pt-4">
                    <h3 class="text-sm font-semibold text-mono mb-3">Current Mood</h3>
                    <div class="space-y-2">
                        <div class="flex items-center gap-2">
                            <span class="text-xs font-medium">{(selectedEmployee.current_mood * 100).toFixed(0)}%</span>

                            <div class="w-full bg-gray-200 rounded-full h-2 mt-2">
                                <div class="kt-progress kt-progress-primary {selectedEmployee.current_mood > 0.7 ? 'kt-progress-success' : selectedEmployee.current_mood > 0.4 ? 'kt-progress-warning' : 'kt-progress-destructive'}">
                                    <div class="kt-progress-indicator" style="width: {(selectedEmployee.current_mood * 100)}%"></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Timeline Section -->
                <div class="border-t border-border pt-4">
                    <h3 class="text-sm font-semibold text-mono mb-3">Employee Timeline</h3>
                    <div class="space-y-3">
                        {#if selectedEmployee.applied_at}
                            <div class="flex items-center gap-3">
                                <div class="flex-1">
                                    <p class="text-xs font-medium text-mono">Applied</p>
                                    <p class="text-xs text-muted-foreground">{formatTimestamp(selectedEmployee.applied_at)}</p>
                                </div>
                            </div>
                        {/if}
                        
                        {#if selectedEmployee.hired_at}
                            <div class="flex items-center gap-3">
                                <div class="flex-1">
                                    <p class="text-xs font-medium text-mono">Hired</p>
                                    <p class="text-xs text-muted-foreground">{formatTimestamp(selectedEmployee.hired_at)}</p>
                                </div>
                            </div>
                        {/if}
                        
                        {#if selectedEmployee.fired_at}
                            <div class="flex items-center gap-3">
                                <div class="flex-1">
                                    <p class="text-xs font-medium text-mono">Fired</p>
                                    <p class="text-xs text-muted-foreground">{formatTimestamp(selectedEmployee.fired_at)}</p>
                                </div>
                            </div>
                        {/if}
                        
                        {#if selectedEmployee.resigned_at}
                            <div class="flex items-center gap-3">
                                <div class="flex-1">
                                    <p class="text-xs font-medium text-mono">Resigned</p>
                                    <p class="text-xs text-muted-foreground">{formatTimestamp(selectedEmployee.resigned_at)}</p>
                                </div>
                            </div>
                        {/if}
                        
                        {#if selectedEmployee.last_promotion_at}
                            <div class="flex items-center gap-3">
                                <div class="flex-1">
                                    <p class="text-xs font-medium text-mono">Last Promotion</p>
                                    <p class="text-xs text-muted-foreground">{formatTimestamp(selectedEmployee.last_promotion_at)}</p>
                                </div>
                            </div>
                        {/if}
                    </div>
                </div>

                <!-- Risk Assessment -->
                {#if selectedEmployee.current_mood < 0.5}
                    <div class="border-t border-border pt-4">
                        <h3 class="text-sm font-semibold text-mono mb-3">Risk Assessment</h3>
                        <div class="kt-alert kt-alert-warning-light">
                            <div class="kt-alert-icon">
                                <i class="ki-filled ki-warning"></i>
                            </div>
                            <div class="kt-alert-content">
                                <h4 class="kt-alert-title">Low Morale Warning</h4>
                                <p class="kt-alert-text">
                                    This employee has low morale ({(selectedEmployee.current_mood * 100).toFixed(0)}%). 
                                    There's a {(5 + ((0.4 - selectedEmployee.current_mood) / 0.4 * 15)).toFixed(1)}% chance they may resign.
                                </p>
                            </div>
                        </div>
                    </div>
                {/if}
            {/if}
        </div>
    </div>

    <!-- Hidden button to trigger promote modal -->
    <button style="display:none" data-kt-modal-toggle="#promote_modal"></button>

    <!-- Promote Employee Modal -->
    <div class="kt-modal" data-kt-modal="true" id="promote_modal">
        <div class="kt-modal-content max-w-[600px] top-[5%]">
            <div class="kt-modal-header">
                <h3 class="kt-modal-title">Promote Employee</h3>
                <button
                    type="button"
                    class="kt-modal-close"
                    aria-label="Close modal"
                    data-kt-modal-dismiss="#promote_modal"
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
                {#if promoteData}
                    <div class="space-y-4">
                        <!-- Employee Info -->
                        <div class="flex items-center gap-4">
                            <div class="flex-shrink-0">
                                <div class="w-16 h-16 rounded-lg bg-accent/50 flex items-center justify-center">
                                    <i class="ki-filled ki-user text-xl text-muted-foreground"></i>
                                </div>
                            </div>
                            <div class="flex-1">
                                <h4 class="font-semibold text-mono mb-1">{promoteData.employee.name}</h4>
                                <p class="text-sm text-muted-foreground mb-1">Position: {promoteData.employee.employee_profile?.name || 'Unknown'}</p>
                                <p class="text-xs text-muted-foreground">Current Salary: DZD {promoteData.currentSalary}</p>
                            </div>
                        </div>

                        <!-- Employee Performance -->
                        <div class="kt-card">
                            <div class="kt-card-body p-4">
                                <h5 class="font-medium text-mono mb-3">Current Performance</h5>
                                <div class="space-y-2">
                                    <div class="flex justify-between">
                                        <span class="text-sm text-muted-foreground">Efficiency Factor:</span>
                                        <span class="font-medium">{promoteData.efficiencyFactor}x</span>
                                    </div>
                                    <div class="flex justify-between">
                                        <span class="text-sm text-muted-foreground">Current Mood:</span>
                                        <span class="font-medium">{(promoteData.currentMood * 100).toFixed(0)}%</span>
                                    </div>
                                    <div class="flex justify-between">
                                        <span class="text-sm text-muted-foreground">Mood Decay Rate:</span>
                                        <span class="font-medium">{(promoteData.employee.mood_decay_rate_days * 100).toFixed(2)}%/day</span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Salary Input -->
                        <div class="kt-card">
                            <div class="kt-card-body p-4">
                                <h5 class="font-medium text-mono mb-3">New Salary</h5>
                                <div class="space-y-2">
                                    <label class="text-sm font-medium text-mono">Salary (DZD)</label>
                                    <input 
                                        type="number" 
                                        class="kt-input w-full" 
                                        bind:value={newSalary}
                                        min={promoteData.currentSalary}
                                        step="0.001"
                                        placeholder="Enter new salary..."
                                    />
                                    <p class="text-xs text-muted-foreground">
                                        Enter the new salary in Algerian Dinars (DZD). Must be higher than current salary.
                                    </p>
                                </div>
                            </div>
                        </div>

                        <!-- Salary Change Preview -->
                        {#if newSalary > promoteData.currentSalary}
                            <div class="kt-card bg-accent/50">
                                <div class="kt-card-body p-4">
                                    <h5 class="font-medium text-mono mb-3">Salary Change Preview</h5>
                                    <div class="space-y-2">
                                        <div class="flex justify-between">
                                            <span class="text-sm text-muted-foreground">Current Salary:</span>
                                            <span class="text-sm font-medium">DZD {promoteData.currentSalary}</span>
                                        </div>
                                        <div class="flex justify-between">
                                            <span class="text-sm text-muted-foreground">New Salary:</span>
                                            <span class="text-sm font-medium">DZD {newSalary}</span>
                                        </div>
                                        <div class="flex justify-between">
                                            <span class="text-sm text-muted-foreground">Increase:</span>
                                            <span class="text-sm font-medium text-green-600">
                                                +DZD {(newSalary - promoteData.currentSalary).toFixed(3)}
                                            </span>
                                        </div>
                                        <div class="flex justify-between">
                                            <span class="text-sm text-muted-foreground">Percentage Increase:</span>
                                            <span class="text-sm font-medium text-green-600">
                                                +{(((newSalary - promoteData.currentSalary) / promoteData.currentSalary) * 100).toFixed(1)}%
                                            </span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        {/if}

                        <!-- Promotion Benefits -->
                        <div class="kt-alert kt-alert-info-light">
                            <div class="kt-alert-icon">
                                <i class="ki-filled ki-information-1"></i>
                            </div>
                            <div class="kt-alert-content">
                                <h4 class="kt-alert-title">Promotion Benefits</h4>
                                <p class="kt-alert-text">
                                    Promoting this employee will increase their salary and potentially improve their mood and efficiency. 
                                    Higher salaries typically result in better employee satisfaction and reduced resignation risk.
                                </p>
                            </div>
                        </div>

                        <!-- Confirmation Message -->
                        <div class="text-sm text-muted-foreground">
                            Are you sure you want to promote <strong>{promoteData.employee.name}</strong>? This will increase their salary from <strong>DZD {promoteData.currentSalary}</strong> to <strong>DZD {newSalary}</strong>.
                        </div>
                    </div>
                {/if}
            </div>
            <div class="kt-modal-footer">
                <div></div>
                <div class="flex gap-4">
                    <button
                        class="kt-btn kt-btn-secondary"
                        data-kt-modal-dismiss="#promote_modal"
                        on:click={closePromoteModal}
                        disabled={loadingPromote}
                    >
                        Cancel
                    </button>
                    <button 
                        class="kt-btn kt-btn-primary"
                        on:click={promoteEmployee}
                        disabled={loadingPromote || newSalary <= promoteData?.currentSalary}
                    >
                        {#if loadingPromote}
                            <i class="ki-filled ki-loading ki-spin text-sm"></i>
                            Promoting...
                        {:else}
                            <i class="ki-filled ki-arrow-up text-sm"></i>
                            Promote Employee
                        {/if}
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Hidden button to trigger fire modal -->
    <button style="display:none" data-kt-modal-toggle="#fire_modal"></button>

    <!-- Fire Employee Modal -->
    <div class="kt-modal" data-kt-modal="true" id="fire_modal">
        <div class="kt-modal-content max-w-[600px] top-[5%]">
            <div class="kt-modal-header">
                <h3 class="kt-modal-title">Fire Employee</h3>
                <button
                    type="button"
                    class="kt-modal-close"
                    aria-label="Close modal"
                    data-kt-modal-dismiss="#fire_modal"
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
                {#if fireData}
                    <div class="space-y-4">
                        <!-- Employee Info -->
                        <div class="flex items-center gap-4">
                            <div class="flex-shrink-0">
                                <div class="w-16 h-16 rounded-lg bg-accent/50 flex items-center justify-center">
                                    <i class="ki-filled ki-user text-xl text-muted-foreground"></i>
                                </div>
                            </div>
                            <div class="flex-1">
                                <h4 class="font-semibold text-mono mb-1">{fireData.employee.name}</h4>
                                <p class="text-sm text-muted-foreground mb-1">Position: {fireData.employeeProfile}</p>
                                <p class="text-xs text-muted-foreground">Current Salary: DZD {fireData.currentSalary}</p>
                            </div>
                        </div>

                        <!-- Employee Performance -->
                        <div class="kt-card">
                            <div class="kt-card-body p-4">
                                <h5 class="font-medium text-mono mb-3">Current Performance</h5>
                                <div class="space-y-2">
                                    <div class="flex justify-between">
                                        <span class="text-sm text-muted-foreground">Efficiency Factor:</span>
                                        <span class="font-medium">{fireData.efficiencyFactor}x</span>
                                    </div>
                                    <div class="flex justify-between">
                                        <span class="text-sm text-muted-foreground">Current Mood:</span>
                                        <span class="font-medium">{(fireData.currentMood * 100).toFixed(0)}%</span>
                                    </div>
                                    <div class="flex justify-between">
                                        <span class="text-sm text-muted-foreground">Mood Decay Rate:</span>
                                        <span class="font-medium">{(fireData.employee.mood_decay_rate_days * 100).toFixed(2)}%/day</span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Warning Alert -->
                        <div class="kt-alert kt-alert-warning-light">
                            <div class="kt-alert-icon">
                                <i class="ki-filled ki-warning"></i>
                            </div>
                            <div class="kt-alert-content">
                                <h4 class="kt-alert-title">Firing Impact Warning</h4>
                                <p class="kt-alert-text">
                                    Firing this employee will have a negative impact on the mood of other employees in your company. 
                                    This action cannot be undone and may lead to decreased productivity and increased resignation risk.
                                </p>
                            </div>
                        </div>

                        <!-- Impact on Other Employees -->
                        <div class="kt-card bg-accent/50">
                            <div class="kt-card-body p-4">
                                <h5 class="font-medium text-mono mb-3">Impact on Other Employees</h5>
                                <div class="space-y-2">
                                    <div class="flex items-center gap-2">
                                        <i class="ki-filled ki-user-minus text-sm text-destructive"></i>
                                        <span class="text-sm text-muted-foreground">Other employees' mood will decrease by 5-15%</span>
                                    </div>
                                    <div class="flex items-center gap-2">
                                        <i class="ki-filled ki-arrow-down text-sm text-destructive"></i>
                                        <span class="text-sm text-muted-foreground">Increased resignation risk for remaining employees</span>
                                    </div>
                                    <div class="flex items-center gap-2">
                                        <i class="ki-filled ki-chart-line text-sm text-destructive"></i>
                                        <span class="text-sm text-muted-foreground">Overall company productivity may decrease</span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Confirmation Message -->
                        <div class="text-sm text-muted-foreground">
                            Are you absolutely sure you want to fire <strong>{fireData.employee.name}</strong>? 
                            This action will permanently remove them from your company and negatively affect other employees' morale.
                        </div>
                    </div>
                {/if}
            </div>
            <div class="kt-modal-footer">
                <div></div>
                <div class="flex gap-4">
                    <button
                        class="kt-btn kt-btn-secondary"
                        data-kt-modal-dismiss="#fire_modal"
                        on:click={closeFireModal}
                        disabled={loadingFire}
                    >
                        Cancel
                    </button>
                    <button 
                        class="kt-btn kt-btn-destructive"
                        on:click={fireEmployee}
                        disabled={loadingFire}
                    >
                        {#if loadingFire}
                            <i class="ki-filled ki-loading ki-spin text-sm"></i>
                            Firing...
                        {:else}
                            <i class="ki-filled ki-trash text-sm"></i>
                            Fire Employee
                        {/if}
                    </button>
                </div>
            </div>
        </div>
    </div>
</CompanyLayout> 