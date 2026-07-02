<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreSavedReportRequest;
use App\Http\Requests\Admin\UpdateSavedReportRequest;
use App\Models\Meal;
use App\Models\Order;
use App\Models\SavedReport;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class SavedReportController extends Controller
{
    public function index(): View
    {
        $reports = SavedReport::query()->latest()->paginate(10);

        $totalSales = (float) Order::query()->sum('total');
        $totalOrders = Order::query()->count();
        $totalCustomers = User::query()->count();

        return view('admin.reports', [
            'reports' => $reports,
            'reportStats' => [
                'totalSales' => $totalSales,
                'totalOrders' => $totalOrders,
                'customers' => $totalCustomers,
                'averageOrder' => $totalOrders > 0 ? round($totalSales / $totalOrders, 2) : 0,
            ],
            'statusMix' => [
                'delivered' => Order::query()->where('status', 'delivered')->count(),
                'pending' => Order::query()->where('status', 'pending')->count(),
                'preparing' => Order::query()->where('status', 'preparing')->count(),
                'cancelled' => Order::query()->where('status', 'cancelled')->count(),
            ],
            'topMeals' => Meal::query()
                ->withCount('orderItems')
                ->withSum('orderItems', 'total')
                ->orderByDesc('order_items_sum_total')
                ->take(4)
                ->get(),
        ]);
    }

    public function create(): RedirectResponse
    {
        return redirect()->route('admin.reports');
    }

    public function store(StoreSavedReportRequest $request): RedirectResponse
    {
        SavedReport::query()->create($this->reportPayload($request->validated()));

        return redirect()
            ->route('admin.reports')
            ->with('status', 'Report created successfully.');
    }

    public function show(SavedReport $report): RedirectResponse
    {
        return redirect()->route('admin.reports');
    }

    public function edit(SavedReport $report): RedirectResponse
    {
        return redirect()->route('admin.reports');
    }

    public function update(UpdateSavedReportRequest $request, SavedReport $report): RedirectResponse
    {
        $report->update($this->reportPayload($request->validated()));

        return redirect()
            ->route('admin.reports')
            ->with('status', 'Report updated successfully.');
    }

    public function destroy(SavedReport $report): RedirectResponse
    {
        $report->delete();

        return redirect()
            ->route('admin.reports')
            ->with('status', 'Report deleted successfully.');
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    private function reportPayload(array $data): array
    {
        $sections = collect(explode(',', (string) ($data['included_sections'] ?? '')))
            ->map(fn (string $section): string => trim($section))
            ->filter()
            ->values()
            ->all();

        return [
            'name' => $data['name'],
            'metric' => $data['metric'],
            'date_range' => $data['date_range'],
            'export_format' => $data['export_format'],
            'included_sections' => $sections,
            'status' => $data['status'],
            'generated_at' => now(),
        ];
    }
}
