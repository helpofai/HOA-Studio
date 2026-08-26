<?php
/*
|--------------------------------------------------------------------------
| HelpOfAi (HOA) Professional Software - Advanced Web Installer
|--------------------------------------------------------------------------
|
| Copyright (c) 2026 Rajib Adhikary. All Rights Reserved.
|
| Author      : Rajib Adhikary
| Organization: HelpOfAi (HOA)
| Website     : https://helpofai.com
| Location    : Basta Purba Para, Aranghata, Nadia, West Bengal, India
|
| Standalone multi-step installer. No Laravel dependency required.
| Auto-detects: main domain, subdomain, or subdirectory deployment.
|
|--------------------------------------------------------------------------
*/

declare(strict_types=1);

// ── Path Constants ──────────────────────────────────────────────────────────
define('HOA_PUBLIC',    __DIR__);
define('HOA_ROOT',      realpath(__DIR__ . '/..'));
define('HOA_STORAGE',   HOA_ROOT . '/storage');
define('HOA_ENV_FILE',  HOA_ROOT . '/.env');
define('HOA_ENV_EXMPL', HOA_ROOT . '/.env.example');
define('HOA_INSTALLED', HOA_STORAGE . '/framework/installed');
define('HOA_ARTISAN',   HOA_ROOT . '/artisan');
define('HOA_VERSION',   '1.0.0');

// ── Session (isolated from app session) ────────────────────────────────────
ini_set('session.name', 'hoa_installer');
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// ── Already Installed Guard ─────────────────────────────────────────────────
if (file_exists(HOA_INSTALLED) && !isset($_GET['reinstall'])) {
    $appUrl = _readEnvKey('APP_URL') ?: '/';
    _renderInstalledPage($appUrl);
    exit;
}

// ── Helpers ─────────────────────────────────────────────────────────────────
function _readEnvKey(string $key, string $file = HOA_ENV_FILE): string {
    if (!file_exists($file)) return '';
    foreach (file($file) as $line) {
        $line = trim($line);
        if (str_starts_with($line, $key . '=')) {
            return trim(substr($line, strlen($key) + 1), " \t\"'");
        }
    }
    return '';
}

/**
 * Auto-detect APP_URL and deployment type from the installer's own URL.
 *
 * Examples:
 *   https://helpofai.com/install.php          → main domain, APP_URL = https://helpofai.com
 *   https://studio.helpofai.com/install.php   → subdomain,   APP_URL = https://studio.helpofai.com
 *   https://helpofai.com/studio/install.php   → subdirectory,APP_URL = https://helpofai.com/studio
 */
function _detectConfig(): array {
    $https    = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
               || ($_SERVER['HTTP_X_FORWARDED_PROTO'] ?? '') === 'https'
               || ($_SERVER['SERVER_PORT'] ?? '') === '443';
    $protocol = $https ? 'https' : 'http';
    $host     = $_SERVER['HTTP_HOST'] ?? 'localhost';

    // Use REQUEST_URI so it works whether accessed directly or via .htaccess rewrite
    $uri      = strtok($_SERVER['REQUEST_URI'] ?? '/install.php', '?');
    $dir      = rtrim(dirname($uri), '/'); // /studio  OR  ''

    // Domain type heuristic
    $hostParts  = explode('.', preg_replace('/:\d+$/', '', $host)); // strip port
    $isSub      = count($hostParts) >= 3 && !is_numeric($hostParts[0]);
    $isSub      = $isSub && !in_array($hostParts[0], ['www', 'localhost']);
    $isSubdir   = ($dir !== '' && $dir !== '/');

    $domainType = $isSubdir ? 'subdirectory' : ($isSub ? 'subdomain' : 'main');
    $appUrl     = rtrim($protocol . '://' . $host . $dir, '/');
    $urlPath    = parse_url($appUrl, PHP_URL_PATH) ?: '/';
    $sesPath    = rtrim($urlPath, '/') ?: '/';

    return [
        'app_url'      => $appUrl,
        'asset_url'    => $appUrl,
        'session_path' => $sesPath,
        'domain_type'  => $domainType,
        'base_path'    => $dir,
        'host'         => $host,
        'protocol'     => $protocol,
    ];
}

function _checkRequirements(): array {
    $checks = [];

    $checks[] = [
        'label'  => 'PHP Version ≥ 8.2',
        'ok'     => version_compare(PHP_VERSION, '8.2.0', '>='),
        'detail' => 'PHP ' . PHP_VERSION,
        'fatal'  => true,
    ];

    foreach (['pdo', 'pdo_mysql', 'mbstring', 'tokenizer', 'xml', 'ctype', 'json', 'bcmath', 'openssl', 'curl', 'fileinfo'] as $ext) {
        $ok = extension_loaded($ext);
        $checks[] = [
            'label'  => "ext: $ext",
            'ok'     => $ok,
            'detail' => $ok ? 'Loaded' : 'MISSING',
            'fatal'  => true,
        ];
    }

    $dirs = [
        HOA_STORAGE . '/framework' => 'storage/framework/ writable',
        HOA_STORAGE . '/logs'      => 'storage/logs/ writable',
        HOA_ROOT . '/bootstrap/cache' => 'bootstrap/cache/ writable',
    ];
    foreach ($dirs as $dir => $label) {
        $checks[] = [
            'label'  => $label,
            'ok'     => is_writable($dir),
            'detail' => is_writable($dir) ? 'Writable ✓' : 'Not writable — run: chmod -R 775 storage bootstrap/cache',
            'fatal'  => true,
        ];
    }

    $checks[] = [
        'label'  => '.env.example present',
        'ok'     => file_exists(HOA_ENV_EXMPL),
        'detail' => file_exists(HOA_ENV_EXMPL) ? 'Found ✓' : 'Missing — re-upload project files',
        'fatal'  => true,
    ];

    $checks[] = [
        'label'  => 'PHP exec() available (for migrations)',
        'ok'     => function_exists('exec') && !in_array('exec', array_map('trim', explode(',', ini_get('disable_functions')))),
        'detail' => 'Needed for: php artisan migrate',
        'fatal'  => false, // warn only
    ];

    return $checks;
}

function _generateKey(): string {
    return 'base64:' . base64_encode(random_bytes(32));
}

function _writeEnv(array $d): bool {
    if (!file_exists(HOA_ENV_EXMPL)) return false;
    $env = file_get_contents(HOA_ENV_EXMPL);

    // Backup existing .env
    if (file_exists(HOA_ENV_FILE)) {
        @copy(HOA_ENV_FILE, HOA_ENV_FILE . '.bak.' . date('YmdHis'));
    }

    $map = [
        'APP_NAME'       => '"' . str_replace('"', '\\"', $d['app_name'] ?? 'HOA Studio') . '"',
        'APP_ENV'        => $d['app_env'] ?? 'production',
        'APP_KEY'        => $d['app_key'] ?? _generateKey(),
        'APP_DEBUG'      => ($d['app_env'] ?? 'production') === 'local' ? 'true' : 'false',
        'APP_URL'        => $d['app_url'] ?? '',
        'ASSET_URL'      => $d['app_url'] ?? '',
        'SESSION_PATH'   => $d['session_path'] ?? '/',
        'DB_CONNECTION'  => 'mysql',
        'DB_HOST'        => $d['db_host'] ?? '127.0.0.1',
        'DB_PORT'        => $d['db_port'] ?? '3306',
        'DB_DATABASE'    => $d['db_name'] ?? '',
        'DB_USERNAME'    => $d['db_user'] ?? '',
        'DB_PASSWORD'    => '"' . str_replace('"', '\\"', $d['db_pass'] ?? '') . '"',
    ];

    foreach ($map as $key => $val) {
        // Replace KEY=anything (including quoted values) on its own line
        $env = preg_replace('/^' . preg_quote($key, '/') . '\s*=.*/m', $key . '=' . $val, $env);
        // If key didn't exist, append it
        if (!preg_match('/^' . preg_quote($key, '/') . '\s*=/m', $env)) {
            $env .= "\n$key=$val";
        }
    }

    return (bool) file_put_contents(HOA_ENV_FILE, $env);
}

function _artisan(string $cmd): array {
    if (!function_exists('exec')) {
        return ['ok' => false, 'out' => '⚠️ exec() disabled. Run manually: php artisan ' . $cmd];
    }
    $php   = PHP_BINARY ?: 'php';
    $art   = escapeshellarg(HOA_ARTISAN);
    $full  = escapeshellarg($php) . ' ' . $art . ' ' . $cmd . ' 2>&1';
    $lines = [];
    $code  = 0;
    exec($full, $lines, $code);
    return ['ok' => ($code === 0), 'out' => implode("\n", $lines)];
}

function _createAdmin(array $d): array {
    try {
        $dsn = sprintf('mysql:host=%s;port=%s;dbname=%s;charset=utf8mb4',
            $d['db_host'] ?? '127.0.0.1',
            $d['db_port'] ?? '3306',
            $d['db_name'] ?? ''
        );
        $pdo = new PDO($dsn, $d['db_user'] ?? '', $d['db_pass'] ?? '', [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        ]);

        // Check existing
        $chk = $pdo->prepare('SELECT id FROM users WHERE email = ? LIMIT 1');
        $chk->execute([$d['admin_email']]);
        if ($chk->fetchColumn()) {
            // Update to admin role if needed
            $pdo->prepare("UPDATE users SET role='admin' WHERE email=?")->execute([$d['admin_email']]);
            return ['ok' => true, 'out' => 'Admin user already exists. Role set to admin.'];
        }

        $pdo->prepare(
            'INSERT INTO users (name, email, password, role, email_verified_at, created_at, updated_at)
             VALUES (?, ?, ?, ?, NOW(), NOW(), NOW())'
        )->execute([
            $d['admin_name'] ?? 'Admin',
            $d['admin_email'],
            password_hash($d['admin_password'], PASSWORD_BCRYPT, ['cost' => 12]),
            'admin',
        ]);

        return ['ok' => true, 'out' => 'Admin user created: ' . $d['admin_email']];
    } catch (Throwable $e) {
        return ['ok' => false, 'out' => $e->getMessage()];
    }
}

// ── AJAX Endpoints ──────────────────────────────────────────────────────────
if (isset($_GET['action'])) {
    header('Content-Type: application/json; charset=utf-8');

    if ($_GET['action'] === 'test_db') {
        $host = trim($_POST['db_host'] ?? '127.0.0.1');
        $port = (int)($_POST['db_port'] ?? 3306);
        $name = trim($_POST['db_name'] ?? '');
        $user = trim($_POST['db_user'] ?? '');
        $pass = $_POST['db_pass'] ?? '';
        try {
            $pdo = new PDO(
                "mysql:host=$host;port=$port;dbname=$name;charset=utf8mb4",
                $user, $pass,
                [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION, PDO::ATTR_TIMEOUT => 5]
            );
            $ver = $pdo->getAttribute(PDO::ATTR_SERVER_VERSION);
            echo json_encode(['ok' => true, 'msg' => "✅ Connected! MySQL $ver — database \"$name\" found."]);
        } catch (Throwable $e) {
            echo json_encode(['ok' => false, 'msg' => '❌ ' . $e->getMessage()]);
        }
        exit;
    }

    if ($_GET['action'] === 'install') {
        $d     = $_SESSION['installer_data'] ?? [];
        $steps = [];

        $push = function (string $msg, bool $ok = true) use (&$steps) {
            $steps[] = ['msg' => $msg, 'ok' => $ok];
        };

        // 1. Generate APP_KEY
        $d['app_key'] = _generateKey();
        $push('🔑 Generated new APP_KEY');

        // 2. Write .env
        if (!_writeEnv($d)) {
            $push('❌ Failed to write .env — check file permissions', false);
            echo json_encode(['ok' => false, 'steps' => $steps, 'err' => 'Cannot write .env file.']);
            exit;
        }
        $push('✅ .env file created');

        // 3. Clear bootstrap cache
        foreach (glob(HOA_ROOT . '/bootstrap/cache/*.php') as $f) { @unlink($f); }
        $push('🗑️  Bootstrap cache cleared');

        // 4. Migrate
        $mig = _artisan('migrate --force');
        $truncated = strlen($mig['out']) > 300 ? substr($mig['out'], 0, 300) . '…' : $mig['out'];
        $push(($mig['ok'] ? '✅' : '⚠️') . ' Migrations: ' . $truncated, $mig['ok']);
        if (!$mig['ok'] && !str_contains($mig['out'], 'exec()')) {
            echo json_encode(['ok' => false, 'steps' => $steps, 'err' => 'Migration failed: ' . $mig['out']]);
            exit;
        }

        // 5. Seed
        $seed = _artisan('db:seed --force');
        $push(($seed['ok'] ? '✅' : '⚠️') . ' Seeders: ' . (strlen($seed['out']) > 150 ? substr($seed['out'], 0, 150) . '…' : $seed['out']));

        // 6. Cache config
        _artisan('config:cache');
        $push('⚡ Config cached');

        // 7. Create admin
        $admin = _createAdmin($d);
        $push(($admin['ok'] ? '✅' : '⚠️') . ' Admin: ' . $admin['out'], $admin['ok']);

        // 8. Create installed flag
        @file_put_contents(HOA_INSTALLED, json_encode([
            'installed_at' => date('Y-m-d H:i:s'),
            'app_url'      => $d['app_url'],
            'installed_by' => $d['admin_email'] ?? 'unknown',
        ], JSON_PRETTY_PRINT));
        $push('🎉 Installation complete!');

        // Clear installer session
        $_SESSION = [];
        session_destroy();

        echo json_encode(['ok' => true, 'steps' => $steps, 'redirect' => $d['app_url'] . '/login']);
        exit;
    }

    echo json_encode(['ok' => false, 'msg' => 'Unknown action']);
    exit;
}

// ── Step POST Handler ───────────────────────────────────────────────────────
$step   = (int)($_SESSION['installer_step'] ?? 1);
$data   = $_SESSION['installer_data'] ?? [];
$config = _detectConfig();
$error  = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['back'])) {
        $_SESSION['installer_step'] = max(1, $step - 1);
        header('Location: ' . strtok($_SERVER['REQUEST_URI'], '?'));
        exit;
    }

    match ($step) {
        1 => (function () use (&$step) {
            $_SESSION['installer_step'] = 2;
            header('Location: ' . strtok($_SERVER['REQUEST_URI'], '?'));
            exit;
        })(),

        2 => (function () use (&$step, &$data, &$error, $config) {
            $appUrl  = rtrim(trim($_POST['app_url'] ?? $config['app_url']), '/');
            $urlPath = parse_url($appUrl, PHP_URL_PATH);
            $data   += [
                'app_name'     => trim($_POST['app_name'] ?? 'HelpOfAi Studio'),
                'app_env'      => in_array($_POST['app_env'] ?? '', ['production', 'local']) ? $_POST['app_env'] : 'production',
                'app_url'      => $appUrl,
                'asset_url'    => $appUrl,
                'session_path' => rtrim($urlPath ?: '/', '/') ?: '/',
            ];
            $data['app_name']     = trim($_POST['app_name'] ?? $data['app_name']);
            $data['app_env']      = in_array($_POST['app_env'] ?? '', ['production', 'local']) ? $_POST['app_env'] : $data['app_env'];
            $data['app_url']      = $appUrl;
            $data['session_path'] = rtrim($urlPath ?: '/', '/') ?: '/';

            if (empty($data['app_url'])) {
                $error = 'Application URL is required.';
                return;
            }
            $_SESSION['installer_data'] = $data;
            $_SESSION['installer_step'] = 3;
            header('Location: ' . strtok($_SERVER['REQUEST_URI'], '?'));
            exit;
        })(),

        3 => (function () use (&$step, &$data, &$error) {
            $data['db_host'] = trim($_POST['db_host'] ?? '127.0.0.1');
            $data['db_port'] = trim($_POST['db_port'] ?? '3306');
            $data['db_name'] = trim($_POST['db_name'] ?? '');
            $data['db_user'] = trim($_POST['db_user'] ?? '');
            $data['db_pass'] = $_POST['db_pass'] ?? '';

            if (empty($data['db_name']) || empty($data['db_user'])) {
                $error = 'Database name and username are required.';
                return;
            }
            $_SESSION['installer_data'] = $data;
            $_SESSION['installer_step'] = 4;
            header('Location: ' . strtok($_SERVER['REQUEST_URI'], '?'));
            exit;
        })(),

        4 => (function () use (&$step, &$data, &$error) {
            $data['admin_name']     = trim($_POST['admin_name'] ?? 'Admin');
            $data['admin_email']    = trim($_POST['admin_email'] ?? '');
            $data['admin_password'] = $_POST['admin_password'] ?? '';
            $confirm                = $_POST['confirm_password'] ?? '';

            if (!filter_var($data['admin_email'], FILTER_VALIDATE_EMAIL)) {
                $error = 'A valid email address is required.';
                return;
            }
            if (strlen($data['admin_password']) < 8) {
                $error = 'Password must be at least 8 characters.';
                return;
            }
            if ($data['admin_password'] !== $confirm) {
                $error = 'Passwords do not match.';
                return;
            }
            $_SESSION['installer_data'] = $data;
            $_SESSION['installer_step'] = 5;
            header('Location: ' . strtok($_SERVER['REQUEST_URI'], '?'));
            exit;
        })(),

        default => null,
    };
}

// Re-read (may have changed on POST error)
$step   = (int)($_SESSION['installer_step'] ?? 1);
$data   = $_SESSION['installer_data'] ?? [];
$config = _detectConfig();
$checks = _checkRequirements();
$hasBlocker = (bool) array_filter($checks, fn($c) => !$c['ok'] && ($c['fatal'] ?? false));

// ── Render ──────────────────────────────────────────────────────────────────
function _renderInstalledPage(string $appUrl): void { ?>
<!DOCTYPE html><html lang="en"><head>
<meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<title>HOA Studio — Already Installed</title>
<script src="https://cdn.tailwindcss.com"></script>
<style>body{background:#020617}</style>
</head><body class="min-h-screen flex items-center justify-center text-white">
<div class="text-center max-w-md px-6">
  <div class="text-7xl mb-6">🎉</div>
  <h1 class="text-3xl font-bold mb-3">HOA Studio is Installed</h1>
  <p class="text-slate-400 mb-8">Your application is ready. The installer is locked.</p>
  <a href="<?= htmlspecialchars($appUrl) ?>/login"
     class="inline-block bg-gradient-to-r from-violet-600 to-indigo-600 px-8 py-3 rounded-2xl font-semibold hover:opacity-90 transition">
    Open HOA Studio →
  </a>
  <p class="mt-6 text-slate-600 text-xs">
    Need to reinstall?
    <a href="?reinstall=1" class="text-slate-400 underline">Force re-run installer</a>
  </p>
</div>
</body></html>
<?php }

$typeLabel  = ['main' => '🌐 Main Domain', 'subdomain' => '🔗 Subdomain', 'subdirectory' => '📁 Subdirectory'];
$stepLabels = ['Requirements', 'Domain & App', 'Database', 'Admin Account', 'Install'];
$selfUrl    = strtok($_SERVER['REQUEST_URI'], '?');
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>HOA Studio — Installer (Step <?= $step ?>/5)</title>
<script src="https://cdn.tailwindcss.com"></script>
<style>
body { background: #020617; font-family: system-ui, -apple-system, sans-serif; }
.glass { background: rgba(15,23,42,0.75); backdrop-filter: blur(24px); border: 1px solid rgba(255,255,255,0.08); }
.glass-sm { background: rgba(30,41,59,0.55); backdrop-filter: blur(12px); border: 1px solid rgba(255,255,255,0.07); }
.glow { box-shadow: 0 0 40px rgba(124,58,237,0.12); }
.btn-violet { background: linear-gradient(135deg, #7c3aed, #4f46e5); }
.btn-violet:hover { opacity: 0.88; }
.inp { background: rgba(2,6,23,0.7); border: 1px solid rgba(255,255,255,0.1); color: #e2e8f0; }
.inp:focus { border-color: rgba(124,58,237,0.55); outline: none; box-shadow: 0 0 0 3px rgba(124,58,237,0.15); }
.inp::placeholder { color: #475569; }
.step-done  { background: #059669; }
.step-act   { background: linear-gradient(135deg,#7c3aed,#4f46e5); }
.step-idle  { background: rgba(51,65,85,0.7); }
.prog-fill  { background: linear-gradient(90deg,#7c3aed,#4f46e5); height:3px; transition: width .5s ease; }
@keyframes spin { to { transform:rotate(360deg) } }
.spin { animation: spin 1s linear infinite; }
@keyframes fadeUp { from { opacity:0; transform:translateY(6px) } to { opacity:1; transform:translateY(0) } }
.fade-up { animation: fadeUp .25s ease forwards; }
</style>
</head>
<body class="min-h-screen text-white">

<!-- Top bar -->
<div class="glass sticky top-0 z-50 px-6 py-3.5 flex items-center justify-between border-b border-white/5">
  <div class="flex items-center gap-3">
    <div class="w-8 h-8 rounded-xl bg-gradient-to-br from-violet-600 to-indigo-600 flex items-center justify-center font-bold text-sm shadow-lg">H</div>
    <span class="font-semibold text-slate-200 tracking-tight">HOA Studio <span class="text-slate-500 font-normal">/ Installer</span></span>
  </div>
  <div class="flex items-center gap-3">
    <span class="text-slate-600 text-xs">v<?= HOA_VERSION ?></span>
    <span class="text-xs text-slate-400 bg-slate-800/60 rounded-full px-3 py-1">Step <?= $step ?> / 5</span>
  </div>
</div>

<!-- Progress bar -->
<div class="bg-slate-900 h-0.5">
  <div class="prog-fill" style="width:<?= max(4, (($step - 1) / 4) * 100) ?>%"></div>
</div>

<div class="max-w-xl mx-auto px-4 py-10">

  <!-- Step indicators -->
  <div class="flex items-center mb-10">
  <?php for ($i = 1; $i <= 5; $i++):
    $cls = $i < $step ? 'step-done' : ($i === $step ? 'step-act' : 'step-idle'); ?>
    <div class="flex items-center <?= $i < 5 ? 'flex-1' : '' ?>">
      <div class="<?= $cls ?> w-8 h-8 rounded-full flex items-center justify-center text-xs font-bold shrink-0 transition-all duration-300">
        <?= $i < $step ? '✓' : $i ?>
      </div>
      <div class="hidden sm:block ml-2 text-xs <?= $i === $step ? 'text-violet-300' : 'text-slate-600' ?> whitespace-nowrap">
        <?= $stepLabels[$i - 1] ?>
      </div>
      <?php if ($i < 5): ?>
      <div class="flex-1 h-px <?= $i < $step ? 'bg-emerald-600/40' : 'bg-white/6' ?> mx-3"></div>
      <?php endif; ?>
    </div>
  <?php endfor; ?>
  </div>

  <!-- Card -->
  <div class="glass glow rounded-3xl p-8 fade-up">

  <?php /* ═══════════════════════════════ STEP 1: REQUIREMENTS ══════════════════════════════ */ ?>
  <?php if ($step === 1): ?>

  <div class="mb-7">
    <h2 class="text-2xl font-bold mb-1 tracking-tight">System Requirements</h2>
    <p class="text-slate-400 text-sm">Checking your server environment before setup begins.</p>
  </div>

  <div class="space-y-1.5 mb-7">
  <?php foreach ($checks as $chk):
    $isFatal = $chk['fatal'] ?? true;
    $border  = $chk['ok'] ? 'border-emerald-500/15 bg-emerald-500/5'
             : ($isFatal   ? 'border-red-500/20 bg-red-500/5'
                           : 'border-yellow-500/20 bg-yellow-500/5');
    $icon    = $chk['ok'] ? '✅' : ($isFatal ? '❌' : '⚠️');
  ?>
  <div class="flex items-center justify-between px-4 py-2.5 rounded-xl border <?= $border ?>">
    <span class="text-sm text-slate-300"><?= htmlspecialchars($chk['label']) ?></span>
    <div class="flex items-center gap-2 shrink-0">
      <span class="text-xs text-slate-500 max-w-xs text-right"><?= htmlspecialchars($chk['detail']) ?></span>
      <span class="text-base"><?= $icon ?></span>
    </div>
  </div>
  <?php endforeach; ?>
  </div>

  <?php if ($hasBlocker): ?>
  <div class="bg-red-500/10 border border-red-500/25 rounded-2xl p-4 mb-6 text-sm text-red-300">
    <strong>⚠️ Fix the errors above</strong> before continuing.<br>
    For permission errors: <code class="bg-black/30 px-1.5 py-0.5 rounded text-xs">chmod -R 775 storage bootstrap/cache</code>
  </div>
  <?php endif; ?>

  <form method="POST">
    <button type="submit" <?= $hasBlocker ? 'disabled' : '' ?>
      class="btn-violet w-full py-3 rounded-2xl font-semibold text-sm transition <?= $hasBlocker ? 'opacity-40 cursor-not-allowed' : '' ?>">
      Continue to Setup →
    </button>
  </form>

  <?php /* ═══════════════════════════════ STEP 2: DOMAIN & APP ══════════════════════════════ */ ?>
  <?php elseif ($step === 2): ?>

  <div class="mb-7">
    <h2 class="text-2xl font-bold mb-1 tracking-tight">Domain & Application</h2>
    <p class="text-slate-400 text-sm">Auto-detected from your server. Confirm or adjust.</p>
  </div>

  <!-- Auto-detect card -->
  <div class="glass-sm rounded-2xl p-5 mb-6">
    <div class="text-xs text-violet-400 font-semibold uppercase tracking-widest mb-4">🔍 Auto-Detected</div>
    <div class="grid grid-cols-2 gap-y-4 gap-x-6 text-sm">
      <div>
        <div class="text-slate-500 text-xs mb-1">Domain Type</div>
        <div class="font-semibold"><?= $typeLabel[$config['domain_type']] ?? $config['domain_type'] ?></div>
      </div>
      <div>
        <div class="text-slate-500 text-xs mb-1">Protocol</div>
        <div class="font-semibold"><?= $config['protocol'] === 'https' ? '🔒 HTTPS (secure)' : '⚠️ HTTP' ?></div>
      </div>
      <div class="col-span-2">
        <div class="text-slate-500 text-xs mb-1">Detected App URL</div>
        <div class="font-mono text-violet-300 text-sm"><?= htmlspecialchars($config['app_url']) ?></div>
      </div>
      <div>
        <div class="text-slate-500 text-xs mb-1">Session Cookie Path</div>
        <div class="font-mono text-xs text-slate-300"><?= htmlspecialchars($config['session_path']) ?></div>
      </div>
      <div>
        <div class="text-slate-500 text-xs mb-1">PHP Binary</div>
        <div class="font-mono text-xs text-slate-300"><?= htmlspecialchars(PHP_BINARY ?: 'php') ?></div>
      </div>
    </div>
  </div>

  <?php if ($error): ?>
  <div class="bg-red-500/10 border border-red-500/25 rounded-2xl p-4 mb-5 text-sm text-red-300"><?= htmlspecialchars($error) ?></div>
  <?php endif; ?>

  <form method="POST" class="space-y-5">
    <div>
      <label class="block text-sm text-slate-400 mb-2">Application Name</label>
      <input type="text" name="app_name" required placeholder="HelpOfAi Studio"
        value="<?= htmlspecialchars($data['app_name'] ?? 'HelpOfAi Studio') ?>"
        class="inp w-full rounded-xl px-4 py-3 text-sm">
    </div>
    <div>
      <label class="block text-sm text-slate-400 mb-2">
        Application URL
        <span class="ml-1 text-violet-400 text-xs">(auto-detected — verify carefully)</span>
      </label>
      <input type="url" name="app_url" required
        value="<?= htmlspecialchars($data['app_url'] ?? $config['app_url']) ?>"
        class="inp w-full rounded-xl px-4 py-3 text-sm font-mono">
      <p class="text-slate-600 text-xs mt-1.5">
        Main: <code>https://helpofai.com</code> &nbsp;•&nbsp;
        Subdir: <code>https://helpofai.com/studio</code> &nbsp;•&nbsp;
        Sub: <code>https://studio.helpofai.com</code>
      </p>
    </div>
    <div>
      <label class="block text-sm text-slate-400 mb-2">Environment</label>
      <select name="app_env" class="inp w-full rounded-xl px-4 py-3 text-sm">
        <option value="production" <?= ($data['app_env'] ?? 'production') === 'production' ? 'selected' : '' ?>>🚀 Production</option>
        <option value="local" <?= ($data['app_env'] ?? '') === 'local' ? 'selected' : '' ?>>🛠️ Local / Development</option>
      </select>
    </div>
    <div class="flex gap-3 pt-1">
      <button type="submit" name="back" value="1"
        class="glass-sm rounded-xl px-5 py-3 text-sm hover:bg-white/5 transition">← Back</button>
      <button type="submit" class="btn-violet flex-1 rounded-xl py-3 font-semibold text-sm transition">Continue →</button>
    </div>
  </form>

  <?php /* ═══════════════════════════════ STEP 3: DATABASE ══════════════════════════════ */ ?>
  <?php elseif ($step === 3): ?>

  <div class="mb-7">
    <h2 class="text-2xl font-bold mb-1 tracking-tight">Database Configuration</h2>
    <p class="text-slate-400 text-sm">Enter your MySQL connection details. Use the test button to verify.</p>
  </div>

  <?php if ($error): ?>
  <div class="bg-red-500/10 border border-red-500/25 rounded-2xl p-4 mb-5 text-sm text-red-300"><?= htmlspecialchars($error) ?></div>
  <?php endif; ?>

  <form method="POST" id="db-form" class="space-y-5">
    <div class="flex gap-3">
      <div class="flex-1">
        <label class="block text-sm text-slate-400 mb-2">Host</label>
        <input type="text" name="db_host" required placeholder="127.0.0.1"
          value="<?= htmlspecialchars($data['db_host'] ?? '127.0.0.1') ?>"
          class="inp w-full rounded-xl px-4 py-3 text-sm">
      </div>
      <div class="w-28">
        <label class="block text-sm text-slate-400 mb-2">Port</label>
        <input type="number" name="db_port" required placeholder="3306"
          value="<?= htmlspecialchars($data['db_port'] ?? '3306') ?>"
          class="inp w-full rounded-xl px-4 py-3 text-sm">
      </div>
    </div>
    <div>
      <label class="block text-sm text-slate-400 mb-2">Database Name</label>
      <input type="text" name="db_name" required placeholder="hoa_studio_db"
        value="<?= htmlspecialchars($data['db_name'] ?? '') ?>"
        class="inp w-full rounded-xl px-4 py-3 text-sm">
    </div>
    <div>
      <label class="block text-sm text-slate-400 mb-2">Username</label>
      <input type="text" name="db_user" required placeholder="db_username"
        value="<?= htmlspecialchars($data['db_user'] ?? '') ?>"
        class="inp w-full rounded-xl px-4 py-3 text-sm" autocomplete="username">
    </div>
    <div>
      <label class="block text-sm text-slate-400 mb-2">Password</label>
      <input type="password" name="db_pass" placeholder="(leave empty if none)"
        value="<?= htmlspecialchars($data['db_pass'] ?? '') ?>"
        class="inp w-full rounded-xl px-4 py-3 text-sm" autocomplete="new-password">
    </div>

    <!-- Test result -->
    <div id="db-result" class="hidden rounded-2xl px-4 py-3 text-sm border"></div>

    <div class="flex gap-3 pt-1">
      <button type="submit" name="back" value="1"
        class="glass-sm rounded-xl px-5 py-3 text-sm hover:bg-white/5 transition">← Back</button>
      <button type="button" id="test-btn" onclick="testDb()"
        class="glass-sm rounded-xl px-5 py-3 text-sm border border-violet-500/30 hover:bg-violet-500/10 transition whitespace-nowrap">
        🔌 Test
      </button>
      <button type="submit" class="btn-violet flex-1 rounded-xl py-3 font-semibold text-sm transition">Continue →</button>
    </div>
  </form>

  <script>
  async function testDb() {
    const f = document.getElementById('db-form');
    const r = document.getElementById('db-result');
    const b = document.getElementById('test-btn');
    b.textContent = '⏳ Testing…'; b.disabled = true;
    r.className = 'rounded-2xl px-4 py-3 text-sm border border-slate-700 bg-slate-800/50 text-slate-400';
    r.textContent = 'Connecting…'; r.classList.remove('hidden');

    const fd = new FormData();
    fd.append('db_host', f.db_host.value);
    fd.append('db_port', f.db_port.value);
    fd.append('db_name', f.db_name.value);
    fd.append('db_user', f.db_user.value);
    fd.append('db_pass', f.db_pass.value);

    try {
      const res  = await fetch('?action=test_db', { method: 'POST', body: fd });
      const json = await res.json();
      r.className = 'rounded-2xl px-4 py-3 text-sm border ' +
        (json.ok ? 'border-emerald-500/25 bg-emerald-500/8 text-emerald-300'
                 : 'border-red-500/25 bg-red-500/8 text-red-300');
      r.textContent = json.msg;
    } catch(e) {
      r.className = 'rounded-2xl px-4 py-3 text-sm border border-red-500/25 bg-red-500/8 text-red-300';
      r.textContent = '❌ Request failed: ' + e.message;
    }
    b.textContent = '🔌 Test'; b.disabled = false;
  }
  </script>

  <?php /* ═══════════════════════════════ STEP 4: ADMIN ACCOUNT ══════════════════════════════ */ ?>
  <?php elseif ($step === 4): ?>

  <div class="mb-7">
    <h2 class="text-2xl font-bold mb-1 tracking-tight">Admin Account</h2>
    <p class="text-slate-400 text-sm">Create your primary administrator account.</p>
  </div>

  <?php if ($error): ?>
  <div class="bg-red-500/10 border border-red-500/25 rounded-2xl p-4 mb-5 text-sm text-red-300"><?= htmlspecialchars($error) ?></div>
  <?php endif; ?>

  <form method="POST" class="space-y-5">
    <div>
      <label class="block text-sm text-slate-400 mb-2">Full Name</label>
      <input type="text" name="admin_name" required placeholder="Admin"
        value="<?= htmlspecialchars($data['admin_name'] ?? '') ?>"
        class="inp w-full rounded-xl px-4 py-3 text-sm">
    </div>
    <div>
      <label class="block text-sm text-slate-400 mb-2">Email Address</label>
      <input type="email" name="admin_email" required placeholder="admin@helpofai.com"
        value="<?= htmlspecialchars($data['admin_email'] ?? '') ?>"
        class="inp w-full rounded-xl px-4 py-3 text-sm" autocomplete="email">
    </div>
    <div>
      <label class="block text-sm text-slate-400 mb-2">Password <span class="text-slate-600 text-xs">(min. 8 characters)</span></label>
      <input type="password" name="admin_password" required minlength="8" placeholder="••••••••••"
        class="inp w-full rounded-xl px-4 py-3 text-sm" autocomplete="new-password">
    </div>
    <div>
      <label class="block text-sm text-slate-400 mb-2">Confirm Password</label>
      <input type="password" name="confirm_password" required placeholder="••••••••••"
        class="inp w-full rounded-xl px-4 py-3 text-sm" autocomplete="new-password">
    </div>
    <div class="flex gap-3 pt-1">
      <button type="submit" name="back" value="1"
        class="glass-sm rounded-xl px-5 py-3 text-sm hover:bg-white/5 transition">← Back</button>
      <button type="submit" class="btn-violet flex-1 rounded-xl py-3 font-semibold text-sm transition">Review & Install →</button>
    </div>
  </form>

  <?php /* ═══════════════════════════════ STEP 5: INSTALL ══════════════════════════════ */ ?>
  <?php elseif ($step === 5): ?>

  <div class="mb-7">
    <h2 class="text-2xl font-bold mb-1 tracking-tight">Ready to Install</h2>
    <p class="text-slate-400 text-sm">Review your configuration then click Install.</p>
  </div>

  <!-- Summary -->
  <div class="glass-sm rounded-2xl p-5 mb-6">
    <div class="text-xs text-violet-400 font-semibold uppercase tracking-widest mb-4">📋 Configuration Summary</div>
    <div class="space-y-2 text-sm">
    <?php $rows = [
      ['🏷️  App Name',      $data['app_name'] ?? '—'],
      ['🌐 App URL',        $data['app_url'] ?? '—'],
      ['🔧 Environment',    $data['app_env'] ?? 'production'],
      ['🍪 Session Path',   $data['session_path'] ?? '/'],
      ['🗄️  Database',      ($data['db_host'] ?? '—') . ':' . ($data['db_port'] ?? '3306') . '/' . ($data['db_name'] ?? '—')],
      ['👤 Admin Email',    $data['admin_email'] ?? '—'],
    ];
    foreach ($rows as [$k, $v]): ?>
    <div class="flex items-center justify-between py-1.5 border-b border-white/5 last:border-0">
      <span class="text-slate-500 text-xs"><?= htmlspecialchars($k) ?></span>
      <span class="font-mono text-xs text-slate-200 max-w-xs text-right truncate"><?= htmlspecialchars($v) ?></span>
    </div>
    <?php endforeach; ?>
    </div>
  </div>

  <!-- Install log -->
  <div id="log" class="hidden glass-sm rounded-2xl p-4 mb-5 max-h-52 overflow-y-auto font-mono text-xs space-y-1.5"></div>

  <!-- Error -->
  <div id="err" class="hidden bg-red-500/10 border border-red-500/25 rounded-2xl p-4 mb-5 text-sm text-red-300"></div>

  <div class="flex gap-3">
    <button id="back-btn" onclick="window.history.back()"
      class="glass-sm rounded-xl px-5 py-3 text-sm hover:bg-white/5 transition">← Back</button>
    <button id="install-btn" onclick="install()"
      class="btn-violet flex-1 rounded-xl py-3 font-semibold text-sm transition flex items-center justify-center gap-2">
      🚀 Install HOA Studio
    </button>
  </div>

  <script>
  async function install() {
    const btn  = document.getElementById('install-btn');
    const back = document.getElementById('back-btn');
    const log  = document.getElementById('log');
    const err  = document.getElementById('err');

    btn.disabled = back.disabled = true;
    btn.innerHTML = '<div class="w-4 h-4 border-2 border-white/30 border-t-white rounded-full spin"></div> Installing…';
    log.classList.remove('hidden');
    err.classList.add('hidden');
    log.innerHTML = '';

    const addLine = (text, cls = 'text-slate-300') => {
      const d = document.createElement('div');
      d.className = cls + ' fade-up';
      d.textContent = text;
      log.appendChild(d);
      log.scrollTop = log.scrollHeight;
    };

    addLine('⏳ Sending installation request…', 'text-violet-400');

    try {
      const res  = await fetch('?action=install', { method: 'POST' });
      const json = await res.json();

      for (const s of (json.steps || [])) {
        await new Promise(r => setTimeout(r, 180));
        addLine(s.msg, s.ok !== false ? 'text-slate-300' : 'text-yellow-300');
      }

      if (json.ok) {
        addLine('', '');
        addLine('🎉 Done! Redirecting to your app…', 'text-emerald-400 font-semibold');
        setTimeout(() => { window.location.href = json.redirect || '/'; }, 2500);
      } else {
        err.classList.remove('hidden');
        err.textContent = json.err || 'Installation failed. See log above.';
        btn.disabled = back.disabled = false;
        btn.innerHTML = '🔄 Retry';
      }
    } catch(e) {
      err.classList.remove('hidden');
      err.textContent = 'Network error: ' + e.message;
      btn.disabled = back.disabled = false;
      btn.innerHTML = '🔄 Retry';
    }
  }
  </script>

  <?php endif; ?>

  </div><!-- end glass card -->
</div><!-- end max-w-xl -->

<p class="text-center text-slate-700 text-xs pb-10">
  HOA Studio Installer &nbsp;·&nbsp; Copyright © 2026 Rajib Adhikary &nbsp;·&nbsp;
  <a href="https://helpofai.com" class="hover:text-slate-500 transition">helpofai.com</a>
</p>
</body>
</html>
