// Функционал для страницы товара
document.addEventListener('DOMContentLoaded', function() {
    // Разворачивание/сворачивание описания
    const descriptionToggle = document.querySelector('.product-description-toggle');
    const descriptionContent = document.querySelector('.product-description-content');
    const descriptionText = document.querySelector('.product-description-text');
    
    if (descriptionToggle && descriptionContent && descriptionText) {
        // Проверяем, нужно ли сворачивать описание
        // Сначала убираем все классы для правильного измерения
        descriptionContent.classList.remove('collapsed', 'expanded');
        const textHeight = descriptionText.scrollHeight;
        const maxHeight = 120; // Максимальная высота в свернутом виде
        
        if (textHeight > maxHeight) {
            // Добавляем класс для сворачивания
            descriptionContent.classList.add('collapsed');
            
            // Обновляем текст кнопки
            const toggleText = descriptionToggle.querySelector('.toggle-text');
            if (toggleText) {
                toggleText.textContent = 'Развернуть';
            }
            
            // Обработчик клика на кнопку
            descriptionToggle.addEventListener('click', function() {
                if (descriptionContent.classList.contains('collapsed')) {
                    descriptionContent.classList.remove('collapsed');
                    descriptionContent.classList.add('expanded');
                    if (toggleText) {
                        toggleText.textContent = 'Свернуть';
                    }
                } else {
                    descriptionContent.classList.remove('expanded');
                    descriptionContent.classList.add('collapsed');
                    if (toggleText) {
                        toggleText.textContent = 'Развернуть';
                    }
                }
            });
        } else {
            // Если описание короткое, скрываем кнопку
            descriptionToggle.style.display = 'none';
        }
    }
});

