<script>
    import CompanyLayout from '../../Layouts/CompanyLayout.svelte';
    import Select2 from '../../Components/Forms/Select2.svelte';
    import { onMount, tick } from 'svelte';
    import { page } from '@inertiajs/svelte'

    // Define breadcrumbs for this page
    const breadcrumbs = [
        {
            title: 'Purchases',
            url: route('company.purchases.index'),
            active: false
        },
        {
            title: 'Make Purchase',
            url: route('company.purchases.purchase-page'),
            active: true
        }
    ];
    
    const pageTitle = 'Make Purchase';

    // Reactive variables
    let selectedProduct = null;
    let selectedProductId = '';
    let availableSuppliers = [];
    let loadingSuppliers = false;
    let quantity = 1;

    // Select2 component references
    let productSelectComponent;

    // Modal state
    let showPurchaseModal = false;
    let purchaseData = null;

    // Fetch suppliers for selected product
    async function fetchSuppliersForProduct(productId) {
        if (!productId) {
            availableSuppliers = [];
            return;
        }

        loadingSuppliers = true;
        try {
            const params = new URLSearchParams({
                product_id: productId,
                perPage: 50
            });
            
            const response = await fetch(route('company.suppliers.index') + '?' + params.toString(), {
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                }
            });
            
            const data = await response.json();
            availableSuppliers = data.suppliers;

        } catch (error) {
            console.error('Error fetching suppliers:', error);
        } finally {
            loadingSuppliers = false;
        }
    }

    // Handle product selection
    function handleProductSelect(event) {
        selectedProduct = event.detail.data;
        selectedProductId = event.detail.value;
        
        if (selectedProduct) {
            fetchSuppliersForProduct(selectedProduct.id);
        } else {
            availableSuppliers = [];
        }
    }

    // Handle product clear
    function handleProductClear() {
        selectedProduct = null;
        selectedProductId = '';
        availableSuppliers = [];
    }

    // Open purchase modal
    function openPurchaseModal(supplier) {
        if (!selectedProduct || !supplier) {
            showToast('Please select both a product and supplier', 'error');
            return;
        }

        calculatePurchaseData(supplier, quantity);
        showPurchaseModal = true;
        
        // Show modal
        const toggleButton = document.querySelector('[data-kt-modal-toggle="#purchase_modal"]');
        if (toggleButton) {
            toggleButton.click();
        }
    }

    function calculatePurchaseData(supplier, quantity) {
        const supplierProduct = supplier.selected_product;
        
        const subtotal = supplierProduct.real_sale_price * quantity;
        const shippingCost = supplier.real_shipping_cost * quantity;
        let customsDuties = 0;
        
        if (supplier.is_international && supplier.country && supplier.country.allows_imports) {
            customsDuties = (subtotal + shippingCost) * supplier.country.customs_duties_rate;
        }
        
        const totalCost = subtotal + shippingCost + customsDuties;
        const carbonFootprint = supplier.carbon_footprint;

        purchaseData = {
            product: selectedProduct,
            supplier: supplier,
            quantity: quantity,
            subtotal: subtotal,
            shippingCost: shippingCost,
            customsDuties: customsDuties,
            totalCost: totalCost,
            carbonFootprint: carbonFootprint,
            shippingTime: supplier.real_shipping_time_days || 0
        };
    }

    // Close purchase modal
    function closePurchaseModal() {
        // Simulate button click to use KT framework logic
        const dismissButton = document.querySelector('[data-kt-modal-dismiss="#purchase_modal"]');
        if (dismissButton) {
            dismissButton.click();
        }
        
        showPurchaseModal = false;
        purchaseData = null;
    }

    // Make purchase
    async function makePurchase() {
        if (!purchaseData) return;

        try {
            const response = await fetch(route('company.purchases.store', purchaseData.product.id), {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content'),
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: JSON.stringify({
                    supplier_id: purchaseData.supplier.id,
                    product_id: purchaseData.product.id,
                    quantity: purchaseData.quantity
                })
            });

            if (response.ok) {
                const data = await response.json();
                
                // Show success toast
                showToast(data.message || 'Purchase completed successfully!', 'success');
                
                // Close modal
                closePurchaseModal();
                
                // Reset form
                selectedProduct = null;
                selectedProductId = '';
                availableSuppliers = [];
                quantity = 1;
                
                if (productSelectComponent) {
                    productSelectComponent.clear();
                }
            } else {
                const errorData = await response.json();
                showToast(errorData.message || 'Error making purchase. Please try again.', 'destructive');
            }
        } catch (error) {
            console.error('Error making purchase:', error);
            showToast('Network error. Please check your connection and try again.', 'destructive');
        }
    }

    onMount(() => {
        // Initialize any required components
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
            <!-- Purchase Header -->
            <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4">
                <div class="flex flex-col gap-1">
                    <h1 class="text-2xl font-bold text-mono">Make Purchase</h1>
                    <p class="text-sm text-secondary-foreground">
                        Select a product and supplier to make a purchase
                    </p>
                </div>
                <div class="flex items-center gap-3">
                    <a href="{route('company.purchases.index')}" class="kt-btn kt-btn-outline">
                        <i class="ki-filled ki-arrow-left text-base"></i>
                        Back to Purchases
                    </a>
                </div>                      
            </div>

            <!-- Product Selector -->
            <div class="kt-card">
                <div class="kt-card-content">
                    <div class="flex">
                        <div class="flex-1">
                            <label class="text-sm font-medium text-mono mb-2 block">Select Product</label>
                            <div class="flex items-center gap-3">
                                {#if selectedProduct}
                                    <!-- Product Badge -->
                                    <div class="flex items-center gap-2">
                                        <span class="kt-badge kt-badge-outline kt-badge-primary">
                                            <i class="ki-filled ki-abstract-26 text-sm me-1"></i>
                                            Product: {selectedProduct.name}
                                        </span>
                                        <button 
                                            class="kt-btn kt-btn-sm kt-btn-icon kt-btn-ghost"
                                            on:click={handleProductClear}
                                            title="Clear product selection"
                                        >
                                            <i class="ki-filled ki-cross text-sm"></i>
                                        </button>
                                    </div>
                                {:else}
                                    <!-- Product Filter -->
                                    <div class="w-full">
                                        <Select2
                                            bind:this={productSelectComponent}
                                            id="product-select"
                                            placeholder="Choose a product..."
                                            bind:value={selectedProductId}
                                            on:select={handleProductSelect}
                                            on:clear={handleProductClear}
                                            disabled={loadingSuppliers}
                                            ajax={{
                                                url: route('company.products.index'),
                                                dataType: 'json',
                                                delay: 300,
                                                data: function(params) {
                                                    return {
                                                        search: params.term,
                                                        perPage: 100
                                                    };
                                                },
                                                processResults: function(data) {
                                                    return {
                                                        results: data.companyProducts.map(companyProduct => ({
                                                            id: companyProduct.product.id,
                                                            text: companyProduct.product.name,
                                                            name: companyProduct.product.name,
                                                            image_url: companyProduct.product.image_url,
                                                            type_name: companyProduct.product.type_name
                                                        }))
                                                    };
                                                },
                                                cache: true
                                            }}
                                            templateResult={function(data) {
                                                if (data.loading) return data.text;
                                                
                                                const $elem = globalThis.$('<div class="flex flex-col">' +
                                                    '<span class="font-medium">' + data.name + '</span>' +
                                                    '<span class="kt-badge kt-badge-outline kt-badge-primary kt-badge-sm w-fit mt-1">' + data.type_name + '</span>' +
                                                    '</div>');
                                                return $elem;
                                            }}
                                            templateSelection={function(data) {
                                                if (!data.id) return data.text;
                                                
                                                return data.name + ' (' + data.type_name + ')';
                                            }}
                                        />
                                    </div>
                                {/if}
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {#if selectedProduct}
                <!-- Suppliers Table -->
                <div class="kt-card">
                    <div class="kt-card-header">
                        <h4 class="kt-card-title">Available Suppliers</h4>
                    </div>
                    <div class="kt-card-content p-0">
                        {#if loadingSuppliers}
                            <!-- Loading skeleton -->
                            <div class="kt-scrollable-x-auto">
                                <table class="kt-table kt-table-border">
                                    <thead>
                                        <tr>
                                            <th>Supplier</th>
                                            <th>Location</th>
                                            <th>Price</th>
                                            <th>Shipping Cost</th>
                                            <th>Shipping Time</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        {#each Array(5) as _, i}
                                            <tr>
                                                <td>
                                                    <div class="flex items-center gap-3">
                                                        <div class="flex-shrink-0">
                                                            <div class="kt-skeleton w-10 h-10 rounded-lg"></div>
                                                        </div>
                                                        <div>
                                                            <div class="kt-skeleton h-4 w-24 mb-1"></div>
                                                            <div class="kt-skeleton h-3 w-20"></div>
                                                        </div>
                                                    </div>
                                                </td>
                                                <td>
                                                    <div class="kt-skeleton h-6 w-20 rounded"></div>
                                                </td>
                                                <td>
                                                    <div class="kt-skeleton h-4 w-16"></div>
                                                </td>
                                                <td>
                                                    <div class="kt-skeleton h-4 w-12"></div>
                                                </td>
                                                <td>
                                                    <div class="kt-skeleton h-8 w-20 rounded"></div>
                                                </td>
                                            </tr>
                                        {/each}
                                    </tbody>
                                </table>
                            </div>
                        {:else if availableSuppliers.length === 0}
                            <div class="flex flex-col items-center justify-center h-48 text-center p-8">
                                <i class="ki-filled ki-shop text-4xl text-muted-foreground mb-4"></i>
                                <h3 class="text-lg font-semibold text-mono mb-2">No suppliers found</h3>
                                <p class="text-sm text-secondary-foreground">
                                    No suppliers are selling this product
                                </p>
                            </div>
                        {:else}
                            <div class="kt-scrollable-x-auto">
                                <table class="kt-table kt-table-border">
                                    <thead>
                                        <tr>
                                            <th>Supplier</th>
                                            <th>Location</th>
                                            <th>Price</th>
                                            <th>Shipping Cost</th>
                                            <th>Shipping Time</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        {#each availableSuppliers as supplier}
                                            <tr>
                                                <td>
                                                    <div class="flex items-center gap-3">
                                                        <div class="flex-shrink-0">
                                                            {#if supplier.image_url}
                                                                <img 
                                                                    src={supplier.image_url} 
                                                                    alt={supplier.name}
                                                                    class="w-10 h-10 rounded-lg object-cover"
                                                                />
                                                            {:else}
                                                                <div class="w-10 h-10 rounded-lg bg-accent/50 flex items-center justify-center">
                                                                    <i class="ki-filled ki-shop text-lg text-muted-foreground"></i>
                                                                </div>
                                                            {/if}
                                                        </div>
                                                        <div>
                                                            <div class="font-medium text-mono">{supplier.name}</div>
                                                        </div>
                                                    </div>
                                                </td>
                                                <td>
                                                    <span class="text-sm">
                                                        {supplier.location_name}
                                                    </span>
                                                    {#if supplier.country}
                                                        <div class="flex items-center gap-1 text-xs text-muted-foreground">
                                                            {#if supplier.country.allows_imports}
                                                                <i class="ki-filled ki-check text-green-500"></i>
                                                                <span>Imports Allowed</span>
                                                            {:else}
                                                                <i class="ki-filled ki-cross text-destructive"></i>
                                                                <span>Imports Blocked</span>
                                                            {/if}
                                                        </div>
                                                    {/if}
                                                </td>
                                                <td>
                                                    <span class="text-sm font-medium">DZD {supplier.selected_product.real_sale_price}</span>
                                                </td>
                                                <td>
                                                    <span class="text-sm font-medium">DZD {supplier.real_shipping_cost}</span>
                                                </td>
                                                <td>
                                                    <span class="text-sm">{supplier.real_shipping_time_days || 0} days</span>
                                                </td>
                                                <td>
                                                    {#if !supplier.country || (supplier.country && supplier.country.allows_imports)}
                                                    <button 
                                                        class="kt-btn kt-btn-sm kt-btn-primary"
                                                        on:click={() => openPurchaseModal(supplier)}
                                                    >
                                                        <i class="ki-filled ki-handcart text-sm"></i>
                                                        Purchase
                                                    </button>                                                
                                                    {:else}
                                                        <button 
                                                            class="kt-btn kt-btn-sm kt-btn-secondary"
                                                            disabled
                                                        >
                                                            <i class="ki-filled ki-cross text-sm"></i>
                                                            Imports Blocked
                                                        </button>
                                                    {/if}
                                                </td>
                                            </tr>
                                        {/each}
                                    </tbody>
                                </table>
                            </div>
                        {/if}
                    </div>
                </div>
            {:else}
                <!-- No Product Selected -->
                <div class="kt-card">
                    <div class="kt-card-content">
                        <div class="flex flex-col items-center justify-center h-96 text-center">
                            <i class="ki-filled ki-abstract-26 text-4xl text-muted-foreground mb-4"></i>
                            <h3 class="text-lg font-semibold text-mono mb-2">Select a Product</h3>
                            <p class="text-sm text-secondary-foreground">
                                Choose a product from the dropdown above to view available suppliers and make a purchase.
                            </p>
                        </div>
                    </div>
                </div>
            {/if}
        </div>
    </div>
    <!-- End of Container -->

    <!-- Hidden button to trigger modal -->
    <button style="display:none" data-kt-modal-toggle="#purchase_modal"></button>

    <!-- Purchase Confirmation Modal -->
    <div class="kt-modal" data-kt-modal="true" id="purchase_modal">
        <div class="kt-modal-content max-w-[600px] top-[5%]">
            <div class="kt-modal-header">
                <h3 class="kt-modal-title">Confirm Purchase</h3>
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
                {#if purchaseData}
                    <div class="space-y-4">
                        <!-- Product and Supplier Info -->
                        <div class="flex items-center gap-4">
                            <div class="flex-shrink-0">
                                {#if purchaseData.product.image_url}
                                    <img 
                                        src={purchaseData.product.image_url} 
                                        alt={purchaseData.product.name}
                                        class="w-16 h-16 rounded-lg object-cover"
                                    />
                                {:else}
                                    <div class="w-16 h-16 rounded-lg bg-accent/50 flex items-center justify-center">
                                        <i class="ki-filled ki-abstract-26 text-xl text-muted-foreground"></i>
                                    </div>
                                {/if}
                            </div>
                            <div class="flex-1">
                                <h4 class="font-semibold text-mono mb-1">{purchaseData.product.name}</h4>
                                <p class="text-sm text-muted-foreground mb-1">Supplier: {purchaseData.supplier.name}</p>
                            </div>
                        </div>

                        <!-- Purchase Details -->
                        <div class="kt-card">
                            <div class="kt-card-body p-4">
                                <h5 class="font-medium text-mono mb-3">Purchase Details</h5>
                                <div class="space-y-2">
                                    <div class="flex justify-between">
                                        <span class="text-sm text-muted-foreground">Shipping Time:</span>
                                        <span class="font-medium">{purchaseData.shippingTime} days</span>
                                    </div>
                                    <div class="flex justify-between">
                                        <span class="text-sm text-muted-foreground">Carbon Footprint: ({purchaseData.supplier.carbon_footprint} kg CO2/purchase)</span>
                                        <span class="font-medium">{purchaseData.carbonFootprint} kg CO2</span>
                                    </div>
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
                                        on:input={() => calculatePurchaseData(purchaseData.supplier, quantity)}
                                    />
                                </div>
                            </div>
                        </div>

                        <!-- Price Breakdown -->
                        <div class="kt-card bg-accent/50">
                            <div class="kt-card-header px-5">
                                <h3 class="kt-card-title">
                                    Price Breakdown
                                </h3>
                            </div>
                            <div class="kt-card-content px-5 py-4 space-y-2">
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
                                            Customs Duties ({(purchaseData.supplier.country.customs_duties_rate * 100).toFixed(2)}%)
                                        </span>
                                        <span class="text-sm font-medium text-mono">
                                            DZD {purchaseData.customsDuties.toFixed(2)}
                                        </span>
                                    </div>
                                {/if}
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



                        <!-- Confirmation Message -->
                        <div class="text-sm text-muted-foreground">
                            Are you sure you want to make this purchase? This will deduct <strong>DZD {purchaseData.totalCost.toFixed(2)}</strong> from your funds and the order will be delivered in <strong>{purchaseData.shippingTime} days</strong>.
                        </div>
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
                    >
                        Confirm Purchase
                    </button>
                </div>
            </div>
        </div>
    </div>
</CompanyLayout> 