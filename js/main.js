// main.js - Basic functionality for WallHub

// Back to top button functionality
document.addEventListener('DOMContentLoaded', function() {
    const backToTopButton = document.querySelector('.back-to-top');
    
    if (backToTopButton) {
        // Show/hide back to top button
        window.addEventListener('scroll', () => {
            if (window.pageYOffset > 300) {
                backToTopButton.classList.add('active');
            } else {
                backToTopButton.classList.remove('active');
            }
        });

        // Smooth scroll to top
        backToTopButton.addEventListener('click', (e) => {
            e.preventDefault();
            window.scrollTo({
                top: 0,
                behavior: 'smooth'
            });
        });
    }
    
    // Add click functionality to wallpaper cards
    document.querySelectorAll('.wallpaper-card').forEach(card => {
        card.addEventListener('click', function() {
            // If card already has onclick, use it
            if (this.getAttribute('onclick')) {
                return;
            }
            
            // Otherwise look for a link inside the card
            const link = this.querySelector('a[href]');
            if (link && link.href) {
                window.location.href = link.href;
            }
        });
    });
    
    // Prevent download button clicks from triggering card navigation
    document.querySelectorAll('.download-btn').forEach(btn => {
        btn.addEventListener('click', (e) => {
            e.stopPropagation();
        });
    });
    
    // Add hover effects to category buttons
    document.querySelectorAll('.category-btn').forEach(btn => {
        btn.addEventListener('mouseenter', () => {
            btn.style.transform = 'translateY(-3px)';
        });
        btn.addEventListener('mouseleave', () => {
            btn.style.transform = 'translateY(0)';
        });
    });
    
    // Add img-fluid class to all images for responsiveness
    document.querySelectorAll('img').forEach(img => {
        if (!img.classList.contains('img-fluid') && !img.classList.contains('wallpaper-img')) {
            img.classList.add('img-fluid');
        }
    });
});