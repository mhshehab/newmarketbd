<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\Payment;

class OfflineModeService
{
    const OFFLINE_ORDERS_KEY = 'offline_orders';
    const OFFLINE_STOCK_MOVEMENTS_KEY = 'offline_stock_movements';
    const OFFLINE_PAYMENTS_KEY = 'offline_payments';

    public static function isOffline()
    {
        return !self::checkInternetConnection();
    }

    public static function checkInternetConnection()
    {
        // Check if we can reach an external service
        try {
            $connected = @fsockopen("www.google.com", 80);
            if ($connected) {
                fclose($connected);
                return true;
            }
        } catch (\Exception $e) {
            return false;
        }
        
        return false;
    }

    public static function storeOfflineOrder($orderData)
    {
        $offlineOrders = Cache::get(self::OFFLINE_ORDERS_KEY, []);
        $orderData['offline_id'] = uniqid('offline_', true);
        $orderData['created_at'] = now()->toISOString();
        $orderData['synced'] = false;
        
        $offlineOrders[] = $orderData;
        
        Cache::put(self::OFFLINE_ORDERS_KEY, $offlineOrders, now()->addDays(7));
        
        // Also store in file for persistence
        self::storeOfflineDataToFile('orders', $offlineOrders);
        
        return $orderData['offline_id'];
    }

    public static function storeOfflineStockMovement($movementData)
    {
        $offlineMovements = Cache::get(self::OFFLINE_STOCK_MOVEMENTS_KEY, []);
        $movementData['offline_id'] = uniqid('offline_movement_', true);
        $movementData['created_at'] = now()->toISOString();
        $movementData['synced'] = false;
        
        $offlineMovements[] = $movementData;
        
        Cache::put(self::OFFLINE_STOCK_MOVEMENTS_KEY, $offlineMovements, now()->addDays(7));
        self::storeOfflineDataToFile('stock_movements', $offlineMovements);
        
        return $movementData['offline_id'];
    }

    public static function storeOfflinePayment($paymentData)
    {
        $offlinePayments = Cache::get(self::OFFLINE_PAYMENTS_KEY, []);
        $paymentData['offline_id'] = uniqid('offline_payment_', true);
        $paymentData['created_at'] = now()->toISOString();
        $paymentData['synced'] = false;
        
        $offlinePayments[] = $paymentData;
        
        Cache::put(self::OFFLINE_PAYMENTS_KEY, $offlinePayments, now()->addDays(7));
        self::storeOfflineDataToFile('payments', $offlinePayments);
        
        return $paymentData['offline_id'];
    }

    public static function syncOfflineData()
    {
        if (!self::checkInternetConnection()) {
            return false;
        }

        $syncResults = [
            'orders' => self::syncOfflineOrders(),
            'stock_movements' => self::syncOfflineStockMovements(),
            'payments' => self::syncOfflinePayments(),
        ];

        return $syncResults;
    }

    private static function syncOfflineOrders()
    {
        $offlineOrders = Cache::get(self::OFFLINE_ORDERS_KEY, []);
        $syncedCount = 0;
        $failedCount = 0;

        foreach ($offlineOrders as $key => $order) {
            if ($order['synced']) {
                continue;
            }

            try {
                DB::transaction(function () use ($order, &$syncedCount) {
                    // Create the order in database
                    $dbOrder = Order::create([
                        'user_id' => $order['user_id'],
                        'total_amount' => $order['total_amount'],
                        'status' => $order['status'],
                        'order_number' => $order['order_number'] ?? null,
                        'created_at' => $order['created_at'],
                    ]);

                    // Create order items
                    foreach ($order['items'] as $item) {
                        OrderItem::create([
                            'order_id' => $dbOrder->id,
                            'product_id' => $item['product_id'],
                            'quantity' => $item['quantity'],
                            'unit_price' => $item['unit_price'],
                        ]);
                    }

                    $syncedCount++;
                });

                // Mark as synced
                $offlineOrders[$key]['synced'] = true;
                $offlineOrders[$key]['synced_at'] = now()->toISOString();

            } catch (\Exception $e) {
                $failedCount++;
                $offlineOrders[$key]['sync_error'] = $e->getMessage();
            }
        }

        // Update cache and file
        Cache::put(self::OFFLINE_ORDERS_KEY, $offlineOrders, now()->addDays(7));
        self::storeOfflineDataToFile('orders', $offlineOrders);

        return [
            'synced' => $syncedCount,
            'failed' => $failedCount,
            'total' => count($offlineOrders)
        ];
    }

    private static function syncOfflineStockMovements()
    {
        $offlineMovements = Cache::get(self::OFFLINE_STOCK_MOVEMENTS_KEY, []);
        $syncedCount = 0;
        $failedCount = 0;

        foreach ($offlineMovements as $key => $movement) {
            if ($movement['synced']) {
                continue;
            }

            try {
                // Update product stock
                $product = Product::find($movement['product_id']);
                if ($product) {
                    if ($movement['type'] === 'sale') {
                        $product->decrement('stock_quantity', $movement['quantity']);
                    } else {
                        $product->increment('stock_quantity', $movement['quantity']);
                    }
                }

                // Create stock movement record
                \App\Models\StockMovement::create([
                    'product_id' => $movement['product_id'],
                    'type' => $movement['type'],
                    'quantity' => $movement['quantity'],
                    'reference_id' => $movement['reference_id'] ?? null,
                    'reference_type' => $movement['reference_type'] ?? null,
                    'notes' => $movement['notes'] ?? 'Offline sync',
                    'user_id' => $movement['user_id'] ?? auth()->id(),
                    'created_at' => $movement['created_at'],
                ]);

                $syncedCount++;
                $offlineMovements[$key]['synced'] = true;
                $offlineMovements[$key]['synced_at'] = now()->toISOString();

            } catch (\Exception $e) {
                $failedCount++;
                $offlineMovements[$key]['sync_error'] = $e->getMessage();
            }
        }

        Cache::put(self::OFFLINE_STOCK_MOVEMENTS_KEY, $offlineMovements, now()->addDays(7));
        self::storeOfflineDataToFile('stock_movements', $offlineMovements);

        return [
            'synced' => $syncedCount,
            'failed' => $failedCount,
            'total' => count($offlineMovements)
        ];
    }

    private static function syncOfflinePayments()
    {
        $offlinePayments = Cache::get(self::OFFLINE_PAYMENTS_KEY, []);
        $syncedCount = 0;
        $failedCount = 0;

        foreach ($offlinePayments as $key => $payment) {
            if ($payment['synced']) {
                continue;
            }

            try {
                Payment::create([
                    'order_id' => $payment['order_id'],
                    'payment_method' => $payment['payment_method'],
                    'amount' => $payment['amount'],
                    'transaction_id' => $payment['transaction_id'],
                    'status' => $payment['status'],
                    'payment_details' => $payment['payment_details'] ?? null,
                    'paid_at' => $payment['paid_at'] ?? now(),
                    'created_at' => $payment['created_at'],
                ]);

                $syncedCount++;
                $offlinePayments[$key]['synced'] = true;
                $offlinePayments[$key]['synced_at'] = now()->toISOString();

            } catch (\Exception $e) {
                $failedCount++;
                $offlinePayments[$key]['sync_error'] = $e->getMessage();
            }
        }

        Cache::put(self::OFFLINE_PAYMENTS_KEY, $offlinePayments, now()->addDays(7));
        self::storeOfflineDataToFile('payments', $offlinePayments);

        return [
            'synced' => $syncedCount,
            'failed' => $failedCount,
            'total' => count($offlinePayments)
        ];
    }

    private static function storeOfflineDataToFile($type, $data)
    {
        $filename = "offline_data_{$type}.json";
        Storage::put($filename, json_encode($data, JSON_PRETTY_PRINT));
    }

    public static function getOfflineDataFromFile($type)
    {
        $filename = "offline_data_{$type}.json";
        
        if (Storage::exists($filename)) {
            $content = Storage::get($filename);
            return json_decode($content, true) ?: [];
        }
        
        return [];
    }

    public static function loadOfflineDataFromFile()
    {
        $orders = self::getOfflineDataFromFile('orders');
        $stockMovements = self::getOfflineDataFromFile('stock_movements');
        $payments = self::getOfflineDataFromFile('payments');

        Cache::put(self::OFFLINE_ORDERS_KEY, $orders, now()->addDays(7));
        Cache::put(self::OFFLINE_STOCK_MOVEMENTS_KEY, $stockMovements, now()->addDays(7));
        Cache::put(self::OFFLINE_PAYMENTS_KEY, $payments, now()->addDays(7));

        return [
            'orders' => count($orders),
            'stock_movements' => count($stockMovements),
            'payments' => count($payments),
        ];
    }

    public static function getOfflineStats()
    {
        $orders = Cache::get(self::OFFLINE_ORDERS_KEY, []);
        $stockMovements = Cache::get(self::OFFLINE_STOCK_MOVEMENTS_KEY, []);
        $payments = Cache::get(self::OFFLINE_PAYMENTS_KEY, []);

        return [
            'orders' => [
                'total' => count($orders),
                'synced' => count(array_filter($orders, fn($o) => $o['synced'])),
                'pending' => count(array_filter($orders, fn($o) => !$o['synced'])),
            ],
            'stock_movements' => [
                'total' => count($stockMovements),
                'synced' => count(array_filter($stockMovements, fn($m) => $m['synced'])),
                'pending' => count(array_filter($stockMovements, fn($m) => !$m['synced'])),
            ],
            'payments' => [
                'total' => count($payments),
                'synced' => count(array_filter($payments, fn($p) => $p['synced'])),
                'pending' => count(array_filter($payments, fn($p) => !$p['synced'])),
            ],
        ];
    }
}
