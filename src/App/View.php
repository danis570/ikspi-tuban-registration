<?php

namespace Danish\IkspiTubanRegistration\App;

class View
{
    public static ?string $redirectPath = null;
    public static $globalData = [];

    public static function share(string $key, mixed $value): void
    {
        self::$globalData[$key] = $value;
    }

    public static function render(string $layout, string $path, array $model): string
    {
        $data = array_merge(self::$globalData, $model);
        extract($data);
        ob_start();

        require __DIR__ . '/../View/' . $layout . '/header.php';
        require __DIR__ . '/../View/' . "$layout/" . $path . '.php';
        require __DIR__ . '/../View/' . $layout . '/footer.php';

        return ob_get_clean() ?: '';
    }

    public static function redirect(string $path)
    {
        if (getenv('APP_ENV') === 'test') {
            self::$redirectPath = $path;
            return;
        }

        header("Location: $path");
        exit();
    }

    public static function flashMessage(string $message, string $type = 'success'): void
    {
        $_SESSION['flash'] = [
            'message' => $message,
            'type' => $type
        ];
    }

}
