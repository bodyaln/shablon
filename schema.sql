

-- DATA TYPES USED IN THIS DATABASE:
-- id: id INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
-- <<id - название колонки; INT - целое число; UNSIGNED - только положительные числа; NOT NULL - значение обязательно; AUTO_INCREMENT - MySQL сам увеличивает id при новой записи; PRIMARY KEY - главный уникальный ключ таблицы.>>

-- name: name VARCHAR(100) NOT NULL,
-- <<name - название колонки; VARCHAR(100) - строка длиной максимум 100 символов; NOT NULL - значение обязательно и не может быть пустым NULL.>>

-- found: found DATE NOT NULL,
-- <<found - название колонки; DATE - дата в формате YYYY-MM-DD; NOT NULL - дата обязательна.>>

-- type: type VARCHAR(100) NOT NULL,
-- <<type - название колонки; VARCHAR(100) - строка максимум 100 символов; NOT NULL - тип обязательно должен быть указан.>>

-- firstsolo_id: firstsolo_id INT UNSIGNED NOT NULL,
-- <<firstsolo_id - колонка для связи с другой таблицей; INT - целое число; UNSIGNED - только положительное; NOT NULL - связь обязательна.>>
--
-- OTHER COMMON MYSQL DATA TYPES EXAMPLES:

-- ^[A-Z]{2,3}$
-- id: id CHAR(2) NOT NULL PRIMARY KEY CHECK (id REGEXP '^[A-Z]{2}$'),
-- <<CHAR(2) - строка ровно 2 символа; PRIMARY KEY - уникальный главный ключ; CHECK - проверка значения; REGEXP '^[A-Z]{2}$' - разрешает только 2 большие английские буквы, например SK или CZ.>>

-- created_at: created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
-- <<TIMESTAMP - дата и время; DEFAULT CURRENT_TIMESTAMP - если значение не передали, MySQL автоматически поставит текущую дату и время.>>

-- updated_at: updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
-- <<ON UPDATE CURRENT_TIMESTAMP - MySQL автоматически обновляет дату и время при каждом изменении строки.>>

-- title: title VARCHAR(255) NOT NULL,
-- <<VARCHAR(255) - текстовая строка максимум 255 символов; NOT NULL - поле обязательно.>>

-- description: description TEXT,
-- <<TEXT - длинный текст; можно хранить описание, заметку или статью; без NOT NULL поле может быть NULL.>>

-- short_text: short_text TINYTEXT,
-- <<TINYTEXT - короткий текстовый тип, меньше чем TEXT; подходит для небольших заметок.>>

-- long_text: long_text LONGTEXT,
-- <<LONGTEXT - очень длинный текст; подходит для больших документов или HTML-контента.>>

-- count: count INT NOT NULL DEFAULT 0,
-- <<INT - целое число; DEFAULT 0 - если значение не передали, будет записан 0.>>

-- small_count: small_count TINYINT UNSIGNED NOT NULL DEFAULT 0,
-- <<TINYINT - маленькое целое число; UNSIGNED - без отрицательных значений; часто используется для маленьких счетчиков или флагов.>>

-- big_count: big_count BIGINT UNSIGNED NOT NULL,
-- <<BIGINT - очень большое целое число; UNSIGNED - только положительные значения.>>

-- price: price DECIMAL(10, 2) NOT NULL,
-- <<DECIMAL(10, 2) - точное число для денег; 10 - всего цифр; 2 - цифры после запятой, например 12345678.90.>>

-- rating: rating FLOAT,
-- <<FLOAT - число с плавающей точкой; подходит для примерных дробных значений, например рейтинг 4.5.>>

-- is_active: is_active BOOLEAN NOT NULL DEFAULT TRUE,
-- <<BOOLEAN - логическое значение TRUE/FALSE; в MySQL обычно хранится как 1 или 0; DEFAULT TRUE - по умолчанию активно.>>

-- birth_date: birth_date DATE NOT NULL,
-- <<DATE - только дата без времени; формат YYYY-MM-DD; подходит для даты рождения или найденной даты.>>

-- start_time: start_time TIME,
-- <<TIME - только время без даты; формат HH:MM:SS.>>

-- event_datetime: event_datetime DATETIME NOT NULL,
-- <<DATETIME - дата и время вместе; формат YYYY-MM-DD HH:MM:SS.>>

-- status: status ENUM('new', 'active', 'blocked') NOT NULL DEFAULT 'new',
-- <<ENUM - выбор только из заранее заданных значений; здесь можно записать только new, active или blocked; DEFAULT 'new' - значение по умолчанию.>>

-- data_json: data_json JSON,
-- <<JSON - хранит структурированные данные в формате JSON, например объект или массив.>>

-- image: image BLOB,
-- <<BLOB - бинарные данные; можно хранить файл или изображение, хотя чаще изображения хранят в папке, а в БД путь к файлу.>>

-- uuid: uuid CHAR(36) NOT NULL UNIQUE,
-- <<CHAR(36) - строка фиксированной длины 36 символов; UNIQUE - значение должно быть уникальным, повторяться нельзя.>>

-- email: email VARCHAR(255) NOT NULL UNIQUE,
-- <<VARCHAR(255) - строка до 255 символов; UNIQUE - одинаковые email в таблице запрещены.>>

-- foreign_id: foreign_id INT UNSIGNED NOT NULL,
-- <<foreign_id - колонка для id из другой таблицы; INT UNSIGNED - положительное целое число; NOT NULL - связь обязательна.>>

-- relation: CONSTRAINT fk_table_relation FOREIGN KEY (foreign_id) REFERENCES other_table(id) ON DELETE CASCADE,
-- <<CONSTRAINT - имя ограничения; FOREIGN KEY - внешний ключ; REFERENCES other_table(id) - ссылается на id в другой таблице; ON DELETE CASCADE - при удалении родительской записи связанные строки удалятся автоматически.>>



CREATE DATABASE IF NOT EXISTS mydatabase
CHARACTER SET utf8mb4
COLLATE utf8mb4_unicode_ci;

USE mydatabase;


CREATE TABLE IF NOT EXISTS firstmany (
    id INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY COMMENT 'Primary key',
    varcharform VARCHAR(100) NOT NULL COMMENT 'Main title or name',
    dateform DATE NOT NULL COMMENT 'Date value in YYYY-MM-DD format',
    code CHAR(8) NULL UNIQUE COMMENT 'Fixed-length short code',

    short_note TINYTEXT NULL COMMENT 'Short text field example',
    text_note TEXT NULL COMMENT 'Standard long text field',
    long_note LONGTEXT NULL COMMENT 'Very large text field',

    timeform TIME NULL COMMENT 'Time only, HH:MM:SS',
    datetimeform DATETIME NULL COMMENT 'Date and time together',

    status ENUM('new', 'active', 'archived') NOT NULL DEFAULT 'new' COMMENT 'Limited predefined set of values',
    floatform FLOAT NULL COMMENT 'Approximate decimal number',
    decimalform DECIMAL(10, 2) NOT NULL DEFAULT 0.00 COMMENT 'Exact decimal value for money',
    bigintform BIGINT UNSIGNED NOT NULL DEFAULT 0 COMMENT 'Large counter value',
    booleanform BOOLEAN NOT NULL DEFAULT TRUE COMMENT 'Boolean flag',

    jsonform JSON NULL COMMENT 'Structured JSON data',

    timestampform TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT 'Created timestamp'
);


CREATE TABLE IF NOT EXISTS secondmany (
    id INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY COMMENT 'Primary key',
    varcharform VARCHAR(100) NOT NULL COMMENT 'Child entity type or category',
    firstsolo_id INT UNSIGNED NOT NULL COMMENT 'Foreign key to firstmany.id',

    charform CHAR(12) NULL UNIQUE COMMENT 'Fixed-length item code',
    smallform SMALLINT UNSIGNED NOT NULL DEFAULT 1 COMMENT 'Small positive integer',
    tinyintform TINYINT UNSIGNED NOT NULL DEFAULT 0 COMMENT 'Small priority flag or order',
    decimalform DECIMAL(10, 2) NOT NULL DEFAULT 0.00 COMMENT 'Price example',
    booleanform BOOLEAN NOT NULL DEFAULT FALSE COMMENT 'Boolean marker',

    dateform DATE NULL COMMENT 'Date-only example',
    timeform TIME NULL COMMENT 'Time-only example',
    datetimeform DATETIME NULL COMMENT 'Full date-time example',

    textform TEXT NULL COMMENT 'Additional text notes',
    blobform BLOB NULL COMMENT 'Binary data example',

    timestampform TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
        COMMENT 'Created timestamp',
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
        ON UPDATE CURRENT_TIMESTAMP
        COMMENT 'Auto-updated timestamp',

    CONSTRAINT fk_secondmany_firstsolo
        FOREIGN KEY (firstsolo_id) REFERENCES firstmany(id)
        ON DELETE CASCADE
);

-- ============================================================
-- OPTIONAL SAMPLE DATA
-- These INSERT examples show how different data types can be used.
-- ============================================================
INSERT INTO firstmany (
    name,
    found,
    uuid,
    code,
    short_note,
    description,
    long_note,
    event_time,
    published_at,
    status,
    rating,
    budget,
    view_count,
    is_active,
    metadata
) VALUES (
    'Example Parent',
    '2026-04-23',
    '123e4567-e89b-12d3-a456-426614174000',
    'ABCD1234',
    'Short note example',
    'General description for the main entity.',
    'Extended long text example for documentation or content.',
    '09:30:00',
    '2026-04-23 09:30:00',
    'active',
    4.5,
    199.99,
    1250,
    TRUE,
    JSON_OBJECT('source', 'manual', 'tags', JSON_ARRAY('demo', 'sql'))
);

INSERT INTO secondmany (
    type,
    firstsolo_id,
    item_code,
    quantity,
    priority,
    unit_price,
    is_featured,
    available_on,
    reminder_time,
    processed_at,
    notes
) VALUES (
    'Example Child',
    1,
    'ITEM00000001',
    3,
    1,
    49.90,
    TRUE,
    '2026-04-24',
    '10:15:00',
    '2026-04-24 10:15:00',
    'Child record linked to the parent entity.'
);
