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

    // Fetch machines data
    async function fetchMachines() {
        loading = true;
        try {
            const params = new URLSearchParams({
                page: currentPage,
                perPage: perPage,
                search: search
            });
            
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
                showToast("Machine deleted successfully!", 'success');

                // Refresh the machines list
                fetchMachines();
            } else {
                const errorData = await response.json().catch(() => ({}));
                const errorMessage = errorData.message || 'Error deleting machine. Please try again.';
                
                showToast(errorMessage, 'destructive');
            }
        } catch (error) {
            console.error('Error deleting machine:', error);
            showToast("Network error. Please check your connection and try again.", 'destructive');
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
                    <a href="{route('admin.machines.create')}" class="kt-btn kt-btn-primary">
                        <i class="ki-filled ki-plus text-base"></i>
                        Add New Machine
                    </a>
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
                        </div>
                    </div>
                </div>
                
                <div class="kt-card-content p-0">
                    <div class="kt-scrollable-x-auto">
                        <table class="kt-table kt-table-border table-fixed">
                            <thead>
                                <tr>
                                    <th style="width: 75px;">
                                        <span class="kt-table-col">
                                            <span class="kt-table-col-label">ID</span>
                                        </span>
                                    </th>
                                    <th>
                                        <span class="kt-table-col">
                                            <span class="kt-table-col-label">Machine</span>
                                        </span>
                                    </th>
                                    <th>
                                        <span class="kt-table-col">
                                            <span class="kt-table-col-label">Performance</span>
                                        </span>
                                    </th>
                                    <th>
                                        <span class="kt-table-col">
                                            <span class="kt-table-col-label">Setup Requirements</span>
                                        </span>
                                    </th>
                                    <th style="width: 80px;">
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
                                                <a href="{route('admin.machines.create')}" class="kt-btn kt-btn-primary">
                                                    <i class="ki-filled ki-plus text-base"></i>
                                                    Add First Machine
                                                </a>
                                            </div>
                                        </td>
                                    </tr>
                                {:else}
                                    <!-- Actual data rows -->
                                    {#each machines as machine}
                                        <tr class="hover:bg-muted/50">
                                            <td>
                                                <span class="text-sm font-medium text-mono">#{machine.id}</span>
                                            </td>
                                            <td>
                                                <div class="flex items-center gap-3">
                                                    {#if machine.image_url}
                                                        <img 
                                                            src={machine.image_url} 
                                                            alt={machine.name}
                                                            class="rounded-lg object-cover"
                                                            style="min-width: 36px; min-height: 36px; max-width: 36px; max-height: 36px;"
                                                        />
                                                    {:else}
                                                        <div class="w-10 h-10 rounded-lg bg-accent/50 flex items-center justify-center">
                                                            <i class="ki-filled ki-package text-lg text-muted-foreground"></i>
                                                        </div>
                                                    {/if}

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
                                                        <span class="text-xs font-medium">{machine.min_speed} - {machine.max_speed} units/day</span>
                                                    </div>
                                                    <div class="flex items-center gap-1">
                                                        <span class="text-xs text-muted-foreground">Quality:</span>
                                                        <span class="text-xs font-medium">{machine.quality_factor * 100}%</span>
                                                    </div>
                                                    <div class="flex items-center gap-1">
                                                        <span class="text-xs text-muted-foreground">Operations Cost:</span>
                                                        <span class="text-xs font-medium">{machine.operations_cost} DZD/week</span>
                                                    </div>
                                                    <div class="flex items-center gap-1">
                                                        <span class="text-xs text-muted-foreground">Carbon Footprint:</span>
                                                        <span class="text-xs font-medium">{machine.carbon_footprint} kg CO2/unit</span>
                                                    </div>
                                                </div>
                                            </td>
                                            <td>
                                                <div class="flex items-center gap-1">
                                                    <span class="text-xs text-muted-foreground">Cost to Acquire:</span>
                                                    <span class="text-xs font-medium">{machine.cost_to_acquire} DZD</span>
                                                </div>
                                                <div class="flex items-center gap-1">
                                                    <span class="text-xs text-muted-foreground">Loss on Sale:</span>
                                                    <span class="text-xs font-medium">{machine.loss_on_sale_days * 100}% / day</span>
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
                                                                <a class="kt-menu-link" href={route('admin.machines.show', { machine: machine.id })}>
                                                                    <span class="kt-menu-icon">
                                                                        <i class="ki-filled ki-search-list"></i>
                                                                    </span>
                                                                    <span class="kt-menu-title">View</span>
                                                                </a>
                                                            </div>
                                                            <div class="kt-menu-item">
                                                                <a class="kt-menu-link" href={route('admin.machines.edit', { machine: machine.id })}>
                                                                    <span class="kt-menu-icon">
                                                                        <i class="ki-filled ki-pencil"></i>
                                                                    </span>
                                                                    <span class="kt-menu-title">Edit</span>
                                                                </a>
                                                            </div>
                                                            <div class="kt-menu-separator"></div>

                                                            <div class="kt-menu-item">
                                                                <button class="kt-menu-link" on:click={() => deleteMachine(machine.id)}>
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