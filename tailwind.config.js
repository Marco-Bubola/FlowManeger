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
        // Breakpoints personalizados para diferentes larguras
        '3xl': '1920px',  // Full HD
        '4xl': '2560px',  // 2K/QHD
        '5xl': '3440px',  // Ultrawide
        ultrawind: '2498px', // Breakpoint customizado original
      },
    },
  },
  plugins: [
    require('flowbite/plugin') // Adicione esta linha
  ],
}
