<script>
    import { onMount, onDestroy } from 'svelte';

    let currentTimestamp = '';
    let loading = false;
    let fetchInterval;

    // Fetch current timestamp from API
    async function fetchCurrentTimestamp() {
        try {
            loading = true;
            
            const response = await fetch(route('utilities.current-timestamp'), {
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                }
            });
            const data = await response.json();
            
            if (data.status === 'success') {
                currentTimestamp = data.timestamp;
            }
        } catch (error) {
            console.error('Error fetching current timestamp:', error);
        } finally {
            loading = false;
        }
    }

    // Toggle drawer
    function toggleDrawer() {
        drawerOpen = !drawerOpen;
        if (drawerOpen) {
            fetchCurrentTimestamp();
        }
    }

    // Format timestamp for display
    function formatTimestamp(timestamp) {
        if (!timestamp) return '';
        
        try {
            const date = new Date(timestamp);
            return date.toLocaleString('en-US', {
                year: 'numeric',
                month: 'short',
                day: 'numeric',
                hour: '2-digit',
                minute: '2-digit',
                hour12: false
            });
        } catch (error) {
            return timestamp;
        }
    }

    onMount(() => {
        fetchCurrentTimestamp();
        
        // Fetch timestamp every minute (60 seconds)
        fetchInterval = setInterval(fetchCurrentTimestamp, 60000);
    });

    // Cleanup interval on component destroy
    onDestroy(() => {
        if (fetchInterval) {
            clearInterval(fetchInterval);
        }
    });
</script>

<!-- Timer -->
<button 
    class="kt-btn kt-btn-ghost kt-btn-icon size-8 hover:bg-background hover:[&_i]:text-primary relative" 
    data-kt-drawer-toggle="#timer_drawer"
    on:click={toggleDrawer}
>
    <i class="ki-filled ki-time text-lg"></i>
    
    <!-- Unread count badge -->

</button>

<!-- Notifications Drawer -->
<div 
    class="hidden kt-drawer kt-drawer-end card flex-col max-w-[90%] w-[450px] top-5 bottom-5 end-5 rounded-xl border border-border" 
    data-kt-drawer="true" 
    data-kt-drawer-container="body" 
    id="timer_drawer"
>
    <div class="flex items-center justify-between gap-2.5 text-sm text-mono font-semibold px-5 py-2.5 border-b border-b-border" id="timer_header">
        Timer
        <button class="kt-btn kt-btn-sm kt-btn-icon kt-btn-dim shrink-0" data-kt-drawer-dismiss="true">
            <i class="ki-filled ki-cross"></i>
        </button>
    </div>

    <!-- Game Timer Display -->
    <div class="flex items-center gap-2 px-3 py-2 bg-background border border-border rounded-lg">
        <i class="ki-filled ki-time text-primary text-sm"></i>
        <div class="flex flex-col">
            <span class="text-xs text-muted-foreground font-medium">Game Time</span>
            <span class="text-sm font-semibold text-foreground">
                {#if loading}
                    <div class="animate-pulse">Loading...</div>
                {:else}
                    {formatTimestamp(currentTimestamp)}
                {/if}
            </span>
        </div>
    </div>
</div>