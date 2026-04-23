<?php

declare(strict_types=1);

final class Secondsolo
{
    public function __construct(private PDO $pdo)
    {
    }

    public function getAll(): array
    {
        $stmt = $this->pdo->query(
            'SELECT id, type, firstsolo_id FROM secondmany ORDER BY id ASC'
        );

        return $this->mapRowsToSecondmany($stmt->fetchAll());
    }

    public function getOne(int $id): ?array
    {
        $stmt = $this->pdo->prepare(
            'SELECT id, type, firstsolo_id FROM secondmany WHERE id = :id'
        );
        $stmt->execute(['id' => $id]);
        $row = $stmt->fetch();

        if ($row === false) {
            return null;
        }

        return $this->mapRowToSecondsolo($row);
    }

    public function distinctTypes(): array
    {
        $stmt = $this->pdo->query('SELECT DISTINCT type FROM secondmany ORDER BY type ASC');
        $types = [];

        foreach ($stmt->fetchAll() as $row) {
            $types[] = $row['type'];
        }

        return $types;
    }

    public function create(string $type, int $firstsoloId): array
    {
        $stmt = $this->pdo->prepare(
            'INSERT INTO secondmany (type, firstsolo_id) VALUES (:type, :firstsolo_id)'
        );
        $stmt->execute([
            'type' => $type,
            'firstsolo_id' => $firstsoloId,
        ]);

        return [
            'id' => (int) $this->pdo->lastInsertId(),
            'type' => $type,
            'firstsolo_id' => $firstsoloId,
        ];
    }

    public function update(int $id, string $type, int $firstsoloId): ?array
    {
        $stmt = $this->pdo->prepare(
            'UPDATE secondmany SET type = :type, firstsolo_id = :firstsolo_id WHERE id = :id'
        );
        $stmt->execute([
            'id' => $id,
            'type' => $type,
            'firstsolo_id' => $firstsoloId,
        ]);

        return $this->getOne($id);
    }

    public function delete(int $id): bool
    {
        $stmt = $this->pdo->prepare('DELETE FROM secondmany WHERE id = :id');
        $stmt->execute(['id' => $id]);

        return $stmt->rowCount() > 0;
    }

    public function existsByTypeAndFirstsoloId(string $type, int $firstsoloId, ?int $excludeId = null): bool
    {
        $sql = 'SELECT 1 FROM secondmany WHERE type = :type AND firstsolo_id = :firstsolo_id';
        $params = [
            'type' => $type,
            'firstsolo_id' => $firstsoloId,
        ];

        if ($excludeId !== null) {
            $sql .= ' AND id <> :exclude_id';
            $params['exclude_id'] = $excludeId;
        }

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);

        return (bool) $stmt->fetchColumn();
    }

    private function mapRowsToSecondmany(array $rows): array
    {
        $secondmany = [];

        foreach ($rows as $row) {
            $secondmany[] = $this->mapRowToSecondsolo($row);
        }

        return $secondmany;
    }

    private function mapRowToSecondsolo(array $row): array
    {
        return [
            'id' => (int) $row['id'],
            'type' => $row['type'],
            'firstsolo_id' => (int) $row['firstsolo_id'],
        ];
    }
}
