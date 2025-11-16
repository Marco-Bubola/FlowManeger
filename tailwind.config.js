module.exports = {
  darkMode: 'class',
  content: [
    './resources/views/**/*.blade.php',
    './resources/js/**/*.js',
    './resources/css/**/*.css',
    './resources/sass/**/*.scss',
    './node_modules/flowbite', // Adicione esta linha
  ],
  theme: {
    extend: {
      screens: {
        // Breakpoint personalizado para telas ultra-wide
        // Ajuste o valor se quiser outra largura mínima
        // Reduzido para 1920px para compatibilidade com monitores comuns
        ultrawind: '1920px',
      },
    },
  },
  plugins: [
    require('flowbite/plugin') // Adicione esta linha
  ],
}
