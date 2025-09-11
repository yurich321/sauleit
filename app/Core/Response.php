<?php

namespace App\Core;

class Response
{

    private int $status = 200;

    public function status(int $code): self
    {
        $this->status = $code;
        http_response_code($code);
        return $this;
    }

    public function text(string $s): void
    {
        echo $s;
    }

    public function json($data): never
    {
        http_response_code($this->status);
        header('Content-Type: application/json; charset=utf-8', true, $this->status);
        echo json_encode($data);
        exit();
    }

    public function redirect(string $to): never
    {
        http_response_code($this->status ?: 302);
        header('Location: ' . $to);
        exit;
    }

    public function view(string $view, array $data = []): void
    {
        http_response_code($this->status);
        \App\Services\View::render($view, $data);
    }
}