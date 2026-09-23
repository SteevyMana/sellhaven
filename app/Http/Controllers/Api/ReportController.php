<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Models\Order;
use App\Models\Product;
use App\Models\Purchase;
use App\Models\Sale;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ReportController extends Controller
{
    private const LOW_STOCK_THRESHOLD = 10;

    public function summary(Request $request)
    {
        $range = $request->query('range', 'Last 30 days');
        $from = $this->rangeToDate($range);

        return [
            'kpis' => $this->kpis(),
            'monthlySales' => $this->monthlySeries($from),
            'topProducts' => $this->topProducts($from),
            'salesByMethod' => $this->salesByMethod($from),
            'lowStockProducts' => $this->lowStock(),
            'recentTransactions' => $this->recentTransactions(),
        ];
    }

    private function rangeToDate(string $range)
    {
        return match ($range) {
            'Last 7 days' => now()->subDays(7),
            'Last 30 days' => now()->subDays(30),
            'Last 6 months' => now()->subMonths(6),
            'This year' => now()->startOfYear(),
            default => now()->subDays(30),
        };
    }

    // KPIs de arriba — no dependen del rango, son un vistazo general del negocio.
    private function kpis(): array
    {
        return [
            'revenue' => (float) Sale::where('status', 'Completed')->sum('total')
                + (float) Order::where('status', 'Delivered')->sum('total'),
            'orders' => Order::count(),
            'customers' => Customer::count(),
            'products' => Product::count(),
            'pendingOrders' => Order::where('status', 'Pending')->count(),
            'refunds' => Sale::where('status', 'Refunded')->count(),
            'lowStock' => Product::where('stock', '<', self::LOW_STOCK_THRESHOLD)->count(),
        ];
    }

    private function monthlySeries($from): array
    {
        $sales = Sale::where('status', 'Completed')
            ->where('date', '>=', $from)
            ->selectRaw("to_char(date, 'YYYY-MM') as month, SUM(total) as total")
            ->groupBy('month')->pluck('total', 'month');

        $purchases = Purchase::where('status', 'Received')
            ->where('date', '>=', $from)
            ->selectRaw("to_char(date, 'YYYY-MM') as month, SUM(total) as total")
            ->groupBy('month')->pluck('total', 'month');

        $months = $sales->keys()->merge($purchases->keys())->unique()->sort()->values();

        return $months->map(fn ($m) => [
            'month' => $m,
            'sales' => (float) ($sales[$m] ?? 0),
            'purchases' => (float) ($purchases[$m] ?? 0),
        ])->all();
    }

    private function topProducts($from, int $limit = 5): array
    {
        return DB::table('sale_details')
            ->join('sales', 'sales.id', '=', 'sale_details.sale_id')
            ->join('products', 'products.id', '=', 'sale_details.product_id')
            ->where('sales.status', 'Completed')
            ->where('sales.date', '>=', $from)
            ->selectRaw('products.name, SUM(sale_details.quantity * sale_details.price) as revenue, SUM(sale_details.quantity) as units')
            ->groupBy('products.id', 'products.name')
            ->orderByDesc('revenue')
            ->limit($limit)
            ->get()
            ->map(fn ($r) => ['name' => $r->name, 'revenue' => (float) $r->revenue, 'units' => (int) $r->units])
            ->all();
    }

    private function salesByMethod($from): array
    {
        $byMethod = Sale::where('status', 'Completed')
            ->where('date', '>=', $from)
            ->selectRaw('payment_method, SUM(total) as total')
            ->groupBy('payment_method')
            ->pluck('total', 'payment_method');

        $grandTotal = $byMethod->sum();
        if ($grandTotal == 0) {
            return [];
        }

        return $byMethod->map(fn ($total, $method) => [
            'name' => $method,
            'value' => round(($total / $grandTotal) * 100, 1),
        ])->values()->all();
    }

    private function lowStock(): array
    {
        return Product::with('category')
            ->where('stock', '<', self::LOW_STOCK_THRESHOLD)
            ->orderBy('stock')
            ->get()
            ->map(fn ($p) => [
                'id' => $p->id, 'name' => $p->name, 'stock' => $p->stock,
                'min' => self::LOW_STOCK_THRESHOLD, 'category' => $p->category?->name ?? '—',
            ])->all();
    }

    private function recentTransactions(int $limit = 10): array
    {
        $sales = Sale::latest('date')->take($limit)->get()->map(fn ($s) => [
            'id' => 'SALE-' . str_pad($s->id, 3, '0', STR_PAD_LEFT),
            'type' => 'Sale', 'amount' => (float) $s->total, 'method' => $s->payment_method,
            'date' => $s->date, 'status' => $s->status,
        ]);

        $orders = Order::latest('date')->take($limit)->get()->map(fn ($o) => [
            'id' => 'ORDER-' . str_pad($o->id, 3, '0', STR_PAD_LEFT),
            'type' => 'Order', 'amount' => (float) $o->total, 'method' => '—',
            'date' => $o->date, 'status' => $o->status,
        ]);

        $purchases = Purchase::latest('date')->take($limit)->get()->map(fn ($p) => [
            'id' => 'PUR-' . str_pad($p->id, 3, '0', STR_PAD_LEFT),
            'type' => 'Purchase', 'amount' => (float) $p->total, 'method' => '—',
            'date' => $p->date, 'status' => $p->status,
        ]);

        return $sales->concat($orders)->concat($purchases)
            ->sortByDesc('date')->take($limit)->values()->all();
    }

    public function sales(Request $request)
    {
        $range = $request->query('range', 'Last 30 days');
        $from  = $this->rangeToDate($range);

        return [
            'monthlySales'  => $this->monthlySeries($from),
            'topProducts'   => $this->topProducts($from),
            'salesByMethod' => $this->salesByMethod($from),
        ];
    }

    public function stock()
    {
        return [
            'lowStockProducts' => $this->lowStock(),
            'totalProducts'    => Product::count(),
            'totalStock'       => (int) Product::sum('stock'),
            'outOfStock'       => Product::where('stock', 0)->count(),
        ];
    }
}