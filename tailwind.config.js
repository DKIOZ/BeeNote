import defaultTheme from 'tailwindcss/defaultTheme';
import forms from '@tailwindcss/forms';
import typography from '@tailwindcss/typography';

/** @type {import('tailwindcss').Config} */
export default {
    content: [
        './vendor/laravel/jetstream/**/*.blade.php',
        './storage/framework/views/*.php',
        './resources/views/**/*.blade.php',
        './resources/js/**/*.vue',
    ],

    theme: {
        extend: {
            colors: {
                forest: '#17382C',
                'forest-deep': '#0F241C',
                honey: '#FFB81C',
                ground: '#F6F5EF',
                ink: '#1B2B25',
                muted: '#3D4F47',
                line: '#6E7A73',
                soft: '#E8E8DF',
                hairline: '#ECEBE3',
                mint: '#DDEFE3',
                'mint-ink': '#174A32',
                peach: '#FDE7D8',
                rust: '#8F3808',
                late: '#C2570F',
                cream: '#FFF0C7',
                'cream-ink': '#6B4B00',
                sage: '#C9D8D0',
            },
            fontFamily: {
                sans: ['Figtree', ...defaultTheme.fontFamily.sans],
                display: ['"Bricolage Grotesque Variable"', 'Figtree', ...defaultTheme.fontFamily.sans],
            },
            borderRadius: {
                card: '22px',
                hero: '32px',
            },
            boxShadow: {
                card: '0 6px 20px rgba(23,56,44,0.07)',
                cta: '0 4px 0 #17382C',
                nav: '0 10px 30px rgba(23,56,44,0.16)',
            },
            keyframes: {
                'bn-ring': {
                    '0%': { transform: 'scale(0.9)', opacity: '0.5' },
                    '100%': { transform: 'scale(1.5)', opacity: '0' },
                },
                'bn-pulse': { '0%,100%': { transform: 'scale(1)' }, '50%': { transform: 'scale(1.05)' } },
                'bn-wave': { '0%,100%': { transform: 'scaleY(0.3)' }, '50%': { transform: 'scaleY(1)' } },
                'bn-pop': {
                    '0%': { transform: 'scale(0.6)', opacity: '0' },
                    '70%': { transform: 'scale(1.08)', opacity: '1' },
                    '100%': { transform: 'scale(1)' },
                },
            },
            animation: {
                'bn-ring': 'bn-ring 1.8s ease-out infinite',
                'bn-pulse': 'bn-pulse 2s ease-in-out infinite',
                'bn-wave': 'bn-wave 0.9s ease-in-out infinite',
                'bn-pop': 'bn-pop 0.5s ease-out both',
            },
        },
    },

    plugins: [forms, typography],
};
