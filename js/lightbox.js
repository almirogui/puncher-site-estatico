/**
 * PUNCHER.COM - Simple Lightbox Gallery
 * Features: Open/Close, Navigation (arrows + keyboard), ESC to close
 */

(function() {
    'use strict';

    let currentIndex = 0;
    let galleryImages = [];
    let lightboxElement = null;

    // Create lightbox HTML
    function createLightbox() {
        const lightbox = document.createElement('div');
        lightbox.id = 'lightbox';
        lightbox.innerHTML = `
            <div class="lightbox-overlay"></div>
            <div class="lightbox-content">
                <button class="lightbox-close" aria-label="Close">&times;</button>
                <button class="lightbox-prev" aria-label="Previous">&#10094;</button>
                <img class="lightbox-image" src="" alt="">
                <button class="lightbox-next" aria-label="Next">&#10095;</button>
                <div class="lightbox-counter"></div>
            </div>
        `;
        document.body.appendChild(lightbox);
        return lightbox;
    }

    // Open lightbox
    function openLightbox(index) {
        if (!lightboxElement) {
            lightboxElement = createLightbox();
            bindLightboxEvents();
        }
        
        currentIndex = index;
        updateLightboxImage();
        lightboxElement.classList.add('active');
        document.body.style.overflow = 'hidden';
    }

    // Close lightbox
    function closeLightbox() {
        if (lightboxElement) {
            lightboxElement.classList.remove('active');
            document.body.style.overflow = '';
        }
    }

    // Update displayed image
    function updateLightboxImage() {
        const img = lightboxElement.querySelector('.lightbox-image');
        const counter = lightboxElement.querySelector('.lightbox-counter');
        
        img.src = galleryImages[currentIndex].src;
        img.alt = galleryImages[currentIndex].alt || 'Gallery image';
        counter.textContent = `${currentIndex + 1} / ${galleryImages.length}`;
        
        // Show/hide navigation arrows
        const prevBtn = lightboxElement.querySelector('.lightbox-prev');
        const nextBtn = lightboxElement.querySelector('.lightbox-next');
        prevBtn.style.display = galleryImages.length > 1 ? 'block' : 'none';
        nextBtn.style.display = galleryImages.length > 1 ? 'block' : 'none';
    }

    // Navigate to previous image
    function prevImage() {
        currentIndex = (currentIndex - 1 + galleryImages.length) % galleryImages.length;
        updateLightboxImage();
    }

    // Navigate to next image
    function nextImage() {
        currentIndex = (currentIndex + 1) % galleryImages.length;
        updateLightboxImage();
    }

    // Bind lightbox events
    function bindLightboxEvents() {
        // Close on overlay click
        lightboxElement.querySelector('.lightbox-overlay').addEventListener('click', closeLightbox);
        
        // Close button
        lightboxElement.querySelector('.lightbox-close').addEventListener('click', closeLightbox);
        
        // Navigation buttons
        lightboxElement.querySelector('.lightbox-prev').addEventListener('click', function(e) {
            e.stopPropagation();
            prevImage();
        });
        
        lightboxElement.querySelector('.lightbox-next').addEventListener('click', function(e) {
            e.stopPropagation();
            nextImage();
        });

        // Keyboard navigation
        document.addEventListener('keydown', function(e) {
            if (!lightboxElement.classList.contains('active')) return;
            
            switch(e.key) {
                case 'Escape':
                    closeLightbox();
                    break;
                case 'ArrowLeft':
                    prevImage();
                    break;
                case 'ArrowRight':
                    nextImage();
                    break;
            }
        });
    }

    // Initialize gallery
    function initGallery() {
        const galleryItems = document.querySelectorAll('.gallery-item img');
        
        if (galleryItems.length === 0) return;
        
        galleryImages = Array.from(galleryItems);
        
        galleryItems.forEach((img, index) => {
            img.style.cursor = 'pointer';
            img.addEventListener('click', function() {
                openLightbox(index);
            });
        });
    }

    // Initialize on DOM ready
    document.addEventListener('DOMContentLoaded', initGallery);

})();
