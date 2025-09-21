<script>
    export let companies = [];
    export let loading = false;
</script>

<div class="kt-card">
    <div class="kt-card-header">
        <h3 class="kt-card-title">
            <i class="ki-filled ki-trophy text-warning me-2"></i>
            Company Leaderboard
        </h3>
        <div class="kt-card-subtitle">
            Ranked by performance score: Net Worth (50%) - Carbon Impact (25%) + Research Level (25%)
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
                    </tr>
                </thead>
                <tbody>
                    {#if loading}
                        <!-- Loading skeleton rows -->
                        {#each Array(10) as _, i}
                            <tr>
                                {#each Array(9) as _, j}
                                    <td class="p-4">
                                        <div class="kt-skeleton w-16 h-4 rounded"></div>
                                    </td>
                                {/each}
                            </tr>
                        {/each}
                    {:else if companies.length === 0}
                        <!-- Empty state -->
                        <tr>
                            <td colspan="9" class="p-10">
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
                        {#each companies as company, index}
                            <tr class="hover:bg-muted/50">
                                <!-- Rank -->
                                <td>
                                    <div class="flex items-center gap-2">
                                        <span class="text-sm font-bold text-mono">#{index + 1}</span>
                                    </div>
                                </td>

                                <!-- Company -->
                                <td>
                                    <div class="flex items-center gap-3">
                                        <div>
                                            <img 
                                                src={company.user.avatarUrl} 
                                                alt={company.user.fullname}
                                                class="w-10 h-10 rounded-lg object-cover"
                                            />
                                        </div>
                                        <div class="flex flex-col gap-1">
                                            <span class="text-sm font-medium text-mono hover:text-primary">
                                                @{company.user.username}
                                            </span>
                                            <span class="text-xs text-secondary-foreground">
                                                {company.user.email}
                                            </span>
                                        </div>
                                    </div>
                                </td>

                                <!-- Score -->
                                <td>
                                    <div class="flex flex-col gap-1">
                                        <span class="text-sm font-bold text-mono {company.score >= 0 ? 'text-success' : 'text-danger'}">
                                            {company.score}
                                        </span>
                                    </div>
                                </td>

                                <!-- Funds -->
                                <td>
                                    <span class="text-sm font-medium text-success">
                                        {(company.funds || 0)} DZD
                                    </span>
                                </td>

                                <!-- Loans -->
                                <td>
                                    <span class="text-sm font-medium text-danger">
                                        {(company.unpaid_loans || 0)} DZD
                                    </span>
                                </td>

                                <!-- Carbon -->
                                <td>
                                    <div class="flex flex-col gap-1">
                                        <span class="text-sm font-medium text-warning">
                                            {(company.carbon_footprint || 0)}
                                        </span>
                                        <span class="text-xs text-secondary-foreground">
                                            Kg CO₂
                                        </span>
                                    </div>
                                </td>

                                <!-- Research -->
                                <td>
                                    <div class="flex flex-col gap-1">
                                        <span class="text-sm font-medium text-info">
                                            {(company.research_level || 0)}
                                        </span>
                                        <span class="text-xs text-secondary-foreground">
                                            level
                                        </span>
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
