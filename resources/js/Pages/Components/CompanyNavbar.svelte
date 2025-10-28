<script>
    import { page } from '@inertiajs/svelte'
</script>

<!-- Navbar -->
<div class="navbar bg-muted hidden lg:flex lg:items-stretch border-y border-input lg:mb-10 [--kt-drawer-enable:true] lg:[--kt-drawer-enable:false]" data-kt-drawer="true" data-kt-drawer-class="kt-drawer kt-drawer-start fixed z-10 top-0 bottom-0 w-full me-5 max-w-[250px] p-5 lg:p-0 overflow-auto" id="navbar">
    <!-- Container -->
    <div class="kt-container-fixed lg:flex lg:flex-wrap lg:justify-center lg:items-center gap-2 px-0 lg:px-7.5">
        <!-- Mega Menu -->
        <div class="kt-menu items-stretch flex-col lg:flex-row gap-0.5 lg:gap-1 grow lg:grow-0" data-kt-menu="true" id="mega_menu">
            <div class="kt-menu-item {$page.url === route('company.dashboard.index') ? 'active' : ''} px-4 lg:px-5 rounded-md shadow-md bg-gradient-to-b from-muted/40 to-muted/20">
                <a class="kt-menu-link lg:py-3.5 border-b border-b-transparent kt-menu-item-active:border-b-mono text-foreground kt-menu-item-hover:text-mono kt-menu-item-active:text-mono kt-menu-item-here:border-b-mono kt-menu-item-here:text-mono" 
                    href={route('company.dashboard.index')}>
                    <span class="kt-menu-title font-medium text-foreground text-sm">
                        Dashboard
                    </span>
                </a>
            </div>
            <div class="kt-menu-item px-4 lg:px-5 rounded-md shadow-sm bg-gradient-to-b from-muted/30 to-muted/10" data-kt-menu-item-toggle="dropdown" data-kt-menu-item-trigger="click|lg:hover">
                <div class="kt-menu-link lg:py-3.5 border-b border-b-transparent kt-menu-item-active:border-b-mono text-foreground kt-menu-item-hover:text-mono kt-menu-item-active:text-mono kt-menu-item-here:border-b-mono kt-menu-item-here:text-mono">
                    <span class="kt-menu-title font-medium text-foreground text-sm">R&amp;D</span>
                </div>
                <div class="kt-menu-dropdown kt-menu-default py-2.5 w-full max-w-[220px]">
                    <div class="kt-menu-item px-4 lg:px-5 rounded-md shadow-sm bg-gradient-to-b from-muted/30 to-muted/10">
                        <a class="kt-menu-link" href={route('company.technologies.index')} tabindex="0">
                            <span class="kt-menu-icon">
                                <i class="fa-solid fa-microchip text-sm"></i>
                            </span>
                            <span class="kt-menu-title grow-0">Technologies</span>
                        </a>
                    </div>
                </div>
            </div>

            <div class="kt-menu-item px-4 lg:px-5 rounded-md shadow-sm bg-gradient-to-b from-muted/30 to-muted/10" data-kt-menu-item-toggle="dropdown" data-kt-menu-item-trigger="click|lg:hover">
                <div class="kt-menu-link lg:py-3.5 border-b border-b-transparent kt-menu-item-active:border-b-mono text-foreground kt-menu-item-hover:text-mono kt-menu-item-active:text-mono kt-menu-item-here:border-b-mono kt-menu-item-here:text-mono">
                    <span class="kt-menu-title font-medium text-foreground text-sm">Production</span>
                </div>
                <div class="kt-menu-dropdown kt-menu-default py-2.5 w-full max-w-[220px]">
                    <div class="kt-menu-item px-4 lg:px-5 rounded-md shadow-sm bg-gradient-to-b from-muted/30 to-muted/10">
                        <a class="kt-menu-link" href={route('company.products.index')} tabindex="0" style="color: #000000 !important;">
                            <span class="kt-menu-icon" style="color: #666666 !important;">
                                <i class="fa-solid fa-box text-sm"></i>
                            </span>
                            <span class="kt-menu-title grow-0" style="color: #000000 !important;">Products</span>
                        </a>
                    </div>
                    <div class="kt-menu-item px-4 lg:px-5 rounded-md shadow-sm bg-gradient-to-b from-muted/30 to-muted/10">
                        <a class="kt-menu-link" href={route('company.product-demand.index')} tabindex="0" style="color: #000000 !important;">
                            <span class="kt-menu-icon" style="color: #666666 !important;">
                                <i class="fa-solid fa-chart-simple text-sm"></i>
                            </span>
                            <span class="kt-menu-title grow-0" style="color: #000000 !important;">Product Demand</span>
                        </a>
                    </div>
                    <div class="kt-menu-item px-4 lg:px-5 rounded-md shadow-sm bg-gradient-to-b from-muted/30 to-muted/10">
                        <a class="kt-menu-link" href={route('company.machines.index')} tabindex="0" style="color: #000000 !important;">
                            <span class="kt-menu-icon" style="color: #666666 !important;">
                                <i class="fa-solid fa-gears text-sm"></i>
                            </span>
                            <span class="kt-menu-title grow-0" style="color: #000000 !important;">Machines</span>
                        </a>
                    </div>
                    <div class="kt-menu-item px-4 lg:px-5 rounded-md shadow-sm bg-gradient-to-b from-muted/30 to-muted/10">
                        <a class="kt-menu-link" href={route('company.production-orders.index')} tabindex="0" style="color: #000000 !important;">
                            <span class="kt-menu-icon" style="color: #666666 !important;">
                                <i class="fa-solid fa-list-check text-sm"></i>
                            </span>
                            <span class="kt-menu-title grow-0" style="color: #000000 !important;">Production Orders</span>
                        </a>
                    </div>
                </div>
            </div>
            
            <div class="kt-menu-item px-4 lg:px-5 rounded-md shadow-sm bg-gradient-to-b from-muted/30 to-muted/10" data-kt-menu-item-toggle="dropdown" data-kt-menu-item-trigger="click|lg:hover">
                <div class="kt-menu-link lg:py-3.5 border-b border-b-transparent kt-menu-item-active:border-b-mono text-foreground kt-menu-item-hover:text-mono kt-menu-item-active:text-mono kt-menu-item-here:border-b-mono kt-menu-item-here:text-mono">
                    <span class="kt-menu-title font-medium text-foreground text-sm">Inventory</span>
                </div>
                <div class="kt-menu-dropdown kt-menu-default py-2.5 w-full max-w-[220px]">
                    <div class="kt-menu-item px-4 lg:px-5 rounded-md shadow-sm bg-gradient-to-b from-muted/30 to-muted/10">
                        <a class="kt-menu-link" href={route('company.inventory.index')} tabindex="0">
                            <span class="kt-menu-icon">
                                <i class="fa-solid fa-boxes-stacked text-sm"></i>
                            </span>
                            <span class="kt-menu-title grow-0">Inventory</span>
                        </a>
                    </div>
                </div>
            </div>

            <div class="kt-menu-item px-4 lg:px-5 rounded-md shadow-sm bg-gradient-to-b from-muted/30 to-muted/10" data-kt-menu-item-toggle="dropdown" data-kt-menu-item-trigger="click|lg:hover">
                <div class="kt-menu-link lg:py-3.5 border-b border-b-transparent kt-menu-item-active:border-b-mono text-foreground kt-menu-item-hover:text-mono kt-menu-item-active:text-mono kt-menu-item-here:border-b-mono kt-menu-item-here:text-mono">
                    <span class="kt-menu-title font-medium text-foreground text-sm">Procurement</span>
                </div>
                <div class="kt-menu-dropdown kt-menu-default py-2.5 w-full max-w-[220px]">
                    <div class="kt-menu-item px-4 lg:px-5 rounded-md shadow-sm bg-gradient-to-b from-muted/30 to-muted/10">
                        <a class="kt-menu-link" href={route('company.suppliers.index')} tabindex="0">
                            <span class="kt-menu-icon">
                                <i class="fa-solid fa-truck text-sm"></i>
                            </span>
                            <span class="kt-menu-title grow-0">Suppliers</span>
                        </a>
                    </div>
                    <div class="kt-menu-item px-4 lg:px-5 rounded-md shadow-sm bg-gradient-to-b from-muted/30 to-muted/10">
                        <a class="kt-menu-link" href={route('company.purchases.index')} tabindex="0">
                            <span class="kt-menu-icon">
                                <i class="fa-solid fa-cart-shopping text-sm"></i>
                            </span>
                            <span class="kt-menu-title grow-0">Purchases</span>
                        </a>
                    </div>
                </div>
            </div>

            <div class="kt-menu-item px-4 lg:px-5 rounded-md shadow-sm bg-gradient-to-b from-muted/30 to-muted/10" data-kt-menu-item-toggle="dropdown" data-kt-menu-item-trigger="click|lg:hover">
                <div class="kt-menu-link lg:py-3.5 border-b border-b-transparent kt-menu-item-active:border-b-mono text-foreground kt-menu-item-hover:text-mono kt-menu-item-active:text-mono kt-menu-item-here:border-b-mono kt-menu-item-here:text-mono">
                    <span class="kt-menu-title font-medium text-foreground text-sm">Sales</span>
                </div>
                <div class="kt-menu-dropdown kt-menu-default py-2.5 w-full max-w-[220px]">
                    <div class="kt-menu-item px-4 lg:px-5 rounded-md shadow-sm bg-gradient-to-b from-muted/30 to-muted/10">
                        <a class="kt-menu-link" href={route('company.sales.index')} tabindex="0">
                            <span class="kt-menu-icon">
                                <i class="fa-solid fa-cart-shopping text-sm"></i>
                            </span>
                            <span class="kt-menu-title grow-0">Sales</span>
                        </a>
                    </div>
                </div>
            </div>

            <div class="kt-menu-item px-4 lg:px-5 rounded-md shadow-sm bg-gradient-to-b from-muted/30 to-muted/10" data-kt-menu-item-toggle="dropdown" data-kt-menu-item-trigger="click|lg:hover">
                <div class="kt-menu-link lg:py-3.5 border-b border-b-transparent kt-menu-item-active:border-b-mono text-foreground kt-menu-item-hover:text-mono kt-menu-item-active:text-mono kt-menu-item-here:border-b-mono kt-menu-item-here:text-mono">
                    <span class="kt-menu-title font-medium text-foreground text-sm">HR</span>
                </div>
                <div class="kt-menu-dropdown kt-menu-default py-2.5 w-full max-w-[220px]">
                    <div class="kt-menu-item px-4 lg:px-5 rounded-md shadow-sm bg-gradient-to-b from-muted/30 to-muted/10">
                        <a class="kt-menu-link" href={route('company.employees.index')} tabindex="0">
                            <span class="kt-menu-icon">
                                <i class="fa-solid fa-users text-sm"></i>
                            </span>
                            <span class="kt-menu-title grow-0">Employees</span>
                        </a>
                    </div>
                </div>
            </div>

            <div class="kt-menu-item px-4 lg:px-5 rounded-md shadow-sm bg-gradient-to-b from-muted/30 to-muted/10" data-kt-menu-item-toggle="dropdown" data-kt-menu-item-trigger="click|lg:hover">
                <div class="kt-menu-link lg:py-3.5 border-b border-b-transparent kt-menu-item-active:border-b-mono text-foreground kt-menu-item-hover:text-mono kt-menu-item-active:text-mono kt-menu-item-here:border-b-mono kt-menu-item-here:text-mono">
                    <span class="kt-menu-title font-medium text-foreground text-sm">Marketing</span>
                </div>
                <div class="kt-menu-dropdown kt-menu-default py-2.5 w-full max-w-[220px]">
                    <div class="kt-menu-item px-4 lg:px-5 rounded-md shadow-sm bg-gradient-to-b from-muted/30 to-muted/10">
                        <a class="kt-menu-link" href={route('company.advertisers.index')} tabindex="0">
                            <span class="kt-menu-icon">
                                <i class="fa-solid fa-users text-sm"></i>
                            </span>
                            <span class="kt-menu-title grow-0">Advertisers</span>
                        </a>
                    </div>
                    <div class="kt-menu-item px-4 lg:px-5 rounded-md shadow-sm bg-gradient-to-b from-muted/30 to-muted/10">
                        <a class="kt-menu-link" href={route('company.ads.index')} tabindex="0">
                            <span class="kt-menu-icon">
                                <i class="fa-solid fa-bullhorn text-sm"></i>
                            </span>
                            <span class="kt-menu-title grow-0">Ad Campaigns</span>
                        </a>
                    </div>
                </div>
            </div>

            <div class="kt-menu-item px-4 lg:px-5 rounded-md shadow-sm bg-gradient-to-b from-muted/30 to-muted/10" data-kt-menu-item-toggle="dropdown" data-kt-menu-item-trigger="click|lg:hover">
                <div class="kt-menu-link lg:py-3.5 border-b border-b-transparent kt-menu-item-active:border-b-mono text-foreground kt-menu-item-hover:text-mono kt-menu-item-active:text-mono kt-menu-item-here:border-b-mono kt-menu-item-here:text-mono">
                    <span class="kt-menu-title font-medium text-foreground text-sm">Finance</span>
                </div>
                <div class="kt-menu-dropdown kt-menu-default py-2.5 w-full max-w-[220px]">
                    <div class="kt-menu-item px-4 lg:px-5 rounded-md shadow-sm bg-gradient-to-b from-muted/30 to-muted/10">
                        <a class="kt-menu-link" href={route('company.banks.index')} tabindex="0">
                            <span class="kt-menu-icon">
                                <i class="fa-solid fa-building-columns text-sm"></i>
                            </span>
                            <span class="kt-menu-title grow-0">Banks</span>
                        </a>
                    </div>
                    <div class="kt-menu-item px-4 lg:px-5 rounded-md shadow-sm bg-gradient-to-b from-muted/30 to-muted/10">
                        <a class="kt-menu-link" href={route('company.loans.index')} tabindex="0">
                            <span class="kt-menu-icon">
                                <i class="fa-solid fa-money-bill-transfer text-sm"></i>
                            </span>
                            <span class="kt-menu-title grow-0">Loans</span>
                        </a>
                    </div>
                    <div class="kt-menu-item px-4 lg:px-5 rounded-md shadow-sm bg-gradient-to-b from-muted/30 to-muted/10">
                        <a class="kt-menu-link" href={route('company.transactions.index')} tabindex="0">
                            <span class="kt-menu-icon">
                                <i class="fa-solid fa-money-bill-transfer text-sm"></i>
                            </span>
                            <span class="kt-menu-title grow-0">Transactions</span>
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