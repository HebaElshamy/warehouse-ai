<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\SalesDaily;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ReportsController extends Controller
{
    public function index(Request $request)
    {
        // الشهر الافتراضي: الشهر الحالي
        $month = $request->get('month', now()->format('Y-m'));
        $start = Carbon::createFromFormat('Y-m', $month)->startOfMonth();
        $end   = (clone $start)->endOfMonth();

        // إجمالي المبيعات (كمية + مبلغ) خلال الشهر
        $monthlyTotals = SalesDaily::whereBetween('sale_date', [$start->toDateString(), $end->toDateString()])
            ->selectRaw('COALESCE(SUM(sales_volume),0) as qty, COALESCE(SUM(sales_amount),0) as amount')
            ->first();

        // أكثر المنتجات مبيعًا (Top)
        $topSelling = SalesDaily::with('product')
            ->whereBetween('sale_date', [$start->toDateString(), $end->toDateString()])
            ->selectRaw('product_id, SUM(sales_volume) as qty, SUM(sales_amount) as amount')
            ->groupBy('product_id')
            ->orderByDesc('qty')
            ->limit(10)
            ->get();

        // أقل المنتجات حركة (Low moving) خلال الشهر: (مبيعات قليلة)
        $lowMoving = SalesDaily::with('product')
            ->whereBetween('sale_date', [$start->toDateString(), $end->toDateString()])
            ->selectRaw('product_id, SUM(sales_volume) as qty, SUM(sales_amount) as amount')
            ->groupBy('product_id')
            ->orderBy('qty')
            ->limit(10)
            ->get();

        // Trend: مبيعات يومية للشهر (للرسم البياني)
        $dailyTrend = SalesDaily::whereBetween('sale_date', [$start->toDateString(), $end->toDateString()])
            ->selectRaw('sale_date, SUM(sales_volume) as qty, SUM(sales_amount) as amount')
            ->groupBy('sale_date')
            ->orderBy('sale_date')
            ->get()
            ->map(fn($r) => [
                'date' => (string)$r->sale_date,
                'qty' => (int)$r->qty,
                'amount' => (float)$r->amount,
            ]);

        return view('dashboard.reports.index', [
            'month' => $month,
            'start' => $start,
            'end' => $end,
            'monthlyTotals' => $monthlyTotals,
            'topSelling' => $topSelling,
            'lowMoving' => $lowMoving,
            'dailyTrend' => $dailyTrend,
        ]);
    }

    public function monthlyPdf(Request $request)
    {
        $month = $request->get('month', now()->format('Y-m'));
        $start = Carbon::createFromFormat('Y-m', $month)->startOfMonth();
        $end   = (clone $start)->endOfMonth();

        $monthlyTotals = SalesDaily::whereBetween('sale_date', [$start->toDateString(), $end->toDateString()])
            ->selectRaw('COALESCE(SUM(sales_volume),0) as qty, COALESCE(SUM(sales_amount),0) as amount')
            ->first();

        $topSelling = SalesDaily::with('product')
            ->whereBetween('sale_date', [$start->toDateString(), $end->toDateString()])
            ->selectRaw('product_id, SUM(sales_volume) as qty, SUM(sales_amount) as amount')
            ->groupBy('product_id')
            ->orderByDesc('qty')
            ->limit(15)
            ->get();

        $lowMoving = SalesDaily::with('product')
            ->whereBetween('sale_date', [$start->toDateString(), $end->toDateString()])
            ->selectRaw('product_id, SUM(sales_volume) as qty, SUM(sales_amount) as amount')
            ->groupBy('product_id')
            ->orderBy('qty')
            ->limit(15)
            ->get();

        // ✅ PDF (باستخدام dompdf)
        $data = compact('month', 'start', 'end', 'monthlyTotals', 'topSelling', 'lowMoving');

        // لو dompdf مش متثبت هتوقفي هنا وتثبتيه (الخطوة 4)
        $pdf = \PDF::loadView('dashboard.reports.monthly_pdf', $data);

        return $pdf->download("monthly-report-{$month}.pdf");
    }
}
