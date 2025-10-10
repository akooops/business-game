<script>
    import CompanyLayout from '../../Layouts/CompanyLayout.svelte';
    import { onMount, onDestroy, tick } from 'svelte';
    import { page } from '@inertiajs/svelte'

    // Define breadcrumbs for this page
    const breadcrumbs = [
        {
            title: 'Suppliers',
            url: route('company.suppliers.index'),
            active: false
        },
        {
            title: 'Index',
            url: route('company.suppliers.index'),
            active: true
        }
    ];
    
    const pageTitle = 'Suppliers';

    // Reactive variables
    let suppliers = [];
    let loading = true;
    let fetchInterval = null;

    // Drawer state
    let selectedSupplier = null;
    let showSupplierDrawer = false;

    // Purchase modal state
    let showPurchaseModal = false;
    let purchaseSupplier = null;
    let selectedProductId = '';
    let quantity = 1;
    let purchaseData = null;

    // Fetch suppliers data
    async function fetchSuppliers() {
        if(suppliers.length == 0) loading = true;
        
        try {

            const response = await fetch(route('company.suppliers.index'), {
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                }
            });
            
            const data = await response.json();
            suppliers = data.suppliers;
            
            // Wait for DOM to update, then initialize menus
            await tick();
            if (window.KTMenu) {
                window.KTMenu.init();
            }
        } catch (error) {
            console.error('Error fetching suppliers:', error);
        } finally {
            loading = false;
        }
    }

    // Open supplier drawer
    function openSupplierDrawer(supplier) {
        selectedSupplier = supplier;
        showSupplierDrawer = true;
        
        // Show drawer
        const toggleButton = document.querySelector('[data-kt-drawer-toggle="#supplier_drawer"]');
        if (toggleButton) {
            toggleButton.click();
        }
    }

    // Close supplier drawer
    function closeSupplierDrawer() {
        showSupplierDrawer = false;
        selectedSupplier = null;
    }

    // Open purchase modal
    function openPurchaseModal(supplier, event) {
        event.stopPropagation(); // Prevent opening the drawer
        purchaseSupplier = supplier;
        selectedProductId = '';
        quantity = 1;
        purchaseData = null;
        showPurchaseModal = true;
        
        // Show modal
        const toggleButton = document.querySelector('[data-kt-modal-toggle="#purchase_modal"]');
        if (toggleButton) {
            toggleButton.click();
        }
    }

    // Close purchase modal
    function closePurchaseModal() {
        // Simulate button click to use KT framework logic
        const dismissButton = document.querySelector('[data-kt-modal-dismiss="#purchase_modal"]');
        if (dismissButton) {
            dismissButton.click();
        }
        
        showPurchaseModal = false;
        purchaseSupplier = null;
        selectedProductId = '';
        quantity = 1;
        purchaseData = null;
    }

    // Calculate purchase data
    function calculatePurchaseData() {
        if (!purchaseSupplier || !selectedProductId) return;

        const supplierProduct = purchaseSupplier.supplier_products.find(sp => sp.product.id == selectedProductId);
        if (!supplierProduct) return;

        const subtotal = supplierProduct.real_sale_price * quantity;
        const shippingCost = purchaseSupplier.real_shipping_cost * quantity;
        let customsDuties = 0;
        
        // Calculate customs duties for international suppliers
        if (purchaseSupplier.is_international && purchaseSupplier.country && purchaseSupplier.country.allows_imports) {
            customsDuties = (subtotal + shippingCost) * purchaseSupplier.country.customs_duties_rate;
        }
        
        const totalCost = subtotal + shippingCost + customsDuties;
        const carbonFootprint = purchaseSupplier.carbon_footprint;

        purchaseData = {
            product: supplierProduct.product,
            supplier: purchaseSupplier,
            supplierProduct: supplierProduct,
            quantity: quantity,
            subtotal: subtotal,
            shippingCost: shippingCost,
            customsDuties: customsDuties,
            totalCost: totalCost,
            carbonFootprint: carbonFootprint,
            shippingTime: purchaseSupplier.real_shipping_time_days || 0
        };
    }

    // Handle product selection
    function handleProductSelect() {
        calculatePurchaseData();
    }

    // Handle quantity change
    function handleQuantityChange() {
        calculatePurchaseData();
    }

    // Make purchase
    async function makePurchase() {
        if (!purchaseData) return;

        try {
            const response = await fetch(route('company.purchases.store'), {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content'),
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: JSON.stringify({
                    supplier_id: purchaseSupplier.id,
                    product_id: selectedProductId,
                    quantity: quantity
                })
            });

            if (response.ok) {
                const data = await response.json();
                
                // Show success toast
                showToast(data.message || 'Purchase completed successfully!', 'success');
                
                // Close modal
                closePurchaseModal();
                
                // Refresh suppliers data
                fetchSuppliers();
            } else {
                const errorData = await response.json();
                showToast(errorData.message || 'Error making purchase. Please try again.', 'error');
            }
        } catch (error) {
            console.error('Error making purchase:', error);
            showToast('Network error. Please check your connection and try again.', 'error');
        }
    }
    
    onMount(() => {
        fetchSuppliers();
        fetchInterval = setInterval(fetchSuppliers, 60000);
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
            <!-- Suppliers Header -->
            <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4">
                <div class="flex flex-col gap-1">
                    <h1 class="text-2xl font-bold text-mono">Suppliers</h1>
                    <p class="text-sm text-secondary-foreground">
                        Browse available suppliers in the market
                    </p>
                </div>                      
            </div>

            <!-- Suppliers Grid -->
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
                    {:else if suppliers.length === 0}
                        <!-- Empty state -->
                        <div class="p-10">
                            <div class="flex flex-col items-center justify-center text-center">
                                <div class="mb-4">
                                    <i class="ki-filled ki-ship text-4xl text-muted-foreground"></i>
                                </div>
                                <h3 class="text-lg font-semibold text-mono mb-2">No suppliers found</h3>
                                <p class="text-sm text-secondary-foreground mb-4">
                                    No suppliers available in the market.
                                </p>
                            </div>
                        </div>
                    {:else}
                        <!-- Suppliers Grid -->
                        <div class="p-6">
                            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6 p-4">
                                {#each suppliers as supplier}
                                    <div class="kt-card kt-card-hover cursor-pointer" on:click={() => openSupplierDrawer(supplier)}>
                                        <div class="kt-card-content flex flex-col items-center lg:pt-10">
                                            <div class="mb-3">
                                                <div class="size-20 relative">
                                                    {#if supplier.image_url}
                                                        <img 
                                                            class="rounded-full w-20 h-20 object-cover" 
                                                            src={supplier.image_url}
                                                            alt={supplier.name}
                                                        />
                                                    {:else}
                                                        <div class="w-20 h-20 rounded-full bg-accent/50 flex items-center justify-center">
                                                            <i class="ki-filled ki-supplier-1 text-2xl text-muted-foreground"></i>
                                                        </div>
                                                    {/if}
                                                </div>
                                            </div>
                                            <div class="flex items-center justify-center gap-1.5 mb-3">
                                                <a class="hover:text-primary text-center text-base leading-5 font-medium text-mono" href="#">
                                                    {supplier.name}
                                                </a>
                                            </div>

                                            <div class="flex items-center gap-2.5">
                                                <span class="kt-badge kt-badge-{supplier.is_international ? 'warning' : 'info'} kt-badge-sm">
                                                    {supplier.is_international ? 'International' : 'Local'}
                                                </span>

                                                <span>
                                                    - 
                                                </span>

                                                <span class="text-secondary-foreground text-sm">
                                                    
                                                    <i class="ki-filled ki-map"></i>
                                                    {supplier.location_name}
                                                </span>
                                            </div>

                                            <span class="text-secondary-foreground text-sm mb-4">
                                                {supplier.supplier_products.length} products
                                            </span>

                                            {#if supplier.country}
                                                <div class="flex flex-col gap-1 text-xs text-secondary-foreground">
                                                    <div class="flex justify-center gap-1">
                                                        {#if supplier.country.allows_imports}
                                                            <i class="ki-filled ki-check text-green-500"></i>
                                                            <span>Imports Allowed</span>
                                                        {:else}
                                                            <i class="ki-filled ki-cross text-destructive"></i>
                                                            <span>Imports Blocked</span>
                                                        {/if}
                                                    </div>
                                                    <div class="flex justify-center gap-1">
                                                        <i class="ki-filled ki-dollar text-orange-500"></i>
                                                        <span>Custom Duties: {(supplier.country.customs_duties_rate * 100).toFixed(2)}%</span>
                                                    </div>
                                                </div>
                                            {/if}

                                            {#if supplier.country == null || (supplier.country && supplier.country.allows_imports)}
                                            <!-- Make Purchase Button -->
                                            <div class="mt-4">
                                                <button 
                                                    class="kt-btn kt-btn-sm kt-btn-primary"
                                                    on:click={(e) => openPurchaseModal(supplier, e)}
                                                >
                                                    <i class="ki-filled ki-handcart text-sm"></i>
                                                    Make Purchase
                                                </button>
                                            </div>
                                            {/if}
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
    <button style="display:none" data-kt-drawer-toggle="#supplier_drawer"></button>

    <!-- Hidden button to trigger modal -->
    <button style="display:none" data-kt-modal-toggle="#purchase_modal"></button>

    <!-- Supplier Details Drawer -->
    <div class="hidden kt-drawer kt-drawer-end card flex-col max-w-[90%] w-[450px] top-5 bottom-5 end-5 rounded-xl border border-border" data-kt-drawer="true" data-kt-drawer-container="body" id="supplier_drawer">
        <div class="flex items-center justify-between gap-2.5 text-sm text-mono font-semibold px-5 py-2.5 border-b border-b-border">
            Supplier Details
            <button class="kt-btn kt-btn-sm kt-btn-icon kt-btn-dim shrink-0" data-kt-drawer-dismiss="true" on:click={closeSupplierDrawer}>
                <i class="ki-filled ki-cross"></i>
            </button>
        </div>
        
        <div class="kt-card-content flex flex-col space-y-3 p-5 kt-scrollable-y-auto">
            {#if selectedSupplier}
                <!-- Supplier Image -->
                <div class="kt-card relative items-center justify-center bg-accent/50 mb-6.5 h-[180px] shadow-none">
                    {#if selectedSupplier.image_url}
                        <img 
                            src={selectedSupplier.image_url} 
                            alt={selectedSupplier.name}
                            class="h-[180px] w-full object-cover rounded-sm"
                        />
                    {:else}
                        <div class="flex items-center justify-center h-full">
                            <i class="ki-filled ki-shop text-4xl text-muted-foreground"></i>
                        </div>
                    {/if}
                </div>

                <!-- Supplier Name -->
                <span class="text-base font-medium text-mono">
                    {selectedSupplier.name}
                </span>

                <!-- Supplier Details -->
                <div class="flex flex-col gap-2.5">
                    <div class="flex items-center gap-2.5">
                        <span class="text-xs font-normal text-foreground min-w-14 xl:min-w-24 shrink-0">
                            Type
                        </span>
                        <div>
                            <span class="kt-badge kt-badge-{selectedSupplier.is_international ? 'warning' : 'info'} kt-badge-sm">
                                {selectedSupplier.is_international ? 'International' : 'Local'}
                            </span>
                        </div>
                    </div>
                    <div class="flex items-center gap-2.5">
                        <span class="text-xs font-normal text-foreground min-w-14 xl:min-w-24 shrink-0">
                            Location
                        </span>
                        <div>
                            <span class="text-xs font-medium text-foreground">
                                {selectedSupplier.location_name}
                            </span>
                        </div>
                    </div>

                    {#if selectedSupplier.country}
                        <div class="flex items-center gap-2.5">
                            <span class="text-xs font-normal text-foreground min-w-14 xl:min-w-24 shrink-0">
                                Allow Imports
                            </span>
                            <div>
                                {#if selectedSupplier.country.allows_imports}
                                    <span class="text-xs font-medium text-green-500">
                                        <i class="ki-filled ki-check text-sm"></i>
                                        Yes
                                    </span>
                                {:else}
                                    <span class="text-xs font-medium text-destructive">
                                        <i class="ki-filled ki-cross text-sm"></i>
                                        No
                                    </span>
                                {/if}
                            </div>
                        </div>
                        
                        <div class="flex items-center gap-2.5">
                            <span class="text-xs font-normal text-foreground min-w-14 xl:min-w-24 shrink-0">
                                Custom Duties
                            </span>
                            <div>
                                <span class="text-xs font-medium text-foreground">
                                    {(selectedSupplier.country.customs_duties_rate * 100).toFixed(2)}%
                                </span>
                            </div>
                        </div>
                    {/if}

                    <div class="flex items-center gap-2.5">
                        <span class="text-xs font-normal text-foreground min-w-14 xl:min-w-24 shrink-0">
                            Current Shipping Cost
                        </span>
                        <div>
                            <span class="text-xs font-medium text-foreground">
                                DZD {selectedSupplier.real_shipping_cost} / unit
                            </span>
                        </div>
                    </div>

                    <div class="flex items-center gap-2.5">
                        <span class="text-xs font-normal text-foreground min-w-14 xl:min-w-24 shrink-0">
                            Current Shipping Time
                        </span>
                        <div>
                            <span class="text-xs font-medium text-foreground">
                                {selectedSupplier.real_shipping_time_days} days
                            </span>
                        </div>
                    </div>

                    <div class="flex items-center gap-2.5">
                        <span class="text-xs font-normal text-foreground min-w-14 xl:min-w-24 shrink-0">
                            Carbon Footprint
                        </span>
                        <div>
                            <span class="text-xs font-medium text-foreground">
                                {selectedSupplier.carbon_footprint} kg CO2e / purchase
                            </span>
                        </div>
                    </div>
                </div>

                <!-- Products Section -->
                {#if selectedSupplier.supplier_products && selectedSupplier.supplier_products.length > 0}
                    <div class="border-t border-border pt-4">
                        <h3 class="text-sm font-semibold text-mono mb-3">Available Products</h3>
                        <div class="space-y-3">
                            {#each selectedSupplier.supplier_products as supplierProduct}
                                {#if supplierProduct.product.is_researched}
                                    <div class="kt-card">
                                        <div class="kt-card-body p-3">
                                            <div class="flex items-center gap-3">
                                                <div class="flex-shrink-0 relative">
                                                    {#if supplierProduct.product.image_url}
                                                        <img 
                                                            src={supplierProduct.product.image_url} 
                                                            alt={supplierProduct.product.name}
                                                            class="w-12 h-12 rounded-lg object-cover"
                                                        />
                                                    {:else}
                                                        <div class="w-12 h-12 rounded-lg bg-accent/50 flex items-center justify-center">
                                                            <i class="ki-filled ki-abstract-26 text-lg text-muted-foreground"></i>
                                                        </div>
                                                    {/if}
                                                </div>
                                                <div class="flex-1 min-w-0">
                                                    <h4 class="text-sm font-semibold text-mono mb-1 truncate">{supplierProduct.product.name}</h4>
                                                    <p class="text-xs text-muted-foreground mb-1">{supplierProduct.product.type_name}</p>
                                                    <span class="text-xs text-green-500 font-medium">{supplierProduct.real_sale_price} DZD/unit</span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                {:else}
                                    <div class="kt-card">
                                        <div class="kt-card-body p-3">
                                            <div class="flex items-center gap-3">
                                                <div class="flex-shrink-0 relative">
                                                    <div class="w-12 h-12 rounded-lg bg-muted animate-pulse"></div>
                                                    <div class="absolute inset-0 flex items-center justify-center" style="top: 50%; left: 50%; bottom: 50%; right: 50%;">
                                                        <i class="ki-filled ki-lock text-muted-foreground text-sm"></i>
                                                    </div>
                                                </div>
                                                <div class="flex-1 min-w-0">
                                                    <div class="kt-skeleton h-4 w-24 mb-1"></div>
                                                    <div class="kt-skeleton h-3 w-16 mb-1"></div>
                                                    <div class="flex items-center gap-1">
                                                        <i class="ki-filled ki-search text-orange-500 text-xs"></i>
                                                        <span class="text-xs text-orange-500 font-medium">Need Research to Unlock</span>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                {/if}
                            {/each}
                        </div>
                    </div>
                {:else}
                    <div class="border-t border-border pt-4">
                        <h3 class="text-sm font-semibold text-mono mb-3">Available Products</h3>
                        <div class="kt-card">
                            <div class="kt-card-body p-4">
                                <div class="flex flex-col items-center justify-center text-center">
                                    <i class="ki-filled ki-abstract-26 text-2xl text-muted-foreground mb-2"></i>
                                    <p class="text-xs text-muted-foreground">No products available</p>
                                </div>
                            </div>
                        </div>
                    </div>
                {/if}
            {/if}
        </div>
    </div>

    <!-- Purchase Modal -->
    <div class="kt-modal" data-kt-modal="true" id="purchase_modal">
        <div class="kt-modal-content max-w-[600px] top-[5%]">
            <div class="kt-modal-header">
                <h3 class="kt-modal-title">Make Purchase</h3>
                <button
                    type="button"
                    class="kt-modal-close"
                    aria-label="Close modal"
                    data-kt-modal-dismiss="#purchase_modal"
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
                {#if purchaseSupplier}
                    <div class="space-y-4">
                        <!-- Supplier Info -->
                        <div class="flex items-center gap-4">
                            <div class="flex-shrink-0">
                                {#if purchaseSupplier.image_url}
                                    <img 
                                        src={purchaseSupplier.image_url} 
                                        alt={purchaseSupplier.name}
                                        class="w-16 h-16 rounded-lg object-cover"
                                    />
                                {:else}
                                    <div class="w-16 h-16 rounded-lg bg-accent/50 flex items-center justify-center">
                                        <i class="ki-filled ki-shop text-xl text-muted-foreground"></i>
                                    </div>
                                {/if}
                            </div>
                            <div class="flex-1">
                                <h4 class="font-semibold text-mono mb-1">{purchaseSupplier.name}</h4>
                                <p class="text-sm text-muted-foreground mb-1">{purchaseSupplier.location_name}</p>
                                <span class="kt-badge kt-badge-{purchaseSupplier.is_international ? 'warning' : 'info'} kt-badge-sm">
                                    {purchaseSupplier.is_international ? 'International' : 'Local'}
                                </span>
                            </div>
                        </div>

                        <!-- Product Selection -->
                        <div class="kt-card">
                            <div class="kt-card-body p-4">
                                <h5 class="font-medium text-mono mb-3">Select Product</h5>
                                <div class="space-y-3">
                                    <label class="block text-sm font-medium text-mono mb-2">Product</label>
                                    <select 
                                        class="kt-input w-full" 
                                        bind:value={selectedProductId}
                                        on:change={handleProductSelect}
                                    >
                                        <option value="">Choose a product...</option>
                                        {#each purchaseSupplier.supplier_products as supplierProduct}
                                            {#if supplierProduct.product.is_researched}
                                                <option value={supplierProduct.product.id}>
                                                    {supplierProduct.product.name} ({supplierProduct.product.type_name}) - {supplierProduct.real_sale_price} DZD/unit
                                                </option>
                                            {/if}
                                        {/each}
                                    </select>
                                </div>
                            </div>
                        </div>

                        <!-- Quantity Input -->
                        <div class="kt-card">
                            <div class="kt-card-body p-4">
                                <h5 class="font-medium text-mono mb-3">Purchase Quantity</h5>
                                <div class="space-y-2">
                                    <label class="block text-sm font-medium text-mono mb-2">Quantity</label>
                                    <input 
                                        type="number" 
                                        class="kt-input w-full" 
                                        bind:value={quantity}
                                        min="0.001"
                                        step="0.001"
                                        on:input={handleQuantityChange}
                                    />
                                </div>
                            </div>
                        </div>

                        <!-- Purchase Summary -->
                        {#if purchaseData}
                            <div class="kt-card bg-accent/50">
                                <div class="kt-card-header px-5">
                                    <h3 class="kt-card-title">Purchase Summary</h3>
                                </div>
                                <div class="kt-card-content px-5 py-4 space-y-2">
                                    <div class="flex justify-between items-center">
                                        <span class="text-sm font-normal text-secondary-foreground">
                                            Product
                                        </span>
                                        <span class="text-sm font-medium text-mono">
                                            {purchaseData.product.name}
                                        </span>
                                    </div>
                                    <div class="flex justify-between items-center">
                                        <span class="text-sm font-normal text-secondary-foreground">
                                            Quantity
                                        </span>
                                        <span class="text-sm font-medium text-mono">
                                            {purchaseData.quantity}
                                        </span>
                                    </div>
                                    <div class="flex justify-between items-center">
                                        <span class="text-sm font-normal text-secondary-foreground">
                                            Subtotal
                                        </span>
                                        <span class="text-sm font-medium text-mono">
                                            DZD {purchaseData.subtotal.toFixed(3)}
                                        </span>
                                    </div>
                                    <div class="flex justify-between items-center">
                                        <span class="text-sm font-normal text-secondary-foreground">
                                            Shipping
                                        </span>
                                        <span class="text-sm font-medium text-mono">
                                            DZD {purchaseData.shippingCost.toFixed(3)}
                                        </span>
                                    </div>
                                    {#if purchaseData.customsDuties > 0}
                                        <div class="flex justify-between items-center">
                                            <span class="text-sm font-normal text-secondary-foreground">
                                                Customs Duties
                                            </span>
                                            <span class="text-sm font-medium text-mono">
                                                DZD {purchaseData.customsDuties.toFixed(2)}
                                            </span>
                                        </div>
                                    {/if}
                                    <div class="flex justify-between items-center">
                                        <span class="text-sm font-normal text-secondary-foreground">
                                            Shipping Time
                                        </span>
                                        <span class="text-sm font-medium text-mono">
                                            {purchaseData.shippingTime} days
                                        </span>
                                    </div>
                                    <div class="flex justify-between items-center">
                                        <span class="text-sm font-normal text-secondary-foreground">
                                            Carbon Footprint
                                        </span>
                                        <span class="text-sm font-medium text-mono">
                                            {purchaseData.carbonFootprint} kg CO2
                                        </span>
                                    </div>
                                </div>
                                <div class="kt-card-footer flex justify-between items-center px-5">
                                    <span class="text-sm font-normal text-secondary-foreground">
                                        Total
                                    </span>
                                    <span class="text-base font-semibold text-mono">
                                        DZD {purchaseData.totalCost.toFixed(2)}
                                    </span>
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
                        data-kt-modal-dismiss="#purchase_modal"
                        on:click={closePurchaseModal}
                    >
                        Cancel
                    </button>
                    <button 
                        class="kt-btn kt-btn-primary"
                        on:click={makePurchase}
                        disabled={!purchaseData}
                    >
                        Confirm Purchase
                    </button>
                </div>
            </div>
        </div>
    </div>
</CompanyLayout> 