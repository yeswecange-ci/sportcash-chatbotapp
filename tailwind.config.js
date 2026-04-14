import defaultTheme from 'tailwindcss/defaultTheme';
import forms from '@tailwindcss/forms';

/** @type {import('tailwindcss').Config} */
export default {
    content: [
        './vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php',
        './storage/framework/views/*.php',
        './resources/views/**/*.blade.php',
    ],

    theme: {
        extend: {
            fontFamily: {
                sans: ['Inter', ...defaultTheme.fontFamily.sans],
            },
            colors: {
                // SportCash / LONACI brand colors — orange primaire #F26522
                indigo: {
                    50:  '#fff7ed',
                    100: '#ffedd5',
                    200: '#fed7aa',
                    300: '#fdba74',
                    400: '#fb923c',
                    500: '#f26522',
                    600: '#d4561d',
                    700: '#b04414',
                    800: '#8a330e',
                    900: '#6b2709',
                    950: '#3d1304',
                },
                primary: {
                    50:  '#fff7ed',
                    100: '#ffedd5',
                    200: '#fed7aa',
                    300: '#fdba74',
                    400: '#fb923c',
                    500: '#f26522',
                    600: '#d4561d',
                    700: '#b04414',
                    800: '#8a330e',
                    900: '#6b2709',
                    950: '#3d1304',
                },
            },
        },
    },

    plugins: [forms],
};
