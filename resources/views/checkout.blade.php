<!DOCTYPE html>
<html>
    <head>
        <title>Checkout</title>
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet">
        <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
        <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery.mask/1.14.16/jquery.mask.min.js"></script>
        <script>
            function toggleCardFields() {
                const method = document.getElementById('payment_method').value;
                const cardFields = document.getElementById('card-fields');
                cardFields.style.display = method === 'CREDIT_CARD' ? 'block' : 'none';
            }

            function updateInstallments() {
                const value = parseFloat(document.querySelector('[name="value"]').value || 0);
                const select = document.querySelector('[name="installment_count"]');
                select.innerHTML = '';

                if (value > 0) {
                    const maxInstallments = 12;
                    for (let i = 1; i <= maxInstallments; i++) {
                        const installmentValue = (value / i).toFixed(2).replace('.', ',');
                        const option = document.createElement('option');
                        option.value = i;
                        option.text = `${i}x de R$ ${installmentValue}`;
                        select.appendChild(option);
                    }
                }
            }

            document.addEventListener('DOMContentLoaded', function () {
                toggleCardFields();
                updateInstallments();
                document.getElementById('payment_method').addEventListener('change', toggleCardFields);
                document.querySelector('[name="value"]').addEventListener('input', updateInstallments);

                const cepInput = document.getElementById('postal_code');
                cepInput.addEventListener('input', function () {
                    const cep = cepInput.value.replace(/\D/g, '');
                    if (cep.length === 8) {
                        fetch(`https://viacep.com.br/ws/${cep}/json/`)
                            .then(response => response.json())
                            .then(data => {
                                if (!data.erro) {
                                    document.getElementById('address').value = data.logradouro || '';
                                    document.getElementById('city').value = data.localidade || '';
                                    document.getElementById('state').value = data.uf || '';
                                }
                            });
                    }
                });

                // Apply input masks
                $('[name="credit_card_number"]').mask('0000 0000 0000 0000');
                $('[name="credit_card_expiry"]').mask('00/00');
                $('[name="credit_card_cvv"]').mask('000');
                $('[name="phone"]').mask('(00) 00000-0000');
                $('[name="postal_code"]').mask('00000-000');
            });
        </script>
    </head>
    <body class="container mt-5">
        <h1>Checkout</h1>
        @if ($errors->any())
            <div class="alert alert-danger">
                <ul>@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
            </div>
        @endif
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

            <div id="card-fields" style="display: none;">
                <h4>Dados do Cartão</h4>
                <div class="mb-3">
                    <label>Nome</label>
                    <input type="text" name="credit_card_holder_name" class="form-control">
                </div>
                <div class="mb-3">
                    <label>Número</label>
                    <input type="text" name="credit_card_number" class="form-control">
                </div>
                <div class="mb-3">
                    <label>Validade</label>
                    <input type="text" name="credit_card_expiry" class="form-control" placeholder="MM/AA">
                </div>
                <div class="mb-3">
                    <label>CVV</label>
                    <input type="text" name="credit_card_cvv" class="form-control">
                </div>
                <div class="mb-3">
                    <label>Parcelas</label>
                    <select name="installment_count" class="form-select"></select>
                </div>

                <h4>Endereço</h4>
                <div class="mb-3">
                    <label>CEP</label>
                    <input type="text" name="postal_code" id="postal_code" class="form-control" required>
                </div>
                <div class="mb-3">
                    <label>Rua</label>
                    <input type="text" name="address" id="address" class="form-control" required>
                </div>
                <div class="mb-3">
                    <label>Número</label>
                    <input type="text" name="address" id="address" class="form-control" required>
                </div>
                <div class="mb-3">
                    <label>Cidade</label>
                    <input type="text" name="city" id="city" class="form-control" required>
                </div>
                <div class="mb-3">
                    <label>Estado</label>
                    <input type="text" name="state" id="state" class="form-control" required>
                </div>
                <div class="mb-3">
                    <label>Complemento</label>
                    <input type="text" name="address_complement" class="form-control">
                </div>
                <div class="mb-3">
                    <label>Telefone</label>
                    <input type="text" name="phone" class="form-control">
                </div>
            </div>

            <button type="submit" class="btn btn-primary">Finalizar Pagamento</button>
        </form>
    </body>
</html>
