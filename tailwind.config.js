import defaultTheme from 'tailwindcss/defaultTheme';
import forms from '@tailwindcss/forms';
import typography from '@tailwindcss/typography';

/** @type {import('tailwindcss').Config} */
export default {
    darkMode: 'class',
    content: [
        './vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php',
        './vendor/laravel/jetstream/**/*.blade.php',
        './storage/framework/views/*.php',
        './resources/views/**/*.blade.php',
        './resources/js/**/*.vue',
        './node_modules/flowbite/**/*.js',
    ],

    theme: {
        extend: {
            fontFamily: {
                sans: ['Figtree', ...defaultTheme.fontFamily.sans],
            },
            colors: {
                beige: {
                    100: '#F5F1E7',
                    200: '#EDE7D5',
                    300: '#E2D9BF',
                    400: '#D8CCB5',
                    500: '#C0B29E',
                    600: '#A79886',
                    700: '#8F7F6E',
                    800: '#766556',
                    900: '#5E4C3E',
                },
            },
        },
    },

    plugins: [forms, typography, 'flowbite/plugin'],
};
