/**
 * Theme Manager — Expense Tracker
 * Runs synchronously in <head> to apply theme before page renders (prevents FOUC).
 * Supports: 'light' | 'dark' | 'system'
 */
(function () {
    'use strict';

    var STORAGE_KEY = 'et_theme';

    function applyTheme(theme) {
        var root = document.documentElement;
        if (theme === 'dark') {
            root.setAttribute('data-theme', 'dark');
        } else if (theme === 'light') {
            root.setAttribute('data-theme', 'light');
        } else {
            // system — follow OS preference
            var prefersDark = window.matchMedia('(prefers-color-scheme: dark)').matches;
            root.setAttribute('data-theme', prefersDark ? 'dark' : 'light');
        }
    }

    // Apply immediately
    var saved = localStorage.getItem(STORAGE_KEY) || 'system';
    applyTheme(saved);

    // Watch OS preference changes (only matters when theme === 'system')
    try {
        window.matchMedia('(prefers-color-scheme: dark)').addEventListener('change', function () {
            if ((localStorage.getItem(STORAGE_KEY) || 'system') === 'system') {
                applyTheme('system');
            }
        });
    } catch (e) { /* older browsers */ }

    // Public API
    window.ThemeManager = {
        set: function (theme) {
            localStorage.setItem(STORAGE_KEY, theme);
            applyTheme(theme);
        },
        get: function () {
            return localStorage.getItem(STORAGE_KEY) || 'system';
        },
        isDark: function () {
            return document.documentElement.getAttribute('data-theme') === 'dark';
        }
    };
}());
