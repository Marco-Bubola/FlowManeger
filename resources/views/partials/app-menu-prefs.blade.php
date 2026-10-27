{{-- Menu do app (Configurações › Menu do app): esconde as áreas e páginas que a pessoa não usa
     no menu lateral, no "Mais" e no "Criar" do celular. O CSS some com os links já no carregamento;
     o script tira os grupos que ficaram vazios. --}}
@auth
    <style id="fm-menu-prefs">{!! \App\Support\AppMenu::css() !!}</style>
    {{-- Cadeado nas telas que o plano não inclui (só com o bloqueio ligado) --}}
    <style id="fm-plan-locks">{!! \App\Support\PlanGate::menuCss() !!}</style>
    <script>
    (function () {
        function off(el) { return getComputedStyle(el).display === 'none'; }
        function allOff(box) {
            var links = box.querySelectorAll('a[href]');
            if (!links.length) return false;
            for (var i = 0; i < links.length; i++) { if (!off(links[i])) return false; }
            return true;
        }
        window.fmTidyMenu = function () {
            document.querySelectorAll('[data-fm-empty]').forEach(function (el) { el.style.display = ''; el.removeAttribute('data-fm-empty'); });
            var hide = function (el) { if (el) { el.style.display = 'none'; el.setAttribute('data-fm-empty', '1'); } };
            // Menu lateral: lista de faturas dos bancos e seções sem nenhum link visível
            document.querySelectorAll('#sidebarScroll nav > div').forEach(function (b) { if (allOff(b)) hide(b); });
            document.querySelectorAll('#sidebarScroll .sidebar-section').forEach(function (s) {
                var nav = s.querySelector('nav');
                if (nav && allOff(nav)) hide(s);
            });
            // "Mais" do celular: grade vazia some junto com o título dela
            document.querySelectorAll('#mobileMoreSheet .more-sheet-app-grid').forEach(function (g) {
                if (!allOff(g)) return;
                hide(g);
                var t = g.previousElementSibling;
                if (t && !t.matches('.more-sheet-app-grid')) hide(t);
            });
            // "Criar" do celular: card de área sem ações
            document.querySelectorAll('#mobileFabSheet .fab-area-card').forEach(function (c) { if (allOff(c)) hide(c); });
        };
        var run = function () { try { window.fmTidyMenu(); } catch (e) {} };
        if (document.readyState === 'loading') document.addEventListener('DOMContentLoaded', run); else setTimeout(run, 0);
        if (!window.__fmMenuTidyBound) {
            window.__fmMenuTidyBound = true;
            document.addEventListener('livewire:navigated', run);
        }
    })();
    </script>
@endauth
