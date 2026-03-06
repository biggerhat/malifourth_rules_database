<?php

namespace App\Services\ContentBuilder;

use App\Models\Index;
use App\Models\Page;
use App\Models\Section;

class ContentBuilder
{
    public const SYMBOL_TAGS = [
        'crow', 'magic', 'warding', 'mask', 'melee',
        'missile', 'negative', 'fortitude', 'positive', 'pulse',
        'ram', 'signatureaction', 'soulstone', 'tome', 'unusualdefense',
    ];

    public const SYMBOL_FONT_MAP = [
        'crow' => 'c',
        'magic' => 'q',
        'warding' => 'x',
        'mask' => 'm',
        'melee' => 'y',
        'missile' => 'z',
        'negative' => '-',
        'fortitude' => 'u',
        'positive' => '+',
        'pulse' => 'p',
        'ram' => 'r',
        'signatureaction' => 'f',
        'soulstone' => 's',
        'tome' => 't',
        'unusualdefense' => 'v',
    ];

    public const SYMBOL_READABLE_MAP = [
        'crow' => '[crow]',
        'magic' => '[magic]',
        'warding' => '[warding]',
        'mask' => '[mask]',
        'melee' => '[melee]',
        'missile' => '[missile]',
        'negative' => '[negative]',
        'fortitude' => '[fortitude]',
        'positive' => '[positive]',
        'pulse' => '[pulse]',
        'ram' => '[ram]',
        'signatureaction' => '[signature action]',
        'soulstone' => '[soulstone]',
        'tome' => '[tome]',
        'unusualdefense' => '[unusual defense]',
    ];

    public array $parsedContent = [];

    public string $jsonContent;

    /** @var array<string, array<string>> */
    public array $pluckedTagResults = [];

    public array $slugModelMap = [
        'index' => Index::class,
        'indexTooltip' => Index::class,
        'section' => Section::class,
        'sectionLink' => Section::class,
        'pageLink' => Page::class,
    ];

    public array $blockTags = [
        'index',
        'section',
    ];

    public bool $isTipTapJson = false;

    private ?array $decodedDoc = null;

    public function __construct(public string $stringContent)
    {
        $this->isTipTapJson = self::detectTipTapJson($stringContent);

        if ($this->isTipTapJson) {
            $this->decodedDoc = json_decode($stringContent, true);
            $this->parsedContent = is_array($this->decodedDoc) ? TipTapContentBuilder::parse($this->decodedDoc) : [];
        } else {
            $this->parsedContent = $this->parseTaggedTextRecursive();
        }
        $this->jsonContent = json_encode($this->parsedContent, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
    }

    public static function detectTipTapJson(string $content): bool
    {
        $trimmed = trim($content);
        if (! str_starts_with($trimmed, '{')) {
            return false;
        }

        $decoded = json_decode($trimmed, true);

        return is_array($decoded) && ($decoded['type'] ?? null) === 'doc' && isset($decoded['content']);
    }

    public static function toSearchable(string $content): string
    {
        if (self::detectTipTapJson($content)) {
            return TipTapContentBuilder::toSearchable(json_decode($content, true));
        }

        $text = self::removeInlineTags($content);
        $text = preg_replace('/\{\{.*?\}\}/', '', $text);
        $text = strip_tags($text);
        $text = html_entity_decode($text, ENT_QUOTES | ENT_HTML5, 'UTF-8');
        $text = preg_replace('/\s+/', ' ', $text);

        return trim($text);
    }

    public static function toPlainText(string $content): string
    {
        if (self::detectTipTapJson($content)) {
            return TipTapContentBuilder::toSearchable(json_decode($content, true));
        }

        $text = preg_replace('/\{\{.*?\}\}/', '', $content);
        $text = str_replace('<br />', ' ', $text);
        $text = preg_replace('/\s+/', ' ', $text);

        return trim($text);
    }

    public static function parseTitleTags(string $title): string
    {
        if (self::detectTipTapJson($title)) {
            return TipTapContentBuilder::parseTitleTags(json_decode($title, true));
        }

        $search = [];
        $replace = [];

        foreach (self::SYMBOL_FONT_MAP as $tag => $char) {
            $search[] = "{{{$tag} /}}";
            $replace[] = '<span class="font-[symbolFont] text-2xl">'.$char.'</span>';
        }

        return str_replace($search, $replace, $title);
    }

    public static function removeInlineTags(string $content): string
    {
        $search = [];
        $replace = [];

        foreach (self::SYMBOL_READABLE_MAP as $tag => $readable) {
            $search[] = "{{{$tag} /}}";
            $replace[] = $readable;
        }

        $search[] = '<br />';
        $replace[] = '';

        return str_replace($search, $replace, $content);
    }

    public function getFullyHydratedContent(): array
    {
        $content = $this->getParsedContent();
        $slugs = $this->getTagSlugs();
        $modelMap = [];
        foreach ($slugs as $key => $slug) {
            if (! isset($this->slugModelMap[$key])) {
                continue;
            }

            $modelMap[$key] = $this->slugModelMap[$key]::whereIn('slug', $slug)
                ->with('newestVersion')
                ->withTrashed()
                ->get()
                ->keyBy('slug')
                ->map(function ($model) use ($key) {
                    $title = $model->newestVersion->title ?? $model->newestVersion->name ?? $model->title ?? $model->name ?? null;

                    return [
                        'slug' => $model->newestVersion->slug ?? $model->slug,
                        'type' => $model->newestVersion->type->value ?? $model->type->value ?? null,
                        'inline' => ! in_array($key, $this->blockTags),
                        'image' => $model->newestVersion->image ?? $model->image ?? null,
                        'title' => $title ? ContentBuilder::parseTitleTags($title) : $title,
                        'content' => (new ContentBuilder($model->newestVersion->content ?? $model->content ?? ''))->getFullyHydratedContent(),
                        'left_column' => (new ContentBuilder($model->newestVersion->left_column ?? $model->left_column ?? ''))->getFullyHydratedContent(),
                        'right_column' => (new ContentBuilder($model->newestVersion->right_column ?? $model->right_column ?? ''))->getFullyHydratedContent(),
                    ];
                })
                ->toArray();
        }

        return $this->hydrateParsedTags($content, $modelMap);
    }

    public function getParsedContent(): array
    {
        return $this->parsedContent;
    }

    public function getJsonContent(): string
    {
        return $this->jsonContent;
    }

    public function getTagSlugs(): array
    {
        if ($this->isTipTapJson) {
            return TipTapContentBuilder::getTagSlugs($this->decodedDoc);
        }

        $this->pluckTagSlugs($this->parsedContent);

        return $this->pluckedTagResults;
    }

    public function hydrateParsedTags(array $parsedContent, array $modelMap): array
    {
        $output = [];

        foreach ($parsedContent as $node) {
            if (isset($node['text'])) {
                $output[] = $node;

                continue;
            }

            foreach ($node as $tag => $data) {
                $slug = $data['slug'] ?? null;

                if ($slug && isset($modelMap[$tag][$slug])) {
                    // Merge model data into tag props
                    $modelData = $modelMap[$tag][$slug];
                    $data = array_merge($data, $modelData);
                }

                // Recurse if text is nested
                if (isset($data['text']) && is_array($data['text'])) {
                    $data['text'] = $this->hydrateParsedTags($data['text'], $modelMap);
                }

                $output[] = [$tag => $data];
            }
        }

        return $output;
    }

    private function parseTaggedTextRecursive(): array
    {
        $basicHtmlTags = $this->parseBasicElements();
        $tokens = $this->tokenize($basicHtmlTags);

        return $this->parseTokens($tokens);
    }

    private function parseBasicElements(): string
    {
        $nl2br = nl2br($this->stringContent);

        return str_replace([
            '{{b}}',
            '{{/b}}',
            '{{i}}',
            '{{/i}}',
            '{{u}}',
            '{{/u}}',
            '{{hr /}}',
            '{{xl}}',
            '{{/xl}}',
            '{{lg}}',
            '{{/lg}}',
            '{{sm}}',
            '{{/sm}}',
            '{{xs}}',
            '{{/xs}}',
        ], [
            '<strong>',
            '</strong>',
            '<i>',
            '</i>',
            '<u>',
            '</u>',
            '<hr />',
            '<span class="text-xl">',
            '</span>',
            '<span class="text-lg">',
            '</span>',
            '<span class="text-sm">',
            '</span>',
            '<span class="text-xs">',
            '</span>',
        ], $nl2br);
    }

    private function tokenize($text): array|false
    {
        $pattern = '/(\{\{\/?\w+(?:=[^}\s]+)?(?:\s+[^}\/]+)?\s*\/?\}\})/';

        return preg_split($pattern, $text, -1, PREG_SPLIT_DELIM_CAPTURE | PREG_SPLIT_NO_EMPTY);
    }

    private function parseTokens(&$tokens, $expectedTag = null): array
    {
        $result = [];

        while (! empty($tokens)) {
            $token = array_shift($tokens);

            // Closing tag
            if (preg_match('/^\{\{\/(\w+)\}\}$/', $token, $closeMatch)) {
                if ($expectedTag && $closeMatch[1] === $expectedTag) {
                    return $result;
                }

                continue;
            }

            // Self-closing tag
            if (preg_match('/^\{\{(\w+)(=([^\s\/}]+))?((?:\s+\w+=(?:"[^"]*"|\'[^\']*\'|\S+))*)\s*\/\}\}$/', $token, $match)) {
                $tag = $match[1];
                $slug = $match[3];
                $attrString = trim($match[4]);
                $attrs = $this->parseAttributes($attrString);
                $attrs['inline'] = ! in_array($tag, $this->blockTags);
                if ($slug) {
                    $attrs['slug'] = $slug;
                }
                $result[] = [$tag => $attrs];

                continue;
            }

            // Opening tag
            if (preg_match('/^\{\{(\w+)(=([^\s}]+))?((?:\s+\w+=(?:"[^"]*"|\'[^\']*\'|\S+))*)\}\}$/', $token, $match)) {
                $tag = $match[1];
                $slug = $match[3];
                $attrString = trim($match[4]);
                $attrs = $this->parseAttributes($attrString);
                if ($slug) {
                    $attrs['slug'] = $slug;
                }

                $children = $this->parseTokens($tokens, $tag);
                $attrs['text'] = $this->flattenText($children);

                $result[] = [$tag => $attrs];

                continue;
            }

            // Plain text
            $result[] = ['text' => preg_replace('~[\r\n]+~', '', $token)];
        }

        return $result;
    }

    private function parseAttributes($attrString): array
    {
        $attrs = [];
        preg_match_all('/(\w+)=(".*?"|\'.*?\'|\S+)/', $attrString, $matches, PREG_SET_ORDER);
        foreach ($matches as $match) {
            $key = $match[1];
            $value = trim($match[2], "\"'");
            $attrs[$key] = $value;
        }

        return $attrs;
    }

    private function flattenText($nodes): array|string
    {
        if (count($nodes) === 1 && isset($nodes[0]['text']) && is_string($nodes[0]['text'])) {
            return $nodes[0]['text'];
        }

        return $nodes;
    }

    /**
     * @param  array<string, mixed>  $parsed
     */
    private function pluckTagSlugs(array $parsed): self
    {
        foreach ($parsed as $node) {
            if (isset($node['text'])) {
                continue; // skip plain text
            }

            foreach ($node as $tag => $data) {
                if (! isset($data['slug'])) {
                    continue; // skip if no slug
                }

                // Initialize the array for the tag
                if (! isset($this->pluckedTagResults[$tag])) {
                    $this->pluckedTagResults[$tag] = [];
                }

                $this->pluckedTagResults[$tag][] = $data['slug'];

                // If nested text exists, recurse
                if (isset($data['text']) && is_array($data['text'])) {
                    $this->pluckTagSlugs($data['text']);
                }
            }
        }

        foreach ($this->pluckedTagResults as $tag => $slugs) {
            $this->pluckedTagResults[$tag] = array_values(array_unique($slugs));
        }

        return $this;
    }
}
