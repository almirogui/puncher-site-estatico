/**
 * PUNCHER.COM - Language Detection & Switcher
 * Detects browser language and allows manual switching via flags
 */

(function() {
    'use strict';

    // Supported languages
    const LANGUAGES = {
        'en': { name: 'English', flag: '🇺🇸', path: '/' },
        'de': { name: 'Deutsch', flag: '🇩🇪', path: '/de/' },
        'fr': { name: 'Français', flag: '🇫🇷', path: '/fr/' },
        'es': { name: 'Español', flag: '🇪🇸', path: '/es/' },
        'it': { name: 'Italiano', flag: '🇮🇹', path: '/it/' }
    };

    // Get current language from URL
    function getCurrentLang() {
        const path = window.location.pathname;
        if (path.startsWith('/de/') || path === '/de') return 'de';
        if (path.startsWith('/fr/') || path === '/fr') return 'fr';
        if (path.startsWith('/es/') || path === '/es') return 'es';
        if (path.startsWith('/it/') || path === '/it') return 'it';
        return 'en';
    }

    // Get browser language
    function getBrowserLang() {
        const browserLang = navigator.language || navigator.userLanguage;
        const lang = browserLang.substring(0, 2).toLowerCase();
        return LANGUAGES[lang] ? lang : 'en';
    }

    // Get current page name
    function getCurrentPage() {
        const path = window.location.pathname;
        // Remove language prefix
        let page = path.replace(/^\/(de|fr|es|it)\//, '/');
        // Get filename
        const parts = page.split('/');
        let filename = parts[parts.length - 1] || 'index.html';
        if (!filename.includes('.')) filename = 'index.html';
        return filename;
    }

    // Build URL for language switch
    function buildLangUrl(targetLang) {
        const currentPage = getCurrentPage();
        if (targetLang === 'en') {
            return '/' + currentPage;
        }
        return '/' + targetLang + '/' + currentPage;
    }

    // Show language suggestion banner
    function showLanguageSuggestion(suggestedLang) {
        // Check if user already dismissed
        if (localStorage.getItem('lang_suggestion_dismissed')) return;
        
        const currentLang = getCurrentLang();
        if (currentLang === suggestedLang) return;

        const langInfo = LANGUAGES[suggestedLang];
        const banner = document.createElement('div');
        banner.id = 'lang-suggestion';
        banner.innerHTML = `
            <div style="background: linear-gradient(135deg, #1a365d 0%, #2b6cb0 100%); color: white; padding: 12px 20px; text-align: center; position: fixed; top: 0; left: 0; right: 0; z-index: 10000; box-shadow: 0 2px 10px rgba(0,0,0,0.2);">
                <span style="margin-right: 15px;">
                    ${langInfo.flag} Would you like to view this page in <strong>${langInfo.name}</strong>?
                </span>
                <a href="${buildLangUrl(suggestedLang)}" style="background: #ed8936; color: white; padding: 8px 20px; border-radius: 5px; text-decoration: none; font-weight: bold; margin-right: 10px;">
                    Yes, switch to ${langInfo.name}
                </a>
                <button onclick="dismissLangSuggestion()" style="background: transparent; border: 1px solid white; color: white; padding: 8px 15px; border-radius: 5px; cursor: pointer;">
                    No, thanks
                </button>
            </div>
        `;
        document.body.insertBefore(banner, document.body.firstChild);
        
        // Add padding to body to prevent content hiding behind banner
        document.body.style.paddingTop = '60px';
    }

    // Dismiss language suggestion
    window.dismissLangSuggestion = function() {
        const banner = document.getElementById('lang-suggestion');
        if (banner) {
            banner.remove();
            document.body.style.paddingTop = '0';
        }
        localStorage.setItem('lang_suggestion_dismissed', 'true');
        // Reset after 24 hours
        setTimeout(() => localStorage.removeItem('lang_suggestion_dismissed'), 24 * 60 * 60 * 1000);
    };

    // Initialize language switcher in header
    function initLanguageSwitcher() {
        const switcher = document.getElementById('language-switcher');
        if (!switcher) return;

        const currentLang = getCurrentLang();
        
        let html = '';
        Object.keys(LANGUAGES).forEach(lang => {
            const info = LANGUAGES[lang];
            const isActive = lang === currentLang;
            const url = buildLangUrl(lang);
            
            html += `
                <a href="${url}" 
                   class="lang-flag ${isActive ? 'active' : ''}" 
                   title="${info.name}"
                   style="font-size: 24px; margin: 0 5px; text-decoration: none; opacity: ${isActive ? '1' : '0.6'}; transition: opacity 0.3s; ${isActive ? 'border-bottom: 2px solid #ed8936;' : ''}"
                   onmouseover="this.style.opacity='1'" 
                   onmouseout="this.style.opacity='${isActive ? '1' : '0.6'}'">
                    ${info.flag}
                </a>
            `;
        });
        
        switcher.innerHTML = html;
    }

    // Check browser language on first visit
    function checkBrowserLanguage() {
        // Only suggest once per session
        if (sessionStorage.getItem('lang_checked')) return;
        sessionStorage.setItem('lang_checked', 'true');
        
        const browserLang = getBrowserLang();
        const currentLang = getCurrentLang();
        
        if (browserLang !== currentLang && browserLang !== 'en') {
            // Small delay to let page render first
            setTimeout(() => showLanguageSuggestion(browserLang), 1000);
        }
    }

    // Expor globalmente para o main.js chamar após injetar menu
    window.initLanguageSwitcher = initLanguageSwitcher;

    // Initialize on DOM ready
    document.addEventListener('DOMContentLoaded', function() {
        initLanguageSwitcher();
        checkBrowserLanguage();
    });

})();
