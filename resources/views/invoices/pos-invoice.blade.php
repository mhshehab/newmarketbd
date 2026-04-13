<!DOCTYPE html>
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <style>
        @font-face {
            font-family: 'SolaimanLipi';
            src: url('{{ storage_path("fonts/SolaimanLipi.ttf") }}') format('truetype');
            font-weight: normal;
            font-style: normal;
        }

        @page { margin: 0; }

        body {
            /* ল্যাঙ্গুয়েজ অনুযায়ী ফন্ট সেট করা */
            font-family: {{ $lang == 'bn' ? "'SolaimanLipi', sans-serif" : "sans-serif" }};
            font-size: 10px;
            margin: 5px;
            line-height: 1.4;
            color: #000;
        }

        .header { text-align: center; margin-bottom: 5px; }
        .line { border-top: 1px dashed #000; margin: 5px 0; }
        table { width: 100%; border-collapse: collapse; }
        th { text-align: left; border-bottom: 1px solid #000; padding: 2px 0; }
        td { padding: 3px 0; vertical-align: top; }
        .text-right { text-align: right; }
        .total { font-weight: bold; margin-top: 5px; text-align: right; font-size: 11px; }
    </style>
</head>
<body>
    <div class="header">
        <strong>Kids&WomensFashion</strong><br>
        @if($lang == 'bn')
            ইনভয়েস নং: #{{ $order->order_number ?? $order->id }}<br>
            তারিখ: {{ $order->created_at->format('d/m/Y h:i A') }}
        @else
            Invoice No: #{{ $order->order_number ?? $order->id }}<br>
            Date: {{ $order->created_at->format('d/m/Y h:i A') }}
        @endif
    </div>

    <div class="line"></div>

    <table>
        <thead>
            <tr>
                <th width="55%">{{ $lang == 'bn' ? 'বিবরণ' : 'Description' }}</th>
                <th width="15%" class="text-right">{{ $lang == 'bn' ? 'পরি.' : 'Qty' }}</th>
                <th width="30%" class="text-right">{{ $lang == 'bn' ? 'মূল্য' : 'Price' }}</th>
            </tr>
        </thead>
        <tbody>
            @foreach($cart as $id => $item)
            <tr>
                <td>{{ $item['name'] }}</td>
                <td class="text-right">{{ $item['quantity'] }}</td>
                <td class="text-right">{{ number_format($item['price'] * $item['quantity'], 2) }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <div class="line"></div>

    <div class="total">
        {{ $lang == 'bn' ? 'মোট:' : 'Total:' }} 
        <span style="font-family: sans-serif;">{{ $lang == 'bn' ? '৳' : '$' }}</span> 
        {{ number_format($order->total_amount, 2) }}
    </div>

    <div class="header" style="margin-top: 15px; font-size: 9px;">
        {{ $lang == 'bn' ? 'কেনাকাটার জন্য ধন্যবাদ!' : 'Thank you for shopping!' }}<br>
        Powered by YourEcommerce
    </div>
</body>
</html>