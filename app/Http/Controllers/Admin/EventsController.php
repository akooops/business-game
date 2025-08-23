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
            [
                'key' => 'allow-countries-import',
                'name' => 'Allow Countries Import',
                'description' => 'Allow China, Morocco, and Tunisia to import goods',
                'category' => 'Trade',
                'impact' => 'positive',
                'icon' => 'fa-solid fa-check'
            ],
            [
                'key' => 'block-countries-import',
                'name' => 'Block Countries Import',
                'description' => 'Block China, Morocco, and Tunisia from importing goods',
                'category' => 'Trade',
                'impact' => 'negative',
                'icon' => 'fa-solid fa-ban'
            ],
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
                'key' => 'raise-oil-price',
                'name' => 'Raise Oil Price',
                'description' => 'Increase shipping costs by 10% due to higher oil prices',
                'category' => 'Economics',
                'impact' => 'negative',
                'icon' => 'fa-solid fa-arrow-up'
            ],
            [
                'key' => 'lower-oil-price',
                'name' => 'Lower Oil Price',
                'description' => 'Decrease shipping costs by 10% due to lower oil prices',
                'category' => 'Economics',
                'impact' => 'positive',
                'icon' => 'fa-solid fa-arrow-down'
            ],
            [
                'key' => 'raise-customs-duties-rate',
                'name' => 'Raise Customs Duties',
                'description' => 'Increase customs duties rates by 5%',
                'category' => 'Trade',
                'impact' => 'negative',
                'icon' => 'fa-solid fa-arrow-trend-up'
            ],
            [
                'key' => 'lower-customs-duties-rate',
                'name' => 'Lower Customs Duties',
                'description' => 'Decrease customs duties rates by 5%',
                'category' => 'Trade',
                'impact' => 'positive',
                'icon' => 'fa-solid fa-arrow-trend-down'
            ],
            [
                'key' => 'damage-inventory-product',
                'name' => 'Damage Inventory',
                'description' => 'Natural disaster damages 10% of all company inventories',
                'category' => 'Disaster',
                'impact' => 'negative',
                'icon' => 'fa-solid fa-triangle-exclamation'
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
