<?php

namespace App\View\Html;

use Symfony\Component\HtmlSanitizer\HtmlSanitizer;

class Sanitizer
{
    public function __construct(private HtmlSanitizer $sanitizer) {}

    /**
     * Sanitize HTML for a specific element context.
     *
     * @param  string  $element  The element context (e.g., 'body', 'div')
     * @param  string  $input  The HTML input to sanitize
     * @return string
     */
    public function sanitizeFor(string $element, string $input): string
    {
        return $this->sanitizer->sanitizeFor($element, $input);
    }

    /**
     * Sanitize HTML content.
     *
     * @param  string  $input  The HTML input to sanitize
     * @return string
     */
    public function sanitize(string $input): string
    {
        return $this->sanitizer->sanitize($input);
    }
}
