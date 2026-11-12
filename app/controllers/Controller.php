<?php
require_once __DIR__ . '/../helpers.php';
class Controller
{
    protected function success(string $message, string $path): void
    {
        flash($message);
        redirect($path);
    }
}
