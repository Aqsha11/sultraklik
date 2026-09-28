<?php

namespace App\Support;

use DOMComment;
use DOMDocument;
use DOMElement;
use DOMNode;
use DOMProcessingInstruction;
use DOMText;

/**
 * Membersihkan HTML hasil input editor (CKEditor / textarea admin) memakai
 * allowlist tag dan atribut. Dipakai sebelum disimpan ke database dan lagi
 * saat render, sehingga script, event handler, dan URL berbahaya tidak pernah
 * sampai ke browser pengunjung.
 */
class HtmlSanitizer
{
    /** @var array<string, list<string>> */
    private const ALLOWED = [
        'a' => ['href', 'title', 'target', 'rel'],
        'abbr' => [],
        'b' => [],
        'blockquote' => ['cite'],
        'br' => [],
        'caption' => [],
        'cite' => [],
        'code' => [],
        'col' => ['span'],
        'colgroup' => ['span'],
        'dd' => [],
        'div' => [],
        'dl' => [],
        'dt' => [],
        'em' => [],
        'figcaption' => [],
        'figure' => [],
        'h1' => [], 'h2' => [], 'h3' => [], 'h4' => [], 'h5' => [], 'h6' => [],
        'hr' => [],
        'i' => [],
        'img' => ['src', 'alt', 'title', 'width', 'height'],
        'ins' => [],
        'kbd' => [],
        'li' => [],
        'mark' => [],
        'ol' => ['start', 'reversed'],
        'p' => [],
        'picture' => [],
        'pre' => [],
        'q' => ['cite'],
        's' => [],
        'samp' => [],
        'section' => [],
        'small' => [],
        'source' => ['src', 'type', 'media'],
        'span' => [],
        'strong' => [],
        'sub' => [],
        'sup' => [],
        'table' => [],
        'tbody' => [],
        'td' => ['colspan', 'rowspan'],
        'tfoot' => [],
        'th' => ['colspan', 'rowspan', 'scope'],
        'thead' => [],
        'time' => ['datetime'],
        'tr' => [],
        'u' => [],
        'ul' => [],
        'var' => [],
    ];

    /** @var list<string> Tag yang dibuang bersama seluruh isinya. */
    private const DROPPED = [
        'applet', 'audio', 'base', 'basefont', 'body', 'button', 'canvas', 'embed',
        'form', 'frame', 'frameset', 'head', 'html', 'iframe', 'input', 'keygen',
        'link', 'map', 'math', 'meta', 'noembed', 'noframes', 'noscript', 'object',
        'option', 'param', 'plaintext', 'script', 'select', 'slot', 'svg', 'template',
        'textarea', 'title', 'track', 'video', 'xmp',
    ];

    /** @var list<string> Atribut URL yang wajib lolos uji skema. */
    private const URL_ATTRIBUTES = ['href', 'src', 'cite'];

    public static function clean(?string $html): string
    {
        if ($html === null || trim($html) === '') {
            return '';
        }

        $document = self::parse($html);

        if ($document === null) {
            return trim(e(strip_tags($html)));
        }

        $root = $document->documentElement;

        if (! $root instanceof DOMElement) {
            return trim(e(strip_tags($html)));
        }

        self::filterChildren($root);

        $output = '';

        foreach (iterator_to_array($root->childNodes) as $child) {
            $output .= $document->saveHTML($child);
        }

        return trim($output);
    }

    private static function parse(string $html): ?DOMDocument
    {
        $document = new DOMDocument('1.0', 'UTF-8');
        $encoded = mb_encode_numericentity($html, [0x80, 0x10FFFF, 0, 0x1FFFFF], 'UTF-8');
        $previous = libxml_use_internal_errors(true);

        $loaded = $document->loadHTML(
            '<div>'.$encoded.'</div>',
            LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD | LIBXML_NONET | LIBXML_NOERROR | LIBXML_NOWARNING
        );

        libxml_clear_errors();
        libxml_use_internal_errors($previous);

        if ($loaded === false) {
            return null;
        }

        $divs = $document->getElementsByTagName('div');
        $wrapper = $divs->length > 0 ? $divs->item(0) : null;

        if (! $wrapper instanceof DOMElement) {
            return null;
        }

        return $document;
    }

    private static function filterChildren(DOMNode $parent): void
    {
        foreach (iterator_to_array($parent->childNodes) as $child) {
            if ($child instanceof DOMText) {
                continue;
            }

            if ($child instanceof DOMComment || $child instanceof DOMProcessingInstruction) {
                $parent->removeChild($child);

                continue;
            }

            if (! $child instanceof DOMElement) {
                $parent->removeChild($child);

                continue;
            }

            $tag = strtolower($child->nodeName);

            if (in_array($tag, self::DROPPED, true)) {
                $parent->removeChild($child);

                continue;
            }

            if (! array_key_exists($tag, self::ALLOWED)) {
                self::filterChildren($child);

                while ($child->firstChild instanceof DOMNode) {
                    $parent->insertBefore($child->firstChild, $child);
                }

                $parent->removeChild($child);

                continue;
            }

            self::filterAttributes($child, self::ALLOWED[$tag]);
            self::filterChildren($child);
        }
    }

    /** @param list<string> $allowed */
    private static function filterAttributes(DOMElement $element, array $allowed): void
    {
        $attributes = $element->attributes === null
            ? []
            : iterator_to_array($element->attributes);

        foreach ($attributes as $attribute) {
            if (! $attribute instanceof \DOMAttr) {
                continue;
            }

            $name = strtolower($attribute->nodeName);

            if (str_contains($name, ':') || ! in_array($name, $allowed, true)) {
                $element->removeAttributeNode($attribute);

                continue;
            }

            if (in_array($name, self::URL_ATTRIBUTES, true) && ! self::isSafeUrl($attribute->nodeValue)) {
                $element->removeAttributeNode($attribute);

                continue;
            }

            if ($name === 'target' && ! in_array(strtolower(trim($attribute->nodeValue)), ['_blank', '_self'], true)) {
                $element->removeAttributeNode($attribute);
            }
        }

        if ($element->getAttribute('target') !== '') {
            $element->setAttribute('rel', 'noopener noreferrer nofollow');
        }
    }

    private static function isSafeUrl(?string $url): bool
    {
        if ($url === null) {
            return false;
        }

        $url = trim(preg_replace('/[\x00-\x20\x7F]/u', '', $url) ?? '');

        if ($url === '') {
            return false;
        }

        if (preg_match('#^(https?:|mailto:|tel:)#i', $url) === 1) {
            return true;
        }

        foreach (['#', '/', './', '../'] as $prefix) {
            if (str_starts_with($url, $prefix)) {
                return true;
            }
        }

        return preg_match('#^[a-z][a-z0-9+.\-]*:#i', $url) !== 1;
    }
}
