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
    
    // Cursor follower функциональность отключена (функция не определена)
    // Можно добавить позже, если потребуется
    
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
    
    // ============================================
    // UNIFIED SLIDER (Hero + Promo)
    // ============================================
    let sliderInitialized = false;
    
    function initUnifiedSlider() {
        // Предотвращаем повторную инициализацию
        if (sliderInitialized) {
            return;
        }
        
        const unifiedSlider = document.querySelector('.unified-slider-wrapper');
        if (!unifiedSlider) {
            console.warn('Unified slider wrapper not found');
            return;
        }
        
        // Проверяем, не инициализирован ли уже слайдер
        if (unifiedSlider.dataset.initialized === 'true') {
            return;
        }
        
        const slides = unifiedSlider.querySelectorAll('.unified-slide');
        if (slides.length === 0) {
            console.warn('No slides found');
            return;
        }
        
        console.log('Initializing unified slider with', slides.length, 'slides');
        
        // Помечаем как инициализированный
        unifiedSlider.dataset.initialized = 'true';
        sliderInitialized = true;
        
        const indicators = document.querySelectorAll('.unified-indicator');
        const prevBtn = document.querySelector('.unified-slider-prev');
        const nextBtn = document.querySelector('.unified-slider-next');
        
        let currentSlide = 0;
        let autoSlideInterval = null;
        const slideDuration = 7000; // 7 секунд
        
        // Функция для переключения слайда
        function goToSlide(index) {
            if (index < 0 || index >= slides.length) return;
            
            // Убираем активный класс со всех слайдов и индикаторов
            slides.forEach(slide => slide.classList.remove('unified-slide-active'));
            indicators.forEach(indicator => indicator.classList.remove('unified-indicator-active'));
            
            // Добавляем активный класс к текущему слайду и индикатору
            if (slides[index]) {
                slides[index].classList.add('unified-slide-active');
            }
            if (indicators[index]) {
                indicators[index].classList.add('unified-indicator-active');
            }
            
            currentSlide = index;
            
            // Перезапускаем анимацию для нового слайда
            const activeSlide = slides[index];
            if (activeSlide) {
                const labels = activeSlide.querySelectorAll('.promo-label, .promo-label-secondary');
                const mainContent = activeSlide.querySelector('.promo-main-content');
                
                // Сбрасываем анимации
                labels.forEach(label => {
                    label.style.animation = 'none';
                    void label.offsetWidth; // Trigger reflow
                    label.style.animation = null;
                });
                
                if (mainContent) {
                    mainContent.style.animation = 'none';
                    void mainContent.offsetWidth;
                    mainContent.style.animation = null;
                }
            }
        }
        
        // Функция для следующего слайда
        function nextSlide() {
            const next = (currentSlide + 1) % slides.length;
            goToSlide(next);
        }
        
        // Функция для предыдущего слайда
        function prevSlide() {
            const prev = (currentSlide - 1 + slides.length) % slides.length;
            goToSlide(prev);
        }
        
        // Автоматическое переключение слайдов
        function startAutoSlide() {
            stopAutoSlide(); // Очищаем предыдущий интервал если есть
            autoSlideInterval = setInterval(() => {
                nextSlide();
            }, slideDuration);
        }
        
        function stopAutoSlide() {
            if (autoSlideInterval) {
                clearInterval(autoSlideInterval);
                autoSlideInterval = null;
            }
        }
        
        // Обработчики для кнопок навигации
        if (nextBtn) {
            nextBtn.addEventListener('click', (e) => {
                e.preventDefault();
                stopAutoSlide();
                nextSlide();
                startAutoSlide();
            });
        } else {
            console.warn('Next button not found');
        }
        
        if (prevBtn) {
            prevBtn.addEventListener('click', (e) => {
                e.preventDefault();
                stopAutoSlide();
                prevSlide();
                startAutoSlide();
            });
        } else {
            console.warn('Prev button not found');
        }
        
        // Обработчики для индикаторов
        indicators.forEach((indicator) => {
            indicator.addEventListener('click', (e) => {
                e.preventDefault();
                const slideIndex = parseInt(indicator.getAttribute('data-slide'));
                if (!isNaN(slideIndex)) {
                    stopAutoSlide();
                    goToSlide(slideIndex);
                    startAutoSlide();
                }
            });
        });
        
        // Останавливаем автопереключение при наведении мыши
        unifiedSlider.addEventListener('mouseenter', stopAutoSlide);
        unifiedSlider.addEventListener('mouseleave', startAutoSlide);
        
        // Убеждаемся, что первый слайд активен
        goToSlide(0);
        
        // Запускаем автопереключение с небольшой задержкой
        setTimeout(() => {
            startAutoSlide();
        }, 1000);
        
        // Анимация промокода при загрузке
        const promoCode = document.getElementById('promo-code');
        if (promoCode) {
            // Добавляем эффект "копирования" при клике
            promoCode.addEventListener('click', function() {
                const code = this.textContent;
                navigator.clipboard.writeText(code).then(() => {
                    const originalText = this.textContent;
                    this.textContent = 'СКОПИРОВАНО!';
                    this.style.background = 'rgba(76, 175, 80, 0.3)';
                    this.style.borderColor = 'rgba(76, 175, 80, 0.6)';
                    
                    setTimeout(() => {
                        this.textContent = originalText;
                        this.style.background = '';
                        this.style.borderColor = '';
                    }, 2000);
                }).catch(() => {
                    // Fallback для старых браузеров
                    const textArea = document.createElement('textarea');
                    textArea.value = code;
                    textArea.style.position = 'fixed';
                    textArea.style.opacity = '0';
                    document.body.appendChild(textArea);
                    textArea.select();
                    document.execCommand('copy');
                    document.body.removeChild(textArea);
                    
                    const originalText = this.textContent;
                    this.textContent = 'СКОПИРОВАНО!';
                    this.style.background = 'rgba(76, 175, 80, 0.3)';
                    this.style.borderColor = 'rgba(76, 175, 80, 0.6)';
                    
                    setTimeout(() => {
                        this.textContent = originalText;
                        this.style.background = '';
                        this.style.borderColor = '';
                    }, 2000);
                });
            });
            
            // Добавляем курсор pointer для промокода
            promoCode.style.cursor = 'pointer';
        }
    }
    
    // Инициализируем слайдер
    // Пробуем сразу, если DOM уже загружен
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initUnifiedSlider);
    } else {
        // DOM уже загружен, инициализируем сразу
        setTimeout(initUnifiedSlider, 100);
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

