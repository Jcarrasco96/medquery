<?php

namespace app\utils;

final class MyEasyAjax
{

    private array $payload = [];

    public static function instance(): self
    {
        return new self();
    }

    public function contentReplace(array $data): self
    {
        $this->payload[] = [
            'content_replace' => $data,
        ];

        return $this;
    }

    public function contentRemove(string $id): self
    {
        $this->payload[] = [
            'content_remove' => $id,
        ];

        return $this;
    }

    public function redirect(string $url, bool $ajax = false, bool $processResponse = false): self
    {
        $this->payload[] = [
            'redirect' => [
                'url' => $url,
                'ajax' => $ajax,
                'processResponse' => $processResponse,
            ]
        ];

        return $this;
    }

    public function reloadPage(): self
    {
        $this->payload[] = [
            'reload_page' => true,
        ];

        return $this;
    }

    public function modal(string $title, string $content = '', string $size = 'modal-lg'): self
    {
        $this->payload[] = [
            'modal' => [
                'title' => $title,
                'content' => $content,
                'size' => $size,
            ]
        ];

        return $this;
    }

    public function modalClose(): self
    {
        $this->payload[] = [
            'modal_close' => true
        ];

        return $this;
    }

    private function notify(string $message, string $type, string $icon): void
    {
        $this->payload[] = [
            'notify' => [
                'message' => $message,
                'type' => $type ,
                'icon' => $icon,
            ]
        ];

    }

    public function notifySuccess(string $message, string $icon = 'bi-check-lg'): self
    {
        $this->notify($message, 'success', $icon);

        return $this;
    }

    public function notifyDanger(string $message, string $icon = 'bi-x-lg'): self
    {
        $this->notify($message, 'danger', $icon);

        return $this;
    }

    public function notifyInfo(string $message, string $icon = 'bi-info-circle'): self
    {
        $this->notify($message, 'info', $icon);

        return $this;
    }

    public function notifyWarning(string $message, string $icon = 'bi-exclamation-triangle'): self
    {
        $this->notify($message, 'warning', $icon);

        return $this;
    }

    public function notifyPrimary(string $message, string $icon = 'bi-exclamation-triangle'): self
    {
        $this->notify($message, 'primary', $icon);

        return $this;
    }

    public function payload(): array
    {
        return $this->payload;
    }

    public static function isSubmit(): bool
    {
        return isset($_POST['jax-submit']);
    }

    public function formReset(string ...$formId): self
    {
        $this->payload[] = [
            'form_reset' => $formId
        ];

        return $this;
    }

    public function openWindow(string $url): self
    {
        $this->payload[] = [
            'open_window' => $url
        ];

        return $this;
    }

}