<?php
/** Base Controller - view rendering + JSON responses */
abstract class Controller
{
    protected function view(string $view, array $data = [], string $layout = 'main')
    {
        extract($data);
        $viewFile = __DIR__ . "/../views/{$view}.php";
        if (!file_exists($viewFile)) {
            throw new Exception("View not found: $view");
        }
        ob_start();
        require $viewFile;
        $content = ob_get_clean();

        if ($layout === false) {
            echo $content;
            return;
        }

        $layoutFile = __DIR__ . "/../views/layouts/{$layout}.php";
        require $layoutFile;
    }

    protected function json(array $data, int $status = 200): void
    {
        http_response_code($status);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($data, JSON_UNESCAPED_UNICODE);
        exit;
    }

    protected function redirect(string $path): void
    {
        header('Location: ' . BASE_PATH . $path);
        exit;
    }

    protected function input(string $key, $default = null)
    {
        $value = $_POST[$key] ?? $_GET[$key] ?? $default;
        return is_string($value) ? trim($value) : $value;
    }

    protected function verifyCsrfOrFail(): void
    {
        if (!CSRF::verifyRequest()) {
            Session::flash('error', 'انتهت صلاحية الجلسة، الرجاء إعادة المحاولة');
            header('Location: ' . ($_SERVER['HTTP_REFERER'] ?? BASE_PATH . '/'));
            exit;
        }
    }
}
