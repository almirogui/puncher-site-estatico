/**
 * PUNCHER.COM - Indicação de embaixador: destaque precoce no hero
 *
 * Carregado de forma SÍNCRONA no <head> das cinco homes (sem defer/async),
 * para que a classe "has-ref" esteja no <html> antes da primeira pintura.
 *
 * - Lê o código do ?ref= da URL; se não houver, lê o cookie puncher_ref
 *   (SOMENTE LEITURA: gravar/apagar o cookie é papel exclusivo do main.js,
 *   que só grava com consentimento).
 * - Código válido -> <html class="has-ref">. O CSS (style.css) inverte as
 *   cores dos botões do hero e mostra a frase de indicação.
 * - puncherRefSwap() é chamada por uma linha inline logo após o </div> de
 *   .hero-buttons e move o link de conta para o primeiro lugar no HTML
 *   (ordem visual, de Tab e de leitor de tela).
 *
 * ATENÇÃO: a regra de normalize() abaixo também existe em js/main.js
 * (função referral). As duas devem ser mantidas IGUAIS.
 */
(function () {
    // Mesma regra do normalize() de js/main.js — manter igual.
    function normalize(raw) {
        var code = String(raw || '').toUpperCase().replace(/[^A-Z0-9]/g, '').slice(0, 12);
        return code.length >= 3 ? code : null;
    }

    var code = null;
    try {
        code = normalize(new URLSearchParams(window.location.search).get('ref'));
    } catch (e) { /* navegador sem URLSearchParams */ }

    if (!code) {
        try {
            var m = document.cookie.match(/(?:^|;\s*)puncher_ref=([^;]*)/);
            if (m) code = normalize(decodeURIComponent(m[1]));
        } catch (e) { /* cookie ilegível: segue sem indicação */ }
    }

    if (code) document.documentElement.classList.add('has-ref');

    window.puncherRefSwap = function () {
        if (!document.documentElement.classList.contains('has-ref')) return;
        var box = document.querySelector('.hero .hero-buttons');
        if (!box) return;
        var account = box.querySelector('[data-ref-cta="account"]');
        if (account && box.firstElementChild !== account) {
            box.insertBefore(account, box.firstElementChild);
        }
    };
})();
