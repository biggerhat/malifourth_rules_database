<?php echo '<?xml version="1.0" encoding="UTF-8"?>'; ?>
<rss version="2.0">
    <channel>
        <title>{{ config('app.name') }} — Changelog</title>
        <link>{{ $siteUrl }}</link>
        <description>Recent errata, FAQ, and card errata updates.</description>
        <atom:link xmlns:atom="http://www.w3.org/2005/Atom" href="{{ $feedUrl }}" rel="self" type="application/rss+xml" />
        @foreach ($entries as $entry)
        <item>
            <title>{{ $entry['type_label'] }}: {{ $entry['title'] }}</title>
            <link>{{ $entry['url'] }}</link>
            <guid isPermaLink="true">{{ $entry['url'] }}</guid>
            <description>{!! e($entry['excerpt'] ?? '') !!}</description>
            <pubDate>{{ $entry['published_at']->toRfc2822String() }}</pubDate>
        </item>
        @endforeach
    </channel>
</rss>
