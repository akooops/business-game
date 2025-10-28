<script>
    import CompanyLayout from '../../Layouts/CompanyLayout.svelte';
    import { onMount, onDestroy, tick } from 'svelte';
    import { page } from '@inertiajs/svelte'

    // Define breadcrumbs for this page
    const breadcrumbs = [
        {
            title: 'Banks',
            url: route('company.banks.index'),
            active: false
        },
        {
            title: 'Index',
            url: route('company.banks.index'),
            active: true
        }
    ];
    
    const pageTitle = 'Banks';

    // Reactive variables
    let banks = [];
    let loading = true;
    let fetchInterval = null;

    // Drawer state
    let selectedBank = null;
    let showBankDrawer = false;

    // Loan modal state
    let showLoanModal = false;
    let loanBank = null;
    let loanAmount = '';
    let loanData = null;

    // Fetch banks data
    async function fetchBanks() {
        if(banks.length == 0) loading = true;
        
        try {

            const response = await fetch(route('company.banks.index'), {
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                }
            });
            
            const data = await response.json();
            banks = data.banks;
            
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

    // Open bank drawer
    function openBankDrawer(bank) {
        selectedBank = bank;
        showBankDrawer = true;
        
        // Show drawer
        const toggleButton = document.querySelector('[data-kt-drawer-toggle="#bank_drawer"]');
        if (toggleButton) {
            toggleButton.click();
        }
    }

    // Close bank drawer
    function closeBankDrawer() {
        showBankDrawer = false;
        selectedBank = null;
    }

    // Open loan modal
    function openLoanModal(bank, event) {
        event.stopPropagation(); // Prevent opening the drawer
        loanBank = bank;
        loanAmount = '';
        loanData = null;
        showLoanModal = true;
        
        // Show modal
        const toggleButton = document.querySelector('[data-kt-modal-toggle="#loan_modal"]');
        if (toggleButton) {
            toggleButton.click();
        }
    }

    // Close loan modal
    function closeLoanModal() {
        // Simulate button click to use KT framework logic
        const dismissButton = document.querySelector('[data-kt-modal-dismiss="#loan_modal"]');
        if (dismissButton) {
            dismissButton.click();
        }
        
        showLoanModal = false;
        loanBank = null;
        loanAmount = '';
        loanData = null;
    }

    // Calculate loan data
    function calculateLoanData() {
        if (!loanBank || !loanAmount || parseFloat(loanAmount) <= 0) {
            loanData = null;
            return;
        }

        const amount = parseFloat(loanAmount);
        const interestRate = parseFloat(loanBank.loan_interest_rate); // Already in decimal format (0-1)
        const months = parseInt(loanBank.loan_duration_months);
        
        // Calculate monthly payment: (amount * interest rate) / months
        const monthlyPayment = (amount * (interestRate + 1)) / months;
        const totalInterest = amount * interestRate;
        const totalRepayment = amount + totalInterest;

        loanData = {
            bank: loanBank,
            principal: amount,
            interestRate: loanBank.loan_interest_rate * 100, // Convert to percentage for display
            months: months,
            monthlyPayment: monthlyPayment.toFixed(3),
            totalInterest: totalInterest.toFixed(3),
            totalRepayment: totalRepayment.toFixed(3)
        };
    }

    // Handle loan amount change
    function handleLoanAmountChange() {
        calculateLoanData();
    }

    // Make loan
    async function makeLoan() {
        if (!loanData) return;

        try {
            const response = await fetch(route('company.loans.store'), {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content'),
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: JSON.stringify({
                    bank_id: loanBank.id,
                    amount: loanData.principal
                })
            });

            if (response.ok) {
                const data = await response.json();
                
                // Show success toast
                showToast(data.message || 'Loan approved successfully!', 'success');
                
                // Close modal
                closeLoanModal();
                
                // Refresh banks data
                fetchBanks();
            } else {
                const errorData = await response.json();
                showToast(errorData.message || 'Error processing loan. Please try again.', 'error');
            }
        } catch (error) {
            console.error('Error making loan:', error);
            showToast('Network error. Please check your connection and try again.', 'error');
        }
    }
    
    onMount(() => {
        fetchBanks();
        fetchInterval = setInterval(fetchBanks, 60000);
    });

    onDestroy(() => {
        if (fetchInterval) {
            clearInterval(fetchInterval);
        }
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
            <!-- Banks Header -->
            <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4">
                <div class="flex flex-col gap-1">
                    <h1 class="text-2xl font-bold text-mono">Banks</h1>
                    <p class="text-sm text-secondary-foreground">
                        Browse available banks in the market
                    </p>
                </div>                      
            </div>

            <!-- Banks Grid -->
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
                    {:else if banks.length === 0}
                        <!-- Empty state -->
                        <div class="p-10">
                            <div class="flex flex-col items-center justify-center text-center">
                                <div class="mb-4">
                                    <i class="ki-filled ki-ship text-4xl text-muted-foreground"></i>
                                </div>
                                <h3 class="text-lg font-semibold text-mono mb-2">No banks found</h3>
                                <p class="text-sm text-secondary-foreground mb-4">
                                    No banks available in the market.
                                </p>
                            </div>
                        </div>
                    {:else}
                        <!-- Banks Grid -->
                        <div class="p-6">
                            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6 p-4">
                                {#each banks as bank}
                                    <div class="kt-card kt-card-hover cursor-pointer" role="button" tabindex="0" on:click={() => openBankDrawer(bank)} on:keydown={(e) => e.key === 'Enter' && openBankDrawer(bank)}>
                                        <div class="kt-card-content flex flex-col items-center lg:pt-10">
                                            <div class="mb-3">
                                                <div class="size-20 relative">
                                                    {#if bank.logo_url}
                                                        <img 
                                                            class="rounded-full w-20 h-20 object-cover" 
                                                            src={bank.logo_url}
                                                            alt={bank.name}
                                                        />
                                                    {:else}
                                                        <div class="w-20 h-20 rounded-full bg-accent/50 flex items-center justify-center">
                                                            <i class="ki-filled ki-bank-1 text-2xl text-muted-foreground"></i>
                                                        </div>
                                                    {/if}
                                                </div>
                                            </div>
                                            <div class="flex items-center justify-center gap-1.5 mb-3">
                                                <span class="hover:text-primary text-center text-base leading-5 font-medium text-mono">
                                                    {bank.name}
                                                </span>
                                            </div>

                                            <div class="flex flex-col gap-1 text-xs text-secondary-foreground mb-4">
                                                <div class="flex justify-center gap-1">
                                                    <i class="ki-filled ki-percentage text-blue-500"></i>
                                                    <span>Interest: {(bank.loan_interest_rate * 100).toFixed(2)}%</span>
                                                </div>
                                                <div class="flex justify-center gap-1">
                                                    <i class="ki-filled ki-calendar text-green-500"></i>
                                                    <span>Duration: {bank.loan_duration_months} months</span>
                                                </div>
                                                <div class="flex justify-center gap-1">
                                                    <i class="ki-filled ki-dollar text-orange-500"></i>
                                                    <span>Max Amount: {bank.loan_max_amount} DZD</span>
                                                </div>
                                            </div>

                                            <!-- Make Loan Button -->
                                            <div class="mt-4">
                                                <button 
                                                    class="kt-btn kt-btn-sm kt-btn-primary"
                                                    on:click={(e) => openLoanModal(bank, e)}
                                                >
                                                    <i class="ki-filled ki-bank text-sm"></i>
                                                    Apply for Loan
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
        </div>
    </div>
    <!-- End of Container -->

    <!-- Hidden button to trigger drawer -->
    <button style="display:none" data-kt-drawer-toggle="#bank_drawer" aria-label="Toggle bank drawer"></button>

    <!-- Hidden button to trigger modal -->
    <button style="display:none" data-kt-modal-toggle="#loan_modal" aria-label="Toggle loan modal"></button>

    <!-- Bank Details Drawer -->
    <div class="hidden kt-drawer kt-drawer-end card flex-col max-w-[90%] w-[450px] top-5 bottom-5 end-5 rounded-xl border border-border" data-kt-drawer="true" data-kt-drawer-container="body" id="bank_drawer">
        <div class="flex items-center justify-between gap-2.5 text-sm text-mono font-semibold px-5 py-2.5 border-b border-b-border">
            Bank Details
            <button class="kt-btn kt-btn-sm kt-btn-icon kt-btn-dim shrink-0" data-kt-drawer-dismiss="true" on:click={closeBankDrawer} aria-label="Close bank drawer">
                <i class="ki-filled ki-cross"></i>
            </button>
        </div>
        
        <div class="kt-card-content flex flex-col space-y-3 p-5 kt-scrollable-y-auto">
            {#if selectedBank}
                <!-- Bank Image -->
                <div class="kt-card relative items-center justify-center bg-accent/50 mb-6.5 h-[180px] shadow-none">
                    {#if selectedBank.logo_url}
                        <img 
                            src={selectedBank.logo_url} 
                            alt={selectedBank.name}
                            class="h-[180px] w-full object-cover rounded-sm"
                        />
                    {:else}
                        <div class="flex items-center justify-center h-full">
                            <i class="ki-filled ki-shop text-4xl text-muted-foreground"></i>
                        </div>
                    {/if}
                </div>

                <!-- Bank Name -->
                <span class="text-base font-medium text-mono">
                    {selectedBank.name}
                </span>

                <!-- Bank Details -->
                <div class="flex flex-col gap-2.5">
                    <div class="flex items-center gap-2.5">
                        <span class="text-xs font-normal text-foreground min-w-14 xl:min-w-24 shrink-0">
                            Interest Rate
                        </span>
                        <div>
                            <span class="text-xs font-medium text-foreground">
                                {(selectedBank.loan_interest_rate * 100).toFixed(2)}%
                            </span>
                        </div>
                    </div>

                    <div class="flex items-center gap-2.5">
                        <span class="text-xs font-normal text-foreground min-w-14 xl:min-w-24 shrink-0">
                            Loan Duration
                        </span>
                        <div>
                            <span class="text-xs font-medium text-foreground">
                                {selectedBank.loan_duration_months} months
                            </span>
                        </div>
                    </div>

                    <div class="flex items-center gap-2.5">
                        <span class="text-xs font-normal text-foreground min-w-14 xl:min-w-24 shrink-0">
                            Max Loan Amount
                        </span>
                        <div>
                            <span class="text-xs font-medium text-foreground">
                                {selectedBank.loan_max_amount} DZD
                            </span>
                        </div>
                    </div>
                </div>
            {/if}
        </div>
    </div>

    <!-- Loan Modal -->
    <div class="kt-modal" data-kt-modal="true" id="loan_modal">
        <div class="kt-modal-content max-w-[600px] top-[5%]">
            <div class="kt-modal-header">
                <h3 class="kt-modal-title">Apply for Loan</h3>
                <button
                    type="button"
                    class="kt-modal-close"
                    aria-label="Close modal"
                    data-kt-modal-dismiss="#loan_modal"
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
                {#if loanBank}
                    <div class="space-y-4">
                        <!-- Bank Info -->
                        <div class="flex items-center gap-4">
                            <div class="flex-shrink-0">
                                {#if loanBank.logo_url}
                                    <img 
                                        src={loanBank.logo_url} 
                                        alt={loanBank.name}
                                        class="w-16 h-16 rounded-lg object-cover"
                                    />
                                {:else}
                                    <div class="w-16 h-16 rounded-lg bg-accent/50 flex items-center justify-center">
                                        <i class="ki-filled ki-bank text-xl text-muted-foreground"></i>
                                    </div>
                                {/if}
                            </div>
                            <div class="flex-1">
                                <h4 class="font-semibold text-mono mb-1">{loanBank.name}</h4>
                                <div class="flex gap-2">
                                    <span class="text-xs bg-blue-100 text-blue-800 px-2 py-1 rounded">
                                        {(loanBank.loan_interest_rate * 100).toFixed(2)}% Interest
                                    </span>
                                    <span class="text-xs bg-green-100 text-green-800 px-2 py-1 rounded">
                                        {loanBank.loan_duration_months} months
                                    </span>
                                </div>
                            </div>
                        </div>

                        <!-- Loan Amount Input -->
                        <div class="kt-card">
                            <div class="kt-card-body p-4">
                                <h5 class="font-medium text-mono mb-3">Loan Amount</h5>
                                <div class="space-y-2">
                                    <label class="block text-sm font-medium text-mono mb-2" for="loan-amount-input">
                                        Amount (DZD) - Max: {loanBank.loan_max_amount}
                                    </label>
                                    <input 
                                        id="loan-amount-input"
                                        type="number" 
                                        class="kt-input w-full" 
                                        bind:value={loanAmount}
                                        min="1"
                                        max={loanBank.loan_max_amount}
                                        step="0.01"
                                        placeholder="Enter loan amount"
                                        on:input={handleLoanAmountChange}
                                    />
                                </div>
                            </div>
                        </div>

                        <!-- Warning Messages -->
                        <div class="kt-card bg-yellow-50 border-yellow-200">
                            <div class="kt-card-body p-4">
                                <div class="flex items-start gap-3">
                                    <i class="ki-filled ki-warning text-yellow-600 text-xl mt-1"></i>
                                    <div class="space-y-2">
                                        <h5 class="font-medium text-yellow-800">Important Loan Information</h5>
                                        <ul class="text-sm text-yellow-700 space-y-1 list-disc list-inside">
                                            <li>Monthly payments will be automatically deducted from your company funds</li>
                                            <li>If you don't have sufficient funds, the system will automatically take a loan from a random bank to cover the payment</li>
                                            <li><strong>Warning:</strong> This can lead to a dangerous loan spiral - borrowing to pay existing loans</li>
                                            <li><strong>Game End Warning:</strong> All unpaid loans will be deducted from your final company funds at game end</li>
                                        </ul>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Loan Calculation Summary -->
                        {#if loanData}
                            <div class="kt-card bg-accent/50">
                                <div class="kt-card-header px-5">
                                    <h3 class="kt-card-title">Loan Summary</h3>
                                </div>
                                <div class="kt-card-content px-5 py-4 space-y-2">
                                    <div class="flex justify-between items-center">
                                        <span class="text-sm font-normal text-secondary-foreground">
                                            Principal Amount
                                        </span>
                                        <span class="text-sm font-medium text-mono">
                                            {loanData.principal} DZD
                                        </span>
                                    </div>
                                    <div class="flex justify-between items-center">
                                        <span class="text-sm font-normal text-secondary-foreground">
                                            Interest Rate
                                        </span>
                                        <span class="text-sm font-medium text-mono">
                                            {loanData.interestRate}%
                                        </span>
                                    </div>
                                    <div class="flex justify-between items-center">
                                        <span class="text-sm font-normal text-secondary-foreground">
                                            Loan Duration
                                        </span>
                                        <span class="text-sm font-medium text-mono">
                                            {loanData.months} months
                                        </span>
                                    </div>
                                    <div class="flex justify-between items-center">
                                        <span class="text-sm font-normal text-secondary-foreground">
                                            Total Interest
                                        </span>
                                        <span class="text-sm font-medium text-mono">
                                            {loanData.totalInterest} DZD
                                        </span>
                                    </div>
                                    <div class="flex justify-between items-center">
                                        <span class="text-sm font-normal text-secondary-foreground">
                                            Total Repayment
                                        </span>
                                        <span class="text-sm font-medium text-mono">
                                            {loanData.totalRepayment} DZD
                                        </span>
                                    </div>
                                </div>
                                <div class="kt-card-footer flex justify-between items-center px-5 bg-primary/10">
                                    <span class="text-sm font-medium text-secondary-foreground">
                                        Monthly Payment
                                    </span>
                                    <span class="text-lg font-bold text-primary">
                                        {loanData.monthlyPayment} DZD
                                    </span>
                                </div>
                            </div>

                            <!-- Risk Warning -->
                            <div class="kt-card bg-red-50 border-red-200">
                                <div class="kt-card-body p-4">
                                    <div class="flex items-start gap-3">
                                        <i class="ki-filled ki-danger text-red-600 text-xl mt-1"></i>
                                        <div>
                                            <h5 class="font-medium text-red-800 mb-2">Loan Repayment Schedule</h5>
                                            <p class="text-sm text-red-700">
                                                You will be charged <strong>{loanData.monthlyPayment} DZD</strong> every month for <strong>{loanData.months} months</strong>.
                                                Please ensure you have consistent cash flow to avoid falling into debt spiral.
                                            </p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        {/if}
                    </div>
                {/if}
            </div>
            <div class="kt-modal-footer">
                <div></div>
                <div class="flex gap-4">
                    <button
                        class="kt-btn kt-btn-secondary"
                        data-kt-modal-dismiss="#loan_modal"
                        on:click={closeLoanModal}
                    >
                        Cancel
                    </button>
                    <button 
                        class="kt-btn kt-btn-primary"
                        on:click={makeLoan}
                        disabled={!loanData}
                    >
                        <i class="ki-filled ki-check text-base"></i>
                        Apply for Loan
                    </button>
                </div>
            </div>
        </div>
    </div>
</CompanyLayout> 