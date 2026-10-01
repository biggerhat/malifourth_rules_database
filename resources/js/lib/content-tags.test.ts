import { describe, expect, it } from 'vitest';
import { COMPONENT_MAP, isSymbolTag, SYMBOL_TAGS } from './content-tags';

describe('isSymbolTag', () => {
    it('recognizes every declared symbol tag', () => {
        for (const tag of SYMBOL_TAGS) {
            expect(isSymbolTag(tag)).toBe(true);
        }
    });

    it('rejects tags that are not symbol tags', () => {
        expect(isSymbolTag('sectionLink')).toBe(false);
        expect(isSymbolTag('pageLink')).toBe(false);
        expect(isSymbolTag('not-a-real-tag')).toBe(false);
        expect(isSymbolTag('')).toBe(false);
    });
});

describe('COMPONENT_MAP', () => {
    it('has a rendering component for every symbol tag', () => {
        // A tag added to SYMBOL_TAGS without a matching COMPONENT_MAP entry parses
        // successfully server-side but silently renders nothing client-side — this
        // is exactly the class of bug the endofactivation/endofturn/specifictime
        // additions in this session had to get right on both sides.
        for (const tag of SYMBOL_TAGS) {
            expect(COMPONENT_MAP[tag], `missing COMPONENT_MAP entry for symbol tag "${tag}"`).toBeDefined();
        }
    });

    it('maps every content cross-reference tag used by ContentBuilder', () => {
        const contentTags = ['indexTooltip', 'index', 'section', 'sectionLink', 'pageLink', 'Link'];

        for (const tag of contentTags) {
            expect(COMPONENT_MAP[tag], `missing COMPONENT_MAP entry for "${tag}"`).toBeDefined();
        }
    });

    it('does not have duplicate or accidental empty keys', () => {
        const keys = Object.keys(COMPONENT_MAP);
        expect(new Set(keys).size).toBe(keys.length);
        expect(keys.every((k) => k.length > 0)).toBe(true);
    });
});
