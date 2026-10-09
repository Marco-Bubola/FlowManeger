import 'flowbite';

// Ícones do site (favicon e ícone do iPhone): o deploy só envia public/build,
// então o Vite copia estes arquivos para lá e o head usa Vite::asset().
import.meta.glob('../images/icons/*', { eager: true, query: '?url', import: 'default' });
