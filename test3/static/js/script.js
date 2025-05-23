document.addEventListener('DOMContentLoaded', () => {
    // DOM-элементы
    const descriptionEl = document.getElementById('description');
    const greetingTextEl = document.getElementById('greeting-text');
    const variantsContainer = document.getElementById('variants-container');
    const variantsList = document.getElementById('variants-list');
    const generateBtn = document.getElementById('generate-btn');
    const regenerateBtn = document.getElementById('regenerate-btn');
    const loadingIndicator = document.getElementById('loading-indicator');
    const errorMessage = document.getElementById('error-message');
    const charCounter = document.getElementById('char-counter');
    const copyBtn = document.getElementById('copy-btn');
    const prevVariantBtn = document.getElementById('prev-variant');
    const nextVariantBtn = document.getElementById('next-variant');
    const variantCounter = document.getElementById('variant-counter');
    const tabs = document.querySelectorAll('.tab');

    // Состояние приложения
    let selectedLang = 'ru';
    let variants = [];
    let currentVariantIndex = 0;
    let attempt = 1;
    let maxAttempts = serverConfig.maxAttempts || 0;
    let maxChars = serverConfig.maxChars || 500;
    let isGenerating = false;

    // Инициализация счетчика символов
    updateCharCounter();

    // Обработка переключения языков
    tabs.forEach(tab => {
        tab.addEventListener('click', () => {
            tabs.forEach(t => t.classList.remove('active'));
            tab.classList.add('active');
            selectedLang = tab.getAttribute('data-lang');
            
            // Для арабского языка устанавливаем RTL направление текста
            if (selectedLang === 'ar') {
                document.body.setAttribute('dir', 'rtl');
            } else {
                document.body.setAttribute('dir', 'ltr');
            }
        });
    });

    // Обновление счетчика символов
    function updateCharCounter() {
        const length = greetingTextEl.value.length;
        charCounter.textContent = `${length} / ${maxChars} символов`;
        
        // Визуальное предупреждение, если превышен лимит символов
        if (length > maxChars) {
            charCounter.classList.add('over-limit');
        } else {
            charCounter.classList.remove('over-limit');
        }
    }

    // Обработчик ввода текста для счетчика символов
    greetingTextEl.addEventListener('input', updateCharCounter);

    // Функция отображения вариантов поздравлений
    function renderVariants() {
        variantsList.innerHTML = '';
        
        if (variants.length === 0) return;
        
        // Отображаем только текущий вариант
        const variantDiv = document.createElement('div');
        variantDiv.className = 'variant-item';
        variantDiv.textContent = variants[currentVariantIndex];
        variantsList.appendChild(variantDiv);
        
        // Обновляем счетчик вариантов
        variantCounter.textContent = `${currentVariantIndex + 1} / ${variants.length}`;
        
        // Управление кнопками навигации
        prevVariantBtn.disabled = currentVariantIndex === 0;
        nextVariantBtn.disabled = currentVariantIndex === variants.length - 1;
    }

    // Навигация по вариантам
    prevVariantBtn.addEventListener('click', () => {
        if (currentVariantIndex > 0) {
            currentVariantIndex--;
            renderVariants();
            updateSelectedVariant();
        }
    });

    nextVariantBtn.addEventListener('click', () => {
        if (currentVariantIndex < variants.length - 1) {
            currentVariantIndex++;
            renderVariants();
            updateSelectedVariant();
        }
    });

    // Обновление выбранного варианта в поле редактирования
    function updateSelectedVariant() {
        greetingTextEl.value = variants[currentVariantIndex] || '';
        updateCharCounter();
    }

    // Копирование текста поздравления
    copyBtn.addEventListener('click', () => {
        if (!greetingTextEl.value) return;
        
        // Копирование в буфер обмена
        greetingTextEl.select();
        document.execCommand('copy');
        
        // Визуальное подтверждение
        const originalTitle = copyBtn.getAttribute('title');
        copyBtn.setAttribute('title', 'Скопировано!');
        copyBtn.classList.add('copied');
        
        setTimeout(() => {
            copyBtn.setAttribute('title', originalTitle);
            copyBtn.classList.remove('copied');
        }, 2000);
    });

    // Показ/скрытие индикатора загрузки
    function toggleLoading(show) {
        isGenerating = show;
        loadingIndicator.style.display = show ? 'block' : 'none';
        generateBtn.disabled = show;
        regenerateBtn.disabled = show || variants.length === 0;
    }

    // Показ сообщения об ошибке
    function showError(message) {
        errorMessage.textContent = message;
        errorMessage.style.display = 'block';
        
        // Автоскрытие ошибки через 5 секунд
        setTimeout(() => {
            errorMessage.style.display = 'none';
        }, 5000);
    }

    // Отправка запроса на сервер для генерации поздравлений
    function generateGreetings(isRegenerate = false) {
        // Предотвращаем множественные запросы
        if (isGenerating) return;
        
        // Обновляем счетчик попыток
        if (isRegenerate) {
            attempt++;
            if (maxAttempts > 0 && attempt > maxAttempts) {
                showError(`Превышено максимальное количество попыток перегенерации (${maxAttempts})`);
                return;
            }
        } else {
            attempt = 1;
        }

        // Скрываем предыдущую ошибку
        errorMessage.style.display = 'none';
        
        // Показываем индикатор загрузки
        toggleLoading(true);
        
        // Скрываем контейнер с вариантами во время загрузки
        variantsContainer.style.display = 'none';

        const payload = {
            lang: selectedLang,
            description: descriptionEl.value.trim(),
            attempt: attempt
        };

        fetch('generate.php', {
            method: 'POST',
            headers: {'Content-Type': 'application/json'},
            body: JSON.stringify(payload)
        })
        .then(response => {
            if (!response.ok) {
                if (response.status === 429) {
                    throw new Error('Превышен лимит запросов. Пожалуйста, попробуйте позже.');
                }
                return response.json().then(data => {
                    throw new Error(data.error || `Ошибка сервера: ${response.status}`);
                });
            }
            return response.json();
        })
        .then(data => {
            toggleLoading(false);
            
            if (data.error) {
                showError(data.error);
                return;
            }
            
            variants = data.variants || [];
            maxAttempts = data.max_attempts || 0;
            
            if (variants.length === 0) {
                showError('Не удалось получить варианты поздравлений.');
                return;
            }
            
            // Обновляем максимальное количество символов, если получено с сервера
            if (data.max_chars) {
                maxChars = data.max_chars;
            }
            
            // Сбрасываем индекс и обновляем UI
            currentVariantIndex = 0;
            renderVariants();
            updateSelectedVariant();
            
            // Показываем контейнер с вариантами
            variantsContainer.style.display = 'block';
            
            // Разблокируем кнопку перегенерации
            regenerateBtn.disabled = false;
        })
        .catch(err => {
            toggleLoading(false);
            showError(err.message || 'Ошибка при запросе к серверу');
            console.error('Ошибка запроса:', err);
        });
    }

    // Обработчики кнопок
    generateBtn.addEventListener('click', () => generateGreetings(false));
    regenerateBtn.addEventListener('click', () => generateGreetings(true));

    // Инициализация приложения
    function init() {
        // Проверка поддержки необходимых API
        if (!window.fetch) {
            showError('Ваш браузер не поддерживает необходимые функции. Пожалуйста, обновите браузер.');
            generateBtn.disabled = true;
            return;
        }
    }

    // Запуск инициализации
    init();
});