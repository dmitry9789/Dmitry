<?php
header('Content-Type: application/json; charset=utf-8');

// Разрешаем только POST-запросы
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Метод не разрешён']);
    exit;
}

// Подключаем конфиг с настройками и API-ключом
require_once __DIR__ . '/config.php';

// Получаем данные из POST (ожидаем JSON)
$input = json_decode(file_get_contents('php://input'), true);

if (!$input) {
    http_response_code(400);
    echo json_encode(['error' => 'Некорректные данные']);
    exit;
}

// Извлекаем и валидируем параметры
$lang = isset($input['lang']) ? trim($input['lang']) : 'ru';
$description = isset($input['description']) ? trim($input['description']) : '';
$attempt = isset($input['attempt']) ? intval($input['attempt']) : 1;

// Валидация языка
$allowedLangs = ['ru', 'en', 'ar'];
if (!in_array($lang, $allowedLangs)) {
    http_response_code(400);
    echo json_encode(['error' => 'Неподдерживаемый язык']);
    exit;
}

// Проверка лимита попыток (если задан)
if ($MAX_REGENERATE_ATTEMPTS > 0 && $attempt > $MAX_REGENERATE_ATTEMPTS) {
    http_response_code(429);
    echo json_encode(['error' => 'Превышено максимальное количество попыток перегенерации']);
    exit;
}

// Подготовка дополнительных инструкций в зависимости от языка
$langInstructions = [
    'ru' => "Поздравления должны быть на русском языке. Максимальная длина каждого: {$MAX_CHARS_PER_GREETING} символов.",
    'en' => "The greetings should be in English. Maximum length for each: {$MAX_CHARS_PER_GREETING} characters.",
    'ar' => "يجب أن تكون التهاني باللغة العربية. الحد الأقصى لطول كل تهنئة: {$MAX_CHARS_PER_GREETING} حرف."
];

// Разделитель вариантов в ответе
$delimiter = '---';

// Формируем системное сообщение с ограничением длины и языком
$systemMessage = "Пожалуйста, создай 5 разных коротких, душевных поздравлений для открытки (каждое не более {$MAX_CHARS_PER_GREETING} символов), " .
                 "раздели их между собой строкой '{$delimiter}'. " .
                 "Ответь только текстами поздравлений, без вступлений и пояснений. " .
                 $langInstructions[$lang];

// Формируем шаблон с подстановкой переменных
$template = $GREETING_TEMPLATE;
$template = str_replace('{description}', $description ?: 'без дополнительного описания', $template);
$template = str_replace('{lang}', $lang, $template);

// Добавляем номер попытки для разнообразия
if ($attempt > 1) {
    $template .= " (Попытка {$attempt}, пожалуйста, сгенерируй другие варианты)";
}

// Формируем массив сообщений для API
$messages = [
    [
        'role' => 'system',
        'content' => $systemMessage
    ],
    [
        'role' => 'user',
        'content' => $template
    ]
];

// Рассчитываем max_tokens исходя из длины одного поздравления и количества вариантов
$maxTokens = intval($MAX_CHARS_PER_GREETING / 4 * 5); // 5 вариантов, примерно 4 символа на токен

// Формируем тело запроса к API с моделью Sonar Pro
$postData = [
    'model' => 'sonar-pro', // модель Sonar Pro
    'messages' => $messages,
    'temperature' => 0.7,
    'max_tokens' => $maxTokens
];

// Инициализация cURL
$ch = curl_init('https://api.perplexity.ai/chat/completions');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Accept: application/json',
    'Content-Type: application/json',
    'Authorization: Bearer ' . $API_KEY
]);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($postData));
curl_setopt($ch, CURLOPT_TIMEOUT, 30);

$response = curl_exec($ch);
$curlErr = curl_error($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

if ($curlErr) {
    http_response_code(500);
    echo json_encode([
        'error' => 'Ошибка при запросе к API: ' . $curlErr,
        'debugInfo' => ['httpCode' => $httpCode]
    ]);
    exit;
}

// Записываем сырой ответ для отладки
file_put_contents(__DIR__ . '/debug_api_response.txt', $response);

// Парсим ответ
$responseData = json_decode($response, true);

if (json_last_error() !== JSON_ERROR_NONE) {
    http_response_code(500);
    echo json_encode([
        'error' => 'Не удалось разобрать ответ API: ' . json_last_error_msg(),
        'httpCode' => $httpCode,
        'rawResponse' => substr($response, 0, 1000)
    ]);
    exit;
}

if (isset($responseData['error'])) {
    http_response_code(500);
    echo json_encode([
        'error' => 'Ошибка API: ' . $responseData['error']['message'],
        'httpCode' => $httpCode
    ]);
    exit;
}

if (!isset($responseData['choices']) || empty($responseData['choices'])) {
    http_response_code(500);
    echo json_encode([
        'error' => 'В ответе API отсутствует поле choices или оно пусто',
        'httpCode' => $httpCode,
        'rawResponse' => substr($response, 0, 1000)
    ]);
    exit;
}

// Извлекаем варианты поздравлений, разбивая по разделителю
$variants = [];
foreach ($responseData['choices'] as $choice) {
    if (isset($choice['message']['content'])) {
        $content = trim($choice['message']['content']);
        $parts = explode($delimiter, $content);
        foreach ($parts as $variant) {
            $variant = trim($variant);
            $variant = preg_replace('/^["\']|["\']$/', '', $variant);
            $variant = preg_replace('/^(Поздравление:|Greeting:|تحية:)\s*/i', '', $variant);
            if (mb_strlen($variant) > $MAX_CHARS_PER_GREETING) {
                $variant = mb_substr($variant, 0, $MAX_CHARS_PER_GREETING);
            }
            if ($variant !== '') {
                $variants[] = $variant;
            }
        }
    }
}

if (empty($variants)) {
    http_response_code(500);
    echo json_encode([
        'error' => 'API вернул пустой список вариантов',
        'httpCode' => $httpCode,
        'rawResponse' => substr($response, 0, 1000)
    ]);
    exit;
}

// Возвращаем варианты и информацию о попытках
echo json_encode([
    'variants' => $variants,
    'attempt' => $attempt,
    'max_attempts' => $MAX_REGENERATE_ATTEMPTS,
    'max_chars_per_greeting' => $MAX_CHARS_PER_GREETING,
    'lang' => $lang
]);
