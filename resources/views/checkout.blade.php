<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Checkout</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery.mask/1.14.16/jquery.mask.min.js"></script>
</head>
<body class="container mt-5">
    <h1 class="mb-4">Checkout</h1>

    @if ($errors->any())
        <div class="alert alert-danger">
            <ul>
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form method="POST" action="{{ url('/checkout') }}" novalidate>
        @csrf

        <section class="mb-4">
            <h4>Dados do Cliente</h4>
            @include('checkout._input', ['name' => 'name', 'label' => 'Nome', 'type' => 'text', 'required' => true])
            @include('checkout._input', ['name' => 'email', 'label' => 'Email', 'type' => 'email', 'required' => true])
            @include('checkout._input', ['name' => 'cpf_cnpj', 'label' => 'CPF/CNPJ', 'type' => 'text', 'required' => true])
            @include('checkout._input', ['name' => 'value', 'label' => 'Valor', 'type' => 'number', 'required' => true, 'step' => '0.01'])
        </section>

        <section class="mb-4">
            <h4>Método de Pagamento</h4>
            <div class="mb-3">
                <label for="payment_method" class="form-label">Selecione</label>
                <select name="payment_method" id="payment_method" class="form-select" required>
                    <option value="BOLETO">Boleto</option>
                    <option value="CREDIT_CARD">Cartão de Crédito</option>
                    <option value="PIX">PIX</option>
                </select>
            </div>
        </section>

        <section id="card-fields" style="display: none;">
            <fieldset class="mb-4">
                <legend>Dados do Cartão</legend>
                @include('checkout._input', ['name' => 'credit_card_holder_name', 'label' => 'Nome no Cartão', 'type' => 'text'])
                @include('checkout._input', ['name' => 'credit_card_number', 'label' => 'Número do Cartão', 'type' => 'text'])
                @include('checkout._input', ['name' => 'credit_card_expiry', 'label' => 'Validade (MM/AA)', 'type' => 'text'])
                @include('checkout._input', ['name' => 'credit_card_cvv', 'label' => 'CVV', 'type' => 'text'])
                <div class="mb-3">
                    <label for="installment_count" class="form-label">Parcelas</label>
                    <select name="installment_count" id="installment_count" class="form-select"></select>
                </div>
            </fieldset>

            <fieldset class="mb-4">
                <legend>Endereço</legend>
                @include('checkout._input', ['name' => 'postal_code', 'label' => 'CEP', 'type' => 'text'])
                @include('checkout._input', ['name' => 'address', 'label' => 'Rua', 'type' => 'text'])
                @include('checkout._input', ['name' => 'address_number', 'label' => 'Número', 'type' => 'text'])
                @include('checkout._input', ['name' => 'city', 'label' => 'Cidade', 'type' => 'text'])
                @include('checkout._input', ['name' => 'state', 'label' => 'Estado', 'type' => 'text'])
                @include('checkout._input', ['name' => 'address_complement', 'label' => 'Complemento', 'type' => 'text'])
                @include('checkout._input', ['name' => 'phone', 'label' => 'Telefone', 'type' => 'text'])
            </fieldset>
        </section>

        <button type="submit" class="btn btn-primary">Finalizar Pagamento</button>
    </form>

    <script>
        function toggleCardFields() {
            const method = $('#payment_method').val();
            const show = method === 'CREDIT_CARD';
            $('#card-fields').toggle(show);
            $('#card-fields').find('input, select').each(function () {
                $(this).prop('disabled', !show);
            });
        }

        function updateInstallments() {
            const value = parseFloat($('[name="value"]').val()) || 0;
            const select = $('#installment_count');
            select.empty();
            if (value > 0) {
                for (let i = 1; i <= 12; i++) {
                    const installment = (value / i).toFixed(2).replace('.', ',');
                    select.append(`<option value="${i}">${i}x de R$ ${installment}</option>`);
                }
            }
        }

        $(document).ready(function () {
            toggleCardFields();
            updateInstallments();

            $('#payment_method').change(toggleCardFields);
            $('[name="value"]').on('input', updateInstallments);

            $('#postal_code').on('input', function () {
                const cep = $(this).val().replace(/\D/g, '');
                if (cep.length === 8) {
                    fetch(`https://viacep.com.br/ws/${cep}/json/`)
                        .then(res => res.json())
                        .then(data => {
                            if (!data.erro) {
                                $('#address').val(data.logradouro || '');
                                $('#city').val(data.localidade || '');
                                $('#state').val(data.uf || '');
                            }
                        });
                }
            });

            $('[name="credit_card_number"]').mask('0000 0000 0000 0000');
            $('[name="credit_card_expiry"]').mask('00/00');
            $('[name="credit_card_cvv"]').mask('000');
            $('[name="phone"]').mask('(00) 00000-0000');
            $('[name="postal_code"]').mask('00000-000');
        });
    </script>
</body>
</html>
