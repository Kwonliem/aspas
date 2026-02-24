import defaultTheme from 'tailwindcss/defaultTheme';
import forms from '@tailwindcss/forms';
import containerQueries from '@tailwindcss/container-queries';

/** @type {import('tailwindcss').Config} */
export default {
    darkMode: 'class',
    content: [
        './vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php',
        './storage/framework/views/*.php',
        './resources/views/**/*.blade.php',
        './resources/js/**/*.vue',
    ],

    theme: {
        extend: {
            colors: {
                
                "primary": "#ffde24",
                "background-light": "#f8f8f5",
                "background-dark": "#23200f",
                "surface-light": "#ffffff",
                "surface-dark": "#2c2918",
                "text-main": "#1f2937",
                "text-muted": "#4b5563",
                "sidebar-bg": "#1e1e1e",
            },
            fontFamily: {
                sans: ['Figtree', ...defaultTheme.fontFamily.sans],
                display: ["Poppins", "sans-serif"], 
            },
            borderRadius: {
                "DEFAULT": "0.25rem",
                "lg": "0.5rem",
                "xl": "0.75rem",
                "full": "9999px"
            },
        },
    },

    plugins: [
        forms,
        containerQueries
    ],
};