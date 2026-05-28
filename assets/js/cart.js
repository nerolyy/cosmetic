/**
 * URL для fetch. Не использовать глобальное `function cosmeticUrl` — в браузере оно станет window.cosmeticUrl и перезапишет разрешение путей из header.php.
 */
const cosmeticFetchUrl = function (relativePath) {
    if (typeof window.cosmeticAppPath === 'function') {
        return window.cosmeticAppPath(relativePath);
    }
    const p = String(relativePath || '').replace(/^\//, '');
    if (typeof window.COSMETIC_BASE_URL === 'string' && window.COSMETIC_BASE_URL) {
        try {
            const u = new URL(window.COSMETIC_BASE_URL);
            const root = (u.pathname || '/').replace(/\/?$/, '/');
            return root + p;
        } catch (e) {}
    }
    return '/' + p;
};

// Функция для показа toast-уведомления
function showToast(title, message, type = 'success') {
    // Удаляем существующие уведомления
    const existingToasts = document.querySelectorAll('.toast-notification');
    existingToasts.forEach(toast => toast.remove());

    // Создаем новое уведомление
    const toast = document.createElement('div');
    toast.className = `toast-notification ${type}`;
    
    const iconSvg = type === 'success' 
        ? '<svg viewBox="0 0 24 24" fill="currentColor"><path d="M9 16.17L4.83 12l-1.42 1.41L9 19 21 7l-1.41-1.41z"/></svg>'
        : '<svg viewBox="0 0 24 24" fill="currentColor"><path d="M19 6.41L17.59 5 12 10.59 6.41 5 5 6.41 10.59 12 5 17.59 6.41 19 12 13.41 17.59 19 19 17.59 13.41 12z"/></svg>';
    
    toast.innerHTML = `
        <div class="toast-notification-icon">
            ${iconSvg}
        </div>
        <div class="toast-notification-content">
            <div class="toast-notification-title">${title}</div>
            <div class="toast-notification-message">${message}</div>
        </div>
    `;
    
    document.body.appendChild(toast);
    
    // Показываем уведомление с небольшой задержкой для анимации
    setTimeout(() => {
        toast.classList.add('show');
    }, 10);
    
    // Автоматически скрываем через 3 секунды
    setTimeout(() => {
        toast.classList.remove('show');
        setTimeout(() => {
            toast.remove();
        }, 300);
    }, 3000);
}

// Глобальные переменные для промокода
let appliedPromoCode = null;
let promoDiscount = 0;

// Функция форматирования цены
function formatPrice(price) {
    return Math.round(price).toString().replace(/\B(?=(\d{3})+(?!\d))/g, ' ');
}

// Функция обновления общей суммы корзины (объединенная с учетом промокода)
function updateCartTotal() {
    const cartItems = document.querySelectorAll('.cart-item-summary');
    let baseTotal = 0;
    
    cartItems.forEach(item => {
        const price = parseFloat(item.dataset.price || 0);
        const quantity = parseInt(item.dataset.quantity || 0);
        baseTotal += price * quantity;
    });
    
    // Обновляем отображение суммы
    const totalElement = document.getElementById('cart-total');
    const totalFinalElement = document.getElementById('cart-total-final');
    const promoDiscountRow = document.getElementById('promo-discount-row');
    const promoDiscountAmount = document.getElementById('promo-discount-amount');
    
    const formattedBaseTotal = formatPrice(baseTotal);
    
    if (totalElement) {
        totalElement.textContent = formattedBaseTotal + ' Р';
    }
    
    // Применяем скидку промокода
    const finalTotal = Math.max(0, baseTotal - promoDiscount);
    const formattedFinalTotal = formatPrice(finalTotal);
    
    // Показываем/скрываем строку со скидкой
    if (promoDiscount > 0 && promoDiscountRow) {
        promoDiscountRow.style.display = 'flex';
        if (promoDiscountAmount) {
            promoDiscountAmount.textContent = '-' + formatPrice(promoDiscount) + ' Р';
        }
    } else if (promoDiscountRow) {
        promoDiscountRow.style.display = 'none';
    }
    
    if (totalFinalElement) {
        totalFinalElement.textContent = formattedFinalTotal + ' Р';
    }
}

// Функция применения промокода
function applyPromoCode() {
    const promoInput = document.getElementById('promo-code-input');
    const promoMessage = document.getElementById('promo-code-message');
    const promoInfo = document.getElementById('promo-code-info');
    const applyBtn = document.getElementById('apply-promo-btn');
    
    if (!promoInput || !promoMessage) return;
    
    const code = promoInput.value.trim().toUpperCase();
    if (!code) {
        promoMessage.textContent = 'Введите промокод';
        promoMessage.style.color = '#C62828';
        return;
    }
    
    // Получаем актуальную сумму заказа из элементов корзины
    let cartTotal = 0;
    document.querySelectorAll('.cart-item-summary').forEach(item => {
        const price = parseFloat(item.dataset.price || 0);
        const quantity = parseInt(item.dataset.quantity || 0);
        cartTotal += price * quantity;
    });
    
    if (cartTotal <= 0) {
        promoMessage.textContent = 'Корзина пуста';
        promoMessage.style.color = '#C62828';
        return;
    }
    
    applyBtn.disabled = true;
    applyBtn.textContent = 'Проверка...';
    promoMessage.textContent = '';
    promoMessage.style.color = '';
    
    fetch(cosmeticFetchUrl('api/check_promo_code.php'), {
        method: 'POST',
        credentials: 'same-origin',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({
            code: code,
            order_total: cartTotal
        })
    })
    .then(response => {
        if (!response.ok) {
            throw new Error('HTTP error! status: ' + response.status);
        }
        return response.json();
    })
    .then(data => {
        applyBtn.disabled = false;
        applyBtn.textContent = 'Применить';
        
        if (!data || typeof data !== 'object') {
            throw new Error('Invalid response format');
        }
        
        if (data.success) {
            appliedPromoCode = {
                id: data.promo_id,
                code: data.promo_code,
                description: data.description,
                discount: data.discount,
                source: data.promo_source || 'promo_codes'
            };
            promoDiscount = data.discount;
            
            // Показываем информацию о промокоде
            document.getElementById('promo-code-name').textContent = data.promo_code;
            document.getElementById('promo-discount').textContent = '-' + formatPrice(data.discount) + ' Р';
            promoInfo.style.display = 'block';
            promoMessage.textContent = 'Промокод успешно применен!';
            promoMessage.style.color = '#2E7D32';
            promoInput.style.borderColor = '#2E7D32';
            
            // Обновляем итоговую сумму
            updateCartTotal();
            
            showToast('Промокод применен', `Скидка ${formatPrice(data.discount)} Р`);
        } else {
            promoMessage.textContent = data.message || 'Ошибка при применении промокода';
            promoMessage.style.color = '#C62828';
            promoInput.style.borderColor = '#C62828';
            appliedPromoCode = null;
            promoDiscount = 0;
            promoInfo.style.display = 'none';
            updateCartTotal();
        }
    })
    .catch(error => {
        console.error('Promo code check error:', error);
        applyBtn.disabled = false;
        applyBtn.textContent = 'Применить';
        
        let errorMessage = 'Ошибка при проверке промокода. ';
        if (error.message && error.message.includes('HTTP')) {
            errorMessage += 'Проверьте подключение к интернету.';
        } else if (error.message && error.message.includes('JSON')) {
            errorMessage += 'Неверный формат ответа от сервера.';
        } else {
            errorMessage += 'Попробуйте позже или обратитесь в поддержку.';
        }
        
        promoMessage.textContent = errorMessage;
        promoMessage.style.color = '#C62828';
        promoInput.style.borderColor = '#C62828';
        appliedPromoCode = null;
        promoDiscount = 0;
        const promoInfo = document.getElementById('promo-code-info');
        if (promoInfo) promoInfo.style.display = 'none';
        updateCartTotal();
    });
}

// Функция удаления промокода
function removePromoCode() {
    appliedPromoCode = null;
    promoDiscount = 0;
    
    const promoInput = document.getElementById('promo-code-input');
    const promoMessage = document.getElementById('promo-code-message');
    const promoInfo = document.getElementById('promo-code-info');
    
    if (promoInput) promoInput.value = '';
    if (promoMessage) {
        promoMessage.textContent = '';
        promoMessage.style.color = '';
    }
    if (promoInfo) promoInfo.style.display = 'none';
    if (promoInput) promoInput.style.borderColor = '';
    
    updateCartTotal();
    showToast('Промокод удален', 'Промокод был удален из заказа');
}


document.addEventListener('DOMContentLoaded', function () {
    console.log('Cart.js loaded');
    
    // Добавление товара в корзину - используем делегирование событий
    document.addEventListener('click', function(e) {
        const cartButton = e.target.closest('.product-cart-btn') || e.target.closest('.product-cart-btn-large');
        if (!cartButton) return;
        
        e.preventDefault();
        e.stopPropagation();

        const productId = cartButton.dataset.productId;
        const isInCart = cartButton.classList.contains('in-cart');

        if (!productId) return;

        // Если товар уже в корзине, переходим на страницу корзины
        if (isInCart) {
            window.location.href = cosmeticFetchUrl('cart.php');
            return;
        }

        // Если кнопка disabled (нет в наличии), не добавляем
        if (cartButton.disabled) {
            return;
        }

        fetch(cosmeticFetchUrl('api/cart_api.php'), {
            method: 'POST',
            credentials: 'same-origin',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: 'action=add&product_id=' + encodeURIComponent(productId) + '&quantity=1'
        })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    cartButton.classList.add('in-cart');
                    cartButton.setAttribute('aria-label', 'В корзине');
                    
                    // Обновляем текст кнопки, если это большая кнопка на странице товара
                    const buttonSpan = cartButton.querySelector('span');
                    if (buttonSpan) {
                        buttonSpan.textContent = 'В корзине';
                    }
                    
                    // Визуальная обратная связь
                    cartButton.style.transform = 'scale(1.05)';
                    setTimeout(() => {
                        cartButton.style.transform = '';
                    }, 200);
                    
                    // Показываем уведомление
                    showToast('Товар добавлен', 'Товар успешно добавлен в корзину', 'success');
                } else if (data.message && data.message.includes('авторизац')) {
                    window.location.href = cosmeticFetchUrl('login.php');
                } else {
                    showToast('Ошибка', data.message || 'Ошибка при добавлении товара в корзину', 'error');
                }
            })
            .catch(error => {
                console.error('Ошибка при добавлении товара в корзину:', error);
                showToast('Ошибка', 'Произошла ошибка. Попробуйте позже.', 'error');
            });
    });

    // Переключение шагов в корзине
    const stepToggles = document.querySelectorAll('.step-toggle');
    stepToggles.forEach(toggle => {
        toggle.addEventListener('click', function (e) {
            e.preventDefault();
            const step = this.closest('.cart-step');
            const content = step.querySelector('.step-content');
            const isActive = step.classList.contains('active');
            
            if (isActive) {
                // Сворачиваем текущий шаг
                content.style.display = 'none';
                step.classList.remove('active');
                const svg = this.querySelector('svg');
                if (svg) svg.style.transform = 'rotate(0deg)';
            } else {
                // Разворачиваем шаг
                content.style.display = 'block';
                step.classList.add('active');
                const svg = this.querySelector('svg');
                if (svg) svg.style.transform = 'rotate(180deg)';
            }
        });
    });

    // Валидация и обработка заказа
    const orderBtn = document.getElementById('order-btn');
    const deliveryRadios = document.querySelectorAll('input[name="delivery"]');
    const phoneInput = document.getElementById('recipient-phone');
    const orderMessage = document.getElementById('order-message');

    // Расчет даты доставки
    function updateDeliveryDate() {
        const selectedDelivery = document.querySelector('input[name="delivery"]:checked');
        const deliveryDateText = document.getElementById('delivery-date-text');
        const deliveryDateInfo = document.getElementById('delivery-date-info');
        
        if (!selectedDelivery || !deliveryDateText || !deliveryDateInfo) {
            console.log('updateDeliveryDate: элементы не найдены', {
                selectedDelivery: !!selectedDelivery,
                deliveryDateText: !!deliveryDateText,
                deliveryDateInfo: !!deliveryDateInfo
            });
            return;
        }
        
        // Курьером: +2 дня, самовывоз: +1 день
        const deliveryDays = (selectedDelivery.value === 'courier') ? 2 : 1;
        const deliveryDate = new Date();
        deliveryDate.setDate(deliveryDate.getDate() + deliveryDays);
        
        const day = String(deliveryDate.getDate()).padStart(2, '0');
        const month = String(deliveryDate.getMonth() + 1).padStart(2, '0');
        const year = deliveryDate.getFullYear();
        const formattedDate = `${day}.${month}.${year}`;
        
        deliveryDateText.textContent = formattedDate;
        console.log('Дата доставки обновлена:', formattedDate);
    }

    // Показ/скрытие полей адреса и магазина в зависимости от способа доставки
    function toggleAddressField() {
        const selectedDelivery = document.querySelector('input[name="delivery"]:checked');
        const addressField = document.getElementById('delivery-address');
        const pickupShop = document.getElementById('pickup-shop');
        const addressInput = document.getElementById('address');
        const addressSelect = document.getElementById('address-select');
        const shopSelect = document.getElementById('shop_id');
        
        if (!selectedDelivery) {
            console.log('toggleAddressField: радиокнопка не найдена');
            return;
        }
        
        console.log('toggleAddressField: выбран способ доставки', selectedDelivery.value);
        
        if (selectedDelivery.value === 'courier') {
            // Показываем поле адреса, скрываем выбор магазина
            if (addressField) addressField.style.display = 'block';
            if (pickupShop) pickupShop.style.display = 'none';
            if (addressInput) addressInput.required = true;
            if (shopSelect) {
                shopSelect.required = false;
                shopSelect.value = '';
            }
        } else if (selectedDelivery.value === 'pickup') {
            // Показываем выбор магазина, скрываем поле адреса
            if (addressField) addressField.style.display = 'none';
            if (pickupShop) pickupShop.style.display = 'block';
            if (addressInput) {
                addressInput.required = false;
                addressInput.value = '';
            }
            if (addressSelect) addressSelect.value = '';
            if (shopSelect) shopSelect.required = true;
        }
        
        // Обновляем дату доставки
        updateDeliveryDate();
    }
    
    // Обработка выбора сохраненного адреса
    const addressSelect = document.getElementById('address-select');
    const addressInput = document.getElementById('address');
    if (addressSelect && addressInput) {
        addressSelect.addEventListener('change', function() {
            if (this.value) {
                addressInput.value = this.value;
            }
        });
        
        // При вводе нового адреса сбрасываем выбор
        addressInput.addEventListener('input', function() {
            if (addressSelect.value) {
                addressSelect.value = '';
            }
        });
    }

    // Удаление товара из корзины
    function removeCartItem(productId, itemElement) {
        fetch(cosmeticFetchUrl('api/cart_api.php'), {
            method: 'POST',
            credentials: 'same-origin',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: 'action=remove&product_id=' + encodeURIComponent(productId)
        })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Анимация удаления
                    itemElement.style.opacity = '0';
                    itemElement.style.transform = 'translateX(-20px)';
                    
                    setTimeout(() => {
                        itemElement.remove();
                        
                        // Пересчитываем общую сумму
                        updateCartTotal();
                        
                        // Проверяем, есть ли еще товары в корзине
                        const cartItemsList = document.querySelector('.cart-items-list');
                        const remainingItems = cartItemsList ? cartItemsList.querySelectorAll('.cart-item-summary') : [];
                        
                        if (remainingItems.length === 0) {
                            if (cartItemsList) {
                                cartItemsList.innerHTML = '<p class="empty-cart-message">Корзина пуста</p>';
                            }
                            // Скрываем кнопку заказа и информацию о доставке
                            const orderBtn = document.getElementById('order-btn');
                            const deliveryDateInfo = document.getElementById('delivery-date-info');
                            if (orderBtn) orderBtn.style.display = 'none';
                            if (deliveryDateInfo) deliveryDateInfo.style.display = 'none';
                            
                            // Перенаправляем на главную страницу через 2 секунды
                            setTimeout(() => {
                                window.location.href = cosmeticFetchUrl('index.php');
                            }, 2000);
                        }
                        
                        // Показываем уведомление
                        showToast('Товар удален', 'Товар успешно удален из корзины', 'success');
                        
                        // Обновляем кнопки "В корзине" на других страницах (если открыты)
                        const cartButtons = document.querySelectorAll(`.product-cart-btn[data-product-id="${productId}"], .product-cart-btn-large[data-product-id="${productId}"]`);
                        cartButtons.forEach(btn => {
                            btn.classList.remove('in-cart');
                            btn.setAttribute('aria-label', 'Добавить в корзину');
                            const btnSpan = btn.querySelector('span');
                            if (btnSpan) {
                                btnSpan.textContent = 'Добавить в корзину';
                            }
                        });
                    }, 300);
                } else {
                    showToast('Ошибка', data.message || 'Не удалось удалить товар из корзины', 'error');
                }
            })
            .catch(error => {
                console.error('Ошибка при удалении товара из корзины:', error);
                showToast('Ошибка', 'Произошла ошибка. Попробуйте позже.', 'error');
            });
    }
    
    // Функция обновления количества товара в корзине
    function updateCartQuantity(productId, newQuantity, itemElement) {
        if (newQuantity <= 0) {
            // Если количество 0, удаляем товар
            if (confirm('Удалить товар из корзины?')) {
                removeCartItem(productId, itemElement);
            } else {
                // Если отменили удаление, возвращаем количество 1
                const quantityInput = itemElement.querySelector('.quantity-input');
                if (quantityInput) {
                    quantityInput.value = 1;
                    updateCartQuantity(productId, 1, itemElement);
                }
            }
            return;
        }
        
        fetch(cosmeticFetchUrl('api/cart_api.php'), {
            method: 'POST',
            credentials: 'same-origin',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: 'action=update&product_id=' + encodeURIComponent(productId) + '&quantity=' + encodeURIComponent(newQuantity)
        })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Обновляем данные элемента
                    itemElement.dataset.quantity = newQuantity;
                    
                    // Обновляем цену товара
                    const price = parseFloat(itemElement.dataset.price || 0);
                    const itemPriceElement = itemElement.querySelector('.item-price');
                    if (itemPriceElement) {
                        const totalPrice = price * newQuantity;
                        itemPriceElement.dataset.total = totalPrice;
                        itemPriceElement.textContent = Math.round(totalPrice).toLocaleString('ru-RU') + ' Р';
                    }
                    
                    // Пересчитываем общую сумму
                    updateCartTotal();
                } else {
                    showToast('Ошибка', data.message || 'Не удалось обновить количество', 'error');
                    // Возвращаем предыдущее значение
                    const quantityInput = itemElement.querySelector('.quantity-input');
                    if (quantityInput) {
                        quantityInput.value = itemElement.dataset.quantity;
                    }
                }
            })
            .catch(error => {
                console.error('Ошибка при обновлении количества:', error);
                showToast('Ошибка', 'Произошла ошибка. Попробуйте позже.', 'error');
                // Возвращаем предыдущее значение
                const quantityInput = itemElement.querySelector('.quantity-input');
                if (quantityInput) {
                    quantityInput.value = itemElement.dataset.quantity;
                }
            });
    }
    
    // Обработчики кнопок изменения количества
    document.addEventListener('click', function(e) {
        // Кнопка уменьшения количества
        if (e.target.classList.contains('btn-quantity-minus') || e.target.closest('.btn-quantity-minus')) {
            e.preventDefault();
            e.stopPropagation();
            
            const button = e.target.classList.contains('btn-quantity-minus') ? e.target : e.target.closest('.btn-quantity-minus');
            const productId = button.dataset.productId;
            const itemElement = button.closest('.cart-item-summary');
            const quantityInput = itemElement ? itemElement.querySelector('.quantity-input') : null;
            
            if (!productId || !itemElement || !quantityInput) return;
            
            let currentQuantity = parseInt(quantityInput.value) || 1;
            const newQuantity = Math.max(1, currentQuantity - 1);
            quantityInput.value = newQuantity;
            updateCartQuantity(productId, newQuantity, itemElement);
        }
        
        // Кнопка увеличения количества
        if (e.target.classList.contains('btn-quantity-plus') || e.target.closest('.btn-quantity-plus')) {
            e.preventDefault();
            e.stopPropagation();
            
            const button = e.target.classList.contains('btn-quantity-plus') ? e.target : e.target.closest('.btn-quantity-plus');
            const productId = button.dataset.productId;
            const itemElement = button.closest('.cart-item-summary');
            const quantityInput = itemElement ? itemElement.querySelector('.quantity-input') : null;
            
            if (!productId || !itemElement || !quantityInput) return;
            
            let currentQuantity = parseInt(quantityInput.value) || 1;
            const newQuantity = Math.min(99, currentQuantity + 1);
            quantityInput.value = newQuantity;
            updateCartQuantity(productId, newQuantity, itemElement);
        }
        
        // Кнопка удаления товара
        if (e.target.closest('.btn-remove-item')) {
            e.preventDefault();
            e.stopPropagation();
            
            const button = e.target.closest('.btn-remove-item');
            const productId = button.dataset.productId;
            const itemElement = button.closest('.cart-item-summary');
            
            if (!productId || !itemElement) return;
            
            // Подтверждение удаления
            if (confirm('Удалить товар из корзины?')) {
                removeCartItem(productId, itemElement);
            }
        }
    });
    
    // Обработка изменения количества через input
    document.addEventListener('input', function(e) {
        if (e.target.classList.contains('quantity-input')) {
            const quantityInput = e.target;
            const productId = quantityInput.dataset.productId;
            const itemElement = quantityInput.closest('.cart-item-summary');
            
            if (!productId || !itemElement) return;
            
            let newQuantity = parseInt(quantityInput.value) || 1;
            
            // Ограничиваем минимальное и максимальное значение
            if (newQuantity < 1) {
                newQuantity = 1;
                quantityInput.value = 1;
            } else if (newQuantity > 99) {
                newQuantity = 99;
                quantityInput.value = 99;
            }
        }
    });
    
    // Обработка изменения количества при потере фокуса input
    document.addEventListener('blur', function(e) {
        if (e.target.classList.contains('quantity-input')) {
            const quantityInput = e.target;
            const productId = quantityInput.dataset.productId;
            const itemElement = quantityInput.closest('.cart-item-summary');
            
            if (!productId || !itemElement) return;
            
            let newQuantity = parseInt(quantityInput.value) || 1;
            
            // Ограничиваем минимальное и максимальное значение
            if (newQuantity < 1) {
                newQuantity = 1;
                quantityInput.value = 1;
            } else if (newQuantity > 99) {
                newQuantity = 99;
                quantityInput.value = 99;
            }
            
            // Обновляем количество только если оно изменилось
            if (newQuantity !== parseInt(itemElement.dataset.quantity || 0)) {
                updateCartQuantity(productId, newQuantity, itemElement);
            }
        }
    }, true);

    // Инициализация обработчиков доставки
    if (deliveryRadios.length > 0) {
        console.log('Найдено радиокнопок доставки:', deliveryRadios.length);
        
        // Обработка изменения способа доставки
        deliveryRadios.forEach(radio => {
            radio.addEventListener('change', function() {
                console.log('Изменен способ доставки:', this.value);
                toggleAddressField();
            });
        });
        
        // Инициализация сразу
        toggleAddressField();
        
        // И повторная инициализация после небольшой задержки
        setTimeout(function() {
            toggleAddressField();
            updateDeliveryDate();
        }, 200);
    } else {
        console.log('Радиокнопки доставки не найдены');
    }

    // Валидация телефона - только цифры
    if (phoneInput) {
        console.log('Поле телефона найдено');
        
        phoneInput.addEventListener('input', function(e) {
            // Оставляем только цифры
            let digits = this.value.replace(/[^0-9]/g, '');
            
            // Ограничиваем длину до 11 цифр
            if (digits.length > 11) {
                digits = digits.substring(0, 11);
            }
            
            // Сохраняем только цифры (без форматирования во время ввода)
            this.value = digits;
            
            // Очищаем ошибку
            const errorEl = document.getElementById('phone-error');
            if (errorEl) {
                errorEl.textContent = '';
            }
        });

        // Форматирование телефона при потере фокуса
        phoneInput.addEventListener('blur', function() {
            let digits = this.value.replace(/[^0-9]/g, '');
            if (digits.length > 0) {
                // Если начинается с 8, заменяем на 7
                if (digits.startsWith('8')) {
                    digits = '7' + digits.substring(1);
                }
                // Если не начинается с 7 и есть 10 цифр, добавляем 7
                if (!digits.startsWith('7') && digits.length === 10) {
                    digits = '7' + digits;
                }
                // Если меньше 11 цифр и не начинается с 7, добавляем 7
                if (digits.length < 11 && !digits.startsWith('7') && digits.length > 0) {
                    digits = '7' + digits;
                }
                // Ограничиваем до 11 цифр
                if (digits.length > 11) {
                    digits = digits.substring(0, 11);
                }
                
                // Форматируем: +7 (999) 123-45-67 если 11 цифр
                if (digits.length === 11 && digits.startsWith('7')) {
                    const formatted = `+7 (${digits.substring(1, 4)}) ${digits.substring(4, 7)}-${digits.substring(7, 9)}-${digits.substring(9)}`;
                    this.value = formatted;
                } else {
                    // Просто оставляем цифры
                    this.value = digits;
                }
            }
        });
    } else {
        console.log('Поле телефона не найдено');
    }

    // Обработка кнопки "Заказать"
    if (orderBtn) {
        console.log('Кнопка заказа найдена');
        
        orderBtn.addEventListener('click', function(e) {
            e.preventDefault();
            
            // Очищаем предыдущие сообщения
            if (orderMessage) {
                orderMessage.textContent = '';
                orderMessage.className = 'order-message';
            }
            
            // Очищаем ошибки
            document.querySelectorAll('.field-error').forEach(el => {
                el.textContent = '';
            });
            
            // Собираем данные формы
            const recipientNameEl = document.getElementById('recipient-name');
            const recipientPhoneEl = document.getElementById('recipient-phone');
            const recipientName = recipientNameEl ? recipientNameEl.value.trim() : '';
            const recipientPhone = recipientPhoneEl ? recipientPhoneEl.value.trim() : '';
            const deliveryMethod = document.querySelector('input[name="delivery"]:checked')?.value;
            const addressSelect = document.getElementById('address-select');
            const addressInput = document.getElementById('address');
            const address = (addressSelect && addressSelect.value) ? addressSelect.value.trim() : (addressInput ? addressInput.value.trim() : '');
            const shopId = document.getElementById('shop_id')?.value || '';
            
            // Валидация
            let hasErrors = false;
            
            if (!recipientName) {
                const nameError = document.getElementById('name-error');
                if (nameError) nameError.textContent = 'Укажите имя';
                hasErrors = true;
            }
            
            if (!recipientPhone) {
                const phoneError = document.getElementById('phone-error');
                if (phoneError) phoneError.textContent = 'Укажите номер телефона';
                hasErrors = true;
            } else {
                // Проверяем формат телефона (только цифры, российский формат)
                let phoneDigits = recipientPhone.replace(/[^0-9]/g, '');
                // Если начинается с 8, заменяем на 7
                if (phoneDigits.length > 0 && phoneDigits[0] === '8') {
                    phoneDigits = '7' + phoneDigits.substring(1);
                }
                // Если не начинается с 7, добавляем 7
                if (phoneDigits.length > 0 && phoneDigits[0] !== '7') {
                    phoneDigits = '7' + phoneDigits;
                }
                // Проверяем формат (11 цифр, начинается с 7)
                if (phoneDigits.length !== 11 || phoneDigits[0] !== '7') {
                    const phoneError = document.getElementById('phone-error');
                    if (phoneError) phoneError.textContent = 'Некорректный номер телефона. Введите российский номер (10 цифр)';
                    hasErrors = true;
                }
            }
            
            if (!deliveryMethod) {
                hasErrors = true;
            }
            
            if (deliveryMethod === 'courier' && !address) {
                const addressError = document.getElementById('address-error');
                if (addressError) addressError.textContent = 'Укажите адрес доставки';
                hasErrors = true;
            }
            
            if (deliveryMethod === 'pickup' && !shopId) {
                const shopError = document.getElementById('shop-error');
                if (shopError) shopError.textContent = 'Выберите магазин для самовывоза';
                hasErrors = true;
            }
            
            if (hasErrors) {
                if (orderMessage) {
                    orderMessage.textContent = 'Заполните все обязательные поля';
                    orderMessage.className = 'order-message error';
                }
                return;
            }
            
            // Отправляем заказ
            orderBtn.disabled = true;
            orderBtn.textContent = 'Оформление...';
            
            const formData = new URLSearchParams();
            formData.append('recipient_name', recipientName);
            formData.append('recipient_phone', recipientPhone);
            formData.append('delivery_method', deliveryMethod);
            if (deliveryMethod === 'courier') {
                formData.append('address', address);
            } else if (deliveryMethod === 'pickup') {
                formData.append('shop_id', shopId);
            }
            
            // Добавляем промокод, если применен
            if (appliedPromoCode && appliedPromoCode.id) {
                if (appliedPromoCode.source === 'wheel') {
                    formData.append('wheel_reward_id', appliedPromoCode.id);
                } else {
                    formData.append('promo_code_id', appliedPromoCode.id);
                }
            }
            
            fetch(cosmeticFetchUrl('api/create_order.php'), {
                method: 'POST',
                credentials: 'same-origin',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: formData.toString()
            })
                .then(async (response) => {
                    const text = await response.text();
                    let data = null;
                    try {
                        data = text ? JSON.parse(text) : null;
                    } catch (parseErr) {
                        const snippet = text.replace(/\s+/g, ' ').trim().slice(0, 280);
                        throw new Error(
                            snippet
                                ? 'Ответ сервера не JSON (HTTP ' + response.status + '): ' + snippet
                                : 'Пустой ответ сервера (HTTP ' + response.status + ')'
                        );
                    }
                    if (!response.ok && data && !data.message && !data.errors) {
                        data.message = 'HTTP ' + response.status;
                    }
                    return data;
                })
                .then(data => {
                    if (!data || typeof data !== 'object') {
                        if (orderMessage) {
                            orderMessage.textContent = 'Некорректный ответ сервера';
                            orderMessage.className = 'order-message error';
                        }
                        orderBtn.disabled = false;
                        orderBtn.textContent = 'заказать';
                        return;
                    }
                    if (data.success) {
                        if (orderMessage) {
                            orderMessage.textContent = 'Заказ успешно оформлен!';
                            orderMessage.className = 'order-message success';
                        }
                        orderBtn.style.display = 'none';
                        
                        // Перенаправляем через 2 секунды
                        setTimeout(() => {
                            window.location.href = cosmeticFetchUrl('profile.php');
                        }, 2000);
                    } else {
                        if (orderMessage) {
                            const msg =
                                data.message ||
                                (data.errors && typeof data.errors === 'object'
                                    ? Object.values(data.errors).join(' ')
                                    : '') ||
                                'Ошибка при оформлении заказа';
                            orderMessage.textContent = msg;
                            orderMessage.className = 'order-message error';
                        }
                        
                        // Показываем ошибки полей
                        if (data.errors) {
                            Object.keys(data.errors).forEach(field => {
                                const errorEl = document.getElementById(field + '-error');
                                if (errorEl) {
                                    errorEl.textContent = data.errors[field];
                                }
                            });
                        }
                        
                        orderBtn.disabled = false;
                        orderBtn.textContent = 'заказать';
                    }
                })
                .catch(error => {
                    console.error('Ошибка при оформлении заказа:', error);
                    if (orderMessage) {
                        const msg =
                            error && error.message
                                ? error.message
                                : 'Произошла ошибка. Попробуйте позже.';
                        orderMessage.textContent = msg.length > 400 ? msg.slice(0, 400) + '…' : msg;
                        orderMessage.className = 'order-message error';
                    }
                    orderBtn.disabled = false;
                    orderBtn.textContent = 'заказать';
                });
        });
    } else {
        console.log('Кнопка заказа не найдена');
    }
});
