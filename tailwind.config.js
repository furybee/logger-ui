const defaultTheme = require('tailwindcss/defaultTheme');

module.exports = {
    purge: {
        content: ['./resources/views/**/*.blade.php', './resources/js/**/*.vue', './resources/js/**/*.js'],
    },
    theme: {
        extend: {
            fontFamily: {
                sans: ['Source Code Pro', ...defaultTheme.fontFamily.sans],
            },
        },
    },
    variants: {},
    plugins: [
        require('@tailwindcss/forms'),
        require('@tailwindcss/ui')
    ],
};
