import defaultTheme from 'tailwindcss/defaultTheme';
import forms from '@tailwindcss/forms';

/** @type {import('tailwindcss').Config} */
export default {
    darkMode: 'class',
    content: [
        './vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php',
        './storage/framework/views/*.php',
        './resources/views/**/*.blade.php',
    ],

    theme: {
        extend: {
            fontFamily: {
                sans: ['Inter', ...defaultTheme.fontFamily.sans],
            },
            colors: {
                bankos: {
                    primary: '#2563EB',      // Blue (Primary)
                    light: '#E6FAF8',        // Primary Light / Surface Accent
                    surface: '#FFFFFF',      // Surface
                    bg: '#F9FAFB',           // Page Background
                    border: '#E5E7EB',       // Border
                    text: '#1F2937',         // Text Primary
                    'text-sec': '#6B7280',   // Text Secondary
                    muted: '#9CA3AF',        // Text Muted
                    success: '#10B981',      // Success / ACTIVE
                    warning: '#F59E0B',      // Warning / PENDING
                    danger: '#EF4444',       // Danger / FAILED
                    
                    // Dark Mode Overrides
                    'dark-bg': '#111827',
                    'dark-surface': '#1F2937',
                    'dark-border': '#374151',
                    'dark-text': '#F9FAFB',
                    'dark-text-sec': '#9CA3AF',
                },
                accent: {
                    crimson: '#DC2626',
                    indigo: '#4F46E5',
                    purple: '#7C3AED',
                }
            }
        },
    },

    plugins: [forms],
};
