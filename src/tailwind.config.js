import defaultTheme from 'tailwindcss/defaultTheme';
import forms from '@tailwindcss/forms';

/** @type {import('tailwindcss').Config} */
export default {
    content: [
        './vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php',
        './storage/framework/views/*.php',
        './resources/views/**/*.blade.php',
        './resources/js/**/*.js',
    ],

    theme: {
        container: {
            center: true,
            padding: '1rem',
            screens: { lg: '1024px', xl: '1200px', '2xl': '1280px' },
        },
        extend: {
            colors: {
                charcoal: { DEFAULT: '#0A0A0A', 800: '#141414', 700: '#1F1F1F' },
                beige: { DEFAULT: '#F5F1E8', 50: '#FDFBF6', 200: '#E8E1D2' },
                gold: { DEFAULT: '#D4AF37', 600: '#B8962B', 700: '#8A6D1F' },
                ink: { DEFAULT: '#0A0A0A', soft: '#4A4A4A', muted: '#6B6B6B' },
                canvas: { DEFAULT: '#FFFFFF', muted: '#F5F1E8' },
                line: { DEFAULT: '#E8E1D2', strong: '#C9C2B2' },
                tint: {
                    success: '#E8F3EC',
                    warning: '#FDF3E1',
                    danger: '#FBE9EA',
                    info: '#EAF1F8',
                },
                success: '#1E6F45',
                warning: '#8A5A00',
                danger: '#A3202B',
                info: '#1F4E79',
            },
            fontFamily: {
                display: ['"Playfair Display"', ...defaultTheme.fontFamily.serif],
                sans: ['Montserrat', ...defaultTheme.fontFamily.sans],
            },
            fontSize: {
                'display-xl': ['clamp(40px, 6vw, 60px)', '1.07'],
                'display-lg': ['48px', '56px'],
                'display-md': ['36px', '44px'],
                'heading-lg': ['30px', '38px'],
                'heading-md': ['24px', '32px'],
                'heading-sm': ['20px', '28px'],
                'body-lg': ['18px', '30px'],
                'body-md': ['16px', '26px'],
                'body-sm': ['14px', '22px'],
                label: ['12px', '16px'],
                price: ['20px', '28px'],
            },
            borderRadius: {
                xs: '2px',
                sm: '4px',
            },
            boxShadow: {
                sm: '0 1px 2px rgba(10,10,10,.06)',
                md: '0 8px 24px rgba(10,10,10,.08)',
                lg: '0 24px 48px rgba(10,10,10,.12)',
            },
            transitionTimingFunction: {
                brand: 'cubic-bezier(.2,.8,.2,1)',
            },
        },
    },

    plugins: [forms],
};
