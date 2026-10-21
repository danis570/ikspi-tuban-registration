<?php

namespace Danish\IkspiTubanRegistration\Repository;

use Danish\IkspiTubanRegistration\Domain\Session;
use PDO;

class SessionRepository
{
    private PDO $pdo;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    public function save(Session $session): Session
    {
        $stmt = $this->pdo->prepare("INSERT INTO sessions(id, user_id)
        VALUES(?, ?)");
        $stmt->execute([
            $session->id,
            $session->userId
        ]);

        return $session;
    }

    public function deleteByUserId(int $userId): bool
    {
        $stmt = $this->pdo->prepare("DELETE FROM sessions WHERE user_id = ?");
        return $stmt->execute([$userId]);
    }

    public function deleteById(string $id): bool
    {
        $stmt = $this->pdo->prepare("DELETE FROM sessions WHERE id = ?");
        return $stmt->execute([$id]);
    }

    function findByUserId(int $userId): ?Session
    {
        return $this->findBy('user_id', $userId);
    }

    // FOR UNIT TEST

    function findById(string $id): ?Session
    {
        return $this->findBy('id', $id);
    }

    function deleteAll()
    {
        $this->pdo->exec("DELETE FROM sessions");
    }

    // HELPERS

    private function findBy(string $column, mixed $value): ?Session
    {
        $stmt = $this->pdo->prepare("SELECT * FROM sessions WHERE $column = ?");
        $stmt->execute([$value]);

        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($row === false) {
            return null;
        }

        return $this->mapToEntity($row);
    }

    private function mapToEntity(array $row): Session
    {
        $session = new Session();
        $session->id = $row['id'];
        $session->userId = $row['user_id'];
        return $session;
    }
}