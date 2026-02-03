<?php

use App\View\Html\Sanitizer;
use Illuminate\Support\HtmlString;

if (! function_exists('h')) {
    /**
     * Sanitize HTML content and return a safe HtmlString.
     *
     * @param  string|null  $html  The HTML content to sanitize
     * @param  string  $element  The element context for sanitization (default: 'body')
     * @return HtmlString
     */
    function h(?string $html, string $element = 'body'): HtmlString
    {
        if ($html === null || $html === '') {
            return new HtmlString('');
        }

        $sanitizer = resolve(Sanitizer::class);

        return new HtmlString($sanitizer->sanitizeFor($element, $html));
    }
}
