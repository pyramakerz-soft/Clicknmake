/** @type {import('tailwindcss').Config} */
export default {
    content: [
        "./resources/**/*.blade.php",
        "./resources/**/*.js",
        "./resources/**/*.vue",
        // "./src/**/*.{html , js, jsx, ts , tsx}",
        "./resources/**/*.{html , js , jsx , tsx, ts}",
        "./resources/**/*.vue",
        "./resources/**/*.js",
        "./resources/**/*.vue",
        "./src/**/*.{html , js, jsx, ts , tsx}",
        // "./src/**/*.{html , js, jsx, ts , tsx}",
    ],
    theme: {
        extend: {
            fontFamily: {
                poppins: ["Poppins", "sans-serif"],
            },
        },
    },
    plugins: [],
};
