<script>
    import AdminLayout from '../../Layouts/AdminLayout.svelte';

    // Props passed from controller
    export let bank;

    // Define breadcrumbs
    const breadcrumbs = [
        {
            title: 'Banks',
            url: route('admin.banks.index'),
            active: false
        },
        {
            title: bank.name || 'Bank Details',
            url: route('admin.banks.show', { bank: bank.id }),
            active: true
        }
    ];
    
    const pageTitle = 'Bank Details';
</script>

<svelte:head>
    <title>Business Game - {pageTitle}</title>
</svelte:head>

<AdminLayout {breadcrumbs} {pageTitle}>
    <!-- Container -->
    <div class="kt-container-fixed">
        <div class="grid gap-5 lg:gap-7.5">
            <!-- Header -->
            <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4">
                <div class="flex flex-col gap-1">
                    <h1 class="text-2xl font-bold text-mono">Bank Details</h1>
                    <p class="text-sm text-secondary-foreground">
                        View bank information and lending terms
                    </p>
                </div>
                <div class="flex items-center gap-3">
                    <a href="{route('admin.banks.index')}" class="kt-btn kt-btn-outline">
                        <i class="ki-filled ki-arrow-left text-base"></i>
                        Back to Banks
                    </a>
                    <a href="{route('admin.banks.edit', { bank: bank.id })}" class="kt-btn kt-btn-primary">
                        <i class="ki-filled ki-pencil text-base"></i>
                        Edit Bank
                    </a>
                </div>
            </div>

            <!-- Basic Information Card -->
            <div class="kt-card">
                <div class="kt-card-header">
                    <h4 class="kt-card-title">Basic Information</h4>
                </div>
                <div class="kt-card-content">
                    <div class="grid gap-4 w-full">
                        <!-- Bank Logo -->
                        <div class="flex">
                            <figure class="figure">
                                <img 
                                    src={bank?.logo_url} 
                                    alt={bank?.name}
                                    class="rounded-lg w-32 h-32 object-cover border"
                                />
                            </figure>
                        </div>

                        <!-- Bank Name -->
                        <div class="flex flex-col gap-2">
                            <h4 class="text-sm font-semibold text-mono">Bank Name</h4>
                            <p class="text-sm text-secondary-foreground">{bank.name}</p>
                        </div>

                        <!-- Created At -->
                        <div class="flex flex-col gap-2">
                            <h4 class="text-sm font-semibold text-mono">Created At</h4>
                            <p class="text-sm text-secondary-foreground">
                                {formatTimestamp(bank.created_at)}
                            </p>
                        </div>

                        <!-- Updated At -->
                        <div class="flex flex-col gap-2">
                            <h4 class="text-sm font-semibold text-mono">Updated At</h4>
                            <p class="text-sm text-secondary-foreground">
                                {formatTimestamp(bank.updated_at)}
                            </p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Loan Terms Card -->
            <div class="kt-card">
                <div class="kt-card-header">
                    <h4 class="kt-card-title">Loan Terms</h4>
                </div>
                <div class="kt-card-content">
                    <div class="grid gap-4 w-full">
                        <!-- Loan Duration -->
                        <div class="flex flex-col gap-2">
                            <h4 class="text-sm font-semibold text-mono">Loan Duration</h4>
                            <p class="text-sm text-secondary-foreground">
                                {bank.loan_duration_months} months
                                <span class="text-xs text-muted-foreground ml-2">
                                    (Standard loan term)
                                </span>
                            </p>
                        </div>

                        <!-- Interest Rate -->
                        <div class="flex flex-col gap-2">
                            <h4 class="text-sm font-semibold text-mono">Interest Rate</h4>
                            <p class="text-sm text-secondary-foreground font-bold">
                                {(bank.loan_interest_rate * 100).toFixed(2)}%
                                <span class="text-xs text-muted-foreground ml-2 font-normal">
                                    (From total borrowed amount)
                                </span>
                            </p>
                        </div>

                        <!-- Maximum Loan Amount -->
                        <div class="flex flex-col gap-2">
                            <h4 class="text-sm font-semibold text-mono">Maximum Loan Amount</h4>
                            <p class="text-sm text-secondary-foreground font-bold">
                                {bank.loan_max_amount} DZD
                                <span class="text-xs text-muted-foreground ml-2 font-normal">
                                    (Maximum borrowing limit)
                                </span>
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</AdminLayout>