<?php
header('Content-Type: application/json; charset=utf-8');

// Разрешаем только POST-запросы
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Метод не разрешён']);
    exit;
}

// Включаем отображение всех ошибок
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Логируем запрос для отладки
error_log("Получен запрос на generate.php");

// Подключаем конфиг с настройками и API-ключом
require_once __DIR__ . '/config.php';

// Получаем данные из POST (ожидаем JSON)
$raw_input = file_get_contents('php://input');
error_log("Тело запроса: " . $raw_input);

$input = json_decode($raw_input, true);

if (!$input) {
    error_log("Ошибка декодирования JSON: " . json_last_error_msg());
    http_response_code(400);
    echo json_encode(['error' => 'Некорректные данные']);
    exit;
}

// Отладочный вывод полученных данных
error_log("Успешно получены данные: " . json_encode($input));

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
    'ru' => "Поздравление должно быть на русском языке. Максимальная длина: {$MAX_CHARS} символов.",
    'en' => "The greeting should be in English. Maximum length: {$MAX_CHARS} characters.",
    'ar' => "يجب أن تكون التحية باللغة العربية. الحد الأقصى للطول: {$MAX_CHARS} حرف."
];

// Формируем системное сообщение с ограничением длины и языком
$systemMessage = "Пожалуйста, создай короткое, душевное поздравление для открытки. " . 
                 "Оно должно быть не более {$MAX_CHARS} символов. " .
                 "Ответь только текстом поздравления, без вступлений и дополнительных пояснений. " . 
                 $langInstructions[$lang];

// Формируем шаблон с подстановкой переменных
$template = $GREETING_TEMPLATE;
$template = str_replace('{description}', $description ?: 'без дополнительного описания', $template);
$template = str_replace('{lang}', $lang, $template);

// Добавляем номер попытки в запрос для разнообразия результатов
if ($attempt > 1) {
    $template .= " (Попытка {$attempt}, пожалуйста, сгенерируй другой вариант)";
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

// Формируем тело запроса к API в соответствии с документацией Perplexity
$postData = [
    'model' => $API_MODEL,
    'messages' => $messages,
    'temperature' => 0.7, // добавляем немного разнообразия
    // 'max_tokens' => intval($MAX_CHARS / 4) // Опциональный параметр, используем значение по умолчанию для API
];

// Вывод отладочной информации
if ($DEBUG_MODE) {
    error_log("API URL: " . $API_URL);
    error_log("API Key: " . substr($API_KEY, 0, 5) . "...");
    error_log("Request data: " . json_encode($postData));
}

// Подробная отладка запроса
if ($DEBUG_MODE) {
    error_log("POST данные (raw): " . json_encode($postData, JSON_PRETTY_PRINT));
}

// Проверка API ключа перед отправкой запроса
if (empty($API_KEY)) {
    error_log("КРИТИЧЕСКАЯ ОШИБКА: API ключ Perplexity не найден в переменных окружения");
    http_response_code(500);
    echo json_encode(['error' => 'API ключ не настроен. Пожалуйста, обратитесь к администратору.']);
    exit;
}

// Вернемся к нашему основному запросу с учетом требований документации Perplexity
// Здесь мы используем оригинальную конфигурацию, настроенную для генерации поздравлений

// Структура запроса строго соответствует документации Perplexity
$requestData = [
    'model' => $API_MODEL, // Используем модель из конфигурации
    'messages' => [
        [
            'role' => 'system',
            'content' => 'Ты - помощник для создания красивых поздравительных открыток. Создавай уникальные, искренние поздравления без штампов.'
        ],
        [
            'role' => 'user',
            'content' => 'Создай поздравление для: ' . $description . '. Язык: ' . $lang
        ]
    ],
    'temperature' => 0.7, // Для творческого контента лучше использовать значение от 0.7 до 0.9
];

// Вывод отладочной информации
error_log("API URL: " . $API_URL);
error_log("Запрос к API (формат JSON): " . json_encode($requestData, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT));

// Инициализация cURL
$ch = curl_init($API_URL);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Accept: application/json',
    'Content-Type: application/json',
    'Authorization: Bearer ' . $API_KEY
]);
curl_setopt($ch, CURLOPT_POST, true);
$jsonData = json_encode($requestData);
curl_setopt($ch, CURLOPT_POSTFIELDS, $jsonData);
curl_setopt($ch, CURLOPT_TIMEOUT, 30); // таймаут 30 секунд
curl_setopt($ch, CURLOPT_VERBOSE, $DEBUG_MODE); // включаем подробный вывод для отладки

$response = curl_exec($ch);
$curlErr = curl_error($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

if ($DEBUG_MODE) {
    error_log("HTTP Code: " . $httpCode);
    error_log("cURL Error: " . ($curlErr ?: 'None'));
    error_log("Response: " . substr($response, 0, 300) . "...");
}

if ($curlErr) {
    http_response_code(500);
    echo json_encode([
        'error' => 'Ошибка при запросе к API: ' . $curlErr,
        'debugInfo' => ['httpCode' => $httpCode]
    ]);
    exit;
}

// Сохраняем сырой ответ API для отладки
file_put_contents(__DIR__ . '/debug_api_response.txt', $response);

// Парсим ответ
$responseData = json_decode($response, true);

if (json_last_error() !== JSON_ERROR_NONE) {
    http_response_code(500);
    echo json_encode([
        'error' => 'Не удалось разобрать ответ API: ' . json_last_error_msg(),
        'httpCode' => $httpCode,
        'rawResponse' => substr($response, 0, 1000) // первые 1000 символов для отладки
    ]);
    exit;
}

// Проверяем наличие ошибки в ответе API
if (isset($responseData['error'])) {
    http_response_code(500);
    echo json_encode([
        'error' => 'Ошибка API: ' . ($responseData['error']['message'] ?? 'Неизвестная ошибка'),
        'httpCode' => $httpCode
    ]);
    exit;
}


if (!isset($responseData['choices']) || empty($responseData['choices'])) {
    http_response_code(500);
    echo json_encode([
        'error' => 'В ответе API отсутствует поле choices или оно пусто',
        'httpCode' => $httpCode,
        'rawResponse' => substr($response, 0, 1000) // первые 1000 символов для отладки
    ]);
    exit;
}

// Вывод отладочной информации о полном ответе API
if ($DEBUG_MODE) {
    error_log("Полный ответ API: " . $response);
}

// Подробный вывод информации о результате запроса для отладки
if ($DEBUG_MODE) {
    error_log("HTTP код: " . $httpCode);
    error_log("Curl ошибки: " . ($curlErr ?: 'нет'));
    error_log("Первые 300 символов ответа: " . substr($response, 0, 300));
}

// Извлекаем варианты поздравлений из ответа API
$variants = [];

// Обрабатываем ответ от Perplexity API
if (isset($responseData['choices'][0]['message']['content'])) {
    $fullContent = trim($responseData['choices'][0]['message']['content']);
    
    // Убираем возможные кавычки и вспомогательный текст
    $fullContent = preg_replace('/^["\']|["\']$/', '', $fullContent);
    $fullContent = preg_replace('/^(Поздравление:|Greeting:|تحية:)\s*/i', '', $fullContent);
    
    // Разделяем ответ на варианты (по абзацам или точкам)
    if (strpos($fullContent, "\n\n") !== false) {
        // Если есть абзацы, разделяем по ним
        $parts = explode("\n\n", $fullContent);
    } else {
        // Иначе разделяем по предложениям (точкам)
        $parts = preg_split('/(?<=[.!?])\s+/', $fullContent);
    }
    
    // Добавляем целый ответ как первый вариант
    if (mb_strlen($fullContent) > $MAX_CHARS) {
        $fullContent = mb_substr($fullContent, 0, $MAX_CHARS);
    }
    $variants[] = $fullContent;
    
    // Добавляем дополнительные варианты, если есть
    if (count($parts) > 1) {
        foreach ($parts as $part) {
            $part = trim($part);
            if (empty($part) || $part === $fullContent) continue;
            
            // Ограничиваем длину
            if (mb_strlen($part) > $MAX_CHARS) {
                $part = mb_substr($part, 0, $MAX_CHARS);
            }
            
            $variants[] = $part;
        }
    }
    
    // Если не удалось разделить на части, сгенерируем 3 варианта модификаций исходного текста
    if (count($variants) < 2) {
        // Короткая версия
        $short = preg_replace('/\s*\([^)]+\)|\s*,[^,]+,/', '', $fullContent);
        if ($short !== $fullContent && !empty($short)) {
            $variants[] = $short;
        }
        
        // Добавляем искусственно измененные версии
        $variants[] = "С теплотой и нежностью: " . $fullContent;
        $variants[] = "От всего сердца: " . $fullContent;
    }
}

// Если вариантов нет - генерируем локальные варианты
if (empty($variants)) {
    error_log("API вернул пустой список вариантов или произошла ошибка, создаём локальные варианты");
    
    // Локальная база поздравлений по языкам и темам
    $localGreetings = [
        'ru' => [
            // Русские поздравления
            "С днем рождения! Пусть каждый день будет наполнен счастьем, любовью и теплом близких. Желаю, чтобы все мечты сбылись, а жизнь была яркой и красочной, как этот букет цветов.",
            "От всего сердца поздравляю с праздником! Пусть жизнь играет яркими красками, а каждый день приносит радость и вдохновение. Эти цветы - символ моего искреннего восхищения и тёплых пожеланий.",
            "Примите самые искренние поздравления и пожелания бесконечного счастья, крепкого здоровья и безграничной любви! Пусть этот букет напоминает о том, как важны вы для меня."
        ],
        'en' => [
            // Английские поздравления
            "Happy Birthday! May your day be filled with joy, love, and the warmth of those close to you. I hope all your dreams come true and your life is as vibrant and colorful as this bouquet of flowers.",
            "Heartfelt congratulations on your special day! May life play with bright colors, and each day bring joy and inspiration. These flowers are a symbol of my sincere admiration and warm wishes.",
            "Please accept my most sincere congratulations and wishes for endless happiness, good health, and boundless love! May this bouquet remind you of how important you are to me."
        ],
        'ar' => [
            // Арабские поздравления
            "عيد ميلاد سعيد! نتمنى أن يكون يومك مليئًا بالفرح والحب ودفء المقربين منك. آمل أن تتحقق جميع أحلامك وأن تكون حياتك نابضة بالحيوية وملونة مثل هذه الباقة من الزهور.",
            "تهانينا القلبية في يومك الخاص! قد تلعب الحياة بألوان زاهية ، ويجلب كل يوم الفرح والإلهام. هذه الزهور هي رمز للإعجاب الصادق والتمنيات الحارة.",
            "يرجى قبول أصدق التهاني وتمنياتي بالسعادة اللامتناهية والصحة الجيدة والحب بلا حدود! نأمل أن تذكرك هذه الباقة بمدى أهميتك بالنسبة لي."
        ]
    ];
    
    // Выбираем поздравления для указанного языка
    $langGreetings = isset($localGreetings[$lang]) ? $localGreetings[$lang] : $localGreetings['en'];
    
    // Формируем варианты добавляя описание, если оно есть
    if (!empty($description)) {
        // Добавляем описание в поздравления
        foreach ($langGreetings as &$greeting) {
            $greeting = "Для: " . $description . "\n\n" . $greeting;
        }
    }
    
    $variants = $langGreetings;
    
    // Если не смогли получить варианты даже локально - возвращаем ошибку
    if (empty($variants)) {
        http_response_code(500);
        echo json_encode([
            'error' => 'Не удалось создать варианты поздравлений',
            'httpCode' => $httpCode,
            'rawResponse' => substr($response, 0, 1000) // первые 1000 символов для отладки
        ]);
        exit;
    }
}

// Возвращаем клиенту варианты и информацию о попытках
echo json_encode([
    'variants' => $variants,
    'attempt' => $attempt,
    'max_attempts' => $MAX_REGENERATE_ATTEMPTS,
    'max_chars' => $MAX_CHARS,
    'lang' => $lang
]);