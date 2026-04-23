<?php

declare(strict_types=1);

final class SecondsoloController
{
    public function __construct(
        private Secondsolo $secondsoloModel,
        private Firstsolo $firstsoloModel
    ) {}

    // GET /secondmany
    // 200 OK - список secondmany успешно возвращен.
    // 500 Internal Server Error - сервер не смог получить данные из базы.
    public function getAll(): void
    {
        Response::json($this->secondsoloModel->getAll());
    }

    // GET /secondmany/{id}
    // 200 OK - одна запись успешно найдена.
    // 400 Bad Request - id в URL имеет неверный формат.
    // 404 Not Found - запись с таким id не существует.
    // 500 Internal Server Error - серверная ошибка во время чтения.
    public function getOne(array $params): void
    {
        $id = $this->validatePathId($params['id'] ?? null);
        $secondsolo = $this->secondsoloModel->getOne($id);

        if ($secondsolo === null) {
            Response::json(['error' => 'Secondsolo not found'], 404);
        }

        Response::json($secondsolo);
    }

    // GET /secondmany/types
    // 200 OK - список уникальных типов успешно возвращен.
    // 500 Internal Server Error - ошибка сервера при чтении справочных данных.
    public function types(): void
    {
        Response::json($this->secondsoloModel->distinctTypes());
    }

    // POST /secondmany
    // 201 Created - новая запись secondsolo успешно создана.
    // 400 Bad Request - тело запроса не является валидным JSON.
    // 404 Not Found - связанная запись firstsolo_id не найдена.
    // 409 Conflict - такая же комбинация type + firstsolo_id уже существует.
    // 422 Unprocessable Entity - поля JSON не прошли валидацию.
    // 500 Internal Server Error - серверная ошибка при сохранении.
    public function post(): void
    {
        $data = $this->readJsonBody();

        $type = $this->validateType($data['type'] ?? null);
        $firstsoloId = $this->validateBodyId($data['firstsolo_id'] ?? null);

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
    // 200 OK - запись успешно обновлена.
    // 400 Bad Request - id в URL или тело запроса имеют неверный формат.
    // 404 Not Found - secondsolo или связанный firstsolo не найден.
    // 409 Conflict - после обновления образовался бы дубликат по type + firstsolo_id.
    // 422 Unprocessable Entity - поля JSON не прошли проверку значений.
    // 500 Internal Server Error - серверная ошибка при обновлении.
    public function put(array $params): void
    {
        $id = $this->validatePathId($params['id'] ?? null);
        $data = $this->readJsonBody();

        $type = $this->validateType($data['type'] ?? null);
        $firstsoloId = $this->validateBodyId($data['firstsolo_id'] ?? null);

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

    // DELETE /secondmany/{id}
    // 204 No Content - запись успешно удалена, тело ответа пустое.
    // 400 Bad Request - id в URL неправильный.
    // 404 Not Found - запись для удаления не найдена.
    // 500 Internal Server Error - серверная ошибка при удалении.
    public function delete(array $params): void
    {
        $id = $this->validatePathId($params['id'] ?? null);

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
            // 400 Bad Request - JSON сломан или тело запроса не соответствует ожидаемой структуре.
            Response::json(['error' => 'Bad data'], 400);
        }

        return $data;
    }

    private function validateType(mixed $value): string
    {
        // Поле type должно прийти строкой, потому что в БД это VARCHAR(100).
        if (!is_string($value)) {
            // 422 Unprocessable Entity - тело запроса понято, но значение поля неверное.
            Response::json(['error' => 'Validation failed for type'], 422);
        }

        // Убираем лишние пробелы по краям.
        $value = trim($value);

        if (
            $value === '' ||
            mb_strlen($value) > 100 ||
            !preg_match("/^[\\p{L}\\p{N}][\\p{L}\\p{N}\\s.'-]{0,99}$/u", $value)
        ) {
            // Проверяем непустую строку, длину до 100 символов и набор разрешенных символов.
            Response::json(['error' => 'Validation failed for type'], 422);
        }

        return $value;
    }

    private function validateBodyId(mixed $value): int
    {
        // firstsolo_id из JSON должен быть integer и должен быть больше 0.
        if (!is_int($value) || $value < 1) {
            // 422 Unprocessable Entity - поле есть, но значение не подходит.
            Response::json(['error' => 'Validation failed for firstsolo_id'], 422);
        }

        return $value;
    }

    private function validatePathId(mixed $value): int
    {
        // id в URL приходит строкой, поэтому проверяем его регуляркой.
        if (!is_string($value) || !preg_match('/^[1-9][0-9]*$/', $value)) {
            // 400 Bad Request - неверный формат пути, например /abc вместо /12.
            Response::json(['error' => 'Bad data'], 400);
        }

        return (int) $value;
    }
}
