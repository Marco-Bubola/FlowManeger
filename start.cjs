const { exec, spawn } = require('child_process');
const http = require('http');
const path = require('path');
const fs = require('fs');

// Parser simples de flags
const args = process.argv.slice(2);
const dryRun = args.includes('--dry-run');
const build = args.includes('--build');
const skipMigrate = args.includes('--skip-migrate');
const skipSeed = args.includes('--skip-seed');
const noBrowser = args.includes('--no-browser');

const LARAVEL_URL = 'http://127.0.0.1:8000';
const LARAVEL_PORT = 8000;

// === SISTEMA DE LOGGING ===
const LOG_DIR = path.join(process.cwd(), 'logs');
const LOG_FILE = path.join(LOG_DIR, 'start.log');

// Criar diretório de logs se não existir
if (!fs.existsSync(LOG_DIR)) {
  fs.mkdirSync(LOG_DIR, { recursive: true });
}

// Limpar log antigo ou criar novo
fs.writeFileSync(LOG_FILE, '');

function getTimestamp() {
  return new Date().toISOString().replace('T', ' ').substring(0, 19);
}

function log(message, level = 'INFO') {
  const timestamp = getTimestamp();
  const logLine = `[${timestamp}] [${level}] ${message}\n`;

  // Escrever no arquivo
  fs.appendFileSync(LOG_FILE, logLine);

  // Escrever no console
  if (level === 'ERROR') {
    console.error(message);
  } else if (level === 'WARN') {
    console.warn(message);
  } else {
    console.log(message);
  }
}

function logSeparator() {
  const line = '='.repeat(60);
  log(line);
}

// Capturar erros não tratados
process.on('uncaughtException', (err) => {
  log(`ERRO NÃO TRATADO: ${err.message}`, 'ERROR');
  log(err.stack || '', 'ERROR');
  pauseBeforeExit(1);
});

process.on('unhandledRejection', (reason, promise) => {
  log(`PROMISE REJEITADA: ${reason}`, 'ERROR');
  pauseBeforeExit(1);
});

// Pausa antes de sair (para ver erros)
function pauseBeforeExit(code = 0) {
  log(`\n${'='.repeat(60)}`);
  if (code !== 0) {
    log('ERRO DETECTADO! Verifique o log em: logs/start.log', 'ERROR');
  }
  log('Pressione qualquer tecla para fechar esta janela...');
  log(`${'='.repeat(60)}`);

  // Aguardar entrada do usuário
  if (process.stdin.isTTY) {
    process.stdin.setRawMode(true);
    process.stdin.resume();
    process.stdin.once('data', () => {
      process.exit(code);
    });
  } else {
    setTimeout(() => process.exit(code), 30000); // 30s timeout se não for TTY
  }
}

function logHeader() {
  logSeparator();
  log('FlowManeger start script');
  log(`cwd: ${process.cwd()}`);
  log(`node: ${process.version}`);
  log(`options: ${args.join(' ') || 'nenhuma'}`);
  log(`log file: ${LOG_FILE}`);
  logSeparator();
}

// Monta a lista de comandos com base nas flags (sync commands)
function buildSyncCommands() {
  const cmds = [];
  cmds.push('composer install');
  cmds.push('npm install');
  cmds.push('php artisan key:generate');

  // Migrations e seeds
  if (!skipMigrate) {
    if (skipSeed) {
      cmds.push('php artisan migrate');
    } else {
      cmds.push('php artisan migrate --seed');
    }
  }

  if (build) cmds.push('npm run build');
  return cmds;
}

function runCommand(command) {
  return new Promise((resolve, reject) => {
    log(`\n-> Executando: ${command}`);
    if (dryRun) {
      log(`[dry-run] ${command}`);
      return resolve(0);
    }

    const proc = exec(command, { cwd: process.cwd() });

    proc.stdout.on('data', (d) => {
      const text = d.toString();
      process.stdout.write(d);
      fs.appendFileSync(LOG_FILE, `[${getTimestamp()}] [STDOUT] ${text}`);
    });

    proc.stderr.on('data', (d) => {
      const text = d.toString();
      process.stderr.write(d);
      fs.appendFileSync(LOG_FILE, `[${getTimestamp()}] [STDERR] ${text}`);
      // Detectar warnings
      if (text.toLowerCase().includes('warning')) {
        log(`⚠ Warning detectado em: ${command}`, 'WARN');
      }
    });

    proc.on('close', (code) => {
      if (code === 0) {
        log(`✓ Comando finalizado com sucesso: ${command}`);
        return resolve(0);
      }
      const err = new Error(`Command failed: ${command} (exit code ${code})`);
      err.code = code;
      log(`✗ Comando falhou: ${command} (exit code ${code})`, 'ERROR');
      return reject(err);
    });

    proc.on('error', (err) => {
      log(`✗ Erro ao executar: ${command} - ${err.message}`, 'ERROR');
      reject(err);
    });
  });
}

// Roda comando em background (não aguarda finalização)
function runBackground(command, label) {
  log(`\n-> Iniciando em background: ${command}`);
  if (dryRun) {
    log(`[dry-run] ${command}`);
    return null;
  }

  const parts = command.split(' ');
  const cmd = parts[0];
  const cmdArgs = parts.slice(1);

  const proc = spawn(cmd, cmdArgs, {
    cwd: process.cwd(),
    detached: false,
    stdio: ['ignore', 'pipe', 'pipe'],
    shell: true
  });

  // Capturar stdout/stderr dos processos background
  if (proc.stdout) {
    proc.stdout.on('data', (d) => {
      const text = d.toString();
      process.stdout.write(d);
      fs.appendFileSync(LOG_FILE, `[${getTimestamp()}] [${label}] ${text}`);
    });
  }

  if (proc.stderr) {
    proc.stderr.on('data', (d) => {
      const text = d.toString();
      process.stderr.write(d);
      fs.appendFileSync(LOG_FILE, `[${getTimestamp()}] [${label}-ERR] ${text}`);
    });
  }

  proc.on('error', (err) => {
    log(`✗ Erro ao iniciar ${label}: ${err.message}`, 'ERROR');
  });

  proc.on('exit', (code, signal) => {
    if (code !== null && code !== 0) {
      log(`⚠ ${label} encerrado com código ${code}`, 'WARN');
    } else if (signal) {
      log(`⚠ ${label} encerrado com sinal ${signal}`, 'WARN');
    }
  });

  log(`✓ ${label} iniciado (PID: ${proc.pid})`);
  return proc;
}

// Aguarda o servidor Laravel estar pronto
function waitForServer(url, maxAttempts = 30, interval = 1000) {
  return new Promise((resolve, reject) => {
    let attempts = 0;
    log(`\nAguardando servidor estar pronto em ${url}...`);

    const check = () => {
      attempts++;
      http.get(url, (res) => {
        if (res.statusCode === 200 || res.statusCode === 302) {
          log(`✓ Servidor pronto! (tentativa ${attempts})`);
          resolve();
        } else {
          log(`⚠ Tentativa ${attempts}: servidor retornou status ${res.statusCode}`, 'WARN');
          retry();
        }
      }).on('error', (err) => {
        if (attempts === 1 || attempts % 5 === 0) {
          log(`⏳ Tentativa ${attempts}/${maxAttempts}: aguardando servidor... (${err.code || 'erro'})`, 'INFO');
        }
        retry();
      });
    };

    const retry = () => {
      if (attempts >= maxAttempts) {
        const msg = `Servidor não ficou pronto após ${maxAttempts} tentativas (${maxAttempts}s)`;
        log(msg, 'ERROR');
        reject(new Error(msg));
      } else {
        setTimeout(check, interval);
      }
    };

    check();
  });
}

// Abre o navegador (Chrome prioritário, fallback para default)
function openBrowser(url) {
  log(`\n-> Abrindo navegador: ${url}`);
  if (dryRun) {
    log(`[dry-run] abrir navegador em ${url}`);
    return;
  }

  // Tenta Chrome primeiro, depois default
  const commands = [
    `start chrome "${url}"`,
    `start "" "${url}"`
  ];

  exec(commands[0], (err) => {
    if (err) {
      log('Chrome não encontrado, abrindo navegador padrão...', 'WARN');
      exec(commands[1], (err2) => {
        if (err2) {
          log(`Erro ao abrir navegador: ${err2.message}`, 'ERROR');
        }
      });
    } else {
      log('✓ Chrome aberto com sucesso');
    }
  });
}

async function runAll() {
  logHeader();

  try {
    // 1. Executar comandos síncronos (composer, npm install, etc)
    log('\n=== FASE 1: Instalando dependências ===');
    const syncCommands = buildSyncCommands();
    for (let i = 0; i < syncCommands.length; i++) {
      const cmd = syncCommands[i];
      try {
        await runCommand(cmd);
      } catch (err) {
        log(`\n✗ ERRO ao executar comando: ${cmd}`, 'ERROR');
        log(err.message || err, 'ERROR');
        log(`\nVerifique o log completo em: ${LOG_FILE}`, 'ERROR');
        pauseBeforeExit(err.code || 1);
        return;
      }
    }

    // 2. Iniciar Vite em background (se não for build)
    log('\n=== FASE 2: Iniciando servidores ===');
    let viteProc = null;
    if (!build && !dryRun) {
      viteProc = runBackground('npm run dev', 'Vite');
      // Aguardar um pouco para Vite iniciar
      log('Aguardando Vite inicializar...');
      await new Promise(resolve => setTimeout(resolve, 3000));
    }

    // 3. Iniciar servidor Laravel em background
    const serverProc = runBackground('php artisan serve', 'Laravel Server');

    // 4. Aguardar servidor estar pronto
    if (!dryRun) {
      log('\n=== FASE 3: Aguardando servidor ===');
      try {
        await waitForServer(LARAVEL_URL);
      } catch (err) {
        log(`\n✗ ERRO: ${err.message}`, 'ERROR');
        log('\nPossíveis causas:', 'ERROR');
        log('- PHP não está instalado ou não está no PATH', 'ERROR');
        log('- Porta 8000 já está em uso', 'ERROR');
        log('- Erro no Laravel (verifique .env e configurações)', 'ERROR');
        log(`\nVerifique o log completo em: ${LOG_FILE}`, 'ERROR');

        // Encerrar processos antes de sair
        if (viteProc) viteProc.kill();
        if (serverProc) serverProc.kill();

        pauseBeforeExit(1);
        return;
      }
    }

    // 5. Abrir navegador
    log('\n=== FASE 4: Abrindo navegador ===');
    if (!noBrowser) {
      openBrowser(LARAVEL_URL);
    }

    logSeparator();
    log('✓ Projeto iniciado com sucesso!');
    log(`✓ Laravel: ${LARAVEL_URL}`);
    if (!build) log('✓ Vite: http://localhost:5173');
    log(`\n📝 Log completo: ${LOG_FILE}`);
    log('\n⚠ IMPORTANTE: Mantenha esta janela aberta!');
    log('   Os servidores estão rodando nesta janela.');
    log('   Pressione Ctrl+C para encerrar os servidores.');
    logSeparator();

    // Manter processo vivo e aguardar Ctrl+C
    process.on('SIGINT', () => {
      log('\n\nEncerrando servidores...');
      if (viteProc) {
        log('Encerrando Vite...');
        viteProc.kill();
      }
      if (serverProc) {
        log('Encerrando Laravel...');
        serverProc.kill();
      }
      log('✓ Servidores encerrados');
      process.exit(0);
    });

    // Manter vivo indefinidamente
    await new Promise(() => {});

  } catch (err) {
    log(`\n✗ ERRO INESPERADO: ${err.message}`, 'ERROR');
    log(err.stack || '', 'ERROR');
    log(`\nVerifique o log completo em: ${LOG_FILE}`, 'ERROR');
    pauseBeforeExit(1);
  }
}

// Entrypoint
if (require.main === module) {
  runAll().catch((err) => {
    log(`✗ ERRO FATAL: ${err.message}`, 'ERROR');
    log(err.stack || '', 'ERROR');
    log(`\nVerifique o log completo em: ${LOG_FILE}`, 'ERROR');
    pauseBeforeExit(1);
  });
}

module.exports = { buildSyncCommands, runCommand };
