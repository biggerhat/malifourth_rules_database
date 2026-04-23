import { Mark, mergeAttributes } from '@tiptap/core';

export type TextSizeValue = 'xl' | 'lg' | 'sm' | 'xs';

const SIZE_CLASSES: Record<TextSizeValue, string> = {
    xl: 'text-xl',
    lg: 'text-lg',
    sm: 'text-sm',
    xs: 'text-xs',
};

export const TextSize = Mark.create({
    name: 'textSize',

    addAttributes() {
        return {
            size: {
                default: 'lg',
                parseHTML: (element) => {
                    for (const [size, cls] of Object.entries(SIZE_CLASSES)) {
                        if (element.classList.contains(cls)) {
                            return size;
                        }
                    }
                    return null;
                },
                renderHTML: (attributes) => {
                    const cls = SIZE_CLASSES[attributes.size as TextSizeValue];
                    return cls ? { class: cls } : {};
                },
            },
        };
    },

    parseHTML() {
        return [
            {
                tag: 'span',
                getAttrs: (el) => {
                    const dom = el as HTMLElement;
                    for (const cls of Object.values(SIZE_CLASSES)) {
                        if (dom.classList.contains(cls)) return {};
                    }
                    return false;
                },
            },
        ];
    },

    renderHTML({ HTMLAttributes }) {
        return ['span', mergeAttributes(HTMLAttributes), 0];
    },

    addCommands() {
        return {
            setTextSize:
                (size: TextSizeValue) =>
                ({ commands }) =>
                    commands.setMark(this.name, { size }),
            unsetTextSize:
                () =>
                ({ commands }) =>
                    commands.unsetMark(this.name),
        };
    },
});
