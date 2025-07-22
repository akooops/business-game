<script>
    import AdminLayout from '../../Layouts/AdminLayout.svelte';
    import Pagination from '../../Components/Pagination.svelte';
    import { onMount, tick } from 'svelte';
    import { page } from '@inertiajs/svelte'

    // Define breadcrumbs for this page
    const breadcrumbs = [
        {
            title: 'Companies',
            url: route('admin.companies.index'),
            active: false
        },
        {
            title: 'Index',
            url: route('admin.companies.index'),
            active: true
        }
    ];
    
    const pageTitle = 'Companies';

    // Reactive variables
    let companies = [];
    let pagination = {};
    let loading = true;
    let search = '';
    let perPage = 10;
    let currentPage = 1;
    let searchTimeout;
    let showFilters = false;

    // Filter variables
    let fundsMin = '';
    let fundsMax = '';
    let carbonFootprintMin = '';
    let carbonFootprintMax = '';
    let researchLevelMin = '';
    let researchLevelMax = '';

    // Fetch companies data
    async function fetchCompanies() {
        loading = true;
        try {
            const params = new URLSearchParams({
                page: currentPage,
                perPage: perPage,
                search: search
            });
            
            // Add filter parameters
            if (fundsMin) {
                params.append('funds_min', fundsMin);
            }
            if (fundsMax) {
                params.append('funds_max', fundsMax);
            }
            if (carbonFootprintMin) {
                params.append('carbon_footprint_min', carbonFootprintMin);
            }
            if (carbonFootprintMax) {
                params.append('carbon_footprint_max', carbonFootprintMax);
            }
            if (researchLevelMin) {
                params.append('research_level_min', researchLevelMin);
            }
            if (researchLevelMax) {
                params.append('research_level_max', researchLevelMax);
            }
            
            const response = await fetch(route('admin.companies.index') + '?' + params.toString(), {
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                }
            });
            
            const data = await response.json();
            companies = data.companies;
            pagination = data.pagination;
            
            // Wait for DOM to update, then initialize menus
            await tick();
            if (window.KTMenu) {
                window.KTMenu.init();
            }
        } catch (error) {
            console.error('Error fetching companies:', error);
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
            fetchCompanies();
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
            fetchCompanies();
        }
    }

    // Handle per page change
    function handlePerPageChange(newPerPage) {
        perPage = newPerPage;
        currentPage = 1;
        fetchCompanies();
    }

    // Handle filter changes
    function handleFilterChange() {
        currentPage = 1;
        fetchCompanies();
    }

    // Clear all filters
    function clearAllFilters() {
        fundsMin = '';
        fundsMax = '';
        carbonFootprintMin = '';
        carbonFootprintMax = '';
        researchLevelMin = '';
        researchLevelMax = '';
        currentPage = 1;
        fetchCompanies();
    }

    // Toggle filters visibility
    function toggleFilters() {
        showFilters = !showFilters;
    }

    // Delete company
    async function deleteCompany(companyId) {
        if (!confirm('Are you sure you want to delete this company? This action cannot be undone.')) {
            return;
        }

        try {
            const formData = new FormData();
            formData.append('_method', 'DELETE');
            formData.append('_token', document.querySelector('meta[name="csrf-token"]')?.getAttribute('content'));

            const response = await fetch(route('admin.companies.destroy', { company: companyId }), {
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
                    message: "Company deleted successfully!",
                    variant: "success",
                    position: "bottom-right",
                });

                // Refresh the companies list
                fetchCompanies();
            } else {
                const errorData = await response.json().catch(() => ({}));
                const errorMessage = errorData.message || 'Error deleting company. Please try again.';
                
                KTToast.show({
                    icon: `<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-info-icon lucide-info"><circle cx="12" cy="12" r="10"/><path d="M12 16v-4"/><path d="M12 8h.01"/></svg>`,
                    message: errorMessage,
                    variant: "destructive",
                    position: "bottom-right",
                });
            }
        } catch (error) {
            console.error('Error deleting company:', error);
            
            KTToast.show({
                    icon: `<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-info-icon lucide-info"><circle cx="12" cy="12" r="10"/><path d="M12 16v-4"/><path d="M12 8h.01"/></svg>`,
                    message: "Network error. Please check your connection and try again.",
                    variant: "destructive",
                    position: "bottom-right",
            });
        }
    }

    onMount(() => {
        fetchCompanies();
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
            <!-- Company Header -->
            <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4">
                <div class="flex flex-col gap-1">
                    <h1 class="text-2xl font-bold text-mono">Companies Management</h1>
                    <p class="text-sm text-secondary-foreground">
                        Manage your simulation companies
                    </p>
                </div>
                <div class="flex items-center gap-3">
                    <a href="{route('admin.companies.create')}" class="kt-btn kt-btn-primary">
                        <i class="ki-filled ki-plus text-base"></i>
                        Add New Company
                    </a>
                </div>                      
            </div>

            <!-- Companies Table -->
            <div class="kt-card">
                <div class="kt-card-header">
                    <div class="kt-card-toolbar">
                        <div class="flex items-center gap-4">
                            <div class="kt-input max-w-64 w-64">
                                <i class="ki-filled ki-magnifier"></i>
                                <input 
                                    type="text" 
                                    class="kt-input" 
                                    placeholder="Search companies..." 
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
                            {#if fundsMin || fundsMax || carbonFootprintMin || carbonFootprintMax || researchLevelMin || researchLevelMax}
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
                            <!-- Funds Range -->
                            <div class="space-y-2">
                                <h4 class="text-sm font-medium text-gray-700">Funds (DZD)</h4>
                                
                                <div class="flex gap-2">
                                    <input 
                                        type="number" 
                                        class="kt-input flex-1" 
                                        placeholder="Min Funds" 
                                        bind:value={fundsMin}
                                        on:input={handleFilterChange}
                                        step="1000"
                                        min="0"
                                    />
                                    <input 
                                        type="number" 
                                        class="kt-input flex-1" 
                                        placeholder="Max Funds" 
                                        bind:value={fundsMax}
                                        on:input={handleFilterChange}
                                        step="1000"
                                        min="0"
                                    />
                                </div>
                            </div>

                            <!-- Carbon Footprint Range -->
                            <div class="space-y-2">
                                <h4 class="text-sm font-medium text-gray-700">Carbon Footprint (kg CO2)</h4>
                                
                                <div class="flex gap-2">
                                    <input 
                                        type="number" 
                                        class="kt-input flex-1" 
                                        placeholder="Min Carbon" 
                                        bind:value={carbonFootprintMin}
                                        on:input={handleFilterChange}
                                        step="0.1"
                                        min="0"
                                    />
                                    <input 
                                        type="number" 
                                        class="kt-input flex-1" 
                                        placeholder="Max Carbon" 
                                        bind:value={carbonFootprintMax}
                                        on:input={handleFilterChange}
                                        step="0.1"
                                        min="0"
                                    />
                                </div>
                            </div>

                            <!-- Research Level Range -->
                            <div class="space-y-2">
                                <h4 class="text-sm font-medium text-gray-700">Research Level</h4>
                                
                                <div class="flex gap-2">
                                    <input 
                                        type="number" 
                                        class="kt-input flex-1" 
                                        placeholder="Min Level" 
                                        bind:value={researchLevelMin}
                                        on:input={handleFilterChange}
                                        min="1"
                                    />
                                    <input 
                                        type="number" 
                                        class="kt-input flex-1" 
                                        placeholder="Max Level" 
                                        bind:value={researchLevelMax}
                                        on:input={handleFilterChange}
                                        min="1"
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
                                            <span class="kt-table-col-label">Company</span>
                                        </span>
                                    </th>
                                    <th class="min-w-[150px]">
                                        <span class="kt-table-col">
                                            <span class="kt-table-col-label">Name</span>
                                        </span>
                                    </th>

                                    <th class="min-w-[150px]">
                                        <span class="kt-table-col">
                                            <span class="kt-table-col-label">Performance</span>
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
                                                <div class="kt-skeleton w-16 h-6 rounded"></div>
                                            </td>
                                            <td class="p-4">
                                                <div class="kt-skeleton w-16 h-6 rounded"></div>
                                            </td>
                                            <td class="p-4">
                                                <div class="kt-skeleton w-8 h-8 rounded"></div>
                                            </td>
                                        </tr>
                                    {/each}
                                {:else if companies.length === 0}
                                    <!-- Empty state -->
                                    <tr>
                                        <td colspan="8" class="p-10">
                                            <div class="flex flex-col items-center justify-center text-center">
                                                <div class="mb-4">
                                                    <i class="ki-filled ki-bank text-4xl text-muted-foreground"></i>
                                                </div>
                                                <h3 class="text-lg font-semibold text-mono mb-2">No companies found</h3>
                                                <p class="text-sm text-secondary-foreground mb-4">
                                                    {search ? 'No companies match your search criteria.' : 'Get started by creating your first company.'}
                                                </p>
                                                <a href="{route('admin.companies.create')}" class="kt-btn kt-btn-primary">
                                                    <i class="ki-filled ki-plus text-base"></i>
                                                    Create First Company
                                                </a>
                                            </div>
                                        </td>
                                    </tr>
                                {:else}
                                    <!-- Actual data rows -->
                                    {#each companies as company}
                                        <tr class="hover:bg-muted/50">
                                            <td>
                                                <input class="kt-checkbox kt-checkbox-sm" type="checkbox" value={company.id}/>
                                            </td>
                                            <td>
                                                <span class="text-sm font-medium text-mono">#{company.id}</span>
                                            </td>
                                            <td>
                                                <div class="flex items-center gap-3">
                                                    <div class="flex-shrink-0">
                                                        <img 
                                                            src={company.user.avatarUrl} 
                                                            alt={company.user.fullname}
                                                            class="w-10 h-10 rounded-lg object-cover"
                                                        />
                                                    </div>
                                                    <div class="flex flex-col gap-1">
                                                        <span class="text-sm font-medium text-mono hover:text-primary">
                                                            @{company.user.username}
                                                        </span>
                                                        <span class="text-xs text-secondary-foreground">
                                                            {company.user.email}
                                                        </span>
                                                    </div>
                                                </div>
                                            </td>

                                            <td>
                                                <span class="text-sm font-medium text-mono hover:text-primary">
                                                    {company.user.fullname}
                                                </span>
                                            </td>

                                            <td>
                                                <div class="flex flex-col gap-1">
                                                    <div class="flex items-center gap-1">
                                                        <span class="text-xs text-muted-foreground">Funds:</span>
                                                        <span class="text-xs font-medium">{company.funds}</span>
                                                    </div>
                                                    <div class="flex items-center gap-1">
                                                        <span class="text-xs text-muted-foreground">Carbon Footprint:</span>
                                                        <span class="text-xs font-medium">{company.carbon_footprint}</span>
                                                    </div>
                                                    <div class="flex items-center gap-1">
                                                        <span class="text-xs text-muted-foreground">Research Level:</span>
                                                        <span class="text-xs font-medium">{company.research_level}</span>
                                                    </div>
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
                                                                <a class="kt-menu-link" href={route('admin.companies.show', { company: company.id })}>
                                                                    <span class="kt-menu-icon">
                                                                        <i class="ki-filled ki-search-list"></i>
                                                                    </span>
                                                                    <span class="kt-menu-title">View</span>
                                                                </a>
                                                            </div>
                                                            
                                                            <div class="kt-menu-item">
                                                                <a class="kt-menu-link" href={route('admin.companies.edit', { company: company.id })}>
                                                                    <span class="kt-menu-icon">
                                                                        <i class="ki-filled ki-pencil"></i>
                                                                    </span>
                                                                    <span class="kt-menu-title">Edit</span>
                                                                </a>
                                                            </div>

                                                            <div class="kt-menu-separator"></div>

                                                            <div class="kt-menu-item">
                                                                <button class="kt-menu-link" on:click={() => deleteCompany(company.id)}>
                                                                    <span class="kt-menu-icon">
                                                                        <i class="ki-filled ki-trash"></i>
                                                                    </span>
                                                                    <span class="kt-menu-title">Remove</span>
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