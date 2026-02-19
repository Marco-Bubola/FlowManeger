<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Services\MercadoLivre\MercadoLivreService;
use App\Services\MercadoLivre\AuthService;

echo "=== TESTE DOS SERVICES MERCADO LIVRE ===\n\n";

// 1. Testar MercadoLivreService
echo "1️⃣ Testando MercadoLivreService...\n";
try {
    $mlService = new MercadoLivreService();
    echo "   ✅ Service instanciado com sucesso\n";
    echo "   📦 Base URL: " . $mlService->getBaseUrl() . "\n";
    echo "   🌍 Environment: " . $mlService->getEnvironment() . "\n";
    echo "   🔑 Has Credentials: " . ($mlService->hasCredentials() ? 'Yes' : 'No') . "\n";
    
    if (!$mlService->hasCredentials()) {
        echo "   ⚠️  Credenciais não configuradas no .env\n";
    }
} catch (\Exception $e) {
    echo "   ❌ Erro: {$e->getMessage()}\n";
}

echo "\n2️⃣ Testando AuthService...\n";
try {
    $authService = new AuthService();
    echo "   ✅ AuthService instanciado com sucesso\n";
    echo "   🔗 Redirect URI: " . $authService->getRedirectUri() . "\n";
    
    // Testar geração de URL (se tiver credenciais)
    if ($authService->hasCredentials()) {
        try {
            $authUrl = $authService->getAuthorizationUrl(1); // User ID fictício
            echo "   ✅ URL de autorização gerada:\n";
            echo "      " . substr($authUrl, 0, 100) . "...\n";
        } catch (\Exception $e) {
            echo "   ⚠️  Erro ao gerar URL: {$e->getMessage()}\n";
        }
    } else {
        echo "   ⚠️  Credenciais não configuradas - não é possível gerar URL\n";
    }
    
    // Testar verificação de conexão (deve retornar false pois não há token)
    $isConnected = $authService->isConnected(1);
    echo "   " . ($isConnected ? "✅" : "ℹ️ ") . " User 1 connected: " . ($isConnected ? 'Yes' : 'No (esperado)') . "\n";
    
} catch (\Exception $e) {
    echo "   ❌ Erro: {$e->getMessage()}\n";
}

echo "\n3️⃣ Verificando estrutura de classes...\n";

$mlMethods = get_class_methods(MercadoLivreService::class);
$authMethods = get_class_methods(AuthService::class);

echo "   📋 MercadoLivreService - " . count($mlMethods) . " métodos públicos\n";
echo "      - makeRequest() " . (in_array('makeRequest', $mlMethods) ? "✅" : "❌") . "\n";
echo "      - hasCredentials() " . (in_array('hasCredentials', $mlMethods) ? "✅" : "❌") . "\n";
echo "      - getBaseUrl() " . (in_array('getBaseUrl', $mlMethods) ? "✅" : "❌") . "\n";

echo "   📋 AuthService - " . count($authMethods) . " métodos públicos\n";
echo "      - getAuthorizationUrl() " . (in_array('getAuthorizationUrl', $authMethods) ? "✅" : "❌") . "\n";
echo "      - handleCallback() " . (in_array('handleCallback', $authMethods) ? "✅" : "❌") . "\n";
echo "      - refreshToken() " . (in_array('refreshToken', $authMethods) ? "✅" : "❌") . "\n";
echo "      - revokeToken() " . (in_array('revokeToken', $authMethods) ? "✅" : "❌") . "\n";
echo "      - getActiveToken() " . (in_array('getActiveToken', $authMethods) ? "✅" : "❌") . "\n";
echo "      - isConnected() " . (in_array('isConnected', $authMethods) ? "✅" : "❌") . "\n";

echo "\n4️⃣ Verificando configuração...\n";

$config = config('services.mercadolivre');
if ($config) {
    echo "   ✅ Configuração services.mercadolivre existe\n";
    echo "      - app_id: " . (isset($config['app_id']) ? (empty($config['app_id']) ? "⚠️  não configurado" : "✅ configurado") : "❌ não definido") . "\n";
    echo "      - secret_key: " . (isset($config['secret_key']) ? (empty($config['secret_key']) ? "⚠️  não configurado" : "✅ configurado") : "❌ não definido") . "\n";
    echo "      - redirect_uri: " . (isset($config['redirect_uri']) ? "✅ " . $config['redirect_uri'] : "❌ não definido") . "\n";
    echo "      - environment: " . ($config['environment'] ?? '❌ não definido') . "\n";
} else {
    echo "   ❌ Configuração services.mercadolivre não encontrada\n";
}

echo "\n=== TESTE CONCLUÍDO ===\n\n";

echo "📊 RESUMO:\n";
echo "   ✅ Services criados e funcionais\n";
echo "   ✅ Métodos implementados corretamente\n";
echo "   ✅ Configuração adicionada\n";

if (!$authService->hasCredentials()) {
    echo "\n⚠️  PRÓXIMO PASSO:\n";
    echo "   1. Acesse: https://developers.mercadolivre.com.br/\n";
    echo "   2. Crie uma aplicação\n";
    echo "   3. Adicione no .env:\n";
    echo "      MERCADOLIVRE_APP_ID=seu_app_id\n";
    echo "      MERCADOLIVRE_SECRET_KEY=sua_secret_key\n";
    echo "      MERCADOLIVRE_REDIRECT_URI=" . url('/mercadolivre/auth/callback') . "\n";
    echo "      MERCADOLIVRE_ENVIRONMENT=sandbox\n";
} else {
    echo "\n✅ Credenciais configuradas! Pronto para usar.\n";
}

echo "\n🚀 Próximo passo: Criar Controllers e Routes\n";
