<?php

namespace App\Services\ContentBuilder;

class ContentToTipTapConverter
{
    /**
     * Convert a bracket-tag content string to TipTap JSON.
     * Returns the JSON string, or the original content if it's already TipTap JSON.
     */
    public static function convert(string $content): string
    {
        if (trim($content) === '') {
            return json_encode([
                'type' => 'doc',
                'content' => [['type' => 'paragraph']],
            ]);
        }

        if (ContentBuilder::detectTipTapJson($content)) {
            return $content;
        }

        $html = self::bracketTagsToHtml($content);
        $nodes = self::htmlToTipTapNodes($html);

        // Group inline nodes into paragraphs, splitting on <br>
        $doc = self::buildDocument($nodes);

        return json_encode($doc, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
    }

    /**
     * Convert bracket tags to intermediate HTML (same as ContentBuilder::parseBasicElements).
     */
    private static function bracketTagsToHtml(string $content): string
    {
        return str_replace([
            '{{b}}', '{{/b}}',
            '{{i}}', '{{/i}}',
            '{{u}}', '{{/u}}',
            '{{hr /}}',
            '{{xl}}', '{{/xl}}',
            '{{lg}}', '{{/lg}}',
            '{{sm}}', '{{/sm}}',
            '{{xs}}', '{{/xs}}',
        ], [
            '<strong>', '</strong>',
            '<i>', '</i>',
            '<u>', '</u>',
            '<hr />',
            '<span class="text-xl">', '</span>',
            '<span class="text-lg">', '</span>',
            '<span class="text-sm">', '</span>',
            '<span class="text-xs">', '</span>',
        ], $content);
    }

    /**
     * Parse the intermediate HTML+bracket-tags into flat TipTap node list.
     */
    private static function htmlToTipTapNodes(string $html): array
    {
        $nodes = [];
        $markStack = [];

        // Tokenize: split on HTML tags and remaining bracket tags
        $pattern = '/(<br\s*\/?>|<hr\s*\/?>|<strong>|<\/strong>|<i>|<\/i>|<u>|<\/u>|<span class="text-(?:xl|lg|sm|xs)">|<\/span>|\{\{\/?\w+(?:=[^\s}]+)?(?:\s+[^}\/]+)?\s*\/?\}\})/';
        $parts = preg_split($pattern, $html, -1, PREG_SPLIT_DELIM_CAPTURE | PREG_SPLIT_NO_EMPTY);

        foreach ($parts as $part) {
            // <br> → hardBreak
            if (preg_match('/^<br\s*\/?>$/', $part)) {
                $nodes[] = ['type' => 'hardBreak'];

                continue;
            }

            // <hr> → horizontalRule
            if (preg_match('/^<hr\s*\/?>$/', $part)) {
                $nodes[] = ['type' => 'horizontalRule'];

                continue;
            }

            // Opening HTML formatting tags
            if ($part === '<strong>') {
                $markStack[] = ['type' => 'bold'];

                continue;
            }
            if ($part === '<i>') {
                $markStack[] = ['type' => 'italic'];

                continue;
            }
            if ($part === '<u>') {
                $markStack[] = ['type' => 'underline'];

                continue;
            }

            // Text size spans
            if (preg_match('/^<span class="text-(xl|lg|sm|xs)">$/', $part, $m)) {
                $markStack[] = ['type' => 'textSize', 'attrs' => ['size' => $m[1]]];

                continue;
            }

            // Closing HTML tags
            if ($part === '</strong>') {
                self::popMark($markStack, 'bold');

                continue;
            }
            if ($part === '</i>') {
                self::popMark($markStack, 'italic');

                continue;
            }
            if ($part === '</u>') {
                self::popMark($markStack, 'underline');

                continue;
            }
            if ($part === '</span>') {
                self::popMark($markStack, 'textSize');

                continue;
            }

            // Self-closing bracket tag: {{symbol /}} or {{index=slug /}} or {{section=slug /}}
            if (preg_match('/^\{\{(\w+)(=([^\s\/}]+))?((?:\s+\w+=(?:"[^"]*"|\'[^\']*\'|\S+))*)\s*\/\}\}$/', $part, $match)) {
                $tag = $match[1];
                $slug = $match[3] ?? null;
                $attrString = trim($match[4] ?? '');
                $attrs = self::parseAttributes($attrString);

                if (in_array($tag, ContentBuilder::SYMBOL_TAGS)) {
                    $nodes[] = ['type' => 'gameSymbol', 'attrs' => ['symbol' => $tag]];
                } elseif ($tag === 'index' || $tag === 'section') {
                    // Block embed
                    $nodes[] = [
                        'type' => 'entityEmbed',
                        'attrs' => [
                            'embedType' => $tag,
                            'slug' => $slug ?? '',
                            'title' => $attrs['title'] ?? $slug ?? '',
                        ],
                    ];
                }

                continue;
            }

            // Opening bracket tag: {{indexTooltip=slug}} or {{sectionLink=slug}} or {{pageLink=slug}} or {{Link=url}}
            if (preg_match('/^\{\{(\w+)(=([^\s}]+))?((?:\s+\w+=(?:"[^"]*"|\'[^\']*\'|\S+))*)\}\}$/', $part, $match)) {
                $tag = $match[1];
                $slug = $match[3] ?? '';

                // Collect text until closing tag
                // We store a marker and will resolve it later
                $markStack[] = ['type' => '__entity', 'tag' => $tag, 'slug' => $slug, 'startIndex' => count($nodes)];

                continue;
            }

            // Closing bracket tag
            if (preg_match('/^\{\{\/(\w+)\}\}$/', $part, $closeMatch)) {
                $tag = $closeMatch[1];

                // Find matching entity mark on stack
                $entityIndex = null;
                for ($i = count($markStack) - 1; $i >= 0; $i--) {
                    if (($markStack[$i]['type'] ?? '') === '__entity' && ($markStack[$i]['tag'] ?? '') === $tag) {
                        $entityIndex = $i;
                        break;
                    }
                }

                if ($entityIndex !== null) {
                    $entityMark = $markStack[$entityIndex];
                    array_splice($markStack, $entityIndex, 1);

                    // Extract label text from nodes added since the entity opened
                    $startIdx = $entityMark['startIndex'];
                    $labelNodes = array_splice($nodes, $startIdx);
                    $label = self::extractTextFromNodes($labelNodes);

                    $refType = $entityMark['tag'];
                    $slug = $entityMark['slug'];

                    $nodes[] = [
                        'type' => 'entityReference',
                        'attrs' => [
                            'referenceType' => $refType,
                            'slug' => $slug,
                            'label' => $label,
                            'url' => $refType === 'Link' ? $slug : null,
                        ],
                    ];
                }

                continue;
            }

            // Plain text
            $text = preg_replace('~[\r\n]+~', '', $part);
            if ($text !== '') {
                $node = ['type' => 'text', 'text' => $text];
                if (! empty($markStack)) {
                    $marks = [];
                    foreach ($markStack as $mark) {
                        if ($mark['type'] === '__entity') {
                            continue;
                        }
                        $m = ['type' => $mark['type']];
                        if (isset($mark['attrs'])) {
                            $m['attrs'] = $mark['attrs'];
                        }
                        $marks[] = $m;
                    }
                    if (! empty($marks)) {
                        $node['marks'] = $marks;
                    }
                }
                $nodes[] = $node;
            }
        }

        return $nodes;
    }

    /**
     * Build a TipTap document from flat nodes, grouping inline nodes into paragraphs.
     */
    private static function buildDocument(array $nodes): array
    {
        $paragraphs = [];
        $currentInline = [];

        $flush = function () use (&$paragraphs, &$currentInline) {
            if (! empty($currentInline)) {
                $paragraphs[] = ['type' => 'paragraph', 'content' => $currentInline];
                $currentInline = [];
            }
        };

        foreach ($nodes as $node) {
            if ($node['type'] === 'horizontalRule') {
                $flush();
                $paragraphs[] = $node;
            } elseif ($node['type'] === 'hardBreak') {
                $flush();
            } elseif ($node['type'] === 'entityEmbed') {
                $flush();
                $paragraphs[] = $node;
            } else {
                $currentInline[] = $node;
            }
        }
        $flush();

        if (empty($paragraphs)) {
            $paragraphs[] = ['type' => 'paragraph'];
        }

        return ['type' => 'doc', 'content' => $paragraphs];
    }

    private static function popMark(array &$stack, string $type): void
    {
        for ($i = count($stack) - 1; $i >= 0; $i--) {
            if (($stack[$i]['type'] ?? '') === $type) {
                array_splice($stack, $i, 1);

                return;
            }
        }
    }

    private static function parseAttributes(string $attrString): array
    {
        $attrs = [];
        preg_match_all('/(\w+)=(".*?"|\'.*?\'|\S+)/', $attrString, $matches, PREG_SET_ORDER);
        foreach ($matches as $match) {
            $attrs[$match[1]] = trim($match[2], "\"'");
        }

        return $attrs;
    }

    private static function extractTextFromNodes(array $nodes): string
    {
        $text = '';
        foreach ($nodes as $node) {
            if (isset($node['text'])) {
                $text .= $node['text'];
            } elseif (($node['type'] ?? '') === 'entityReference') {
                $text .= $node['attrs']['label'] ?? '';
            }
        }

        return $text;
    }
}
