<?php

namespace App\Services;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\User;
use App\Models\Payment;
use App\Models\StockMovement;
use App\Models\PurchaseOrder;
use App\Models\Supplier;
use App\Models\LoyaltyPoint;
use Carbon\Carbon;

class ReportService
{
    // Sales Reports
    public static function getDailySalesReport($date = null)
    {
        $date = $date ?? Carbon::today();
        
        $orders = Order::whereDate('created_at', $date)
            ->where('status', 'delivered')
            ->get();

        return [
            'date' => $date->format('Y-m-d'),
            'total_orders' => $orders->count(),
            'total_revenue' => $orders->sum('total_amount'),
            'average_order_value' => $orders->count() > 0 ? $orders->sum('total_amount') / $orders->count() : 0,
            'payment_methods' => self::getPaymentMethodBreakdown($orders),
            'top_products' => self::getTopProducts($orders, 5),
            'hourly_sales' => self::getHourlySales($date),
        ];
    }

    public static function getWeeklySalesReport($startDate = null, $endDate = null)
    {
        $startDate = $startDate ?? Carbon::now()->startOfWeek();
        $endDate = $endDate ?? Carbon::now()->endOfWeek();
        
        $orders = Order::whereBetween('created_at', [$startDate, $endDate])
            ->where('status', 'delivered')
            ->get();

        return [
            'period' => $startDate->format('M d') . ' - ' . $endDate->format('M d, Y'),
            'total_orders' => $orders->count(),
            'total_revenue' => $orders->sum('total_amount'),
            'average_order_value' => $orders->count() > 0 ? $orders->sum('total_amount') / $orders->count() : 0,
            'daily_breakdown' => self::getDailyBreakdown($startDate, $endDate),
            'top_customers' => self::getTopCustomers($orders, 5),
            'growth_comparison' => self::getGrowthComparison($startDate, $endDate),
        ];
    }

    public static function getMonthlySalesReport($month = null, $year = null)
    {
        $month = $month ?? Carbon::now()->month;
        $year = $year ?? Carbon::now()->year;
        
        $startDate = Carbon::create($year, $month, 1);
        $endDate = $startDate->copy()->endOfMonth();
        
        $orders = Order::whereBetween('created_at', [$startDate, $endDate])
            ->where('status', 'delivered')
            ->get();

        return [
            'month' => $startDate->format('F Y'),
            'total_orders' => $orders->count(),
            'total_revenue' => $orders->sum('total_amount'),
            'average_order_value' => $orders->count() > 0 ? $orders->sum('total_amount') / $orders->count() : 0,
            'weekly_breakdown' => self::getWeeklyBreakdown($startDate, $endDate),
            'category_performance' => self::getCategoryPerformance($orders),
            'month_over_month_growth' => self::getMonthOverMonthGrowth($startDate),
        ];
    }

    // Inventory Reports
    public static function getInventoryReport()
    {
        $products = Product::with('category')->get();
        
        return [
            'total_products' => $products->count(),
            'total_stock_value' => $products->sum(function ($product) {
                return $product->stock_quantity * $product->price;
            }),
            'low_stock_products' => $products->filter(function ($product) {
                return $product->stock_quantity <= $product->low_stock_threshold;
            })->values(),
            'out_of_stock_products' => $products->filter(function ($product) {
                return $product->stock_quantity <= 0;
            })->values(),
            'expiring_products' => $products->filter(function ($product) {
                return $product->expiry_date && 
                       Carbon::parse($product->expiry_date)->diffInDays(now()) <= 30;
            })->values(),
            'category_breakdown' => self::getInventoryByCategory($products),
            'stock_movements' => self::getRecentStockMovements(50),
        ];
    }

    public static function getStockMovementReport($startDate = null, $endDate = null)
    {
        $startDate = $startDate ?? Carbon::now()->subDays(30);
        $endDate = $endDate ?? Carbon::now();
        
        $movements = StockMovement::whereBetween('created_at', [$startDate, $endDate])
            ->with('product', 'user')
            ->get();

        return [
            'period' => $startDate->format('M d') . ' - ' . $endDate->format('M d, Y'),
            'total_movements' => $movements->count(),
            'sales_movements' => $movements->where('type', 'sale')->count(),
            'purchase_movements' => $movements->where('type', 'purchase')->count(),
            'adjustment_movements' => $movements->where('type', 'adjustment')->count(),
            'movement_by_type' => $movements->groupBy('type')->map->count(),
            'top_moved_products' => self::getTopMovedProducts($movements, 10),
        ];
    }

    // Customer Reports
    public static function getCustomerReport()
    {
        $customers = User::with('orders')->get();
        
        return [
            'total_customers' => $customers->count(),
            'active_customers' => $customers->filter(function ($customer) {
                return $customer->orders()->where('created_at', '>=', Carbon::now()->subDays(30))->count() > 0;
            })->count(),
            'new_customers_this_month' => $customers->filter(function ($customer) {
                return $customer->created_at >= Carbon::now()->startOfMonth();
            })->count(),
            'top_customers_by_spending' => $customers->sortByDesc('total_spent')->take(10),
            'customer_retention_rate' => self::getCustomerRetentionRate(),
            'loyalty_points_summary' => self::getLoyaltyPointsSummary(),
            'customer_demographics' => self::getCustomerDemographics($customers),
        ];
    }

    // Financial Reports
    public static function getFinancialReport($startDate = null, $endDate = null)
    {
        $startDate = $startDate ?? Carbon::now()->startOfMonth();
        $endDate = $endDate ?? Carbon::now()->endOfMonth();
        
        $orders = Order::whereBetween('created_at', [$startDate, $endDate])
            ->where('status', 'delivered')
            ->get();
        
        $payments = Payment::whereBetween('created_at', [$startDate, $endDate])
            ->where('status', 'completed')
            ->get();

        return [
            'period' => $startDate->format('M d') . ' - ' . $endDate->format('M d, Y'),
            'total_revenue' => $orders->sum('total_amount'),
            'total_orders' => $orders->count(),
            'average_order_value' => $orders->count() > 0 ? $orders->sum('total_amount') / $orders->count() : 0,
            'payment_method_breakdown' => $payments->groupBy('payment_method')->map(function ($payments) {
                return [
                    'count' => $payments->count(),
                    'total_amount' => $payments->sum('amount'),
                    'percentage' => ($payments->sum('amount') / $payments->sum('amount')) * 100,
                ];
            }),
            'daily_revenue' => self::getDailyRevenue($startDate, $endDate),
            'profit_analysis' => self::getProfitAnalysis($orders),
        ];
    }

    // Supplier Reports
    public static function getSupplierReport()
    {
        $suppliers = Supplier::with('purchaseOrders')->get();
        
        return [
            'total_suppliers' => $suppliers->count(),
            'active_suppliers' => $suppliers->filter(function ($supplier) {
                return $supplier->purchaseOrders()->whereIn('status', ['pending', 'confirmed', 'partial_received'])->count() > 0;
            })->count(),
            'total_purchase_value' => $suppliers->sum(function ($supplier) {
                return $supplier->purchaseOrders()->where('status', 'received')->sum('final_amount');
            }),
            'top_suppliers_by_value' => $suppliers->sortByDesc(function ($supplier) {
                return $supplier->purchaseOrders()->where('status', 'received')->sum('final_amount');
            })->take(10),
            'pending_purchase_orders' => PurchaseOrder::whereIn('status', ['pending', 'confirmed'])->count(),
            'overdue_purchase_orders' => PurchaseOrder::where('expected_delivery_date', '<', now())
                ->whereIn('status', ['pending', 'confirmed'])
                ->count(),
        ];
    }

    // Helper Methods
    private static function getPaymentMethodBreakdown($orders)
    {
        return Payment::whereIn('order_id', $orders->pluck('id'))
            ->where('status', 'completed')
            ->groupBy('payment_method')
            ->map(function ($payments) {
                return [
                    'count' => $payments->count(),
                    'total_amount' => $payments->sum('amount'),
                ];
            });
    }

    private static function getTopProducts($orders, $limit = 5)
    {
        return OrderItem::whereIn('order_id', $orders->pluck('id'))
            ->with('product')
            ->groupBy('product_id')
            ->selectRaw('product_id, SUM(quantity) as total_quantity, SUM(quantity * unit_price) as total_revenue')
            ->orderBy('total_quantity', 'desc')
            ->limit($limit)
            ->get()
            ->map(function ($item) {
                return [
                    'product' => $item->product,
                    'quantity_sold' => $item->total_quantity,
                    'revenue' => $item->total_revenue,
                ];
            });
    }

    private static function getHourlySales($date)
    {
        $orders = Order::whereDate('created_at', $date)
            ->where('status', 'delivered')
            ->get()
            ->groupBy(function ($order) {
                return Carbon::parse($order->created_at)->hour;
            });

        $hourlyData = [];
        for ($hour = 0; $hour < 24; $hour++) {
            $hourlyData[$hour] = [
                'hour' => $hour,
                'orders' => $orders->get($hour, collect())->count(),
                'revenue' => $orders->get($hour, collect())->sum('total_amount'),
            ];
        }

        return $hourlyData;
    }

    private static function getDailyBreakdown($startDate, $endDate)
    {
        $orders = Order::whereBetween('created_at', [$startDate, $endDate])
            ->where('status', 'delivered')
            ->get()
            ->groupBy(function ($order) {
                return Carbon::parse($order->created_at)->format('Y-m-d');
            });

        return $orders->map(function ($dayOrders) {
            return [
                'orders' => $dayOrders->count(),
                'revenue' => $dayOrders->sum('total_amount'),
            ];
        });
    }

    private static function getTopCustomers($orders, $limit = 5)
    {
        return $orders->groupBy('user_id')
            ->map(function ($userOrders) {
                return [
                    'user_id' => $userOrders->first()->user_id,
                    'orders_count' => $userOrders->count(),
                    'total_spent' => $userOrders->sum('total_amount'),
                ];
            })
            ->sortByDesc('total_spent')
            ->take($limit);
    }

    private static function getGrowthComparison($startDate, $endDate)
    {
        $previousPeriodStart = $startDate->copy()->subDays($startDate->diffInDays($endDate));
        $previousPeriodEnd = $endDate->copy()->subDays($startDate->diffInDays($endDate));

        $currentRevenue = Order::whereBetween('created_at', [$startDate, $endDate])
            ->where('status', 'delivered')
            ->sum('total_amount');

        $previousRevenue = Order::whereBetween('created_at', [$previousPeriodStart, $previousPeriodEnd])
            ->where('status', 'delivered')
            ->sum('total_amount');

        $growth = $previousRevenue > 0 ? (($currentRevenue - $previousRevenue) / $previousRevenue) * 100 : 0;

        return [
            'current_revenue' => $currentRevenue,
            'previous_revenue' => $previousRevenue,
            'growth_percentage' => round($growth, 2),
        ];
    }

    private static function getInventoryByCategory($products)
    {
        return $products->groupBy('category_id')
            ->map(function ($categoryProducts) {
                return [
                    'product_count' => $categoryProducts->count(),
                    'total_stock' => $categoryProducts->sum('stock_quantity'),
                    'total_value' => $categoryProducts->sum(function ($product) {
                        return $product->stock_quantity * $product->price;
                    }),
                ];
            });
    }

    private static function getRecentStockMovements($limit = 50)
    {
        return StockMovement::with('product', 'user')
            ->orderBy('created_at', 'desc')
            ->limit($limit)
            ->get();
    }

    private static function getTopMovedProducts($movements, $limit = 10)
    {
        return $movements->groupBy('product_id')
            ->map(function ($productMovements) {
                return [
                    'product' => $productMovements->first()->product,
                    'total_quantity' => $productMovements->sum('quantity'),
                    'movement_count' => $productMovements->count(),
                ];
            })
            ->sortByDesc('total_quantity')
            ->take($limit);
    }

    private static function getCustomerRetentionRate()
    {
        $totalCustomers = User::count();
        $returningCustomers = User::whereHas('orders', function ($query) {
            $query->where('created_at', '>=', Carbon::now()->subDays(30));
        })->count();

        return $totalCustomers > 0 ? ($returningCustomers / $totalCustomers) * 100 : 0;
    }

    private static function getLoyaltyPointsSummary()
    {
        return [
            'total_points_issued' => LoyaltyPoint::where('transaction_type', 'earned')->sum('points_earned'),
            'total_points_redeemed' => LoyaltyPoint::where('transaction_type', 'redeemed')->sum('points_redeemed'),
            'active_points' => LoyaltyPoint::getCurrentBalance(0), // Will need to be calculated per user
        ];
    }

    private static function getCustomerDemographics($customers)
    {
        // This is a placeholder - implement based on your specific demographic data
        return [
            'new_vs_returning' => [
                'new' => $customers->filter(function ($customer) {
                    return $customer->created_at >= Carbon::now()->subDays(30);
                })->count(),
                'returning' => $customers->filter(function ($customer) {
                    return $customer->created_at < Carbon::now()->subDays(30);
                })->count(),
            ],
        ];
    }

    private static function getDailyRevenue($startDate, $endDate)
    {
        return Order::whereBetween('created_at', [$startDate, $endDate])
            ->where('status', 'delivered')
            ->get()
            ->groupBy(function ($order) {
                return Carbon::parse($order->created_at)->format('Y-m-d');
            })
            ->map(function ($dayOrders) {
                return $dayOrders->sum('total_amount');
            });
    }

    private static function getProfitAnalysis($orders)
    {
        // This is a placeholder - implement based on your cost structure
        $revenue = $orders->sum('total_amount');
        $estimatedCost = $revenue * 0.7; // Assume 70% cost
        $profit = $revenue - $estimatedCost;
        
        return [
            'revenue' => $revenue,
            'estimated_cost' => $estimatedCost,
            'estimated_profit' => $profit,
            'profit_margin' => $revenue > 0 ? ($profit / $revenue) * 100 : 0,
        ];
    }

    private static function getWeeklyBreakdown($startDate, $endDate)
    {
        $orders = Order::whereBetween('created_at', [$startDate, $endDate])
            ->where('status', 'delivered')
            ->get()
            ->groupBy(function ($order) {
                return Carbon::parse($order->created_at)->weekOfYear;
            });

        return $orders->map(function ($weekOrders) {
            return [
                'orders' => $weekOrders->count(),
                'revenue' => $weekOrders->sum('total_amount'),
            ];
        });
    }

    private static function getCategoryPerformance($orders)
    {
        return OrderItem::whereIn('order_id', $orders->pluck('id'))
            ->with('product.category')
            ->get()
            ->groupBy('product.category_id')
            ->map(function ($categoryItems) {
                return [
                    'category' => $categoryItems->first()->product->category,
                    'quantity_sold' => $categoryItems->sum('quantity'),
                    'revenue' => $categoryItems->sum(function ($item) {
                        return $item->quantity * $item->unit_price;
                    }),
                ];
            });
    }

    private static function getMonthOverMonthGrowth($currentMonth)
    {
        $previousMonth = $currentMonth->copy()->subMonth();
        
        $currentRevenue = Order::whereMonth('created_at', $currentMonth->month)
            ->whereYear('created_at', $currentMonth->year)
            ->where('status', 'delivered')
            ->sum('total_amount');

        $previousRevenue = Order::whereMonth('created_at', $previousMonth->month)
            ->whereYear('created_at', $previousMonth->year)
            ->where('status', 'delivered')
            ->sum('total_amount');

        $growth = $previousRevenue > 0 ? (($currentRevenue - $previousRevenue) / $previousRevenue) * 100 : 0;

        return [
            'current_month' => $currentMonth->format('F Y'),
            'current_revenue' => $currentRevenue,
            'previous_month' => $previousMonth->format('F Y'),
            'previous_revenue' => $previousRevenue,
            'growth_percentage' => round($growth, 2),
        ];
    }
}
