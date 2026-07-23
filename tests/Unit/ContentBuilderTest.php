<?php

use App\Services\ContentBuilder\ContentBuilder;

it('converts basic bbcode-style tags into safe html', function () {
    $builder = new ContentBuilder('{{b}}bold{{/b}} and {{i}}italic{{/i}}');

    $content = $builder->getParsedContent();

    expect($content)->toBe([
        ['text' => '<strong>bold</strong> and <i>italic</i>'],
    ]);
});

it('parses self-closing symbol tags with inline flag', function () {
    $builder = new ContentBuilder('{{crow /}}');

    expect($builder->getParsedContent())->toBe([
        ['crow' => ['inline' => true]],
    ]);
});

it('parses block reference tags with a slug and nested text', function () {
    $builder = new ContentBuilder('{{section=line-of-sight}}some text{{/section}}');

    expect($builder->getParsedContent())->toBe([
        ['section' => ['slug' => 'line-of-sight', 'text' => 'some text']],
    ]);
});

it('collects slugs referenced by block tags for hydration lookups', function () {
    $builder = new ContentBuilder('{{section=line-of-sight}}text{{/section}} {{pageLink=movement}}link{{/pageLink}}');

    expect($builder->getTagSlugs())->toBe([
        'section' => ['line-of-sight'],
        'pageLink' => ['movement'],
    ]);
});

it('escapes raw html typed into content so it cannot execute as markup', function () {
    $builder = new ContentBuilder('<script>alert(1)</script>');

    $content = $builder->getParsedContent();

    expect($content)->toBe([
        ['text' => '&lt;script&gt;alert(1)&lt;/script&gt;'],
    ]);
});

it('escapes an html attribute injection attempt embedded in plain text', function () {
    $builder = new ContentBuilder('<img src=x onerror=alert(1)>');

    expect($builder->getParsedContent())->toBe([
        ['text' => '&lt;img src=x onerror=alert(1)&gt;'],
    ]);
});

it('still applies intentional bold/italic formatting around escaped text', function () {
    $builder = new ContentBuilder('{{b}}<script>bad</script>{{/b}}');

    expect($builder->getParsedContent())->toBe([
        ['text' => '<strong>&lt;script&gt;bad&lt;/script&gt;</strong>'],
    ]);
});

it('does not escape the custom tag delimiters themselves', function () {
    $builder = new ContentBuilder('{{index=some-index}}text{{/index}}');

    expect($builder->getParsedContent())->toBe([
        ['index' => ['slug' => 'some-index', 'text' => 'text']],
    ]);
});

it('escapes html in titles before substituting symbol-font spans', function () {
    $result = ContentBuilder::parseTitleTags('<script>alert(1)</script>{{crow /}}');

    expect($result)
        ->not->toContain('<script>')
        ->toContain('&lt;script&gt;')
        ->toContain('<span class="font-[symbolFont] text-2xl">c</span>');
});

it('leaves plain titles without markup untouched aside from escaping', function () {
    expect(ContentBuilder::parseTitleTags('Rules & Regulations'))
        ->toBe('Rules &amp; Regulations');
});

it('strips tags and normalizes whitespace for plain text output', function () {
    $result = ContentBuilder::toPlainText("{{b}}Line One{{/b}}\nLine   Two");

    expect($result)->toBe('Line One Line Two');
});

it('builds searchable text with tags and entities stripped', function () {
    $result = ContentBuilder::toSearchable('{{b}}Rules &amp; Regulations{{/b}}<br />More text');

    expect($result)->toBe('Rules & RegulationsMore text');
});
