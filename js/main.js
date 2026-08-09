/**
 * PUNCHER.COM - Main JavaScript
 * Static HTML Version
 */

// =========================================================
// I18N — dicionário de mensagens de interface
// =========================================================
var UI_LANG = (function () {
    var seg = window.location.pathname.split('/')[1];
    return ['de', 'es', 'fr', 'it'].indexOf(seg) !== -1 ? seg : 'en';
})();

var UI_TEXTS = {
    en: {
        dimRequired:  'Please enter at least one dimension (width or height).',
        unitRequired: 'Please select a unit of measurement (cm or inches).',
        sending:      'Sending...',
        quoteSent:    'Quote request sent! We will contact you soon.',
        quoteError:   'Error sending request. Please try again.',
        messageSent:  'Message sent! We will respond soon.',
        messageError: 'Error sending message. Please try again.',
        changeFile:   '📎 Change file',
        chooseFile:   '📎 Click to choose a file'
    },
    de: {
        dimRequired:  'Bitte geben Sie mindestens eine Abmessung an (Breite oder Höhe).',
        unitRequired: 'Bitte wählen Sie eine Maßeinheit (cm oder Zoll).',
        sending:      'Wird gesendet...',
        quoteSent:    'Angebotsanfrage gesendet! Wir melden uns in Kürze bei Ihnen.',
        quoteError:   'Fehler beim Senden der Anfrage. Bitte versuchen Sie es erneut.',
        messageSent:  'Nachricht gesendet! Wir antworten Ihnen in Kürze.',
        messageError: 'Fehler beim Senden der Nachricht. Bitte versuchen Sie es erneut.',
        changeFile:   '📎 Datei ändern',
        chooseFile:   '📎 Klicken zum Auswählen'
    },
    es: {
        dimRequired:  'Introduzca al menos una dimensión (ancho o alto).',
        unitRequired: 'Seleccione una unidad de medida (cm o pulgadas).',
        sending:      'Enviando...',
        quoteSent:    '¡Solicitud de presupuesto enviada! Nos pondremos en contacto con usted en breve.',
        quoteError:   'Error al enviar la solicitud. Inténtelo de nuevo.',
        messageSent:  '¡Mensaje enviado! Le responderemos en breve.',
        messageError: 'Error al enviar el mensaje. Inténtelo de nuevo.',
        changeFile:   '📎 Cambiar archivo',
        chooseFile:   '📎 Haga clic para seleccionar un archivo'
    },
    fr: {
        dimRequired:  'Veuillez indiquer au moins une dimension (largeur ou hauteur).',
        unitRequired: 'Veuillez sélectionner une unité de mesure (cm ou pouces).',
        sending:      'Envoi en cours...',
        quoteSent:    'Demande de devis envoyée ! Nous vous contacterons très prochainement.',
        quoteError:   "Erreur lors de l'envoi de la demande. Veuillez réessayer.",
        messageSent:  'Message envoyé ! Nous vous répondrons très prochainement.',
        messageError: "Erreur lors de l'envoi du message. Veuillez réessayer.",
        changeFile:   '📎 Changer de fichier',
        chooseFile:   '📎 Cliquez pour choisir un fichier'
    },
    it: {
        dimRequired:  'Inserisci almeno una dimensione (larghezza o altezza).',
        unitRequired: "Seleziona un'unità di misura (cm o pollici).",
        sending:      'Invio in corso...',
        quoteSent:    'Richiesta di preventivo inviata! Ti contatteremo a breve.',
        quoteError:   "Errore durante l'invio della richiesta. Riprova.",
        messageSent:  'Messaggio inviato! Ti risponderemo a breve.',
        messageError: "Errore durante l'invio del messaggio. Riprova.",
        changeFile:   '📎 Cambia file',
        chooseFile:   '📎 Clicca per scegliere un file'
    }
};

function t(key) {
    var pack = UI_TEXTS[UI_LANG] || UI_TEXTS.en;
    return (pack[key] !== undefined) ? pack[key] : UI_TEXTS.en[key];
}

// =========================================================
// MENU INJECT — carrega menu.html em todas as páginas
// =========================================================
(function injectMenu() {
    var depth  = (window.location.pathname.match(/\//g) || []).length;
    var base   = depth > 1 ? '../' : '';
    var lang = window.location.pathname.split('/')[1];
    var langMenus = {'de':'menu-de.html','es':'menu-es.html','fr':'menu-fr.html','it':'menu-it.html'};
    var menuFile = langMenus[lang] || 'menu.html';
    var menuUrl = base + menuFile + '?v=2026a';

    fetch(menuUrl)
        .then(function(r) { return r.text(); })
        .then(function(html) {
            var existing  = document.querySelector('header');
            var temp      = document.createElement('div');
            temp.innerHTML = html;
            var newHeader = temp.querySelector('header');

            if (existing) {
                existing.parentNode.replaceChild(newHeader, existing);
            } else {
                document.body.insertBefore(newHeader, document.body.firstChild);
            }

            // Marcar item ativo conforme página atual
            var page = window.location.pathname.split('/').pop() || 'index.html';
            var path = window.location.pathname;
            document.querySelectorAll('#nav-menu a').forEach(function(a) {
                var href = a.getAttribute('href');
                if (!href) return;
                if (href.charAt(href.length - 1) === '/') {
                    // Home do idioma ("/", "/de/", ...): casa com a URL de
                    // diretório e com a variante index.html
                    if (path === href || path === href + 'index.html') {
                        a.classList.add('active');
                    }
                } else if (href === page) {
                    a.classList.add('active');
                }
            });

            // Marcar Services como active em subpáginas de serviço
            var servicePages = ['vector-service.html', 'embroidery-file-formats.html', 'partner-companies.html'];
            if (servicePages.indexOf(page) !== -1) {
                var dropLink = document.querySelector('.nav-dropdown > a');
                if (dropLink) dropLink.classList.add('active');
            }

            // Mobile dropdown toggle
            document.querySelectorAll('.nav-dropdown > a').forEach(function(link) {
                link.addEventListener('click', function(e) {
                    var isMobile = window.getComputedStyle(
                        document.querySelector('.mobile-menu-btn')
                    ).display !== 'none';
                    if (isMobile) {
                        e.preventDefault();
                        this.parentElement.classList.toggle('open');
                    }
                });
            });

            // Fechar menu mobile ao clicar fora
            document.addEventListener('click', function(e) {
                var menu    = document.getElementById('nav-menu');
                var menuBtn = document.querySelector('.mobile-menu-btn');
                if (menu && menu.classList.contains('active')) {
                    if (!menu.contains(e.target) && menuBtn && !menuBtn.contains(e.target)) {
                        menu.classList.remove('active');
                    }
                }
            });

            // Re-init language switcher se existir
            if (typeof initLanguageSwitcher === 'function') {
                initLanguageSwitcher();
            }
        })
        .catch(function(err) {
            console.warn('Menu inject failed:', err);
        });
})();

// =========================================================
// FOOTER INJECT — carrega footer.html em todas as páginas
// =========================================================
(function injectFooter() {
    var depth = (window.location.pathname.match(/\//g) || []).length;
    var base  = depth > 1 ? '../' : '';
    var lang = window.location.pathname.split('/')[1];
    var langFooters = {'de':'footer-de.html','es':'footer-es.html','fr':'footer-fr.html','it':'footer-it.html'};
    var footerFile = langFooters[lang] || 'footer.html';
    var footerUrl = base + footerFile + '?v=2026a';

    // Dispara o fetch de imediato, mas só injeta com o DOM pronto:
    // o rodapé vai como último filho do <body> e precisa que o corpo
    // da página já tenha sido totalmente parseado.
    function whenReady(fn) {
        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', fn);
        } else {
            fn();
        }
    }

    fetch(footerUrl)
        .then(function(r) { return r.text(); })
        .then(function(html) {
            whenReady(function() {
                var temp = document.createElement('div');
                temp.innerHTML = html;
                var newFooter = temp.querySelector('footer');
                if (!newFooter) return;

                // Substitui rodapé estático se existir (migração gradual),
                // senão anexa ao final do body.
                var existing = document.querySelector('footer');
                if (existing) {
                    existing.parentNode.replaceChild(newFooter, existing);
                } else {
                    document.body.appendChild(newFooter);
                }

                var yearEl = document.getElementById('footer-year');
                if (yearEl) {
                    yearEl.textContent = new Date().getFullYear();
                }
            });
        })
        .catch(function(err) {
            console.warn('Footer inject failed:', err);
        });
})();

// =========================================================
// MOBILE MENU TOGGLE
// =========================================================
function toggleMenu() {
  var menu = document.getElementById('nav-menu');
  var menuBtn = document.querySelector('.mobile-menu-btn');
  if (menu) {
    menu.classList.toggle('active');
    menuBtn.classList.toggle('active');  // <-- Adicione esta linha
  }
}


// =========================================================
// FAQ TOGGLE
// =========================================================
function toggleFaq(element) {
    const faqItem = element.parentElement;
    const isActive = faqItem.classList.contains('active');
    document.querySelectorAll('.faq-item').forEach(item => item.classList.remove('active'));
    if (!isActive) faqItem.classList.add('active');
}

// =========================================================
// TOAST NOTIFICATION
// =========================================================
function showToast(message, type = 'success') {
    document.querySelectorAll('.toast').forEach(t => t.remove());
    const toast = document.createElement('div');
    toast.className = `toast ${type}`;
    toast.textContent = message;
    document.body.appendChild(toast);
    setTimeout(() => { toast.remove(); }, 5000);
}

// =========================================================
// DOM READY
// =========================================================
document.addEventListener('DOMContentLoaded', function() {

    // Quote Form
    const quoteForm = document.getElementById('quote-form');
    if (quoteForm) {
        quoteForm.addEventListener('submit', function(e) {
            e.preventDefault();
            const service  = quoteForm.querySelector('[name="service"]').value;
            const widthEl  = quoteForm.querySelector('[name="width"]');
            const heightEl = quoteForm.querySelector('[name="height"]');
            const unitEl   = quoteForm.querySelector('[name="unit"]:checked');
            const hasWidth     = widthEl  && widthEl.value  && parseFloat(widthEl.value)  > 0;
            const hasHeight    = heightEl && heightEl.value && parseFloat(heightEl.value) > 0;
            const hasDimension = hasWidth || hasHeight;
            if ((service === 'digitizing' || service === 'both') && !hasDimension) {
                showToast(t('dimRequired'), 'error');
                return;
            }
            if (hasDimension && !unitEl) {
                showToast(t('unitRequired'), 'error');
                return;
            }
            const formData   = new FormData(this);
            const submitBtn  = this.querySelector('.form-submit');
            const origText   = submitBtn.textContent;
            submitBtn.textContent = t('sending');
            submitBtn.disabled    = true;
            fetch('/php/send-quote.php', { method: 'POST', body: formData })
                .then(r => r.json())
                .then(data => {
                    if (data.success) { showToast(t('quoteSent'), 'success'); quoteForm.reset(); if (window.resetFilePreviews) window.resetFilePreviews(); }
                    else              { showToast(t('quoteError'), 'error'); }
                })
                .catch(() => showToast(t('quoteError'), 'error'))
                .finally(() => { submitBtn.textContent = origText; submitBtn.disabled = false; });
        });
    }

    // Contact Form
    const contactForm = document.getElementById('contact-form');
    if (contactForm) {
        contactForm.addEventListener('submit', function(e) {
            e.preventDefault();
            const formData  = new FormData(this);
            const submitBtn = this.querySelector('.form-submit');
            const origText  = submitBtn.textContent;
            submitBtn.textContent = t('sending');
            submitBtn.disabled    = true;
            fetch('/php/send-contact.php', { method: 'POST', body: formData })
                .then(r => r.json())
                .then(data => {
                    if (data.success) { showToast(t('messageSent'), 'success'); contactForm.reset(); }
                    else              { showToast(t('messageError'), 'error'); }
                })
                .catch(() => showToast(t('messageError'), 'error'))
                .finally(() => { submitBtn.textContent = origText; submitBtn.disabled = false; });
        });
    }

    // Smooth scroll
    document.querySelectorAll('a[href^="#"]').forEach(anchor => {
        anchor.addEventListener('click', function(e) {
            e.preventDefault();
            const target = document.querySelector(this.getAttribute('href'));
            if (target) target.scrollIntoView({ behavior: 'smooth', block: 'start' });
        });
    });

    // Scroll shadow no header
    window.addEventListener('scroll', function() {
        const header = document.querySelector('header');
        if (!header) return;
        header.style.boxShadow = window.pageYOffset > 100
            ? '0 2px 20px rgba(0,0,0,0.15)'
            : '0 2px 10px rgba(0,0,0,0.1)';
    });

});

// =========================================================
// ANIMATE ON SCROLL
// =========================================================
function animateOnScroll() {
    const els = document.querySelectorAll('.feature-card, .step, .gallery-item, .partner-card');
    const obs = new IntersectionObserver((entries) => {
        entries.forEach(e => {
            if (e.isIntersecting) { e.target.classList.add('animate-on-scroll'); obs.unobserve(e.target); }
        });
    }, { threshold: 0.1 });
    els.forEach(el => obs.observe(el));
}
document.addEventListener('DOMContentLoaded', animateOnScroll);

// =========================================================
// FILE UPLOAD
// =========================================================
function showFileName(input) {
    var fileInfo  = document.getElementById('file-info');
    var fileName  = document.getElementById('file-name');
    var fileSize  = document.getElementById('file-size');
    var fileIcon  = document.getElementById('file-icon');
    var fileLabel = document.getElementById('file-label');
    if (input.files && input.files[0]) {
        var file = input.files[0];
        var s    = file.size;
        var sText = s < 1024 ? s + ' B' : s < 1048576 ? (s/1024).toFixed(1) + ' KB' : (s/1048576).toFixed(1) + ' MB';
        var ext   = file.name.split('.').pop().toLowerCase();
        fileIcon.textContent  = ['jpg','jpeg','png','gif','webp'].includes(ext) ? '🖼️' : ext === 'pdf' ? '📕' : ['ai','eps','svg'].includes(ext) ? '🎨' : '📄';
        fileName.textContent  = file.name;
        fileSize.textContent  = '(' + sText + ')';
        fileInfo.style.display = 'block';
        fileLabel.textContent = t('changeFile');
    }
}

function removeFile() {
    var input = document.getElementById('file');
    input.value = '';
    document.getElementById('file-info').style.display = 'none';
    document.getElementById('file-label').textContent  = t('chooseFile');
}
document.addEventListener('click', function(e) {
  var menu = document.getElementById('nav-menu');
  var menuBtn = document.querySelector('.mobile-menu-btn');
  if (menu && menu.classList.contains('active') && 
      !menu.contains(e.target) && !menuBtn.contains(e.target)) {
    menu.classList.remove('active');
  }
});
