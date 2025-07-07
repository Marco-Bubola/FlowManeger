<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Venda #{{ $sale->id }}</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        /* Forçar cores para PDF */
        * {
            -webkit-print-color-adjust: exact !important;
            color-adjust: exact !important;
            print-color-adjust: exact !important;
        }

        body {
            font-family: 'Arial', sans-serif;
            line-height: 1.6;
            color: #333;
            background: #f0f0f0;
            margin: 0;
            padding: 20px;
        }


        .container {
            max-width: 1200px;
            margin: 0 auto;
            background: white;
            padding: 30px;
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
        }

        /* CLIENTE E STATUS NA MESMA LINHA - Usando display table para máxima compatibilidade */
        .info-section {
            width: 100%;
            margin-bottom: 30px;
            display: table;
            border-collapse: separate;
            border-spacing: 15px;
        }

        .info-box {
            display: table-cell;
            width: 48%;
            vertical-align: top;
            background: #f8f9fa;
            padding: 20px;
            border-radius: 10px;
            border-left: 4px solid #9575cd;
            box-shadow: 0 3px 10px rgba(0, 0, 0, 0.08);
        }

        /* Fallback para float caso table não funcione */
        @supports not (display: table-cell) {
            .info-section {
                display: block;
                overflow: hidden;
            }

            .info-box {
                display: block;
                float: left;
                width: 48%;
                margin-right: 4%;
            }

            .info-box:last-child {
                margin-right: 0;
            }
        }

        .info-box h3 {
            color: #495057;
            font-size: 14px;
            font-weight: 700;
            margin-bottom: 10px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .info-box p {
            color: #212529;
            font-size: 15px;
            font-weight: 500;
            margin-bottom: 4px;
        }

        .info-box .small {
            color: #6c757d;
            font-size: 12px;
            font-weight: normal;
        }

        .status {
            display: inline-block;
            padding: 6px 12px;
            border-radius: 15px;
            font-size: 11px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .status.pago {
            background: #d1fae5;
            color: #065f46;
        }

        .status.pendente {
            background: #fef3c7;
            color: #92400e;
        }

        .status.cancelado {
            background: #fee2e2;
            color: #991b1b;
        }

        /* PRODUTOS EM 3 COLUNAS - Usando inline-block para máxima compatibilidade PDF */
        .products-section {
            margin-bottom: 30px;
        }

        .section-title {
            color: #212529;
            font-size: 22px;
            font-weight: 700;
            margin-bottom: 20px;
            padding-bottom: 10px;
            border-bottom: 3px solid #9575cd;
            text-align: center;
        }

        .products-grid {
            width: 100%;
            display: table;
            table-layout: fixed;
            border-collapse: separate;
            border-spacing: 10px;
        }

        .products-row {
            display: table-row;
        }

        .product-card {
            display: table-cell;
            width: 33.33%;
            vertical-align: top;
            background: #e6e6fa;
            border: 2.5px solid #b39ddb;
            border-radius: 1.7em;
            overflow: hidden;
            box-shadow: 0 4px 18px rgba(149, 117, 205, 0.13);
            position: relative;
            min-height: 240px;
            box-sizing: border-box;
            font-size: 14px;
            line-height: 1.4;
            page-break-inside: avoid;
        }

        /* Remover regras de float que não funcionam no PDF */
        .products-grid::after {
            content: "";
            display: table;
            clear: both;
        }

        /* Para PDF - Garantir layout de tabela para máxima compatibilidade */
        @media print {
            .products-grid {
                display: table !important;
                table-layout: fixed !important;
                width: 100% !important;
                border-collapse: separate !important;
                border-spacing: 10px !important;
            }

            .products-row {
                display: table-row !important;
            }

            .product-card {
                display: table-cell !important;
                width: 33.33% !important;
                vertical-align: top !important;
                page-break-inside: avoid !important;
                float: none !important;
                margin-right: 0 !important;
                margin-bottom: 0 !important;
            }
        }

        .product-card::before {
            content: '';
            position: absolute;
            inset: 0;
            border-radius: 1.7em;
            pointer-events: none;
            box-shadow: 0 0 0 3px #ffe0b2 inset;
            z-index: 1;
        }

        .product-image-container {
            position: relative;
            height: 140px;
            background: #e6e6fa;
            border-top-left-radius: 1.7em;
            border-top-right-radius: 1.7em;
            border-bottom: 4px solid #b39ddb;
            box-shadow: 0 2px 16px rgba(149, 117, 205, 0.2);
            display: flex;
            align-items: center;
            justify-content: center;
            overflow: hidden;
            z-index: 2;
            flex-shrink: 0;
            padding: 0;
            margin: 0;
        }

        .product-image {
            width: 100%;
            height: 100%;
            object-fit: cover;
            border-top-left-radius: 1.7em;
            border-top-right-radius: 1.7em;
            border: 3px solid #ffe0b2;
            box-shadow: 0 2px 16px rgba(149, 117, 205, 0.2);
            background: #fff;
            z-index: 2;
            position: absolute;
            top: 0;
            left: 0;
        }

        .product-placeholder {
            font-size: 28px;
            color: #9575cd;
            opacity: 0.6;
            z-index: 2;
            font-weight: 700;
            text-shadow: 0 1px 2px rgba(149, 117, 205, 0.3);
        }

        .product-code-badge {
            position: absolute;
            top: 0.4em;
            left: 0.4em;
            background: #f8bbd0;
            color: #424242;
            padding: 0.15em 0.7em;
            border-radius: 1.2em 0 1.2em 0;
            font-size: 9px;
            font-weight: 700;
            text-transform: uppercase;
            z-index: 4;
            box-shadow: 0 2px 8px rgba(248, 187, 208, 0.2);
            border-bottom: 2px solid #b39ddb;
            border-right: 2px solid #b39ddb;
            text-shadow: 1px 1px 2px rgba(179, 157, 219, 0.6);
        }

        .product-info {
            padding: 10px;
            position: relative;
            min-height: 100px;
        }

        .product-name {
            font-family: 'Arial', sans-serif;
            font-size: 12px;
            font-weight: 700;
            color: #424242;
            margin: 0 0 8px 0;
            text-align: center;
            line-height: 1.2;
            text-shadow: 0 1px 4px #fff, 0 0 2px #fff;
            letter-spacing: 0.02em;
            max-height: 28px;
            overflow: hidden;
            display: block;
        }

        .product-details {
            display: block;
            width: 100%;
            margin-bottom: 6px;
        }

        .product-quantity,
        .product-price {
            display: inline-block;
            width: 48%;
            padding: 4px 8px;
            font-size: 12px;
            font-weight: 700;
            text-align: center;
            border-radius: 1.2em 0 0.8em 0;
            box-shadow: 0 2px 8px rgba(248, 187, 208, 0.2);
            border-bottom: 2px solid;
            border-right: 2px solid;
            min-height: 20px;
            vertical-align: top;
            box-sizing: border-box;
            white-space: nowrap;
            margin-right: 2%;
        }

        .product-price {
            margin-right: 0;
            border-radius: 0 1.2em 0 0.8em;
            border-left: 2px solid #ba68c8;
            border-right: none;
        }

        .product-quantity {
            background: #f8bbd0;
            color: #424242;
            border-color: #9575cd;
        }

        .product-price {
            background: #9575cd;
            color: #fff;
            border-color: #ba68c8;
            border-radius: 0 1.2em 0 0.8em;
            border-left: 2px solid #ba68c8;
            border-right: none;
        }

        .product-total {
            background: #f8bbd0;
            color: #424242;
            padding: 5px 10px;
            border-radius: 1.2em 0 0.8em 0;
            font-size: 15px;
            font-weight: 700;
            text-align: center;
            margin-top: 4px;
            width: 100%;
            box-shadow: 0 2px 8px rgba(248, 187, 208, 0.2);
            border-bottom: 2px solid #9575cd;
            border-right: 2px solid #9575cd;
            min-height: 22px;
            display: block;
            box-sizing: border-box;
        }

        /* Seção de Totais */
        .total-section {
            background: #f8f9fa;
            padding: 25px;
            border-radius: 15px;
            border: 2px solid #9575cd;
            margin-bottom: 25px;
        }

        .total-row {
            display: table;
            width: 100%;
            margin-bottom: 10px;
            padding: 6px 0;
            font-size: 15px;
        }

        .total-row .total-label {
            display: table-cell;
            font-weight: 600;
        }

        .total-row .total-value {
            display: table-cell;
            text-align: right;
            font-weight: 700;
            font-size: 16px;
        }

        .total-row.final {
            border-top: 2px solid #9575cd;
            padding-top: 15px;
            margin-top: 15px;
            font-size: 18px;
            font-weight: bold;
            color: #9575cd;
        }

        /* Seção de Pagamentos */
        .payments-section {
            margin-top: 25px;
        }

        .payment-card {
            background: white;
            border: 2px solid #e9ecef;
            border-left: 4px solid #10b981;
            border-radius: 10px;
            padding: 15px;
            margin-bottom: 10px;
        }

        .payment-header {
            display: table;
            width: 100%;
            margin-bottom: 8px;
        }

        .payment-amount {
            display: table-cell;
            font-size: 16px;
            font-weight: 700;
            color: #10b981;
        }

        .payment-method {
            display: table-cell;
            text-align: right;
            background: #e3f2fd;
            color: #1565c0;
            padding: 4px 8px;
            border-radius: 10px;
            font-size: 11px;
            font-weight: 600;
            text-transform: uppercase;
        }

        .payment-date {
            color: #6c757d;
            font-size: 13px;
        }

        /* Footer */
        .footer {
            margin-top: 30px;
            padding: 15px;
            background: #9575cd;
            color: white;
            text-align: center;
            border-radius: 10px;
            font-size: 13px;
        }

        /* CSS para PDF - Garantir que funcione */
        @media print {
            body {
                background: white !important;
                padding: 0 !important;
            }

            .container {
                box-shadow: none !important;
                margin: 0 !important;
                padding: 20px !important;
                max-width: none !important;
            }

            .info-section {
                display: table !important;
                width: 100% !important;
                border-collapse: separate !important;
                border-spacing: 15px !important;
            }

            .info-box {
                display: table-cell !important;
                width: 48% !important;
                vertical-align: top !important;
                break-inside: avoid !important;
            }

            .products-grid {
                display: table !important;
                table-layout: fixed !important;
                width: 100% !important;
                border-collapse: separate !important;
                border-spacing: 10px !important;
            }

            .products-row {
                display: table-row !important;
            }

            .product-card {
                display: table-cell !important;
                width: 33.33% !important;
                vertical-align: top !important;
                page-break-inside: avoid !important;
            }

            .product-image-container {
                background: #e6e6fa !important;
                border-bottom: 4px solid #b39ddb !important;
                box-shadow: 0 2px 16px rgba(149, 117, 205, 0.2) !important;
                border-top-left-radius: 1.7em !important;
                border-top-right-radius: 1.7em !important;
            }

            .product-image {
                background: #fff !important;
                border: 3px solid #ffe0b2 !important;
                border-top-left-radius: 1.7em !important;
                border-top-right-radius: 1.7em !important;
                box-shadow: 0 2px 16px rgba(149, 117, 205, 0.2) !important;
                position: absolute !important;
                top: 0 !important;
                left: 0 !important;
            }

            .product-code-badge {
                background: #f8bbd0 !important;
                color: #424242 !important;
                border-radius: 1.2em 0 1.2em 0 !important;
                border-bottom: 2px solid #b39ddb !important;
                border-right: 2px solid #b39ddb !important;
            }

            .product-quantity {
                background: #f8bbd0 !important;
                color: #424242 !important;
            }

            .product-price {
                background: #9575cd !important;
                color: #fff !important;
            }

            .product-info {
                padding: 10px !important;
                position: relative !important;
                min-height: 100px !important;
            }

            .product-name {
                font-family: 'Arial', sans-serif !important;
                font-size: 12px !important;
                font-weight: 700 !important;
                color: #424242 !important;
                margin: 0 0 8px 0 !important;
                text-align: center !important;
                line-height: 1.2 !important;
                display: block !important;
                max-height: 28px !important;
                overflow: hidden !important;
            }

            .product-details {
                display: block !important;
                width: 100% !important;
                margin-bottom: 6px !important;
            }

            .product-quantity,
            .product-price {
                display: inline-block !important;
                width: 48% !important;
                vertical-align: top !important;
                box-sizing: border-box !important;
                white-space: nowrap !important;
                margin-right: 2% !important;
                padding: 4px 8px !important;
                font-size: 12px !important;
                font-weight: 700 !important;
                text-align: center !important;
                border-radius: 1.2em 0 0.8em 0 !important;
                border-bottom: 2px solid !important;
                border-right: 2px solid !important;
                min-height: 20px !important;
            }

            .product-price {
                margin-right: 0 !important;
                border-radius: 0 1.2em 0 0.8em !important;
                border-left: 2px solid #ba68c8 !important;
                border-right: none !important;
            }

            .product-total {
                background: #f8bbd0 !important;
                color: #424242 !important;
                padding: 5px 10px !important;
                border-radius: 1.2em 0 0.8em 0 !important;
                font-size: 15px !important;
                font-weight: 700 !important;
                text-align: center !important;
                margin-top: 4px !important;
                width: 100% !important;
                min-height: 22px !important;
                display: block !important;
                box-sizing: border-box !important;
            }

            /* Forçar cores em PDF */
            .header {
                background: #9575cd !important;
                color: white !important;
            }

            .info-box {
                background: #f8f9fa !important;
            }

            .total-section {
                background: #f8f9fa !important;
            }

            .footer {
                background: #9575cd !important;
                color: white !important;
            }
        }

        /* Clearfix para compatibilidade */
        .info-section::after {
            content: "";
            display: table;
            clear: both;
        }
    </style>
</head>

<body>
    <div class="container">


        <!-- Informações Principais -->
        <div class="info-section">
            <!-- Cliente -->
            <div class="info-box">
                <h3>CLIENTE</h3>
                <p>{{ $sale->client->name }}</p>
                @if($sale->client->email)
                <p class="small">Email: {{ $sale->client->email }}</p>
                @endif
                @if($sale->client->phone)
                <p class="small">Fone: {{ $sale->client->phone }}</p>
                @endif
            </div>

            <!-- Status e Pagamento -->
            <div class="info-box">
                <h3>STATUS E PAGAMENTO</h3>
                <p>
                    <span class="status {{ $sale->status }}">{{ ucfirst($sale->status) }}</span>
                </p>
                <p class="small">
                    {{ $sale->tipo_pagamento === 'a_vista' ? 'A Vista' : 'Parcelado' }}
                    @if($sale->tipo_pagamento === 'parcelado')
                    ({{ $sale->parcelas }}x)
                    @endif
                </p>
                <p class="small">Criada em {{ $sale->created_at->format('d/m/Y H:i') }}</p>
            </div>
        </div>

        <!-- Produtos -->
        <div class="products-section">
            <h2 class="section-title">PRODUTOS DA VENDA</h2>

            @if($sale->saleItems->count() > 0)
            <!-- Grid de produtos - 3 por linha usando table -->
            <div class="products-grid">
                @php
                $items = $sale->saleItems;
                $chunks = $items->chunk(3);
                @endphp

                @foreach($chunks as $chunk)
                <div class="products-row">
                    @foreach($chunk as $item)
                    <div class="product-card">
                        <div class="product-image-container">
                            @if($item->product->image && $item->product->image !== 'product-placeholder.png')
                            @php
                            $imagePath = public_path('storage/products/' . $item->product->image);
                            $imageExists = file_exists($imagePath);
                            @endphp
                            @if($imageExists)
                            @php
                            $imageData = base64_encode(file_get_contents($imagePath));
                            $extension = pathinfo($imagePath, PATHINFO_EXTENSION);
                            $mimeType = 'image/' . ($extension === 'jpg' ? 'jpeg' : $extension);
                            @endphp
                            <img src="data:{{ $mimeType }};base64,{{ $imageData }}"
                                alt="{{ $item->product->name }}"
                                class="product-image">
                            @else
                            <div class="product-placeholder">[IMG]</div>
                            @endif
                            @else
                            <div class="product-placeholder">[IMG]</div>
                            @endif

                            <div class="product-code-badge">
                                #{{ $item->product->product_code ?? 'N/A' }}
                            </div>
                        </div>

                        <div class="product-info">
                            <h3 class="product-name">{{ $item->product->name }}</h3>

                            <div class="product-details" style="display: flex; gap: 6px; align-items: center; justify-center: center;">
                                <div class="product-quantity" style="margin-bottom: 0; width: auto;">
                                    Qtd: {{ $item->quantity }}
                                </div>
                                <div class="product-price" style="margin-bottom: 0; width: auto;">
                                    R$ {{ number_format($item->price_sale, 2, ',', '.') }}
                                </div>
                            </div>

                            <div class="product-total">
                                Total: R$ {{ number_format($item->quantity * $item->price_sale, 2, ',', '.') }}
                            </div>
                        </div>
                    </div>
                    @endforeach

                    <!-- Preencher células vazias se necessário -->
                    @for($i = $chunk->count(); $i < 3; $i++)
                        <div class="product-card" style="visibility: hidden;">
                </div>
                @endfor
            </div>
            @endforeach
        </div>
        @else
        <div style="text-align: center; padding: 40px; color: #6c757d;">
            <div style="font-size: 48px; margin-bottom: 20px;">[VAZIO]</div>
            <p>Nenhum produto encontrado nesta venda.</p>
        </div>
        @endif
    </div>

    <!-- Totais -->
    <div class="total-section">
        <div class="total-row">
            <span class="total-label">Subtotal:</span>
            <span class="total-value">R$ {{ number_format($sale->total_price, 2, ',', '.') }}</span>
        </div>

        @if($sale->amount_paid > 0)
        <div class="total-row">
            <span class="total-label">Valor Pago:</span>
            <span class="total-value" style="color: #10b981;">R$ {{ number_format($sale->amount_paid, 2, ',', '.') }}</span>
        </div>
        @endif

        @if($sale->amount_due > 0)
        <div class="total-row">
            <span class="total-label">Valor Pendente:</span>
            <span class="total-value" style="color: #dc2626;">R$ {{ number_format($sale->amount_due, 2, ',', '.') }}</span>
        </div>
        @endif

        <div class="total-row final">
            <span class="total-label">Total da Venda:</span>
            <span class="total-value">R$ {{ number_format($sale->total_price, 2, ',', '.') }}</span>
        </div>
    </div>

    <!-- Pagamentos -->
    @if($sale->payments->count() > 0)
    <div class="payments-section">
        <h2 class="section-title">PAGAMENTOS</h2>

        @foreach($sale->payments as $payment)
        <div class="payment-card">
            <div class="payment-header">
                <span class="payment-amount">
                    R$ {{ number_format($payment->amount_paid, 2, ',', '.') }}
                </span>
                <span class="payment-method">{{ ucfirst(str_replace('_', ' ', $payment->payment_method)) }}</span>
            </div>
            <div class="payment-date">
                {{ \Carbon\Carbon::parse($payment->payment_date)->format('d/m/Y') }}
            </div>
        </div>
        @endforeach
    </div>
    @endif


    </div>
</body>

</html>