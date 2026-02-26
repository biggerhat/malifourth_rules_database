export type SymbolTag =
    | 'crow' | 'magic' | 'warding' | 'mask' | 'melee'
    | 'missile' | 'negative' | 'fortitude' | 'positive' | 'pulse'
    | 'ram' | 'signatureaction' | 'soulstone' | 'tome' | 'unusualdefense';

export type FormattingTag = 'b' | 'strong' | 'i' | 'em' | 'u' | 'xl' | 'lg' | 'sm' | 'xs';

export type SlugTag = 'indexTooltip' | 'sectionLink' | 'pageLink' | 'Link';

export interface TextSegment {
    type: string;
    content?: string | SlugContent | { inline: boolean };
    children?: TextSegment[];
}

export interface SlugContent {
    slug: string;
    text: string;
    inline?: boolean;
}

export interface ContentBlock {
    text?: TextSegment[];
    index?: { slug: string };
    section?: { slug: string };
    uniqueIndex?: number;
    [key: string]: any;
}

export interface LinkableEntity {
    id: number;
    title: string;
    display_name?: string;
    slug: string;
}
