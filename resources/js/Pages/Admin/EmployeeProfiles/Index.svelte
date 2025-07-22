<script>
    import AdminLayout from '../../Layouts/AdminLayout.svelte';
    import Pagination from '../../Components/Pagination.svelte';
    import { onMount, tick } from 'svelte';
    import { page } from '@inertiajs/svelte'

    // Define breadcrumbs for this page
    const breadcrumbs = [
        {
            title: 'Employee Profiles',
            url: route('admin.employee-profiles.index'),
            active: false
        },
        {
            title: 'Index',
            url: route('admin.employee-profiles.index'),
            active: true
        }
    ];
    
    const pageTitle = 'Employee Profiles';

    // Reactive variables
    let employeeProfiles = [];
    let pagination = {};
    let loading = true;
    let search = '';
    let perPage = 10;
    let currentPage = 1;
    let searchTimeout;
    let showFilters = false;

    // Filter variables
    let salaryMin = '';
    let salaryMax = '';
    let recruitmentCostMin = '';
    let recruitmentCostMax = '';

    // Fetch employee profiles data
    async function fetchEmployeeProfiles() {
        loading = true;
        try {
            const params = new URLSearchParams({
                page: currentPage,
                perPage: perPage,
                search: search
            });
            
            // Add filter parameters
            if (salaryMin) {
                params.append('salary_min', salaryMin);
            }
            if (salaryMax) {
                params.append('salary_max', salaryMax);
            }
            if (recruitmentCostMin) {
                params.append('recruitment_cost_min', recruitmentCostMin);
            }
            if (recruitmentCostMax) {
                params.append('recruitment_cost_max', recruitmentCostMax);
            }
            
            const response = await fetch(route('admin.employee-profiles.index') + '?' + params.toString(), {
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                }
            });
            
            const data = await response.json();
            employeeProfiles = data.employeeProfiles;
            pagination = data.pagination;
            
            // Wait for DOM to update, then initialize menus
            await tick();
            if (window.KTMenu) {
                window.KTMenu.init();
            }
        } catch (error) {
            console.error('Error fetching employee profiles:', error);
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
            fetchEmployeeProfiles();
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
            fetchEmployeeProfiles();
        }
    }

    // Handle per page change
    function handlePerPageChange(newPerPage) {
        perPage = newPerPage;
        currentPage = 1;
        fetchEmployeeProfiles();
    }

    // Handle filter changes
    function handleFilterChange() {
        currentPage = 1;
        fetchEmployeeProfiles();
    }

    // Clear all filters
    function clearAllFilters() {
        salaryMin = '';
        salaryMax = '';
        recruitmentCostMin = '';
        recruitmentCostMax = '';
        currentPage = 1;
        
        fetchEmployeeProfiles();
    }

    // Toggle filters visibility
    function toggleFilters() {
        showFilters = !showFilters;
    }

    // Get difficulty badge class
    function getDifficultyBadgeClass(difficulty) {
        switch(difficulty) {
            case 'very_easy':
                return 'kt-badge kt-badge-success kt-badge-sm';
            case 'easy':
                return 'kt-badge kt-badge-outline kt-badge-success kt-badge-sm';
            case 'medium':
                return 'kt-badge kt-badge-outline kt-badge-warning kt-badge-sm';
            case 'hard':
                return 'kt-badge kt-badge-outline kt-badge-destructive kt-badge-sm';
            case 'very_hard':
                return 'kt-badge kt-badge-destructive kt-badge-sm';
            default:
                return 'kt-badge kt-badge-outline kt-badge-sm';
        }
    }

    // Delete employee profile
    async function deleteEmployeeProfile(employeeProfileId) {
        if (!confirm('Are you sure you want to delete this employee profile? This action cannot be undone.')) {
            return;
        }

        try {
            const formData = new FormData();
            formData.append('_method', 'DELETE');
            formData.append('_token', document.querySelector('meta[name="csrf-token"]')?.getAttribute('content'));

            const response = await fetch(route('admin.employee-profiles.destroy', { employeeProfile: employeeProfileId }), {
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
                    message: "Employee profile deleted successfully!",
                    variant: "success",
                    position: "bottom-right",
                });

                // Refresh the employee profiles list
                fetchEmployeeProfiles();
            } else {
                const errorData = await response.json().catch(() => ({}));
                const errorMessage = errorData.message || 'Error deleting employee profile. Please try again.';
                
                KTToast.show({
                    icon: `<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-info-icon lucide-info"><circle cx="12" cy="12" r="10"/><path d="M12 16v-4"/><path d="M12 8h.01"/></svg>`,
                    message: errorMessage,
                    variant: "destructive",
                    position: "bottom-right",
                });
            }
        } catch (error) {
            console.error('Error deleting employee profile:', error);
            
            KTToast.show({
                icon: `<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-info-icon lucide-info"><circle cx="12" cy="12" r="10"/><path d="M12 16v-4"/><path d="M12 8h.01"/></svg>`,
                message: "Network error. Please check your connection and try again.",
                variant: "destructive",
                position: "bottom-right",
            });
        }
    }

    onMount(() => {
        fetchEmployeeProfiles();
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
            <!-- Employee Profiles Header -->
            <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4">
                <div class="flex flex-col gap-1">
                    <h1 class="text-2xl font-bold text-mono">Employee Profiles Management</h1>
                    <p class="text-sm text-secondary-foreground">
                        Manage your employee profile templates for recruitment and training
                    </p>
                </div>
                <div class="flex items-center gap-3">
                    <a href="{route('admin.employee-profiles.create')}" class="kt-btn kt-btn-primary">
                        <i class="ki-filled ki-plus text-base"></i>
                        Add New Employee Profile
                    </a>
                </div>                      
            </div>

            <!-- Employee Profiles Table -->
            <div class="kt-card">
                <div class="kt-card-header">
                    <div class="kt-card-toolbar">
                        <div class="flex items-center gap-4">
                            <div class="kt-input max-w-64 w-64">
                                <i class="ki-filled ki-magnifier"></i>
                                <input 
                                    type="text" 
                                    class="kt-input" 
                                    placeholder="Search employee profiles..." 
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
                            {#if salaryMin || salaryMax || recruitmentCostMin || recruitmentCostMax}
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
                            <!-- Salary Range -->
                            <div class="space-y-2">
                                <h4 class="text-sm font-medium text-gray-700">Average Salary Range</h4>
                                
                                <div class="flex gap-2">
                                    <input 
                                        type="number" 
                                        class="kt-input flex-1" 
                                        placeholder="Min Salary" 
                                        bind:value={salaryMin}
                                        on:input={handleFilterChange}
                                        min="0"
                                    />
                                    <input 
                                        type="number" 
                                        class="kt-input flex-1" 
                                        placeholder="Max Salary" 
                                        bind:value={salaryMax}
                                        on:input={handleFilterChange}
                                        min="0"
                                    />
                                </div>
                            </div>

                            <!-- Recruitment Cost Range -->
                            <div class="space-y-2">
                                <h4 class="text-sm font-medium text-gray-700">Recruitment Cost</h4>
                                
                                <div class="flex gap-2">
                                    <input 
                                        type="number" 
                                        class="kt-input flex-1" 
                                        placeholder="Min Cost" 
                                        bind:value={recruitmentCostMin}
                                        on:input={handleFilterChange}
                                        min="0"
                                    />
                                    <input 
                                        type="number" 
                                        class="kt-input flex-1" 
                                        placeholder="Max Cost" 
                                        bind:value={recruitmentCostMax}
                                        on:input={handleFilterChange}
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
                                            <span class="kt-table-col-label">Employee Profile</span>
                                        </span>
                                    </th>
                                    <th class="min-w-[120px]">
                                        <span class="kt-table-col">
                                            <span class="kt-table-col-label">Avg Salary</span>
                                        </span>
                                    </th>
                                    <th class="min-w-[120px]">
                                        <span class="kt-table-col">
                                            <span class="kt-table-col-label">Recruitment Cost</span>
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
                                                <div class="flex flex-col gap-1">
                                                    <div class="kt-skeleton w-32 h-4 rounded"></div>
                                                    <div class="kt-skeleton w-20 h-3 rounded"></div>
                                                </div>
                                            </td>
                                            <td class="p-4">
                                                <div class="kt-skeleton w-16 h-4 rounded"></div>
                                            </td>
                                            <td class="p-4">
                                                <div class="kt-skeleton w-8 h-8 rounded"></div>
                                            </td>
                                        </tr>
                                    {/each}
                                {:else if employeeProfiles.length === 0}
                                    <!-- Empty state -->
                                    <tr>
                                        <td colspan="8" class="p-10">
                                            <div class="flex flex-col items-center justify-center text-center">
                                                <div class="mb-4">
                                                    <i class="ki-filled ki-user text-4xl text-muted-foreground"></i>
                                                </div>
                                                <h3 class="text-lg font-semibold text-mono mb-2">No employee profiles found</h3>
                                                <p class="text-sm text-secondary-foreground mb-4">
                                                    {search ? 'No employee profiles match your search criteria.' : 'Get started by creating your first employee profile.'}
                                                </p>
                                                <a href="{route('admin.employee-profiles.create')}" class="kt-btn kt-btn-primary">
                                                    <i class="ki-filled ki-plus text-base"></i>
                                                    Create First Employee Profile
                                                </a>
                                            </div>
                                        </td>
                                    </tr>
                                {:else}
                                    <!-- Actual data rows -->
                                    {#each employeeProfiles as profile}
                                        <tr class="hover:bg-muted/50">
                                            <td>
                                                <input class="kt-checkbox kt-checkbox-sm" type="checkbox" value={profile.id}/>
                                            </td>
                                            <td>
                                                <span class="text-sm font-medium text-mono">#{profile.id}</span>
                                            </td>
                                            <td>
                                                <div class="flex flex-col gap-1">
                                                    <span class="text-sm font-medium text-mono hover:text-primary">
                                                        {profile.name}
                                                    </span>
                                                    {#if profile.description}
                                                        <span class="text-xs text-muted-foreground">
                                                            {profile.description.slice(0, 50)}{profile.description.length > 50 ? '...' : ''}
                                                        </span>
                                                    {/if}
                                                </div>
                                            </td>
                                            <td>
                                                <div class="flex flex-col gap-1">
                                                    <span class="text-sm font-medium">{profile.avg_salary_month}</span>
                                                    <span class="text-xs text-muted-foreground">
                                                        {profile.min_salary_month} - {profile.max_salary_month}
                                                    </span>
                                                </div>
                                            </td>

                                            <!-- Recruitment Cost -->
                                            <td>
                                                <div class="flex flex-col gap-1">
                                                    <span class="text-sm font-medium">{profile.avg_recruitment_cost}</span>
                                                    <span class="text-xs text-muted-foreground">
                                                        {profile.min_recruitment_cost} - {profile.max_recruitment_cost}
                                                    </span>
                                                </div>
                                            </td>

                                            <td class="text-center">
                                                <div class="kt-menu flex-inline" data-kt-menu="true">
                                                    <div class="kt-menu-item" data-kt-menu-item-offset="0, 10px" data-kt-menu-item-placement="bottom-end" data-kt-menu-item-placement-rtl="bottom-start" data-kt-menu-item-toggle="dropdown" data-kt-menu-item-trigger="click">
                                                        <button class="kt-menu-toggle kt-btn kt-btn-sm kt-btn-icon kt-btn-ghost">
                                                            <i class="ki-filled ki-dots-vertical text-lg"></i>
                                                        </button>
                                                        <div class="kt-menu-dropdown kt-menu-default w-full max-w-[175px]" data-kt-menu-dismiss="true">
                                                            <div class="kt-menu-item">
                                                                <a class="kt-menu-link" href={route('admin.employee-profiles.show', { employeeProfile: profile.id })}>
                                                                    <span class="kt-menu-icon">
                                                                        <i class="ki-filled ki-search-list"></i>
                                                                    </span>
                                                                    <span class="kt-menu-title">View</span>
                                                                </a>
                                                            </div>
                                                            <div class="kt-menu-item">
                                                                <a class="kt-menu-link" href={route('admin.employee-profiles.edit', { employeeProfile: profile.id })}>
                                                                    <span class="kt-menu-icon">
                                                                        <i class="ki-filled ki-pencil"></i>
                                                                    </span>
                                                                    <span class="kt-menu-title">Edit</span>
                                                                </a>
                                                            </div>

                                                            <div class="kt-menu-separator"></div>
                                                            
                                                            <div class="kt-menu-item">
                                                                <button class="kt-menu-link" on:click={() => deleteEmployeeProfile(profile.id)}>
                                                                    <span class="kt-menu-icon">
                                                                        <i class="ki-filled ki-trash"></i>
                                                                    </span>
                                                                    <span class="kt-menu-title">Delete</span>
                                                                </button>
                                                            </div>
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