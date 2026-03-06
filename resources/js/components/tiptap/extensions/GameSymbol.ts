import { Node, mergeAttributes } from '@tiptap/core';
import { VueNodeViewRenderer } from '@tiptap/vue-3';
import GameSymbolView from '../views/GameSymbolView.vue';

export const SYMBOL_LIST = [
    'crow',
    'magic',
    'warding',
    'mask',
    'melee',
    'missile',
    'negative',
    'fortitude',
    'positive',
    'pulse',
    'ram',
    'signatureaction',
    'soulstone',
    'tome',
    'unusualdefense',
] as const;

export type SymbolName = (typeof SYMBOL_LIST)[number];

export const SYMBOL_FONT_MAP: Record<SymbolName, string> = {
    crow: 'c',
    magic: 'q',
    warding: 'x',
    mask: 'm',
    melee: 'y',
    missile: 'z',
    negative: '-',
    fortitude: 'u',
    positive: '+',
    pulse: 'p',
    ram: 'r',
    signatureaction: 'f',
    soulstone: 's',
    tome: 't',
    unusualdefense: 'v',
};

export const GameSymbol = Node.create({
    name: 'gameSymbol',
    group: 'inline',
    inline: true,
    atom: true,

    addAttributes() {
        return {
            symbol: {
                default: 'crow',
            },
        };
    },

    parseHTML() {
        return [
            {
                tag: 'span[data-game-symbol]',
                getAttrs: (el) => ({
                    symbol: (el as HTMLElement).getAttribute('data-game-symbol'),
                }),
            },
        ];
    },

    renderHTML({ HTMLAttributes }) {
        return [
            'span',
            mergeAttributes(HTMLAttributes, {
                'data-game-symbol': HTMLAttributes.symbol,
            }),
        ];
    },

    addNodeView() {
        return VueNodeViewRenderer(GameSymbolView);
    },
});
