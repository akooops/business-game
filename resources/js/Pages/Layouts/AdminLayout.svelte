<script>
  import { page } from '@inertiajs/svelte';
  import Topbar from '../Components/Topbar.svelte';
  import Breadcrumbs from '../Components/Breadcrumbs.svelte';
  import AdminSidebar from '../Components/AdminSidebar.svelte';
  
  // Props for the layout
  export let breadcrumbs = [];
  export let pageTitle = 'Dashboard';

  function isActiveRoute(pattern) {
    return window.location.href.includes(pattern);
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
        <AdminSidebar />
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