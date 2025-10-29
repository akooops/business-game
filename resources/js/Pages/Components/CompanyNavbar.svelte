<script>
    import { page } from '@inertiajs/svelte'
    import { onMount } from 'svelte'
    
    onMount(() => {
        // Force dropdown positioning after Metronic initialization
        const forceDropdownPosition = () => {
            requestAnimationFrame(() => {
                const dropdowns = document.querySelectorAll('#navbar .kt-menu-item[data-kt-menu-item-toggle="dropdown"] .kt-menu-dropdown');
                dropdowns.forEach(dropdown => {
                    const menuItem = dropdown.closest('.kt-menu-item');
                    if (menuItem) {
                        // Get the full height of the menu item including all padding
                        const menuItemRect = menuItem.getBoundingClientRect();
                        const menuItemHeight = menuItem.offsetHeight || menuItemRect.height;
                        const menuLink = menuItem.querySelector('.kt-menu-link');
                        const menuLinkRect = menuLink ? menuLink.getBoundingClientRect() : null;
                        const menuLinkBottom = menuLinkRect ? menuLinkRect.bottom - menuItemRect.top : menuItemHeight;
                        
                        // Check if dropdown is visible or should be visible (hover/click state ONLY - not based on active state)
                        // Force visibility check only on hover, not on active state
                        const isHovered = menuItem.matches(':hover');
                        const isDropdownHovered = dropdown.matches(':hover');
                        const isClickOpen = menuItem.getAttribute('data-kt-menu-dropdown-toggle') === 'true';
                        // Only show if actually hovered (parent or dropdown) or explicitly opened via click - ignore active state
                        const isVisible = isHovered || isDropdownHovered || isClickOpen;
                        
                        // Set dropdown position to overlap slightly with menu item bottom for seamless mouse transition
                        // Start dropdown slightly above the bottom to create overlap zone
                        dropdown.style.setProperty('position', 'absolute', 'important');
                        dropdown.style.setProperty('top', `${menuLinkBottom - 2}px`, 'important'); // Start 2px above bottom for overlap
                        dropdown.style.setProperty('left', '50%', 'important');
                        dropdown.style.setProperty('margin-top', '0', 'important');
                        dropdown.style.setProperty('padding-top', '6px', 'important'); // Padding creates invisible hover zone at top
                        dropdown.style.setProperty('transform', isVisible ? 'translateX(-50%) translateY(0)' : 'translateX(-50%) translateY(-10px)', 'important');
                        dropdown.style.setProperty('z-index', '1000', 'important');
                        dropdown.style.setProperty('background', 'white', 'important');
                        dropdown.style.setProperty('border-radius', '8px', 'important');
                        dropdown.style.setProperty('box-shadow', '0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06)', 'important');
                        dropdown.style.setProperty('min-width', '200px', 'important');
                        dropdown.style.setProperty('width', 'max-content', 'important');
                        dropdown.style.setProperty('max-width', 'none', 'important');
                        dropdown.style.setProperty('opacity', isVisible ? '1' : '0', 'important');
                        dropdown.style.setProperty('visibility', isVisible ? 'visible' : 'hidden', 'important');
                        dropdown.style.setProperty('right', 'auto', 'important');
                        dropdown.style.setProperty('bottom', 'auto', 'important');
                        dropdown.style.setProperty('margin-left', '0', 'important');
                        dropdown.style.setProperty('margin-right', '0', 'important');
                        dropdown.style.setProperty('margin-bottom', '0', 'important');
                        dropdown.style.setProperty('pointer-events', 'auto', 'important');
                    }
                });
            });
        };
        
        // Initialize all dropdowns as hidden first
        const initializeDropdowns = () => {
            const dropdowns = document.querySelectorAll('#navbar .kt-menu-item[data-kt-menu-item-toggle="dropdown"] .kt-menu-dropdown');
            dropdowns.forEach(dropdown => {
                const menuItem = dropdown.closest('.kt-menu-item');
                if (menuItem && !menuItem.matches(':hover')) {
                    // Force hide all dropdowns that are not currently hovered
                    dropdown.style.setProperty('opacity', '0', 'important');
                    dropdown.style.setProperty('visibility', 'hidden', 'important');
                    dropdown.style.setProperty('display', 'none', 'important');
                    dropdown.classList.remove('show');
                    menuItem.setAttribute('data-kt-menu-dropdown-toggle', 'false');
                }
            });
        };
        
        // Run initialization first, then positioning
        initializeDropdowns();
        
        // Run immediately and after multiple delays to catch all initialization phases
        forceDropdownPosition();
        setTimeout(() => { initializeDropdowns(); forceDropdownPosition(); }, 10);
        setTimeout(() => { initializeDropdowns(); forceDropdownPosition(); }, 50);
        setTimeout(() => { initializeDropdowns(); forceDropdownPosition(); }, 100);
        setTimeout(() => { initializeDropdowns(); forceDropdownPosition(); }, 200);
        setTimeout(() => { initializeDropdowns(); forceDropdownPosition(); }, 500);
        setTimeout(() => { initializeDropdowns(); forceDropdownPosition(); }, 1000);
        
        // Use MutationObserver to watch for dropdown visibility changes
        const observer = new MutationObserver(() => {
            forceDropdownPosition();
        });
        
        // Observe the navbar for any DOM changes
        const navbar = document.getElementById('navbar');
        if (navbar) {
            observer.observe(navbar, {
                childList: true,
                subtree: true,
                attributes: true,
                attributeFilter: ['class', 'style']
            });
        }
        
        // Also observe hover and click events to fix position when dropdown shows
        const menuItems = document.querySelectorAll('#navbar .kt-menu-item[data-kt-menu-item-toggle="dropdown"]');
        const hideTimeouts = new Map(); // Track hide timeouts for each menu item
        
        menuItems.forEach(item => {
            // Fix position on hover
            item.addEventListener('mouseenter', () => {
                const dropdown = item.querySelector('.kt-menu-dropdown');
                if (dropdown) {
                    // Clear any pending hide timeout
                    const timeout = hideTimeouts.get(item);
                    if (timeout) {
                        clearTimeout(timeout);
                        hideTimeouts.delete(item);
                    }
                    // Mark as explicitly opened on hover
                    item.setAttribute('data-kt-menu-dropdown-toggle', 'true');
                }
                forceDropdownPosition();
                setTimeout(forceDropdownPosition, 10);
                setTimeout(forceDropdownPosition, 50);
            });
            
            // Fix position on click (for click-based dropdowns)
            item.addEventListener('click', (e) => {
                const dropdown = item.querySelector('.kt-menu-dropdown');
                if (dropdown && dropdown.style.display !== 'none') {
                    item.setAttribute('data-kt-menu-dropdown-toggle', 'true');
                }
                forceDropdownPosition();
                setTimeout(forceDropdownPosition, 10);
                setTimeout(forceDropdownPosition, 50);
                setTimeout(forceDropdownPosition, 100);
            });
            
            // Allow hovering over dropdown itself to keep it visible
            const dropdown = item.querySelector('.kt-menu-dropdown');
            if (dropdown) {
                dropdown.addEventListener('mouseenter', () => {
                    // Clear any pending hide timeout when entering dropdown
                    const timeout = hideTimeouts.get(item);
                    if (timeout) {
                        clearTimeout(timeout);
                        hideTimeouts.delete(item);
                    }
                    // Keep dropdown visible
                    item.setAttribute('data-kt-menu-dropdown-toggle', 'true');
                    forceDropdownPosition();
                });
                
                dropdown.addEventListener('mouseleave', (e) => {
                    const relatedTarget = e.relatedTarget;
                    
                    // If mouse is going back to menu item, don't hide
                    if (relatedTarget && (item.contains(relatedTarget) || item === relatedTarget)) {
                        // Mouse is going back to menu item, cancel any timeout
                        const timeout = hideTimeouts.get(item);
                        if (timeout) {
                            clearTimeout(timeout);
                            hideTimeouts.delete(item);
                        }
                        return;
                    }
                    
                    // Hide dropdown when leaving the dropdown area
                    const hideTimeout = setTimeout(() => {
                        // Double-check mouse position
                        if (!item.matches(':hover') && !dropdown.matches(':hover')) {
                            dropdown.style.setProperty('transform', 'translateX(-50%) translateY(-10px)', 'important');
                            dropdown.style.setProperty('opacity', '0', 'important');
                            dropdown.style.setProperty('visibility', 'hidden', 'important');
                            dropdown.style.setProperty('display', 'none', 'important');
                            dropdown.classList.remove('show');
                            item.setAttribute('data-kt-menu-dropdown-toggle', 'false');
                            hideTimeouts.delete(item);
                        }
                    }, 200); // Delay to allow mouse movement
                    hideTimeouts.set(item, hideTimeout);
                });
            }
            
            item.addEventListener('mouseleave', (e) => {
                const dropdown = item.querySelector('.kt-menu-dropdown');
                if (dropdown) {
                    // Check where the mouse is going
                    const relatedTarget = e.relatedTarget;
                    
                    // If mouse is moving to dropdown, don't hide
                    if (relatedTarget && (dropdown.contains(relatedTarget) || dropdown === relatedTarget)) {
                        // Mouse is going to dropdown, cancel any timeout
                        const timeout = hideTimeouts.get(item);
                        if (timeout) {
                            clearTimeout(timeout);
                            hideTimeouts.delete(item);
                        }
                        return;
                    }
                    
                    // Add a longer delay before hiding to allow mouse to move to dropdown
                    const hideTimeout = setTimeout(() => {
                        // Double-check if mouse is actually over dropdown or item before hiding
                        if (!item.matches(':hover') && !dropdown.matches(':hover')) {
                            dropdown.style.setProperty('transform', 'translateX(-50%) translateY(-10px)', 'important');
                            dropdown.style.setProperty('opacity', '0', 'important');
                            dropdown.style.setProperty('visibility', 'hidden', 'important');
                            dropdown.style.setProperty('display', 'none', 'important');
                            dropdown.classList.remove('show');
                            item.setAttribute('data-kt-menu-dropdown-toggle', 'false');
                            hideTimeouts.delete(item);
                        }
                    }, 300); // Longer delay to allow moving cursor to dropdown
                    hideTimeouts.set(item, hideTimeout);
                }
            });
        });
        
        // Watch for dropdown show/hide via attribute changes
        const dropdownObserver = new MutationObserver((mutations) => {
            mutations.forEach((mutation) => {
                if (mutation.type === 'attributes' && 
                    (mutation.attributeName === 'class' || mutation.attributeName === 'style')) {
                    const target = mutation.target;
                    if (target.classList && target.classList.contains('kt-menu-dropdown')) {
                        forceDropdownPosition();
                    }
                }
            });
        });
        
        // Observe all existing and future dropdowns
        const observeDropdowns = () => {
            const dropdowns = document.querySelectorAll('#navbar .kt-menu-dropdown');
            dropdowns.forEach(dropdown => {
                dropdownObserver.observe(dropdown, {
                    attributes: true,
                    attributeFilter: ['class', 'style']
                });
            });
        };
        
        observeDropdowns();
        setTimeout(observeDropdowns, 100);
        setTimeout(observeDropdowns, 500);
        
        // Recalculate on resize
        let resizeTimeout;
        window.addEventListener('resize', () => {
            clearTimeout(resizeTimeout);
            resizeTimeout = setTimeout(forceDropdownPosition, 100);
        });
        
        // Also run on Inertia navigation events
        if (window.Inertia) {
            document.addEventListener('inertia:start', () => {
                // Hide all dropdowns on navigation start
                initializeDropdowns();
            });
            document.addEventListener('inertia:finish', () => {
                // Reinitialize and reposition after navigation
                setTimeout(() => { initializeDropdowns(); forceDropdownPosition(); }, 50);
                setTimeout(() => { initializeDropdowns(); forceDropdownPosition(); }, 200);
                setTimeout(observeDropdowns, 100);
            });
        }
    });
</script>

<!-- Navbar -->
<div class="navbar hidden lg:flex lg:items-stretch border-y lg:mb-6 [--kt-drawer-enable:true] lg:[--kt-drawer-enable:false]" data-kt-drawer="true" data-kt-drawer-class="kt-drawer kt-drawer-start fixed z-10 top-0 bottom-0 w-full me-5 max-w-[250px] p-5 lg:p-0 overflow-auto" id="navbar" style="background-color: #193D39; border-color: #193D39; padding-top: 8px; padding-bottom: 8px; width: fit-content; max-width: 90%; margin-left: auto; margin-right: auto; border-radius: 145px;">
    <!-- Container -->
    <div class="kt-container-fixed lg:flex lg:flex-wrap lg:justify-center lg:items-center gap-2 px-0 lg:px-7.5">
        <!-- Mega Menu -->
        <div class="kt-menu items-stretch flex-col lg:flex-row gap-0.5 lg:gap-1 grow lg:grow-0" data-kt-menu="true" id="mega_menu">
            <div class="kt-menu-item {$page.url === new URL(route('company.dashboard.index')).pathname ? 'active px-6 py-1 rounded-full navbar-active' : 'px-6 py-1 rounded-full'}">
                <a class="kt-menu-link lg:py-2 border-b border-b-transparent {$page.url === new URL(route('company.dashboard.index')).pathname ? 'text-black font-bold' : 'text-white hover:text-white'}" 
                    href={route('company.dashboard.index')}>
                    <span class="kt-menu-title font-medium text-sm">
                        Dashboard
                    </span>
                </a>
            </div>
            <div class="kt-menu-item {$page.url.startsWith(new URL(route('company.technologies.index')).pathname) ? 'active px-6 py-1 rounded-full navbar-active' : 'px-6 py-1 rounded-full'}" data-kt-menu-item-toggle="dropdown" data-kt-menu-item-trigger="hover">
                <div class="kt-menu-link lg:py-2 border-b border-b-transparent {$page.url.startsWith(new URL(route('company.technologies.index')).pathname) ? 'text-black font-bold' : 'text-white hover:text-white'}">
                    <span class="kt-menu-title font-medium text-sm">R&amp;D</span>
                </div>
                <div class="kt-menu-dropdown kt-menu-default py-2.5 w-full max-w-[220px]">
                    <div class="kt-menu-item px-6 py-2 rounded-full">
                        <a class="kt-menu-link" href={route('company.technologies.index')} tabindex="0">
                            <span class="kt-menu-icon">
                                <i class="fa-solid fa-microchip text-sm"></i>
                            </span>
                            <span class="kt-menu-title grow-0">Technologies</span>
                        </a>
                    </div>
                </div>
            </div>

            <div class="kt-menu-item {$page.url.startsWith(new URL(route('company.products.index')).pathname) || $page.url.startsWith(new URL(route('company.product-demand.index')).pathname) || $page.url.startsWith(new URL(route('company.machines.index')).pathname) || $page.url.startsWith(new URL(route('company.production-orders.index')).pathname) ? 'active px-6 py-1 rounded-full navbar-active' : 'px-6 py-1 rounded-full'}" data-kt-menu-item-toggle="dropdown" data-kt-menu-item-trigger="hover">
                <div class="kt-menu-link lg:py-2 border-b border-b-transparent {$page.url.startsWith(new URL(route('company.products.index')).pathname) || $page.url.startsWith(new URL(route('company.product-demand.index')).pathname) || $page.url.startsWith(new URL(route('company.machines.index')).pathname) || $page.url.startsWith(new URL(route('company.production-orders.index')).pathname) ? 'text-black font-bold' : 'text-white hover:text-white'}">
                    <span class="kt-menu-title font-medium text-sm">Production</span>
                </div>
                <div class="kt-menu-dropdown kt-menu-default py-2.5 w-full max-w-[220px]">
                    <div class="kt-menu-item px-6 py-2 rounded-full">
                        <a class="kt-menu-link" href={route('company.products.index')} tabindex="0">
                            <span class="kt-menu-icon">
                                <i class="fa-solid fa-box text-sm"></i>
                            </span>
                            <span class="kt-menu-title grow-0">Products</span>
                        </a>
                    </div>
                    <div class="kt-menu-item px-6 py-2 rounded-full">
                        <a class="kt-menu-link" href={route('company.product-demand.index')} tabindex="0">
                            <span class="kt-menu-icon">
                                <i class="fa-solid fa-chart-simple text-sm"></i>
                            </span>
                            <span class="kt-menu-title grow-0">Product Demand</span>
                        </a>
                    </div>
                    <div class="kt-menu-item px-6 py-2 rounded-full">
                        <a class="kt-menu-link" href={route('company.machines.index')} tabindex="0">
                            <span class="kt-menu-icon">
                                <i class="fa-solid fa-gears text-sm"></i>
                            </span>
                            <span class="kt-menu-title grow-0">Machines</span>
                        </a>
                    </div>
                    <div class="kt-menu-item px-6 py-2 rounded-full">
                        <a class="kt-menu-link" href={route('company.production-orders.index')} tabindex="0">
                            <span class="kt-menu-icon">
                                <i class="fa-solid fa-list-check text-sm"></i>
                            </span>
                            <span class="kt-menu-title grow-0">Production Orders</span>
                        </a>
                    </div>
                </div>
            </div>
            
            <div class="kt-menu-item {$page.url.startsWith(new URL(route('company.inventory.index')).pathname) ? 'active px-6 py-1 rounded-full navbar-active' : 'px-6 py-1 rounded-full'}" data-kt-menu-item-toggle="dropdown" data-kt-menu-item-trigger="hover">
                <div class="kt-menu-link lg:py-2 border-b border-b-transparent {$page.url.startsWith(new URL(route('company.inventory.index')).pathname) ? 'text-black font-bold' : 'text-white hover:text-white'}">
                    <span class="kt-menu-title font-medium text-sm">Inventory</span>
                </div>
                <div class="kt-menu-dropdown kt-menu-default py-2.5 w-full max-w-[220px]">
                    <div class="kt-menu-item px-6 py-2 rounded-full">
                        <a class="kt-menu-link" href={route('company.inventory.index')} tabindex="0">
                            <span class="kt-menu-icon">
                                <i class="fa-solid fa-boxes-stacked text-sm"></i>
                            </span>
                            <span class="kt-menu-title grow-0">Inventory</span>
                        </a>
                    </div>
                </div>
            </div>

            <div class="kt-menu-item {$page.url.startsWith(new URL(route('company.suppliers.index')).pathname) || $page.url.startsWith(new URL(route('company.purchases.index')).pathname) ? 'active px-6 py-1 rounded-full navbar-active' : 'px-6 py-1 rounded-full'}" data-kt-menu-item-toggle="dropdown" data-kt-menu-item-trigger="hover">
                <div class="kt-menu-link lg:py-2 border-b border-b-transparent {$page.url.startsWith(new URL(route('company.suppliers.index')).pathname) || $page.url.startsWith(new URL(route('company.purchases.index')).pathname) ? 'text-black font-bold' : 'text-white hover:text-white'}">
                    <span class="kt-menu-title font-medium text-sm">Procurement</span>
                </div>
                <div class="kt-menu-dropdown kt-menu-default py-2.5 w-full max-w-[220px]">
                    <div class="kt-menu-item px-6 py-2 rounded-full">
                        <a class="kt-menu-link" href={route('company.suppliers.index')} tabindex="0">
                            <span class="kt-menu-icon">
                                <i class="fa-solid fa-truck text-sm"></i>
                            </span>
                            <span class="kt-menu-title grow-0">Suppliers</span>
                        </a>
                    </div>
                    <div class="kt-menu-item px-6 py-2 rounded-full">
                        <a class="kt-menu-link" href={route('company.purchases.index')} tabindex="0">
                            <span class="kt-menu-icon">
                                <i class="fa-solid fa-cart-shopping text-sm"></i>
                            </span>
                            <span class="kt-menu-title grow-0">Purchases</span>
                        </a>
                    </div>
                </div>
            </div>

            <div class="kt-menu-item {$page.url.startsWith(new URL(route('company.sales.index')).pathname) ? 'active px-6 py-1 rounded-full navbar-active' : 'px-6 py-1 rounded-full'}" data-kt-menu-item-toggle="dropdown" data-kt-menu-item-trigger="hover">
                <div class="kt-menu-link lg:py-2 border-b border-b-transparent {$page.url.startsWith(new URL(route('company.sales.index')).pathname) ? 'text-black font-bold' : 'text-white hover:text-white'}">
                    <span class="kt-menu-title font-medium text-sm">Sales</span>
                </div>
                <div class="kt-menu-dropdown kt-menu-default py-2.5 w-full max-w-[220px]">
                    <div class="kt-menu-item px-6 py-2 rounded-full">
                        <a class="kt-menu-link" href={route('company.sales.index')} tabindex="0">
                            <span class="kt-menu-icon">
                                <i class="fa-solid fa-cart-shopping text-sm"></i>
                            </span>
                            <span class="kt-menu-title grow-0">Sales</span>
                        </a>
                    </div>
                </div>
            </div>

            <div class="kt-menu-item {$page.url.startsWith(new URL(route('company.employees.index')).pathname) ? 'active px-6 py-1 rounded-full navbar-active' : 'px-6 py-1 rounded-full'}" data-kt-menu-item-toggle="dropdown" data-kt-menu-item-trigger="hover">
                <div class="kt-menu-link lg:py-2 border-b border-b-transparent {$page.url.startsWith(new URL(route('company.employees.index')).pathname) ? 'text-black font-bold' : 'text-white hover:text-white'}">
                    <span class="kt-menu-title font-medium text-sm">HR</span>
                </div>
                <div class="kt-menu-dropdown kt-menu-default py-2.5 w-full max-w-[220px]">
                    <div class="kt-menu-item px-6 py-2 rounded-full">
                        <a class="kt-menu-link" href={route('company.employees.index')} tabindex="0">
                            <span class="kt-menu-icon">
                                <i class="fa-solid fa-users text-sm"></i>
                            </span>
                            <span class="kt-menu-title grow-0">Employees</span>
                        </a>
                    </div>
                </div>
            </div>

            <div class="kt-menu-item {$page.url.startsWith(new URL(route('company.advertisers.index')).pathname) || $page.url.startsWith(new URL(route('company.ads.index')).pathname) ? 'active px-6 py-1 rounded-full navbar-active' : 'px-6 py-1 rounded-full'}" data-kt-menu-item-toggle="dropdown" data-kt-menu-item-trigger="hover">
                <div class="kt-menu-link lg:py-2 border-b border-b-transparent {$page.url.startsWith(new URL(route('company.advertisers.index')).pathname) || $page.url.startsWith(new URL(route('company.ads.index')).pathname) ? 'text-black font-bold' : 'text-white hover:text-white'}">
                    <span class="kt-menu-title font-medium text-sm">Marketing</span>
                </div>
                <div class="kt-menu-dropdown kt-menu-default py-2.5 w-full max-w-[220px]">
                    <div class="kt-menu-item px-6 py-2 rounded-full">
                        <a class="kt-menu-link" href={route('company.advertisers.index')} tabindex="0">
                            <span class="kt-menu-icon">
                                <i class="fa-solid fa-users text-sm"></i>
                            </span>
                            <span class="kt-menu-title grow-0">Advertisers</span>
                        </a>
                    </div>
                    <div class="kt-menu-item px-6 py-2 rounded-full">
                        <a class="kt-menu-link" href={route('company.ads.index')} tabindex="0">
                            <span class="kt-menu-icon">
                                <i class="fa-solid fa-bullhorn text-sm"></i>
                            </span>
                            <span class="kt-menu-title grow-0">Ad Campaigns</span>
                        </a>
                    </div>
                </div>
            </div>

            <div class="kt-menu-item {$page.url.startsWith(new URL(route('company.banks.index')).pathname) || $page.url.startsWith(new URL(route('company.loans.index')).pathname) || $page.url.startsWith(new URL(route('company.transactions.index')).pathname) ? 'active px-6 py-1 rounded-full navbar-active' : 'px-6 py-1 rounded-full'}" data-kt-menu-item-toggle="dropdown" data-kt-menu-item-trigger="hover">
                <div class="kt-menu-link lg:py-2 border-b border-b-transparent {$page.url.startsWith(new URL(route('company.banks.index')).pathname) || $page.url.startsWith(new URL(route('company.loans.index')).pathname) || $page.url.startsWith(new URL(route('company.transactions.index')).pathname) ? 'text-black font-bold' : 'text-white hover:text-white'}">
                    <span class="kt-menu-title font-medium text-sm">Finance</span>
                </div>
                <div class="kt-menu-dropdown kt-menu-default py-2.5 w-full max-w-[220px]">
                    <div class="kt-menu-item px-6 py-2 rounded-full">
                        <a class="kt-menu-link" href={route('company.banks.index')} tabindex="0">
                            <span class="kt-menu-icon">
                                <i class="fa-solid fa-building-columns text-sm"></i>
                            </span>
                            <span class="kt-menu-title grow-0">Banks</span>
                        </a>
                    </div>
                    <div class="kt-menu-item px-6 py-2 rounded-full">
                        <a class="kt-menu-link" href={route('company.loans.index')} tabindex="0">
                            <span class="kt-menu-icon">
                                <i class="fa-solid fa-money-bill-transfer text-sm"></i>
                            </span>
                            <span class="kt-menu-title grow-0">Loans</span>
                        </a>
                    </div>
                    <div class="kt-menu-item px-6 py-2 rounded-full">
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

<style>
    .navbar-active {
        background-color: #E2A673 !important;
        border: 2px solid #D4A574 !important;
        display: flex !important;
        align-items: center !important;
        justify-content: center !important;
        text-align: center !important;
    }
    
    .navbar-active .kt-menu-link {
        color: #000000 !important;
        font-weight: bold !important;
        display: flex !important;
        align-items: center !important;
        justify-content: center !important;
        width: 100% !important;
        text-align: center !important;
    }
    
    .navbar-active .kt-menu-title {
        color: #000000 !important;
        font-weight: bold !important;
        text-align: center !important;
        width: 100% !important;
        display: block !important;
    }
    
    /* More specific selectors to override any conflicts */
    #navbar .kt-menu-item.navbar-active {
        background-color: #E2A673 !important;
        border: 2px solid #D4A574 !important;
        display: flex !important;
        align-items: center !important;
        justify-content: center !important;
        text-align: center !important;
    }
    
    #navbar .kt-menu-item.navbar-active .kt-menu-link {
        color: #000000 !important;
        font-weight: bold !important;
        display: flex !important;
        align-items: center !important;
        justify-content: center !important;
        width: 100% !important;
        text-align: center !important;
    }
    
    #navbar .kt-menu-item.navbar-active .kt-menu-title {
        color: #000000 !important;
        font-weight: bold !important;
        text-align: center !important;
        width: 100% !important;
        display: block !important;
    }
    
    /* Additional centering for different menu item structures */
    #navbar .kt-menu-item.navbar-active a {
        display: flex !important;
        align-items: center !important;
        justify-content: center !important;
        width: 100% !important;
        text-align: center !important;
    }
    
    #navbar .kt-menu-item.navbar-active div {
        display: flex !important;
        align-items: center !important;
        justify-content: center !important;
        width: 100% !important;
        text-align: center !important;
    }
    
    /* Dropdown hover behavior - Fixed positioning */
    #navbar .kt-menu-item[data-kt-menu-item-toggle="dropdown"] {
        position: relative !important;
        padding-bottom: 4px !important; /* Create invisible bridge zone */
    }
    
    /* Make the menu link relative for positioning reference */
    #navbar .kt-menu-item[data-kt-menu-item-toggle="dropdown"] .kt-menu-link {
        position: relative !important;
    }
    
    /* Position dropdown directly below the menu link text, centered - Override all possible Metronic/Popper styles */
    #navbar .kt-menu-item[data-kt-menu-item-toggle="dropdown"] .kt-menu-dropdown,
    #navbar .kt-menu-item[data-kt-menu-item-toggle="dropdown"] .kt-menu-dropdown[data-popper-placement],
    #navbar .kt-menu-item[data-kt-menu-item-toggle="dropdown"] .kt-menu-dropdown.show,
    #navbar .kt-menu-item[data-kt-menu-item-toggle="dropdown"] .kt-menu-dropdown.show[data-popper-placement],
    #navbar .kt-menu-item[data-kt-menu-item-toggle="dropdown"] .kt-menu-link .kt-menu-dropdown {
        opacity: 0 !important;
        visibility: hidden !important;
        transform: translateX(-50%) translateY(-10px) !important;
        transition: opacity 0.2s ease-in-out, visibility 0.2s ease-in-out, transform 0.2s ease-in-out !important;
        position: absolute !important;
        top: calc(100% - 2px) !important;
        left: 50% !important;
        right: auto !important;
        bottom: auto !important;
        margin-left: 0 !important;
        margin-right: 0 !important;
        margin-top: 0 !important;
        margin-bottom: 0 !important;
        padding-top: 6px !important;
        inset: auto !important;
        z-index: 1000 !important;
        background: white !important;
        border-radius: 8px !important;
        box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06) !important;
        min-width: 200px !important;
        width: max-content !important;
        max-width: none !important;
        pointer-events: auto !important;
    }
    
    /* Force hide dropdowns when not hovered on parent or dropdown itself, even if parent is active */
    #navbar .kt-menu-item[data-kt-menu-item-toggle="dropdown"]:not(:hover) .kt-menu-dropdown:not(:hover) {
        opacity: 0 !important;
        visibility: hidden !important;
        display: none !important;
    }
    
    /* Show dropdown on hover - Override all states */
    #navbar .kt-menu-item[data-kt-menu-item-toggle="dropdown"]:hover .kt-menu-dropdown,
    #navbar .kt-menu-item[data-kt-menu-item-toggle="dropdown"]:hover .kt-menu-dropdown.show,
    #navbar .kt-menu-item[data-kt-menu-item-toggle="dropdown"]:hover .kt-menu-dropdown[data-popper-placement],
    #navbar .kt-menu-item[data-kt-menu-item-toggle="dropdown"]:hover .kt-menu-link .kt-menu-dropdown,
    #navbar .kt-menu-item[data-kt-menu-item-toggle="dropdown"] .kt-menu-dropdown:hover {
        opacity: 1 !important;
        visibility: visible !important;
        transform: translateX(-50%) translateY(0) !important;
        display: flex !important;
    }
    
    /* Force consistent styling for all dropdown items, regardless of parent active state */
    #navbar .kt-menu-item[data-kt-menu-item-toggle="dropdown"] .kt-menu-dropdown .kt-menu-item .kt-menu-link {
        color: #666666 !important;
        font-weight: 400 !important;
        text-decoration: none !important;
    }
    
    #navbar .kt-menu-item[data-kt-menu-item-toggle="dropdown"] .kt-menu-dropdown .kt-menu-item .kt-menu-link:hover {
        color: #666666 !important;
        font-weight: 400 !important;
    }
    
    #navbar .kt-menu-item[data-kt-menu-item-toggle="dropdown"] .kt-menu-dropdown .kt-menu-item .kt-menu-title {
        color: #666666 !important;
        font-weight: 400 !important;
    }
    
    #navbar .kt-menu-item[data-kt-menu-item-toggle="dropdown"] .kt-menu-dropdown .kt-menu-item .kt-menu-icon {
        color: #666666 !important;
    }
    
    #navbar .kt-menu-item[data-kt-menu-item-toggle="dropdown"] .kt-menu-dropdown .kt-menu-item .kt-menu-link:hover .kt-menu-title,
    #navbar .kt-menu-item[data-kt-menu-item-toggle="dropdown"] .kt-menu-dropdown .kt-menu-item .kt-menu-link:hover .kt-menu-icon {
        color: #666666 !important;
        font-weight: 400 !important;
    }
    
    /* Ensure navbar-active styles don't affect dropdown items */
    #navbar .kt-menu-item.navbar-active[data-kt-menu-item-toggle="dropdown"] .kt-menu-dropdown .kt-menu-item .kt-menu-link,
    #navbar .kt-menu-item.navbar-active[data-kt-menu-item-toggle="dropdown"] .kt-menu-dropdown .kt-menu-item .kt-menu-title,
    #navbar .kt-menu-item.navbar-active[data-kt-menu-item-toggle="dropdown"] .kt-menu-dropdown .kt-menu-item .kt-menu-icon {
        color: #666666 !important;
        font-weight: 400 !important;
    }
</style>