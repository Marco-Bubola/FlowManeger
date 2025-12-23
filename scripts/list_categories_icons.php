<?php
// Script para listar categorias e status de ícone (rodar com `php scripts/list_categories_icons.php`)

$base = __DIR__ . '/../';

// carregar .env simples
function parseEnv($path) {
    if (!file_exists($path)) return [];
    $lines = file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    $data = [];
    foreach ($lines as $line) {
        if (strpos(trim($line), '#') === 0) continue;
        if (!str_contains($line, '=')) continue;
        [$k,$v] = explode('=', $line, 2);
        $data[trim($k)] = trim($v);
    }
    return $data;
}

$env = parseEnv($base . '.env');

// extrair classes do CSS
$cssPath = $base . 'public/assets/css/icon-category.css';
$availableIcons = [];
if (file_exists($cssPath)) {
    $css = file_get_contents($cssPath);
    if (preg_match_all('/\.([a-zA-Z0-9\-_]+)\s*\{/', $css, $m)) {
        $availableIcons = array_values(array_unique($m[1]));
    }
}

// conectar ao DB via PDO conforme .env
$driver = $env['DB_CONNECTION'] ?? $env['DB_DRIVER'] ?? 'sqlite';
try {
    if ($driver === 'sqlite') {
        $dbfile = $env['DB_DATABASE'] ?? $base . 'database/database.sqlite';
        if ($dbfile === ':memory:') {
            fwrite(STDERR, "SQLite in-memory not supported by this script.\n");
            exit(1);
        }
        $dsn = "sqlite:" . ($dbfile[0] === '/' || preg_match('/^[A-Za-z]:\\\\/', $dbfile) ? $dbfile : $base . $dbfile);
        $pdo = new PDO($dsn);
    } else {
        $host = $env['DB_HOST'] ?? '127.0.0.1';
        $port = $env['DB_PORT'] ?? ($driver === 'pgsql' ? 5432 : 3306);
        $database = $env['DB_DATABASE'] ?? '';
        $user = $env['DB_USERNAME'] ?? '';
        $pass = $env['DB_PASSWORD'] ?? '';
        if ($driver === 'pgsql') {
            $dsn = "pgsql:host=$host;port=$port;dbname=$database";
        } else {
            $dsn = "mysql:host=$host;port=$port;dbname=$database;charset=utf8mb4";
        }
        $pdo = new PDO($dsn, $user, $pass, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        ]);
    }
} catch (Exception $e) {
    fwrite(STDERR, "Erro ao conectar ao DB: " . $e->getMessage() . "\n");
    exit(1);
}

// tentar descobrir a tabela de categorias automaticamente
$commonNames = ['categories','category','categorias','categoria','cats','cat','app_categories'];
$tables = [];
try {
    // listar tabelas dependendo do driver
    if (stripos($driver, 'sqlite') !== false) {
        $res = $pdo->query("SELECT name FROM sqlite_master WHERE type='table'")->fetchAll(PDO::FETCH_COLUMN);
        $tables = $res ?: [];
    } else {
        // MySQL / MariaDB / Pgsql
        if (stripos($driver, 'pgsql') !== false) {
            $sth = $pdo->query("SELECT tablename AS name FROM pg_tables WHERE schemaname NOT IN ('pg_catalog','information_schema')");
            $tables = $sth->fetchAll(PDO::FETCH_COLUMN);
        } else {
            // mysql
            $dbname = $env['DB_DATABASE'] ?? null;
            if ($dbname) {
                $sth = $pdo->query("SELECT table_name AS name FROM information_schema.tables WHERE table_schema='" . addslashes($dbname) . "'");
                $tables = $sth->fetchAll(PDO::FETCH_COLUMN);
            }
        }
    }
} catch (Exception $e) {
    fwrite(STDERR, "Erro ao listar tabelas: " . $e->getMessage() . "\n");
}

$found = null;
foreach ($commonNames as $c) {
    foreach ($tables as $t) {
        if (strcasecmp($t, $c) === 0) {
            $found = $t;
            break 2;
        }
    }
}

if (!$found) {
    // tentar pegar a tabela mais parecida (contains)
    foreach ($tables as $t) {
        foreach ($commonNames as $c) {
            if (stripos($t, $c) !== false) {
                $found = $t;
                break 2;
            }
        }
    }
}

if (!$found) {
    fwrite(STDERR, "Não foi possível localizar automaticamente uma tabela de categorias. Tabelas encontradas:\n");
    foreach ($tables as $t) {
        fwrite(STDERR, " - $t\n");
    }
    fwrite(STDERR, "\nSe sua tabela tem outro nome, rode o script ajustando a variável \$table.\n");
    exit(1);
}

$table = $found;

// detectar colunas disponíveis na tabela
$columns = [];
try {
    if (stripos($driver, 'sqlite') !== false) {
        $res = $pdo->query("PRAGMA table_info('$table')")->fetchAll(PDO::FETCH_ASSOC);
        foreach ($res as $row) $columns[] = $row['name'];
    } elseif (stripos($driver, 'pgsql') !== false) {
        $sth = $pdo->prepare("SELECT column_name FROM information_schema.columns WHERE table_name = :table");
        $sth->execute([':table' => $table]);
        $columns = $sth->fetchAll(PDO::FETCH_COLUMN);
    } else {
        $dbname = $env['DB_DATABASE'] ?? null;
        if ($dbname) {
            $sth = $pdo->prepare("SELECT COLUMN_NAME FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = :schema AND TABLE_NAME = :table");
            $sth->execute([':schema' => $dbname, ':table' => $table]);
            $columns = $sth->fetchAll(PDO::FETCH_COLUMN);
        }
    }
} catch (Exception $e) {
    fwrite(STDERR, "Aviso: não foi possível detectar colunas automaticamente: " . $e->getMessage() . "\n");
}

// candidatos prováveis para cada campo
$candidates = [
    'id' => ['id','id_category','category_id','pk','identifier'],
    'name' => ['name','nome','title','label',' descricao','descricao'],
    'type' => ['type','categoria_type','categoria','tipo','kind'],
    'icone' => ['icone','icon','icon_class','icone_class','icon_name','icone_name']
];

$selected = [];
foreach ($candidates as $key => $cands) {
    $selected[$key] = null;
    foreach ($columns as $col) {
        foreach ($cands as $cand) {
            if (strtolower($col) === strtolower(trim($cand))) {
                $selected[$key] = $col;
                break 2;
            }
        }
    }
}

// fallbacks razoáveis
if (!$selected['id'] && count($columns) > 0) $selected['id'] = $columns[0];
if (!$selected['name']) {
    foreach ($columns as $col) {
        if (stripos($col, 'name') !== false || stripos($col, 'nome') !== false || stripos($col, 'title') !== false) { $selected['name'] = $col; break; }
    }
}
if (!$selected['icone']) {
    foreach ($columns as $col) {
        if (stripos($col, 'icon') !== false || stripos($col, 'icone') !== false) { $selected['icone'] = $col; break; }
    }
}
if (!$selected['type']) {
    foreach ($columns as $col) {
        if (stripos($col, 'type') !== false || stripos($col, 'tipo') !== false || stripos($col, 'categoria') !== false) { $selected['type'] = $col; break; }
    }
}

// verificar se temos colunas mínimas
if (!$selected['id'] || (!$selected['name'] && !$selected['icone'])) {
    fwrite(STDERR, "Não foi possível determinar colunas necessárias na tabela '$table'. Colunas detectadas: \n");
    foreach ($columns as $c) fwrite(STDERR, " - $c\n");
    fwrite(STDERR, "\nAjuste manualmente o script para usar os nomes de coluna corretos.\n");
    exit(1);
}

// preparar consulta com colunas detectadas
$selectCols = [];
foreach (['id','name','type','icone'] as $k) {
    if (!empty($selected[$k])) $selectCols[$k] = $selected[$k];
}

$sql = 'SELECT ' . implode(', ', array_map(function($c){ return "$c"; }, $selectCols)) . " FROM $table";
if (!empty($selected['id'])) $sql .= " ORDER BY " . $selected['id'];

try {
    $sth = $pdo->query($sql);
    $rows = $sth->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    fwrite(STDERR, "Erro ao consultar tabela '$table' com colunas detectadas: " . $e->getMessage() . "\n");
    exit(1);
}

// montar saída
$out = [];
foreach ($rows as $r) {
    $icone = trim($r['icone'] ?? '');
    $status = 'no icon';
    if ($icone === '') {
        $status = 'no icon';
    } else {
        // se tem espaços, pegar última classe
        $parts = preg_split('/\s+/', $icone);
        $last = end($parts) ?: $icone;
        if (in_array($last, $availableIcons)) {
            $status = 'css icon';
        } elseif (str_starts_with($icone, 'fas ') || str_starts_with($icone, 'fa ') || str_starts_with($icone, 'bi-') || preg_match('/\bbi\b|\bfas\b|\bfa\b/', $icone)) {
            $status = 'font icon (fa/bi)';
        } else {
            $status = 'missing (not in css)';
        }
    }
    $out[] = [
        'id' => $r['id'] ?? '',
        'name' => $r['name'] ?? '',
        'type' => $r['type'] ?? '',
        'icone' => $icone,
        'status' => $status
    ];
}

// imprimir tabela simples
echo str_pad('ID',6) . str_pad('NAME',40) . str_pad('TYPE',20) . str_pad('ICON',40) . "STATUS\n";
echo str_repeat('-', 120) . "\n";
foreach ($out as $o) {
    echo str_pad($o['id'],6) . str_pad(mb_strimwidth($o['name'],0,38,'..'),40) . str_pad(mb_strimwidth($o['type'],0,18,'..'),20) . str_pad(mb_strimwidth($o['icone'],0,38,'..'),40) . $o['status'] . "\n";
}

// também listar ícones faltantes únicos
$missing = [];
foreach ($out as $o) {
    if ($o['status'] === 'missing (not in css)' || $o['status'] === 'no icon') {
        if ($o['icone']) $missing[] = $o['icone'];
    }
}
$missing = array_values(array_unique($missing));
if (!empty($missing)) {
    echo "\nÍcones referenciados não presentes no CSS:\n";
    foreach ($missing as $m) echo " - $m\n";
}

echo "\nTotal categorias: " . count($out) . "\n";

exit(0);
