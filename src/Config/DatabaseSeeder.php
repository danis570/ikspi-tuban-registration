<?php

namespace Danish\IkspiTubanRegistration\Config;

use PDO;

class DatabaseSeeder
{
    private PDO $pdo;

    public function __construct()
    {
        $this->pdo = Database::getConnection();
    }

    public function runDummyUser()
    {
        $stmt = $this->pdo->query("SELECT COUNT(*) FROM users");
        $count = $stmt->fetchColumn();

        if ($count == 0) {
            $stmt = $this->pdo->prepare("INSERT INTO users
            (email, password, role) VALUES (?, ?, ?)");

            $adminPass = password_hash('admin', PASSWORD_BCRYPT);
            $stmt->execute(['admin@example.com', $adminPass, 'admin']);

            $userPass = password_hash('user', PASSWORD_BCRYPT);
            $stmt->execute(['user@example.com', $userPass, 'user']);

            if (getenv('APP_ENV') !== 'test') {
                echo 'Success ad dummy users in database </br> 
                ROLE USER username: user@example.com password: user  </br> 
                ROLE ADMIN username: admin@example.com password: admin </br>
                <a href="/">Back to home</a>';
            }
        }
    }
}