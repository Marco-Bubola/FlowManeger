<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex">
    <title>Abrindo o seu carrinho · {{ $name }}</title>
    <style>body{margin:0;min-height:100vh;display:grid;place-items:center;font-family:system-ui,sans-serif;background:#f5f5f5;color:#333}p{font-size:15px}</style>
</head>
<body>
    <p>Abrindo o seu carrinho…</p>
    <script>
        (function () {
            var back = @js($items);
            try {
                var now = JSON.parse(localStorage.getItem('portal_cart') || '[]') || [];
                back.forEach(function (i) { if (!now.some(function (n) { return n.id === i.id; })) now.push(i); });
                localStorage.setItem('portal_cart', JSON.stringify(now));
            } catch (e) {}
            location.replace(@js($next));
        })();
    </script>
</body>
</html>
