<script>
    import { page } from '@inertiajs/svelte'
    import Timer from './Timer.svelte';
    import Notifications from './Notifications.svelte';

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

<!-- Header -->
<header class="header flex items-center transition-[height] shrink-0 bg-background h-(--header-height)" data-kt-sticky="true" data-kt-sticky-class="transition-[height] fixed z-10 top-0 left-0 right-0 shadow-xs backdrop-blur-md bg-background/70 border border-border" data-kt-sticky-name="header" data-kt-sticky-offset="100px" id="header">
  <!-- Container -->
  <div class="kt-container-fixed flex lg:justify-between items-center gap-2.5">
      <!-- Logo -->
      <div class="flex items-center gap-1 lg:w-[400px] grow lg:grow-0">
          <button class="kt-btn kt-btn-icon kt-btn-ghost -ms-2.5 lg:hidden" data-kt-drawer-toggle="#navbar">
          <i class="ki-filled ki-menu">
          </i>
          </button>
          <div class="flex items-center gap-2">
              <a class="flex items-center shrink-0" href="{route('company.dashboard.index')}">
                <img class="dark:hidden w-8 shrink-0" src="/assets/images/logo.png" style="width: 50px; height: 50px;"/>
                <img class="hidden dark:inline-block w-8 shrink-0" src="/assets/images/logo.png" style="width: 50px; height: 50px;"/>
              </a>
          </div>
      </div>
      <!-- End of Logo -->

      <!-- Topbar -->
      <div class="flex items-center gap-2 lg:gap-3.5 lg:w-[400px] justify-end">
          <div class="flex items-center gap-2 me-0.5">
                <!-- Timer -->
                <Timer />
                <!-- End of Timer -->

              <!-- Notifications -->
              <Notifications />
              <!-- End of Notifications -->

              <!-- End of Notifications -->
              <!-- User -->
              <div data-kt-dropdown="true" data-kt-dropdown-offset="10px, 10px" data-kt-dropdown-offset-rtl="-20px, 10px" data-kt-dropdown-placement="bottom-end" data-kt-dropdown-placement-rtl="bottom-start" data-kt-dropdown-trigger="click">
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
                        <a class="kt-btn kt-btn-outline justify-center w-full" on:click={handleLogout}>
                            Log out
                        </a>
                    </div>
                </div>
              </div>
              <!-- End of User -->
          </div>
          <!-- End of Topbar -->
      </div>
  </div>
  <!-- End of Container -->
</header>
<!-- End of Header -->