/** @type {import('tailwindcss').Config} */
export default {
  content: [
    "./index.html",
    "./src/**/*.{js,ts,jsx,tsx,vue}",
  ],
  theme: {
    extend: {},
  },
  safelist: [
    {
      pattern: /^bg-(teal|purple|blue|green)-100$/,
    },
    {
      pattern: /^text-(teal|purple|blue|green)-700$/,
    },
    {
      pattern: /^bg-(teal|purple|blue|green)-400$/,
    },
  ],
  plugins: [],
}

