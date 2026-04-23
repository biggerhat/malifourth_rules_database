import { Node, mergeAttributes } from '@tiptap/core';
import { VueNodeViewRenderer } from '@tiptap/vue-3';
import EntityEmbedView from '../views/EntityEmbedView.vue';

export type EmbedType = 'index' | 'section';

export const EntityEmbed = Node.create({
    name: 'entityEmbed',
    group: 'block',
    atom: true,

    addAttributes() {
        return {
            embedType: { default: 'index' },
            slug: { default: '' },
            title: { default: '' },
        };
    },

    parseHTML() {
        return [
            {
                tag: 'div[data-entity-embed]',
                getAttrs: (el) => {
                    const dom = el as HTMLElement;
                    return {
                        embedType: dom.getAttribute('data-embed-type'),
                        slug: dom.getAttribute('data-slug'),
                        title: dom.getAttribute('data-title'),
                    };
                },
            },
        ];
    },

    renderHTML({ HTMLAttributes }) {
        return [
            'div',
            mergeAttributes(HTMLAttributes, {
                'data-entity-embed': '',
                'data-embed-type': HTMLAttributes.embedType,
                'data-slug': HTMLAttributes.slug,
                'data-title': HTMLAttributes.title,
            }),
            `${HTMLAttributes.embedType}: ${HTMLAttributes.title}`,
        ];
    },

    addNodeView() {
        return VueNodeViewRenderer(EntityEmbedView);
    },
});
