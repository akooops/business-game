<script>
    import CompanyLayout from '../../Layouts/CompanyLayout.svelte';
    import Pagination from '../../Components/Pagination.svelte';
    import { onMount, tick } from 'svelte';
    import { page } from '@inertiajs/svelte'

    // Define breadcrumbs for this page
    const breadcrumbs = [
        {
            title: 'Technologies',
            url: route('company.technologies.index'),
            active: false
        },
        {
            title: 'Research',
            url: route('company.technologies.research-page'),
            active: true
        }
    ];
    
    const pageTitle = 'Research Technologies';

    // Reactive variables
    let technologies = [];
    let pagination = {};
    let loading = true;
    let search = '';
    let perPage = 10;
    let currentPage = 1;
    let searchTimeout;

    // Drawer state
    let selectedTechnology = null;
    let showTechnologyDrawer = false;

    // Modal state
    let showResearchModal = false;
    let researchTechnology = null;

    // Fetch technologies data
    async function fetchTechnologies() {
        loading = true;
        try {
            const params = new URLSearchParams({
                page: currentPage,
                perPage: perPage,
                search: search
            });
            
            const response = await fetch(route('company.technologies.research-page') + '?' + params.toString(), {
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                }
            });
            
            const data = await response.json();
            technologies = data.technologies;
            pagination = data.pagination;
            
            // Wait for DOM to update, then initialize menus
            await tick();
            if (window.KTMenu) {
                window.KTMenu.init();
            }
        } catch (error) {
            console.error('Error fetching technologies:', error);
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
            fetchTechnologies();
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
            fetchTechnologies();
        }
    }

    // Handle per page change
    function handlePerPageChange(newPerPage) {
        perPage = newPerPage;
        currentPage = 1;
        fetchTechnologies();
    }

    // Open technology drawer
    function openTechnologyDrawer(technology) {
        selectedTechnology = technology;
        showTechnologyDrawer = true;
        
        // Show drawer
        const toggleButton = document.querySelector('[data-kt-drawer-toggle="#technology_drawer"]');
        if (toggleButton) {
            toggleButton.click();
        }
    }

    // Close technology drawer
    function closeTechnologyDrawer() {
        showTechnologyDrawer = false;
        selectedTechnology = null;
    }

    // Open research modal
    function openResearchModal(technology) {
        researchTechnology = technology;
        showResearchModal = true;
        
        // Show modal
        const toggleButton = document.querySelector('[data-kt-modal-toggle="#research_modal"]');
        if (toggleButton) {
            toggleButton.click();
        }
    }

    // Close research modal
    function closeResearchModal() {
        // Simulate button click to use KT framework logic
        const dismissButton = document.querySelector('[data-kt-modal-dismiss="#research_modal"]');
        if (dismissButton) {
            dismissButton.click();
        }
        
        showResearchModal = false;
        researchTechnology = null;
    }

    // Start research
    async function startResearch() {
        if (!researchTechnology) return;

        try {
            const response = await fetch(route('company.technologies.research', researchTechnology.id), {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content'),
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: JSON.stringify({
                    funds: researchTechnology.research_cost
                })
            });

            if (response.ok) {
                const data = await response.json();
                
                // Show success toast
                showToast(data.message || 'Research started successfully!', 'success');
                
                // Close modal
                closeResearchModal();
            } else {
                const errorData = await response.json();
                showToast(errorData.message || 'Error starting research. Please try again.', 'error');
            }
        } catch (error) {
            console.error('Error starting research:', error);
            showToast('Network error. Please check your connection and try again.', 'error');
        }
    }

    // Show toast notification
    function showToast(message, type = 'success') {
        if (window.KTToast) {
            KTToast.show({
                icon: `<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-info-icon lucide-info"><circle cx="12" cy="12" r="10"/><path d="M12 16v-4"/><path d="M12 8h.01"/></svg>`,
                message: message,
                variant: type === 'success' ? 'success' : 'destructive',
                position: 'bottom-right',
            });
        }
    }

    onMount(() => {
        fetchTechnologies();
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
    <div class="kt-container-fluid">
        <div class="grid gap-5 lg:gap-7.5">
            <!-- Technologies Header -->
            <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4">
                <div class="flex flex-col gap-1">
                    <h1 class="text-2xl font-bold text-mono">Research Technologies</h1>
                    <p class="text-sm text-secondary-foreground">
                        Discover and research new technologies to unlock products
                    </p>
                </div>
                <div class="flex items-center gap-3">
                    <a href="{route('company.technologies.index')}" class="kt-btn kt-btn-outline">
                        <i class="ki-filled ki-arrow-left text-base"></i>
                        Back to My Technologies
                    </a>
                </div>                      
            </div>

            <!-- Technologies Grid -->
            <div class="kt-card">
                <div class="kt-card-header">
                    <div class="kt-card-toolbar">
                        <div class="flex items-center gap-4">
                            <div class="kt-input max-w-64 w-64">
                                <i class="ki-filled ki-magnifier"></i>
                                <input 
                                    type="text" 
                                    class="kt-input" 
                                    placeholder="Search technologies..." 
                                    bind:value={search}
                                    on:input={handleSearchInput}
                                />
                            </div>
                        </div>
                    </div>
                </div>

                <div class="kt-card-content p-0">
                    {#if loading}
                        <!-- Loading skeleton -->
                        <div class="p-6">
                            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 p-4">
                                {#each Array(perPage) as _, i}
                                    <div class="kt-card animate-pulse">
                                        <div class="kt-card-body p-4">
                                            <!-- Technology Image Skeleton -->
                                            <div class="flex items-center justify-center mb-4">
                                                <div class="kt-skeleton h-[180px] w-full rounded-sm"></div>
                                            </div>

                                            <!-- Technology Info Skeleton -->
                                            <div class="mb-4">
                                                <div class="kt-skeleton h-6 w-3/4 mb-2"></div>
                                                <div class="kt-skeleton h-4 w-1/2 mb-2"></div>
                                                <div class="kt-skeleton h-3 w-full"></div>
                                            </div>

                                            <!-- Research Details Skeleton -->
                                            <div class="space-y-1">
                                                <div class="flex justify-between">
                                                    <div class="kt-skeleton h-3 w-20"></div>
                                                    <div class="kt-skeleton h-3 w-16"></div>
                                                </div>
                                                <div class="flex justify-between">
                                                    <div class="kt-skeleton h-3 w-24"></div>
                                                    <div class="kt-skeleton h-3 w-12"></div>
                                                </div>
                                            </div>

                                            <!-- Button Skeleton -->
                                            <div class="mt-4">
                                                <div class="kt-skeleton h-10 w-full rounded-lg"></div>
                                            </div>
                                        </div>
                                    </div>
                                {/each}
                            </div>
                        </div>
                    {:else if technologies.length === 0}
                        <!-- Empty state -->
                        <div class="p-10">
                            <div class="flex flex-col items-center justify-center text-center">
                                <div class="mb-4">
                                    <i class="ki-filled ki-technology-1 text-4xl text-muted-foreground"></i>
                                </div>
                                <h3 class="text-lg font-semibold text-mono mb-2">No technologies available</h3>
                                <p class="text-sm text-secondary-foreground mb-4">
                                    {search ? 'No technologies match your search criteria.' : 'No technologies are available for research at your current level.'}
                                </p>
                            </div>
                        </div>
                    {:else}
                        <!-- Technologies Grid -->
                        <div class="p-6">
                            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 p-4">
                                {#each technologies as technology}
                                    <div class="kt-card kt-card-hover">
                                        <div class="kt-card-body p-4">
                                            <!-- Technology Image -->
                                            <div class="flex items-center justify-center mb-4">
                                                {#if technology.image_url}
                                                    <img 
                                                        src={technology.image_url} 
                                                        alt={technology.name}
                                                        class="h-[180px] w-full object-cover rounded-sm cursor-pointer"
                                                        on:click={() => openTechnologyDrawer(technology)}
                                                    />
                                                {:else}
                                                    <div class="w-16 h-16 rounded-lg bg-accent/50 flex items-center justify-center cursor-pointer" on:click={() => openTechnologyDrawer(technology)}>
                                                        <i class="ki-filled ki-technology-1 text-2xl text-muted-foreground"></i>
                                                    </div>
                                                {/if}
                                            </div>

                                            <!-- Technology Info -->
                                            <div class="mb-4">
                                                <h3 class="text-lg font-semibold text-mono mb-1 cursor-pointer" on:click={() => openTechnologyDrawer(technology)}>
                                                    {technology.name}
                                                </h3>
                                                <p class="text-sm text-muted-foreground mb-2">
                                                    Level {technology.level}
                                                </p>
                                                {#if technology.description}
                                                    <p class="text-xs text-muted-foreground line-clamp-2">
                                                        {technology.description}
                                                    </p>
                                                {/if}
                                            </div>

                                            <!-- Research Details -->
                                            <div class="text-xs text-muted-foreground space-y-1 mb-4">
                                                <div class="flex justify-between">
                                                    <span>Research Cost:</span>
                                                    <span class="font-medium">DZD {technology.research_cost}</span>
                                                </div>
                                                <div class="flex justify-between">
                                                    <span>Research Time:</span>
                                                    <span class="font-medium">{technology.research_time_days} days</span>
                                                </div>
                                            </div>

                                            <!-- Research Button -->
                                            <button 
                                                class="kt-btn kt-btn-primary w-full"
                                                on:click={() => openResearchModal(technology)}
                                            >
                                                <i class="ki-filled ki-plus text-base"></i>
                                                Start Research
                                            </button>
                                        </div>
                                    </div>
                                {/each}
                            </div>
                        </div>

                        <!-- Pagination -->
                        {#if pagination && pagination.total > 0}
                            <div class="border-t border-gray-200">
                                <Pagination 
                                    {pagination} 
                                    {perPage}
                                    onPageChange={goToPage} 
                                    onPerPageChange={handlePerPageChange}
                                />
                            </div>
                        {/if}
                    {/if}
                </div>
            </div>
        </div>
    </div>
    <!-- End of Container -->

    <!-- Hidden button to trigger drawer -->
    <button style="display:none" data-kt-drawer-toggle="#technology_drawer"></button>

    <!-- Technology Details Drawer -->
    <div class="hidden kt-drawer kt-drawer-end card flex-col max-w-[90%] w-[450px] top-5 bottom-5 end-5 rounded-xl border border-border" data-kt-drawer="true" data-kt-drawer-container="body" id="technology_drawer">
        <div class="flex items-center justify-between gap-2.5 text-sm text-mono font-semibold px-5 py-2.5 border-b border-b-border">
            Technology Details
            <button class="kt-btn kt-btn-sm kt-btn-icon kt-btn-dim shrink-0" data-kt-drawer-dismiss="true" on:click={closeTechnologyDrawer}>
                <i class="ki-filled ki-cross"></i>
            </button>
        </div>
        
        <div class="kt-card-content flex flex-col space-y-3 p-5 kt-scrollable-y-auto">
            {#if selectedTechnology}
                <!-- Technology Image -->
                <div class="kt-card relative items-center justify-center bg-accent/50 mb-6.5 h-[180px] shadow-none">
                    {#if selectedTechnology.image_url}
                        <img 
                            src={selectedTechnology.image_url} 
                            alt={selectedTechnology.name}
                            class="h-[180px] w-full object-cover rounded-sm"
                        />
                    {:else}
                        <div class="flex items-center justify-center h-full">
                            <i class="ki-filled ki-technology-1 text-4xl text-muted-foreground"></i>
                        </div>
                    {/if}
                </div>

                <!-- Technology Name -->
                <span class="text-base font-medium text-mono">
                    {selectedTechnology.name}
                </span>

                <!-- Technology Description -->
                <span class="text-sm font-normal text-foreground block mb-7">
                    {#if selectedTechnology.description}
                        {selectedTechnology.description}
                    {:else}
                        No description available for this technology.
                    {/if}
                </span>

                <!-- Technology Details -->
                <div class="flex flex-col gap-2.5">
                    <div class="flex items-center gap-2.5">
                        <span class="text-xs font-normal text-foreground min-w-14 xl:min-w-24 shrink-0">
                            Level
                        </span>
                        <div>
                            <span class="kt-badge kt-badge-primary kt-badge-sm">
                                {selectedTechnology.level}
                            </span>
                        </div>
                    </div>
                    <div class="flex items-center gap-2.5">
                        <span class="text-xs font-normal text-foreground min-w-14 xl:min-w-24 shrink-0">
                            Research Cost
                        </span>
                        <div>
                            <span class="text-xs font-medium text-foreground">
                                DZD {selectedTechnology.research_cost}
                            </span>
                        </div>
                    </div>
                    <div class="flex items-center gap-2.5">
                        <span class="text-xs font-normal text-foreground min-w-14 xl:min-w-24 shrink-0">
                            Research Time
                        </span>
                        <div>
                            <span class="text-xs font-medium text-foreground">
                                {selectedTechnology.research_time_days} days
                            </span>
                        </div>
                    </div>
                </div>

                <!-- Products Section -->
                {#if selectedTechnology.products && selectedTechnology.products.length > 0}
                    <div class="border-t border-border pt-4">
                        <h3 class="text-sm font-semibold text-mono mb-3">Unlocked Products</h3>
                        <div class="space-y-3">
                            {#each selectedTechnology.products as product}
                                <div class="kt-card">
                                    <div class="kt-card-body p-3">
                                        <div class="flex items-center gap-3">
                                            <div class="flex-shrink-0">
                                                {#if product.image_url}
                                                    <img 
                                                        src={product.image_url} 
                                                        alt={product.name}
                                                        class="w-12 h-12 rounded-lg object-cover"
                                                    />
                                                {:else}
                                                    <div class="w-12 h-12 rounded-lg bg-accent/50 flex items-center justify-center">
                                                        <i class="ki-filled ki-abstract-26 text-lg text-muted-foreground"></i>
                                                    </div>
                                                {/if}
                                            </div>
                                            <div class="flex-1 min-w-0">
                                                <h4 class="text-sm font-semibold text-mono mb-1 truncate">{product.name}</h4>
                                                <p class="text-xs text-muted-foreground mb-1">{product.type_name}</p>
                                                {#if product.description}
                                                    <p class="text-xs text-muted-foreground line-clamp-2">{product.description}</p>
                                                {/if}
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            {/each}
                        </div>
                    </div>
                {:else}
                    <div class="border-t border-border pt-4">
                        <h3 class="text-sm font-semibold text-mono mb-3">Unlocked Products</h3>
                        <div class="kt-card">
                            <div class="kt-card-body p-4">
                                <div class="flex flex-col items-center justify-center text-center">
                                    <i class="ki-filled ki-abstract-26 text-2xl text-muted-foreground mb-2"></i>
                                    <p class="text-xs text-muted-foreground">No products unlocked yet</p>
                                </div>
                            </div>
                        </div>
                    </div>
                {/if}
            {/if}
        </div>
    </div>

    <!-- Hidden button to trigger modal -->
    <button style="display:none" data-kt-modal-toggle="#research_modal"></button>

    <!-- Research Confirmation Modal -->
    <div class="kt-modal" data-kt-modal="true" id="research_modal">
        <div class="kt-modal-content max-w-[500px] top-[5%]">
            <div class="kt-modal-header">
                <h3 class="kt-modal-title">Confirm Research</h3>
                <button
                    type="button"
                    class="kt-modal-close"
                    aria-label="Close modal"
                    data-kt-modal-dismiss="#research_modal"
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
                {#if researchTechnology}
                    <div class="space-y-4">
                        <!-- Technology Info -->
                        <div class="flex items-center gap-4">
                            <div class="flex-shrink-0">
                                {#if researchTechnology.image_url}
                                    <img 
                                        src={researchTechnology.image_url} 
                                        alt={researchTechnology.name}
                                        class="w-16 h-16 rounded-lg object-cover"
                                    />
                                {:else}
                                    <div class="w-16 h-16 rounded-lg bg-accent/50 flex items-center justify-center">
                                        <i class="ki-filled ki-technology-1 text-xl text-muted-foreground"></i>
                                    </div>
                                {/if}
                            </div>
                            <div class="flex-1">
                                <h4 class="font-semibold text-mono mb-1">{researchTechnology.name}</h4>
                                <p class="text-sm text-muted-foreground">Level {researchTechnology.level}</p>
                            </div>
                        </div>

                        <!-- Research Details -->
                        <div class="kt-card">
                            <div class="kt-card-body p-4">
                                <h5 class="font-medium text-mono mb-3">Research Details</h5>
                                <div class="space-y-2">
                                    <div class="flex justify-between">
                                        <span class="text-sm text-muted-foreground">Research Cost:</span>
                                        <span class="font-medium">DZD {researchTechnology.research_cost}</span>
                                    </div>
                                    <div class="flex justify-between">
                                        <span class="text-sm text-muted-foreground">Research Time:</span>
                                        <span class="font-medium">{researchTechnology.research_time_days} days</span>
                                    </div>
                                    <div class="flex justify-between">
                                        <span class="text-sm text-muted-foreground">Products Unlocked:</span>
                                        <span class="font-medium">{researchTechnology.products ? researchTechnology.products.length : 0}</span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Confirmation Message -->
                        <div class="text-sm text-muted-foreground">
                            Are you sure you want to start researching this technology? This will deduct <strong>DZD {researchTechnology.research_cost}</strong> from your funds and take <strong>{researchTechnology.research_time_days} days</strong> to complete.
                        </div>
                    </div>
                {/if}
            </div>
            <div class="kt-modal-footer">
                <div></div>
                <div class="flex gap-4">
                    <button
                        class="kt-btn kt-btn-secondary"
                        data-kt-modal-dismiss="#research_modal"
                        on:click={closeResearchModal}
                    >
                        Cancel
                    </button>
                    <button 
                        class="kt-btn kt-btn-primary"
                        on:click={startResearch}
                    >
                        Start Research
                    </button>
                </div>
            </div>
        </div>
    </div>
</CompanyLayout> 