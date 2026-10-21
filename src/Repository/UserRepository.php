<?php

namespace Danish\IkspiTubanRegistration\Repository;

use Danish\IkspiTubanRegistration\Domain\User;
use Danish\IkspiTubanRegistration\Domain\UserRole;
use PDO;

class UserRepository
{
    private PDO $pdo;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    function save(User $user): User
    {
        $stmt = $this->pdo->prepare("INSERT INTO users(email, password, role)
        VALUES(?, ?, ?)");
        $stmt->execute([
            $user->email,
            $user->password,
            $user->role->value
        ]);

        $user->id = $this->pdo->lastInsertId();
        return $user;
    }

    function updateEmailById(int $id, string $email): int
    {
        $stmt = $this->pdo->prepare("UPDATE users SET email = ?
        WHERE id = ?");
        $result = $stmt->execute([$email, $id]);

        if ($result) {
            return $stmt->rowCount();
        }

        return $result;
    }

    function updatePasswordById(int $id, string $password): int
    {
        $stmt = $this->pdo->prepare("UPDATE users SET password = ?
        WHERE id = ?");
        $result = $stmt->execute([$password, $id]);

        if ($result) {
            return $stmt->rowCount();
        }

        return $result;
    }


    function findAll(): array
    {
        $stmt = $this->pdo->query("SELECT * FROM users");
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $users = [];
        foreach ($rows as $row) {
            $users[] = $this->mapToEntity($row);
        }

        return $users;
    }

    function deleteById(int $id): bool
    {
        $stmt = $this->pdo->prepare("DELETE FROM users WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->rowCount() > 0;
    }

    // FOR VALIDATION

    function findByEmail(string $email): ?User
    {
        return $this->findBy('email', $email);
    }

    // FOR UNIT TEST

    function findById(int $id): ?User
    {
        return $this->findBy('id', $id);
    }

    function deleteAll()
    {
        $this->pdo->exec("DELETE FROM users");
    }

    // HELPERS

    private function findBy(string $column, mixed $value): ?User
    {
        $stmt = $this->pdo->prepare("SELECT * FROM users WHERE $column = ?");
        $stmt->execute([$value]);

        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($row === false) {
            return null;
        }

        return $this->mapToEntity($row);
    }

    private function mapToEntity(array $row): User
    {
        $user = new User();
        $user->id = $row['id'];
        $user->email = $row['email'];
        $user->password = $row['password'];
        $user->role = UserRole::from($row['role']);
        return $user;
    }
}