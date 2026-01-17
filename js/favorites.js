document.addEventListener('DOMContentLoaded', function () {
    // Избранные товары
    const productFavoriteButtons = document.querySelectorAll('.product-favorite');
    productFavoriteButtons.forEach(button => {
        button.addEventListener('click', function (e) {
            e.preventDefault();
            e.stopPropagation();

            const productId = this.dataset.productId;
            const isActive = this.classList.contains('active');
            const svg = this.querySelector('svg');

            if (!productId) return;

            fetch('product_favorite.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: 'action=' + (isActive ? 'remove' : 'add') + '&product_id=' + encodeURIComponent(productId)
            })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        if (isActive) {
                            this.classList.remove('active');
                            if (svg) {
                                svg.setAttribute('fill', 'none');
                            }
                            this.setAttribute('aria-label', 'В избранное');
                            
                            // Обновляем текст кнопки на странице товара, если есть
                            const buttonText = this.querySelector('span');
                            if (buttonText && this.classList.contains('btn-secondary')) {
                                buttonText.textContent = 'В избранное';
                            }
                            
                            // Если удаляем из избранного в профиле, удаляем карточку товара
                            const productCard = this.closest('.product-card');
                            const productsGrid = productCard ? productCard.closest('.products-grid') : null;
                            if (productCard && productsGrid) {
                                productCard.style.transition = 'opacity 0.3s ease, transform 0.3s ease';
                                productCard.style.opacity = '0';
                                productCard.style.transform = 'scale(0.8)';
                                setTimeout(() => {
                                    productCard.remove();
                                    
                                    // Проверяем, остались ли товары
                                    if (productsGrid && productsGrid.children.length === 0) {
                                        const section = productsGrid.closest('.profile-section');
                                        if (section) {
                                            const emptyMessage = document.createElement('p');
                                            emptyMessage.textContent = 'У вас пока нет избранных товаров';
                                            section.appendChild(emptyMessage);
                                        }
                                    }
                                }, 300);
                            }
                        } else {
                            this.classList.add('active');
                            if (svg) {
                                svg.setAttribute('fill', 'currentColor');
                            }
                            this.setAttribute('aria-label', 'Удалить из избранного');
                            
                            // Обновляем текст кнопки на странице товара, если есть
                            const buttonText = this.querySelector('span');
                            if (buttonText && this.classList.contains('btn-secondary')) {
                                buttonText.textContent = 'В избранном';
                            }
                        }
                    } else if (data.message && data.message.includes('авторизац')) {
                        window.location.href = 'login.php';
                    }
                })
                .catch(error => {
                    console.error('Ошибка при работе с избранным товаром:', error);
                });
        });
    });

    // Функция обработки клика по кнопке избранного бренда
    function handleBrandFavoriteClick(e) {
        e.preventDefault();
        e.stopPropagation();

        const button = e.target.closest('.brand-favorite-btn');
        if (!button) return;

        const brandId = button.dataset.brandId;
        const isActive = button.classList.contains('active');
        const svg = button.querySelector('svg');
        const brandItem = button.closest('.brand-item');
        const brandLink = brandItem ? brandItem.querySelector('.brand-link') : null;
        const brandName = brandLink ? brandLink.textContent.trim() : '';
        const brandHref = brandLink ? brandLink.getAttribute('href') : '';
        const brandSlug = brandHref ? brandHref.split('brand=')[1] : '';

        if (!brandId) return;

        fetch('brand_favorite.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: 'action=' + (isActive ? 'remove' : 'add') + '&brand_id=' + encodeURIComponent(brandId)
        })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    if (isActive) {
                        // Удаляем из избранного
                        button.classList.remove('active');
                        if (svg) {
                            svg.setAttribute('fill', 'none');
                        }
                        button.setAttribute('aria-label', 'Добавить в избранное');
                        
                        // Удаляем из списка избранных в левой колонке
                        const favoritesList = document.querySelector('.brands-favorites .brands-list');
                        if (favoritesList) {
                            const favoriteItem = favoritesList.querySelector(`[data-brand-id="${brandId}"]`);
                            if (favoriteItem) {
                                const listItem = favoriteItem.closest('.brand-item');
                                if (listItem) {
                                    listItem.remove();
                                    
                                    // Если список стал пустым, показываем сообщение
                                    if (favoritesList.children.length === 0) {
                                        const favoritesSection = document.querySelector('.brands-favorites');
                                        if (favoritesSection) {
                                            const emptyMessage = document.createElement('p');
                                            emptyMessage.className = 'empty-message';
                                            emptyMessage.textContent = 'Пока здесь ничего нет. Добавляйте новые бренды в своё избранное';
                                            favoritesSection.appendChild(emptyMessage);
    }
                                    }
                                }
                            }
                        }
                        
                        // Обновляем кнопку в правой колонке, если она видна
                        const allBrandButtons = document.querySelectorAll(`.brand-favorite-btn[data-brand-id="${brandId}"]`);
                        allBrandButtons.forEach(btn => {
                            if (btn !== button) {
                                btn.classList.remove('active');
                                const btnSvg = btn.querySelector('svg');
                                if (btnSvg) {
                                    btnSvg.setAttribute('fill', 'none');
                                }
                                btn.setAttribute('aria-label', 'Добавить в избранное');
                            }
                        });
                    } else {
                        // Добавляем в избранное
                        button.classList.add('active');
                        if (svg) {
                            svg.setAttribute('fill', 'currentColor');
                        }
                        button.setAttribute('aria-label', 'Удалить из избранного');
                        
                        // Обновляем все кнопки с этим brandId в правой колонке
                        const allBrandButtons = document.querySelectorAll(`.brand-favorite-btn[data-brand-id="${brandId}"]`);
                        allBrandButtons.forEach(btn => {
                            if (btn !== button) {
                                btn.classList.add('active');
                                const btnSvg = btn.querySelector('svg');
                                if (btnSvg) {
                                    btnSvg.setAttribute('fill', 'currentColor');
                                }
                                btn.setAttribute('aria-label', 'Удалить из избранного');
                            }
                        });
                        
                        // Добавляем в список избранных в левой колонке
                        const favoritesSection = document.querySelector('.brands-favorites');
                        if (favoritesSection && brandName) {
                            let favoritesList = favoritesSection.querySelector('.brands-list');
                            const emptyMessage = favoritesSection.querySelector('.empty-message');
                            
                            // Удаляем сообщение о пустом списке, если есть
                            if (emptyMessage) {
                                emptyMessage.remove();
                            }
                            
                            // Создаем список, если его нет
                            if (!favoritesList) {
                                favoritesList = document.createElement('ul');
                                favoritesList.className = 'brands-list';
                                favoritesSection.appendChild(favoritesList);
                            }
                            
                            // Проверяем, нет ли уже этого бренда в списке
                            const existingItem = favoritesList.querySelector(`[data-brand-id="${brandId}"]`);
                            if (!existingItem) {
                                const listItem = document.createElement('li');
                                listItem.className = 'brand-item';
                                
                                const link = document.createElement('a');
                                link.href = brandHref || `catalog.php?brand=${brandSlug}`;
                                link.className = 'brand-link';
                                link.textContent = brandName;
                                
                                const favoriteBtn = document.createElement('button');
                                favoriteBtn.className = 'brand-favorite-btn active';
                                favoriteBtn.setAttribute('data-brand-id', brandId);
                                favoriteBtn.setAttribute('aria-label', 'Удалить из избранного');
                                favoriteBtn.innerHTML = '<svg width="16" height="16" viewBox="0 0 24 24" fill="currentColor" stroke="currentColor" stroke-width="2"><path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"></path></svg>';
                                
                                listItem.appendChild(link);
                                listItem.appendChild(favoriteBtn);
                                favoritesList.appendChild(listItem);
                            }
                        }
                    }
                } else if (data.message && data.message.includes('авторизац')) {
                    window.location.href = 'login.php';
                }
            })
            .catch(error => {
                console.error('Ошибка при работе с избранным брендом:', error);
            });
    }

    // Избранные бренды - используем делегирование событий
    const brandsContainer = document.querySelector('.brands-container') || document.body;
    brandsContainer.addEventListener('click', function(e) {
        if (e.target.closest('.brand-favorite-btn')) {
            handleBrandFavoriteClick(e);
        }
    });

    // Переключатели подкатегорий каталога
    const navToggles = document.querySelectorAll('.catalog-nav-toggle');
    navToggles.forEach(toggle => {
        toggle.addEventListener('click', function (e) {
            e.preventDefault();
            e.stopPropagation();

            const item = this.closest('.catalog-nav-item');
            if (!item) return;
            const submenu = item.querySelector('.catalog-nav-submenu');
            const arrow = this.querySelector('.nav-arrow');

            if (!submenu) return;

            const isVisible = submenu.style.display !== 'none' && submenu.style.display !== '';
            submenu.style.display = isVisible ? 'none' : 'block';
            if (arrow) {
                arrow.style.transform = isVisible ? 'rotate(0deg)' : 'rotate(90deg)';
            }
        });
    });
});
