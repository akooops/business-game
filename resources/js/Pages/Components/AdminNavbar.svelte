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
                    href={route('admin.dashboard.index')}>
                    <span class="kt-menu-title font-medium text-foreground text-sm">
                        Dashboard
                    </span>
                </a>
            </div>
            
            <div class="kt-menu-item" data-kt-menu-item-offset="0,0|lg:-20px, 0" data-kt-menu-item-offset-rtl="0,0|lg:20px, 0" data-kt-menu-item-overflow="true" data-kt-menu-item-placement="bottom-start" data-kt-menu-item-placement-rtl="bottom-end" data-kt-menu-item-toggle="dropdown" data-kt-menu-item-trigger="click|lg:hover">
                <div class="kt-menu-link lg:py-3.5 border-b border-b-transparent kt-menu-item-active:border-b-mono text-foreground kt-menu-item-hover:text-mono kt-menu-item-active:text-mono kt-menu-item-here:border-b-mono kt-menu-item-here:text-mono">
                    <span class="kt-menu-title font-medium text-foreground text-sm">
                        Users
                    </span>
                    <span class="kt-menu-arrow flex lg:hidden">
                    <span class="flex kt-menu-item-show:hidden">
                    <i class="ki-filled ki-plus text-xs text-secondary-foreground">
                    </i>
                    </span>
                    <span class="hidden kt-menu-item-show:inline-flex">
                    <i class="ki-filled ki-minus text-xs text-secondary-foreground">
                    </i>
                    </span>
                    </span>
                </div>
                <div class="kt-menu-dropdown kt-menu-default py-2.5 w-full max-w-[220px]">
                    <div class="kt-menu-item">
                        <a class="kt-menu-link" href={route('admin.users.index')} tabindex="0">
                        <span class="kt-menu-icon">
                            <i class="fa-solid fa-user text-sm"></i>
                        </span>
                        <span class="kt-menu-title grow-0">
                            Users
                        </span>
                        </a>
                    </div>

                    <div class="kt-menu-item">
                        <a class="kt-menu-link" href={route('admin.companies.index')} tabindex="0">
                        <span class="kt-menu-icon">
                            <i class="fa-solid fa-building text-sm"></i>
                        </span>
                        <span class="kt-menu-title grow-0">
                            Companies
                        </span>
                        </a>
                    </div>
                </div>
            </div>

            <div class="kt-menu-item" data-kt-menu-item-offset="0,0|lg:-20px, 0" data-kt-menu-item-offset-rtl="0,0|lg:20px, 0" data-kt-menu-item-overflow="true" data-kt-menu-item-placement="bottom-start" data-kt-menu-item-placement-rtl="bottom-end" data-kt-menu-item-toggle="dropdown" data-kt-menu-item-trigger="click|lg:hover">
                <div class="kt-menu-link lg:py-3.5 border-b border-b-transparent kt-menu-item-active:border-b-mono text-foreground kt-menu-item-hover:text-mono kt-menu-item-active:text-mono kt-menu-item-here:border-b-mono kt-menu-item-here:text-mono">
                    <span class="kt-menu-title font-medium text-foreground text-sm">
                        R&D
                    </span>
                    <span class="kt-menu-arrow flex lg:hidden">
                    <span class="flex kt-menu-item-show:hidden">
                    <i class="ki-filled ki-plus text-xs text-secondary-foreground">
                    </i>
                    </span>
                    <span class="hidden kt-menu-item-show:inline-flex">
                    <i class="ki-filled ki-minus text-xs text-secondary-foreground">
                    </i>
                    </span>
                    </span>
                </div>
                <div class="kt-menu-dropdown kt-menu-default py-2.5 w-full max-w-[220px]">
                    <div class="kt-menu-item">
                        <a class="kt-menu-link" href={route('admin.technologies.index')} tabindex="0">
                        <span class="kt-menu-icon">
                            <i class="fa-solid fa-microchip text-sm"></i>
                        </span>
                        <span class="kt-menu-title grow-0">
                            Technologies
                        </span>
                        </a>
                    </div>
                </div>
            </div>

            <div class="kt-menu-item" data-kt-menu-item-offset="0,0|lg:-20px, 0" data-kt-menu-item-offset-rtl="0,0|lg:20px, 0" data-kt-menu-item-overflow="true" data-kt-menu-item-placement="bottom-start" data-kt-menu-item-placement-rtl="bottom-end" data-kt-menu-item-toggle="dropdown" data-kt-menu-item-trigger="click|lg:hover">
                <div class="kt-menu-link lg:py-3.5 border-b border-b-transparent kt-menu-item-active:border-b-mono text-foreground kt-menu-item-hover:text-mono kt-menu-item-active:text-mono kt-menu-item-here:border-b-mono kt-menu-item-here:text-mono">
                    <span class="kt-menu-title font-medium text-foreground text-sm">
                        Production
                    </span>
                    <span class="kt-menu-arrow flex lg:hidden">
                    <span class="flex kt-menu-item-show:hidden">
                    <i class="ki-filled ki-plus text-xs text-secondary-foreground">
                    </i>
                    </span>
                    <span class="hidden kt-menu-item-show:inline-flex">
                    <i class="ki-filled ki-minus text-xs text-secondary-foreground">
                    </i>
                    </span>
                    </span>
                </div>
                <div class="kt-menu-dropdown kt-menu-default py-2.5 w-full max-w-[220px]">
                    <div class="kt-menu-item">
                        <a class="kt-menu-link" href={route('admin.products.index')} tabindex="0">
                        <span class="kt-menu-icon">
                            <i class="fa-solid fa-box text-sm"></i>
                        </span>
                        <span class="kt-menu-title grow-0">
                            Products
                        </span>
                        </a>
                    </div>

                    <div class="kt-menu-item">
                        <a class="kt-menu-link" href={route('admin.product-recipes.index')} tabindex="0">
                        <span class="kt-menu-icon">
                            <i class="fa-solid fa-spoon text-sm"></i>
                        </span>
                        <span class="kt-menu-title grow-0">
                            Product Recipes
                        </span>
                        </a>
                    </div>

                    <div class="kt-menu-item">
                        <a class="kt-menu-link" href={route('admin.product-demand.index')} tabindex="0">
                        <span class="kt-menu-icon">
                            <i class="fa-solid fa-chart-simple text-sm"></i>
                        </span>
                        <span class="kt-menu-title grow-0">
                            Product Demand
                        </span>
                        </a>
                    </div>

                    <div class="kt-menu-item">
                        <a class="kt-menu-link" href={route('admin.machines.index')} tabindex="0">
                        <span class="kt-menu-icon">
                            <i class="fa-solid fa-gears text-sm"></i>
                        </span>
                        <span class="kt-menu-title grow-0">
                            Machines
                        </span>
                        </a>
                    </div>
                </div>
            </div>

            <div class="kt-menu-item" data-kt-menu-item-offset="0,0|lg:-20px, 0" data-kt-menu-item-offset-rtl="0,0|lg:20px, 0" data-kt-menu-item-overflow="true" data-kt-menu-item-placement="bottom-start" data-kt-menu-item-placement-rtl="bottom-end" data-kt-menu-item-toggle="dropdown" data-kt-menu-item-trigger="click|lg:hover">
                <div class="kt-menu-link lg:py-3.5 border-b border-b-transparent kt-menu-item-active:border-b-mono text-foreground kt-menu-item-hover:text-mono kt-menu-item-active:text-mono kt-menu-item-here:border-b-mono kt-menu-item-here:text-mono">
                    <span class="kt-menu-title font-medium text-foreground text-sm">
                        HR
                    </span>
                </div>

                <div class="kt-menu-dropdown kt-menu-default py-2.5 w-full max-w-[220px]">
                    <div class="kt-menu-item">
                        <a class="kt-menu-link" href={route('admin.employee-profiles.index')} tabindex="0">
                        <span class="kt-menu-icon">
                            <i class="fa-solid fa-helmet-safety text-sm"></i>
                        </span>
                        <span class="kt-menu-title grow-0">
                            Employee Profiles
                        </span>
                        </a>
                    </div>
                </div>
            </div>

            <div class="kt-menu-item" data-kt-menu-item-offset="0,0|lg:-20px, 0" data-kt-menu-item-offset-rtl="0,0|lg:20px, 0" data-kt-menu-item-overflow="true" data-kt-menu-item-placement="bottom-start" data-kt-menu-item-placement-rtl="bottom-end" data-kt-menu-item-toggle="dropdown" data-kt-menu-item-trigger="click|lg:hover">
                <div class="kt-menu-link lg:py-3.5 border-b border-b-transparent kt-menu-item-active:border-b-mono text-foreground kt-menu-item-hover:text-mono kt-menu-item-active:text-mono kt-menu-item-here:border-b-mono kt-menu-item-here:text-mono">
                    <span class="kt-menu-title font-medium text-foreground text-sm">
                        Procurement
                    </span>
                    <span class="kt-menu-arrow flex lg:hidden">
                    <span class="flex kt-menu-item-show:hidden">
                    <i class="ki-filled ki-plus text-xs text-secondary-foreground">
                    </i>
                    </span>
                    <span class="hidden kt-menu-item-show:inline-flex">
                    <i class="ki-filled ki-minus text-xs text-secondary-foreground">
                    </i>
                    </span>
                    </span>
                </div>
                <div class="kt-menu-dropdown kt-menu-default py-2.5 w-full max-w-[220px]">
                    <div class="kt-menu-item">
                        <a class="kt-menu-link" href={route('admin.products.index')} tabindex="0">
                        <span class="kt-menu-icon">
                            <i class="fa-solid fa-globe text-sm"></i>
                        </span>
                        <span class="kt-menu-title grow-0">
                            Countries
                        </span>
                        </a>
                    </div>

                    <div class="kt-menu-item">
                        <a class="kt-menu-link" href={route('admin.product-recipes.index')} tabindex="0">
                        <span class="kt-menu-icon">
                            <i class="fa-solid fa-location-dot text-sm"></i>
                        </span>
                        <span class="kt-menu-title grow-0">
                            Wilayas
                        </span>
                        </a>
                    </div>

                    <div class="kt-menu-item">
                        <a class="kt-menu-link" href={route('admin.product-demand.index')} tabindex="0">
                        <span class="kt-menu-icon">
                            <i class="fa-solid fa-truck text-sm"></i>
                        </span>
                        <span class="kt-menu-title grow-0">
                            Suppliers
                        </span>
                        </a>
                    </div>
                </div>
            </div>

            <div class="kt-menu-item" data-kt-menu-item-offset="0,0|lg:-20px, 0" data-kt-menu-item-offset-rtl="0,0|lg:20px, 0" data-kt-menu-item-overflow="true" data-kt-menu-item-placement="bottom-start" data-kt-menu-item-placement-rtl="bottom-end" data-kt-menu-item-toggle="dropdown" data-kt-menu-item-trigger="click|lg:hover">
                <div class="kt-menu-link lg:py-3.5 border-b border-b-transparent kt-menu-item-active:border-b-mono text-foreground kt-menu-item-hover:text-mono kt-menu-item-active:text-mono kt-menu-item-here:border-b-mono kt-menu-item-here:text-mono">
                    <span class="kt-menu-title font-medium text-foreground text-sm">
                        Settings
                    </span>
                    <span class="kt-menu-arrow flex lg:hidden">
                    <span class="flex kt-menu-item-show:hidden">
                    <i class="ki-filled ki-plus text-xs text-secondary-foreground">
                    </i>
                    </span>
                    <span class="hidden kt-menu-item-show:inline-flex">
                    <i class="ki-filled ki-minus text-xs text-secondary-foreground">
                    </i>
                    </span>
                    </span>
                </div>
                <div class="kt-menu-dropdown kt-menu-default py-2.5 w-full max-w-[220px]">
                    <div class="kt-menu-item">
                        <a class="kt-menu-link" href={route('admin.settings.index')} tabindex="0">
                        <span class="kt-menu-icon">
                            <i class="fa-solid fa-gear text-sm"></i>
                        </span>
                        <span class="kt-menu-title grow-0">
                            Settings
                        </span>
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