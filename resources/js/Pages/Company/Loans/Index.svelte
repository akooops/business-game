<script>
    import CompanyLayout from '../../Layouts/CompanyLayout.svelte';
    import Pagination from '../../Components/Pagination.svelte';
    import { onMount, onDestroy, tick } from 'svelte';
    import { page } from '@inertiajs/svelte'

    // Define breadcrumbs for this page
    const breadcrumbs = [
        {
            title: 'Loans',
            url: route('company.loans.index'),
            active: false
        },
        {
            title: 'Index',
            url: route('company.loans.index'),
            active: true
        }
    ];
    
    const pageTitle = 'My Loans';

    // Reactive variables
    let loans = [];
    let loading = true;
    let fetchInterval = null;

    // Modal state
    let selectedLoan = null;
    let showPayLoanModal = false;
    let payingLoan = false;

    // Drawer state for loan details
    let selectedLoanDetails = null;
    let showLoanDrawer = false;

    // Fetch loans data
    async function fetchLoans() {
        if(loans.length == 0) loading = true;
        
        try {
            const response = await fetch(route('company.loans.index'), {
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                }
            });
            
            const data = await response.json();
            loans = data.loans;
            
            // Wait for DOM to update, then initialize menus
            await tick();
            if (window.KTMenu) {
                window.KTMenu.init();
            }
        } catch (error) {
            console.error('Error fetching loans:', error);
        } finally {
            loading = false;
        }
    }

    // Open pay loan modal
    function openPayLoanModal(loan) {
        selectedLoan = loan;
        showPayLoanModal = true;
        
        // Show modal
        const toggleButton = document.querySelector('[data-kt-modal-toggle="#pay_loan_modal"]');
        if (toggleButton) {
            toggleButton.click();
        }
    }

    // Close pay loan modal
    function closePayLoanModal() {
        showPayLoanModal = false;
        selectedLoan = null;
        payingLoan = false;
        
        // Simulate button click to use KT framework logic
        const dismissButton = document.querySelector('[data-kt-modal-dismiss="#pay_loan_modal"]');
        if (dismissButton) {
            dismissButton.click();
        }
    }

    // Open loan drawer for loan details
    function openLoanDrawer(loan) {
        selectedLoanDetails = loan;
        showLoanDrawer = true;
        
        // Show drawer
        const toggleButton = document.querySelector('[data-kt-drawer-toggle="#loan_drawer"]');
        if (toggleButton) {
            toggleButton.click();
        }
    }

    // Close loan drawer
    function closeLoanDrawer() {
        showLoanDrawer = false;
        selectedLoanDetails = null;
    }

    // Pay loan completely
    async function payLoan() {
        if (!selectedLoan) return;

        payingLoan = true;
        try {
            const response = await fetch(route('company.loans.pay', selectedLoan.id), {
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
                showToast(data.message || 'Loan paid successfully!', 'success');
                
                // Close modal
                closePayLoanModal();
                
                // Refresh loans data
                fetchLoans();
            } else {
                const errorData = await response.json();
                showToast(errorData.message || 'Error paying loan. Please try again.', 'error');
            }
        } catch (error) {
            console.error('Error paying loan:', error);
            showToast('Network error. Please check your connection and try again.', 'error');
        } finally {
            payingLoan = false;
        }
    }

    // Get status badge class
    function getStatusBadgeClass(loan) {
        if (loan.paid_at) {
            return 'kt-badge-success';
        } else {
            return 'kt-badge-warning';
        }
    }

    // Get status text
    function getStatusText(loan) {
        return loan.paid_at ? 'Paid' : 'Active';
    }

    onMount(() => {
        fetchLoans();
        fetchInterval = setInterval(fetchLoans, 60000);
    });
    
    onDestroy(() => {
        clearInterval(fetchInterval);
    });

    // Flash message handling
    export let success;
    export let error;

    $: if (success) {
        showToast(success, 'success');
    }

    $: if (error) {
        showToast(error, 'error');
    }
</script>

<svelte:head>
    <title>Business game - {pageTitle}</title>
</svelte:head>

<CompanyLayout {breadcrumbs} {pageTitle}>
    <!-- Container -->
    <div class="kt-container-fixed">
        <div class="grid gap-5 lg:gap-7.5">
            <!-- Loans Header -->
            <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4">
                <div class="flex flex-col gap-1">
                    <h1 class="text-2xl font-bold text-mono">My Loans</h1>
                    <p class="text-sm text-secondary-foreground">
                        Track your active loans and payment history
                    </p>
                </div>   
            </div>

            <!-- Loans Grid -->
            <div class="kt-card">
                <div class="kt-card-content p-0">
                    {#if loading}
                        <!-- Loading skeleton -->
                        <div class="p-6">
                            <div class="grid grid-cols-1 gap-6 p-4">
                                {#each Array(5) as _, i}
                                    <div class="kt-card animate-pulse">
                                        <div class="kt-card-header justify-start bg-muted/70 gap-9 h-auto py-5">
                                            <div class="kt-skeleton h-4 w-20"></div>
                                            <div class="kt-skeleton h-4 w-24"></div>
                                            <div class="kt-skeleton h-4 w-16"></div>
                                        </div>
                                        <div class="kt-card-content p-5 lg:p-7.5 space-y-5">
                                            <div class="kt-card">
                                                <div class="kt-card-content flex items-center flex-wrap justify-between gap-4.5 p-2 pe-5">
                                                    <div class="flex items-center gap-3.5">
                                                        <div class="kt-skeleton w-[90px] h-[70px] rounded"></div>
                                                        <div class="flex flex-col gap-1">
                                                            <div class="kt-skeleton h-4 w-32"></div>
                                                            <div class="kt-skeleton h-3 w-24"></div>
                                                        </div>
                                                    </div>
                                                    <div class="flex flex-col gap-1.5">
                                                        <div class="kt-skeleton h-3 w-8"></div>
                                                        <div class="kt-skeleton h-4 w-16"></div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                {/each}
                            </div>
                        </div>
                    {:else if loans.length === 0}
                        <!-- Empty state -->
                        <div class="p-10">
                            <div class="flex flex-col items-center justify-center text-center">
                                <div class="mb-4">
                                    <i class="ki-filled ki-bank text-4xl text-muted-foreground"></i>
                                </div>
                                <h3 class="text-lg font-semibold text-mono mb-2">No loans found</h3>
                                <p class="text-sm text-secondary-foreground mb-4">
                                    You don't have any loans yet. Visit banks to apply for loans when needed.
                                </p>
                                <a href="{route('company.banks.index')}" class="kt-btn kt-btn-primary">
                                    <i class="ki-filled ki-bank text-base"></i>
                                    Browse Banks
                                </a>
                            </div>
                        </div>
                    {:else}
                        <!-- Loans Grid -->
                        <div class="p-6">
                            <div class="grid grid-cols-1 gap-6 p-4">
                                {#each loans as loan}
                                    <div class="kt-card kt-card-hover cursor-pointer" role="button" tabindex="0" on:click={() => openLoanDrawer(loan)} on:keydown={(e) => e.key === 'Enter' && openLoanDrawer(loan)}>
                                        <div class="kt-card-header justify-start bg-muted/70 gap-9 h-auto py-5">
                                            <div class="flex flex-col gap-1.5">
                                                <span class="text-xs font-normal text-secondary-foreground">
                                                    Loan ID
                                                </span>
                                                <span class="text-sm font-medium text-mono">
                                                    #{loan.id}
                                                </span>
                                            </div>
                                            <div class="flex flex-col gap-1.5">
                                                <span class="text-xs font-normal text-secondary-foreground">
                                                    Status
                                                </span>
                                                <span class="kt-badge kt-badge-sm {getStatusBadgeClass(loan)}">
                                                    {getStatusText(loan)}
                                                </span>
                                            </div>
                                            <div class="flex flex-col gap-1.5">
                                                <span class="text-xs font-normal text-secondary-foreground">
                                                    Borrowed
                                                </span>
                                                <span class="text-sm font-medium text-mono">
                                                    {formatTimestamp(loan.borrowed_at)}
                                                </span>
                                            </div>
                                            {#if loan.paid_at}
                                            <div class="flex flex-col gap-1.5">
                                                <span class="text-xs font-normal text-secondary-foreground">
                                                    Paid At
                                                </span>
                                                <span class="text-sm font-medium text-mono">
                                                    {formatTimestamp(loan.paid_at)}
                                                </span>
                                            </div>
                                            {/if}
                                            {#if !loan.paid_at}
                                            <div class="flex flex-col gap-1.5 ml-auto">
                                                <button 
                                                    class="kt-btn kt-btn-sm kt-btn-primary"
                                                    on:click|stopPropagation={() => openPayLoanModal(loan)}
                                                >
                                                    <i class="ki-filled ki-wallet text-sm"></i>
                                                    Pay Loan
                                                </button>
                                            </div>
                                            {/if}
                                        </div>
                                        <div class="kt-card-content p-5 lg:p-7.5 space-y-5">
                                            <div class="kt-card">
                                                <div class="kt-card-content flex items-center flex-wrap justify-between gap-4.5 p-2 pe-5">
                                                    <div class="flex items-center gap-3.5">
                                                        <div class="kt-card flex items-center justify-center bg-accent/50 h-[70px] w-[90px] shadow-none">
                                                            {#if loan.bank.logo_url}
                                                                <img 
                                                                    alt={loan.bank.name}
                                                                    class="cursor-pointer h-[70px] object-cover rounded-sm" 
                                                                    src={loan.bank.logo_url}
                                                                />
                                                            {:else}
                                                                <i class="ki-filled ki-bank text-2xl text-muted-foreground"></i>
                                                            {/if}
                                                        </div>
                                                        <div class="flex flex-col gap-1">
                                                            <span class="hover:text-primary text-sm font-medium text-mono leading-5.5">
                                                                {loan.bank.name}
                                                            </span>
                                                            <div class="flex items-center gap-1.5">
                                                                <span class="text-xs font-normal text-secondary-foreground uppercase">
                                                                    Interest Rate:
                                                                    <span class="text-xs font-medium text-foreground">
                                                                        {(loan.interest_rate * 100).toFixed(2)}%
                                                                    </span>
                                                                </span>
                                                            </div>
                                                            <div class="flex items-center gap-1.5">
                                                                <span class="text-xs font-normal text-secondary-foreground uppercase">
                                                                    Duration:
                                                                    <span class="text-xs font-medium text-foreground">
                                                                        {loan.duration_months} months
                                                                    </span>
                                                                </span>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="flex flex-col gap-1.5">
                                                        <span class="text-xs font-normal text-secondary-foreground text-end">
                                                            {#if loan.paid_at}
                                                                Total Paid
                                                            {:else}
                                                                Remaining
                                                            {/if}
                                                        </span>
                                                        <div class="flex items-center flex-wrap gap-1.5">
                                                            <span class="text-sm font-semibold text-mono">
                                                                {#if loan.paid_at}
                                                                    DZD {loan.total_amount}
                                                                {:else}
                                                                    DZD {loan.remaining_amount}
                                                                {/if}
                                                            </span>
                                                        </div>
                                                        <span class="text-xs font-normal text-secondary-foreground text-end">
                                                            Monthly: DZD {loan.monthly_payment}
                                                        </span>
                                                    </div>
                                                </div>
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

    <!-- Hidden button to trigger modal -->
    <button style="display:none" data-kt-modal-toggle="#pay_loan_modal" aria-label="Toggle pay loan modal"></button>

    <!-- Hidden button to trigger drawer -->
    <button style="display:none" data-kt-drawer-toggle="#loan_drawer" aria-label="Toggle loan drawer"></button>

    <!-- Pay Loan Modal -->
    <div class="kt-modal" data-kt-modal="true" id="pay_loan_modal">
        <div class="kt-modal-content max-w-[600px] top-[5%]">
            <div class="kt-modal-header">
                <h3 class="kt-modal-title">Pay Loan</h3>
                <button
                    type="button"
                    class="kt-modal-close"
                    aria-label="Close modal"
                    data-kt-modal-dismiss="#pay_loan_modal"
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
                {#if selectedLoan}
                    <div class="space-y-4">
                        <!-- Bank Info -->
                        <div class="flex items-center gap-4">
                            <div class="flex-shrink-0">
                                {#if selectedLoan.bank.logo_url}
                                    <img 
                                        src={selectedLoan.bank.logo_url} 
                                        alt={selectedLoan.bank.name}
                                        class="w-16 h-16 rounded-lg object-cover"
                                    />
                                {:else}
                                    <div class="w-16 h-16 rounded-lg bg-accent/50 flex items-center justify-center">
                                        <i class="ki-filled ki-bank text-xl text-muted-foreground"></i>
                                    </div>
                                {/if}
                            </div>
                            <div class="flex-1">
                                <h4 class="font-semibold text-mono mb-1">{selectedLoan.bank.name}</h4>
                                <p class="text-sm text-muted-foreground mb-1">Loan ID: #{selectedLoan.id}</p>
                                <div class="flex gap-2">
                                    <span class="text-xs bg-blue-100 text-blue-800 px-2 py-1 rounded">
                                        {(selectedLoan.interest_rate * 100).toFixed(2)}% Interest
                                    </span>
                                    <span class="text-xs bg-green-100 text-green-800 px-2 py-1 rounded">
                                        {selectedLoan.duration_months} months
                                    </span>
                                </div>
                            </div>
                        </div>

                        <!-- Loan Details -->
                        <div class="kt-card">
                            <div class="kt-card-body p-4">
                                <h5 class="font-medium text-mono mb-3">Loan Details</h5>
                                <div class="space-y-2">
                                    <div class="flex justify-between">
                                        <span class="text-sm text-muted-foreground">Borrowed Amount:</span>
                                        <span class="font-medium">DZD {selectedLoan.amount}</span>
                                    </div>
                                    <div class="flex justify-between">
                                        <span class="text-sm text-muted-foreground">Total with Interest:</span>
                                        <span class="font-medium">DZD {selectedLoan.total_amount}</span>
                                    </div>
                                    <div class="flex justify-between">
                                        <span class="text-sm text-muted-foreground">Monthly Payment:</span>
                                        <span class="font-medium">DZD {selectedLoan.monthly_payment}</span>
                                    </div>
                                    <div class="flex justify-between">
                                        <span class="text-sm text-muted-foreground">Borrowed At:</span>
                                        <span class="font-medium">{formatTimestamp(selectedLoan.borrowed_at)}</span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Payment Summary -->
                        <div class="kt-card bg-accent/50">
                            <div class="kt-card-header px-5">
                                <h3 class="kt-card-title">
                                    Payment Summary
                                </h3>
                            </div>
                            <div class="kt-card-content px-5 py-4 space-y-2">
                                <div class="flex justify-between items-center">
                                    <span class="text-sm font-normal text-secondary-foreground">
                                        Amount to Pay
                                    </span>
                                    <span class="text-lg font-bold text-primary">
                                        DZD {selectedLoan.remaining_amount}
                                    </span>
                                </div>
                            </div>
                        </div>

                        <!-- Important Notice -->
                        <div class="kt-card bg-warning/10 border-warning/20">
                            <div class="kt-card-body p-4">
                                <div class="flex items-start gap-3">
                                    <i class="ki-filled ki-information text-warning text-lg mt-0.5"></i>
                                    <div class="space-y-2">
                                        <h5 class="font-medium text-warning">Important Notice</h5>
                                        <div class="text-sm text-warning/80 space-y-1">
                                            <p>• You will pay the full remaining amount of <strong>DZD {selectedLoan.remaining_amount}</strong></p>
                                            <p>• This will completely close the loan and stop monthly payments</p>
                                            <p>• Make sure you have sufficient funds before proceeding</p>
                                            <p>• This action cannot be undone</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Confirmation Message -->
                        <div class="text-sm text-muted-foreground">
                            Are you sure you want to pay off this loan completely? This will deduct the remaining amount from your company funds.
                        </div>
                    </div>
                {/if}
            </div>
            <div class="kt-modal-footer">
                <div></div>
                <div class="flex gap-4">
                    <button
                        class="kt-btn kt-btn-secondary"
                        data-kt-modal-dismiss="#pay_loan_modal"
                        on:click={closePayLoanModal}
                        disabled={payingLoan}
                    >
                        Cancel
                    </button>
                    <button 
                        class="kt-btn kt-btn-primary"
                        on:click={payLoan}
                        disabled={payingLoan}
                    >
                        {#if payingLoan}
                            <i class="ki-filled ki-loading animate-spin text-sm"></i>
                            Processing...
                        {:else}
                            <i class="ki-filled ki-wallet text-sm"></i>
                            Pay Loan
                        {/if}
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Loan Details Drawer -->
    <div class="hidden kt-drawer kt-drawer-end card flex-col max-w-[90%] w-[450px] top-5 bottom-5 end-5 rounded-xl border border-border" data-kt-drawer="true" data-kt-drawer-container="body" id="loan_drawer">
        <div class="flex items-center justify-between gap-2.5 text-sm text-mono font-semibold px-5 py-2.5 border-b border-b-border">
            Loan Details
            <button class="kt-btn kt-btn-sm kt-btn-icon kt-btn-dim shrink-0" data-kt-drawer-dismiss="true" on:click={closeLoanDrawer} aria-label="Close loan drawer">
                <i class="ki-filled ki-cross"></i>
            </button>
        </div>
        
        <div class="kt-card-content flex flex-col space-y-3 p-5 kt-scrollable-y-auto">
            {#if selectedLoanDetails}
                <!-- Bank Logo -->
                <div class="kt-card relative items-center justify-center bg-accent/50 mb-6.5 h-[180px] shadow-none">
                    {#if selectedLoanDetails.bank.logo_url}
                        <img 
                            src={selectedLoanDetails.bank.logo_url} 
                            alt={selectedLoanDetails.bank.name}
                            class="h-[180px] w-full object-cover rounded-sm"
                        />
                    {:else}
                        <div class="flex items-center justify-center h-full">
                            <i class="ki-filled ki-bank text-4xl text-muted-foreground"></i>
                        </div>
                    {/if}
                </div>

                <!-- Bank Name -->
                <span class="text-base font-medium text-mono">
                    {selectedLoanDetails.bank.name}
                </span>

                <!-- Loan Details -->
                <div class="flex flex-col gap-2.5">
                    <div class="flex items-center gap-2.5">
                        <span class="text-xs font-normal text-foreground min-w-14 xl:min-w-24 shrink-0">
                            Loan ID
                        </span>
                        <div>
                            <span class="text-xs font-medium text-foreground">
                                #{selectedLoanDetails.id}
                            </span>
                        </div>
                    </div>
                    <div class="flex items-center gap-2.5">
                        <span class="text-xs font-normal text-foreground min-w-14 xl:min-w-24 shrink-0">
                            Status
                        </span>
                        <div>
                            <span class="kt-badge kt-badge-sm {getStatusBadgeClass(selectedLoanDetails)}">
                                {getStatusText(selectedLoanDetails)}
                            </span>
                        </div>
                    </div>
                    <div class="flex items-center gap-2.5">
                        <span class="text-xs font-normal text-foreground min-w-14 xl:min-w-24 shrink-0">
                            Borrowed Amount
                        </span>
                        <div>
                            <span class="text-xs font-medium text-foreground">
                                DZD {selectedLoanDetails.amount}
                            </span>
                        </div>
                    </div>
                    <div class="flex items-center gap-2.5">
                        <span class="text-xs font-normal text-foreground min-w-14 xl:min-w-24 shrink-0">
                            Interest Rate
                        </span>
                        <div>
                            <span class="text-xs font-medium text-foreground">
                                {(selectedLoanDetails.interest_rate * 100).toFixed(2)}%
                            </span>
                        </div>
                    </div>
                    <div class="flex items-center gap-2.5">
                        <span class="text-xs font-normal text-foreground min-w-14 xl:min-w-24 shrink-0">
                            Duration
                        </span>
                        <div>
                            <span class="text-xs font-medium text-foreground">
                                {selectedLoanDetails.duration_months} months
                            </span>
                        </div>
                    </div>
                    <div class="flex items-center gap-2.5">
                        <span class="text-xs font-normal text-foreground min-w-14 xl:min-w-24 shrink-0">
                            Total Amount
                        </span>
                        <div>
                            <span class="text-xs font-medium text-foreground">
                                DZD {selectedLoanDetails.total_amount}
                            </span>
                        </div>
                    </div>
                    <div class="flex items-center gap-2.5">
                        <span class="text-xs font-normal text-foreground min-w-14 xl:min-w-24 shrink-0">
                            Remaining Amount
                        </span>
                        <div>
                            <span class="text-xs font-medium text-foreground">
                                DZD {selectedLoanDetails.remaining_amount}
                            </span>
                        </div>
                    </div>
                    <div class="flex items-center gap-2.5">
                        <span class="text-xs font-normal text-foreground min-w-14 xl:min-w-24 shrink-0">
                            Monthly Payment
                        </span>
                        <div>
                            <span class="text-xs font-medium text-foreground">
                                DZD {selectedLoanDetails.monthly_payment}
                            </span>
                        </div>
                    </div>
                    <div class="flex items-center gap-2.5">
                        <span class="text-xs font-normal text-foreground min-w-14 xl:min-w-24 shrink-0">
                            Borrowed At
                        </span>
                        <div>
                            <span class="text-xs font-medium text-foreground">
                                {formatTimestamp(selectedLoanDetails.borrowed_at)}
                            </span>
                        </div>
                    </div>
                    {#if selectedLoanDetails.paid_at}
                        <div class="flex items-center gap-2.5">
                            <span class="text-xs font-normal text-foreground min-w-14 xl:min-w-24 shrink-0">
                                Paid At
                            </span>
                            <div>
                                <span class="text-xs font-medium text-foreground">
                                    {formatTimestamp(selectedLoanDetails.paid_at)}
                                </span>
                            </div>
                        </div>
                    {/if}
                </div>                
            {/if}
        </div>
    </div>
</CompanyLayout>