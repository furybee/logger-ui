/** @type {import('tailwindcss').Config} */
export default {
    content: [
        "./resources/**/*.blade.php",
        "./resources/**/*.js",
        "./resources/**/*.vue",
    ],
    theme: {
        extend: {
            fontFamily: {
                sans: ['Source Code Pro', 'sans-serif'],
            },
        },
    },
    plugins: [
        require('daisyui')
    ],
};
