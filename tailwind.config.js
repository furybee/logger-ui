const defaultTheme = require('tailwindcss/defaultTheme');

module.exports = {
    purge: {
        content: ['./resources/views/**/*.blade.php', './resources/js/**/*.vue', './resources/js/**/*.js'],
    },
    theme: {
        extend: {
            colors: {
                grey: {
                    '950': '#0e1420'
                }
            },
            fontFamily: {
                sans: ['Source Code Pro', ...defaultTheme.fontFamily.sans],
            },
        },
    },
    variants: {},
    plugins: [],
};
