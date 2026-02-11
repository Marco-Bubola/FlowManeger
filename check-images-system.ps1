# Script de Verificação do Sistema de Imagens - FlowManager
# Uso: .\check-images-system.ps1

Write-Host "`n==================================================" -ForegroundColor Cyan
Write-Host "  VERIFICAÇÃO DO SISTEMA DE IMAGENS - FlowManager" -ForegroundColor Cyan
Write-Host "==================================================" -ForegroundColor Cyan

$baseDir = "C:\projetos\FlowManeger"
$checks = @()

# Função para adicionar check
function Add-Check {
    param($Name, $Result, $Details = "")
    
    $icon = if ($Result) { "[✓]" } else { "[✗]" }
    $color = if ($Result) { "Green" } else { "Red" }
    
    Write-Host "`n$icon $Name" -ForegroundColor $color
    if ($Details) {
        Write-Host "    $Details" -ForegroundColor Gray
    }
    
    $script:checks += @{
        Name = $Name
        Result = $Result
        Details = $Details
    }
}

# Verificação 1: Link Simbólico
Write-Host "`n[1/7] Verificando Link Simbólico..." -ForegroundColor Yellow
$linkExists = Test-Path "$baseDir\public\storage"
Add-Check "Link Simbólico public/storage existe" $linkExists

# Verificação 2: Pasta products via link
Write-Host "[2/7] Verificando Pasta products via link..." -ForegroundColor Yellow
$productsViaLink = Test-Path "$baseDir\public\storage\products"
Add-Check "Pasta products/ acessível via link" $productsViaLink

# Verificação 3: Pasta física
Write-Host "[3/7] Verificando Pasta física..." -ForegroundColor Yellow
$physicalFolder = Test-Path "$baseDir\storage\app\public\products"
Add-Check "Pasta física storage/app/public/products existe" $physicalFolder

# Verificação 4: Contagem de imagens
Write-Host "[4/7] Contando imagens..." -ForegroundColor Yellow
if ($physicalFolder) {
    $imageCount = (Get-ChildItem "$baseDir\storage\app\public\products" -File | Where-Object {
        $_.Extension -match '\.(jpg|jpeg|png|gif|webp)$'
    }).Count
    Add-Check "Imagens encontradas na pasta" ($imageCount -gt 0) "$imageCount arquivo(s)"
} else {
    Add-Check "Imagens encontradas na pasta" $false "Pasta não existe"
}

# Verificação 5: Placeholder
Write-Host "[5/7] Verificando Placeholder..." -ForegroundColor Yellow
$placeholderExists = Test-Path "$baseDir\storage\app\public\products\product-placeholder.png"
Add-Check "Imagem placeholder existe" $placeholderExists "product-placeholder.png"

# Verificação 6: Configuração APP_URL
Write-Host "[6/7] Verificando APP_URL..." -ForegroundColor Yellow
$envFile = "$baseDir\.env"
if (Test-Path $envFile) {
    $appUrl = (Get-Content $envFile | Select-String "APP_URL=").ToString().Split("=")[1]
    Add-Check "APP_URL configurado" ($appUrl -ne $null) $appUrl
} else {
    Add-Check "APP_URL configurado" $false "Arquivo .env não encontrado"
}

# Verificação 7: Model Product
Write-Host "[7/7] Verificando Model Product..." -ForegroundColor Yellow
$modelFile = "$baseDir\app\Models\Product.php"
if (Test-Path $modelFile) {
    $modelContent = Get-Content $modelFile -Raw
    $hasAccessor = $modelContent -match "getImageUrlAttribute"
    Add-Check "Accessor image_url no Model" $hasAccessor
} else {
    Add-Check "Accessor image_url no Model" $false "Model não encontrado"
}

# Resumo
Write-Host "`n==================================================" -ForegroundColor Cyan
Write-Host "  RESUMO DA VERIFICAÇÃO" -ForegroundColor Cyan
Write-Host "==================================================" -ForegroundColor Cyan

$passed = ($checks | Where-Object { $_.Result -eq $true }).Count
$failed = ($checks | Where-Object { $_.Result -eq $false }).Count
$total = $checks.Count

Write-Host "`nTestes Passados: $passed/$total" -ForegroundColor Green
if ($failed -gt 0) {
    Write-Host "Testes Falhados: $failed/$total" -ForegroundColor Red
}

# Ações recomendadas
if ($failed -gt 0) {
    Write-Host "`n==================================================" -ForegroundColor Yellow
    Write-Host "  AÇÕES RECOMENDADAS" -ForegroundColor Yellow
    Write-Host "==================================================" -ForegroundColor Yellow
    
    if (-not $linkExists -or -not $productsViaLink) {
        Write-Host "`n⚠ Recriar link simbólico:" -ForegroundColor Yellow
        Write-Host "  cd $baseDir" -ForegroundColor Gray
        Write-Host "  php artisan storage:link" -ForegroundColor Cyan
    }
    
    if (-not $placeholderExists) {
        Write-Host "`n⚠ Criar imagem placeholder (recomendado):" -ForegroundColor Yellow
        Write-Host "  Adicione uma imagem 500x500px em:" -ForegroundColor Gray
        Write-Host "  storage/app/public/products/product-placeholder.png" -ForegroundColor Cyan
    }
    
    if (-not $hasAccessor) {
        Write-Host "`n⚠ Adicionar accessor ao Model Product:" -ForegroundColor Yellow
        Write-Host "  Veja: docs/product-images-system.md" -ForegroundColor Cyan
    }
} else {
    Write-Host "`n✅ SISTEMA DE IMAGENS ESTÁ FUNCIONANDO CORRETAMENTE!" -ForegroundColor Green
}

# Informações adicionais
Write-Host "`n==================================================" -ForegroundColor Cyan
Write-Host "  RECURSOS ÚTEIS" -ForegroundColor Cyan
Write-Host "==================================================" -ForegroundColor Cyan

Write-Host "`n📚 Documentação:" -ForegroundColor Cyan
Write-Host "  docs/product-images-system.md" -ForegroundColor Gray

Write-Host "`n🧪 Página de Teste:" -ForegroundColor Cyan
Write-Host "  http://localhost:8000/test-images.php" -ForegroundColor Gray

Write-Host "`n📝 Resumo da Correção:" -ForegroundColor Cyan
Write-Host "  CORRECAO-IMAGENS-PRODUTOS.md" -ForegroundColor Gray

Write-Host "`n"
