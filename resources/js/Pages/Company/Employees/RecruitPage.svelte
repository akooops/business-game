<script>
    import CompanyLayout from '../../Layouts/CompanyLayout.svelte';
    import Select2 from '../../Components/Forms/Select2.svelte';
    import Pagination from '../../Components/Pagination.svelte';
    import { onMount, tick } from 'svelte';
    import { page } from '@inertiajs/svelte'

    // Define breadcrumbs for this page
    const breadcrumbs = [
        {
            title: 'Employees',
            url: route('company.employees.index'),
            active: false
        },
        {
            title: 'Recruit Employees',
            url: route('company.employees.recruit-page'),
            active: true
        }
    ];
    
    const pageTitle = 'Recruit Employees';

    // Reactive variables
    let selectedEmployeeProfile = null;
    let selectedEmployeeProfileId = '';
    let availableEmployees = [];
    let loadingEmployees = false;
    let appliedEmployees = [];
    let loadingAppliedEmployees = false;
    let activeTab = 'recruitment'; // 'recruitment' or 'applied'
    
    // Pagination variables
    let appliedPagination = {};
    let appliedCurrentPage = 1;
    let appliedPerPage = 10;

    // Select2 component references
    let employeeProfileSelectComponent;

    // Modal state
    let showRecruitModal = false;
    let recruitData = null;

    // Fetch employees for selected profile
    async function fetchEmployeesForProfile(employeeProfileId) {
        if (!employeeProfileId) {
            availableEmployees = [];
            return;
        }

        loadingEmployees = true;
        try {
            const response = await fetch(route('company.employee-profiles.find-employees', employeeProfileId), {
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                }
            });
            
            const data = await response.json();
            availableEmployees = data.employees;
        } catch (error) {
            console.error('Error fetching employees:', error);
        } finally {
            loadingEmployees = false;
        }
    }

    // Handle employee profile selection
    function handleEmployeeProfileSelect(event) {
        selectedEmployeeProfile = event.detail.data;
        selectedEmployeeProfileId = event.detail.value;
        
        if (selectedEmployeeProfile) {
            fetchEmployeesForProfile(selectedEmployeeProfile.id);
        } else {
            availableEmployees = [];
        }
    }

    // Handle employee profile clear
    function handleEmployeeProfileClear() {
        selectedEmployeeProfile = null;
        selectedEmployeeProfileId = '';
        availableEmployees = [];
    }

    // Open recruit modal
    function openRecruitModal(employee) {
        if (!employee) {
            showToast('Please select an employee to recruit', 'error');
            return;
        }
        
        recruitData = {
            employee: employee,
            employeeProfile: employee.employee_profile || selectedEmployeeProfile,
            recruitmentCost: employee.recruitment_cost,
            salary: employee.salary_month,
            efficiencyFactor: employee.efficiency_factor,
            moodDecayRate: (employee.mood_decay_rate_days * 100).toFixed(2) + '%'
        };

        showRecruitModal = true;
        
        // Show modal
        const toggleButton = document.querySelector('[data-kt-modal-toggle="#recruit_modal"]');
        if (toggleButton) {
            toggleButton.click();
        }
    }

    // Close recruit modal
    function closeRecruitModal() {
        // Simulate button click to use KT framework logic
        const dismissButton = document.querySelector('[data-kt-modal-dismiss="#recruit_modal"]');
        if (dismissButton) {
            dismissButton.click();
        }
        
        showRecruitModal = false;
        recruitData = null;
    }

    // Recruit employee
    async function recruitEmployee() {
        if (!recruitData) return;

        try {
            const response = await fetch(route('company.employees.recruit', recruitData.employee.id), {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content'),
                    'X-Requested-With': 'XMLHttpRequest'
                }
            });

            if (response.ok) {
                const data = await response.json();
                
                // Show success toast
                showToast(data.message || 'Employee recruited successfully!', 'success');
                
                // Close modal
                closeRecruitModal();
                
                // Reset form
                selectedEmployeeProfile = null;
                selectedEmployeeProfileId = '';
                availableEmployees = [];
                
                // Refetch applied employees if we're on that tab
                const appliedTab = document.getElementById('tab_applied');
                if (appliedTab && !appliedTab.classList.contains('hidden')) {
                    fetchAppliedEmployees();
                }
            } else {
                const errorData = await response.json();
                showToast(errorData.message || 'Error recruiting employee. Please try again.', 'error');
            }
        } catch (error) {
            console.error('Error recruiting employee:', error);
            showToast('Network error. Please check your connection and try again.', 'error');
        }
    }

    // Fetch applied employees
    async function fetchAppliedEmployees() {
        loadingAppliedEmployees = true;
        try {
            const params = new URLSearchParams({
                status: 'applied',
                page: appliedCurrentPage,
                perPage: appliedPerPage
            });
            
            const response = await fetch(route('company.employees.index') + '?' + params.toString(), {
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                }
            });
            
            const data = await response.json();
            appliedEmployees = data.employees;
            appliedPagination = data.pagination;
        } catch (error) {
            console.error('Error fetching applied employees:', error);
        } finally {
            loadingAppliedEmployees = false;
        }
    }

    // Handle applied employees pagination
    function goToAppliedPage(page) {
        if (page && page !== appliedCurrentPage) {
            appliedCurrentPage = page;
            fetchAppliedEmployees();
        }
    }

    // Handle applied employees per page change
    function handleAppliedPerPageChange(newPerPage) {
        appliedPerPage = newPerPage;
        appliedCurrentPage = 1;
        fetchAppliedEmployees();
    }

    // Auto-refresh applied employees when tab is activated
    function handleTabChange() {
        // Check if applied employees tab is active
        const appliedTab = document.getElementById('tab_applied');
        if (appliedTab && !appliedTab.classList.contains('hidden')) {
            fetchAppliedEmployees();
        }
    }

    onMount(() => {
        // Initialize any required components
        
        // Listen for tab changes
        const tabButtons = document.querySelectorAll('[data-kt-tab-toggle]');
        tabButtons.forEach(button => {
            button.addEventListener('click', () => {
                // Small delay to ensure tab is switched
                setTimeout(handleTabChange, 100);
            });
        });
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
    <div class="kt-container-fixed">
        <div class="grid gap-5 lg:gap-7.5">
            <!-- Recruitment Header -->
            <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4">
                <div class="flex flex-col gap-1">
                    <h1 class="text-2xl font-bold text-mono">Recruit Employees</h1>
                    <p class="text-sm text-secondary-foreground">
                        Find and recruit new employees for your company
                    </p>
                </div>
                <div class="flex items-center gap-3">
                    <a href="{route('company.employees.index')}" class="kt-btn kt-btn-outline">
                        <i class="ki-filled ki-arrow-left text-base"></i>
                        Back to Employees
                    </a>
                </div>                      
            </div>

            <!-- Tabs -->
            <div class="kt-card">
                <div class="kt-card-content p-4">
                    <div class="space-y-3">
                        <div class="kt-tabs kt-tabs-line" data-kt-tabs="true">
                            <button class="kt-tab-toggle active" data-kt-tab-toggle="#tab_recruitment">
                                <i class="ki-filled ki-users text-base me-2"></i>
                                Find New Employees
                            </button>
                            <button class="kt-tab-toggle" data-kt-tab-toggle="#tab_applied">
                                <i class="ki-filled ki-user-tick text-base me-2"></i>
                                Applied Employees
                            </button>
                        </div>
                        <div class="text-sm">
                            <div id="tab_recruitment">
                                <!-- Recruitment Tab -->
                                <div class="p-6">
                                    <!-- Employee Profile Selector -->
                                    <div class="mb-4">
                                        <label class="text-sm font-medium text-mono mb-2 block">Select Employee Profile</label>
                                        <div class="flex items-center gap-3">
                                            {#if selectedEmployeeProfile}
                                                <!-- Employee Profile Badge -->
                                                <div class="flex items-center gap-2">
                                                    <span class="kt-badge kt-badge-outline kt-badge-primary">
                                                        <i class="ki-filled ki-user text-sm me-1"></i>
                                                        Profile: {selectedEmployeeProfile.name}
                                                    </span>
                                                    <button 
                                                        class="kt-btn kt-btn-sm kt-btn-icon kt-btn-ghost"
                                                        on:click={handleEmployeeProfileClear}
                                                        title="Clear profile selection"
                                                    >
                                                        <i class="ki-filled ki-cross text-sm"></i>
                                                    </button>
                                                </div>
                                            {:else}
                                                <!-- Employee Profile Filter -->
                                                <div class="w-full">
                                                    <Select2
                                                        bind:this={employeeProfileSelectComponent}
                                                        id="employee-profile-select"
                                                        placeholder="Choose an employee profile..."
                                                        bind:value={selectedEmployeeProfileId}
                                                        on:select={handleEmployeeProfileSelect}
                                                        on:clear={handleEmployeeProfileClear}
                                                        disabled={loadingEmployees}
                                                        ajax={{
                                                            url: route('company.employee-profiles.index'),
                                                            dataType: 'json',
                                                            delay: 300,
                                                            data: function(params) {
                                                                return {
                                                                    search: params.term,
                                                                    perPage: 100
                                                                };
                                                            },
                                                            processResults: function(data) {
                                                                return {
                                                                    results: data.employeeProfiles.map(profile => ({
                                                                        id: profile.id,
                                                                        text: profile.name,
                                                                        name: profile.name,
                                                                        description: profile.description,
                                                                    }))
                                                                };
                                                            },
                                                            cache: true
                                                        }}
                                                        templateResult={function(data) {
                                                            if (data.loading) return data.text;
                                                            
                                                            const $elem = globalThis.$('<div class="flex flex-col">' +
                                                                '<span class="font-medium">' + data.name + '</span>' +
                                                                '<span class="text-sm text-muted-foreground">' + (data.description || 'No description') + '</span>' +
                                                                '</div>');
                                                            return $elem;
                                                        }}
                                                        templateSelection={function(data) {
                                                            if (!data.id) return data.text;
                                                            
                                                            return data.name;
                                                        }}
                                                    />
                                                </div>
                                            {/if}
                                        </div>
                                    </div>

                                    {#if selectedEmployeeProfile}
                                        <!-- Available Employees Grid -->
                                        <div class="kt-card">
                                            <div class="kt-card-header">
                                                <h4 class="kt-card-title">Available Candidates</h4>
                                            </div>
                                            <div class="kt-card-content p-0">
                                                {#if loadingEmployees}
                                                    <!-- Loading skeleton -->
                                                    <div class="p-6">
                                                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6 p-4">
                                                            {#each Array(8) as _, i}
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
                                                {:else if availableEmployees.length === 0}
                                                    <!-- Empty state -->
                                                    <div class="p-10">
                                                        <div class="flex flex-col items-center justify-center text-center">
                                                            <div class="mb-4">
                                                                <i class="ki-filled ki-user text-4xl text-muted-foreground"></i>
                                                            </div>
                                                            <h3 class="text-lg font-semibold text-mono mb-2">No candidates found</h3>
                                                            <p class="text-sm text-secondary-foreground mb-4">
                                                                No candidates are available for this profile
                                                            </p>
                                                        </div>
                                                    </div>
                                                {:else}
                                                    <!-- Candidates Grid -->
                                                    <div class="p-6">
                                                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6 p-4">
                                                            {#each availableEmployees as employee}
                                                                <div class="kt-card kt-card-hover cursor-pointer" on:click={() => openRecruitModal(employee)}>
                                                                    <div class="kt-card-content flex flex-col items-center lg:pt-10">
                                                                        <div class="mb-3">
                                                                            <div class="size-20 relative">
                                                                                <div class="w-20 h-20 rounded-full bg-accent/50 flex items-center justify-center">
                                                                                    <i class="ki-filled ki-user text-2xl text-muted-foreground"></i>
                                                                                </div>
                                                                                <div class="w-2.5 h-2.5 rounded-full ring-2 ring-background absolute bottom-0.5 start-16 transform -translate-y-1/2 bg-yellow-500"></div>
                                                                            </div>
                                                                        </div>
                                                                        <div class="flex items-center justify-center gap-1.5 mb-3">
                                                                            <a class="hover:text-primary text-center text-base leading-5 font-medium text-mono" href="#">
                                                                                {employee.name}
                                                                            </a>
                                                                        </div>

                                                                        <div class="flex flex-col gap-1 text-xs text-secondary-foreground">
                                                                            <div class="flex justify-center gap-1">
                                                                                <i class="fa-solid fa-dollar-sign text-green-500"></i>
                                                                                <span>DZD {employee.salary_month}</span>
                                                                            </div>
                                                                            <div class="flex justify-center gap-1">
                                                                                <i class="fa-solid fa-coins text-blue-500"></i>
                                                                                <span>DZD {employee.recruitment_cost}</span>
                                                                            </div>
                                                                            <div class="flex justify-center gap-1">
                                                                                <i class="fa-solid fa-chart-line text-primary"></i>
                                                                                <span>{employee.efficiency_factor}x Efficiency</span>
                                                                            </div>
                                                                            <div class="flex justify-center gap-1">
                                                                                <i class="fa-solid fa-clock text-orange-500"></i>
                                                                                <span>{(employee.mood_decay_rate_days * 100).toFixed(2)}%/day</span>
                                                                            </div>
                                                                        </div>

                                                                        <!-- Action Button -->
                                                                        <div class="flex items-center gap-2 mt-4">
                                                                            <button 
                                                                                class="kt-btn kt-btn-sm kt-btn-primary"
                                                                                on:click|stopPropagation={() => openRecruitModal(employee)}
                                                                            >
                                                                                <i class="ki-filled ki-check text-sm"></i>
                                                                                Recruit
                                                                            </button>
                                                                        </div>
                                                                    </div>
                                                                </div>      
                                                            {/each}
                                                        </div>
                                                    </div>
                                                {/if}
                                            </div>
                                        </div>
                                    {:else}
                                        <!-- No Profile Selected -->
                                        <div class="kt-card">
                                            <div class="kt-card-content">
                                                <div class="flex flex-col items-center justify-center h-96 text-center">
                                                    <i class="ki-filled ki-user text-4xl text-muted-foreground mb-4"></i>
                                                    <h3 class="text-lg font-semibold text-mono mb-2">Select an Employee Profile</h3>
                                                    <p class="text-sm text-secondary-foreground">
                                                        Choose an employee profile from the dropdown above to view available candidates and recruit them.
                                                    </p>
                                                </div>
                                            </div>
                                        </div>
                                    {/if}
                                </div>
                            </div>
                            <div class="hidden" id="tab_applied">
                                <!-- Applied Employees Tab -->
                                <div class="p-6">
                                    <div class="flex flex-col gap-1 mb-4">
                                        <h4 class="text-lg font-semibold text-mono">Applied Employees</h4>
                                        <p class="text-sm text-secondary-foreground">
                                            All employees who have applied to your company
                                        </p>
                                    </div>

                                    {#if loadingAppliedEmployees}
                                        <!-- Loading skeleton -->
                                        <div class="p-6">
                                            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6 p-4">
                                                {#each Array(8) as _, i}
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
                                    {:else if appliedEmployees.length === 0}
                                        <!-- Empty state -->
                                        <div class="p-10">
                                            <div class="flex flex-col items-center justify-center text-center">
                                                <div class="mb-4">
                                                    <i class="ki-filled ki-user-tick text-4xl text-muted-foreground"></i>
                                                </div>
                                                <h3 class="text-lg font-semibold text-mono mb-2">No applied employees</h3>
                                                <p class="text-sm text-secondary-foreground mb-4">
                                                    No employees have applied to your company yet
                                                </p>
                                            </div>
                                        </div>
                                    {:else}
                                        <!-- Applied Employees Grid -->
                                        <div class="p-6">
                                            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6 p-4">
                                                {#each appliedEmployees as employee}
                                                    <div class="kt-card kt-card-hover cursor-pointer" on:click={() => openRecruitModal(employee)}>
                                                        <div class="kt-card-content flex flex-col items-center lg:pt-10">
                                                            <div class="mb-3">
                                                                <div class="size-20 relative">
                                                                    <div class="w-20 h-20 rounded-full bg-accent/50 flex items-center justify-center">
                                                                        <i class="ki-filled ki-user text-2xl text-muted-foreground"></i>
                                                                    </div>
                                                                    <div class="w-2.5 h-2.5 rounded-full ring-2 ring-background absolute bottom-0.5 start-16 transform -translate-y-1/2 bg-yellow-500"></div>
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
                                                            </div>

                                                            <div class="flex flex-col gap-1 text-xs text-secondary-foreground">
                                                                <div class="flex justify-center gap-1">
                                                                    <i class="fa-solid fa-dollar-sign text-green-500"></i>
                                                                    <span>DZD {employee.salary_month}</span>
                                                                </div>
                                                                <div class="flex justify-center gap-1">
                                                                    <i class="fa-solid fa-coins text-blue-500"></i>
                                                                    <span>DZD {employee.recruitment_cost}</span>
                                                                </div>
                                                                <div class="flex justify-center gap-1">
                                                                    <i class="fa-solid fa-chart-line text-primary"></i>
                                                                    <span>{employee.efficiency_factor}x Efficiency</span>
                                                                </div>
                                                                <div class="flex justify-center gap-1">
                                                                    <i class="fa-solid fa-clock text-orange-500"></i>
                                                                    <span>{(employee.mood_decay_rate_days * 100).toFixed(2)}%/day</span>
                                                                </div>
                                                            </div>

                                                            <!-- Action Button -->
                                                            <div class="flex items-center gap-2 mt-4">
                                                                <button 
                                                                    class="kt-btn kt-btn-sm kt-btn-primary"
                                                                    on:click|stopPropagation={() => openRecruitModal(employee)}
                                                                >
                                                                    <i class="ki-filled ki-check text-sm"></i>
                                                                    Recruit
                                                                </button>
                                                            </div>
                                                        </div>
                                                    </div>      
                                                {/each}
                                            </div>
                                        </div>

                                        <!-- Pagination -->
                                        {#if appliedPagination && appliedPagination.total > 0}
                                            <Pagination 
                                                pagination={appliedPagination} 
                                                perPage={appliedPerPage}
                                                onPageChange={goToAppliedPage} 
                                                onPerPageChange={handleAppliedPerPageChange}
                                            />
                                        {/if}
                                    {/if}
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- End of Container -->

    <!-- Hidden button to trigger modal -->
    <button style="display:none" data-kt-modal-toggle="#recruit_modal"></button>

    <!-- Recruit Confirmation Modal -->
    <div class="kt-modal" data-kt-modal="true" id="recruit_modal">
        <div class="kt-modal-content max-w-[600px] top-[5%]">
            <div class="kt-modal-header">
                <h3 class="kt-modal-title">Confirm Recruitment</h3>
                <button
                    type="button"
                    class="kt-modal-close"
                    aria-label="Close modal"
                    data-kt-modal-dismiss="#recruit_modal"
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
                {#if recruitData}
                    <div class="space-y-4">
                        <!-- Employee and Profile Info -->
                        <div class="flex items-center gap-4">
                            <div class="flex-shrink-0">
                                <div class="w-16 h-16 rounded-lg bg-accent/50 flex items-center justify-center">
                                    <i class="ki-filled ki-user text-xl text-muted-foreground"></i>
                                </div>
                            </div>
                            <div class="flex-1">
                                <h4 class="font-semibold text-mono mb-1">{recruitData.employee.name}</h4>
                                <p class="text-sm text-muted-foreground mb-1">Position: {recruitData.employeeProfile.name}</p>
                            </div>
                        </div>

                        <!-- Employee Details -->
                        <div class="kt-card">
                            <div class="kt-card-body p-4">
                                <h5 class="font-medium text-mono mb-3">Employee Details</h5>
                                <div class="space-y-2">
                                    <div class="flex justify-between">
                                        <span class="text-sm text-muted-foreground">Efficiency Factor:</span>
                                        <span class="font-medium">{recruitData.efficiencyFactor}x</span>
                                    </div>
                                    <div class="flex justify-between">
                                        <span class="text-sm text-muted-foreground">Mood Decay Rate:</span>
                                        <span class="font-medium">{recruitData.moodDecayRate}/day</span>
                                    </div>
                                    <div class="flex justify-between">
                                        <span class="text-sm text-muted-foreground">Current Mood:</span>
                                        <span class="font-medium">{recruitData.employee.current_mood}</span>
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
                                        Monthly Salary
                                    </span>
                                    <span class="text-sm font-medium text-mono">
                                        DZD {recruitData.salary}
                                    </span>
                                </div>
                                <div class="flex justify-between items-center">
                                    <span class="text-sm font-normal text-secondary-foreground">
                                        Recruitment Cost
                                    </span>
                                    <span class="text-sm font-medium text-mono">
                                        DZD {recruitData.recruitmentCost}
                                    </span>
                                </div>
                            </div>
                        </div>

                        <!-- Confirmation Message -->
                        <div class="text-sm text-muted-foreground">
                            Are you sure you want to recruit <strong>{recruitData.employee.name}</strong>? This will cost <strong>DZD {recruitData.recruitmentCost}</strong> in advance and also the monthly salary of <strong>DZD {recruitData.salary}</strong> and they will join your company immediately.
                        </div>
                    </div>
                {/if}
            </div>
            <div class="kt-modal-footer">
                <div></div>
                <div class="flex gap-4">
                    <button
                        class="kt-btn kt-btn-secondary text-white"
                        data-kt-modal-dismiss="#recruit_modal"
                        on:click={closeRecruitModal}
                    >
                        Cancel
                    </button>
                    <button 
                        class="kt-btn kt-btn-primary"
                        on:click={recruitEmployee}
                    >
                        Confirm Recruitment
                    </button>
                </div>
            </div>
        </div>
    </div>
</CompanyLayout> 