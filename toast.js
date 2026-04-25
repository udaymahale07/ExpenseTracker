/**
 * Toast Notification System — Expense Tracker
 * Replaces browser alert() with beautiful slide-in notifications.
 * Usage: Toast.success('msg') | Toast.error('msg') | Toast.info('msg') | Toast.warning('msg')
 */
(function () {
    'use strict';

    var ICONS = {
        success: '<svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>',
        error:   '<svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><line x1="15" y1="9" x2="9" y2="15"/><line x1="9" y1="9" x2="15" y2="15"/></svg>',
        info:    '<svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>',
        warning: '<svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"/><line x1="12" y1="9" x2="12" y2="13"/><line x1="12" y1="17" x2="12.01" y2="17"/></svg>'
    };

    var STYLES = {
        success: { bg: '#d1e7dd', border: '#52ab98', text: '#0a3622', icon: '#2b6777' },
        error:   { bg: '#f8d7da', border: '#e53935', text: '#842029', icon: '#e53935' },
        info:    { bg: '#dbeafe', border: '#2b6777', text: '#1e3a5f', icon: '#2b6777' },
        warning: { bg: '#fff3cd', border: '#f59e0b', text: '#78350f', icon: '#d97706' }
    };

    function getContainer() {
        var el = document.getElementById('et-toast-container');
        if (!el) {
            el = document.createElement('div');
            el.id = 'et-toast-container';
            el.style.cssText = [
                'position:fixed', 'bottom:24px', 'right:24px', 'z-index:99999',
                'display:flex', 'flex-direction:column', 'gap:10px',
                'max-width:340px', 'width:calc(100% - 48px)'
            ].join(';');
            document.body.appendChild(el);
        }
        return el;
    }

    function show(message, type, duration) {
        duration = duration || 3800;
        var c = STYLES[type] || STYLES.info;
        var container = getContainer();

        var toast = document.createElement('div');
        toast.style.cssText = [
            'display:flex', 'align-items:flex-start', 'gap:12px',
            'padding:14px 16px',
            'background:' + c.bg,
            'border:1px solid ' + c.border,
            'border-left:4px solid ' + c.border,
            'border-radius:10px',
            'box-shadow:0 4px 16px rgba(0,0,0,0.12)',
            'font-family:Poppins,sans-serif',
            'font-size:0.875rem',
            'color:' + c.text,
            'transform:translateX(120%)',
            'transition:transform 0.35s cubic-bezier(0.175,0.885,0.32,1.275)',
            'cursor:pointer'
        ].join(';');

        toast.innerHTML =
            '<span style="color:' + c.icon + ';flex-shrink:0;margin-top:1px">' + (ICONS[type] || ICONS.info) + '</span>' +
            '<span style="flex:1;line-height:1.5">' + message + '</span>' +
            '<button style="background:none;border:none;cursor:pointer;color:' + c.text + ';opacity:0.5;font-size:1.2rem;line-height:1;padding:0;flex-shrink:0;margin-top:-1px" onclick="this.parentElement._dismiss()">&#x2715;</button>';

        toast._dismiss = function () {
            clearTimeout(timer);
            toast.style.transform = 'translateX(120%)';
            setTimeout(function () { if (toast.parentElement) toast.parentElement.removeChild(toast); }, 350);
        };
        toast.addEventListener('click', function (e) { if (e.target.tagName !== 'BUTTON') toast._dismiss(); });

        container.appendChild(toast);

        requestAnimationFrame(function () {
            requestAnimationFrame(function () { toast.style.transform = 'translateX(0)'; });
        });

        var timer = setTimeout(function () { toast._dismiss(); }, duration);
    }

    window.Toast = {
        success: function (msg, dur) { /* Disabled per user request */ },
        error:   function (msg, dur) { show(msg, 'error',   dur); },
        info:    function (msg, dur) { /* Disabled per user request */ },
        warning: function (msg, dur) { show(msg, 'warning', dur); }
    };
}());
