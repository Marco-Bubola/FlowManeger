import { exec, spawn } from 'child_process';
import { fileURLToPath } from 'url';
import http from 'http';

// Parser simples de flags
const args = process.argv.slice(2);
const dryRun = args.includes('--dry-run');
const build = args.includes('--build');
const skipMigrate = args.includes('--skip-migrate');
const noBrowser = args.includes('--no-browser');
const fullSetup = args.includes('--full-setup');
// Por padrão, usa modo server-only, exceto se --full-setup for especificado
const serverOnly = !fullSetup;

const LARAVEL_URL = 'http://127.0.0.1:8000';
const LARAVEL_PORT = 8000;

function logHeader() {
  console.log('=========================================');
  console.log('FlowManeger start script');
  console.log(`cwd: ${process.cwd()}`);
  console.log(`node: ${process.version}`);
  console.log(`options: ${args.join(' ')}`);
  console.log('=========================================');
}

// Monta a lista de comandos com base nas flags (sync commands)
function buildSyncCommands() {
  const cmds = [];

  // Se for modo servidor apenas (padrão), pular todas as instalações e migrações
  if (serverOnly) {
    console.log('🚀 Modo servidor apenas (padrão) - pulando instalações e migrações');
    console.log('💡 Para executar instalação completa use: --full-setup');
    return cmds; // Retorna array vazio
  }

  cmds.push('composer install');
  cmds.push('npm install');
  cmds.push('php artisan key:generate');
  if (!skipMigrate) cmds.push('php artisan migrate --seed');
  if (build) cmds.push('npm run build');
  return cmds;
}

function runCommand(command) {
  return new Promise((resolve, reject) => {
    console.log(`\n-> Executando: ${command}`);
    if (dryRun) {
      console.log(`[dry-run] ${command}`);
      return resolve(0);
    }

    const proc = exec(command, { cwd: process.cwd() });

    proc.stdout.on('data', (d) => process.stdout.write(d));
    proc.stderr.on('data', (d) => process.stderr.write(d));

    proc.on('close', (code) => {
      if (code === 0) return resolve(0);
      const err = new Error(`Command failed: ${command} (exit ${code})`);
      err.code = code;
      return reject(err);
    });
    proc.on('error', (err) => reject(err));
  });
}

// Roda comando em background (não aguarda finalização)
function runBackground(command, label) {
  console.log(`\n-> Iniciando em background: ${command}`);
  if (dryRun) {
    console.log(`[dry-run] ${command}`);
    return null;
  }

  const parts = command.split(' ');
  const cmd = parts[0];
  const cmdArgs = parts.slice(1);

  const proc = spawn(cmd, cmdArgs, {
    cwd: process.cwd(),
    detached: false,
    stdio: 'inherit',
    shell: true
  });

  proc.on('error', (err) => {
    console.error(`Erro ao iniciar ${label}:`, err);
  });

  console.log(`${label} iniciado (PID: ${proc.pid})`);
  return proc;
}

// Aguarda o servidor Laravel estar pronto
function waitForServer(url, maxAttempts = 30, interval = 1000) {
  return new Promise((resolve, reject) => {
    let attempts = 0;
    console.log(`\nAguardando servidor estar pronto em ${url}...`);

    const check = () => {
      attempts++;
      http.get(url, (res) => {
        if (res.statusCode === 200 || res.statusCode === 302) {
          console.log(`✓ Servidor pronto! (tentativa ${attempts})`);
          resolve();
        } else {
          retry();
        }
      }).on('error', () => {
        retry();
      });
    };

    const retry = () => {
      if (attempts >= maxAttempts) {
        reject(new Error(`Servidor não ficou pronto após ${maxAttempts} tentativas`));
      } else {
        setTimeout(check, interval);
      }
    };

    check();
  });
}

// Abre o navegador (Chrome prioritário, fallback para default)
function openBrowser(url) {
  console.log(`\n-> Abrindo navegador: ${url}`);
  if (dryRun) {
    console.log(`[dry-run] abrir navegador em ${url}`);
    return;
  }

  // Tenta Chrome primeiro, depois default
  const commands = [
    `start chrome "${url}"`,
    `start "" "${url}"`
  ];

  exec(commands[0], (err) => {
    if (err) {
      console.log('Chrome não encontrado, abrindo navegador padrão...');
      exec(commands[1]);
    }
  });
}

async function runAll() {
  logHeader();

  // 1. Executar comandos síncronos (composer, npm install, etc)
  const syncCommands = buildSyncCommands();
  if (syncCommands.length > 0) {
    console.log('\n=== FASE 1: Instalando dependências ===');
    for (let i = 0; i < syncCommands.length; i++) {
      const cmd = syncCommands[i];
      try {
        await runCommand(cmd);
      } catch (err) {
        console.error(`\nErro ao executar comando: ${cmd}`);
        console.error(err.message || err);
        process.exit(err.code || 1);
      }
    }
  } else {
    console.log('\n=== FASE 1: Pulando instalação de dependências (modo servidor apenas) ===');
  }

  // 2. Iniciar Vite em background (se não for build)
  let viteProc = null;
  if (!build && !dryRun) {
    viteProc = runBackground('npm run dev', 'Vite');
    // Aguardar um pouco para Vite iniciar
    await new Promise(resolve => setTimeout(resolve, 2000));
  }

  // 3. Iniciar servidor Laravel em background
  const serverProc = runBackground('php artisan serve', 'Laravel Server');

  // 4. Aguardar servidor estar pronto
  if (!dryRun) {
    try {
      await waitForServer(LARAVEL_URL);
    } catch (err) {
      console.error('\nErro:', err.message);
      process.exit(1);
    }
  }

  // 5. Abrir navegador
  if (!noBrowser) {
    openBrowser(LARAVEL_URL);
  }

  console.log('\n=========================================');
  console.log('✓ Projeto iniciado com sucesso!');
  console.log(`✓ Laravel: ${LARAVEL_URL}`);
  if (!build) console.log('✓ Vite: http://localhost:5173');
  console.log('\nPressione Ctrl+C para encerrar os servidores.');
  console.log('=========================================\n');

  // Manter processo vivo e aguardar Ctrl+C
  process.on('SIGINT', () => {
    console.log('\n\nEncerrando servidores...');
    if (viteProc) viteProc.kill();
    if (serverProc) serverProc.kill();
    process.exit(0);
  });
}

// Entrypoint para ESM
const __filename = fileURLToPath(import.meta.url);
if (process.argv[1] === __filename) {
  runAll().catch((err) => {
    console.error(err);
    process.exit(1);
  });
}

export { buildSyncCommands, runCommand };
