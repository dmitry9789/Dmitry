<?php
// Конфигурация для интеграции с API Перплексити и настройки генерации поздравлений

// API ключ Перплексити (получаем из переменной окружения или используем предоставленный)
$API_KEY = $_ENV['PERPLEXITY_API_KEY'] ?? getenv('PERPLEXITY_API_KEY') ?? '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';

// Шаблон поздравления с переменной {description} для пользовательского описания
// и {lang} для выбранного языка
$GREETING_TEMPLATE = <<<EOT
Пожалуйста, сгенерируй красивое, душевное поздравление-открытку для интернет-магазина цветов. 
Описание: {description}
Язык: {lang}
Поздравление должно быть уникальным, тёплым и искренним, без штампов.
Оно должно подходить для отправки вместе с букетом цветов.
EOT;

// Соответствие кодов языков и их названий
$LANGUAGES = [
    'ru' => 'Русский',
    'en' => 'English',
    'ar' => 'العربية'
];

// URL API Perplexity
$API_URL = 'https://api.perplexity.ai/chat/completions';

// Модель API Perplexity
// Используем актуальную модель согласно документации и тестовому примеру
$API_MODEL = 'sonar-pro';  // модель из рабочего примера
// Другие возможные модели: sonar-small-online, sonar-medium-online, mixtral-8x7b-instruct

// Максимальное количество символов в поздравлении
$MAX_CHARS = 500;

// Максимальное количество попыток перегенерации (0 - без ограничений)
$MAX_REGENERATE_ATTEMPTS = 0;

// Информация о приложении
$APP_VERSION = '1.0.0';

// Логирование ошибок
$DEBUG_MODE = true; // установите true для отладки
if ($DEBUG_MODE) {
    ini_set('display_errors', 1);
    ini_set('display_startup_errors', 1);
    error_reporting(E_ALL);
}

// Функция для безопасного логирования ошибок
function logError($message, $context = []) {
    global $DEBUG_MODE;
    if ($DEBUG_MODE) {
        error_log('[Greeting Generator] ' . $message . ': ' . json_encode($context));
    }
}