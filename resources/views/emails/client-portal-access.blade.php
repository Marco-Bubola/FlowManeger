<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Acesso ao Portal</title>
    <style>
        * { box-sizing:border-box; margin:0; padding:0; }
        body { font-family:'Helvetica Neue',Arial,sans-serif; background:#f1f5f9; color:#1e293b; }
        .wrapper { max-width:580px; margin:40px auto; }
        .header { background:linear-gradient(135deg,#0369a1,#4f46e5); border-radius:16px 16px 0 0; padding:40px 40px 32px; text-align:center; }
        .header-icon { width:64px; height:64px; background:rgba(255,255,255,0.2); border-radius:16px; display:inline-flex; align-items:center; justify-content:center; margin-bottom:16px; font-size:28px; }
        .header h1 { color:#fff; font-size:24px; font-weight:800; letter-spacing:-0.5px; }
        .header p { color:#bae6fd; font-size:14px; margin-top:6px; }
        .body { background:#fff; padding:40px; }
        .greeting { font-size:20px; font-weight:700; color:#0f172a; margin-bottom:8px; }
        .intro { font-size:14px; color:#64748b; line-height:1.6; margin-bottom:32px; }
        .creds-box { background:#f0f9ff; border:2px solid #bae6fd; border-radius:12px; padding:24px; margin-bottom:28px; }
        .creds-box h3 { font-size:12px; font-weight:700; text-transform:uppercase; letter-spacing:1px; color:#0369a1; margin-bottom:16px; }
        .cred-row { display:flex; align-items:center; justify-content:space-between; padding:10px 0; border-bottom:1px solid #e0f2fe; }
        .cred-row:last-child { border-bottom:none; }
        .cred-label { font-size:13px; color:#64748b; font-weight:500; }
        .cred-value { font-size:14px; font-weight:700; color:#0f172a; background:#fff; padding:6px 12px; border-radius:8px; border:1px solid #bae6fd; }
        .btn { display:block; width:100%; text-align:center; padding:16px; background:linear-gradient(135deg,#0ea5e9,#4f46e5); color:#fff; text-decoration:none; border-radius:12px; font-weight:800; font-size:16px; letter-spacing:-0.3px; margin-bottom:24px; }
        .warning { background:#fff7ed; border:1px solid #fed7aa; border-radius:10px; padding:16px; font-size:13px; color:#92400e; line-height:1.5; margin-bottom:24px; }
        .footer { background:#f8fafc; border-top:1px solid #e2e8f0; padding:24px 40px; text-align:center; font-size:12px; color:#94a3b8; border-radius:0 0 16px 16px; }
        .footer a { color:#0ea5e9; text-decoration:none; }
    </style>
</head>
<body>
<div class="wrapper">
    <div class="header">
        <div class="header-icon">🏪</div>
        <h1>{{ config('app.name') }}</h1>
        <p>Portal exclusivo para clientes</p>
    </div>

    <div class="body">
        <p class="greeting">Olá, {{ $client->name }}! 👋</p>
        <p class="intro">
            Seu acesso ao <strong>Portal do Cliente</strong> foi criado com sucesso.<br>
            Por meio do portal, você pode acompanhar suas compras, consultar produtos disponíveis e solicitar orçamentos a qualquer momento.
        </p>

        <div class="creds-box">
            <h3>🔐 Suas Credenciais de Acesso</h3>
            <div class="cred-row">
                <span class="cred-label">Login do portal</span>
                <span class="cred-value">{{ $client->portal_login }}</span>
            </div>
            <div class="cred-row">
                <span class="cred-label">Senha</span>
                <span class="cred-value">{{ $plainPassword }}</span>
            </div>
            <div class="cred-row">
                <span class="cred-label">E-mail para recuperacao</span>
                <span class="cred-value">{{ $client->email }}</span>
            </div>
        </div>

        <a href="{{ route('portal.login') }}" class="btn">
            Acessar o Portal →
        </a>

        <div class="warning">
            ⚠️ <strong>Primeiro acesso obrigatório:</strong> ao entrar no portal, você será direcionado para definir sua nova senha e completar seu cadastro com CEP, endereço e demais informações. Nunca compartilhe sua senha com ninguém.
        </div>

        <p style="font-size:13px;color:#64748b;line-height:1.6;">
            Se você não pediu este acesso, pode ignorar este e-mail com segurança. Para dúvidas, entre em contato diretamente com seu vendedor.
        </p>
    </div>

    <div class="footer">
        <p>{{ config('app.name') }} · <a href="{{ config('app.url') }}">{{ parse_url(config('app.url'), PHP_URL_HOST) }}</a></p>
        <p style="margin-top:6px;">© {{ date('Y') }} · Todos os direitos reservados</p>
    </div>
</div>
</body>
</html>
