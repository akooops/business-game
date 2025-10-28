<script>
    import CompanyLayout from '../../Layouts/CompanyLayout.svelte';
    import { onMount, onDestroy, tick } from 'svelte';
    import { page } from '@inertiajs/svelte'

    // Define breadcrumbs for this page
    const breadcrumbs = [
        {
            title: 'Technologies',
            url: route('company.technologies.index'),
            active: false
        },
        {
            title: 'Index',
            url: route('company.technologies.index'),
            active: true
        }
    ];
    
    const pageTitle = 'My Technologies';

    // Reactive variables
    let technologies = [];
    let companyTechnologies = [];
    let maxResearchLevel = 0;
    let currentResearchLevel = 0;
    let loading = true;
    let fetchInterval = null;

    // Drawer state
    let selectedTechnology = null;
    let showTechnologyDrawer = false;
    let selectedCompanyTechnology = null;
    let showCompanyTechnologyDrawer = false;

    let showResearchModal = false;
    let researchTechnology = null;

    // Fetch technologies data
    async function fetchTechnologies() {
        if(technologies.length == 0) loading = true;
        
        try {
            const response = await fetch(route('company.technologies.index'), {
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                }
            });
            
            const data = await response.json();
            technologies = data.technologies;
            companyTechnologies = data.companyTechnologies;
            maxResearchLevel = data.maxResearchLevel;
            currentResearchLevel = data.currentResearchLevel;

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

    // Map companyTechnologies by technology_id for quick lookup (reactive)
    $: companyTechMap = companyTechnologies.reduce((map, ct) => {
        map[ct.technology_id] = ct;
        return map;
    }, {});

    // Helper to get technologies for a level
    function getTechnologiesByLevel(level) {
        return technologies.filter(t => t.level ==level);
    }

    // Open technology drawer (locked/unresearched)
    function openTechnologyDrawer(technology) {
        selectedTechnology = technology;
        showTechnologyDrawer = true;
        const toggleButton = document.querySelector('[data-kt-drawer-toggle="#technology_drawer"]');
        if (toggleButton) {
            toggleButton.click();
        }
    }

    // Open company technology drawer (researched)
    function openCompanyTechnologyDrawer(companyTechnology) {
        selectedCompanyTechnology = companyTechnology;
        showCompanyTechnologyDrawer = true;
        const toggleButton = document.querySelector('[data-kt-drawer-toggle="#company_technology_drawer"]');
        if (toggleButton) {
            toggleButton.click();
        }
    }

    // Close drawers
    function closeTechnologyDrawer() {
        showTechnologyDrawer = false;
        selectedTechnology = null;
    }

    function closeCompanyTechnologyDrawer() {
        showCompanyTechnologyDrawer = false;
        selectedCompanyTechnology = null;
    }

    // Open research modal
    function openResearchModal(technology) {
        researchTechnology = technology;
        showResearchModal = true;
        const toggleButton = document.querySelector('[data-kt-modal-toggle="#research_modal"]');
        if (toggleButton) {
            toggleButton.click();
        }
    }

    // Close research modal
    function closeResearchModal() {
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
            });
            if (response.ok) {
                const data = await response.json();
                showToast(data.message || 'Research started successfully!', 'success');
                closeResearchModal();
                fetchTechnologies();
            } else {
                const errorData = await response.json();
                showToast(errorData.message || 'Error starting research. Please try again.', 'error');
            }
        } catch (error) {
            console.error('Error starting research:', error);
            showToast('Network error. Please check your connection and try again.', 'error');
        }
    }

    onMount(() => {
        fetchTechnologies();
        fetchInterval = setInterval(fetchTechnologies, 60000);
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
    <div class="kt-container-fixed">
        <div class="grid gap-5 lg:gap-7.5">
            <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4">
                <div class="flex flex-col gap-1">
                    <h1 class="text-2xl font-bold text-mono">My Technologies</h1>
                    <p class="text-sm text-secondary-foreground">
                        View your researched and researching technologies
                    </p>
                </div>                     
            </div>

            <div class="kt-card">
                <div class="kt-card-content p-4">
                    {#if loading}
                        <!-- Loading skeleton -->
                        <div class="p-6">
                            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 p-4">
                                {#each Array(10) as _, i}
                                    <div class="kt-card animate-pulse">
                                        <div class="kt-card-body p-4">
                                            <div class="flex items-center justify-center mb-4">
                                                <div class="kt-skeleton h-[180px] w-full rounded-sm"></div>
                                            </div>
                                            <div class="mb-4">
                                                <div class="kt-skeleton h-6 w-3/4 mb-2"></div>
                                                <div class="kt-skeleton h-4 w-1/2 mb-2"></div>
                                                <div class="kt-skeleton h-3 w-full"></div>
                                            </div>
                                            <div class="mb-3">
                                                <div class="flex items-center justify-between mb-2">
                                                    <div class="kt-skeleton h-3 w-12"></div>
                                                    <div class="kt-skeleton h-5 w-20 rounded-full"></div>
                                                </div>
                                                <div class="w-full bg-gray-200 rounded-full h-2">
                                                    <div class="kt-skeleton h-2 w-1/3 rounded-full"></div>
                                                </div>
                                            </div>
                                            <div class="space-y-1">
                                                <div class="flex justify-between">
                                                    <div class="kt-skeleton h-3 w-20"></div>
                                                    <div class="kt-skeleton h-3 w-16"></div>
                                                </div>
                                                <div class="flex justify-between">
                                                    <div class="kt-skeleton h-3 w-24"></div>
                                                    <div class="kt-skeleton h-3 w-12"></div>
                                                </div>
                                                <div class="flex justify-between">
                                                    <div class="kt-skeleton h-3 w-16"></div>
                                                    <div class="kt-skeleton h-3 w-20"></div>
                                                </div>
                                                <div class="flex justify-between">
                                                    <div class="kt-skeleton h-3 w-20"></div>
                                                    <div class="kt-skeleton h-3 w-16"></div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                {/each}
                            </div>
                        </div>
                    {:else if technologies.length ==0}
                        <div class="p-10">
                            <div class="flex flex-col items-center justify-center text-center">
                                <div class="mb-4">
                                    <i class="ki-filled ki-technology-1 text-4xl text-muted-foreground"></i>
                                </div>
                                <h3 class="text-lg font-semibold text-mono mb-2">No technologies found</h3>
                                <p class="text-sm text-secondary-foreground mb-4">
                                    No technologies found to be researched, all products are researched by default.
                                </p>
                            </div>
                        </div>
                    {:else}
                        <!-- Loop by level -->
                        {#each Array(maxResearchLevel + 1) as _, level}
                            <div class="kt-card mb-12 {level < currentResearchLevel ? 'bg-company-technology-completed-level border-blue-400 border-2' : level ==currentResearchLevel ? 'bg-company-technology-current-level border-green-400 border-2' : 'bg-white'} rounded-xl p-4">
                                <div class="kt-card-header">
                                    <div class="kt-card-toolbar">
                                        <div class="flex items-center mb-4">
                                            <h2 class="text-lg font-bold text-mono">Level {level}</h2>
                                            {#if level ==currentResearchLevel}
                                                <span class="kt-badge kt-badge-primary" style="margin-left: 10px;">Current Level</span>
                                            {/if}
                                        </div>
                                    </div>
                                </div>
                                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 p-4">
                                    {#each getTechnologiesByLevel(level) as technology}
                                        {#if technology.is_researched}
                                            <!-- CompanyTechnologyCard (researched) -->
                                            <div class="kt-card kt-card-hover cursor-pointer border-green-400 border-2" on:click={() => openCompanyTechnologyDrawer(companyTechMap[technology.id])}>
                                                <div class="kt-card-body p-4">
                                                    <div class="size-20 mb-4">
                                                        <img 
                                                            class="rounded-lg w-20 h-20 object-cover" 
                                                            src={technology.image_url}
                                                            alt={technology.name}
                                                        />
                                                    </div>

                                                    <div class="mb-4">
                                                        <h3 class="text-lg font-semibold text-mono mb-1">{technology.name}</h3>
                                                        {#if technology.description}
                                                            <p class="text-xs text-muted-foreground line-clamp-2">{technology.description}</p>
                                                        {/if}
                                                    </div>
                                                    <div class="mb-3">
                                                        <div class="flex items-center justify-between mb-2">
                                                            <span class="text-xs font-medium text-muted-foreground">Status</span>
                                                            <span class="kt-badge kt-badge-primary kt-badge-sm text-white">
                                                                {companyTechMap[technology.id].is_researching ? 'Researching' : 'Completed'}
                                                            </span>
                                                        </div>
                                                        <div class="w-full bg-gray-200 rounded-full h-2">
                                                            <div class="kt-progress kt-progress-primary">
                                                                <div class="kt-progress-indicator" style="width: {companyTechMap[technology.id].research_progress}%"></div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="text-xs text-muted-foreground space-y-1">
                                                        <div class="flex justify-between">
                                                            <span>Paid Cost:</span>
                                                            <span class="font-medium">DZD {companyTechMap[technology.id].research_cost}</span>
                                                        </div>
                                                        <div class="flex justify-between">
                                                            <span>Research Time:</span>
                                                            <span class="font-medium">{companyTechMap[technology.id].research_time_days} days</span>
                                                        </div>
                                                        <div class="flex justify-between">
                                                            <span>Started:</span>
                                                            <span class="font-medium">{formatTimestamp(companyTechMap[technology.id].started_at)}</span>
                                                        </div>
                                                        {#if !companyTechMap[technology.id].is_researching}
                                                            <div class="flex justify-between">
                                                                <span>Completed:</span>
                                                                <span class="font-medium">{formatTimestamp(companyTechMap[technology.id].completed_at)}</span>
                                                            </div>
                                                        {/if}
                                                    </div>
                                                </div>
                                            </div>
                                        {:else}
                                            <!-- TechnologyCard (locked) -->
                                            <div class="kt-card kt-card-hover cursor-pointer" style="{currentResearchLevel >= technology.level ? 'opacity: 0.9;' : 'opacity: 0.5; filter: blur(2px);'}" on:click={() => {
                                                if(currentResearchLevel ==technology.level){
                                                    openTechnologyDrawer(technology);
                                                }
                                            }}>
                                                <div class="kt-card-body p-4">
                                                    <div class="size-20 mb-4">
                                                        <img 
                                                            class="rounded-lg w-full h-full object-cover" 
                                                            src={technology.image_url}
                                                            alt={technology.name}
                                                        />
                                                    </div>

                                                    <div class="mb-4">
                                                        <h3 class="text-lg font-semibold text-mono mb-1">{technology.name}</h3>
                                                        {#if technology.description}
                                                            <p class="text-xs text-muted-foreground line-clamp-2">{technology.description}</p>
                                                        {/if}
                                                    </div>
                                                    <div class="mb-3">
                                                        <div class="flex items-center justify-between mb-2">
                                                            <span class="text-xs font-medium text-muted-foreground">Status</span>
                                                            <span class="kt-badge kt-badge-primary kt-badge-sm text-white">Locked</span>
                                                        </div>
                                                        <div class="w-full bg-gray-200 rounded-full h-2">
                                                            <div class="kt-progress kt-progress-secondary">
                                                                <div class="kt-progress-indicator" style="width: 0%"></div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="text-xs text-muted-foreground space-y-1">
                                                        <div class="flex justify-between">
                                                            <span>Research Cost:</span>
                                                            <span class="font-medium">DZD {technology.research_cost}</span>
                                                        </div>
                                                        <div class="flex justify-between">
                                                            <span>Research Time:</span>
                                                            <span class="font-medium">{technology.research_time_days} days</span>
                                                        </div>
                                                    </div>
                                                    {#if level ==currentResearchLevel}
                                                        <button class="kt-btn kt-btn-primary w-full mt-4" on:click|stopPropagation={() => openResearchModal(technology)}>
                                                            <i class="fa-solid fa-rocket text-base"></i>
                                                            Research
                                                        </button>
                                                    {/if}
                                                </div>
                                            </div>
                                        {/if}
                                    {/each}
                                </div>
                            </div>
                        {/each}
                    {/if}
                </div>
            </div>
        </div>
    </div>
    <!-- End of Container -->

    <!-- Hidden buttons to trigger drawers -->
    <button style="display:none" data-kt-drawer-toggle="#technology_drawer"></button>
    <button style="display:none" data-kt-drawer-toggle="#company_technology_drawer"></button>

    <!-- Technology Details Drawer (locked/unresearched) -->
    <div class="hidden kt-drawer kt-drawer-end card flex-col max-w-[90%] w-[450px] top-5 bottom-5 end-5 rounded-xl border border-border" data-kt-drawer="true" data-kt-drawer-container="body" id="technology_drawer">
        <div class="flex items-center justify-between gap-2.5 text-sm text-mono font-semibold px-5 py-2.5 border-b border-b-border">
            Technology Details
            <button class="kt-btn kt-btn-sm kt-btn-icon kt-btn-dim shrink-0" data-kt-drawer-dismiss="true" on:click={closeTechnologyDrawer}>
                <i class="ki-filled ki-cross"></i>
            </button>
        </div>
        <div class="kt-card-content flex flex-col space-y-3 p-5 kt-scrollable-y-auto">
            {#if selectedTechnology}
                <div class="kt-card relative items-center justify-center bg-accent/50 mb-6.5 h-[180px] shadow-none">
                    {#if selectedTechnology.image_url}
                        <img src={selectedTechnology.image_url} alt={selectedTechnology.name} class="h-[180px] w-full object-cover rounded-sm" />
                    {:else}
                        <div class="flex items-center justify-center h-full">
                            <i class="ki-filled ki-technology-1 text-4xl text-muted-foreground"></i>
                        </div>
                    {/if}
                </div>
                <span class="text-base font-medium text-mono">{selectedTechnology.name}</span>
                <span class="text-sm font-normal text-foreground block mb-7">
                    {#if selectedTechnology.description}
                        {selectedTechnology.description}
                    {:else}
                        No description available for this technology.
                    {/if}
                </span>
                <div class="flex flex-col gap-2.5">
                    <div class="flex items-center gap-2.5">
                        <span class="text-xs font-normal text-foreground min-w-14 xl:min-w-24 shrink-0">Level</span>
                        <div><span class="kt-badge kt-badge-primary kt-badge-sm">{selectedTechnology.level}</span></div>
                    </div>
                    <div class="flex items-center gap-2.5">
                        <span class="text-xs font-normal text-foreground min-w-14 xl:min-w-24 shrink-0">Research Cost</span>
                        <div><span class="text-xs font-medium text-foreground">DZD {selectedTechnology.research_cost}</span></div>
                    </div>
                    <div class="flex items-center gap-2.5">
                        <span class="text-xs font-normal text-foreground min-w-14 xl:min-w-24 shrink-0">Research Time</span>
                        <div><span class="text-xs font-medium text-foreground">{selectedTechnology.research_time_days} days</span></div>
                    </div>
                </div>
                <!-- Products Section -->
                {#if selectedTechnology.products && selectedTechnology.products.length > 0}
                    <div class="border-t border-border pt-4">
                        <h3 class="text-sm font-semibold text-mono">Unlocked Products</h3>
                    </div>

                    {#each selectedTechnology.products as product}
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
                    {/each}
                {:else}
                    <div class="border-t border-border pt-4">
                        <h3 class="text-sm font-semibold text-mono mb-3">Unlocked Products</h3>
                        <div class="kt-card opacity-50 blur-sm">
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

    <!-- Company Technology Details Drawer (researched) -->
    <div class="hidden kt-drawer kt-drawer-end card flex-col max-w-[90%] w-[450px] top-5 bottom-5 end-5 rounded-xl border border-border" data-kt-drawer="true" data-kt-drawer-container="body" id="company_technology_drawer">
        <div class="flex items-center justify-between gap-2.5 text-sm text-mono font-semibold px-5 py-2.5 border-b border-b-border">
            Company Technology Details
            <button class="kt-btn kt-btn-sm kt-btn-icon kt-btn-dim shrink-0" data-kt-drawer-dismiss="true" on:click={closeCompanyTechnologyDrawer}>
                <i class="ki-filled ki-cross"></i>
            </button>
        </div>
        <div class="kt-card-content flex flex-col space-y-3 p-5 kt-scrollable-y-auto">
            {#if selectedCompanyTechnology}
                <div class="kt-card relative items-center justify-center bg-accent/50 mb-6.5 h-[180px] shadow-none">
                    {#if selectedCompanyTechnology.technology.image_url}
                        <img src={selectedCompanyTechnology.technology.image_url} alt={selectedCompanyTechnology.technology.name} class="h-[180px] w-full object-cover rounded-sm" />
                    {:else}
                        <div class="flex items-center justify-center h-full">
                            <i class="ki-filled ki-technology-1 text-4xl text-muted-foreground"></i>
                        </div>
                    {/if}
                </div>
                <span class="text-base font-medium text-mono">{selectedCompanyTechnology.technology.name}</span>
                <span class="text-sm font-normal text-foreground block mb-7">
                    {#if selectedCompanyTechnology.technology.description}
                        {selectedCompanyTechnology.technology.description}
                    {:else}
                        No description available for this technology.
                    {/if}
                </span>
                <div class="flex flex-col gap-2.5">
                    <div class="flex items-center gap-2.5">
                        <span class="text-xs font-normal text-foreground min-w-14 xl:min-w-24 shrink-0">Level</span>
                        <div><span class="kt-badge kt-badge-primary kt-badge-sm">{selectedCompanyTechnology.technology.level}</span></div>
                    </div>
                    <div class="flex items-center gap-2.5">
                        <span class="text-xs font-normal text-foreground min-w-14 xl:min-w-24 shrink-0">Paid Cost</span>
                        <div><span class="text-xs font-medium text-foreground">DZD {selectedCompanyTechnology.research_cost}</span></div>
                    </div>
                    <div class="flex items-center gap-2.5">
                        <span class="text-xs font-normal text-foreground min-w-14 xl:min-w-24 shrink-0">Research Time</span>
                        <div><span class="text-xs font-medium text-foreground">{selectedCompanyTechnology.research_time_days} days</span></div>
                    </div>
                    <div class="flex items-center gap-2.5">
                        <span class="text-xs font-normal text-foreground min-w-14 xl:min-w-24 shrink-0">Status</span>
                        <div><span class="kt-badge kt-badge-primary kt-badge-sm">{selectedCompanyTechnology.is_researching ? 'Researching' : 'Completed'}</span></div>
                    </div>
                    <div class="flex items-center gap-2.5">
                        <span class="text-xs font-normal text-foreground min-w-14 xl:min-w-24 shrink-0">Started At</span>
                        <div><span class="text-xs font-medium text-foreground">{formatTimestamp(selectedCompanyTechnology.started_at)}</span></div>
                    </div>
                    <div class="flex items-center gap-2.5">
                        <span class="text-xs font-normal text-foreground min-w-14 xl:min-w-24 shrink-0">Completed At</span>
                        <div><span class="text-xs font-medium text-foreground">{formatTimestamp(selectedCompanyTechnology.completed_at)}</span></div>
                    </div>
                </div>
                <!-- Products Section -->
                {#if selectedCompanyTechnology.technology.products && selectedCompanyTechnology.technology.products.length > 0}
                    <div class="border-t border-border pt-4">
                        <h3 class="text-sm font-semibold text-mono mb-3">Unlocked Products</h3>
                        <div class="space-y-3">
                            {#each selectedCompanyTechnology.technology.products as product}
                                <div class="kt-card">
                                    <div class="kt-card-body p-3">
                                        <div class="flex items-center gap-3">
                                            <div class="flex-shrink-0">
                                                {#if product.image_url}
                                                    <img src={product.image_url} alt={product.name} class="w-12 h-12 rounded-lg object-cover" />
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
                        <div class="flex items-center gap-4">
                            <div class="flex-shrink-0">
                                <img 
                                    class="rounded-lg w-20 h-20 object-cover" 
                                    src={researchTechnology.image_url}
                                    alt={researchTechnology.name}
                                />
                            </div>
                            <div class="flex-1">
                                <h4 class="font-semibold text-mono mb-1">{researchTechnology.name}</h4>
                            </div>
                        </div>
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
                        class="kt-btn kt-btn-secondary text-white"
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

