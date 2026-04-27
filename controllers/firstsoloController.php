<?php

declare(strict_types=1);

final class FirstsoloController
{
    public function __construct(private Firstsolo $firstsoloModel) {}

    // GET /firstmany
    public function getAll(): void
    {
        Response::json($this->firstsoloModel->getAll());
    }

    // GET /firstmany/{id}
    public function getOne(array $params): void
    {
        $id = $this->validatePositiveIntPath($params['id'] ?? null);
        $firstsolo = $this->firstsoloModel->getOne($id);

        if ($firstsolo === null) {
            Response::json(['error' => 'Firstsolo not found'], 404);
        }

        Response::json($firstsolo);
    }

    // POST /firstmany
    // 201 Created - новая запись успешно создана.
    // 400 Bad Request - тело запроса не является корректным JSON.
    // 409 Conflict - запись с таким же name и found уже существует.
    // 422 Unprocessable Entity - JSON прочитан, но значения полей не прошли валидацию.
    public function post(): void
    {
        $data = $this->readJsonBody();

        $name = $this->validateVarcharName($data['name'] ?? null);
        $found = $this->validateDateYmd($data['found'] ?? null);

        if ($this->firstsoloModel->existsByNameAndFound($name, $found)) {
            Response::json(['error' => 'Firstsolo already exists'], 409);
        }

        $firstsolo = $this->firstsoloModel->create($name, $found);
        Response::json($firstsolo, 201);
    }

    // PUT /firstmany/{id}
    public function put(array $params): void
    {
        $id = $this->validatePositiveIntPath($params['id'] ?? null);
        $data = $this->readJsonBody();

        $name = $this->validateVarcharName($data['name'] ?? null);
        $found = $this->validateDateYmd($data['found'] ?? null);

        if ($this->firstsoloModel->getOne($id) === null) {
            Response::json(['error' => 'Firstsolo not found'], 404);
        }

        if ($this->firstsoloModel->existsByNameAndFound($name, $found, $id)) {
            Response::json(['error' => 'Firstsolo already exists'], 409);
        }

        $firstsolo = $this->firstsoloModel->update($id, $name, $found);
        Response::json($firstsolo);
    }

    // PATCH /firstmany/{id}
    public function patch(array $params): void
    {
        $id = $this->validatePositiveIntPath($params['id'] ?? null);
        $data = $this->readJsonBody();
        $firstsolo = $this->firstsoloModel->getOne($id);

        if ($firstsolo === null) {
            Response::json(['error' => 'Firstsolo not found'], 404);
        }

        if (!array_key_exists('name', $data) && !array_key_exists('found', $data)) {
            Response::json(['error' => 'Bad data'], 400);
        }

        $name = $firstsolo['name'];
        $found = $firstsolo['found'];

        if (array_key_exists('name', $data)) {
            $name = $this->validateVarcharName($data['name']);
        }

        if (array_key_exists('found', $data)) {
            $found = $this->validateDateYmd($data['found']);
        }

        if ($this->firstsoloModel->existsByNameAndFound($name, $found, $id)) {
            Response::json(['error' => 'Firstsolo already exists'], 409);
        }

        $firstsolo = $this->firstsoloModel->update($id, $name, $found);
        Response::json($firstsolo);
    }

    // DELETE /firstmany/{id}
    public function delete(array $params): void
    {
        $id = $this->validatePositiveIntPath($params['id'] ?? null);

        if (!$this->firstsoloModel->delete($id)) {
            Response::json(['error' => 'Firstsolo not found'], 404);
        }

        Response::empty(204);
    }

    private function readJsonBody(): array
    {
        $raw = file_get_contents('php://input');
        $data = json_decode($raw ?: '', true);

        if (!is_array($data)) {
            Response::json(['error' => 'Bad data'], 400);
        }

        return $data;
    }

    // VARCHAR
    private function validateVarcharName(mixed $value): string
    {
        // Проверяем, что значение пришло строкой, потому что name хранится как VARCHAR.
        if (!is_string($value)) {
            // Если это не строка, JSON понятен, но поле не прошло валидацию, поэтому 422 Unprocessable Entity.
            Response::json(['error' => 'Validation failed for name'], 422);
        }

        // Убираем лишние пробелы слева и справа, чтобы " Name " стало "Name".
        $value = trim($value);

        if (
            // Запрещаем пустую строку после trim().
            $value === '' ||
            // Проверяем максимум 100 символов, как указано в VARCHAR(100).
            mb_strlen($value) > 100 ||
            // Регулярка: первый символ буква/цифра, дальше буквы, цифры, пробелы, точка, апостроф или дефис.
            !preg_match("/^[\\p{L}\\p{N}][\\p{L}\\p{N}\\s.'-]{0,99}$/u", $value)
        ) {
            // Если строка пустая, слишком длинная или не подходит под регулярку, это 422 Unprocessable Entity.
            Response::json(['error' => 'Validation failed for name'], 422);
        }

        // Возвращаем уже очищенную и проверенную строку.
        return $value;
    }

    // DATE
    private function validateDateYmd(mixed $value): string
    {
        // Проверяем, что дата пришла строкой, например "2026-04-23".
        if (!is_string($value)) {
            // Если поле пришло не строкой, тело запроса прочитано, но само значение невалидно, поэтому 422.
            Response::json(['error' => 'Validation failed for found'], 422);
        }

        // Убираем пробелы по краям.
        $value = trim($value);
        // Пытаемся создать дату строго по формату Y-m-d, то есть YYYY-MM-DD.
        $date = DateTime::createFromFormat('Y-m-d', $value);
        // Получаем предупреждения и ошибки, которые могли появиться при разборе даты.
        $errors = DateTime::getLastErrors();

        if (
            // Если DateTime не смог создать дату, значит формат или дата неправильные.
            $date === false ||
            // Проверяем, что после разбора дата осталась такой же, например 2026-02-30 не пройдет.
            $date->format('Y-m-d') !== $value ||
            // Проверяем внутренние ошибки DateTime, например несуществующий день или месяц.
            ($errors !== false && ($errors['warning_count'] > 0 || $errors['error_count'] > 0))
        ) {
            // Если дата невалидная, это ошибка в поле found, поэтому возвращаем 422 Unprocessable Entity.
            Response::json(['error' => 'Validation failed for found'], 422);
        }

        // Возвращаем дату в формате YYYY-MM-DD.
        return $value;
    }

    // INT
    private function validatePositiveIntPath(mixed $value): int
    {
        // Проверяем id из URL: он приходит строкой, например "/firstmany/5".
        if (!is_string($value) || !preg_match('/^[1-9][0-9]*$/', $value)) {
            // Регулярка разрешает только положительное целое число без нуля в начале.
            Response::json(['error' => 'Bad data'], 400);
        }

        // Преобразуем строковый id в integer.
        return (int) $value;
    }
}
