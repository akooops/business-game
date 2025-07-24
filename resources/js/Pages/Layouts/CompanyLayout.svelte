<script>
  import CompanyTopbar from '../Components/CompanyTopbar.svelte';
  import CompanyNavbar from '../Components/CompanyNavbar.svelte';

  import Breadcrumbs from '../Components/Breadcrumbs.svelte';
  
  // Props for the layout
  export let breadcrumbs = [];
  export let pageTitle = 'Dashboard';

  function isActiveRoute(pattern) {
    return window.location.href.includes(pattern);
  }

  // Show toast notification
  function showToast(message, type = 'info') {
    if (window.KTToast) {
        KTToast.show({
            icon: `<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-info-icon lucide-info"><circle cx="12" cy="12" r="10"/><path d="M12 16v-4"/><path d="M12 8h.01"/></svg>`,
            message: message,
            variant: type,
            position: 'bottom-end',
        });
    }
  }

  function formatTimestamp(timestamp) {
    return new Date(timestamp).toLocaleDateString('en-US', 
    {
      year: 'numeric',
      month: 'long',
      day: 'numeric',
    }) || 'N/A';
  }

  // Make functions globally available for child components
  window.isActiveRoute = isActiveRoute;
  window.formatTimestamp = formatTimestamp;
  window.showToast = showToast;
</script>

<div class="flex grow flex-col in-data-kt-[sticky-header=on]:pt-(--header-height)">
    <!-- Header -->
    <CompanyTopbar />
    <!-- End of Header -->

    <!-- Navbar -->
    <CompanyNavbar />
    <!-- End of Navbar -->

    <!-- Wrapper -->
    <div class="container-fixed w-full flex px-0">
        <!-- Main -->
          <main class="grow" role="content">
              <Breadcrumbs {breadcrumbs} {pageTitle} />

              <slot />
        </main>
        <!-- End of Main -->
    </div>
</div>