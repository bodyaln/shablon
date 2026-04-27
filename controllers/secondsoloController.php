<?php

declare(strict_types=1);

final class SecondsoloController
{
    public function __construct(private Secondsolo $secondsoloModel, private Firstsolo $firstsoloModel) {}

    // GET /secondmany
    public function getAll(): void
    {
        Response::json($this->secondsoloModel->getAll());
    }

    // GET /secondmany/{id}
    public function getOne(array $params): void
    {
        $id = $this->validatePositiveIntPath($params['id'] ?? null);
        $secondsolo = $this->secondsoloModel->getOne($id);

        if ($secondsolo === null) {
            Response::json(['error' => 'Secondsolo not found'], 404);
        }

        Response::json($secondsolo);
    }

    // GET /secondmany/types
    public function types(): void
    {
        Response::json($this->secondsoloModel->distinctTypes());
    }

    // POST /secondmany
    public function post(): void
    {
        $data = $this->readJsonBody();

        $type = $this->validateVarcharType($data['type'] ?? null);
        $firstsoloId = $this->validatePositiveIntBody($data['firstsolo_id'] ?? null);

        if (!$this->firstsoloModel->exists($firstsoloId)) {
            Response::json(['error' => 'Firstsolo not found'], 404);
        }

        if ($this->secondsoloModel->existsByTypeAndFirstsoloId($type, $firstsoloId)) {
            Response::json(['error' => 'Secondsolo already exists'], 409);
        }

        $secondsolo = $this->secondsoloModel->create($type, $firstsoloId);
        Response::json($secondsolo, 201);
    }

    // PUT /secondmany/{id}
    public function put(array $params): void
    {
        $id = $this->validatePositiveIntPath($params['id'] ?? null);
        $data = $this->readJsonBody();

        $type = $this->validateVarcharType($data['type'] ?? null);
        $firstsoloId = $this->validatePositiveIntBody($data['firstsolo_id'] ?? null);

        if ($this->secondsoloModel->getOne($id) === null) {
            Response::json(['error' => 'Secondsolo not found'], 404);
        }

        if (!$this->firstsoloModel->exists($firstsoloId)) {
            Response::json(['error' => 'Firstsolo not found'], 404);
        }

        if ($this->secondsoloModel->existsByTypeAndFirstsoloId($type, $firstsoloId, $id)) {
            Response::json(['error' => 'Secondsolo already exists'], 409);
        }

        $secondsolo = $this->secondsoloModel->update($id, $type, $firstsoloId);
        Response::json($secondsolo);
    }

    // PATCH /secondmany/{id}
    public function patch(array $params): void
    {
        $id = $this->validatePositiveIntPath($params['id'] ?? null);
        $data = $this->readJsonBody();
        $secondsolo = $this->secondsoloModel->getOne($id);

        if ($secondsolo === null) {
            Response::json(['error' => 'Secondsolo not found'], 404);
        }

        if (!array_key_exists('type', $data) && !array_key_exists('firstsolo_id', $data)) {
            Response::json(['error' => 'Bad data'], 400);
        }

        $type = $secondsolo['type'];
        $firstsoloId = $secondsolo['firstsolo_id'];

        if (array_key_exists('type', $data)) {
            $type = $this->validateVarcharType($data['type']);
        }

        if (array_key_exists('firstsolo_id', $data)) {
            $firstsoloId = $this->validatePositiveIntBody($data['firstsolo_id']);
        }

        if (!$this->firstsoloModel->exists($firstsoloId)) {
            Response::json(['error' => 'Firstsolo not found'], 404);
        }

        if ($this->secondsoloModel->existsByTypeAndFirstsoloId($type, $firstsoloId, $id)) {
            Response::json(['error' => 'Secondsolo already exists'], 409);
        }

        $secondsolo = $this->secondsoloModel->update($id, $type, $firstsoloId);
        Response::json($secondsolo);
    }

    // DELETE /secondmany/{id}
    public function delete(array $params): void
    {
        $id = $this->validatePositiveIntPath($params['id'] ?? null);

        if (!$this->secondsoloModel->delete($id)) {
            Response::json(['error' => 'Secondsolo not found'], 404);
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
    private function validateVarcharType(mixed $value): string
    {
        // Проверяем, что значение пришло строкой, потому что type хранится как VARCHAR.
        if (!is_string($value)) {
            // Если это не строка, JSON понятен, но поле не прошло валидацию, поэтому 422 Unprocessable Entity.
            Response::json(['error' => 'Validation failed for type'], 422);
        }

        // Убираем лишние пробелы слева и справа.
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
            Response::json(['error' => 'Validation failed for type'], 422);
        }

        // Возвращаем уже очищенную и проверенную строку.
        return $value;
    }

    // INT
    private function validatePositiveIntBody(mixed $value): int
    {
        // Проверяем id из JSON body: после json_decode число обычно приходит как int.
        if (!is_int($value) || $value < 1) {
            // Разрешаем только integer больше или равный 1.
            Response::json(['error' => 'Validation failed for firstsolo_id'], 422);
        }

        // Возвращаем проверенный id.
        return $value;
    }

    // INT
    private function validatePositiveIntPath(mixed $value): int
    {
        // Проверяем id из URL: он приходит строкой, например "/secondmany/5".
        if (!is_string($value) || !preg_match('/^[1-9][0-9]*$/', $value)) {
            // Регулярка разрешает только положительное целое число без нуля в начале.
            Response::json(['error' => 'Bad data'], 400);
        }

        // Преобразуем строковый id в integer.
        return (int) $value;
    }
}
