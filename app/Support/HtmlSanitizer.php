<?php

namespace App\Support;

use DOMDocument;
use DOMElement;
use DOMNode;

/**
 * Allow-list sanitizer for rich text written in the Quill editor (event descriptions).
 *
 * Keeps the formatting Quill produces and removes everything else: scripts, event
 * handler attributes, styles, iframes, and links/images with unsafe URLs.
 */
class HtmlSanitizer
{
    /** Tags kept, with the attributes allowed on each. */
    private const ALLOWED = [
        'p' => ['class'], 'br' => [], 'h1' => ['class'], 'h2' => ['class'], 'h3' => ['class'],
        'strong' => [], 'b' => [], 'em' => [], 'i' => [], 'u' => [], 's' => [], 'strike' => [],
        'blockquote' => ['class'], 'pre' => ['class', 'spellcheck'], 'code' => [],
        'ol' => ['class'], 'ul' => ['class'], 'li' => ['class', 'data-list'], 'span' => ['class'],
        'a' => ['href', 'target', 'rel'], 'img' => ['src', 'alt', 'width', 'height'],
    ];

    /** Tags removed together with everything inside them. */
    private const DROP = [
        'script', 'style', 'iframe', 'frame', 'frameset', 'object', 'embed', 'applet', 'svg', 'math',
        'form', 'input', 'textarea', 'button', 'select', 'option', 'template', 'noscript', 'link',
        'meta', 'base', 'title', 'head', 'video', 'audio', 'source', 'canvas',
    ];

    public static function clean(?string $html): string
    {
        if ($html === null || trim($html) === '') {
            return '';
        }

        $doc = new DOMDocument();
        $doc->loadHTML(
            '<?xml encoding="utf-8"?><div id="__root">' . $html . '</div>',
            LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD | LIBXML_NOERROR | LIBXML_NOWARNING | LIBXML_NONET
        );

        $root = $doc->getElementById('__root');
        if (!$root) {
            return e(strip_tags($html));
        }

        self::cleanChildren($root);

        $out = '';
        foreach ($root->childNodes as $child) {
            $out .= $doc->saveHTML($child);
        }

        return $out;
    }

    private static function cleanChildren(DOMNode $node): void
    {
        // Copy first: the loop removes and replaces nodes.
        foreach (iterator_to_array($node->childNodes) as $child) {
            if ($child->nodeType === XML_TEXT_NODE) {
                continue;
            }

            if (!$child instanceof DOMElement) {
                $node->removeChild($child); // comments, CDATA, processing instructions
                continue;
            }

            $tag = strtolower($child->tagName);

            if (in_array($tag, self::DROP, true)) {
                $node->removeChild($child);
                continue;
            }

            self::cleanChildren($child);

            if (!array_key_exists($tag, self::ALLOWED)) {
                // Unknown wrapper (div, font, …): keep its cleaned content, drop the tag.
                while ($child->firstChild) {
                    $node->insertBefore($child->firstChild, $child);
                }
                $node->removeChild($child);
                continue;
            }

            self::cleanAttributes($child, $tag);
        }
    }

    private static function cleanAttributes(DOMElement $el, string $tag): void
    {
        foreach (iterator_to_array($el->attributes) as $attr) {
            $name  = strtolower($attr->name);
            $value = $attr->value;

            $keep = in_array($name, self::ALLOWED[$tag], true) && match ($name) {
                'class'     => (bool) preg_match('/^(ql-[a-z0-9-]+\s*)+$/i', trim($value)),
                'href'      => self::safeHref($value),
                'src'       => self::safeImageSrc($value),
                'target'    => $value === '_blank',
                'data-list' => in_array($value, ['bullet', 'ordered', 'checked', 'unchecked'], true),
                'width', 'height' => ctype_digit($value),
                default     => true,
            };

            if (!$keep) {
                $el->removeAttribute($attr->name);
            }
        }

        if ($tag === 'a') {
            $el->setAttribute('rel', 'noopener noreferrer nofollow');
        }
    }

    private static function normalizeUrl(string $url): string
    {
        // Browsers ignore control characters and whitespace inside schemes ("java\tscript:").
        return strtolower(preg_replace('/[\x00-\x20]+/', '', html_entity_decode($url)));
    }

    private static function safeHref(string $url): bool
    {
        $url = self::normalizeUrl($url);

        return (bool) preg_match('#^(https?://|mailto:|/(?!/)|\#)#', $url);
    }

    private static function safeImageSrc(string $url): bool
    {
        $url = self::normalizeUrl($url);

        return str_starts_with($url, 'https://')
            || str_starts_with($url, 'http://')
            || (bool) preg_match('#^data:image/(png|jpe?g|gif|webp);base64,[a-z0-9+/=]+$#', $url);
    }
}
