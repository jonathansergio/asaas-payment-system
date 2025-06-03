# 💳 Checkout Laravel com Integração Asaas

Este projeto é uma aplicação Laravel 10+ que implementa um sistema de checkout com suporte a pagamentos via Boleto, Pix e Cartão de Crédito, utilizando a API do Asaas.

## Requisitos

- PHP >= 8.2
- Composer
- Banco de dados PostgreSQL ou MySQL
- Node.js + NPM (se for utilizar Vite para assets)
- Laravel >= 10

## Instalação

Clone o repositório:

git clone https://github.com/jonathansergio/asaas-payment-system

cd asaas-payment-system

### Instale as dependências do backend e frontend:

composer install
npm install && npm run build

### Copie o arquivo de variáveis de ambiente e configure:

cp .env.example .env

### Edite o arquivo .env com as configurações apropriadas:

APP_NAME=Laravel Checkout

APP_URL=http://127.0.0.1:8000

DB_CONNECTION=pgsql

DB_HOST=127.0.0.1

DB_PORT=5432

DB_DATABASE=laravel

DB_USERNAME=postgres

DB_PASSWORD=postgres

ASAAS_API_KEY=sua_api_key_aqui

ASAAS_API_URL=https://sandbox.asaas.com/api/v3

### Gere a chave da aplicação:

php artisan key:generate

### Rode as migrations para criar as tabelas:

php artisan migrate

### Inicie o servidor de desenvolvimento:

php artisan serve

### Acesse a aplicação em http://127.0.0.1:8000/checkout

## Executando os testes

### Crie um banco de dados de testes:

CREATE DATABASE laravel_test;

### Copie o arquivo .env para criar um ambiente de teste:

cp .env .env.testing

### Edite o .env.testing e altere o banco:

DB_DATABASE=laravel_test

### Execute os testes:

php artisan test

## Estrutura principal

- `app/Http/Controllers/CheckoutController.php` – Controlador principal
- `app/Services/Asaas/CustomerService.php` – Gerenciamento de clientes
- `app/Services/Asaas/PaymentService.php` – Pagamentos
- `resources/views/checkout.blade.php` – Formulário de pagamento
- `resources/views/thanks.blade.php` – Página de agradecimento
- `tests/Feature/CheckoutTest.php` – Testes do fluxo principal

## Recursos

- Criação automática de clientes no Asaas
- Pagamento via cartão, boleto ou pix
- Suporte a parcelamento
- Validações robustas
- Mensagens de erro formatadas da API Asaas
- Logs e tratamento centralizado de falhas na API
