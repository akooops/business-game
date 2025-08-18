<script>
    import { onMount, onDestroy, tick } from 'svelte';

    let transactions = [];
    let pagination = {};
    let loading = false;
    let search = '';
    let perPage = 20;
    let currentPage = 1;
    let searchTimeout;
    let drawerOpen = false;
    let fetchInterval;
    
    // Company stats
    let companyStats = {
        funds: 0,
        unpaidLoans: 0,
        researchLevel: 0,
        carbonFootprint: 0
    };

    // Fetch company stats from UtilitiesController
    async function fetchCompanyStats() {
        try {
            const response = await fetch(route('utilities.index'), {
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                }
            });
            const data = await response.json();
            companyStats = {
                funds: data.funds,
                unpaidLoans: data.unpaidLoans,
                researchLevel: data.researchLevel,
                carbonFootprint: data.carbonFootprint
            };
        } catch (error) {
            console.error('Error fetching company stats:', error);
        }
    }

    // Fetch transactions from TransactionsController
    async function fetchTransactions() {
        try {
            loading = true;
            
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
        if (searchTimeout) {
            clearTimeout(searchTimeout);
        }
        
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

    // Toggle drawer
    function toggleDrawer() {
        drawerOpen = !drawerOpen;
        if (drawerOpen) {
            fetchTransactions();
        }
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
            'machine_sold': 'Machine Sold',
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
        return ['sale_payment', 'loan_received', 'machine_sold'].includes(type);
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
        fetchCompanyStats();
        
        // Fetch company stats every 30 seconds
        fetchInterval = setInterval(fetchCompanyStats, 30000);
    });

    // Cleanup interval on component destroy
    onDestroy(() => {
        if (fetchInterval) {
            clearInterval(fetchInterval);
        }
    });
</script>

<!-- Company Stats Button -->
<button 
    class="kt-btn kt-btn-primary kt-btn-icon size-9 rounded-full relative" 
    data-kt-drawer-toggle="#transactions_drawer"
    on:click={toggleDrawer}
>    
    <i class="fa-solid fa-coins text-lg"></i>
</button>

<!-- Transactions Drawer -->
<div 
    class="hidden kt-drawer kt-drawer-end card flex-col max-w-[90%] w-[600px] top-5 bottom-5 end-5 rounded-xl border border-border" 
    data-kt-drawer="true" 
    data-kt-drawer-container="body" 
    id="transactions_drawer"
>
    <div class="flex items-center justify-between gap-2.5 text-sm text-mono font-semibold px-5 py-2.5 border-b border-b-border" id="transactions_header">
        Company Overview
        <button class="kt-btn kt-btn-sm kt-btn-icon kt-btn-dim shrink-0" data-kt-drawer-dismiss="true">
            <i class="ki-filled ki-cross"></i>
        </button>
    </div>
    
    <!-- Company Stats -->
    <div class="px-5 py-4 border-b border-b-border">
        <div class="grid grid-cols-4 text-center">
            <div class="flex flex-col">
                <span class="text-xs text-muted-foreground mb-1">Funds (DZD)</span>
                <span class="text-lg font-bold text-success">{companyStats.funds}</span>
            </div>
            <div class="flex flex-col">
                <span class="text-xs text-muted-foreground mb-1">Unpaid Loans (DZD)</span>
                <span class="text-lg font-bold text-destructive">{companyStats.unpaidLoans}</span>
            </div>
            <div class="flex flex-col">
                <span class="text-xs text-muted-foreground mb-1">Research (Level)</span>
                <span class="text-lg font-bold text-primary">{companyStats.researchLevel}</span>
            </div>
            <div class="flex flex-col">
                <span class="text-xs text-muted-foreground mb-1">Carbon (kg CO2)</span>
                <span class="text-lg font-bold text-destructive">{companyStats.carbonFootprint}</span>
            </div>
        </div>
    </div>
    
    <div class="grow flex flex-col mt-4 kt-scrollable-y-auto" data-kt-scrollable="true" data-kt-scrollable-dependencies="#header" data-kt-scrollable-max-height="auto" data-kt-scrollable-offset="0px">
        <!-- Search Bar -->
        <div class="px-5 pb-3">
            <div class="kt-input max-w-full">
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
        
        <div>
            {#if loading}
                <div class="flex items-center justify-center py-8">
                    <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-primary"></div>
                </div>
            {:else if transactions.length === 0}
                <div class="flex flex-col items-center justify-center py-8 text-center">
                    <i class="fa-solid fa-receipt text-4xl text-muted-foreground mb-4"></i>
                    <p class="text-muted-foreground">
                        {search ? 'No transactions match your search criteria.' : 'No transactions yet'}
                    </p>
                </div>
            {:else}
                <div class="grow flex flex-col gap-5 pt-3 pb-4 divider-y divider-border">
                    {#each transactions as transaction, index}
                        <div class="flex grow gap-2.5 px-5 transition-all duration-300">
                            <div class="kt-avatar size-8">
                                <div class="kt-avatar-image">
                                    <i class="{isIncomeTransaction(transaction.type) ? 'fa-solid fa-arrow-up text-success' : 'fa-solid fa-arrow-down text-destructive'} text-lg"></i>
                                </div>
                            </div>
                            <div class="flex flex-col gap-1 flex-1">
                                <div class="text-sm font-medium mb-px">
                                    <span class="text-mono font-semibold">
                                        {getTransactionTypeDisplay(transaction.type)}
                                    </span>
                                </div>
                                <div class="flex items-center justify-between">
                                    <span class="text-sm {isIncomeTransaction(transaction.type) ? 'text-success' : 'text-destructive'} font-bold">
                                        {isIncomeTransaction(transaction.type) ? '+' : '-'}DZD {transaction.amount}
                                    </span>
                                    <span class="flex items-center text-xs font-medium text-muted-foreground">
                                        {formatTimeAgo(transaction.created_at)}
                                    </span>
                                </div>
                            </div>
                        </div>
                        {#if index < pagination.total - 1}
                            <div class="border-b border-b-border"></div>
                        {/if}
                    {/each}
                </div>
            {/if}
        </div>
        
        {#if pagination && pagination.total > 0}
            <div class="border-b border-b-border"></div>
            <div class="p-5">
                <div class="flex items-center justify-between">
                    <div class="text-sm text-muted-foreground">
                        Showing {pagination.from || 0} to {pagination.to || 0} of {pagination.total} transactions
                    </div>
                    <div class="flex items-center gap-2">
                        {#if pagination.current_page > 1}
                            <button 
                                class="kt-btn kt-btn-sm kt-btn-outline"
                                on:click={() => goToPage(pagination.current_page - 1)}
                            >
                                Previous
                            </button>
                        {/if}
                        {#if pagination.current_page < pagination.last_page}
                            <button 
                                class="kt-btn kt-btn-sm kt-btn-outline"
                                on:click={() => goToPage(pagination.current_page + 1)}
                            >
                                Next
                            </button>
                        {/if}
                    </div>
                </div>
            </div>
        {/if}
    </div>

    <div class="flex items-center justify-between" id="transactions_footer">
        <div class="border-b border-b-border"></div>
        <div class="grid p-5 gap-2.5" id="transactions_all_footer">
            <a class="kt-btn kt-btn-primary justify-center" href={route('company.transactions.index')}>
                View all transactions
            </a>
        </div>
    </div>
</div>

<!-- End of Transactions -->
