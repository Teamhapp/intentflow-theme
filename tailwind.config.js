/** @type {import('tailwindcss').Config} */
module.exports = {
  content: [
    './**/*.php',
    './src/**/*.js',
  ],
  theme: {
    extend: {
      colors: {
        primary: '#2563EB',
        cta: '#22C55E',
        surface: '#F3F4F6',
        border: '#E5E7EB',
        'text-dark': '#111827',
        'text-light': '#6B7280',
      },
      fontSize: {
        h1: ['2rem', { lineHeight: '1.2', fontWeight: '700' }],
        h2: ['1.5rem', { lineHeight: '1.3', fontWeight: '600' }],
        h3: ['1.25rem', { lineHeight: '1.4', fontWeight: '500' }],
        body: ['1rem', { lineHeight: '1.6' }],
        small: ['0.875rem', { lineHeight: '1.5' }],
      },
      spacing: {
        '18': '4.5rem',
      },
    },
  },
  plugins: [],
};
