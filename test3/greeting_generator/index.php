<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Генератор поздравлений</title>
    <style>
    /* Встроенные стили для лучшей совместимости в Битрикс */
    /* Основные стили для приложения генератора поздравлений */
    :root {
        --primary-color: #4e6aad;
        --secondary-color: #ff8484;
        --text-color: #333333;
        --background-color: #f7f9fc;
        --border-color: #e1e4e8;
        --success-color: #28a745;
        --error-color: #dc3545;
        --disabled-color: #6c757d;
    }

    * {
        margin: 0;
        padding: 0;
        box-sizing: border-box;
    }

    body {
        font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        line-height: 1.6;
        color: var(--text-color);
        background-color: var(--background-color);
        padding: 20px;
    }

    .container {
        max-width: 800px;
        margin: 0 auto;
        background: white;
        border-radius: 10px;
        box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        padding: 30px;
    }

    h1 {
        color: var(--primary-color);
        margin-bottom: 20px;
        font-size: 24px;
        text-align: center;
    }

    /* Языковые вкладки */
    .tabs {
        display: flex;
        border-bottom: 1px solid var(--border-color);
        margin-bottom: 20px;
    }

    .tab {
        padding: 10px 15px;
        cursor: pointer;
        transition: all 0.3s ease;
        border-bottom: 2px solid transparent;
    }

    .tab:hover {
        background-color: rgba(78, 106, 173, 0.1);
    }

    .tab.active {
        border-bottom: 2px solid var(--primary-color);
        font-weight: bold;
    }

    /* Форма и поля ввода */
    .form-group {
        margin-bottom: 20px;
    }

    label {
        display: block;
        margin-bottom: 8px;
        font-weight: 500;
    }

    textarea, input[type="text"] {
        width: 100%;
        padding: 12px;
        border: 1px solid var(--border-color);
        border-radius: 4px;
        font-size: 16px;
        transition: border 0.3s ease;
    }

    textarea:focus, input[type="text"]:focus {
        border-color: var(--primary-color);
        outline: none;
    }

    .char-counter {
        display: flex;
        justify-content: flex-end;
        font-size: 12px;
        color: var(--disabled-color);
        margin-top: 5px;
    }

    .char-counter.over-limit {
        color: var(--error-color);
    }

    /* Кнопки */
    .button-group {
        display: flex;
        justify-content: space-between;
        margin-top: 20px;
    }

    button {
        padding: 10px 20px;
        border: none;
        border-radius: 4px;
        font-size: 16px;
        cursor: pointer;
        transition: all 0.3s ease;
    }

    button:disabled {
        background-color: var(--disabled-color);
        cursor: not-allowed;
        opacity: 0.7;
    }

    .btn-primary {
        background-color: var(--primary-color);
        color: white;
    }

    .btn-secondary {
        background-color: var(--secondary-color);
        color: white;
    }

    .btn-primary:hover:not(:disabled),
    .btn-secondary:hover:not(:disabled) {
        opacity: 0.9;
    }

    /* Индикатор загрузки */
    .loading-indicator {
        display: none;
        text-align: center;
        margin: 20px 0;
    }

    .spinner {
        border: 4px solid rgba(0, 0, 0, 0.1);
        border-left-color: var(--primary-color);
        border-radius: 50%;
        width: 30px;
        height: 30px;
        animation: spin 1s linear infinite;
        margin: 0 auto;
    }

    @keyframes spin {
        0% { transform: rotate(0deg); }
        100% { transform: rotate(360deg); }
    }

    /* Сообщение об ошибке */
    .error-message {
        display: none;
        background-color: rgba(220, 53, 69, 0.1);
        border-left: 4px solid var(--error-color);
        padding: 10px 15px;
        margin: 20px 0;
        color: var(--error-color);
    }

    /* Варианты поздравлений */
    .variants-container {
        margin-top: 30px;
        display: none;
    }

    .variants-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 15px;
    }

    .variants-title {
        font-weight: bold;
        color: var(--primary-color);
    }

    .variant-navigation {
        display: flex;
        align-items: center;
    }

    .variant-counter {
        margin: 0 10px;
        font-size: 14px;
    }

    .nav-btn {
        background: none;
        border: 1px solid var(--border-color);
        width: 30px;
        height: 30px;
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 0;
    }

    .nav-btn:disabled {
        opacity: 0.5;
    }

    .variants-list {
        background-color: rgba(78, 106, 173, 0.05);
        border-radius: 4px;
        padding: 15px;
        margin-bottom: 15px;
    }

    .variant-item {
        margin-bottom: 5px;
        line-height: 1.5;
    }

    /* Поле для редактирования */
    .editor-section {
        margin-top: 20px;
    }

    .editor-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 10px;
    }

    .copy-btn {
        background: none;
        border: none;
        cursor: pointer;
        opacity: 0.7;
        transition: opacity 0.3s ease;
        padding: 5px;
    }

    .copy-btn:hover {
        opacity: 1;
    }

    .copy-btn.copied {
        color: var(--success-color);
    }

    .copy-icon {
        width: 20px;
        height: 20px;
    }

    /* Адаптивный дизайн */
    @media (max-width: 768px) {
        .container {
            padding: 20px;
        }
        
        h1 {
            font-size: 20px;
        }
        
        .button-group {
            flex-direction: column;
            gap: 10px;
        }
        
        button {
            width: 100%;
        }
    }
    </style>
</head>
<body>
    <?php require_once __DIR__ . '/config.php'; ?>
    
    <div class="container">
        <h1>Генератор поздравительных открыток</h1>
        
        <!-- Вкладки для выбора языка -->
        <div class="tabs">
            <div class="tab active" data-lang="ru">Русский</div>
            <div class="tab" data-lang="en">English</div>
            <div class="tab" data-lang="ar">العربية</div>
        </div>
        
        <!-- Форма для ввода описания -->
        <div class="form-group">
            <label for="description">Опишите повод для поздравления:</label>
            <textarea id="description" rows="4" placeholder="Например: день рождения коллеги, 35 лет, любит путешествия и фотографию"></textarea>
        </div>
        
        <!-- Кнопки управления -->
        <div class="button-group">
            <button id="generate-btn" class="btn-primary">Сгенерировать</button>
            <button id="regenerate-btn" class="btn-secondary" disabled>Другие варианты</button>
        </div>
        
        <!-- Индикатор загрузки -->
        <div id="loading-indicator" class="loading-indicator">
            <div class="spinner"></div>
            <p>Генерируем варианты поздравлений...</p>
        </div>
        
        <!-- Сообщение об ошибке -->
        <div id="error-message" class="error-message"></div>
        
        <!-- Варианты поздравлений -->
        <div id="variants-container" class="variants-container">
            <div class="variants-header">
                <span class="variants-title">Варианты поздравлений:</span>
                <div class="variant-navigation">
                    <button id="prev-variant" class="nav-btn" disabled>&lt;</button>
                    <span id="variant-counter" class="variant-counter">1 / 1</span>
                    <button id="next-variant" class="nav-btn" disabled>&gt;</button>
                </div>
            </div>
            
            <div id="variants-list" class="variants-list"></div>
            
            <!-- Секция редактирования -->
            <div class="editor-section">
                <div class="editor-header">
                    <label for="greeting-text">Редактируйте текст поздравления:</label>
                    <button id="copy-btn" class="copy-btn" title="Копировать в буфер обмена">
                        <img class="copy-icon" src="static/images/copy.svg" alt="Copy">
                    </button>
                </div>
                <textarea id="greeting-text" rows="6"></textarea>
                <div id="char-counter" class="char-counter">0 / 500 символов</div>
            </div>
        </div>
    </div>
    
    <script>
        // Конфигурация приложения (передаём серверные настройки)
        const serverConfig = {
            maxChars: <?php echo $MAX_CHARS ?? 500; ?>,
            maxAttempts: <?php echo $MAX_REGENERATE_ATTEMPTS ?? 0; ?>
        };

        // Встроенный JavaScript-код для лучшей совместимости с Битрикс
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

                // Выводим отладочную информацию
                console.log("Отправляем запрос:", payload);
                
                // Используем абсолютный путь к generate.php, чтобы избежать проблем с маршрутизацией
                const apiUrl = window.location.protocol + '//' + window.location.hostname + ':8080/generate.php';
                console.log("Отправляем запрос на URL:", apiUrl);
                
                fetch(apiUrl, {
                    method: 'POST',
                    headers: {'Content-Type': 'application/json'},
                    body: JSON.stringify(payload)
                })
                .then(response => {
                    console.log("Получен ответ от сервера:", response.status);
                    // Для отладки - выведем текст ответа
                    return response.text().then(text => {
                        console.log("Текст ответа:", text.substring(0, 200) + "...");
                        try {
                            // Пытаемся распарсить ответ как JSON
                            return JSON.parse(text);
                        } catch (e) {
                            console.error("Ошибка парсинга JSON:", e);
                            throw new Error(`Ошибка парсинга ответа: ${text.substring(0, 100)}...`);
                        }
                    });
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
    </script>
</body>
</html>