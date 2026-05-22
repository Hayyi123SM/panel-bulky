<?php

namespace App\Support;

class RichTextContent
{
    public static function toHtml(mixed $value, ?string $activeLocale = null): string
    {
        $resolved = self::resolveLocaleValue($value, $activeLocale);

        if (is_string($resolved)) {
            return $resolved;
        }

        if (is_array($resolved)) {
            if (isset($resolved['type']) && $resolved['type'] === 'doc') {
                return self::renderDoc($resolved);
            }

            if (self::isSequentialArray($resolved)) {
                return implode('', array_map(static fn($item) => self::toHtml($item, $activeLocale), $resolved));
            }
        }

        return '';
    }

    public static function normalizeForStorage(mixed $value, ?string $activeLocale = null): mixed
    {
        if (!is_array($value)) {
            return self::toHtml($value, $activeLocale);
        }

        if (isset($value['type']) && $value['type'] === 'doc') {
            return self::renderDoc($value);
        }

        if (self::looksLikeTranslationsMap($value)) {
            $targetLocale = self::resolveTargetLocaleForWrite($value, $activeLocale);

            if ($targetLocale !== null && array_key_exists($targetLocale, $value)) {
                $value[$targetLocale] = self::toHtml($value[$targetLocale], $targetLocale);
            }

            return $value;
        }

        return self::toHtml($value, $activeLocale);
    }

    private static function resolveLocaleValue(mixed $value, ?string $activeLocale): mixed
    {
        if (!is_array($value)) {
            return $value;
        }

        if (isset($value['type']) && $value['type'] === 'doc') {
            return $value;
        }

        if (self::looksLikeTranslationsMap($value)) {
            if ($activeLocale && array_key_exists($activeLocale, $value)) {
                return $value[$activeLocale];
            }

            $fallbackLocale = app()->getLocale();
            if (array_key_exists($fallbackLocale, $value)) {
                return $value[$fallbackLocale];
            }

            foreach ($value as $translatedValue) {
                if ($translatedValue !== null && $translatedValue !== '') {
                    return $translatedValue;
                }
            }
        }

        return $value;
    }

    private static function looksLikeTranslationsMap(array $value): bool
    {
        if (isset($value['type']) && $value['type'] === 'doc') {
            return false;
        }

        foreach (array_keys($value) as $key) {
            if (!is_string($key)) {
                return false;
            }
        }

        return true;
    }

    private static function renderDoc(array $doc): string
    {
        $content = $doc['content'] ?? [];

        if (!is_array($content)) {
            return '';
        }

        return implode('', array_map(static fn($node) => self::renderNode($node), $content));
    }

    private static function renderNode(mixed $node): string
    {
        if (!is_array($node)) {
            return '';
        }

        $type = $node['type'] ?? null;

        return match ($type) {
            'paragraph' => self::renderParagraph($node),
            'heading' => self::renderHeading($node),
            'bulletList' => self::wrapTag('ul', self::renderChildren($node)),
            'orderedList' => self::wrapTag('ol', self::renderChildren($node)),
            'listItem' => self::wrapTag('li', self::renderChildren($node)),
            'blockquote' => self::wrapTag('blockquote', self::renderChildren($node)),
            'hardBreak' => '<br>',
            'text' => self::renderTextNode($node),
            default => self::renderChildren($node),
        };
    }

    private static function renderParagraph(array $node): string
    {
        $attrs = $node['attrs'] ?? [];
        $style = '';

        if (is_array($attrs) && !empty($attrs['textAlign'])) {
            $align = htmlspecialchars((string) $attrs['textAlign'], ENT_QUOTES, 'UTF-8');
            $style = ' style="text-align:' . $align . '"';
        }

        return '<p' . $style . '>' . self::renderChildren($node) . '</p>';
    }

    private static function renderHeading(array $node): string
    {
        $attrs = $node['attrs'] ?? [];
        $level = isset($attrs['level']) ? (int) $attrs['level'] : 1;
        $level = max(1, min(6, $level));

        return '<h' . $level . '>' . self::renderChildren($node) . '</h' . $level . '>';
    }

    private static function renderTextNode(array $node): string
    {
        $text = htmlspecialchars((string) ($node['text'] ?? ''), ENT_QUOTES, 'UTF-8');
        $marks = $node['marks'] ?? [];

        if (!is_array($marks)) {
            return $text;
        }

        foreach ($marks as $mark) {
            if (!is_array($mark)) {
                continue;
            }

            $markType = $mark['type'] ?? null;
            $text = match ($markType) {
                'bold' => '<strong>' . $text . '</strong>',
                'italic' => '<em>' . $text . '</em>',
                'underline' => '<u>' . $text . '</u>',
                'strike' => '<s>' . $text . '</s>',
                'link' => self::renderLinkMark($text, $mark),
                default => $text,
            };
        }

        return $text;
    }

    private static function renderLinkMark(string $text, array $mark): string
    {
        $href = $mark['attrs']['href'] ?? null;

        if (!is_string($href) || $href === '') {
            return $text;
        }

        return '<a href="' . htmlspecialchars($href, ENT_QUOTES, 'UTF-8') . '" target="_blank" rel="noopener noreferrer">' . $text . '</a>';
    }

    private static function renderChildren(array $node): string
    {
        $children = $node['content'] ?? [];

        if (!is_array($children)) {
            return '';
        }

        return implode('', array_map(static fn($child) => self::renderNode($child), $children));
    }

    private static function wrapTag(string $tag, string $content): string
    {
        return '<' . $tag . '>' . $content . '</' . $tag . '>';
    }

    private static function isSequentialArray(array $array): bool
    {
        return array_keys($array) === range(0, count($array) - 1);
    }

    private static function resolveTargetLocaleForWrite(array $value, ?string $activeLocale): ?string
    {
        if ($activeLocale && array_key_exists($activeLocale, $value)) {
            return $activeLocale;
        }

        $appLocale = app()->getLocale();
        if (array_key_exists($appLocale, $value)) {
            return $appLocale;
        }

        $firstLocale = array_key_first($value);
        return is_string($firstLocale) ? $firstLocale : null;
    }
}
