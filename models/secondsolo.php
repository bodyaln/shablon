<?php

declare(strict_types=1);

final class Secondsolo
{
    public function __construct(private PDO $pdo) {}

    public function getAll(): array
    {
        $stmt = $this->pdo->query(
            'SELECT s.id, s.type, s.firstsolo_id
             FROM secondmany s
             ORDER BY s.id ASC'
        );

        $secondmany = $stmt->fetchAll();

        foreach ($secondmany as &$secondsolo) {
            $secondsolo['id'] = (int) $secondsolo['id'];
            $secondsolo['firstsolo_id'] = (int) $secondsolo['firstsolo_id'];
        }
        unset($secondsolo);

        return $secondmany;
    }

    public function getOne(int $id): ?array
    {
        $stmt = $this->pdo->prepare(
            'SELECT s.id, s.type, s.firstsolo_id
             FROM secondmany s
             WHERE s.id = :id'
        );
        $stmt->execute(['id' => $id]);
        $secondsolo = $stmt->fetch();

        if ($secondsolo === false) {
            return null;
        }

        $secondsolo['id'] = (int) $secondsolo['id'];
        $secondsolo['firstsolo_id'] = (int) $secondsolo['firstsolo_id'];

        return $secondsolo;
    }

    public function distinctTypes(): array
    {
        $stmt = $this->pdo->query(
            'SELECT DISTINCT s.type
             FROM secondmany s
             ORDER BY s.type ASC'
        );

        $rows = $stmt->fetchAll();
        $types = [];

        foreach ($rows as $row) {
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
        $stmt = $this->pdo->prepare(
            'SELECT 1
             FROM secondmany s
             WHERE s.type = :type
               AND s.firstsolo_id = :firstsolo_id
               AND (:exclude_id IS NULL OR s.id <> :exclude_id)'
        );

        $stmt->execute([
            'type' => $type,
            'firstsolo_id' => $firstsoloId,
            'exclude_id' => $excludeId,
        ]);

        return (bool) $stmt->fetchColumn();
    }
}
