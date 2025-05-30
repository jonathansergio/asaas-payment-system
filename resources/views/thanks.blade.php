<!DOCTYPE html>
<html>
    <head>
        <title>Obrigado</title>
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet">
    </head>
    <body class="container mt-5">
        <h1>Obrigado pelo seu pedido!</h1>

        {{-- Link para a página de pagamento no Asaas (sempre aparece) --}}
        <p>
            <a href="{{ $paymentData['invoiceUrl'] }}" target="_blank" class="btn btn-primary">
                Ver detalhes do pagamento no Asaas
            </a>
        </p>

        @if($paymentData['billingType'] === 'BOLETO' && isset($billingData['bankSlip']))
            <h4>Pagamento via Boleto</h4>
            <p>
                <a href="{{ $paymentData['bankSlipUrl'] }}" target="_blank">
                    Baixar Boleto
                </a>
            </p>

        @elseif($paymentData['billingType'] === 'PIX' && isset($billingData['pix']))
            <h4>Pagamento via Pix</h4>
            <p>QR Code do Pix:</p>

            @if(isset($billingData['pix']['encodedImage']))
                <img src="data:image/png;base64,{{ $billingData['pix']['encodedImage'] }}" alt="QR Code PIX">
            @else
                <p>
                    <a href="{{ $paymentData['invoiceUrl'] }}" target="_blank">
                        Ver QRCode do Pix
                    </a>
                </p>
            @endif

            <p>Copia e Cola:</p>
            <textarea class="form-control" rows="3">
                        {{ $billingData['pix']['payload'] ?? 'Código não disponível' }}
            </textarea>

        @else
            <h4>Pagamento via Cartão de Crédito</h4>
            <p>Pagamento processado com sucesso.</p>
        @endif

        <a href="/checkout" class="btn btn-success mt-3">Fazer outro pagamento</a>
    </body>
</html>
