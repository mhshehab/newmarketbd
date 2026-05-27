<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Invoice - {{ $order->order_number }}</title>
    <style>
        * {
            box-sizing: border-box;
            margin: 0;
        padding: 0;
        }
        
        body {
            font-family: 'Courier New', monospace;
            margin: 0;
            padding: 0;
            background-color: white;
            font-size: 11px;
            line-height: 1.3;
        }
        
        @media print {
            body {
                padding: 0;
                background-color: white;
                font-size: 8px;
                width: 80mm;
                margin: 0;
            }
            
            .invoice-container {
                width: 80mm;
                padding: 3px;
            }
        }
        
        .invoice-container {
            width: 80mm;
            margin: 0 auto;
            background: white;
            padding: 5px;
            border: none;
        }
        .website-header {
            text-align: center;
            margin-bottom: 5px;
        }
        
        .logo-container {
            margin-bottom: 3px;
        }
        
        .company-logo {
            max-width: 40mm;
            max-height: 15mm;
            object-fit: contain;
        }
        
        .website-name {
            text-align: center;
            font-size: 12px;
            font-weight: bold;
            margin-bottom: 5px;
        }
        
        .invoice-header {
            text-align: center;
            margin-bottom: 8px;
        }
        
        .invoice-number {
            font-size: 10px;
            font-weight: bold;
            margin-bottom: 2px;
        }
        
        .invoice-date {
            font-size: 9px;
            margin-bottom: 5px;
        }
        
        .divider {
            border-bottom: 1px dashed #333;
            margin: 5px 0;
        }
        
        .section-title {
            font-weight: bold;
            font-size: 9px;
            margin-bottom: 3px;
            text-transform: uppercase;
        }
        
        .info-row {
            margin-bottom: 2px;
            font-size: 9px;
        }
        
        .items-table {
            width: 100%;
            border-collapse: collapse;
            margin: 5px 0;
            font-size: 8px;
        }
        
        .items-table th {
            padding: 2px 1px;
            text-align: left;
            border-bottom: 1px solid #333;
            font-weight: bold;
            font-size: 8px;
        }
        
        .items-table td {
            padding: 2px 1px;
            border-bottom: 1px solid #ddd;
            font-size: 8px;
        }
        
        .totals-section {
            margin: 0px 15px 0px 0px;
            font-size: 9px;

        }
        
        .total-row {
            display: flex;
            justify-content: flex-end;
            margin-bottom: 2px;
        }
        
        .total-label {
            margin-right: 5px;
            font-weight: bold;
            min-width: 50px;
            text-align: right;
        }
        
        .total-value {
            min-width: 40px;
            text-align: right;
            font-weight: bold;
        }
        
        .payment-details {
            margin-top: 5px;
            font-size: 9px;
        }
        
        .print-button {
            text-align: center;
            margin-top: 20px;
        }
        
        @media print {
            .print-button {
                display: none;
            }
        }
    </style>
</head>
<body>
    <div class="invoice-container">
        <!-- Website Name with Logo -->
        <div class="website-header">
            <div class="logo-container">
                <img src="{{ asset('images/logo.png') }}" alt="Logo" class="company-logo">
            </div>
            <div class="website-name">
                NewMarket BD
            </div>
        </div>

        <!-- Invoice Header -->
        <div class="invoice-header">
            <div class="invoice-number">Invoice No: #{{ $order->order_number }}</div>
            <div class="invoice-date">Date: {{ $order->created_at->format('d/m/Y') }} Time: {{ $order->created_at->format('h:i A') }}</div>
        </div>

        <div class="divider"></div>

        <!-- Customer Information -->
        <div class="section-title">Customer Information:</div>
        <div class="info-row">Customer Name: {{ $order->user->name ?? 'Walk-in Customer' }}</div>
        <div class="info-row">Customer Mobile No: {{ $order->user->phone ?? 'N/A' }}</div>

        <div class="divider"></div>

        <!-- Order Information -->
        <div class="section-title">Order Information:</div>
        <div class="info-row">Order Date and Time: {{ $order->created_at->format('d/m/Y h:i A') }}</div>
        <div class="info-row">Status: {{ ucfirst($order->status) }}</div>
        @if($order->payments->count() > 0)
            @php $payment = $order->payments->first(); @endphp
            <div class="info-row">Payment Method: {{ ucfirst($payment->payment_method ?? 'N/A') }}</div>
        @endif

        <div class="divider"></div>

        <!-- Order Items -->
        <table class="items-table">
            <thead>
                <tr>
                    <th style="width: 50%">Description</th>
                    <th style="width: 15%">Quantity</th>
                    <th style="width: 17%">Unit Price</th>
                    <th style="width: 18%">Total</th>
                </tr>
            </thead>
            <tbody>
                @foreach($order->orderItems as $item)
                    <tr>
                        <td>{{ $item->product->name }}</td>
                        <td>{{ str_pad($item->quantity, 2, '0', STR_PAD_LEFT) }}</td>
                        <td>BDT {{ number_format($item->unit_price, 0) }}</td>
                        <td>BDT {{ number_format($item->total_price, 0) }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        <div class="divider"></div>

        <!-- Totals Section -->
        <div class="totals-section">
            <div class="total-row">
                <div class="total-label">Subtotal:</div>
                <div class="total-value">BDT {{ number_format($order->total_amount, 2) }}</div>
            </div>
            <div class="total-row">
                <div class="total-label">Total Amount:</div>
                <div class="total-value">BDT {{ number_format($order->total_amount, 2) }}</div>
            </div>
        </div>

        <div class="divider"></div>

        <!-- Payment Details -->
        <div class="payment-details">
            <div class="section-title">Payment Details</div>
            @if($order->payments->count() > 0)
                @php $payment = $order->payments->first(); @endphp
                <div class="info-row">Payment Method: {{ ucfirst($payment->payment_method ?? 'N/A') }}</div>
                @if($payment->payment_method === 'cash' && isset($payment->payment_details['cash_received']))
                    <div class="info-row">Cash Received: BDT {{ number_format($payment->payment_details['cash_received'], 2) }}</div>
                    <div class="info-row">Change: BDT {{ number_format($payment->payment_details['change_amount'], 2) }}</div>
                @endif
            @else
                <div class="info-row">Payment Method: N/A</div>
            @endif
        </div>
    </div>

    <!-- Print Button (Hidden when printing) -->
    <div class="print-button">
        <button onclick="window.print()" style="padding: 8px 16px; background: #333; color: white; border: none; border-radius: 3px; cursor: pointer; font-size: 12px;">
            Print Invoice
        </button>
    </div>
</body>
</html>
