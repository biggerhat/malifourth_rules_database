<?php

namespace App\Services\ContentBuilder;

class TipTapContentBuilder
{
    public const MARK_MAP = [
        'bold' => 'strong',
        'italic' => 'i',
        'underline' => 'u',
        'strike' => 's',
    ];

    public const SIZE_CLASS_MAP = [
        'xl' => 'text-xl',
        'lg' => 'text-lg',
        'sm' => 'text-sm',
        'xs' => 'text-xs',
    ];

    /**
     * Convert TipTap JSON document to the same hydrated array format
     * that ContentBuilder::parseTaggedTextRecursive() produces.
     */
    public static function parse(array $doc): array
    {
        $result = [];

        if (! isset($doc['content'])) {
            return $result;
        }

        foreach ($doc['content'] as $node) {
            $parsed = self::parseNode($node);
            if ($parsed !== null) {
                if (is_array($parsed) && ! isset($parsed[0]) && ! empty($parsed)) {
                    $result[] = $parsed;
                } elseif (is_array($parsed) && isset($parsed[0])) {
                    array_push($result, ...$parsed);
                } else {
                    $result[] = $parsed;
                }
            }
        }

        return $result;
    }

    private static function parseNode(array $node): ?array
    {
        $type = $node['type'] ?? '';

        return match ($type) {
            'paragraph' => self::parseParagraph($node),
            'text' => self::parseText($node),
            'hardBreak' => ['text' => '<br />'],
            'horizontalRule' => ['text' => '<hr />'],
            'gameSymbol' => self::parseGameSymbol($node),
            'entityReference' => self::parseEntityReference($node),
            'entityEmbed' => self::parseEntityEmbed($node),
            'heading' => self::parseParagraph($node),
            default => null,
        };
    }

    private static function parseParagraph(array $node): ?array
    {
        if (! isset($node['content']) || empty($node['content'])) {
            return ['text' => '<br />'];
        }

        $parts = [];
        foreach ($node['content'] as $child) {
            $parsed = self::parseNode($child);
            if ($parsed !== null) {
                if (is_array($parsed) && isset($parsed[0])) {
                    array_push($parts, ...$parsed);
                } else {
                    $parts[] = $parsed;
                }
            }
        }

        // If paragraph has a single text, return it directly
        if (count($parts) === 1 && isset($parts[0]['text'])) {
            return $parts[0];
        }

        // Return all parts as individual items (will be merged at top level)
        return $parts;
    }

    private static function parseText(array $node): array
    {
        $text = $node['text'] ?? '';
        $marks = $node['marks'] ?? [];

        if (empty($marks)) {
            return ['text' => $text];
        }

        $html = htmlspecialchars($text, ENT_QUOTES | ENT_HTML5, 'UTF-8', false);
        $openTags = [];
        $closeTags = [];

        foreach ($marks as $mark) {
            $markType = $mark['type'] ?? '';

            if ($markType === 'textSize') {
                $size = $mark['attrs']['size'] ?? 'lg';
                $class = self::SIZE_CLASS_MAP[$size] ?? 'text-lg';
                $openTags[] = '<span class="'.$class.'">';
                $closeTags[] = '</span>';
            } elseif (isset(self::MARK_MAP[$markType])) {
                $tag = self::MARK_MAP[$markType];
                $openTags[] = '<'.$tag.'>';
                $closeTags[] = '</'.$tag.'>';
            }
        }

        return ['text' => implode('', $openTags).$html.implode('', array_reverse($closeTags))];
    }

    private static function parseGameSymbol(array $node): array
    {
        $symbol = $node['attrs']['symbol'] ?? 'crow';

        return [$symbol => ['inline' => true]];
    }

    private static function parseEntityReference(array $node): array
    {
        $attrs = $node['attrs'] ?? [];
        $refType = $attrs['referenceType'] ?? 'indexTooltip';
        $slug = $attrs['slug'] ?? '';
        $label = $attrs['label'] ?? '';

        return [$refType => [
            'slug' => $slug,
            'text' => $label,
            'inline' => true,
        ]];
    }

    private static function parseEntityEmbed(array $node): array
    {
        $attrs = $node['attrs'] ?? [];
        $embedType = $attrs['embedType'] ?? 'index';
        $slug = $attrs['slug'] ?? '';

        return [$embedType => [
            'slug' => $slug,
            'inline' => false,
        ]];
    }

    /**
     * Extract plain text from a TipTap JSON document for search indexing.
     */
    public static function toSearchable(array $doc): string
    {
        return trim(preg_replace('/\s+/', ' ', self::extractText($doc)));
    }

    private static function extractText(array $node): string
    {
        $text = '';

        if (isset($node['text'])) {
            $text .= $node['text'].' ';
        }

        if (isset($node['type']) && $node['type'] === 'entityReference') {
            $text .= ($node['attrs']['label'] ?? '').' ';
        }

        if (isset($node['content'])) {
            foreach ($node['content'] as $child) {
                $text .= self::extractText($child);
            }
        }

        return $text;
    }

    /**
     * Extract tag slugs from a TipTap JSON document.
     * Returns the same format as ContentBuilder::getTagSlugs().
     */
    public static function getTagSlugs(array $doc): array
    {
        $result = [];
        self::walkForSlugs($doc, $result);

        foreach ($result as $tag => $slugs) {
            $result[$tag] = array_values(array_unique($slugs));
        }

        return $result;
    }

    private static function walkForSlugs(array $node, array &$result): void
    {
        $type = $node['type'] ?? '';

        if ($type === 'entityReference') {
            $attrs = $node['attrs'] ?? [];
            $refType = $attrs['referenceType'] ?? '';
            $slug = $attrs['slug'] ?? '';
            if ($refType && $slug) {
                $result[$refType][] = $slug;
            }
        }

        if ($type === 'entityEmbed') {
            $attrs = $node['attrs'] ?? [];
            $embedType = $attrs['embedType'] ?? '';
            $slug = $attrs['slug'] ?? '';
            if ($embedType && $slug) {
                $result[$embedType][] = $slug;
            }
        }

        if (isset($node['content'])) {
            foreach ($node['content'] as $child) {
                self::walkForSlugs($child, $result);
            }
        }
    }

    /**
     * Parse title with symbol nodes into HTML for display.
     */
    public static function parseTitleTags(array $doc): string
    {
        $html = '';

        $nodes = $doc['content'] ?? [$doc];

        foreach ($nodes as $node) {
            $type = $node['type'] ?? '';

            if ($type === 'text') {
                $html .= htmlspecialchars($node['text'] ?? '', ENT_QUOTES | ENT_HTML5, 'UTF-8', false);
            } elseif ($type === 'gameSymbol') {
                $symbol = $node['attrs']['symbol'] ?? '';
                $char = ContentBuilder::SYMBOL_FONT_MAP[$symbol] ?? '';
                if ($char) {
                    $html .= '<span class="font-[symbolFont] text-2xl">'.$char.'</span>';
                }
            } elseif ($type === 'paragraph' && isset($node['content'])) {
                $html .= self::parseTitleTags($node);
            }
        }

        return $html;
    }
}
