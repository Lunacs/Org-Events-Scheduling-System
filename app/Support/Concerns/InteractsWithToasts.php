<?php

namespace App\Support\Concerns;

/**
 * Drop-in replacement for MaryUI's Toast trait.
 *
 * Exposes the same method signatures MaryUI's trait provided
 * (`toast()`, `success()`, `error()`, `info()`, `warning()`) so migrating a
 * Livewire component only requires swapping the `use` import. Instead of
 * MaryUI's `$this->js('toast(...)')` call, each method dispatches a
 * `toast-show` browser event consumed by the `<x-ui.toast>` Alpine host.
 *
 * The $redirectTo navigate-redirect behavior is preserved.
 */
trait InteractsWithToasts
{
    /**
     * Default toast position applied when a caller does not specify one.
     */
    protected string $defaultToastPosition = 'toast-top toast-end';

    /**
     * Dispatch a toast of an explicit type.
     */
    public function toast(
        string $type,
        string $title,
        ?string $description = null,
        ?string $position = null,
        string $icon = 'o-information-circle',
        string $css = 'alert-info',
        int $timeout = 3000,
        ?string $redirectTo = null,
        bool $noProgress = false,
        ?string $progressClass = null,
    ) {
        $this->dispatch('toast-show', ...[
            'type' => $type,
            'message' => $title,
            'description' => $description,
            'position' => $position ?? $this->defaultToastPosition,
            'icon' => $icon,
            'css' => $css,
            'timeout' => $timeout,
            'noProgress' => $noProgress,
            'progressClass' => $progressClass,
        ]);

        if ($redirectTo !== null) {
            return $this->redirect($redirectTo, navigate: true);
        }
    }

    /**
     * Dispatch a success toast.
     */
    public function success(
        string $title,
        ?string $description = null,
        ?string $position = null,
        string $icon = 'o-check-circle',
        string $css = 'alert-success',
        int $timeout = 3000,
        ?string $redirectTo = null,
        bool $noProgress = false,
        ?string $progressClass = null,
    ) {
        return $this->toast('success', $title, $description, $position, $icon, $css, $timeout, $redirectTo, $noProgress, $progressClass);
    }

    /**
     * Dispatch a warning toast.
     */
    public function warning(
        string $title,
        ?string $description = null,
        ?string $position = null,
        string $icon = 'o-exclamation-triangle',
        string $css = 'alert-warning',
        int $timeout = 3000,
        ?string $redirectTo = null,
        bool $noProgress = false,
        ?string $progressClass = null,
    ) {
        return $this->toast('warning', $title, $description, $position, $icon, $css, $timeout, $redirectTo, $noProgress, $progressClass);
    }

    /**
     * Dispatch an error toast.
     */
    public function error(
        string $title,
        ?string $description = null,
        ?string $position = null,
        string $icon = 'o-x-circle',
        string $css = 'alert-error',
        int $timeout = 3000,
        ?string $redirectTo = null,
        bool $noProgress = false,
        ?string $progressClass = null,
    ) {
        return $this->toast('error', $title, $description, $position, $icon, $css, $timeout, $redirectTo, $noProgress, $progressClass);
    }

    /**
     * Dispatch an info toast.
     */
    public function info(
        string $title,
        ?string $description = null,
        ?string $position = null,
        string $icon = 'o-information-circle',
        string $css = 'alert-info',
        int $timeout = 3000,
        ?string $redirectTo = null,
        bool $noProgress = false,
        ?string $progressClass = null,
    ) {
        return $this->toast('info', $title, $description, $position, $icon, $css, $timeout, $redirectTo, $noProgress, $progressClass);
    }
}
