<script>
    import CompanyLayout from '../../Layouts/CompanyLayout.svelte';
    import Pagination from '../../Components/Pagination.svelte';
    import { onMount, tick } from 'svelte';
    import { page } from '@inertiajs/svelte'

    // Define breadcrumbs for this page
    const breadcrumbs = [
        {
            title: 'Transactions',
            url: route('company.transactions.index'),
            active: false
        },
        {
            title: 'Index',
            url: route('company.transactions.index'),
            active: true
        }
    ];
    
    const pageTitle = 'Transactions';

    // Reactive variables
    let transactions = [];
    let pagination = {};
    let loading = true;
    let search = '';
    let perPage = 10;
    let currentPage = 1;
    let searchTimeout;

    // Fetch transactions data
    async function fetchTransactions() {
        loading = true;
        try {
            const response = await fetch(route('company.transactions.index', {
                page: currentPage,
                perPage: perPage,
                search: search
            }), {
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                }
            });
            
            const data = await response.json();
            transactions = data.transactions;
            pagination = data.pagination;
            
            // Wait for DOM to update, then initialize menus
            await tick();
            if (window.KTMenu) {
                window.KTMenu.init();
            }
        } catch (error) {
            console.error('Error fetching transactions:', error);
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
            fetchTransactions();
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
            fetchTransactions();
        }
    }

    // Handle per page change
    function handlePerPageChange(newPerPage) {
        perPage = newPerPage;
        currentPage = 1;
        fetchTransactions();
    }

    // Format currency
    function formatCurrency(amount) {
        return new Intl.NumberFormat('en-US', {
            style: 'currency',
            currency: 'DZD',
            minimumFractionDigits: 2
        }).format(amount);
    }

    // Get transaction type display name
    function getTransactionTypeDisplay(type) {
        const typeMap = {
            'technology': 'Technology Research',
            'purchase': 'Purchase',
            'inventory': 'Inventory Cost',
            'sale_shipping': 'Shipping Cost',
            'sale_payment': 'Sale Payment',
            'employee_recruitment': 'Employee Recruitment',
            'employee_salary': 'Employee Salary',
            'machine_setup': 'Machine Setup',
            'machine_operations': 'Machine Operations',
            'maintenance': 'Maintenance',
            'marketing': 'Marketing',
            'loan_received': 'Loan Received',
            'loan_payment': 'Loan Payment'
        };
        return typeMap[type] || type;
    }

    // Check if transaction type is income (positive)
    function isIncomeTransaction(type) {
        return ['sale_payment', 'loan_received'].includes(type);
    }

    // Get transaction type badge class
    function getTypeBadgeClass(type) {
        if (isIncomeTransaction(type)) {
            return 'kt-badge-success';
        }
        
        switch (type) {
            case 'technology':
                return 'kt-badge-primary';
            case 'purchase':
                return 'kt-badge-info';
            case 'inventory':
                return 'kt-badge-warning';
            case 'maintenance':
                return 'kt-badge-destructive';
            case 'marketing':
                return 'kt-badge-secondary';
            default:
                return 'kt-badge-outline';
        }
    }

    // Format time ago
    function formatTimeAgo(dateString) {
        const date = new Date(dateString);
        const now = new Date();
        const diffInSeconds = Math.floor((now - date) / 1000);
        
        if (diffInSeconds < 60) return 'Just now';
        if (diffInSeconds < 3600) return `${Math.floor(diffInSeconds / 60)}m ago`;
        if (diffInSeconds < 86400) return `${Math.floor(diffInSeconds / 3600)}h ago`;
        return `${Math.floor(diffInSeconds / 86400)}d ago`;
    }

    onMount(() => {
        fetchTransactions();
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
    <title>Business Game - {pageTitle}</title>
</svelte:head>

<CompanyLayout {breadcrumbs} {pageTitle}>
    <!-- Container -->
    <div class="kt-container-fixed">
        <div class="grid gap-5 lg:gap-7.5">
            <!-- Transactions Header -->
            <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4">
                <div class="flex flex-col gap-1">
                    <h1 class="text-2xl font-bold text-mono">Transaction History</h1>
                    <p class="text-sm text-secondary-foreground">
                        View and manage all company financial transactions
                    </p>
                </div>
            </div>

            <!-- Transactions Table -->
            <div class="kt-card">
                <div class="kt-card-header">
                    <div class="kt-card-toolbar">
                        <div class="kt-input max-w-64 w-64">
                            <i class="ki-filled ki-magnifier"></i>
                            <input 
                                type="text" 
                                class="kt-input" 
                                placeholder="Search transactions..." 
                                bind:value={search}
                                on:input={handleSearchInput}
                            />
                        </div>
                    </div>
                </div>
                
                <div class="kt-card-content p-0">
                    <div class="kt-scrollable-x-auto">
                        <table class="kt-table kt-table-border table-fixed">
                            <thead>
                                <tr>
                                    <th class="w-[80px]">
                                        <span class="kt-table-col">
                                            <span class="kt-table-col-label">ID</span>
                                        </span>
                                    </th>
                                    <th class="min-w-[200px]">
                                        <span class="kt-table-col">
                                            <span class="kt-table-col-label">Transaction Type</span>
                                        </span>
                                    </th>
                                    <th class="w-[120px]">
                                        <span class="kt-table-col">
                                            <span class="kt-table-col-label">Type</span>
                                        </span>
                                    </th>
                                    <th class="w-[140px]">
                                        <span class="kt-table-col">
                                            <span class="kt-table-col-label">Amount</span>
                                        </span>
                                    </th>
                                    <th class="w-[120px]">
                                        <span class="kt-table-col">
                                            <span class="kt-table-col-label">Date</span>
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
                                                        <div class="kt-skeleton w-24 h-4 rounded"></div>
                                                        <div class="kt-skeleton w-16 h-3 rounded"></div>
                                                    </div>
                                                </div>
                                            </td>
                                            <td class="p-4">
                                                <div class="kt-skeleton w-16 h-6 rounded"></div>
                                            </td>
                                            <td class="p-4">
                                                <div class="kt-skeleton w-20 h-4 rounded"></div>
                                            </td>
                                            <td class="p-4">
                                                <div class="kt-skeleton w-16 h-4 rounded"></div>
                                            </td>
                                        </tr>
                                    {/each}
                                {:else if transactions.length === 0}
                                    <!-- Empty state -->
                                    <tr>
                                        <td colspan="6" class="p-10">
                                            <div class="flex flex-col items-center justify-center text-center">
                                                <div class="mb-4">
                                                    <i class="fa-solid fa-receipt text-4xl text-muted-foreground"></i>
                                                </div>
                                                <h3 class="text-lg font-semibold text-mono mb-2">No transactions found</h3>
                                                <p class="text-sm text-secondary-foreground mb-4">
                                                    {search ? 'No transactions match your search criteria.' : 'No transactions have been recorded yet.'}
                                                </p>
                                            </div>
                                        </td>
                                    </tr>
                                {:else}
                                    <!-- Actual data rows -->
                                    {#each transactions as transaction}
                                        <tr class="hover:bg-muted/50">
                                            <td>
                                                <span class="text-sm font-medium text-mono">#{transaction.id}</span>
                                            </td>
                                            <td>
                                                <div class="flex items-center gap-3">
                                                    <div class="flex-shrink-0">
                                                        <div class="w-10 h-10 rounded-lg {isIncomeTransaction(transaction.type) ? 'bg-success/10' : 'bg-destructive/10'} flex items-center justify-center">
                                                            <i class="{isIncomeTransaction(transaction.type) ? 'fa-solid fa-arrow-up text-success' : 'fa-solid fa-arrow-down text-destructive'} text-lg"></i>
                                                        </div>
                                                    </div>
                                                    <div class="flex flex-col gap-1">
                                                        <span class="text-sm font-medium text-mono">
                                                            {getTransactionTypeDisplay(transaction.type)}
                                                        </span>
                                                    </div>
                                                </div>
                                            </td>
                                            <td>
                                                <span class="kt-badge {getTypeBadgeClass(transaction.type)}">
                                                    {transaction.type}
                                                </span>
                                            </td>
                                            <td>
                                                <span class="text-sm font-bold {isIncomeTransaction(transaction.type) ? 'text-success' : 'text-destructive'}">
                                                    {isIncomeTransaction(transaction.type) ? '+' : '-'}DZD {transaction.amount}
                                                </span>
                                            </td>
                                            <td>
                                                <span class="text-xs text-muted-foreground">
                                                    {formatTimeAgo(transaction.created_at)}
                                                </span>
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
</CompanyLayout>