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
            padding: { DEFAULT: '1.25rem', sm: '1.5rem', lg: '2rem' },
            screens: { lg: '1024px', xl: '1200px', '2xl': '1280px' },
        },
        extend: {
            colors: {
                charcoal: { DEFAULT: '#0A0A0A', 800: '#141414', 700: '#1F1F1F', 600: '#2E2E2E' },
                beige: { DEFAULT: '#F5F1E8', 50: '#FBF9F5', 100: '#F5F1E8', 200: '#E8E1D2' },
                gold: { DEFAULT: '#D4AF37', 600: '#B8962B', 700: '#8A6D1F' },
                ink: { DEFAULT: '#0A0A0A', soft: '#4A4A4A', muted: '#6E6A63' },
                canvas: { DEFAULT: '#FFFFFF', muted: '#F5F1E8' },
                line: { DEFAULT: '#EAE4D8', strong: '#C9C2B2' },
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
                sans: ['Manrope', ...defaultTheme.fontFamily.sans],
            },
            fontSize: {
                'display-2xl': ['clamp(48px, 7vw, 84px)', { lineHeight: '1.02', letterSpacing: '-0.02em' }],
                'display-xl': ['clamp(42px, 6vw, 68px)', { lineHeight: '1.05', letterSpacing: '-0.02em' }],
                'display-lg': ['52px', { lineHeight: '1.08', letterSpacing: '-0.015em' }],
                'display-md': ['40px', { lineHeight: '1.12', letterSpacing: '-0.01em' }],
                'heading-lg': ['32px', { lineHeight: '1.22', letterSpacing: '-0.01em' }],
                'heading-md': ['25px', { lineHeight: '1.3', letterSpacing: '-0.005em' }],
                'heading-sm': ['20px', { lineHeight: '1.4' }],
                'body-lg': ['18px', '1.65'],
                'body-md': ['16px', '1.65'],
                'body-sm': ['14px', '1.6'],
                label: ['12px', { lineHeight: '1.4', letterSpacing: '0.08em' }],
                price: ['20px', { lineHeight: '1.4', letterSpacing: '-0.01em' }],
            },
            borderRadius: {
                xs: '8px',
                sm: '12px',
                md: '16px',
                lg: '24px',
                xl: '32px',
            },
            boxShadow: {
                sm: '0 1px 2px rgba(10,10,10,.04), 0 1px 3px rgba(10,10,10,.06)',
                md: '0 4px 12px rgba(10,10,10,.05), 0 12px 32px rgba(10,10,10,.07)',
                lg: '0 24px 64px rgba(10,10,10,.14)',
                card: '0 1px 2px rgba(10,10,10,.04), 0 8px 24px rgba(10,10,10,.05)',
                lift: '0 12px 32px rgba(10,10,10,.10), 0 24px 64px rgba(10,10,10,.08)',
                ring: '0 0 0 3px rgba(212,175,55,.35)',
            },
            transitionTimingFunction: {
                brand: 'cubic-bezier(.2,.8,.2,1)',
                spring: 'cubic-bezier(.34,1.56,.64,1)',
            },
            keyframes: {
                'fade-up': {
                    from: { opacity: '0', transform: 'translateY(18px)' },
                    to: { opacity: '1', transform: 'translateY(0)' },
                },
                'fade-in': {
                    from: { opacity: '0' },
                    to: { opacity: '1' },
                },
                'scale-in': {
                    from: { opacity: '0', transform: 'scale(.96)' },
                    to: { opacity: '1', transform: 'scale(1)' },
                },
                shimmer: {
                    '100%': { transform: 'translateX(100%)' },
                },
            },
            animation: {
                'fade-up': 'fade-up .6s cubic-bezier(.2,.8,.2,1) both',
                'fade-in': 'fade-in .5s cubic-bezier(.2,.8,.2,1) both',
                'scale-in': 'scale-in .35s cubic-bezier(.2,.8,.2,1) both',
            },
        },
    },

    plugins: [forms],
};