<?php

namespace App\Services\Admin;

use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Database\Eloquent\Builder;
use Symfony\Component\HttpFoundation\Response;

class OrderExportService
{
    /**
     * @param  Builder<\App\Models\Order>  $query
     */
    public function pdf(Builder $query): Response
    {
        $orders = (clone $query)
            ->with('user:id,full_name,phone')
            ->limit(500)
            ->get();

        return Pdf::loadView('admin.orders-export-pdf', [
            'orders' => $orders,
            'generatedAt' => now(),
        ])->setPaper('a4', 'landscape')->download('foodify-orders.pdf');
    }
}
