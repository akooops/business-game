<script>
    import { page } from '@inertiajs/svelte'
    import Notifications from './Notifications.svelte';
    import Timer from './Timer.svelte';
    
    // State to track which primary menu is active
    let activeSection = 'dashboard'; // default to dashboard
    
    // Function to determine active section based on current route
    function getActiveSectionFromRoute(url) {
        // Add more route-to-section mappings here as needed
        if($page.url.includes('company/technologies')){
            return 'technologies';
        }
        if($page.url.includes('company/products') || $page.url.includes('company/product-demand')){
            return 'production';
        }
        if($page.url.includes('company/suppliers') || $page.url.includes('company/purchases')){
            return 'procurement';
        }

        return 'dashboard'; // default fallback
    }
    
    // Reactive statement to update activeSection when route changes
    $: {
        const routeBasedSection = getActiveSectionFromRoute($page.url);
        activeSection = routeBasedSection;
    }
    
    function setActiveSection(section) {
        activeSection = section;
    }
    
    function handleLogout() {
        // Create a form element to submit the logout request
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = route('auth.logout');
        
        // Add CSRF token
        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
        if (csrfToken) {
            const csrfInput = document.createElement('input');
            csrfInput.type = 'hidden';
            csrfInput.name = '_token';
            csrfInput.value = csrfToken;
            form.appendChild(csrfInput);
        }
        
        // Submit the form
        document.body.appendChild(form);
        form.submit();
    }
</script>

<!-- Sidebar -->
<div class="fixed top-0 bottom-0 z-20 hidden lg:flex items-stretch shrink-0 w-(--sidebar-width) bg-muted [--kt-drawer-enable:true] lg:[--kt-drawer-enable:false]" data-kt-drawer="true" data-kt-drawer-class="kt-drawer kt-drawer-start flex flex-row top-0 bottom-0" id="sidebar">
    <!-- Sidebar Primary -->
    <div class="flex flex-col items-stretch shrink-0 gap-5 py-5 w-[70px] border-e border-input" id="sidebar_primary">
        <div class="hidden lg:flex items-center justify-center shrink-0" id="sidebar_primary_header">
            <a href={route('admin.dashboard.index')}>
                <img class="min-h-[30px] h-[30px]" src="/assets/images/logo.png" style="width: 50px; height: 50px;"/>    
            </a>
        </div>
        
        <div class="flex grow shrink-0" id="sidebar_primary_content">
            <div class="kt-scrollable-y-hover grow gap-2.5 shrink-0 flex ps-4 flex-col" data-kt-scrollable="true" data-kt-scrollable-dependencies="#sidebar_primary_header,#sidebar_primary_footer" data-kt-scrollable-height="auto" data-kt-scrollable-offset="80px" data-kt-scrollable-wrappers="#sidebar_primary_content">
                
                {#if hasPermission('admin.dashboard.index')}
                <button 
                    class="kt-btn kt-btn-icon kt-btn-ghost rounded-md size-9 border border-transparent hover:bg-background hover:[&_i]:text-primary hover:border-border {activeSection === 'dashboard' ? 'bg-background [&_i]:text-primary border-border' : ''}" 
                    data-kt-tooltip="" 
                    data-kt-tooltip-placement="right"
                    on:click={() => setActiveSection('dashboard')}>
                    <span class="kt-menu-icon">
                        <i class="ki-outline ki-home-3 text-lg"></i>
                    </span>
                    <span class="kt-tooltip" data-kt-tooltip-content="true">
                        Dashboard
                    </span>
                </button>
                {/if}

                {#if hasPermission('admin.technologies.index')}
                <button 
                    class="kt-btn kt-btn-icon kt-btn-ghost rounded-md size-9 border border-transparent hover:bg-background hover:[&_i]:text-primary hover:border-border {activeSection === 'technologies' ? 'bg-background [&_i]:text-primary border-border' : ''}" 
                    data-kt-tooltip="" 
                    data-kt-tooltip-placement="right"
                    on:click={() => setActiveSection('technologies')}>
                    <span class="kt-menu-icon">
                        <i class="ki-outline ki-technology-1 text-lg"></i>
                    </span>
                    <span class="kt-tooltip" data-kt-tooltip-content="true">
                        R&D
                    </span>
                </button>
                {/if}

                {#if hasPermission('admin.products.index')}
                <button 
                    class="kt-btn kt-btn-icon kt-btn-ghost rounded-md size-9 border border-transparent hover:bg-background hover:[&_i]:text-primary hover:border-border {activeSection === 'production' ? 'bg-background [&_i]:text-primary border-border' : ''}" 
                    data-kt-tooltip="" 
                    data-kt-tooltip-placement="right"
                    on:click={() => setActiveSection('production')}>
                    <span class="kt-menu-icon">
                        <i class="ki-outline ki-handcart text-lg"></i>
                    </span>
                    <span class="kt-tooltip" data-kt-tooltip-content="true">
                        Production
                    </span>
                </button>
                {/if}
                
                {#if hasPermission('company.suppliers.index')}
                <button 
                    class="kt-btn kt-btn-icon kt-btn-ghost rounded-md size-9 border border-transparent hover:bg-background hover:[&_i]:text-primary hover:border-border {activeSection === 'procurement' ? 'bg-background [&_i]:text-primary border-border' : ''}" 
                    data-kt-tooltip="" 
                    data-kt-tooltip-placement="right"
                    on:click={() => setActiveSection('procurement')}>
                    <span class="kt-menu-icon">
                        <i class="ki-outline ki-ship text-lg"></i>
                    </span>
                    <span class="kt-tooltip" data-kt-tooltip-content="true">
                        Procurement
                    </span>
                </button>
                {/if}

                {#if hasPermission('admin.employee-profiles.index')}
                <button 
                    class="kt-btn kt-btn-icon kt-btn-ghost rounded-md size-9 border border-transparent hover:bg-background hover:[&_i]:text-primary hover:border-border {activeSection === 'employees' ? 'bg-background [&_i]:text-primary border-border' : ''}" 
                    data-kt-tooltip="" 
                    data-kt-tooltip-placement="right"
                    on:click={() => setActiveSection('employees')}>
                    <span class="kt-menu-icon">
                        <i class="ki-outline ki-users text-lg"></i>
                    </span>
                    <span class="kt-tooltip" data-kt-tooltip-content="true">
                        HR Management
                    </span>
                </button>
                {/if}

                {#if hasPermission('admin.countries.index')}
                <button 
                    class="kt-btn kt-btn-icon kt-btn-ghost rounded-md size-9 border border-transparent hover:bg-background hover:[&_i]:text-primary hover:border-border {activeSection === 'logistics' ? 'bg-background [&_i]:text-primary border-border' : ''}" 
                    data-kt-tooltip="" 
                    data-kt-tooltip-placement="right"
                    on:click={() => setActiveSection('logistics')}>
                    <span class="kt-menu-icon">
                        <i class="ki-outline ki-logistic text-lg"></i>
                    </span>
                    <span class="kt-tooltip" data-kt-tooltip-content="true">
                        Logistics Management
                    </span>
                </button>
                {/if}

                {#if hasPermission('admin.settings.index')}
                <button 
                    class="kt-btn kt-btn-icon kt-btn-ghost rounded-md size-9 border border-transparent hover:bg-background hover:[&_i]:text-primary hover:border-border {activeSection === 'settings' ? 'bg-background [&_i]:text-primary border-border' : ''}" 
                    data-kt-tooltip="" 
                    data-kt-tooltip-placement="right"
                    on:click={() => setActiveSection('settings')}>
                    <span class="kt-menu-icon">
                        <i class="ki-outline ki-setting-2 text-lg"></i>
                    </span>
                    <span class="kt-tooltip" data-kt-tooltip-content="true">
                        Settings
                    </span>
                </button>
                {/if}
            </div>
        </div>
        
        <div class="flex flex-col gap-5 items-center shrink-0" id="sidebar_primary_footer">
            <div class="flex flex-col gap-1.5">
                <Timer/>
                <Notifications />
            </div>
            
            <!-- User -->
            <div data-kt-dropdown="true" data-kt-dropdown-offset="10px, 10px" data-kt-dropdown-offset-rtl="-20px, 10px" data-kt-dropdown-placement="bottom-start" data-kt-dropdown-placement-rtl="bottom-end" data-kt-dropdown-trigger="click">
                <div class="cursor-pointer shrink-0" data-kt-dropdown-toggle="true">
                    <img alt="" class="size-9 rounded-full shrink-0" src="{$page.props.auth.user.avatarUrl}"/>
                </div>
                <div class="kt-dropdown-menu w-[250px]" data-kt-dropdown-menu="true">
                    <div class="flex items-center justify-between px-2.5 py-1.5 gap-1.5">
                        <div class="flex items-center gap-2">
                            <img alt="" class="size-9 shrink-0 rounded-full border-2 border-green-500" src="{$page.props.auth.user.avatarUrl}"/>
                            <div class="flex flex-col gap-1.5">
                                <span class="text-sm text-foreground font-semibold leading-none">
                                    {$page.props.auth.user.fullname}
                                </span>
                                <a class="text-xs text-secondary-foreground hover:text-primary font-medium leading-none" href="#">
                                    {$page.props.auth.user.email}
                                </a>
                            </div>
                        </div>
                    </div>
                    <ul class="kt-dropdown-menu-sub">
                        <li>
                            <div class="kt-dropdown-menu-separator"></div>
                        </li>
                    </ul>
                    <div class="px-2.5 pt-1.5 mb-2.5 flex flex-col gap-3.5">
                        <div class="flex items-center gap-2 justify-between">
                            <span class="flex items-center gap-2">
                                <i class="ki-filled ki-moon text-base text-muted-foreground"></i>
                                <span class="font-medium text-2sm">
                                    Dark Mode
                                </span>
                            </span>
                            <input class="kt-switch" data-kt-theme-switch-state="dark" data-kt-theme-switch-toggle="true" name="check" type="checkbox" value="1"/>
                        </div>
                        
                        <a class="kt-btn kt-btn-outline justify-center w-full" on:click={handleLogout}>
                            Log out
                        </a>
                    </div>
                </div>
            </div>
            <!-- End of User -->
        </div>
    </div>
    <!-- End of Sidebar Primary -->
    
    <!-- Sidebar Secondary -->
    <div class="flex items-stretch grow shrink-0 justify-center ps-1.5 my-5 me-1.5" id="sidebar_secondary">
        <div class="kt-scrollable-y-auto grow" data-kt-scrollable="true" data-kt-scrollable-height="auto" data-kt-scrollable-offset="0px" data-kt-scrollable-wrappers="#sidebar_secondary">
            <!-- Sidebar Menu -->
            <div class="kt-menu flex flex-col w-full gap-px px-2.5" data-kt-menu="true" data-kt-menu-accordion-expand-all="false" id="sidebar_menu">
                
                <!-- Dashboard Section -->
                {#if activeSection === 'dashboard'}
                <div class="mb-3">
                    <h3 class="text-xs text-muted-foreground uppercase ps-2.5 mb-2.5">
                        Dashboard
                    </h3>
                    {#if hasPermission('admin.dashboard.index')}
                    <div class="kt-menu-item">
                        <a class="kt-menu-link py-2 ps-2.5 pe-2.5 rounded-md border border-transparent {isActiveRoute('admin.dashboard.index') ? 'border-border bg-background' : ''} kt-menu-link-hover:bg-background kt-menu-link-hover:border-border" href={route('admin.dashboard.index')}>
                            <span class="kt-menu-icon items-start text-lg text-secondary-foreground kt-menu-item-active:text-mono kt-menu-item-here:text-mono">
                                <i class="ki-outline ki-home-3"></i>
                            </span>
                            <span class="kt-menu-title text-sm text-foreground font-medium kt-menu-item-here:text-mono kt-menu-item-active:text-mono kt-menu-link-hover:text-mono ms-2">
                                Dashboard Home
                            </span>
                        </a>
                    </div>
                    {/if}
                    
                    <!-- Add more dashboard-related links here -->
                    <div class="kt-menu-item">
                        <a class="kt-menu-link py-2 ps-2.5 pe-2.5 rounded-md border border-transparent {isActiveRoute('/admin/analytics') ? 'border-border bg-background' : ''} kt-menu-link-hover:bg-background kt-menu-link-hover:border-border" href="#">
                            <span class="kt-menu-icon items-start text-lg text-secondary-foreground kt-menu-item-active:text-mono kt-menu-item-here:text-mono">
                                <i class="ki-outline ki-chart-line"></i>
                            </span>
                            <span class="kt-menu-title text-sm text-foreground font-medium kt-menu-item-here:text-mono kt-menu-item-active:text-mono kt-menu-link-hover:text-mono ms-2">
                                Analytics
                            </span>
                        </a>
                    </div>
                    
                    <div class="kt-menu-item">
                        <a class="kt-menu-link py-2 ps-2.5 pe-2.5 rounded-md border border-transparent {isActiveRoute('/admin/reports') ? 'border-border bg-background' : ''} kt-menu-link-hover:bg-background kt-menu-link-hover:border-border" href="#">
                            <span class="kt-menu-icon items-start text-lg text-secondary-foreground kt-menu-item-active:text-mono kt-menu-item-here:text-mono">
                                <i class="ki-outline ki-graph-up"></i>
                            </span>
                            <span class="kt-menu-title text-sm text-foreground font-medium kt-menu-item-here:text-mono kt-menu-item-active:text-mono kt-menu-link-hover:text-mono ms-2">
                                Reports
                            </span>
                        </a>
                    </div>
                </div>
                {/if}

                <!-- Technologies Management Section -->
                {#if activeSection === 'technologies'}
                <div class="mb-3">
                    <h3 class="text-xs text-muted-foreground uppercase ps-2.5 mb-2.5">
                        R&D
                    </h3>
                    {#if hasPermission('company.technologies.index')}
                    <div class="kt-menu-item">
                        <a class="kt-menu-link py-2 ps-2.5 pe-2.5 rounded-md border border-transparent {isActiveRoute(route('company.technologies.index')) ? 'border-border bg-background' : ''} kt-menu-link-hover:bg-background kt-menu-link-hover:border-border" href={route('company.technologies.index')}>
                            <span class="kt-menu-icon items-start text-lg text-secondary-foreground kt-menu-item-active:text-mono kt-menu-item-here:text-mono">
                                <i class="ki-outline ki-technology-1"></i>
                            </span>
                            <span class="kt-menu-title text-sm text-foreground font-medium kt-menu-item-here:text-mono kt-menu-item-active:text-mono kt-menu-link-hover:text-mono ms-2">
                                Technologies
                            </span>
                        </a>
                    </div>
                    {/if}
                </div>
                {/if}
                
                <!-- Products Management Section -->
                {#if activeSection === 'production'}
                <div class="mb-3">
                    <h3 class="text-xs text-muted-foreground uppercase ps-2.5 mb-2.5">
                        Production
                    </h3>
                    {#if hasPermission('company.products.index')}
                    <div class="kt-menu-item">
                        <a class="kt-menu-link py-2 ps-2.5 pe-2.5 rounded-md border border-transparent {isActiveRoute(route('company.products.index')) ? 'border-border bg-background' : ''} kt-menu-link-hover:bg-background kt-menu-link-hover:border-border" href={route('company.products.index')}>
                            <span class="kt-menu-icon items-start text-lg text-secondary-foreground kt-menu-item-active:text-mono kt-menu-item-here:text-mono">
                                <i class="ki-outline ki-handcart"></i>
                            </span>
                            <span class="kt-menu-title text-sm text-foreground font-medium kt-menu-item-here:text-mono kt-menu-item-active:text-mono kt-menu-link-hover:text-mono ms-2">
                                Products
                            </span>
                        </a>
                    </div>
                    {/if}

                    {#if hasPermission('company.product-demand.index')}
                    <div class="kt-menu-item">
                        <a class="kt-menu-link py-2 ps-2.5 pe-2.5 rounded-md border border-transparent {isActiveRoute(route('company.product-demand.index')) ? 'border-border bg-background' : ''} kt-menu-link-hover:bg-background kt-menu-link-hover:border-border" href={route('company.product-demand.index')}>
                            <span class="kt-menu-icon items-start text-lg text-secondary-foreground kt-menu-item-active:text-mono kt-menu-item-here:text-mono">
                                <i class="ki-outline ki-chart-line"></i>
                            </span>
                            <span class="kt-menu-title text-sm text-foreground font-medium kt-menu-item-here:text-mono kt-menu-item-active:text-mono kt-menu-link-hover:text-mono ms-2">
                                Product Demand
                            </span>
                        </a>
                    </div>
                    {/if}

                    {#if hasPermission('admin.machines.index')}
                    <div class="kt-menu-item">
                        <a class="kt-menu-link py-2 ps-2.5 pe-2.5 rounded-md border border-transparent {isActiveRoute(route('admin.machines.index')) ? 'border-border bg-background' : ''} kt-menu-link-hover:bg-background kt-menu-link-hover:border-border" href={route('admin.machines.index')}>
                            <span class="kt-menu-icon items-start text-lg text-secondary-foreground kt-menu-item-active:text-mono kt-menu-item-here:text-mono">
                                <i class="ki-outline ki-setting-3"></i>
                            </span>
                            <span class="kt-menu-title text-sm text-foreground font-medium kt-menu-item-here:text-mono kt-menu-item-active:text-mono kt-menu-link-hover:text-mono ms-2">
                                Machines
                            </span>
                        </a>
                    </div>
                    {/if}
                </div>
                {/if}

                <!-- Procurement Management Section -->

                {#if activeSection === 'procurement'}
                <div class="mb-3">
                    <h3 class="text-xs text-muted-foreground uppercase ps-2.5 mb-2.5">
                        Procurement Management
                    </h3>
                    {#if hasPermission('company.suppliers.index')}
                    <div class="kt-menu-item">
                        <a class="kt-menu-link py-2 ps-2.5 pe-2.5 rounded-md border border-transparent {isActiveRoute(route('company.suppliers.index')) ? 'border-border bg-background' : ''} kt-menu-link-hover:bg-background kt-menu-link-hover:border-border" href={route('company.suppliers.index')}>
                            <span class="kt-menu-icon items-start text-lg text-secondary-foreground kt-menu-item-active:text-mono kt-menu-item-here:text-mono">
                                <i class="ki-outline ki-ship"></i>
                            </span>
                            <span class="kt-menu-title text-sm text-foreground font-medium kt-menu-item-here:text-mono kt-menu-item-active:text-mono kt-menu-link-hover:text-mono ms-2">
                                Suppliers
                            </span>
                        </a>
                    </div>
                    {/if}

                    {#if hasPermission('company.purchases.index')}
                    <div class="kt-menu-item">
                        <a class="kt-menu-link py-2 ps-2.5 pe-2.5 rounded-md border border-transparent {isActiveRoute(route('company.purchases.index')) ? 'border-border bg-background' : ''} kt-menu-link-hover:bg-background kt-menu-link-hover:border-border" href={route('company.purchases.index')}>
                            <span class="kt-menu-icon items-start text-lg text-secondary-foreground kt-menu-item-active:text-mono kt-menu-item-here:text-mono">
                                <i class="ki-outline ki-handcart"></i>
                            </span>
                            <span class="kt-menu-title text-sm text-foreground font-medium kt-menu-item-here:text-mono kt-menu-item-active:text-mono kt-menu-link-hover:text-mono ms-2">
                                Purchases
                            </span>
                        </a>
                    </div>
                    {/if}
                </div>
                {/if}

                <!-- Employee Profiles Management Section -->
                {#if activeSection === 'employees'}
                <div class="mb-3">
                    <h3 class="text-xs text-muted-foreground uppercase ps-2.5 mb-2.5">
                        HR Management
                    </h3>
                    {#if hasPermission('admin.employee-profiles.index')}
                    <div class="kt-menu-item">
                        <a class="kt-menu-link py-2 ps-2.5 pe-2.5 rounded-md border border-transparent {isActiveRoute(route('admin.employee-profiles.index')) ? 'border-border bg-background' : ''} kt-menu-link-hover:bg-background kt-menu-link-hover:border-border" href={route('admin.employee-profiles.index')}>
                            <span class="kt-menu-icon items-start text-lg text-secondary-foreground kt-menu-item-active:text-mono kt-menu-item-here:text-mono">
                                <i class="ki-outline ki-users"></i>
                            </span> 
                            <span class="kt-menu-title text-sm text-foreground font-medium kt-menu-item-here:text-mono kt-menu-item-active:text-mono kt-menu-link-hover:text-mono ms-2">
                                Employee Profiles
                            </span>
                        </a>
                    </div>
                    {/if}
                </div>
                {/if}

                <!-- Logistics Management Section -->
                {#if activeSection === 'logistics'}
                <div class="mb-3">
                    <h3 class="text-xs text-muted-foreground uppercase ps-2.5 mb-2.5">
                        Logistics Management
                    </h3>
                    {#if hasPermission('admin.countries.index')}
                    <div class="kt-menu-item">
                        <a class="kt-menu-link py-2 ps-2.5 pe-2.5 rounded-md border border-transparent {isActiveRoute(route('admin.countries.index')) ? 'border-border bg-background' : ''} kt-menu-link-hover:bg-background kt-menu-link-hover:border-border" href={route('admin.countries.index')}>
                            <span class="kt-menu-icon items-start text-lg text-secondary-foreground kt-menu-item-active:text-mono kt-menu-item-here:text-mono">
                                <i class="ki-outline ki-flag"></i>
                            </span> 
                            <span class="kt-menu-title text-sm text-foreground font-medium kt-menu-item-here:text-mono kt-menu-item-active:text-mono kt-menu-link-hover:text-mono ms-2">
                                Countries
                            </span>
                        </a>
                    </div>
                    {/if}

                    {#if hasPermission('admin.wilayas.index')}
                    <div class="kt-menu-item">
                        <a class="kt-menu-link py-2 ps-2.5 pe-2.5 rounded-md border border-transparent {isActiveRoute(route('admin.wilayas.index')) ? 'border-border bg-background' : ''} kt-menu-link-hover:bg-background kt-menu-link-hover:border-border" href={route('admin.wilayas.index')}>
                            <span class="kt-menu-icon items-start text-lg text-secondary-foreground kt-menu-item-active:text-mono kt-menu-item-here:text-mono">
                                <i class="ki-outline ki-map"></i>
                            </span> 
                            <span class="kt-menu-title text-sm text-foreground font-medium kt-menu-item-here:text-mono kt-menu-item-active:text-mono kt-menu-link-hover:text-mono ms-2">
                                Wilayas
                            </span>
                        </a>
                    </div>
                    {/if}
                </div>
                {/if}

                <!-- Settings Section -->
                {#if activeSection === 'settings'}
                <div class="mb-3">
                    <h3 class="text-xs text-muted-foreground uppercase ps-2.5 mb-2.5">
                        Settings
                    </h3>
                    {#if hasPermission('admin.settings.index')}
                    <div class="kt-menu-item">
                        <a class="kt-menu-link py-2 ps-2.5 pe-2.5 rounded-md border border-transparent {isActiveRoute(route('admin.settings.index')) ? 'border-border bg-background' : ''} kt-menu-link-hover:bg-background kt-menu-link-hover:border-border" href={route('admin.settings.index')}>
                            <span class="kt-menu-icon items-start text-lg text-secondary-foreground kt-menu-item-active:text-mono kt-menu-item-here:text-mono">
                                <i class="ki-outline ki-setting-2 text-lg"></i>
                            </span>
                            <span class="kt-menu-title text-sm text-foreground font-medium kt-menu-item-here:text-mono kt-menu-item-active:text-mono kt-menu-link-hover:text-mono ms-2">
                                Settings
                            </span>
                        </a>
                    </div>
                    {/if}
                </div>
                {/if}
            </div>
            <!-- End of Sidebar Menu -->
        </div>
    </div>
    <!-- End of Sidebar Secondary-->
</div>
<!-- End of Sidebar -->