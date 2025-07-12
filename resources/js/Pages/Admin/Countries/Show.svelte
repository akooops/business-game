<script>
    import AdminLayout from '../../Layouts/AdminLayout.svelte';
    import { page } from '@inertiajs/svelte';

    // Props
    export let country;

    // Define breadcrumbs for this country
    const breadcrumbs = [
        {
            title: 'Countries',
            url: route('admin.countries.index'),
            active: false
        },
        {
            title: country.name || 'Country Details',
            url: route('admin.countries.show', { country: country.id }),
            active: true
        }
    ];
    
    const pageTitle = 'Country Details';

    // Import cost calculator state
    let calculatorAmount = 10000;
    let calculatedCosts = {};

    // Calculate import costs based on country rates
    function calculateImportCosts(amount) {
        const baseAmount = parseFloat(amount) || 0;
        
        // Base CIF components
        const freight = parseFloat(country.freight_cost) || 0;
        const insurance = baseAmount * (parseFloat(country.insurance_rate) || 0);
        const portHandling = parseFloat(country.port_handling_fee) || 0;
        
        const cif = baseAmount + freight + insurance + portHandling;
        
        // Taxes
        const customsDuties = cif * (parseFloat(country.customs_duties_rate) || 0);
        const tva = (cif + customsDuties) * (parseFloat(country.tva_rate) || 0);
        
        const totalCost = cif + customsDuties + tva;
        
        return {
            baseAmount,
            freight,
            insurance,
            portHandling,
            cif,
            customsDuties,
            tva,
            totalCost,
            totalTaxes: customsDuties + tva,
            taxPercentage: baseAmount > 0 ? ((totalCost - baseAmount) / baseAmount * 100) : 0
        };
    }

    // Reactive calculation
    $: calculatedCosts = calculateImportCosts(calculatorAmount);

    // Format currency
    function formatCurrency(amount) {
        return new Intl.NumberFormat('en-US', {
            style: 'currency',
            currency: 'DZD',
            minimumFractionDigits: 2
        }).format(amount);
    }

    // Format percentage
    function formatPercentage(value) {
        return `${(value * 100).toFixed(2)}%`;
    }
</script>

<svelte:head>
    <title>Business Game - {pageTitle}</title>
</svelte:head>

<AdminLayout {breadcrumbs} {pageTitle}>
    <div class="kt-container-fixed">
        <div class="grid gap-5 lg:gap-7.5">
            <!-- Country Header -->
            <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4">
                <div class="flex flex-col gap-1">
                    <h1 class="text-2xl font-bold text-mono">Country Details</h1>
                    <p class="text-sm text-secondary-foreground">
                        View country information and import regulations
                    </p>
                </div>
                <div class="flex items-center gap-3">
                    <a href="{route('admin.countries.index')}" class="kt-btn kt-btn-outline">
                        <i class="ki-filled ki-arrow-left text-base"></i>
                        Back to Countries
                    </a>
                    {#if hasPermission('admin.countries.update')}
                    <a href="{route('admin.countries.edit', { country: country.id })}" class="kt-btn kt-btn-primary">
                        <i class="ki-filled ki-pencil text-base"></i>
                        Edit Country
                    </a>
                    {/if}
                </div>
            </div>

            <!-- Country Information Card -->
            <div class="kt-card">
                <div class="kt-card-header">
                    <h4 class="kt-card-title">Basic Information</h4>
                </div>
                <div class="kt-card-content">
                     <!-- Country Details -->
                     <div class="grid gap-4 w-full">
                        <!-- Country Flag -->
                        <div class="flex">
                            <figure class="figure">
                                <img 
                                    src={country?.flag_url} 
                                    alt={country?.name}
                                    class="rounded-lg w-32 h-32 object-cover border"
                                />
                            </figure>
                        </div>

                        <!-- Country Name -->
                        <div class="flex flex-col gap-2">
                            <h4 class="text-sm font-semibold text-mono">Country Name</h4>
                            <p class="text-sm text-secondary-foreground">{country?.name}</p>
                        </div>

                        <!-- Country Code -->
                        <div class="flex flex-col gap-2">
                            <h4 class="text-sm font-semibold text-mono">Country Code</h4>
                            <p class="text-sm text-secondary-foreground">{country?.code}</p>
                        </div>
                        
                        <!-- Import Status -->
                        <div class="flex flex-col gap-2">
                            <h4 class="text-sm font-semibold text-mono">Import Status</h4>
                            <div>
                                {#if country?.allows_imports}
                                    <span class="kt-badge kt-badge-success kt-badge-sm">
                                        <i class="ki-filled ki-check text-xs"></i>
                                        Imports Allowed
                                    </span>
                                {:else}
                                    <span class="kt-badge kt-badge-destructive kt-badge-sm">
                                        <i class="ki-filled ki-cross text-xs"></i>
                                        Imports Restricted
                                    </span>
                                {/if}
                            </div>
                        </div>

                        <!-- Created At -->
                        <div class="flex flex-col gap-2">
                            <h4 class="text-sm font-semibold text-mono">Created At</h4>
                            <p class="text-sm text-secondary-foreground">
                                {formatTimestamp(country?.created_at)}
                            </p>
                        </div>

                        <!-- Updated At -->
                        <div class="flex flex-col gap-2">
                            <h4 class="text-sm font-semibold text-mono">Updated At</h4>
                            <p class="text-sm text-secondary-foreground">
                                {formatTimestamp(country?.updated_at)}
                            </p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Import Regulations Card -->
            <div class="kt-card">
                <div class="kt-card-header">
                    <h4 class="kt-card-title">Import Regulations</h4>
                </div>
                <div class="kt-card-content">
                    <div class="grid gap-4 w-full">
                        <!-- Customs Duties Rate -->
                        <div class="flex flex-col gap-2">
                            <h4 class="text-sm font-semibold text-mono">Customs Duties Rate</h4>
                            <p class="text-sm text-secondary-foreground">
                                {formatPercentage(country?.customs_duties_rate)}
                                <span class="text-xs text-muted-foreground ml-2">
                                    (Applied on CIF value)
                                </span>
                            </p>
                        </div>

                        <!-- TVA Rate -->
                        <div class="flex flex-col gap-2">
                            <h4 class="text-sm font-semibold text-mono">TVA Rate</h4>
                            <p class="text-sm text-secondary-foreground">
                                {formatPercentage(country?.tva_rate)}
                                <span class="text-xs text-muted-foreground ml-2">
                                    (Applied on CIF + Customs Duties)
                                </span>
                            </p>
                        </div>

                        <!-- Insurance Rate -->
                        <div class="flex flex-col gap-2">
                            <h4 class="text-sm font-semibold text-mono">Insurance Rate</h4>
                            <p class="text-sm text-secondary-foreground">
                                {formatPercentage(country?.insurance_rate)}
                                <span class="text-xs text-muted-foreground ml-2">
                                    (Applied on goods value)
                                </span>
                            </p>
                        </div>

                        <!-- Freight Cost -->
                        <div class="flex flex-col gap-2">
                            <h4 class="text-sm font-semibold text-mono">Freight Cost</h4>
                            <p class="text-sm text-secondary-foreground">
                                {formatCurrency(country?.freight_cost)}
                                <span class="text-xs text-muted-foreground ml-2">
                                    (Fixed shipping cost)
                                </span>
                            </p>
                        </div>

                        <!-- Port Handling Fee -->
                        <div class="flex flex-col gap-2">
                            <h4 class="text-sm font-semibold text-mono">Port Handling Fee</h4>
                            <p class="text-sm text-secondary-foreground">
                                {formatCurrency(country?.port_handling_fee)}
                                <span class="text-xs text-muted-foreground ml-2">
                                    (Port processing fee)
                                </span>
                            </p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Import Cost Calculator Card -->
            <div class="kt-card">
                <div class="kt-card-header">
                    <h4 class="kt-card-title">Import Cost Calculator</h4>
                    <p class="text-sm text-secondary-foreground">
                        Calculate total import costs for goods from {country?.name}
                    </p>
                </div>
                <div class="kt-card-content">
                    <div class="grid gap-6 w-full">
                        <!-- Calculator Input -->
                        <div class="flex flex-col gap-2">
                            <label class="text-sm font-semibold text-mono">Goods Value (DZD)</label>
                            <input
                                type="number"
                                bind:value={calculatorAmount}
                                min="0"
                                step="0.01"
                                class="kt-input"
                                placeholder="Enter goods value..."
                            />
                        </div>

                        <!-- Calculation Results -->
                        <div class="space-y-5">
                            <!-- begin: Card -->
                            <div class="kt-card bg-accent/50">
                                <div class="kt-card-header px-5">
                                    <h3 class="kt-card-title">
                                        Import Cost Summary
                                    </h3>
                                </div>
                                <div class="kt-card-content px-5 py-4 space-y-2">
                                    <h4 class="text-sm font-medium text-mono mb-3.5">
                                        Cost Breakdown
                                    </h4>
                                    <div class="flex justify-between items-center">
                                        <span class="text-sm font-normal text-secondary-foreground">
                                            Goods Value
                                        </span>
                                        <span class="text-sm font-medium text-mono">
                                            {formatCurrency(calculatedCosts.baseAmount)}
                                        </span>
                                    </div>
                                    <div class="flex justify-between items-center">
                                        <span class="text-sm font-normal text-secondary-foreground">
                                            Freight
                                        </span>
                                        <span class="text-sm font-medium text-mono">
                                            {formatCurrency(calculatedCosts.freight)}
                                        </span>
                                    </div>
                                    <div class="flex justify-between items-center">
                                        <span class="text-sm font-normal text-secondary-foreground">
                                            Insurance
                                        </span>
                                        <span class="text-sm font-medium text-mono">
                                            {formatCurrency(calculatedCosts.insurance)}
                                        </span>
                                    </div>
                                    <div class="flex justify-between items-center">
                                        <span class="text-sm font-normal text-secondary-foreground">
                                            Port Handling
                                        </span>
                                        <span class="text-sm font-medium text-mono">
                                            {formatCurrency(calculatedCosts.portHandling)}
                                        </span>
                                    </div>
                                    <div class="flex justify-between items-center">
                                        <span class="text-sm font-normal text-secondary-foreground">
                                            Customs Duties
                                        </span>
                                        <span class="text-sm font-medium text-mono">
                                            {formatCurrency(calculatedCosts.customsDuties)}
                                        </span>
                                    </div>
                                    <div class="flex justify-between items-center">
                                        <span class="text-sm font-normal text-secondary-foreground">
                                            TVA
                                        </span>
                                        <span class="text-sm font-medium text-mono">
                                            {formatCurrency(calculatedCosts.tva)}
                                        </span>
                                    </div>
                                </div>
                                <div class="kt-card-footer flex justify-between items-center px-5">
                                    <span class="text-sm font-normal text-secondary-foreground">
                                        Total Import Cost
                                    </span>
                                    <span class="text-base font-semibold text-mono">
                                        {formatCurrency(calculatedCosts.totalCost)}
                                    </span>
                                </div>
                            </div>
                            <!-- end: Card -->
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</AdminLayout> 