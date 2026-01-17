// Premium Homepage Animations and Interactions

document.addEventListener('DOMContentLoaded', function() {
    
    // Помечаем что JS загружен
    document.body.classList.add('js-loaded');
    
    // ============================================
    // FADE IN UP ANIMATIONS
    // ============================================
    const fadeElements = document.querySelectorAll('.fade-in-up[data-delay]');
    
    function animateFadeIn(element) {
        const delay = parseFloat(element.getAttribute('data-delay')) || 0;
        setTimeout(() => {
            element.classList.add('visible');
        }, delay * 1000);
    }
    
    fadeElements.forEach(element => {
        animateFadeIn(element);
    });
    
    // Проверяем, что hero секция видна
    let heroSection = document.querySelector('.hero-section');
    if (heroSection) {
        // Принудительно применяем стили если нужно
        heroSection.style.display = 'flex';
        heroSection.style.minHeight = '90vh';
        heroSection.style.position = 'relative';
    }
    
    // Убеждаемся что hero-content виден
    const heroContent = document.querySelector('.hero-content');
    if (heroContent) {
        heroContent.style.position = 'relative';
        heroContent.style.zIndex = '2';
        heroContent.style.display = 'block';
    }
    
    // ============================================
    // SCROLL ANIMATIONS
    // ============================================
    const observerOptions = {
        threshold: 0.1,
        rootMargin: '0px 0px -50px 0px'
    };
    
    const scrollObserver = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                entry.target.classList.add('visible');
                // Останавливаем наблюдение после анимации
                scrollObserver.unobserve(entry.target);
            }
        });
    }, observerOptions);
    
    // Наблюдаем за карточками преимуществ
    const featureCards = document.querySelectorAll('.feature-card[data-animate]');
    featureCards.forEach(card => {
        scrollObserver.observe(card);
    });
    
    // ============================================
    // PRODUCT CARDS STAGGER ANIMATION
    // ============================================
    const productSections = document.querySelectorAll('.products-section');
    
    const productObserver = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                const productCards = entry.target.querySelectorAll('.product-card');
                productCards.forEach((card, index) => {
                    setTimeout(() => {
                        card.style.opacity = '1';
                        card.style.transform = 'translateY(0) scale(1)';
                    }, index * 100);
                });
                productObserver.unobserve(entry.target);
            }
        });
    }, {
        threshold: 0.2
    });
    
    productSections.forEach(section => {
        productObserver.observe(section);
    });
    
    // ============================================
    // PARALLAX EFFECT FOR HERO
    // ============================================
    // heroSection уже объявлена выше
    if (heroSection) {
        let lastScrollY = window.scrollY;
        
        function updateParallax() {
            const scrollY = window.scrollY;
            const heroHeight = heroSection.offsetHeight;
            
            if (scrollY < heroHeight) {
                const parallaxValue = scrollY * 0.5;
                const particles = heroSection.querySelectorAll('.hero-particle');
                const shapes = heroSection.querySelectorAll('.shape');
                
                // Легкое смещение частиц
                particles.forEach((particle, index) => {
                    const speed = (index % 3) * 0.1 + 0.2;
                    const offset = parallaxValue * speed;
                    particle.style.transform = `translateY(${offset}px)`;
                });
                
                // Смещение фигур
                shapes.forEach((shape, index) => {
                    const speed = (index + 1) * 0.3;
                    const offset = parallaxValue * speed;
                    shape.style.transform = `translateY(${offset}px)`;
                });
            }
            
            lastScrollY = scrollY;
        }
        
        window.addEventListener('scroll', () => {
            requestAnimationFrame(updateParallax);
        }, { passive: true });
    }
    
    // ============================================
    // SMOOTH SCROLL FOR CTA BUTTONS
    // ============================================
    const ctaButtons = document.querySelectorAll('.btn-hero, .btn-cta, .btn-hero-secondary');
    ctaButtons.forEach(button => {
        button.addEventListener('click', function(e) {
            const href = this.getAttribute('href');
            if (href && href.startsWith('#')) {
                e.preventDefault();
                const target = document.querySelector(href);
                if (target) {
                    target.scrollIntoView({
                        behavior: 'smooth',
                        block: 'start'
                    });
                }
            }
        });
    });
    
    // ============================================
    // PRODUCT NAVIGATION ARROWS (CAROUSEL)
    // ============================================
    const navArrows = document.querySelectorAll('.nav-arrow');
    navArrows.forEach(arrow => {
        arrow.addEventListener('click', function() {
            const section = this.closest('.products-section');
            const grid = section?.querySelector('.products-grid');
            
            if (grid) {
                const isRight = this.classList.contains('nav-arrow-right');
                const scrollAmount = grid.offsetWidth * 0.8;
                
                grid.scrollBy({
                    left: isRight ? scrollAmount : -scrollAmount,
                    behavior: 'smooth'
                });
            }
        });
    });
    
    // ============================================
    // INTERACTIVE HOVER EFFECTS
    // ============================================
    const interactiveElements = document.querySelectorAll('.feature-card, .product-card');
    
    interactiveElements.forEach(element => {
        element.addEventListener('mousemove', function(e) {
            const rect = this.getBoundingClientRect();
            const x = e.clientX - rect.left;
            const y = e.clientY - rect.top;
            
            const centerX = rect.width / 2;
            const centerY = rect.height / 2;
            
            const rotateX = (y - centerY) / 20;
            const rotateY = (centerX - x) / 20;
            
            this.style.transform = `perspective(1000px) rotateX(${rotateX}deg) rotateY(${rotateY}deg) translateY(-12px) scale(1.02)`;
        });
        
        element.addEventListener('mouseleave', function() {
            this.style.transform = '';
        });
    });
    
 
    
    // Активируем только на десктопе
    if (window.innerWidth > 768) {
        createCursorFollower();
        
        document.addEventListener('mousemove', (e) => {
            if (cursorFollower) {
                cursorFollower.style.display = 'block';
                cursorFollower.style.left = e.clientX - 10 + 'px';
                cursorFollower.style.top = e.clientY - 10 + 'px';
            }
        });
        
        interactiveElements.forEach(element => {
            element.addEventListener('mouseenter', () => {
                if (cursorFollower) {
                    cursorFollower.style.transform = 'scale(1.5)';
                    cursorFollower.style.borderColor = 'rgba(102, 126, 234, 0.8)';
                }
            });
            
            element.addEventListener('mouseleave', () => {
                if (cursorFollower) {
                    cursorFollower.style.transform = 'scale(1)';
                    cursorFollower.style.borderColor = 'rgba(102, 126, 234, 0.5)';
                }
            });
        });
    }
    
    // ============================================
    // LOADING ANIMATION
    // ============================================
    window.addEventListener('load', () => {
        document.body.classList.add('loaded');
        
        // Увеличиваем задержку для элементов, которые должны появиться после загрузки
        setTimeout(() => {
            fadeElements.forEach(element => {
                if (!element.classList.contains('visible')) {
                    element.classList.add('visible');
                }
            });
        }, 300);
    });
    
    // ============================================
    // PERFORMANCE: REDUCE MOTION FOR USERS WHO PREFER IT
    // ============================================
    if (window.matchMedia('(prefers-reduced-motion: reduce)').matches) {
        document.body.style.setProperty('--animation-duration', '0.01ms');
        document.body.style.setProperty('--transition-duration', '0.01ms');
    }
    
});

// ============================================
// UTILITY: THROTTLE FUNCTION
// ============================================
function throttle(func, wait) {
    let timeout;
    return function executedFunction(...args) {
        const later = () => {
            clearTimeout(timeout);
            func(...args);
        };
        clearTimeout(timeout);
        timeout = setTimeout(later, wait);
    };
}

