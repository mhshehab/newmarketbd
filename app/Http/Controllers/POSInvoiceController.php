<?php

namespace App\Http\Controllers;

use App\Models\Order;
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;

class POSInvoiceController extends Controller
{
    public function download($id)
    {
        $order = Order::with(['user', 'orderItems.product', 'payments'])->findOrFail($id);
        
        $pdf = Pdf::loadView('pos.invoice', compact('order'))
            ->setPaper([0, 0, 226.77, 600], 'portrait') // 80mm width in points (80mm = 226.77 points), 600 points height (~210mm)
            ->setOption('margin-top', 0)
            ->setOption('margin-right', 0)
            ->setOption('margin-bottom', 0)
            ->setOption('margin-left', 0);
        
        return $pdf->download("invoice-{$order->order_number}.pdf");
    }
}
