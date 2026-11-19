<?php
require_once __DIR__ . '/../helpers.php';
class Controller
{
    public function recordId($value): int
    {
        $id = is_scalar($value) ? filter_var($value, FILTER_VALIDATE_INT) : false;
        if (!$id || $id < 1) {
            http_response_code(400);
            exit('Invalid ID.');
        }
        return $id;
    }

    protected function success(string $message, string $path): void
    {
        flash($message);
        redirect($path);
    }
}
