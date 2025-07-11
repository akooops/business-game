<script>
  import { page } from '@inertiajs/svelte';
  import Sidebar from '../Components/Sidebar.svelte';
  import Topbar from '../Components/Topbar.svelte';
  import Breadcrumbs from '../Components/Breadcrumbs.svelte';
  
  // Props for the layout
  export let breadcrumbs = [];
  export let pageTitle = 'Dashboard';

  // Global utility functions for admin components
  function hasPermission(permission) {
    // Check if permissions are globally disabled for debugging
    if ($page.props.app?.permissions_enabled === false) {
      return true; // Allow all permissions when disabled
    }
    
    if (!$page.props.auth.permissions) return false;
    return $page.props.auth.permissions.some(p => p === permission);
  }

  function isActiveRoute(routePattern) {
    return $page.url.startsWith(route(routePattern));
  }

  function formatTimestamp(timestamp) {
    return new Date(timestamp).toLocaleDateString('en-US', 
    {
      year: 'numeric',
      month: 'long',
      day: 'numeric',
      hour: '2-digit',
      minute: '2-digit'
    }) || 'N/A';
  }

  // Make functions globally available for child components
  window.hasPermission = hasPermission;
  window.isActiveRoute = isActiveRoute;
  window.formatTimestamp = formatTimestamp;
</script>

<div class="flex grow">
    <!-- Header -->
    <Topbar />
    <!-- End of Header -->

    <!-- Wrapper -->
    <div class="flex flex-col lg:flex-row grow pt-(--header-height) lg:pt-0">
        <!-- Sidebar -->
        <Sidebar />
        <!-- End of Sidebar -->

        <!-- Main -->
        <div class="flex grow rounded-xl bg-background border border-input lg:ms-(--sidebar-width) mt-0 lg:m-5 m-5 pb-10" style="height: 100vh;">
            <div class="flex flex-col grow kt-scrollable-y-auto lg:[--kt-scrollbar-width:auto] pt-5" id="scrollable_content">
                <div class="grow" role="content">
                    <Breadcrumbs {breadcrumbs} {pageTitle} />

                    <slot />
                </div>
            </div>
        </div>
        <!-- End of Main -->
    </div>
</div>