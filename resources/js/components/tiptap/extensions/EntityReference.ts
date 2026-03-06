import { Node, mergeAttributes } from '@tiptap/core';
import { VueNodeViewRenderer } from '@tiptap/vue-3';
import EntityReferenceView from '../views/EntityReferenceView.vue';

export type ReferenceType = 'indexTooltip' | 'sectionLink' | 'pageLink' | 'Link';

export const EntityReference = Node.create({
    name: 'entityReference',
    group: 'inline',
    inline: true,
    atom: true,

    addAttributes() {
        return {
            referenceType: { default: 'indexTooltip' },
            slug: { default: '' },
            label: { default: '' },
            url: { default: null },
        };
    },

    parseHTML() {
        return [
            {
                tag: 'span[data-entity-reference]',
                getAttrs: (el) => {
                    const dom = el as HTMLElement;
                    return {
                        referenceType: dom.getAttribute('data-reference-type'),
                        slug: dom.getAttribute('data-slug'),
                        label: dom.getAttribute('data-label'),
                        url: dom.getAttribute('data-url') || null,
                    };
                },
            },
        ];
    },

    renderHTML({ HTMLAttributes }) {
        return [
            'span',
            mergeAttributes(HTMLAttributes, {
                'data-entity-reference': '',
                'data-reference-type': HTMLAttributes.referenceType,
                'data-slug': HTMLAttributes.slug,
                'data-label': HTMLAttributes.label,
                'data-url': HTMLAttributes.url,
            }),
            HTMLAttributes.label,
        ];
    },

    addNodeView() {
        return VueNodeViewRenderer(EntityReferenceView);
    },
});
