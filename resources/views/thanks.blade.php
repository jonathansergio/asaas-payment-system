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
        <a href="{{ $paymentData['invoiceUrl'] ?? '#' }}" target="_blank" class="btn btn-primary mb-3">
            Ver detalhes do pagamento no Asaas
        </a>
    </p>

    @if ($paymentData['billingType'] === 'BOLETO' && isset($billingData['bankSlip']))
        <h4 class="mb-3">Pagamento via Boleto</h4>
        <p>
            <a href="{{ $paymentData['bankSlipUrl'] ?? '#' }}" target="_blank" class="btn btn-outline-secondary">
                Baixar Boleto
            </a>
        </p>

    @elseif ($paymentData['billingType'] === 'PIX' && isset($billingData['pix']))
        <h4 class="mb-3">Pagamento via Pix</h4>

        @if (!empty($billingData['pix']['encodedImage']))
            <div>
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

        <p class="mt-4"><strong>Código Copia e Cola:</strong></p>
        <div class="input-group">
            <textarea id="pixCode" class="form-control" rows="3" readonly>{{ $billingData['pix']['payload'] ?? 'Código não disponível' }}</textarea>
            <button class="btn btn-primary btn-lg" type="button" onclick="copyPixCode()">Copiar</button>
        </div>

    @else
        <h4 class="mb-3">Pagamento via Cartão de Crédito</h4>
        <p class="text-success">Pagamento processado com sucesso.</p>
    @endif

    <a href="{{ url('/checkout') }}" class="btn btn-success mt-5 mb-5">Fazer outro pagamento</a>

    <div class="position-fixed bottom-0 end-0 p-3" style="z-index: 9999">
        <div id="copyToast" class="toast align-items-center text-white bg-success border-0" role="alert">
            <div class="d-flex">
                <div class="toast-body">
                    Código copiado com sucesso!
                </div>
                <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function copyPixCode() {
            const textarea = document.getElementById('pixCode');
            navigator.clipboard.writeText(textarea.value)
                .then(() => {
                    const toastEl = document.getElementById('copyToast');
                    const toast = new bootstrap.Toast(toastEl);
                    toast.show();
                })
                .catch(err => console.error('Erro ao copiar:', err));
        }
    </script>
</body>
</html>
