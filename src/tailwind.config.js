/** @type {import('tailwindcss').Config} */
module.exports = {
    content: [
      './vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php',
      './storage/framework/views/*.php',
      './resources/views/**/*.blade.php',
      './resources/**/*.{html,js,vue}',
      './resources/js/**/*.vue',
    ],
    theme: {
      extend: {
        fontFamily: {
          sans: ['Figtree', 'sans-serif'],
        },
      },
    },
    plugins: [require('@tailwindcss/forms')],
  };
