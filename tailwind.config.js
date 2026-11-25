import defaultTheme from 'tailwindcss/defaultTheme';
import forms from '@tailwindcss/forms';

/** @type {import('tailwindcss').Config} */
export default {
    content: [
        './storage/framework/views/*.php',
        './resources/views/**/*.blade.php',
    ],

    theme: {
        extend: {
            fontFamily: {
                /* Nama "... Variable" sesuai @font-face dari @fontsource-variable. */
                sans: ['"Plus Jakarta Sans Variable"', '"Plus Jakarta Sans"', 'Figtree', ...defaultTheme.fontFamily.sans],
                display: ['"Sora Variable"', '"Sora"', '"Plus Jakarta Sans Variable"', ...defaultTheme.fontFamily.sans],
            },
            colors: {
                /* Hijau telaga: tetap formal untuk naskah dinas, tetapi jauh
                   lebih segar daripada navy yang monoton. */
                gov: {
                    50: '#EEF8F5',
                    100: '#D5EEE7',
                    200: '#ABDCD0',
                    300: '#78C5B6',
                    400: '#43A897',
                    500: '#268A7B',
                    600: '#1B6E63',
                    700: '#155750',
                    800: '#124640',
                    900: '#0F3A35',
                    950: '#082522',
                },
                /* Kuning emas hangat sebagai aksen kedua (kop, garis, sorotan). */
                gold: {
                    50: '#FEF9EC',
                    100: '#FBEECB',
                    200: '#F6DC99',
                    300: '#F0C765',
                    400: '#E6AE38',
                    500: '#CE931F',
                    600: '#A8731A',
                    700: '#82561A',
                    800: '#613F18',
                    900: '#402A11',
                },
                /* Hijau daun untuk penanda selesai, dipisahkan dari hijau telaga. */
                leaf: {
                    50: '#F1F9EE',
                    100: '#DFF1D6',
                    200: '#C0E3AE',
                    300: '#98CD7E',
                    400: '#72B356',
                    500: '#549743',
                    600: '#3F7835',
                    700: '#335E2D',
                    800: '#2A4B26',
                    900: '#233E21',
                },
            },
            boxShadow: {
                gov: '0 1px 2px rgba(15, 58, 53, 0.07), 0 8px 24px -12px rgba(15, 58, 53, 0.20)',
                card: '0 1px 2px rgba(15, 58, 53, 0.05)',
            },
        },
    },

    plugins: [forms],
};
