<!DOCTYPE html>
<html>
<head>
    @if ($errors->any())
        <div class="alert alert-danger">
            <ul>
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif
    <title>Checkout</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <script>
        function toggleCardFields() {
            const method = document.getElementById('payment_method').value;
            const cardFields = document.getElementById('card-fields');
            if (method === 'CREDIT_CARD') {
                cardFields.style.display = 'block';
            } else {
                cardFields.style.display = 'none';
            }
        }
        document.addEventListener('DOMContentLoaded', function() {
            toggleCardFields();
        });
    </script>
</head>
<body class="container mt-5">
<h1>Checkout</h1>

<form method="POST" action="/checkout">
    @csrf
    <div class="mb-3">
        <label>Nome</label>
        <input type="text" name="name" class="form-control" required>
    </div>
    <div class="mb-3">
        <label>Email</label>
        <input type="email" name="email" class="form-control" required>
    </div>
    <div class="mb-3">
        <label>CPF/CNPJ</label>
        <input type="text" name="cpf_cnpj" class="form-control" required>
    </div>
    <div class="mb-3">
        <label>Valor</label>
        <input type="number" step="0.01" name="value" class="form-control" required>
    </div>
    <div class="mb-3">
        <label>Método de pagamento</label>
        <select name="payment_method" id="payment_method" class="form-select" onchange="toggleCardFields()" required>
            <option value="BOLETO">Boleto</option>
            <option value="CREDIT_CARD">Cartão de Crédito</option>
            <option value="PIX">PIX</option>
        </select>
    </div>

    <!-- Campos do Cartão -->
    <div id="card-fields" style="display:none;">
        <h4>Dados do Cartão</h4>
        <div class="mb-3">
            <label>Nome no Cartão</label>
            <input type="text" name="credit_card_holder_name" class="form-control">
        </div>
        <div class="mb-3">
            <label>Número do Cartão</label>
            <input type="text" name="credit_card_number" class="form-control">
        </div>
        <div class="mb-3">
            <label>Validade (MM/AA)</label>
            <input type="text" name="credit_card_expiry" placeholder="MM/AA" class="form-control">
        </div>
        <div class="mb-3">
            <label>CVV</label>
            <input type="text" name="credit_card_cvv" class="form-control">
        </div>
    </div>

    <button type="submit" class="btn btn-primary">Finalizar Pagamento</button>
</form>
</body>
</html>
