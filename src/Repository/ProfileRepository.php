<?php

namespace Danish\IkspiTubanRegistration\Repository;

use Danish\IkspiTubanRegistration\Domain\Profile;
use PDO;

class ProfileRepository
{
    private PDO $pdo;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    public function save(Profile $profile): Profile
    {
        $stmt = $this->pdo->prepare("INSERT INTO profiles(user_id, name, avatar)
        VALUES(?, ?, ?)");
        $stmt->execute([
            $profile->userId,
            $profile->name,
            $profile->avatar
        ]);

        $profile->id = $this->pdo->lastInsertId();
        return $profile;
    }

    public function update(Profile $profile): bool
    {
        $stmt = $this->pdo->prepare("UPDATE profiles 
        SET name = ?, avatar = ? WHERE user_id = ?");
        $stmt->execute([
            $profile->name,
            $profile->avatar,
            $profile->userId
        ]);

        return $stmt->rowCount() > 0;
    }

    public function findByUserId(int $userId): ?Profile
    {
        $stmt = $this->pdo->prepare("SELECT * FROM profiles 
         WHERE user_id = ?");
        $stmt->execute([
            $userId
        ]);

        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($row == false) {
            return null;
        }

        $response = new Profile();
        $response->id = $row['id'];
        $response->userId = $row['user_id'];
        $response->name = $row['name'];
        $response->avatar = $row['avatar'];

        return $response;

    }

    // FOR UNIT TEST

    public function deleteAll()
    {
        $this->pdo->exec("DELETE FROM profiles");
    }

}
