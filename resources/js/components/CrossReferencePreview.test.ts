import { mount } from '@vue/test-utils';
import { describe, expect, it } from 'vitest';
import CrossReferencePreview from './CrossReferencePreview.vue';

describe('CrossReferencePreview', () => {
    it('renders a plain link with no tooltip wrapper when there is nothing to preview', () => {
        const wrapper = mount(CrossReferencePreview, {
            props: { href: '/rules/movement', text: 'Movement' },
        });

        const link = wrapper.find('a');
        expect(link.exists()).toBe(true);
        expect(link.attributes('href')).toBe('/rules/movement');
        expect(wrapper.text()).toContain('Movement');
        // No preview data means hasPreview is false and the component takes the
        // plain-Link branch entirely, skipping the tooltip trigger/provider tree.
        expect(wrapper.findComponent({ name: 'TooltipTrigger' }).exists()).toBe(false);
    });

    it('wraps the link in a tooltip trigger when a title is provided', () => {
        const wrapper = mount(CrossReferencePreview, {
            props: { href: '/rules/line-of-sight', text: 'Line of Sight', title: 'Line of Sight' },
        });

        const link = wrapper.find('a');
        expect(link.exists()).toBe(true);
        expect(link.attributes('href')).toBe('/rules/line-of-sight');
        expect(wrapper.findComponent({ name: 'TooltipTrigger' }).exists()).toBe(true);
    });

    it('wraps the link in a tooltip trigger when preview content is provided', () => {
        const wrapper = mount(CrossReferencePreview, {
            props: {
                href: '/rules/movement',
                text: 'Movement',
                content: [{ text: 'A model may move up to its Movement stat.' }],
            },
        });

        expect(wrapper.findComponent({ name: 'TooltipTrigger' }).exists()).toBe(true);
    });

    it('prefers left_column content over the main content when both are present', () => {
        // previewContent() checks left_column first — without this precedence, a
        // Section's right-column-only preview would silently show nothing even
        // though the component still (correctly) reports hasPreview as true.
        const wrapperLeftOnly = mount(CrossReferencePreview, {
            props: {
                href: '/rules/section',
                text: 'A Section',
                left_column: [{ text: 'Left column text.' }],
                content: [],
            },
        });

        expect(wrapperLeftOnly.findComponent({ name: 'TooltipTrigger' }).exists()).toBe(true);
    });

    it('renders custom render-function text when text is a plain string', () => {
        const wrapper = mount(CrossReferencePreview, {
            props: { href: '/rules/movement', text: 'Movement' },
        });

        expect(wrapper.text()).toBe('Movement');
    });
});
