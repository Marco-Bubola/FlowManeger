import 'flowbite';

// Gráficos dos dashboards (x-dash.chart). Vem pelo Vite porque o deploy não envia public/assets.
import './dash-charts.js';

// Ícones do site (favicon e ícone do iPhone): o deploy só envia public/build,
// então o Vite copia estes arquivos para lá e o head usa Vite::asset().
import.meta.glob('../images/icons/*', { eager: true, query: '?url', import: 'default' });
