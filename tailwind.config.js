import defaultTheme from 'tailwindcss/defaultTheme';
import forms from '@tailwindcss/forms';
import typography from '@tailwindcss/typography';
import daisyui from "daisyui"

/** @type {import('tailwindcss').Config} */
export default {
    darkMode: 'class',
    content: [
        './vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php',
        './vendor/laravel/jetstream/**/*.blade.php',
        './storage/framework/views/*.php',
        './resources/views/**/*.blade.php',
        './resources/js/**/*.vue',
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

    plugins: [forms, typography, daisyui],

    daisyui: {
        themes: [
            // "retro",
            // "dark",
            {
                runemanager: {
                    "primary": "#EF9995",
                    "primary-content": "#282425",
                    "secondary": "#A4CBB4",
                    "secondary-content": "#282425",
                    "accent": "#DC8850",
                    "accent-content": "#282425",
                    "neutral": "#2E282A",
                    "neutral-content": "#EDE6D4",
                    "base-100": "#d8ccb4",
                    "base-200": "#E2D9BF",
                    "base-300": "#94866D",
                    "base-content": "#282425",
                    "info": "#2563EB",
                    "info-content": "#D2E2FF",
                    "success": "#16A34A",
                    "success-content": "#000A02",
                    "warning": "#D97706",
                    "warning-content": "#110500",
                    "error": "#F35248",
                    "error-content": "#140202"
                },
            },
        ],
    },
};
