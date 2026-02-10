<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Teste de Imagens - FlowManager</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
            background: #f5f5f5;
        }
        .container {
            background: white;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        h1 {
            color: #333;
            border-bottom: 3px solid #4CAF50;
            padding-bottom: 10px;
        }
        .test-section {
            margin: 30px 0;
            padding: 20px;
            background: #f9f9f9;
            border-left: 4px solid #2196F3;
        }
        .test-section h2 {
            color: #2196F3;
            margin-top: 0;
        }
        .image-test {
            display: inline-block;
            margin: 10px;
            padding: 10px;
            background: white;
            border: 1px solid #ddd;
            border-radius: 5px;
        }
        .image-test img {
            max-width: 200px;
            max-height: 200px;
            display: block;
            margin-bottom: 10px;
        }
        .image-test p {
            margin: 5px 0;
            font-size: 12px;
            color: #666;
        }
        .status {
            display: inline-block;
            padding: 5px 10px;
            border-radius: 3px;
            font-weight: bold;
            margin: 5px 0;
        }
        .status.ok {
            background: #4CAF50;
            color: white;
        }
        .status.error {
            background: #f44336;
            color: white;
        }
        .info-box {
            background: #E3F2FD;
            border: 1px solid #2196F3;
            padding: 15px;
            border-radius: 5px;
            margin: 20px 0;
        }
        .info-box strong {
            color: #1976D2;
        }
        pre {
            background: #263238;
            color: #AECBFA;
            padding: 15px;
            border-radius: 5px;
            overflow-x: auto;
        }
        .check-item {
            padding: 10px;
            margin: 10px 0;
            background: white;
            border-radius: 5px;
            border-left: 4px solid #4CAF50;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>🖼️ Teste de Sistema de Imagens - FlowManager</h1>
        
        <div class="info-box">
            <strong>📍 Localização deste arquivo:</strong> 
            <code><?php echo __FILE__; ?></code><br>
            <strong>🌐 URL Base da Aplicação:</strong> 
            <code><?php echo env('APP_URL', 'http://localhost:8000'); ?></code>
        </div>

        <div class="test-section">
            <h2>✅ Verificações do Sistema</h2>
            
            <?php
            $checks = [
                'Link Simbólico Existe' => file_exists(public_path('storage')),
                'Pasta products/ acessível' => file_exists(public_path('storage/products')),
                'Pasta física existe' => file_exists(storage_path('app/public/products')),
                'Placeholder existe' => file_exists(storage_path('app/public/products/product-placeholder.png')),
            ];

            foreach ($checks as $check => $result) {
                $statusClass = $result ? 'ok' : 'error';
                $statusText = $result ? '✓ OK' : '✗ FALHOU';
                echo "<div class='check-item'>";
                echo "<span class='status $statusClass'>$statusText</span> ";
                echo "<strong>$check</strong>";
                echo "</div>";
            }
            ?>
        </div>

        <div class="test-section">
            <h2>📂 Arquivos na Pasta products/</h2>
            <?php
            $productsPath = storage_path('app/public/products');
            if (is_dir($productsPath)) {
                $files = array_slice(scandir($productsPath), 2, 10); // Pega até 10 arquivos
                
                if (empty($files)) {
                    echo "<p><em>Nenhum arquivo encontrado na pasta.</em></p>";
                } else {
                    echo "<p>Encontrados " . count(scandir($productsPath) - 2) . " arquivo(s). Mostrando os primeiros 10:</p>";
                    echo "<ul>";
                    foreach ($files as $file) {
                        if ($file !== '.' && $file !== '..') {
                            $filePath = $productsPath . '/' . $file;
                            $fileSize = filesize($filePath);
                            echo "<li><strong>$file</strong> - " . number_format($fileSize / 1024, 2) . " KB</li>";
                        }
                    }
                    echo "</ul>";
                }
            } else {
                echo "<p class='status error'>Pasta não existe!</p>";
            }
            ?>
        </div>

        <div class="test-section">
            <h2>🔍 Teste de Carregamento de Imagens</h2>
            <p>Testando as primeiras 5 imagens encontradas:</p>
            
            <?php
            if (is_dir($productsPath)) {
                $imageFiles = array_filter(
                    array_slice(scandir($productsPath), 2, 5),
                    function($file) use ($productsPath) {
                        $ext = strtolower(pathinfo($file, PATHINFO_EXTENSION));
                        return in_array($ext, ['jpg', 'jpeg', 'png', 'gif', 'webp']);
                    }
                );

                if (empty($imageFiles)) {
                    echo "<p><em>Nenhuma imagem encontrada para testar.</em></p>";
                } else {
                    foreach ($imageFiles as $imageFile) {
                        $imageUrl = asset('storage/products/' . $imageFile);
                        echo "<div class='image-test'>";
                        echo "<img src='$imageUrl' alt='$imageFile' onerror=\"this.style.border='3px solid red'; this.alt='ERRO AO CARREGAR';\">";
                        echo "<p><strong>Arquivo:</strong> $imageFile</p>";
                        echo "<p><strong>URL:</strong><br><code style='font-size: 10px;'>$imageUrl</code></p>";
                        echo "</div>";
                    }
                }
            }
            ?>
        </div>

        <div class="test-section">
            <h2>🛠️ Comandos Úteis</h2>
            
            <h3>Recriar Link Simbólico:</h3>
            <pre>php artisan storage:link</pre>

            <h3>Verificar Link no Windows PowerShell:</h3>
            <pre>Get-Item "public/storage" | Select-Object FullName, LinkType, Target</pre>

            <h3>Limpar Cache de Configuração:</h3>
            <pre>php artisan config:clear
php artisan config:cache</pre>

            <h3>Verificar Permissões (Linux/Mac):</h3>
            <pre>chmod -R 755 storage
chmod -R 755 public/storage</pre>
        </div>

        <div class="test-section">
            <h2>📝 Exemplo de Uso no Blade</h2>
            
            <h3>Método 1: Usando o Accessor (Recomendado)</h3>
            <pre>&lt;img src="{{ $product-&gt;image_url }}" alt="{{ $product-&gt;name }}"&gt;</pre>

            <h3>Método 2: Usando o Helper asset()</h3>
            <pre>&lt;img src="{{ asset('storage/products/' . $product-&gt;image) }}" alt="{{ $product-&gt;name }}"&gt;</pre>

            <h3>Método 3: Com Fallback</h3>
            <pre>&lt;img src="{{ $product-&gt;image_url }}" 
     alt="{{ $product-&gt;name }}"
     onerror="this.src='{{ asset('storage/products/product-placeholder.png') }}'"&gt;</pre>
        </div>

        <div class="info-box">
            <strong>✅ Sistema Corrigido em:</strong> 08/02/2026<br>
            <strong>📚 Documentação:</strong> <code>docs/product-images-system.md</code><br>
            <strong>🔧 Correções Aplicadas:</strong>
            <ul>
                <li>Link simbólico recriado com <code>php artisan storage:link</code></li>
                <li>Accessor <code>image_url</code> adicionado ao Model Product</li>
                <li>Documentação completa criada</li>
            </ul>
        </div>
    </div>

    <script>
        // Adiciona listeners para detectar erros de carregamento de imagem
        document.addEventListener('DOMContentLoaded', function() {
            const images = document.querySelectorAll('img');
            images.forEach(img => {
                img.addEventListener('load', function() {
                    console.log('✓ Imagem carregada:', this.src);
                });
                img.addEventListener('error', function() {
                    console.error('✗ Erro ao carregar:', this.src);
                    this.parentElement.style.border = '2px solid red';
                });
            });
        });
    </script>
</body>
</html>
