<script>
    import AdminLayout from '../../Layouts/AdminLayout.svelte';
    import Pagination from '../../Components/Pagination.svelte';
    import { onMount, tick } from 'svelte';
    import { page } from '@inertiajs/svelte';

    // Define breadcrumbs for this page
    const breadcrumbs = [
        {
            title: 'Banks',
            url: route('admin.banks.index'),
            active: false
        },
        {
            title: 'Index',
            url: route('admin.banks.index'),
            active: true
        }
    ];
    
    const pageTitle = 'Banks';

    // Reactive variables
    let banks = [];
    let pagination = {};
    let loading = true;
    let search = '';
    let perPage = 10;
    let currentPage = 1;
    let searchTimeout;

    // Fetch banks data
    async function fetchBanks() {
        loading = true;
        try {
            const params = new URLSearchParams({
                page: currentPage,
                perPage: perPage,
                search: search
            });
            
            const response = await fetch(route('admin.banks.index') + '?' + params.toString(), {
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                }
            });
            
            const data = await response.json();
            banks = data.banks;
            pagination = data.pagination;
            
            // Wait for DOM to update, then initialize menus
            await tick();
            if (window.KTMenu) {
                window.KTMenu.init();
            }
        } catch (error) {
            console.error('Error fetching banks:', error);
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
            fetchBanks();
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
            fetchBanks();
        }
    }

    // Handle per page change
    function handlePerPageChange(newPerPage) {
        perPage = newPerPage;
        currentPage = 1;
        fetchBanks();
    }

    // Delete bank
    async function deleteBank(bankId) {
        if (!confirm('Are you sure you want to delete this bank? This action cannot be undone.')) {
            return;
        }

        try {
            const formData = new FormData();
            formData.append('_method', 'DELETE');
            formData.append('_token', document.querySelector('meta[name="csrf-token"]')?.getAttribute('content'));

            const response = await fetch(route('admin.banks.destroy', { bank: bankId }), {
                method: 'POST',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: formData
            });

            if (response.ok) {
                // Show success toast
                showToast("Bank deleted successfully!", 'success');

                // Refresh the banks list
                fetchBanks();
            } else {
                const errorData = await response.json().catch(() => ({}));
                const errorMessage = errorData.message || 'Error deleting bank. Please try again.';
                
                showToast(errorMessage, 'destructive');
            }
        } catch (error) {
            console.error('Error deleting bank:', error);
            showToast("Network error. Please check your connection and try again.", 'destructive');
        }
    }

    onMount(() => {
        fetchBanks();
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
            <!-- Banks Header -->
            <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4">
                <div class="flex flex-col gap-1">
                    <h1 class="text-2xl font-bold text-mono">Banks Management</h1>
                    <p class="text-sm text-secondary-foreground">
                        Manage banks and their lending services for the business game
                    </p>
                </div>
                <div class="flex items-center gap-3">
                    <a href="{route('admin.banks.create')}" class="kt-btn kt-btn-primary">
                        <i class="ki-filled ki-plus text-base"></i>
                        Add New Bank
                    </a>
                </div>                      
            </div>

            <!-- Banks Table -->
            <div class="kt-card">
                <div class="kt-card-header">
                    <div class="kt-card-toolbar">
                        <div class="flex items-center gap-4">
                            <div class="kt-input max-w-64 w-64">
                                <i class="ki-filled ki-magnifier"></i>
                                <input 
                                    type="text" 
                                    class="kt-input" 
                                    placeholder="Search banks..." 
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
                                            <span class="kt-table-col-label">Bank</span>
                                        </span>
                                    </th>
                                    <th>
                                        <span class="kt-table-col">
                                            <span class="kt-table-col-label">Loan Terms</span>
                                        </span>
                                    </th>
                                    <th>
                                        <span class="kt-table-col">
                                            <span class="kt-table-col-label">Interest Rate</span>
                                        </span>
                                    </th>
                                    <th>
                                        <span class="kt-table-col">
                                            <span class="kt-table-col-label">Max Amount</span>
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
                                                <div class="kt-skeleton w-20 h-4 rounded"></div>
                                            </td>
                                            <td class="p-4">
                                                <div class="kt-skeleton w-8 h-8 rounded"></div>
                                            </td>
                                        </tr>
                                    {/each}
                                {:else if banks.length === 0}
                                    <!-- Empty state -->
                                    <tr>
                                        <td colspan="6" class="p-10">
                                            <div class="flex flex-col items-center justify-center text-center">
                                                <div class="mb-4">
                                                    <i class="ki-filled ki-bank text-4xl text-muted-foreground"></i>
                                                </div>
                                                <h3 class="text-lg font-semibold text-mono mb-2">No banks found</h3>
                                                <p class="text-sm text-secondary-foreground mb-4">
                                                    {search ? 'No banks match your search criteria.' : 'Get started by adding your first bank.'}
                                                </p>
                                                <a href="{route('admin.banks.create')}" class="kt-btn kt-btn-primary">
                                                    <i class="ki-filled ki-plus text-base"></i>
                                                    Add First Bank
                                                </a>
                                            </div>
                                        </td>
                                    </tr>
                                {:else}
                                    <!-- Actual data rows -->
                                    {#each banks as bank}
                                        <tr class="hover:bg-muted/50">
                                            <td>
                                                <span class="text-sm font-medium text-mono">#{bank.id}</span>
                                            </td>
                                            <td>
                                                <div class="flex items-center gap-3">
                                                    <div class="flex-shrink-0">
                                                        {#if bank.logo_url}
                                                            <img 
                                                                src={bank.logo_url} 
                                                                alt={bank.name}
                                                                class="w-10 h-10 rounded-lg object-cover"
                                                            />
                                                        {:else}
                                                            <div class="w-10 h-10 rounded-lg bg-accent/50 flex items-center justify-center">
                                                                <i class="ki-filled ki-bank text-lg text-muted-foreground"></i>
                                                            </div>
                                                        {/if}
                                                    </div>
                                                    <div class="flex flex-col gap-1">
                                                        <span class="text-sm font-medium text-mono hover:text-primary">
                                                            {bank.name}
                                                        </span>
                                                    </div>
                                                </div>
                                            </td>
                                            <td>
                                                <span class="text-sm text-secondary-foreground">
                                                    {bank.loan_duration_months} months
                                                </span>
                                            </td>
                                            <td>
                                                <span class="text-sm font-medium text-secondary-foreground">
                                                    {(bank.loan_interest_rate * 100).toFixed(2)}%
                                                </span>
                                            </td>
                                            <td>
                                                <span class="text-sm font-medium text-secondary-foreground">
                                                    {bank.loan_max_amount} DZD
                                                </span>
                                            </td>
                                            <td class="text-center">
                                                <div class="kt-menu flex-inline" data-kt-menu="true">
                                                    <div class="kt-menu-item" data-kt-menu-item-offset="0, 10px" data-kt-menu-item-placement="bottom-end" data-kt-menu-item-placement-rtl="bottom-start" data-kt-menu-item-toggle="dropdown" data-kt-menu-item-trigger="click">
                                                        <button class="kt-menu-toggle kt-btn kt-btn-sm kt-btn-icon kt-btn-ghost" aria-label="Bank actions menu">
                                                            <i class="ki-filled ki-dots-vertical text-lg"></i>
                                                        </button>
                                                        <div class="kt-menu-dropdown kt-menu-default w-full max-w-[175px]" data-kt-menu-dismiss="true">
                                                            <div class="kt-menu-item">
                                                                <a class="kt-menu-link" href={route('admin.banks.show', { bank: bank.id })}>
                                                                    <span class="kt-menu-icon">
                                                                        <i class="ki-filled ki-search-list"></i>
                                                                    </span>
                                                                    <span class="kt-menu-title">View</span>
                                                                </a>
                                                            </div>
                                                            <div class="kt-menu-item">
                                                                <a class="kt-menu-link" href={route('admin.banks.edit', { bank: bank.id })}>
                                                                    <span class="kt-menu-icon">
                                                                        <i class="ki-filled ki-pencil"></i>
                                                                    </span>
                                                                    <span class="kt-menu-title">Edit</span>
                                                                </a>
                                                            </div>
                                                            <div class="kt-menu-separator"></div>

                                                            <div class="kt-menu-item">
                                                                <button class="kt-menu-link" on:click={() => deleteBank(bank.id)}>
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