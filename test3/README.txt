ГЕНЕРАТОР ПОЗДРАВЛЕНИЙ ДЛЯ БИТРИКС
===================================

УСТАНОВКА:
1. Распакуйте архив в директорию вашего сайта (например, в /local/components/custom/greeting-generator/)
2. Откройте файл config.php и укажите ваш API-ключ Perplexity в строке $API_KEY = '';
3. Добавьте компонент на страницу сайта с помощью включаемых областей или напрямую через подключение index.php

НАСТРОЙКА:
- В файле config.php вы можете изменить:
  * Шаблон поздравления в переменной $GREETING_TEMPLATE
  * Максимальное количество символов $MAX_CHARS
  * Ограничение числа перегенераций $MAX_REGENERATE_ATTEMPTS (0 - без ограничений)

ИСПОЛЬЗОВАНИЕ:
1. Пользователь выбирает язык поздравления (русский, английский или арабский)
2. Вводит описание поздравления (опционально)
3. Нажимает "Сгенерировать"
4. Просматривает варианты поздравлений, выбирает подходящий
5. При необходимости редактирует текст вручную или перегенерирует

Версия 1.0.0
