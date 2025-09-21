<script>
    import CompanyLayout from '../../Layouts/CompanyLayout.svelte';
    import { onMount, onDestroy, tick } from 'svelte';
    import { page } from '@inertiajs/svelte'

    // Define breadcrumbs for this page
    const breadcrumbs = [
        {
            title: 'Marketing',
            url: route('company.advertisers.index'),
            active: false
        },
        {
            title: 'Advertisers',
            url: route('company.advertisers.index'),
            active: true
        }
    ];
    
    const pageTitle = 'Advertisers';

    // Reactive variables
    let advertisers = [];
    let loading = true;
    let fetchInterval = null;

    // Drawer state
    let selectedAdvertiser = null;
    let showAdvertiserDrawer = false;

    // Create ad modal state
    let showCreateAdModal = false;
    let createAdAdvertiser = null;
    let createAdProduct = null;
    let products = [];
    let selectedProductId = '';

    // Fetch advertisers data
    async function fetchAdvertisers() {
        if(advertisers.length == 0) loading = true;
        
        try {
            const response = await fetch(route('company.advertisers.index'), {
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                }
            });
            
            const data = await response.json();
            advertisers = data.advertisers;
            
            // Wait for DOM to update, then initialize menus
            await tick();
            if (window.KTMenu) {
                window.KTMenu.init();
            }
        } catch (error) {
            console.error('Error fetching advertisers:', error);
        } finally {
            loading = false;
        }
    }

    // Fetch products data
    async function fetchProducts() {
        try {
            const response = await fetch(route('company.products.index'), {
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                }
            });
            
            const data = await response.json();
            // Filter to only show products that the company has (companyProducts)
            products = data.companyProducts.map(companyProduct => ({
                id: companyProduct.product.id,
                name: companyProduct.product.name,
                type: companyProduct.product.type,
                type_name: companyProduct.product.type_name,
                available_stock: companyProduct.available_stock,
                sale_price: companyProduct.sale_price
            }));
        } catch (error) {
            console.error('Error fetching products:', error);
        }
    }

    // Open advertiser drawer
    function openAdvertiserDrawer(advertiser) {
        selectedAdvertiser = advertiser;
        showAdvertiserDrawer = true;
        
        // Show drawer
        const toggleButton = document.querySelector('[data-kt-drawer-toggle="#advertiser_drawer"]');
        if (toggleButton) {
            toggleButton.click();
        }
    }

    // Close advertiser drawer
    function closeAdvertiserDrawer() {
        showAdvertiserDrawer = false;
        selectedAdvertiser = null;
    }

    // Open create ad modal
    function openCreateAdModal(advertiser, event) {
        event.stopPropagation(); // Prevent opening the drawer
        createAdAdvertiser = advertiser;
        selectedProductId = '';
        showCreateAdModal = true;
        
        // Show modal
        const toggleButton = document.querySelector('[data-kt-modal-toggle="#create_ad_modal"]');
        if (toggleButton) {
            toggleButton.click();
        }
    }

    // Close create ad modal
    function closeCreateAdModal() {
        // Simulate button click to use KT framework logic
        const dismissButton = document.querySelector('[data-kt-modal-dismiss="#create_ad_modal"]');
        if (dismissButton) {
            dismissButton.click();
        }
        
        showCreateAdModal = false;
        createAdAdvertiser = null;
        selectedProductId = '';
    }

    // Create ad
    async function createAd() {
        if (!selectedProductId) return;

        try {
            const response = await fetch(route('company.ads.store'), {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content'),
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: JSON.stringify({
                    advertiser_id: createAdAdvertiser.id,
                    product_id: selectedProductId
                })
            });

            if (response.ok) {
                const data = await response.json();
                
                // Show success toast
                showToast(data.message || 'Ad package created successfully!', 'success');
                
                // Close modal
                closeCreateAdModal();
                
                // Refresh advertisers data
                fetchAdvertisers();
            } else {
                const errorData = await response.json();
                showToast(errorData.message || 'Error creating ad package. Please try again.', 'error');
            }
        } catch (error) {
            console.error('Error creating ad:', error);
            showToast('Network error. Please check your connection and try again.', 'error');
        }
    }
    
    onMount(() => {
        fetchAdvertisers();
        fetchProducts();
        fetchInterval = setInterval(fetchAdvertisers, 60000);
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
            <!-- Advertisers Header -->
            <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4">
                <div class="flex flex-col gap-1">
                    <h1 class="text-2xl font-bold text-mono">Advertisers</h1>
                    <p class="text-sm text-secondary-foreground">
                        Browse available marketing packages and create ad campaigns
                    </p>
                </div>                      
            </div>

            <!-- Advertisers Grid -->
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
                    {:else if advertisers.length === 0}
                        <!-- Empty state -->
                        <div class="p-10">
                            <div class="flex flex-col items-center justify-center text-center">
                                <div class="mb-4">
                                    <i class="ki-filled ki-megaphone text-4xl text-muted-foreground"></i>
                                </div>
                                <h3 class="text-lg font-semibold text-mono mb-2">No advertisers found</h3>
                                <p class="text-sm text-secondary-foreground mb-4">
                                    No marketing packages available in the market.
                                </p>
                            </div>
                        </div>
                    {:else}
                        <!-- Advertisers Grid -->
                        <div class="p-6">
                            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6 p-4">
                                {#each advertisers as advertiser}
                                    <div class="kt-card kt-card-hover cursor-pointer" role="button" tabindex="0" on:click={() => openAdvertiserDrawer(advertiser)} on:keydown={(e) => e.key === 'Enter' && openAdvertiserDrawer(advertiser)}>
                                        <div class="kt-card-content flex flex-col items-center lg:pt-10">
                                            <div class="mb-3">
                                                <div class="size-20 relative">
                                                    {#if advertiser.logo_url}
                                                        <img 
                                                            class="rounded-full w-20 h-20 object-cover" 
                                                            src={advertiser.logo_url}
                                                            alt={advertiser.name}
                                                        />
                                                    {:else}
                                                        <div class="w-20 h-20 rounded-full bg-accent/50 flex items-center justify-center">
                                                            <i class="ki-filled ki-megaphone text-2xl text-muted-foreground"></i>
                                                        </div>
                                                    {/if}
                                                </div>
                                            </div>
                                            <div class="flex items-center justify-center gap-1.5 mb-3">
                                                <span class="hover:text-primary text-center text-base leading-5 font-medium text-mono">
                                                    {advertiser.name}
                                                </span>
                                            </div>

                                            <div class="flex flex-col gap-1 text-xs text-secondary-foreground mb-4">
                                                <div class="flex justify-center gap-1">
                                                    <i class="ki-filled ki-dollar text-orange-500"></i>
                                                    <span>Price: {advertiser.real_price} DZD</span>
                                                </div>
                                                <div class="flex justify-center gap-1">
                                                    <i class="ki-filled ki-calendar text-green-500"></i>
                                                    <span>Duration: {advertiser.duration_days} days</span>
                                                </div>
                                                <div class="flex justify-center gap-1">
                                                    <i class="ki-filled ki-chart text-blue-500"></i>
                                                    <span>Impact: {(advertiser.min_market_impact_percentage * 100).toFixed(2)}% - {(advertiser.max_market_impact_percentage * 100).toFixed(2)}%</span>
                                                </div>
                                            </div>

                                            <!-- Create Ad Button -->
                                            <div class="mt-4">
                                                <button 
                                                    class="kt-btn kt-btn-sm kt-btn-primary"
                                                    on:click={(e) => openCreateAdModal(advertiser, e)}
                                                >
                                                    <i class="ki-filled ki-plus text-sm"></i>
                                                    Create Ad
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
    <button style="display:none" data-kt-drawer-toggle="#advertiser_drawer" aria-label="Toggle advertiser drawer"></button>

    <!-- Hidden button to trigger modal -->
    <button style="display:none" data-kt-modal-toggle="#create_ad_modal" aria-label="Toggle create ad modal"></button>

    <!-- Advertiser Details Drawer -->
    <div class="hidden kt-drawer kt-drawer-end card flex-col max-w-[90%] w-[450px] top-5 bottom-5 end-5 rounded-xl border border-border" data-kt-drawer="true" data-kt-drawer-container="body" id="advertiser_drawer">
        <div class="flex items-center justify-between gap-2.5 text-sm text-mono font-semibold px-5 py-2.5 border-b border-b-border">
            Advertiser Details
            <button class="kt-btn kt-btn-sm kt-btn-icon kt-btn-dim shrink-0" data-kt-drawer-dismiss="true" on:click={closeAdvertiserDrawer} aria-label="Close advertiser drawer">
                <i class="ki-filled ki-cross"></i>
            </button>
        </div>
        
        <div class="kt-card-content flex flex-col space-y-3 p-5 kt-scrollable-y-auto">
            {#if selectedAdvertiser}
                <!-- Advertiser Image -->
                <div class="kt-card relative items-center justify-center bg-accent/50 mb-6.5 h-[180px] shadow-none">
                    {#if selectedAdvertiser.logo_url}
                        <img 
                            src={selectedAdvertiser.logo_url} 
                            alt={selectedAdvertiser.name}
                            class="h-[180px] w-full object-cover rounded-sm"
                        />
                    {:else}
                        <div class="flex items-center justify-center h-full">
                            <i class="ki-filled ki-megaphone text-4xl text-muted-foreground"></i>
                        </div>
                    {/if}
                </div>

                <!-- Advertiser Name -->
                <span class="text-base font-medium text-mono">
                    {selectedAdvertiser.name}
                </span>

                <!-- Advertiser Details -->
                <div class="flex flex-col gap-2.5">
                    <div class="flex items-center gap-2.5">
                        <span class="text-xs font-normal text-foreground min-w-14 xl:min-w-24 shrink-0">
                            Price
                        </span>
                        <div>
                            <span class="text-xs font-medium text-foreground">
                                {selectedAdvertiser.real_price} DZD
                            </span>
                        </div>
                    </div>

                    <div class="flex items-center gap-2.5">
                        <span class="text-xs font-normal text-foreground min-w-14 xl:min-w-24 shrink-0">
                            Duration
                        </span>
                        <div>
                            <span class="text-xs font-medium text-foreground">
                                {selectedAdvertiser.duration_days} days
                            </span>
                        </div>
                    </div>

                    <div class="flex items-center gap-2.5">
                        <span class="text-xs font-normal text-foreground min-w-14 xl:min-w-24 shrink-0">
                            Market Impact
                        </span>
                        <div>
                            <span class="text-xs font-medium text-foreground">
                                {(selectedAdvertiser.min_market_impact_percentage * 100).toFixed(2)}% - {(selectedAdvertiser.max_market_impact_percentage * 100).toFixed(2)}%
                            </span>
                        </div>
                    </div>
                </div>
            {/if}
        </div>
    </div>

    <!-- Create Ad Modal -->
    <div class="kt-modal" data-kt-modal="true" id="create_ad_modal">
        <div class="kt-modal-content max-w-[600px] top-[5%]">
            <div class="kt-modal-header">
                <h3 class="kt-modal-title">Create Ad Campaign</h3>
                <button
                    type="button"
                    class="kt-modal-close"
                    aria-label="Close modal"
                    data-kt-modal-dismiss="#create_ad_modal"
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
                {#if createAdAdvertiser}
                    <div class="space-y-4">
                        <!-- Advertiser Info -->
                        <div class="flex items-center gap-4">
                            <div class="flex-shrink-0">
                                {#if createAdAdvertiser.logo_url}
                                    <img 
                                        src={createAdAdvertiser.logo_url} 
                                        alt={createAdAdvertiser.name}
                                        class="w-16 h-16 rounded-lg object-cover"
                                    />
                                {:else}
                                    <div class="w-16 h-16 rounded-lg bg-accent/50 flex items-center justify-center">
                                        <i class="ki-filled ki-megaphone text-xl text-muted-foreground"></i>
                                    </div>
                                {/if}
                            </div>
                            <div class="flex-1">
                                <h4 class="font-semibold text-mono mb-1">{createAdAdvertiser.name}</h4>
                                <div class="flex gap-2">
                                    <span class="text-xs bg-orange-100 text-orange-800 px-2 py-1 rounded">
                                        {createAdAdvertiser.real_price} DZD
                                    </span>
                                    <span class="text-xs bg-green-100 text-green-800 px-2 py-1 rounded">
                                        {createAdAdvertiser.duration_days} days
                                    </span>
                                </div>
                            </div>
                        </div>

                        <!-- Product Selection -->
                        <div class="kt-card">
                            <div class="kt-card-body p-4">
                                <h5 class="font-medium text-mono mb-3">Select Product to Advertise</h5>
                                <div class="space-y-2">
                                    <label class="block text-sm font-medium text-mono mb-2" for="product-select">
                                        Choose a product to advertise
                                    </label>
                                    <select 
                                        id="product-select"
                                        class="kt-input w-full" 
                                        bind:value={selectedProductId}
                                    >
                                        <option value="">Select a product...</option>
                                        {#each products as product}
                                            <option value={product.id}>
                                                {product.name} ({product.type_name}) - Stock: {product.available_stock}
                                            </option>
                                        {/each}
                                    </select>
                                </div>
                            </div>
                        </div>

                        <!-- Ad Package Info -->
                        <div class="kt-card bg-accent/50">
                            <div class="kt-card-header px-5">
                                <h3 class="kt-card-title">Ad Package Details</h3>
                            </div>
                            <div class="kt-card-content px-5 py-4 space-y-2">
                                <div class="flex justify-between items-center">
                                    <span class="text-sm font-normal text-secondary-foreground">
                                        Package Price
                                    </span>
                                    <span class="text-sm font-medium text-mono">
                                        {createAdAdvertiser.real_price} DZD
                                    </span>
                                </div>
                                <div class="flex justify-between items-center">
                                    <span class="text-sm font-normal text-secondary-foreground">
                                        Duration
                                    </span>
                                    <span class="text-sm font-medium text-mono">
                                        {createAdAdvertiser.duration_days} days
                                    </span>
                                </div>
                                <div class="flex justify-between items-center">
                                    <span class="text-sm font-normal text-secondary-foreground">
                                        Market Impact Range
                                    </span>
                                    <span class="text-sm font-medium text-mono">
                                        {(createAdAdvertiser.min_market_impact_percentage * 100).toFixed(2)}% - {(createAdAdvertiser.max_market_impact_percentage * 100).toFixed(2)}%
                                    </span>
                                </div>
                            </div>
                        </div>

                        <!-- Warning Messages -->
                        <div class="kt-card bg-yellow-50 border-yellow-200">
                            <div class="kt-card-body p-4">
                                <div class="flex items-start gap-3">
                                    <i class="ki-filled ki-warning text-yellow-600 text-xl mt-1"></i>
                                    <div class="space-y-2">
                                        <h5 class="font-medium text-yellow-800">Important Ad Information</h5>
                                        <ul class="text-sm text-yellow-700 space-y-1 list-disc list-inside">
                                            <li>The exact market impact percentage will be randomly calculated within the specified range</li>
                                            <li>Your ad will start immediately and run for the specified duration</li>
                                            <li>Multiple ads for the same product will have cumulative impact on demand</li>
                                            <li>Payment will be deducted from your company funds immediately</li>
                                        </ul>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                {/if}
            </div>
            <div class="kt-modal-footer">
                <div></div>
                <div class="flex gap-4">
                    <button
                        class="kt-btn kt-btn-secondary"
                        data-kt-modal-dismiss="#create_ad_modal"
                        on:click={closeCreateAdModal}
                    >
                        Cancel
                    </button>
                    <button 
                        class="kt-btn kt-btn-primary"
                        on:click={createAd}
                        disabled={!selectedProductId}
                    >
                        <i class="ki-filled ki-check text-base"></i>
                        Create Ad Campaign
                    </button>
                </div>
            </div>
        </div>
    </div>
</CompanyLayout>
