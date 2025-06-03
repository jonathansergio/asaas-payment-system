<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Obrigado</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="container mt-5">

    <h1 class="mb-4">Obrigado pelo seu pedido!</h1>
    <p>
        <a href="{{ $paymentData['invoiceUrl'] ?? '#' }}" target="_blank" class="btn btn-primary">
            Ver detalhes do pagamento no Asaas
        </a>
    </p>

    @if ($paymentData['billingType'] === 'BOLETO' && isset($billingData['bankSlip']))
        <h4>Pagamento via Boleto</h4>
        <p>
            <a href="{{ $paymentData['bankSlipUrl'] ?? '#' }}" target="_blank" class="btn btn-outline-secondary">
                Baixar Boleto
            </a>
        </p>

    @elseif ($paymentData['billingType'] === 'PIX' && isset($billingData['pix']))
        <h4>Pagamento via Pix</h4>

        @if (!empty($billingData['pix']['encodedImage']))
            <div class="mb-3">
                <img src="data:image/png;base64,{{ $billingData['pix']['encodedImage'] }}"
                     alt="QR Code PIX" class="img-fluid border p-2" style="max-width: 300px;">
            </div>
        @else
            <p>
                <a href="{{ $paymentData['invoiceUrl'] ?? '#' }}" target="_blank">
                    Ver QR Code do Pix
                </a>
            </p>
        @endif

        <p><strong>Código Copia e Cola:</strong></p>
        <textarea class="form-control" rows="3" readonly>{{ $billingData['pix']['payload'] ?? 'Código não disponível' }}</textarea>

    @else
        <h4>Pagamento via Cartão de Crédito</h4>
        <p class="text-success">Pagamento processado com sucesso.</p>
    @endif

    <a href="{{ url('/checkout') }}" class="btn btn-success mt-4">Fazer outro pagamento</a>
</body>
</html>
