<?php

declare(strict_types=1);

final class Firstsolo
{
    public function __construct(private PDO $pdo) {}

    public function getAll(): array
    {
        $stmt = $this->pdo->query(
            'SELECT c.id, c.name, c.found, m.id AS secondsolo_id, m.type AS secondsolo_type, m.firstsolo_id
             FROM firstmany c
             LEFT JOIN secondmany m ON m.firstsolo_id = c.id
             ORDER BY c.id ASC, m.id ASC'
        );

        return $this->mapRowsToFirstmany($stmt->fetchAll());
    }

    public function getOne(int $id): ?array
    {
        $stmt = $this->pdo->prepare(
            'SELECT c.id, c.name, c.found, m.id AS secondsolo_id, m.type AS secondsolo_type, m.firstsolo_id
             FROM firstmany c
             LEFT JOIN secondmany m ON m.firstsolo_id = c.id
             WHERE c.id = :id
             ORDER BY m.id ASC'
        );
        $stmt->execute(['id' => $id]);

        $firstmany = $this->mapRowsToFirstmany($stmt->fetchAll());

        return $firstmany[0] ?? null;
    }

    public function create(string $name, string $found): array
    {
        $stmt = $this->pdo->prepare(
            'INSERT INTO firstmany (name, found) VALUES (:name, :found)'
        );
        $stmt->execute([
            'name' => $name,
            'found' => $found,
        ]);

        return [
            'id' => (int) $this->pdo->lastInsertId(),
            'name' => $name,
            'found' => $found,
            'secondmany' => [],
        ];
    }

    public function update(int $id, string $name, string $found): ?array
    {
        $stmt = $this->pdo->prepare(
            'UPDATE firstmany SET name = :name, found = :found WHERE id = :id'
        );
        $stmt->execute([
            'id' => $id,
            'name' => $name,
            'found' => $found,
        ]);

        return $this->getOne($id);
    }

    public function delete(int $id): bool
    {
        $stmt = $this->pdo->prepare('DELETE FROM firstmany WHERE id = :id');
        $stmt->execute(['id' => $id]);

        return $stmt->rowCount() > 0;
    }

    public function exists(int $id): bool
    {
        $stmt = $this->pdo->prepare('SELECT 1 FROM firstmany WHERE id = :id');
        $stmt->execute(['id' => $id]);
        return (bool) $stmt->fetchColumn();
    }

    public function existsByNameAndFound(string $name, string $found, ?int $excludeId = null): bool
    {
        $sql = 'SELECT 1 FROM firstmany WHERE name = :name AND found = :found';
        $params = [
            'name' => $name,
            'found' => $found,
        ];

        if ($excludeId !== null) {
            $sql .= ' AND id <> :exclude_id';
            $params['exclude_id'] = $excludeId;
        }

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);

        return (bool) $stmt->fetchColumn();
    }

    private function mapRowsToFirstmany(array $rows): array
    {
        $firstmany = [];

        foreach ($rows as $row) {
            $id = (int) $row['id'];

            if (!isset($firstmany[$id])) {
                $firstmany[$id] = [
                    'id' => $id,
                    'name' => $row['name'],
                    'found' => $row['found'],
                    'secondmany' => [],
                ];
            }

            if ($row['secondsolo_id'] !== null) {
                $firstmany[$id]['secondmany'][] = [
                    'id' => (int) $row['secondsolo_id'],
                    'type' => $row['secondsolo_type'],
                    'firstsolo_id' => (int) $row['firstsolo_id'],
                ];
            }
        }

        return array_values($firstmany);
    }
}

/*
SQL QUERY EXAMPLES FOR THIS MODEL:

1. SELECT all records from firstmany
   SELECT id, name, found FROM firstmany ORDER BY id ASC;
   <<SELECT выбирает данные; id, name, found - нужные колонки; FROM firstmany - из какой таблицы брать; ORDER BY id ASC - сортировка по id от меньшего к большему.>>

2. SELECT one record by id
   SELECT id, name, found FROM firstmany WHERE id = :id;
   <<WHERE id = :id фильтрует одну запись по id; :id - placeholder для безопасной подстановки значения через PDO prepare/execute.>>

3. SELECT only names
   SELECT name FROM firstmany ORDER BY name ASC;
   <<Можно выбрать не все колонки, а только name; ORDER BY name ASC сортирует строки по имени в алфавитном порядке.>>

4. SELECT records found after a date
   SELECT id, name, found FROM firstmany WHERE found >= :date ORDER BY found ASC;
   <<WHERE found >= :date выбирает записи, у которых дата found больше или равна переданной дате.>>

5. SELECT records between two dates
   SELECT id, name, found FROM firstmany WHERE found BETWEEN :date_from AND :date_to;
   <<BETWEEN выбирает значения в диапазоне; здесь даты от :date_from до :date_to включительно.>>

6. Search by part of name
   SELECT id, name, found FROM firstmany WHERE name LIKE :search ORDER BY name ASC;
   <<LIKE ищет по шаблону; в PHP можно передать ['search' => '%' . $search . '%'], чтобы найти часть строки.>>

7. Search by name prefix
   SELECT id, name, found FROM firstmany WHERE name LIKE :prefix ORDER BY name ASC;
   <<Для поиска начала строки можно передать ['prefix' => $prefix . '%']; например 'Bra%' найдет имена, которые начинаются на Bra.>>

8. Count all firstmany records
   SELECT COUNT(*) AS total FROM firstmany;
   <<COUNT(*) считает количество строк; AS total задает имя результата total.>>

9. Count related secondmany records for every firstsolo
   SELECT f.id, f.name, COUNT(s.id) AS secondmany_count
   FROM firstmany f
   LEFT JOIN secondmany s ON s.firstsolo_id = f.id
   GROUP BY f.id, f.name
   ORDER BY f.id ASC;
   <<LEFT JOIN соединяет таблицы; COUNT(s.id) считает связанные записи; GROUP BY группирует результат по каждой записи firstmany.>>

10. Get firstmany with related secondmany records
    SELECT f.id, f.name, f.found, s.id AS secondsolo_id, s.type, s.firstsolo_id
    FROM firstmany f
    LEFT JOIN secondmany s ON s.firstsolo_id = f.id
    ORDER BY f.id ASC, s.id ASC;
    <<LEFT JOIN показывает все записи firstmany даже если связанных secondmany нет; AS secondsolo_id переименовывает колонку в результате.>>

11. Get only firstmany records that have secondmany
    SELECT DISTINCT f.id, f.name, f.found
    FROM firstmany f
    INNER JOIN secondmany s ON s.firstsolo_id = f.id;
    <<INNER JOIN возвращает только те записи, где связь существует; DISTINCT убирает дубликаты firstmany, если secondmany несколько.>>

12. Get firstmany records without secondmany
    SELECT f.id, f.name, f.found
    FROM firstmany f
    LEFT JOIN secondmany s ON s.firstsolo_id = f.id
    WHERE s.id IS NULL;
    <<LEFT JOIN + WHERE s.id IS NULL находит записи firstmany, у которых нет связанных строк в secondmany.>>

13. Insert new firstmany record
    INSERT INTO firstmany (name, found) VALUES (:name, :found);
    <<INSERT INTO добавляет новую строку; (name, found) - колонки; VALUES (:name, :found) - значения через безопасные placeholders.>>

14. Update name only
    UPDATE firstmany SET name = :name WHERE id = :id;
    <<UPDATE изменяет существующую строку; SET задает новое значение; WHERE id = :id важно, чтобы не обновить всю таблицу.>>

15. Update found date only
    UPDATE firstmany SET found = :found WHERE id = :id;
    <<Можно обновлять одну колонку; здесь меняется только дата found у записи с нужным id.>>

16. Update name and found together
    UPDATE firstmany SET name = :name, found = :found WHERE id = :id;
    <<Можно обновить несколько колонок сразу через запятую.>>

17. Delete one firstmany record
    DELETE FROM firstmany WHERE id = :id;
    <<DELETE удаляет строку; WHERE id = :id ограничивает удаление одной записью; связанные secondmany удалятся автоматически из-за ON DELETE CASCADE.>>

18. Check if firstmany exists
    SELECT 1 FROM firstmany WHERE id = :id;
    <<SELECT 1 используется для быстрой проверки существования записи; данные самой строки не загружаются.>>

19. Limit results for pagination
    SELECT id, name, found FROM firstmany ORDER BY id ASC LIMIT :limit OFFSET :offset;
    <<LIMIT задает сколько строк вернуть; OFFSET задает сколько строк пропустить, например для страниц.>>

20. Get latest records by date
    SELECT id, name, found FROM firstmany ORDER BY found DESC, id DESC LIMIT 10;
    <<ORDER BY found DESC сортирует от новой даты к старой; LIMIT 10 вернет только 10 последних записей.>>

21. Use DATE value
    SELECT id, name, found FROM firstmany WHERE found = :found;
    <<DATE сравнивается как строка формата YYYY-MM-DD, но значение должно быть валидной датой.>>

22. Use TIMESTAMP fields if table has created_at
    SELECT id, name, created_at FROM firstmany WHERE created_at >= :created_at;
    <<TIMESTAMP хранит дату и время; запрос выбирает записи, созданные после указанного момента.>>

23. Use DATETIME fields if table has event_datetime
    SELECT id, name, event_datetime FROM firstmany WHERE event_datetime BETWEEN :start AND :end;
    <<DATETIME удобно фильтровать по диапазону даты и времени.>>

24. Use TIME field if table has start_time
    SELECT id, name, start_time FROM firstmany WHERE start_time >= :time;
    <<TIME хранит только время; запрос может выбрать записи после конкретного времени, например после 09:00:00.>>

25. Use ENUM field if table has status
    SELECT id, name, status FROM firstmany WHERE status = :status;
    <<ENUM сравнивается как строка, но в БД разрешены только заранее заданные значения, например new, active или blocked.>>

26. Use BOOLEAN field if table has is_active
    SELECT id, name, is_active FROM firstmany WHERE is_active = TRUE;
    <<BOOLEAN в MySQL обычно хранится как 1/0; TRUE выбирает активные записи.>>

27. Use DECIMAL field if table has price
    SELECT id, name, price FROM firstmany WHERE price BETWEEN :min_price AND :max_price;
    <<DECIMAL подходит для денег; BETWEEN выбирает цены в заданном диапазоне.>>

28. Use FLOAT field if table has rating
    SELECT id, name, rating FROM firstmany WHERE rating >= :rating ORDER BY rating DESC;
    <<FLOAT подходит для примерных дробных чисел; запрос выбирает записи с рейтингом выше заданного.>>

29. Use email field
    SELECT id, name, email FROM firstmany WHERE email = :email;
    <<Email обычно ищут точным сравнением; если поле UNIQUE, одинаковых email быть не должно.>>

30. Use UUID field
    SELECT id, uuid, name FROM firstmany WHERE uuid = :uuid;
    <<UUID обычно хранится как CHAR(36); по нему можно искать запись как по внешнему публичному идентификатору.>>

31. Use JSON field
    SELECT id, name, data_json FROM firstmany WHERE JSON_EXTRACT(data_json, '$.city') = :city;
    <<JSON_EXTRACT достает значение из JSON по пути; '$.city' означает поле city внутри JSON-объекта.>>

32. Use BLOB field
    SELECT id, name, image FROM firstmany WHERE id = :id;
    <<BLOB хранит бинарные данные; обычно лучше хранить файл в папке, а в БД сохранять путь к файлу.>>

33. Add foreign key value
    INSERT INTO secondmany (type, firstsolo_id) VALUES (:type, :firstsolo_id);
    <<firstsolo_id должен существовать в firstmany.id, иначе FOREIGN KEY не даст добавить неправильную связь.>>

34. Delete parent and related children
    DELETE FROM firstmany WHERE id = :id;
    <<Если в schema.sql стоит ON DELETE CASCADE, то при удалении firstmany автоматически удаляются связанные строки secondmany.>>

35. Transaction example
    START TRANSACTION;
    INSERT INTO firstmany (name, found) VALUES (:name, :found);
    INSERT INTO secondmany (type, firstsolo_id) VALUES (:type, LAST_INSERT_ID());
    COMMIT;
    <<Транзакция выполняет несколько запросов как одну операцию; если что-то пошло не так, можно сделать ROLLBACK вместо COMMIT.>>
*/
