<?php
require_once __DIR__ . '/../helpers.php';
class Controller
{
    protected function success(string $message, string $path): void
    {
        flash($message);
        redirect($path);
    }
    // Compatibility for existing module views; new forms return error arrays.
    protected function results_checker($message, $status): string
    {
        $class = $status === 1 ? 'success' : 'danger';
        return '<p class="text-' . $class . '">' . e($message) . '</p>';
    }
}
