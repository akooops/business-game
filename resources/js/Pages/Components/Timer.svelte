<script>
    import { onMount, onDestroy } from 'svelte';

    let currentTimestamp = '';
    let currentGameWeek = '';
    let fetchInterval;

    // Fetch current timestamp from API
    async function fetchCurrentTimestamp() {
        try {            
            const response = await fetch(route('utilities.index'), {
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                }
            });
            const data = await response.json();
            
            if (data.status === 'success') {
                currentTimestamp = data.timestamp;
                currentGameWeek = data.currentGameWeek;
            }
        } catch (error) {
            console.error('Error fetching current timestamp:', error);
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

    // Toggle drawer
    function toggleDrawer() {
        // This function is handled by the data-kt-drawer-toggle attribute
        // No additional logic needed
    }

    // Format timestamp
    function formatTimestamp(timestamp) {
        if (!timestamp) return 'Loading...';
        return new Date(timestamp).toLocaleDateString('en-US', {
            year: 'numeric',
            month: 'long',
            day: 'numeric',
            hour: '2-digit',
            minute: '2-digit'
        });
    }
</script>

<!-- Timer -->
<button 
    class="kt-btn kt-btn-primary kt-btn-icon size-9 rounded-full relative" 
    data-kt-drawer-toggle="#timer_drawer"
    on:click={toggleDrawer}
>
    <i class="fa-regular fa-clock text-lg"></i>
    
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
        <i class="fa-regular fa-clock text-primary text-sm"></i>
        <div class="flex flex-col">
            <span class="text-xs text-muted-foreground font-medium">Game Time</span>
            <span class="text-sm font-semibold text-foreground">
                {formatTimestamp(currentTimestamp)}
            </span>
        </div>
    </div>

    <!-- Game Week Display -->
    <div class="flex items-center gap-2 px-3 py-2 bg-background border border-border rounded-lg">
        <i class="fa-regular fa-calendar-days text-primary text-sm"></i>
        <div class="flex flex-col">
            <span class="text-xs text-muted-foreground font-medium">Game Week</span>
            <span class="text-sm font-semibold text-foreground">
                {currentGameWeek}
            </span>
        </div>
    </div>
</div>