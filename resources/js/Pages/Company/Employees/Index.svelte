<script>
    import CompanyLayout from '../../Layouts/CompanyLayout.svelte';
    import Pagination from '../../Components/Pagination.svelte';
    import { onMount, tick } from 'svelte';
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
    let pagination = {};
    let loading = true;
    let search = '';
    let perPage = 10;
    let currentPage = 1;
    let searchTimeout;
    let showFilters = false;

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

    // Employee status mapping
    const employeeStatuses = {
        'active': 'Active',
        'applied': 'Applied',
        'fired': 'Fired',
        'resigned': 'Resigned'
    };

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

    // Handle employee profile selection
    function handleEmployeeProfileSelect(event) {
        employeeProfileFilter = event.detail.value;
        handleFilterChange();
    }

    // Fetch employees data
    async function fetchEmployees() {
        loading = true;
        try {
            const params = new URLSearchParams({
                page: currentPage,
                perPage: perPage,
                search: search
            });
            
            // Add filter parameters
            if (employeeProfileFilter) {
                params.append('employee_profile_id', employeeProfileFilter);
            }
            if (statusFilter) {
                params.append('status', statusFilter);
            }
            if (currentMoodMin) {
                params.append('current_mood_min', currentMoodMin);
            }
            if (currentMoodMax) {
                params.append('current_mood_max', currentMoodMax);
            }
            if (efficiencyFactorMin) {
                params.append('efficiency_factor_min', efficiencyFactorMin);
            }
            if (efficiencyFactorMax) {
                params.append('efficiency_factor_max', efficiencyFactorMax);
            }
            if (moodDecayRateMin) {
                params.append('mood_decay_rate_days_min', moodDecayRateMin);
            }
            if (moodDecayRateMax) {
                params.append('mood_decay_rate_days_max', moodDecayRateMax);
            }
            
            const response = await fetch(route('company.employees.index') + '?' + params.toString(), {
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                }
            });
            
            const data = await response.json();
            employees = data.employees;
            pagination = data.pagination;
            
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

    // Handle search with debouncing
    function handleSearch() {
        // Clear existing timeout
        if (searchTimeout) {
            clearTimeout(searchTimeout);
        }
        
        // Set new timeout for 500ms
        searchTimeout = setTimeout(() => {
            currentPage = 1;
            fetchEmployees();
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
            fetchEmployees();
        }
    }

    // Handle per page change
    function handlePerPageChange(newPerPage) {
        perPage = newPerPage;
        currentPage = 1;
        fetchEmployees();
    }

    // Handle filter changes
    function handleFilterChange() {
        currentPage = 1;
        fetchEmployees();
    }

    // Clear all filters
    function clearAllFilters() {
        employeeProfileFilter = '';
        statusFilter = '';
        currentMoodMin = '';
        currentMoodMax = '';
        efficiencyFactorMin = '';
        efficiencyFactorMax = '';
        moodDecayRateMin = '';
        moodDecayRateMax = '';
        if (employeeProfileSelectComponent) {
            employeeProfileSelectComponent.clear();
        }
        currentPage = 1;
        fetchEmployees();
    }

    // Toggle filters visibility
    function toggleFilters() {
        showFilters = !showFilters;
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

            <!-- Employees Table -->
            <div class="kt-card">
                <div class="kt-card-header">
                    <div class="kt-card-toolbar">
                        <div class="flex items-center gap-4">
                            <div class="kt-input max-w-64 w-64">
                                <i class="ki-filled ki-magnifier"></i>
                                <input 
                                    type="text" 
                                    class="kt-input" 
                                    placeholder="Search employees..." 
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
                            {#if employeeProfileFilter || statusFilter || currentMoodMin || currentMoodMax || efficiencyFactorMin || efficiencyFactorMax || moodDecayRateMin || moodDecayRateMax}
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
                            <!-- Employee Properties -->
                            <div class="space-y-2">
                                <h4 class="text-sm font-medium text-gray-700">Employee Properties</h4>                                    
                                <!-- Employee profile -->
                                <Select2
                                    bind:this={employeeProfileSelectComponent}
                                    id="employee-profile-filter"
                                    placeholder="All Profiles"
                                    allowClear={true}
                                    on:select={handleEmployeeProfileSelect}
                                    on:clear={() => {
                                        employeeProfileFilter = '';
                                        handleFilterChange();
                                    }}
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
                                                    text: profile.name,
                                                    name: profile.name,
                                                    description: profile.description
                                                }))
                                            };
                                        },
                                        cache: true
                                    }}
                                    templateResult={function(data) {
                                        if (data.loading) return data.text;
                                        
                                        const $elem = globalThis.$('<div class="flex items-center gap-2">' +
                                            '<i class="ki-filled ki-user text-sm text-muted-foreground"></i>' +
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

                            <!-- Status -->
                            <div class="space-y-2">
                                <h4 class="text-sm font-medium text-gray-700">Status</h4>                                    
                                <select 
                                    class="kt-select w-full" 
                                    bind:value={statusFilter}
                                    on:change={handleFilterChange}
                                >
                                    <option value="">All Statuses</option>
                                    <option value="active">Active</option>
                                    <option value="applied">Applied</option>
                                    <option value="fired">Fired</option>
                                    <option value="resigned">Resigned</option>
                                </select>
                            </div>

                            <!-- Current Mood Range -->
                            <div class="space-y-2">
                                <h4 class="text-sm font-medium text-gray-700">Current Mood (%)</h4>
                                
                                <div class="flex gap-2">
                                    <input 
                                        type="number" 
                                        class="kt-input flex-1" 
                                        placeholder="Min %" 
                                        bind:value={currentMoodMin}
                                        on:input={handleFilterChange}
                                        step="0.01"
                                        min="0"
                                        max="100"
                                    />
                                    <input 
                                        type="number" 
                                        class="kt-input flex-1" 
                                        placeholder="Max %" 
                                        bind:value={currentMoodMax}
                                        on:input={handleFilterChange}
                                        step="0.01"
                                        min="0"
                                        max="100"
                                    />
                                </div>
                            </div>

                            <!-- Efficiency Factor Range -->
                            <div class="space-y-2">
                                <h4 class="text-sm font-medium text-gray-700">Efficiency Factor</h4>
                                
                                <div class="flex gap-2">
                                    <input 
                                        type="number" 
                                        class="kt-input flex-1" 
                                        placeholder="Min" 
                                        bind:value={efficiencyFactorMin}
                                        on:input={handleFilterChange}
                                        step="0.01"
                                        min="0"
                                    />
                                    <input 
                                        type="number" 
                                        class="kt-input flex-1" 
                                        placeholder="Max" 
                                        bind:value={efficiencyFactorMax}
                                        on:input={handleFilterChange}
                                        step="0.01"
                                        min="0"
                                    />
                                </div>
                            </div>

                            <!-- Mood Decay Rate Range -->
                            <div class="space-y-2">
                                <h4 class="text-sm font-medium text-gray-700">Mood Decay Rate (%/day)</h4>
                                
                                <div class="flex gap-2">
                                    <input 
                                        type="number" 
                                        class="kt-input flex-1" 
                                        placeholder="Min %" 
                                        bind:value={moodDecayRateMin}
                                        on:input={handleFilterChange}
                                        step="0.001"
                                        min="0"
                                    />
                                    <input 
                                        type="number" 
                                        class="kt-input flex-1" 
                                        placeholder="Max %" 
                                        bind:value={moodDecayRateMax}
                                        on:input={handleFilterChange}
                                        step="0.001"
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
                                            <span class="kt-table-col-label">Employee</span>
                                        </span>
                                    </th>
                                    <th class="min-w-[150px]">
                                        <span class="kt-table-col">
                                            <span class="kt-table-col-label">Profile</span>
                                        </span>
                                    </th>
                                    <th class="min-w-[120px]">
                                        <span class="kt-table-col">
                                            <span class="kt-table-col-label">Status</span>
                                        </span>
                                    </th>
                                    <th class="min-w-[120px]">
                                        <span class="kt-table-col">
                                            <span class="kt-table-col-label">Salary</span>
                                        </span>
                                    </th>
                                    <th class="min-w-[120px]">
                                        <span class="kt-table-col">
                                            <span class="kt-table-col-label">Mood</span>
                                        </span>
                                    </th>
                                    <th class="min-w-[120px]">
                                        <span class="kt-table-col">
                                            <span class="kt-table-col-label">Efficiency</span>
                                        </span>
                                    </th>
                                    <th class="min-w-[120px]">
                                        <span class="kt-table-col">
                                            <span class="kt-table-col-label">Mood Decay</span>
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
                                                        <div class="kt-skeleton w-24 h-4 rounded"></div>
                                                        <div class="kt-skeleton w-16 h-3 rounded"></div>
                                                    </div>
                                                </div>
                                            </td>
                                            <td class="p-4">
                                                <div class="kt-skeleton w-20 h-4 rounded"></div>
                                            </td>
                                            <td class="p-4">
                                                <div class="kt-skeleton w-16 h-6 rounded"></div>
                                            </td>
                                            <td class="p-4">
                                                <div class="kt-skeleton w-16 h-4 rounded"></div>
                                            </td>
                                            <td class="p-4">
                                                <div class="kt-skeleton w-12 h-4 rounded"></div>
                                            </td>
                                            <td class="p-4">
                                                <div class="kt-skeleton w-16 h-6 rounded"></div>
                                            </td>
                                            <td class="p-4">
                                                <div class="kt-skeleton w-12 h-4 rounded"></div>
                                            </td>
                                            <td class="p-4">
                                                <div class="kt-skeleton w-8 h-8 rounded"></div>
                                            </td>
                                        </tr>
                                    {/each}
                                {:else if employees.length === 0}
                                    <!-- Empty state -->
                                    <tr>
                                        <td colspan="10" class="p-10">
                                            <div class="flex flex-col items-center justify-center text-center">
                                                <div class="mb-4">
                                                    <i class="ki-filled ki-user text-4xl text-muted-foreground"></i>
                                                </div>
                                                <h3 class="text-lg font-semibold text-mono mb-2">No employees found</h3>
                                                <p class="text-sm text-secondary-foreground mb-4">
                                                    {search ? 'No employees match your search criteria.' : 'Get started by recruiting your first employee.'}
                                                </p>
                                                <a href="{route('company.employees.recruit-page')}" class="kt-btn kt-btn-primary">
                                                    <i class="ki-filled ki-plus text-base"></i>
                                                    Recruit First Employee
                                                </a>
                                            </div>
                                        </td>
                                    </tr>
                                {:else}
                                    <!-- Actual data rows -->
                                    {#each employees as employee}
                                        <tr class="hover:bg-muted/50">
                                            <td>
                                                <input class="kt-checkbox kt-checkbox-sm" type="checkbox" value={employee.id}/>
                                            </td>
                                            <td>
                                                <span class="text-sm font-medium text-mono">#{employee.id}</span>
                                            </td>
                                            <td>
                                                <span class="text-sm font-medium text-mono hover:text-primary">
                                                    {employee.name}
                                                </span>
                                            </td>
                                            <td>
                                                <span class="text-sm text-muted-foreground">{employee.employee_profile?.name || 'Unknown'}</span>
                                            </td>
                                            <td>
                                                <span class={getEmployeeStatusBadgeClass(employee.status)}>
                                                    {employeeStatuses[employee.status] || employee.status}
                                                </span>
                                            </td>
                                            <td>
                                                <span class="text-sm font-medium text-mono">DZD {employee.salary_month}</span>
                                            </td>
                                            <td>
                                                <div class="flex items-center gap-2">
                                                    <!-- Progress Bar -->
                                                    <div class="w-full bg-gray-200 rounded-full h-2">
                                                        <div class="kt-progress {employee.current_mood > 0.7 ? 'kt-progress-primary' : employee.current_mood > 0.4 ? 'kt-progress-warning' : 'kt-progress-destructive'}">
                                                            <div class="kt-progress-indicator" style="width: {(employee.current_mood * 100)}%"></div>
                                                        </div>
                                                    </div>

                                                    <span class="text-xs text-muted-foreground">{(employee.current_mood * 100).toFixed(0)}%</span>
                                                </div>
                                            </td>
                                            <td>
                                                <span class="kt-badge kt-badge-{employee.efficiency_factor > 1 ? 'success' : 'warning'} kt-badge-sm">
                                                    {employee.efficiency_factor}x
                                                </span>
                                            </td>
                                            <td>
                                                <span class="text-sm text-muted-foreground">{(employee.mood_decay_rate_days * 100).toFixed(2)}%/day</span>
                                            </td>
                                            <td class="text-center">
                                                <div class="kt-menu flex-inline" data-kt-menu="true">
                                                    <div class="kt-menu-item" data-kt-menu-item-offset="0, 10px" data-kt-menu-item-placement="bottom-end" data-kt-menu-item-placement-rtl="bottom-start" data-kt-menu-item-toggle="dropdown" data-kt-menu-item-trigger="click">
                                                        <button class="kt-menu-toggle kt-btn kt-btn-sm kt-btn-icon kt-btn-ghost">
                                                            <i class="ki-filled ki-dots-vertical text-lg"></i>
                                                        </button>
                                                        <div class="kt-menu-dropdown kt-menu-default w-full max-w-[175px]" data-kt-menu-dismiss="true">
                                                            <div class="kt-menu-item">
                                                                <button class="kt-menu-link" on:click={() => openEmployeeDrawer(employee)}>
                                                                    <span class="kt-menu-icon">
                                                                        <i class="ki-filled ki-eye"></i>
                                                                    </span>
                                                                    <span class="kt-menu-title">View Details</span>
                                                                </button>
                                                            </div>
                                                            
                                                            {#if employee.status === 'active'}
                                                                <div class="kt-menu-separator"></div>
                                                                <div class="kt-menu-item">
                                                                    <button class="kt-menu-link" on:click={() => openPromoteModal(employee)}>
                                                                        <span class="kt-menu-icon">
                                                                            <i class="ki-filled ki-arrow-up"></i>
                                                                        </span>
                                                                        <span class="kt-menu-title">Promote</span>
                                                                    </button>
                                                                </div>
                                                                <div class="kt-menu-separator"></div>
                                                                <div class="kt-menu-item">
                                                                    <button class="kt-menu-link" on:click={() => openFireModal(employee)}>
                                                                        <span class="kt-menu-icon">
                                                                            <i class="ki-filled ki-trash"></i>
                                                                        </span>
                                                                        <span class="kt-menu-title">Fire Employee</span>
                                                                    </button>
                                                                </div>
                                                            {:else if employee.status === 'applied'}
                                                                <div class="kt-menu-separator"></div>
                                                                <div class="kt-menu-item">
                                                                    <a class="kt-menu-link" href={route('company.employees.recruit-page')}>
                                                                        <span class="kt-menu-icon">
                                                                            <i class="ki-filled ki-check"></i>
                                                                        </span>
                                                                        <span class="kt-menu-title">Recruit</span>
                                                                    </a>
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
                        {employeeStatuses[selectedEmployee.status] || selectedEmployee.status}
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
                            <div class="w-full bg-gray-200 rounded-full h-3">
                                <div 
                                    class="h-3 rounded-full {selectedEmployee.current_mood > 0.7 ? 'bg-green-500' : selectedEmployee.current_mood > 0.4 ? 'bg-yellow-500' : 'bg-red-500'}"
                                    style="width: {(selectedEmployee.current_mood * 100)}%"
                                ></div>
                            </div>
                            <span class="text-xs font-medium">{(selectedEmployee.current_mood * 100).toFixed(0)}%</span>
                        </div>
                        <p class="text-xs text-muted-foreground">
                            {selectedEmployee.current_mood > 0.7 ? 'High morale - Employee is very satisfied' : 
                              selectedEmployee.current_mood > 0.4 ? 'Moderate morale - Employee is somewhat satisfied' : 
                              'Low morale - Employee is dissatisfied and may resign'}
                        </p>
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