/**
 * PUNCHER.COM - Main JavaScript
 * Static HTML Version
 */

// =========================================================
// MENU INJECT — carrega menu.html em todas as páginas
// =========================================================
(function injectMenu() {
    var depth  = (window.location.pathname.match(/\//g) || []).length;
    var base   = depth > 1 ? '../' : '';
    var lang = window.location.pathname.split('/')[1];
    var langMenus = {'de':'menu-de.html','es':'menu-es.html','fr':'menu-fr.html','it':'menu-it.html'};
    var menuFile = langMenus[lang] || 'menu.html';
    var menuUrl = base + menuFile;

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
            document.querySelectorAll('#nav-menu a').forEach(function(a) {
                if (a.getAttribute('href') === page) {
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
                showToast('Please enter at least one dimension (width or height).', 'error');
                return;
            }
            if (hasDimension && !unitEl) {
                showToast('Please select a unit of measurement (cm or inches).', 'error');
                return;
            }
            const formData   = new FormData(this);
            const submitBtn  = this.querySelector('.form-submit');
            const origText   = submitBtn.textContent;
            submitBtn.textContent = 'Sending...';
            submitBtn.disabled    = true;
            fetch('php/send-quote.php', { method: 'POST', body: formData })
                .then(r => r.json())
                .then(data => {
                    if (data.success) { showToast('Quote request sent! We will contact you soon.', 'success'); quoteForm.reset(); if (window.resetFilePreviews) window.resetFilePreviews(); }
                    else              { showToast('Error: ' + data.message, 'error'); }
                })
                .catch(() => showToast('Error sending request. Please try again.', 'error'))
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
            submitBtn.textContent = 'Sending...';
            submitBtn.disabled    = true;
            fetch('php/send-contact.php', { method: 'POST', body: formData })
                .then(r => r.json())
                .then(data => {
                    if (data.success) { showToast('Message sent! We will respond soon.', 'success'); contactForm.reset(); }
                    else              { showToast('Error: ' + data.message, 'error'); }
                })
                .catch(() => showToast('Error sending message. Please try again.', 'error'))
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
        fileLabel.textContent = '📎 Change file';
    }
}

function removeFile() {
    var input = document.getElementById('file');
    input.value = '';
    document.getElementById('file-info').style.display = 'none';
    document.getElementById('file-label').textContent  = '📎 Click to choose a file';
}
document.addEventListener('click', function(e) {
  var menu = document.getElementById('nav-menu');
  var menuBtn = document.querySelector('.mobile-menu-btn');
  if (menu && menu.classList.contains('active') && 
      !menu.contains(e.target) && !menuBtn.contains(e.target)) {
    menu.classList.remove('active');
  }
});
