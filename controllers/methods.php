<?php

declare(strict_types=1);

trait FirstsoloControllerUnusedValidationMethods
{
    // INT
    private function validatePositiveIntBody(mixed $value): int
    {
        // Проверяем id из JSON body: после json_decode число обычно приходит как int.
        if (!is_int($value) || $value < 1) {
            // Разрешаем только integer больше или равный 1.
            Response::json(['error' => 'Validation failed for id'], 422);
        }

        // Возвращаем проверенный id.
        return $value;
    }

    // CHAR
    private function validateChar2Uppercase(mixed $value): string
    {
        // Проверяем строковый код из двух больших английских букв, например SK или CZ.
        if (!is_string($value) || !preg_match('/^[A-Z]{2}$/', $value)) {
            // Регулярка ^[A-Z]{2}$ означает: от начала до конца строки ровно 2 символа A-Z.
            Response::json(['error' => 'Validation failed for code'], 422);
        }

        // Возвращаем проверенный код.
        return $value;
    }

    // TIMESTAMP
    private function validateTimestampYmdHis(mixed $value): string
    {
        // TIMESTAMP проверяем тем же форматом, что и DATETIME: YYYY-MM-DD HH:MM:SS.
        return $this->validateDateTimeStringYmdHis($value);
    }

    // DATETIME
    private function validateDateTimeYmdHis(mixed $value): string
    {
        // DATETIME проверяем общей функцией validateDateTimeStringYmdHis().
        return $this->validateDateTimeStringYmdHis($value);
    }

    // TIME
    private function validateTimeHis(mixed $value): string
    {
        // Проверяем время строкой в формате HH:MM:SS.
        if (!is_string($value) || !preg_match('/^(?:[01][0-9]|2[0-3]):[0-5][0-9]:[0-5][0-9]$/', $value)) {
            // Регулярка разрешает часы 00-23, минуты 00-59 и секунды 00-59.
            Response::json(['error' => 'Validation failed for time'], 422);
        }

        // Возвращаем проверенное время.
        return $value;
    }

    // VARCHAR
    private function validateVarcharTitle(mixed $value): string
    {
        // Проверяем, что title пришел строкой, потому что VARCHAR хранит текст.
        if (!is_string($value)) {
            // Все нестроковые значения отклоняем.
            Response::json(['error' => 'Validation failed for title'], 422);
        }

        // Убираем пробелы слева и справа.
        $value = trim($value);

        if (
            // Заголовок не может быть пустым.
            $value === '' ||
            // VARCHAR(255) разрешает максимум 255 символов.
            mb_strlen($value) > 255 ||
            // Регулярка разрешает буквы, цифры, пробелы и базовые знаки пунктуации.
            !preg_match("/^[\\p{L}\\p{N}][\\p{L}\\p{N}\\s.,:;!?()'\"-]{0,254}$/u", $value)
        ) {
            // Если title пустой, слишком длинный или с запрещенными символами, возвращаем ошибку.
            Response::json(['error' => 'Validation failed for title'], 422);
        }

        // Возвращаем очищенный title.
        return $value;
    }

    // TEXT
    private function validateTextNullable(mixed $value): ?string
    {
        // Если description не обязателен и пришел null, оставляем null.
        if ($value === null) {
            // Возвращаем null, потому что в примере TEXT без NOT NULL.
            return null;
        }

        // TEXT должен быть строкой и не должен превышать примерный лимит 65535 символов.
        if (!is_string($value) || mb_strlen($value) > 65535) {
            // Если пришел не текст или слишком длинный текст, возвращаем ошибку.
            Response::json(['error' => 'Validation failed for description'], 422);
        }

        // Убираем лишние пробелы по краям и возвращаем текст.
        return trim($value);
    }

    // TINYTEXT
    private function validateTinyTextNullable(mixed $value): ?string
    {
        // Если короткий текст не передан, разрешаем null.
        if ($value === null) {
            // Возвращаем null для необязательного поля.
            return null;
        }

        // TINYTEXT проверяем как строку до 255 символов.
        if (!is_string($value) || mb_strlen($value) > 255) {
            // Если значение не строка или длиннее 255 символов, это ошибка.
            Response::json(['error' => 'Validation failed for short_text'], 422);
        }

        // Возвращаем очищенную строку.
        return trim($value);
    }

    // LONGTEXT
    private function validateLongTextNullable(mixed $value): ?string
    {
        // LONGTEXT может быть необязательным, поэтому null разрешаем.
        if ($value === null) {
            // Возвращаем null без дополнительной проверки.
            return null;
        }

        // Проверяем, что значение действительно строка.
        if (!is_string($value)) {
            // Не строковые значения для LONGTEXT отклоняем.
            Response::json(['error' => 'Validation failed for long_text'], 422);
        }

        // Возвращаем текст без лишних пробелов по краям.
        return trim($value);
    }

    // INT UNSIGNED
    private function validateUnsignedIntCount(mixed $value): int
    {
        // count - обычное положительное целое число, поэтому используем общую проверку unsigned int.
        return $this->validateUnsignedIntValue($value);
    }

    // TINYINT UNSIGNED
    private function validateTinyUnsignedIntValue(mixed $value): int
    {
        // TINYINT UNSIGNED в MySQL хранит значения от 0 до 255.
        if (!is_int($value) || $value < 0 || $value > 255) {
            // Если значение не int или выходит за диапазон 0-255, возвращаем ошибку.
            Response::json(['error' => 'Validation failed for small_count'], 422);
        }

        // Возвращаем проверенное маленькое число.
        return $value;
    }

    // BIGINT UNSIGNED
    private function validateBigUnsignedIntValue(mixed $value): string
    {
        // BIGINT может быть больше безопасного integer в PHP, поэтому принимаем строку или int.
        if (!is_string($value) && !is_int($value)) {
            // Остальные типы, например array или bool, отклоняем.
            Response::json(['error' => 'Validation failed for big_count'], 422);
        }

        // Превращаем значение в строку, чтобы безопасно проверить регуляркой.
        $value = (string) $value;

        // Разрешаем 0 или положительное число без ведущих нулей, максимум 20 цифр.
        if (!preg_match('/^(?:0|[1-9][0-9]{0,19})$/', $value)) {
            // Если формат числа неправильный, возвращаем ошибку.
            Response::json(['error' => 'Validation failed for big_count'], 422);
        }

        // Возвращаем строку, чтобы не потерять большое число из-за лимитов PHP int.
        return $value;
    }

    // DECIMAL
    private function validateDecimalPrice(mixed $value): string
    {
        // DECIMAL может прийти строкой, int или float.
        if (!is_string($value) && !is_int($value) && !is_float($value)) {
            // Другие типы не подходят для цены.
            Response::json(['error' => 'Validation failed for price'], 422);
        }

        // Приводим к строке, чтобы проверить точный формат DECIMAL.
        $value = (string) $value;

        // DECIMAL(10, 2): до 8 цифр до точки и до 2 цифр после точки.
        if (!preg_match('/^(?:0|[1-9][0-9]{0,7})(?:\\.[0-9]{1,2})?$/', $value)) {
            // Отклоняем отрицательные числа, ведущие нули и больше 2 знаков после точки.
            Response::json(['error' => 'Validation failed for price'], 422);
        }

        // Возвращаем цену строкой, чтобы сохранить точность десятичного числа.
        return $value;
    }

    // FLOAT
    private function validateFloatNonNegative(mixed $value): float
    {
        // FLOAT может прийти как int, float или строка с числом.
        if (!is_int($value) && !is_float($value) && !is_string($value)) {
            // Остальные типы отклоняем.
            Response::json(['error' => 'Validation failed for rating'], 422);
        }

        // Регулярка разрешает только неотрицательное число, например 4 или 4.5.
        if (!preg_match('/^(?:0|[1-9][0-9]*)(?:\\.[0-9]+)?$/', (string) $value)) {
            // Если строка не похожа на число, возвращаем ошибку.
            Response::json(['error' => 'Validation failed for rating'], 422);
        }

        // Преобразуем значение в float.
        return (float) $value;
    }

    // BOOLEAN
    private function validateBoolValue(mixed $value): bool
    {
        // BOOLEAN из JSON должен прийти настоящим true или false.
        if (!is_bool($value)) {
            // Строки "true", "false", числа 1 и 0 здесь не принимаем.
            Response::json(['error' => 'Validation failed for is_active'], 422);
        }

        // Возвращаем проверенное логическое значение.
        return $value;
    }

    // ENUM
    private function validateEnumStatus(mixed $value): string
    {
        // ENUM проверяем как строку из заранее разрешенного списка.
        if (!is_string($value) || !in_array($value, ['new', 'active', 'blocked'], true)) {
            // Разрешены только new, active или blocked.
            Response::json(['error' => 'Validation failed for status'], 422);
        }

        // Возвращаем проверенный статус.
        return $value;
    }

    // JSON
    private function validateJsonArray(mixed $value): array
    {
        // После json_decode объект JSON обычно приходит как array.
        if (!is_array($value)) {
            // Если значение не массив, значит это не ожидаемый JSON object/array.
            Response::json(['error' => 'Validation failed for data_json'], 422);
        }

        // Возвращаем проверенную структуру.
        return $value;
    }

    // BLOB
    private function validateBase64BlobValue(mixed $value): string
    {
        // BLOB удобно принимать в API как base64-строку.
        if (!is_string($value) || !preg_match('/^[A-Za-z0-9+\\/]+={0,2}$/', $value)) {
            // Регулярка проверяет допустимые символы base64 и возможные знаки = в конце.
            Response::json(['error' => 'Validation failed for image'], 422);
        }

        // Декодируем base64 в бинарную строку; true включает строгую проверку.
        $decoded = base64_decode($value, true);

        // Если декодирование не удалось, base64 был неправильный.
        if ($decoded === false) {
            // Возвращаем ошибку для неправильного бинарного значения.
            Response::json(['error' => 'Validation failed for image'], 422);
        }

        // Возвращаем уже декодированные бинарные данные для записи в BLOB.
        return $decoded;
    }

    // UUID
    private function validateUuidValue(mixed $value): string
    {
        // UUID должен быть строкой в формате xxxxxxxx-xxxx-Mxxx-Nxxx-xxxxxxxxxxxx.
        if (!is_string($value) || !preg_match('/^[0-9a-fA-F]{8}-[0-9a-fA-F]{4}-[1-5][0-9a-fA-F]{3}-[89abAB][0-9a-fA-F]{3}-[0-9a-fA-F]{12}$/', $value)) {
            // Регулярка проверяет стандартный UUID версии 1-5 и корректный variant.
            Response::json(['error' => 'Validation failed for uuid'], 422);
        }

        // Возвращаем UUID в нижнем регистре для единого хранения.
        return strtolower($value);
    }

    // EMAIL
    private function validateEmail(mixed $value): string
    {
        // Email должен прийти строкой.
        if (!is_string($value)) {
            // Не строковые значения отклоняем.
            Response::json(['error' => 'Validation failed for email'], 422);
        }

        // Убираем пробелы по краям.
        $value = trim($value);

        // Проверяем длину VARCHAR(255) и формат email встроенным PHP-фильтром.
        if (mb_strlen($value) > 255 || filter_var($value, FILTER_VALIDATE_EMAIL) === false) {
            // Если email слишком длинный или неверного формата, возвращаем ошибку.
            Response::json(['error' => 'Validation failed for email'], 422);
        }

        // Возвращаем проверенный email.
        return $value;
    }

    // INT UNSIGNED
    private function validateUnsignedIntValue(mixed $value): int
    {
        // UNSIGNED INT должен быть целым числом без отрицательных значений.
        if (!is_int($value) || $value < 0) {
            // Если это не int или число меньше 0, возвращаем ошибку.
            Response::json(['error' => 'Validation failed for unsigned integer'], 422);
        }

        // Возвращаем проверенное целое число.
        return $value;
    }

    // DATETIME
    private function validateDateTimeStringYmdHis(mixed $value): string
    {
        // Проверяем, что дата и время пришли строкой в формате YYYY-MM-DD HH:MM:SS.
        if (!is_string($value) || !preg_match('/^[0-9]{4}-[0-9]{2}-[0-9]{2} [0-9]{2}:[0-9]{2}:[0-9]{2}$/', $value)) {
            // Регулярка проверяет только форму записи, например 2026-04-23 15:30:00.
            Response::json(['error' => 'Validation failed for datetime'], 422);
        }

        // Создаем DateTime строго по формату Y-m-d H:i:s.
        $date = DateTime::createFromFormat('Y-m-d H:i:s', $value);
        // Получаем ошибки и предупреждения DateTime.
        $errors = DateTime::getLastErrors();

        if (
            // Если DateTime не смог создать объект, значение неправильное.
            $date === false ||
            // Проверяем, что DateTime не исправил дату автоматически, например 2026-02-30.
            $date->format('Y-m-d H:i:s') !== $value ||
            // Проверяем предупреждения и ошибки парсинга.
            ($errors !== false && ($errors['warning_count'] > 0 || $errors['error_count'] > 0))
        ) {
            // Если дата-время невалидные, возвращаем ошибку.
            Response::json(['error' => 'Validation failed for datetime'], 422);
        }

        // Возвращаем проверенное значение DATETIME/TIMESTAMP.
        return $value;
    }
}
