// Load Header
fetch('includes/header.html')
    .then(response => response.text())
    .then(data => {
        document.getElementById('header-container').innerHTML = data;
        
        // Set active link based on current page
        const currentPage = window.location.pathname.split('/').pop();
        const navLinks = {
            'index.html': 'nav-home',
            'categories.html': 'nav-categories',
            'trending.html': 'nav-trending',
            'popular-collections.html': 'nav-collections',
            'premium.html': 'nav-premium'
        };
        
        // Remove active class from all links
        document.querySelectorAll('.nav-link-custom').forEach(link => {
            link.classList.remove('active');
        });
        
        // Add active class to current page link
        if (navLinks[currentPage]) {
            const activeLink = document.getElementById(navLinks[currentPage]);
            if (activeLink) {
                activeLink.classList.add('active');
            }
        }
        
        // Initialize mobile menu
        const navbarToggler = document.querySelector('.navbar-toggler');
        if (navbarToggler) {
            navbarToggler.addEventListener('click', function() {
                const navbarCollapse = document.getElementById('navbarNav');
                navbarCollapse.classList.toggle('show');
            });
        }
    })
    .catch(error => console.error('Error loading header:', error));

// Load Footer
fetch('includes/footer.html')
    .then(response => response.text())
    .then(data => {
        document.getElementById('footer-container').innerHTML = data;
        
        // Add back to top functionality after footer loads
        const backToTopButton = document.querySelector('.back-to-top');
        
        if (backToTopButton) {
            window.addEventListener('scroll', () => {
                if (window.pageYOffset > 300) {
                    backToTopButton.classList.add('active');
                } else {
                    backToTopButton.classList.remove('active');
                }
            });
            
            backToTopButton.addEventListener('click', (e) => {
                e.preventDefault();
                window.scrollTo({
                    top: 0,
                    behavior: 'smooth'
                });
            });
        }
    })
    .catch(error => console.error('Error loading footer:', error));