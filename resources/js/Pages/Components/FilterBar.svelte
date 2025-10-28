<script>
    export let searchTerm = '';
    export let placeholder = 'Search...';
    export let filters = [];
    
    // Emit events
    function handleSearch(e) {
        searchTerm = e.target.value;
    }
    
    function handleFilterChange(filterKey, value) {
        // Dispatch custom event
        const event = new CustomEvent('filterchange', {
            detail: { filterKey, value }
        });
        window.dispatchEvent(event);
    }
</script>

<div class="flex flex-col sm:flex-row gap-4 mb-6">
    <!-- Search Bar -->
    <div class="flex-1">
        <input
            type="text"
            class="kt-input w-full"
            placeholder={placeholder}
            bind:value={searchTerm}
            on:input={handleSearch}
        />
    </div>
    
    <!-- Filter Buttons -->
    {#if filters.length > 0}
        <div class="flex gap-2 flex-wrap">
            {#each filters as filter}
                <button
                    class="kt-btn kt-btn-outline {filter.active ? 'kt-btn-primary' : ''}"
                    on:click={() => handleFilterChange(filter.key, !filter.active)}
                >
                    {filter.label}
                </button>
            {/each}
        </div>
    {/if}
    
    <!-- Sort Dropdown -->
    <select class="kt-input" id="sort-select">
        <option value="">Sort by...</option>
        <option value="name_asc">Name (A-Z)</option>
        <option value="name_desc">Name (Z-A)</option>
        <option value="price_asc">Price (Low to High)</option>
        <option value="price_desc">Price (High to Low)</option>
    </select>
</div>

