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

    // Format timestamp (original function kept for compatibility)
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

    // Format date as DD/MM/YYYY for display
    function formatDate(timestamp) {
        if (!timestamp) return '--/--/----';
        const date = new Date(timestamp);
        const day = String(date.getDate()).padStart(2, '0');
        const month = String(date.getMonth() + 1).padStart(2, '0');
        const year = date.getFullYear();
        return `${day}/${month}/${year}`;
    }
</script>

<!-- Date and Week Display (elliptical badge only) -->
{#if formatDate(currentTimestamp) !== '--/--/----'}
    <div class="bg-primary text-white text-[10px] font-semibold px-3 h-9 rounded-full whitespace-nowrap flex items-center justify-center gap-1.5" title="Date: {formatDate(currentTimestamp)} | Week: {currentGameWeek || '--'}">
        <span>{formatDate(currentTimestamp)}</span>
        <span class="text-[9px] opacity-90">W{currentGameWeek || '--'}</span>
    </div>
{/if}