<script>
    export let leaderboard = [];
    export let loading = false;

    // Format currency
    function formatCurrency(amount) {
        return new Intl.NumberFormat('en-US', {
            style: 'currency',
            currency: 'DZD',
            minimumFractionDigits: 0,
            maximumFractionDigits: 0
        }).format(amount || 0);
    }

    // Format number
    function formatNumber(number) {
        return new Intl.NumberFormat('en-US').format(number || 0);
    }

    // Format score (4 decimal places)
    function formatScore(score) {
        return parseFloat(score || 0).toFixed(4);
    }

    // Get rank badge class
    function getRankBadgeClass(rank) {
        switch (rank) {
            case 1:
                return 'kt-badge-light-warning'; // Gold
            case 2:
                return 'kt-badge-light-secondary'; // Silver
            case 3:
                return 'kt-badge-light-primary'; // Bronze
            default:
                return 'kt-badge-light-info';
        }
    }

    // Get rank icon
    function getRankIcon(rank) {
        switch (rank) {
            case 1:
                return 'ki-crown-2';
            case 2:
                return 'ki-medal-star';
            case 3:
                return 'ki-award';
            default:
                return 'ki-ranking';
        }
    }
</script>

<div class="kt-card">
    <div class="kt-card-header">
        <h3 class="kt-card-title">
            <i class="ki-filled ki-trophy text-warning me-2"></i>
            Company Leaderboard
        </h3>
        <div class="kt-card-subtitle">
            Ranked by performance score (funds - loans - carbon + research)
        </div>
    </div>
    
    <div class="kt-card-content p-0">
        <div class="kt-scrollable-x-auto">
            <table class="kt-table kt-table-border table-fixed">
                <thead>
                    <tr>
                        <th style="width: 80px;">
                            <span class="kt-table-col">
                                <span class="kt-table-col-label">Rank</span>
                            </span>
                        </th>
                        <th>
                            <span class="kt-table-col">
                                <span class="kt-table-col-label">Company</span>
                            </span>
                        </th>
                        <th>
                            <span class="kt-table-col">
                                <span class="kt-table-col-label">Score</span>
                            </span>
                        </th>
                        <th>
                            <span class="kt-table-col">
                                <span class="kt-table-col-label">Funds</span>
                            </span>
                        </th>
                        <th>
                            <span class="kt-table-col">
                                <span class="kt-table-col-label">Loans</span>
                            </span>
                        </th>
                        <th>
                            <span class="kt-table-col">
                                <span class="kt-table-col-label">Carbon</span>
                            </span>
                        </th>
                        <th>
                            <span class="kt-table-col">
                                <span class="kt-table-col-label">Research</span>
                            </span>
                        </th>
                        <th>
                            <span class="kt-table-col">
                                <span class="kt-table-col-label">Technologies</span>
                            </span>
                        </th>
                    </tr>
                </thead>
                <tbody>
                    {#if loading}
                        <!-- Loading skeleton rows -->
                        {#each Array(10) as _, i}
                            <tr>
                                {#each Array(8) as _, j}
                                    <td class="p-4">
                                        <div class="kt-skeleton w-16 h-4 rounded"></div>
                                    </td>
                                {/each}
                            </tr>
                        {/each}
                    {:else if leaderboard.length === 0}
                        <!-- Empty state -->
                        <tr>
                            <td colspan="8" class="p-10">
                                <div class="flex flex-col items-center justify-center text-center">
                                    <div class="mb-4">
                                        <i class="ki-filled ki-information-2 text-4xl text-muted-foreground"></i>
                                    </div>
                                    <h3 class="text-lg font-semibold text-mono mb-2">No companies found</h3>
                                    <p class="text-sm text-secondary-foreground mb-4">
                                        Companies will appear here once they start playing the game.
                                    </p>
                                </div>
                            </td>
                        </tr>
                    {:else}
                        <!-- Actual data rows -->
                        {#each leaderboard as company}
                            <tr class="hover:bg-muted/50">
                                <!-- Rank -->
                                <td>
                                    <div class="flex items-center gap-2">
                                        <div class="kt-badge {getRankBadgeClass(company.rank)} kt-badge-circle size-8">
                                            <i class="ki-filled {getRankIcon(company.rank)} text-sm"></i>
                                        </div>
                                        <span class="text-sm font-bold text-mono">#{company.rank}</span>
                                    </div>
                                </td>

                                <!-- Company -->
                                <td>
                                    <div class="flex flex-col gap-1">
                                        <span class="text-sm font-medium text-mono">
                                            {company.name}
                                        </span>
                                        <span class="text-xs text-secondary-foreground">
                                            {company.user_name}
                                        </span>
                                    </div>
                                </td>

                                <!-- Score -->
                                <td>
                                    <div class="flex flex-col gap-1">
                                        <span class="text-sm font-bold text-mono {company.final_score >= 0 ? 'text-success' : 'text-danger'}">
                                            {formatScore(company.final_score)}
                                        </span>
                                        <div class="flex gap-1">
                                            <div class="kt-badge kt-badge-sm kt-badge-light-primary" title="Normalized Funds">
                                                F: {formatScore(company.normalized_funds)}
                                            </div>
                                        </div>
                                    </div>
                                </td>

                                <!-- Funds -->
                                <td>
                                    <span class="text-sm font-medium text-success">
                                        {formatCurrency(company.funds)}
                                    </span>
                                </td>

                                <!-- Loans -->
                                <td>
                                    <span class="text-sm font-medium text-danger">
                                        {formatCurrency(company.unpaid_loans)}
                                    </span>
                                </td>

                                <!-- Carbon -->
                                <td>
                                    <div class="flex flex-col gap-1">
                                        <span class="text-sm font-medium text-warning">
                                            {formatNumber(company.carbon_footprint)}
                                        </span>
                                        <span class="text-xs text-secondary-foreground">
                                            tons CO₂
                                        </span>
                                    </div>
                                </td>

                                <!-- Research -->
                                <td>
                                    <div class="flex flex-col gap-1">
                                        <span class="text-sm font-medium text-info">
                                            {formatScore(company.research_level)}
                                        </span>
                                        <span class="text-xs text-secondary-foreground">
                                            level
                                        </span>
                                    </div>
                                </td>

                                <!-- Technologies -->
                                <td>
                                    <div class="kt-badge kt-badge-light-success kt-badge-sm">
                                        <i class="ki-filled ki-flask text-xs me-1"></i>
                                        {company.completed_technologies}
                                    </div>
                                </td>
                            </tr>
                        {/each}
                    {/if}
                </tbody>
            </table>
        </div>
    </div>
</div>
