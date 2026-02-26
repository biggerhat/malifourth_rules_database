import type { Component } from 'vue';
import type { SymbolTag } from '@/types/content';
import {
    Crow, Fortitude, Magic, Mask, Melee, Missile, Negative,
    Positive, Pulse, Ram, SignatureAction, Soulstone, Tome,
    UnusualDefense, Warding,
} from '@/components/symbols';
import IndexTooltip from '@/components/IndexTooltip.vue';
import IndexContent from '@/components/IndexContent.vue';
import SectionContent from '@/components/SectionContent.vue';
import SectionLink from '@/components/SectionLink.vue';
import PageLink from '@/components/PageLink.vue';
import ExternalLink from '@/components/ExternalLink.vue';

export const SYMBOL_TAGS: SymbolTag[] = [
    'crow', 'magic', 'warding', 'mask', 'melee',
    'missile', 'negative', 'fortitude', 'positive', 'pulse',
    'ram', 'signatureaction', 'soulstone', 'tome', 'unusualdefense',
];

export const COMPONENT_MAP: Record<string, Component> = {
    indexTooltip: IndexTooltip,
    index: IndexContent,
    section: SectionContent,
    sectionLink: SectionLink,
    pageLink: PageLink,
    Link: ExternalLink,
    crow: Crow,
    magic: Magic,
    warding: Warding,
    mask: Mask,
    melee: Melee,
    missile: Missile,
    negative: Negative,
    fortitude: Fortitude,
    positive: Positive,
    pulse: Pulse,
    ram: Ram,
    signatureaction: SignatureAction,
    soulstone: Soulstone,
    tome: Tome,
    unusualdefense: UnusualDefense,
};

export function isSymbolTag(tag: string): tag is SymbolTag {
    return SYMBOL_TAGS.includes(tag as SymbolTag);
}
