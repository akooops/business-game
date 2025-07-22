<script>
    import { page } from '@inertiajs/svelte'
    
    // State to track which primary menu is active
    let activeSection = 'dashboard'; // default to dashboard
    
    // Function to determine active section based on current route
    function getActiveSectionFromRoute(url) {
        if (url.includes('/admin/users') || url.includes('/admin/roles') || url.includes('/admin/permissions') || url.includes('/admin/companies')) {
            return 'users';
        }

        if (url.includes('/admin/technologies')) {
            return 'technologies';
        }

        if (url.includes('/admin/products') || url.includes('/admin/product-recipes') || url.includes('/admin/product-demand') || url.includes('/admin/machines')) {
            return 'production';
        }

        if (url.includes('/admin/employee-profiles')) {
            return 'hr';
        }

        if (url.includes('/admin/countries') || url.includes('/admin/wilayas') || url.includes('/admin/suppliers')) {
            return 'logistics';
        }

        if (url.includes('/admin/settings')) {
            return 'settings';
        }
    }
</script>

<!-- Navbar -->
<div class="navbar bg-muted hidden lg:flex lg:items-stretch border-y border-input lg:mb-10 [--kt-drawer-enable:true] lg:[--kt-drawer-enable:false]" data-kt-drawer="true" data-kt-drawer-class="kt-drawer kt-drawer-start fixed z-10 top-0 bottom-0 w-full me-5 max-w-[250px] p-5 lg:p-0 overflow-auto" id="navbar">
    <!-- Container -->
    <div class="kt-container-fixed lg:flex lg:flex-wrap lg:justify-between lg:items-center gap-2 px-0 lg:px-7.5">
        <!-- Mega Menu -->
        <div class="kt-menu items-stretch flex-col lg:flex-row gap-5 lg:gap-7.5 grow lg:grow-0" data-kt-menu="true" id="mega_menu">
            <div class="kt-menu-item active">
                <a class="kt-menu-link lg:py-3.5 border-b border-b-transparent kt-menu-item-active:border-b-mono text-foreground kt-menu-item-hover:text-mono kt-menu-item-active:text-mono kt-menu-item-here:border-b-mono kt-menu-item-here:text-mono" 
                    href={route('company.dashboard.index')}>
                    <span class="kt-menu-title font-medium text-foreground text-sm">
                        Dashboard
                    </span>
                </a>
            </div>
            <div class="kt-menu-item" data-kt-menu-item-toggle="dropdown" data-kt-menu-item-trigger="click|lg:hover">
                <div class="kt-menu-link lg:py-3.5 border-b border-b-transparent kt-menu-item-active:border-b-mono text-foreground kt-menu-item-hover:text-mono kt-menu-item-active:text-mono kt-menu-item-here:border-b-mono kt-menu-item-here:text-mono">
                    <span class="kt-menu-title font-medium text-foreground text-sm">R&amp;D</span>
                </div>
                <div class="kt-menu-dropdown kt-menu-default py-2.5 w-full max-w-[220px]">
                    <div class="kt-menu-item">
                        <a class="kt-menu-link" href={route('company.technologies.index')} tabindex="0">
                            <span class="kt-menu-icon">
                                <i class="ki-outline ki-technology-1 text-lg"></i>
                            </span>
                            <span class="kt-menu-title grow-0">Technologies</span>
                        </a>
                    </div>
                </div>
            </div>
            <div class="kt-menu-item" data-kt-menu-item-toggle="dropdown" data-kt-menu-item-trigger="click|lg:hover">
                <div class="kt-menu-link lg:py-3.5 border-b border-b-transparent kt-menu-item-active:border-b-mono text-foreground kt-menu-item-hover:text-mono kt-menu-item-active:text-mono kt-menu-item-here:border-b-mono kt-menu-item-here:text-mono">
                    <span class="kt-menu-title font-medium text-foreground text-sm">Production</span>
                </div>
                <div class="kt-menu-dropdown kt-menu-default py-2.5 w-full max-w-[220px]">
                    <div class="kt-menu-item">
                        <a class="kt-menu-link" href={route('company.products.index')} tabindex="0">
                            <span class="kt-menu-icon">
                                <i class="ki-outline ki-handcart text-lg"></i>
                            </span>
                            <span class="kt-menu-title grow-0">Products</span>
                        </a>
                    </div>
                    <div class="kt-menu-item">
                        <a class="kt-menu-link" href={route('company.product-demand.index')} tabindex="0">
                            <span class="kt-menu-icon">
                                <i class="ki-outline ki-chart-line text-lg"></i>
                            </span>
                            <span class="kt-menu-title grow-0">Product Demand</span>
                        </a>
                    </div>
                    <div class="kt-menu-item">
                        <a class="kt-menu-link" href={route('company.machines.index')} tabindex="0">
                            <span class="kt-menu-icon">
                                <i class="ki-outline ki-setting-3 text-lg"></i>
                            </span>
                            <span class="kt-menu-title grow-0">Machines</span>
                        </a>
                    </div>
                </div>
            </div>
            <div class="kt-menu-item" data-kt-menu-item-toggle="dropdown" data-kt-menu-item-trigger="click|lg:hover">
                <div class="kt-menu-link lg:py-3.5 border-b border-b-transparent kt-menu-item-active:border-b-mono text-foreground kt-menu-item-hover:text-mono kt-menu-item-active:text-mono kt-menu-item-here:border-b-mono kt-menu-item-here:text-mono">
                    <span class="kt-menu-title font-medium text-foreground text-sm">Procurement</span>
                </div>
                <div class="kt-menu-dropdown kt-menu-default py-2.5 w-full max-w-[220px]">
                    <div class="kt-menu-item">
                        <a class="kt-menu-link" href={route('company.suppliers.index')} tabindex="0">
                            <span class="kt-menu-icon">
                                <i class="ki-outline ki-ship text-lg"></i>
                            </span>
                            <span class="kt-menu-title grow-0">Suppliers</span>
                        </a>
                    </div>
                    <div class="kt-menu-item">
                        <a class="kt-menu-link" href={route('company.purchases.index')} tabindex="0">
                            <span class="kt-menu-icon">
                                <i class="ki-outline ki-handcart text-lg"></i>
                            </span>
                            <span class="kt-menu-title grow-0">Purchases</span>
                        </a>
                    </div>
                </div>
            </div>
            <div class="kt-menu-item" data-kt-menu-item-toggle="dropdown" data-kt-menu-item-trigger="click|lg:hover">
                <div class="kt-menu-link lg:py-3.5 border-b border-b-transparent kt-menu-item-active:border-b-mono text-foreground kt-menu-item-hover:text-mono kt-menu-item-active:text-mono kt-menu-item-here:border-b-mono kt-menu-item-here:text-mono">
                    <span class="kt-menu-title font-medium text-foreground text-sm">HR</span>
                </div>
                <div class="kt-menu-dropdown kt-menu-default py-2.5 w-full max-w-[220px]">
                    <div class="kt-menu-item">
                        <a class="kt-menu-link" href={route('company.employees.index')} tabindex="0">
                            <span class="kt-menu-icon">
                                <i class="ki-outline ki-users text-lg"></i>
                            </span>
                            <span class="kt-menu-title grow-0">Employees</span>
                        </a>
                    </div>
                    <div class="kt-menu-item">
                        <a class="kt-menu-link" href={route('company.employees.recruit-page')} tabindex="0">
                            <span class="kt-menu-icon">
                                <i class="ki-outline ki-users text-lg"></i>
                            </span>
                            <span class="kt-menu-title grow-0">Recruitment</span>
                        </a>
                    </div>
                </div>
            </div>
            <div class="kt-menu-item" data-kt-menu-item-toggle="dropdown" data-kt-menu-item-trigger="click|lg:hover">
                <div class="kt-menu-link lg:py-3.5 border-b border-b-transparent kt-menu-item-active:border-b-mono text-foreground kt-menu-item-hover:text-mono kt-menu-item-active:text-mono kt-menu-item-here:border-b-mono kt-menu-item-here:text-mono">
                    <span class="kt-menu-title font-medium text-foreground text-sm">Logistics Management</span>
                </div>
                <div class="kt-menu-dropdown kt-menu-default py-2.5 w-full max-w-[220px]">
                    <div class="kt-menu-item">
                        <a class="kt-menu-link" href={route('admin.countries.index')} tabindex="0">
                            <span class="kt-menu-icon">
                                <i class="ki-outline ki-flag text-lg"></i>
                            </span>
                            <span class="kt-menu-title grow-0">Countries</span>
                        </a>
                    </div>
                    <div class="kt-menu-item">
                        <a class="kt-menu-link" href={route('admin.wilayas.index')} tabindex="0">
                            <span class="kt-menu-icon">
                                <i class="ki-outline ki-map text-lg"></i>
                            </span>
                            <span class="kt-menu-title grow-0">Wilayas</span>
                        </a>
                    </div>
                </div>
            </div>
            <div class="kt-menu-item" data-kt-menu-item-toggle="dropdown" data-kt-menu-item-trigger="click|lg:hover">
                <div class="kt-menu-link lg:py-3.5 border-b border-b-transparent kt-menu-item-active:border-b-mono text-foreground kt-menu-item-hover:text-mono kt-menu-item-active:text-mono kt-menu-item-here:border-b-mono kt-menu-item-here:text-mono">
                    <span class="kt-menu-title font-medium text-foreground text-sm">Settings</span>
                </div>
                <div class="kt-menu-dropdown kt-menu-default py-2.5 w-full max-w-[220px]">
                    <div class="kt-menu-item">
                        <a class="kt-menu-link" href={route('admin.settings.index')} tabindex="0">
                            <span class="kt-menu-icon">
                                <i class="ki-outline ki-setting-2 text-lg"></i>
                            </span>
                            <span class="kt-menu-title grow-0">Settings</span>
                        </a>
                    </div>
                </div>
            </div>
        </div>
        <!-- End of Mega Menu -->
    </div>
    <!-- End of Container -->
</div>
<!-- End of Navbar -->