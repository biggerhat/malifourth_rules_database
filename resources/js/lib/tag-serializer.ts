import { isSymbolTag } from '@/lib/content-tags';
import type { TextSegment } from '@/types/content';

const FORMATTING_TAGS = new Set(['b', 'i', 'u', 'xl', 'lg', 'sm', 'xs']);
const SLUG_TAGS = new Set(['indexTooltip', 'sectionLink', 'pageLink', 'Link']);

/**
 * Convert an array of typed segments into custom tag string.
 * Handles both tree (children) and flat (content) segments.
 */
export function segmentsToCustomTags(segments: TextSegment[]): string {
    let result = '';

    for (const seg of segments) {
        const type = seg.type;

        // Tree node with children — serialize recursively
        if (seg.children) {
            const tag = type === 'strong' ? 'b' : type;
            result += `{{${tag}}}${segmentsToCustomTags(seg.children)}{{/${tag}}}`;
            continue;
        }

        // Symbol self-closing tags
        if (isSymbolTag(type)) {
            result += `{{${type} /}} `;
            continue;
        }

        switch (type) {
            case 'text':
                result += htmlToCustomTags(seg.content as string);
                break;
            case 'b':
            case 'strong':
                result += `{{b}}${htmlToCustomTags(seg.content as string)}{{/b}} `;
                break;
            case 'i':
            case 'em':
                result += `{{i}}${htmlToCustomTags(seg.content as string)}{{/i}} `;
                break;
            case 'u':
                result += `{{u}}${htmlToCustomTags(seg.content as string)}{{/u}} `;
                break;
            case 'xl':
                result += `{{xl}}${htmlToCustomTags(seg.content as string)}{{/xl}} `;
                break;
            case 'lg':
                result += `{{lg}}${htmlToCustomTags(seg.content as string)}{{/lg}} `;
                break;
            case 'sm':
                result += `{{sm}}${htmlToCustomTags(seg.content as string)}{{/sm}} `;
                break;
            case 'xs':
                result += `{{xs}}${htmlToCustomTags(seg.content as string)}{{/xs}} `;
                break;
            case 'hr':
                result += '{{hr /}}';
                break;
            case 'br':
                result += '\n';
                break;
            case 'indexTooltip':
            case 'sectionLink':
            case 'pageLink':
            case 'Link':
                if (seg.content && typeof seg.content === 'object' && 'slug' in seg.content) {
                    result += `{{${type}=${seg.content.slug}}}${htmlToCustomTags(seg.content.text)}{{/${type}}} `;
                }
                break;
            default:
                result += htmlToCustomTags(seg.content as string) + ' ' || '';
        }
    }

    return result;
}

/**
 * Serialize an array of content blocks (with text arrays, index, section keys) into a custom tag string.
 */
export function blocksToCustomTags(blocks: any[]): string {
    let result = '';

    for (const block of blocks) {
        if (block.text) {
            result += segmentsToCustomTags(block.text);
        } else if (block.index) {
            result += `{{index=${block.index.slug} /}} `;
        } else if (block.section) {
            result += `{{section=${block.section.slug} /}} `;
        }
    }

    return result;
}

// --- Tokenizer + Recursive Descent Parser ---

type Token =
    | { kind: 'text'; value: string }
    | { kind: 'open'; tag: string; slug?: string }
    | { kind: 'close'; tag: string }
    | { kind: 'selfclose'; tag: string }
    | { kind: 'br' };

function tokenize(str: string): Token[] {
    // Normalize <br />
    str = str.replace(/<br\s*\/?>/gi, '{{__br__}}');

    const tagPattern = /\{\{(\/?[a-zA-Z]+|[a-zA-Z]+=[^}]+|[a-zA-Z]+\s*\/|__br__)\}\}/g;
    const tokens: Token[] = [];
    let lastIndex = 0;
    let match;

    while ((match = tagPattern.exec(str)) !== null) {
        const before = str.slice(lastIndex, match.index);
        if (before) {
            tokens.push({ kind: 'text', value: before });
        }

        const tag = match[1];
        lastIndex = tagPattern.lastIndex;

        if (tag === '__br__') {
            tokens.push({ kind: 'br' });
        } else if (tag.startsWith('/')) {
            tokens.push({ kind: 'close', tag: tag.slice(1) });
        } else if (tag.endsWith('/')) {
            tokens.push({ kind: 'selfclose', tag: tag.replace('/', '').trim() });
        } else if (tag.includes('=')) {
            const eqIdx = tag.indexOf('=');
            tokens.push({ kind: 'open', tag: tag.slice(0, eqIdx), slug: tag.slice(eqIdx + 1) });
        } else {
            tokens.push({ kind: 'open', tag });
        }
    }

    if (lastIndex < str.length) {
        tokens.push({ kind: 'text', value: str.slice(lastIndex) });
    }

    return tokens;
}

/** Collect plain text from a tree of segments (for slug tag inner text). */
function collectText(segments: TextSegment[]): string {
    let text = '';
    for (const seg of segments) {
        if (seg.children) {
            text += collectText(seg.children);
        } else if (seg.type === 'text' && typeof seg.content === 'string') {
            text += seg.content;
        }
    }
    return text;
}

/**
 * Parse a custom-tag string back into typed segments using recursive descent.
 */
export function customTagsToSegments(str: string): TextSegment[] {
    const tokens = tokenize(str);
    let pos = 0;

    function parse(stopTag?: string): TextSegment[] {
        const result: TextSegment[] = [];

        while (pos < tokens.length) {
            const token = tokens[pos];

            if (token.kind === 'close') {
                if (token.tag === stopTag) {
                    pos++; // consume the close tag
                    return result;
                }
                // Mismatched close tag — treat as text and skip
                pos++;
                continue;
            }

            if (token.kind === 'text') {
                result.push({ type: 'text', content: token.value });
                pos++;
            } else if (token.kind === 'br') {
                result.push({ type: 'br', content: '' });
                pos++;
            } else if (token.kind === 'selfclose') {
                if (token.tag === 'hr') {
                    result.push({ type: 'hr', content: '' });
                } else {
                    // Symbols and other self-closing tags
                    result.push({ type: token.tag, content: { inline: true } as any });
                }
                pos++;
            } else if (token.kind === 'open') {
                pos++; // consume the open tag

                if (FORMATTING_TAGS.has(token.tag)) {
                    const children = parse(token.tag);
                    const type = token.tag === 'b' ? 'strong' : token.tag;
                    result.push({ type, children });
                } else if (SLUG_TAGS.has(token.tag)) {
                    const children = parse(token.tag);
                    const text = collectText(children);
                    result.push({
                        type: token.tag,
                        content: {
                            slug: token.slug!,
                            text,
                            inline: true,
                        },
                    });
                } else {
                    // Unknown tag — parse children and collect as text
                    const children = parse(token.tag);
                    const text = collectText(children);
                    result.push({ type: 'text', content: text });
                }
            }
        }

        return result;
    }

    return parse();
}

/**
 * Convert HTML formatting tags to custom bracket tags.
 */
export function htmlToCustomTags(html: string): string {
    if (typeof html !== 'string') return '';
    return html
        .replace(/<b>/gi, '{{b}}')
        .replace(/<\/b>/gi, '{{/b}} ')
        .replace(/\[mask]/gi, '{{mask /}} ')
        .replace(/<i>/gi, '{{i}}')
        .replace(/<\/i>/gi, '{{/i}} ')
        .replace(/<u>/gi, '{{u}}')
        .replace(/<\/u>/gi, '{{/u}} ')
        .replace(/<strong>/gi, '{{b}}')
        .replace(/<\/strong>/gi, '{{/b}} ')
        .replace(/<em>/gi, '{{i}}')
        .replace(/<\/em>/gi, '{{/i}} ');
}
