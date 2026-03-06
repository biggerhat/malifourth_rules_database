import { customTagsToSegments } from '@/lib/tag-serializer';
import type { TextSegment } from '@/types/content';
import { SYMBOL_TAGS } from '@/lib/content-tags';

interface TipTapNode {
    type: string;
    attrs?: Record<string, any>;
    marks?: TipTapMark[];
    content?: TipTapNode[];
    text?: string;
}

interface TipTapMark {
    type: string;
    attrs?: Record<string, any>;
}

const SIZE_TAGS = new Set(['xl', 'lg', 'sm', 'xs']);

function segmentsToTipTapNodes(segments: TextSegment[], marks: TipTapMark[] = []): TipTapNode[] {
    const nodes: TipTapNode[] = [];

    for (const seg of segments) {
        if (seg.children) {
            let newMark: TipTapMark | null = null;
            const type = seg.type === 'b' ? 'strong' : seg.type;

            if (type === 'strong') {
                newMark = { type: 'bold' };
            } else if (type === 'i' || type === 'em') {
                newMark = { type: 'italic' };
            } else if (type === 'u') {
                newMark = { type: 'underline' };
            } else if (SIZE_TAGS.has(seg.type)) {
                newMark = { type: 'textSize', attrs: { size: seg.type } };
            }

            const childMarks = newMark ? [...marks, newMark] : marks;
            nodes.push(...segmentsToTipTapNodes(seg.children, childMarks));
            continue;
        }

        if (SYMBOL_TAGS.includes(seg.type as any)) {
            nodes.push({ type: 'gameSymbol', attrs: { symbol: seg.type } });
            continue;
        }

        if (seg.type === 'hr') {
            nodes.push({ type: 'horizontalRule' });
            continue;
        }

        if (seg.type === 'br') {
            nodes.push({ type: 'hardBreak' });
            continue;
        }

        if (
            seg.type === 'indexTooltip' ||
            seg.type === 'sectionLink' ||
            seg.type === 'pageLink' ||
            seg.type === 'Link'
        ) {
            const content = seg.content as { slug: string; text: string };
            nodes.push({
                type: 'entityReference',
                attrs: {
                    referenceType: seg.type,
                    slug: content.slug,
                    label: content.text,
                    url: seg.type === 'Link' ? content.slug : null,
                },
            });
            continue;
        }

        if (seg.type === 'text' && typeof seg.content === 'string') {
            const textNode: TipTapNode = { type: 'text', text: seg.content };
            if (marks.length > 0) {
                textNode.marks = [...marks];
            }
            nodes.push(textNode);
        }
    }

    return nodes;
}

/**
 * Convert a legacy bracket-tag string to TipTap JSON document.
 */
export function legacyToTipTap(content: string): string {
    if (!content || content.trim() === '') {
        return JSON.stringify({
            type: 'doc',
            content: [{ type: 'paragraph' }],
        });
    }

    const segments = customTagsToSegments(content);

    // Split segments into paragraphs by br/hardBreak
    const paragraphs: TipTapNode[] = [];
    let currentInline: TipTapNode[] = [];

    const flush = () => {
        if (currentInline.length > 0) {
            paragraphs.push({ type: 'paragraph', content: currentInline });
            currentInline = [];
        }
    };

    const tipTapNodes = segmentsToTipTapNodes(segments);

    for (const node of tipTapNodes) {
        if (node.type === 'horizontalRule') {
            flush();
            paragraphs.push(node);
        } else if (node.type === 'hardBreak') {
            // Convert <br> to paragraph break
            flush();
        } else if (node.type === 'entityEmbed') {
            flush();
            paragraphs.push(node);
        } else {
            currentInline.push(node);
        }
    }
    flush();

    if (paragraphs.length === 0) {
        paragraphs.push({ type: 'paragraph' });
    }

    return JSON.stringify({ type: 'doc', content: paragraphs });
}

/**
 * Detect whether a string is TipTap JSON or legacy bracket-tag format.
 */
export function isTipTapJson(content: string): boolean {
    if (!content) return false;
    const trimmed = content.trim();
    if (!trimmed.startsWith('{')) return false;
    try {
        const parsed = JSON.parse(trimmed);
        return parsed.type === 'doc' && Array.isArray(parsed.content);
    } catch {
        return false;
    }
}
