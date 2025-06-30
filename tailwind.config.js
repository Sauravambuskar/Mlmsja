/** @type {import('tailwindcss').Config} */
module.exports = {
  content: [
    "./**/*.{php,html,js}",
    "./client/**/*.{php,html,js}",
    "./admin/**/*.{php,html,js}",
    "./includes/**/*.{php,html,js}",
  ],
  theme: {
    extend: {
      colors: {
        'primary': '#4a90e2', // Professional Blue
        'success': '#10b981', // Green
        'warning': '#f59e0b', // Orange
        'danger': '#ef4444', // Red
        'info': '#3b82f6', // Blue
        'background': '#f8fafc', // Light Gray
        'text-dark': '#2d3436', // Dark Gray
        'sidebar': '#1e293b', // Admin Sidebar
      },
      fontFamily: {
        sans: ['Inter', 'sans-serif'],
      },
    },
  },
  plugins: [],
} 