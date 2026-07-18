/**
 * PUNCHER.COM - Consentimento de cookies
 * Centraliza o carregamento de scripts de rastreamento não essenciais.
 * Nada é carregado antes do consentimento explícito do visitante.
 */
(function () {
    'use strict';

    var STORAGE_KEY = 'puncher_consent';
    var GA4_ID = 'G-376DZQKDWZ';

    // =========================================================
    // Textos por idioma
    // =========================================================
    var TEXTS = {
        en: {
            msg: "We use cookies (Google Analytics and Meta Pixel) to understand how visitors use our site. See our <a href='/app/terms/'>Terms of Service</a>.",
            accept: 'Accept',
            decline: 'Decline'
        },
        de: {
            msg: "Wir verwenden Cookies (Google Analytics und Meta Pixel), um zu verstehen, wie Besucher unsere Website nutzen. Siehe unsere <a href='/app/terms/'>Nutzungsbedingungen</a>.",
            accept: 'Akzeptieren',
            decline: 'Ablehnen'
        },
        fr: {
            msg: "Nous utilisons des cookies (Google Analytics et Meta Pixel) pour comprendre comment les visiteurs utilisent notre site. Consultez nos <a href='/app/terms/'>Conditions d'utilisation</a>.",
            accept: 'Accepter',
            decline: 'Refuser'
        },
        es: {
            msg: "Utilizamos cookies (Google Analytics y Meta Pixel) para entender cómo los visitantes usan nuestro sitio. Consulte nuestros <a href='/app/terms/'>Términos de servicio</a>.",
            accept: 'Aceptar',
            decline: 'Rechazar'
        },
        it: {
            msg: "Utilizziamo i cookie (Google Analytics e Meta Pixel) per capire come i visitatori usano il nostro sito. Consulta i nostri <a href='/app/terms/'>Termini di servizio</a>.",
            accept: 'Accetta',
            decline: 'Rifiuta'
        }
    };

    // Mesmo mecanismo de detecção usado em js/main.js
    function detectLang() {
        var seg = window.location.pathname.split('/')[1];
        return TEXTS[seg] ? seg : 'en';
    }

    // =========================================================
    // Injeção dos scripts de rastreamento
    // =========================================================
    function loadGA4() {
        if (window.__puncherGA4Loaded) return;
        window.__puncherGA4Loaded = true;

        var s = document.createElement('script');
        s.async = true;
        s.src = 'https://www.googletagmanager.com/gtag/js?id=' + GA4_ID;
        document.head.appendChild(s);

        window.dataLayer = window.dataLayer || [];
        window.gtag = function () { window.dataLayer.push(arguments); };
        window.gtag('js', new Date());
        window.gtag('config', GA4_ID);
    }

    /*
     * Meta Pixel — ID 1380381974131578
     *
     * DESATIVADO POR ORA. O Pixel roda hoje apenas no app WordPress, fora
     * deste repositório; o site estático nunca o carregou. Ativar aqui é
     * decisão futura — ao fazê-lo, verificar se não haverá disparo duplicado
     * entre o app e o site estático.
     *
     * Para ativar: descomentar a função e chamar loadMetaPixel() dentro de
     * loadTracking(), ao lado de loadGA4().
     *
     * function loadMetaPixel() {
     *     if (window.__puncherPixelLoaded) return;
     *     window.__puncherPixelLoaded = true;
     *
     *     !function(f,b,e,v,n,t,s)
     *     {if(f.fbq)return;n=f.fbq=function(){n.callMethod?
     *     n.callMethod.apply(n,arguments):n.queue.push(arguments)};
     *     if(!f._fbq)f._fbq=n;n.push=n;n.loaded=!0;n.version='2.0';
     *     n.queue=[];t=b.createElement(e);t.async=!0;
     *     t.src=v;s=b.getElementsByTagName(e)[0];
     *     s.parentNode.insertBefore(t,s)}(window, document,'script',
     *     'https://connect.facebook.net/en_US/fbevents.js');
     *     fbq('init', '1380381974131578');
     *     fbq('track', 'PageView');
     * }
     */

    function loadTracking() {
        loadGA4();
        // loadMetaPixel();  // ver bloco acima
    }

    // =========================================================
    // Banner
    // =========================================================
    var bannerEl = null;

    function removeBanner() {
        if (bannerEl && bannerEl.parentNode) {
            bannerEl.parentNode.removeChild(bannerEl);
        }
        bannerEl = null;
    }

    function save(value) {
        try {
            localStorage.setItem(STORAGE_KEY, value);
        } catch (e) {
            // localStorage indisponível (modo privado / cookies bloqueados):
            // segue sem persistir — o banner reaparece na próxima visita.
        }
    }

    function read() {
        try {
            return localStorage.getItem(STORAGE_KEY);
        } catch (e) {
            return null;
        }
    }

    function buildBanner() {
        var t = TEXTS[detectLang()];

        var bar = document.createElement('div');
        bar.id = 'puncher-consent-banner';
        bar.setAttribute('role', 'dialog');
        bar.setAttribute('aria-live', 'polite');
        bar.style.cssText =
            'position:fixed;bottom:0;left:0;right:0;' +
            'background:#0d1b2a;color:rgba(255,255,255,.85);' +
            "font-family:'Inter',sans-serif;font-size:.9rem;line-height:1.5;" +
            'padding:16px 20px;z-index:9999;' +
            'display:flex;flex-wrap:wrap;align-items:center;justify-content:center;' +
            'gap:16px;box-shadow:0 -2px 12px rgba(0,0,0,.25);';

        var text = document.createElement('div');
        text.innerHTML = t.msg;
        text.style.cssText = 'flex:1 1 420px;max-width:760px;';
        var link = text.querySelector('a');
        if (link) {
            link.style.color = '#e6b050';
            link.style.textDecoration = 'underline';
        }

        var actions = document.createElement('div');
        actions.style.cssText = 'display:flex;gap:10px;flex-shrink:0;';

        var accept = document.createElement('button');
        accept.type = 'button';
        accept.textContent = t.accept;
        accept.style.cssText =
            'background:#e6b050;color:#0d1b2a;border:none;border-radius:50px;' +
            "padding:10px 26px;font-family:'Inter',sans-serif;font-size:.9rem;" +
            'font-weight:600;cursor:pointer;';

        var decline = document.createElement('button');
        decline.type = 'button';
        decline.textContent = t.decline;
        decline.style.cssText =
            'background:transparent;color:#ffffff;' +
            'border:1px solid rgba(255,255,255,.4);border-radius:50px;' +
            "padding:10px 26px;font-family:'Inter',sans-serif;font-size:.9rem;" +
            'font-weight:600;cursor:pointer;';

        accept.addEventListener('click', function () {
            save('granted');
            removeBanner();
            loadTracking();
        });

        decline.addEventListener('click', function () {
            save('denied');
            removeBanner();
        });

        actions.appendChild(accept);
        actions.appendChild(decline);
        bar.appendChild(text);
        bar.appendChild(actions);
        return bar;
    }

    function showBanner() {
        if (bannerEl) return;
        bannerEl = buildBanner();
        document.body.appendChild(bannerEl);
    }

    // =========================================================
    // Init
    // =========================================================
    function init() {
        var consent = read();

        if (consent === 'granted') {
            loadTracking();
        } else if (consent === 'denied') {
            // nada é carregado
        } else {
            showBanner();
        }

        // O rodapé é injetado de forma assíncrona pelo main.js, então o
        // listener vai no document (delegação) em vez de no elemento.
        document.addEventListener('click', function (e) {
            var target = e.target.closest ? e.target.closest('#cookie-settings') : null;
            if (target) {
                e.preventDefault();
                window.openCookieSettings();
            }
        });
    }

    // Reabre o banner: apaga a preferência e pergunta de novo.
    window.openCookieSettings = function () {
        try {
            localStorage.removeItem(STORAGE_KEY);
        } catch (e) { /* ignora */ }
        removeBanner();
        showBanner();
    };

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }
})();
