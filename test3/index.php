<?php
// Подключаем конфигурацию (API-ключ, настройки)
require_once __DIR__ . '/config.php';
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8" />
    <title>Генерация поздравления-открытки</title>
    <link rel="stylesheet" href="static/css/style.css" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
</head>
<body>
<div class="container">
    <h1>Генерация поздравления-открытки</h1>

    <form id="greeting-form" onsubmit="return false;">
        <label for="description">Описание поздравления (опционально):</label><br/>
        <textarea id="description" name="description" rows="3" placeholder="Имя получателя, повод, пожелания..."></textarea>

        <div class="language-switcher">
            <label>Выберите язык:</label>
            <div class="tabs">
                <button type="button" class="tab active" data-lang="ru">Русский</button>
                <button type="button" class="tab" data-lang="en">English</button>
                <button type="button" class="tab" data-lang="ar">العربية</button>
            </div>
        </div>

        <div class="buttons">
            <button id="generate-btn" type="button">Сгенерировать</button>
            <button id="regenerate-btn" type="button" disabled>Перегенерировать</button>
        </div>

        <div id="loading-indicator" style="display:none;">
            <div class="loader"></div>
            <p>Генерируем поздравление, пожалуйста подождите...</p>
        </div>

        <div id="variants-container" style="display:none;">
            <div class="variants-header">
                <label>Варианты поздравлений (выберите один):</label>
                <div class="variants-controls">
                    <button id="prev-variant" type="button" class="control-btn" title="Предыдущий вариант">&#8592;</button>
                    <span id="variant-counter">1 / 5</span>
                    <button id="next-variant" type="button" class="control-btn" title="Следующий вариант">&#8594;</button>
                </div>
            </div>
            <div id="variants-list"></div>
        </div>

        <div id="greeting-editor">
            <div class="editor-header">
                <label for="greeting-text">Текст поздравления:</label>
                <button id="copy-btn" type="button" class="icon-btn" title="Копировать текст">
                    <img src="static/images/copy.svg" alt="Копировать" width="18" height="18">
                </button>
            </div>
            <textarea id="greeting-text" name="greeting-text" rows="6" placeholder="Здесь появится сгенерированный текст..."></textarea>
            <div id="char-counter" class="char-counter">0 / <?php echo $MAX_CHARS; ?> символов</div>
        </div>

        <div id="error-message" class="error-message" style="display:none;"></div>
    </form>
</div>

<script>
    // Передаем серверные настройки в JavaScript
    const serverConfig = {
        maxChars: <?php echo $MAX_CHARS; ?>,
        maxAttempts: <?php echo $MAX_REGENERATE_ATTEMPTS; ?>
    };
</script>
<script src="static/js/script.js"></script>
</body>
</html>