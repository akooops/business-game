<?php

namespace App\Http\Controllers\Admin;

use Inertia\Inertia;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use App\Http\Requests\Admin\Events\RunEventRequest;

class EventsController extends Controller
{
    public function index()
    {
        $events = [
            // Trade Events
            [
                'key' => 'allow-countries-import',
                'name' => 'Allow Countries Import',
                'description' => 'Allow Spain to import goods',
                'category' => 'Trade',
                'impact' => 'positive',
                'icon' => 'fa-solid fa-check'
            ],
            [
                'key' => 'block-countries-import',
                'name' => 'Block Countries Import',
                'description' => 'Block Spain from importing goods',
                'category' => 'Trade',
                'impact' => 'negative',
                'icon' => 'fa-solid fa-ban'
            ],
            
            // Logistics Events
            [
                'key' => 'open-suez-canal',
                'name' => 'Open Suez Canal',
                'description' => 'Reduce shipping costs by reopening the Suez Canal',
                'category' => 'Logistics',
                'impact' => 'positive',
                'icon' => 'fa-solid fa-ship'
            ],
            [
                'key' => 'close-suez-canal',
                'name' => 'Close Suez Canal',
                'description' => 'Increase shipping costs by closing the Suez Canal',
                'category' => 'Logistics',
                'impact' => 'negative',
                'icon' => 'fa-solid fa-times-circle'
            ],
            [
                'key' => 'open-ormuz-canal',
                'name' => 'Open Ormuz Canal',
                'description' => 'Reduce shipping costs by 15% from UAE and decrease raw material prices',
                'category' => 'Logistics',
                'impact' => 'positive',
                'icon' => 'fa-solid fa-ship'
            ],
            [
                'key' => 'close-ormuz-canal',
                'name' => 'Close Ormuz Canal',
                'description' => 'Increase shipping costs by 15% from UAE and increase raw material prices',
                'category' => 'Logistics',
                'impact' => 'negative',
                'icon' => 'fa-solid fa-times-circle'
            ],
            
            // Disaster/Environmental Events
            [
                'key' => 'start-heat-wave',
                'name' => 'Start Heat Wave',
                'description' => 'Heat wave damages 8% of cosmetic products, reduces employee efficiency, and increases supplier prices',
                'category' => 'Disaster',
                'impact' => 'negative',
                'icon' => 'fa-solid fa-sun'
            ],
            [
                'key' => 'stop-heat-wave',
                'name' => 'Stop Heat Wave',
                'description' => 'End the heat wave effects, restore employee efficiency and supplier prices',
                'category' => 'Disaster',
                'impact' => 'positive',
                'icon' => 'fa-solid fa-cloud'
            ],
            
            // Health/Public Safety Events
            [
                'key' => 'start-health-complaint',
                'name' => 'Start Health Complaint',
                'description' => 'Health complaints reduce product demand by 20% across all products',
                'category' => 'Health',
                'impact' => 'negative',
                'icon' => 'fa-solid fa-hospital'
            ],
            [
                'key' => 'stop-health-complaint',
                'name' => 'Stop Health Complaint',
                'description' => 'End health complaint crisis and restore normal product demand',
                'category' => 'Health',
                'impact' => 'positive',
                'icon' => 'fa-solid fa-heart-pulse'
            ],
            
            // Labor/Social Events
            [
                'key' => 'start-workers-protest',
                'name' => 'Start Workers Protest',
                'description' => 'Workers protest reduces worker efficiency by 15%',
                'category' => 'Labor',
                'impact' => 'negative',
                'icon' => 'fa-solid fa-person-walking-with-cane'
            ],
            [
                'key' => 'end-workers-protest',
                'name' => 'End Workers Protest',
                'description' => 'End workers protest and restore normal worker efficiency',
                'category' => 'Labor',
                'impact' => 'positive',
                'icon' => 'fa-solid fa-handshake'
            ]
        ];

        return Inertia::render('Admin/Events/Index', [
            'events' => $events
        ]);
    }

    public function run(RunEventRequest $request)
    {
        $validated = $request->validated();
        $eventKey = $validated['event'];

        try {
            // Run the artisan command for the selected event
            $exitCode = Artisan::call('game:' . $eventKey);
            
            if ($exitCode === 0) {
                return back()->with('success', 'Event executed successfully!');
            } else {
                return back()->with('error', 'Event execution failed.');
            }
        } catch (\Exception $e) {
            return back()->with('error', 'Error running event: ' . $e->getMessage());
        }
    }
}
