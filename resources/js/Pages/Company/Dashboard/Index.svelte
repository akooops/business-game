<script>
    import CompanyLayout from '../../Layouts/CompanyLayout.svelte';
    
    // Define breadcrumbs for this page
    const breadcrumbs = [
        {
            title: 'Dashboard',
            url: '/dashboard',
            active: false
        },
        {
            title: 'Index',
            url: '/pages/index',
            active: true
        }
    ];
    
    const pageTitle = 'Dashboard';
</script>

<svelte:head>
    <title>Business Game - {pageTitle}</title>
</svelte:head>

<CompanyLayout {breadcrumbs} {pageTitle}>
    <!-- Container -->
    <div class="kt-container-fluid">
        <div class="grid gap-5 lg:gap-7.5">

        </div>
    </div>
    <!-- End of Container -->
</CompanyLayout>