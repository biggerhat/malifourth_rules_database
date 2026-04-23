<script setup lang="ts">
import type { Editor } from '@tiptap/vue-3';
import { Button } from '@/components/ui/button';
import {
    DropdownMenu,
    DropdownMenuContent,
    DropdownMenuItem,
    DropdownMenuLabel,
    DropdownMenuSeparator,
    DropdownMenuTrigger,
} from '@/components/ui/dropdown-menu';
import { SYMBOL_LIST, SYMBOL_FONT_MAP } from './extensions/GameSymbol';
import type { TextSizeValue } from './extensions/TextSize';
import { Bold, Italic, Underline, Strikethrough, Minus } from 'lucide-vue-next';

const props = defineProps<{
    editor: Editor;
    mode?: 'full' | 'inline';
}>();

const emit = defineEmits<{
    openTooltip: [];
    openLink: [];
    openEmbed: [type: 'index' | 'section'];
}>();

const sizes: { label: string; value: TextSizeValue }[] = [
    { label: 'XL', value: 'xl' },
    { label: 'LG', value: 'lg' },
    { label: 'SM', value: 'sm' },
    { label: 'XS', value: 'xs' },
];

const symbolGroups = [
    { label: 'Suits', symbols: ['crow', 'mask', 'ram', 'tome'] as const },
    { label: 'Range', symbols: ['melee', 'missile', 'pulse'] as const },
    { label: 'Modifiers', symbols: ['positive', 'negative'] as const },
    { label: 'Defense', symbols: ['fortitude', 'warding', 'unusualdefense'] as const },
    { label: 'Other', symbols: ['magic', 'signatureaction', 'soulstone'] as const },
];

const insertSymbol = (symbol: string) => {
    props.editor.chain().focus().insertContent({ type: 'gameSymbol', attrs: { symbol } }).run();
};

const setSize = (size: TextSizeValue) => {
    if (props.editor.isActive('textSize', { size })) {
        (props.editor.commands as any).unsetTextSize();
    } else {
        (props.editor.commands as any).setTextSize(size);
    }
};

const isFullMode = props.mode !== 'inline';
</script>

<template>
    <div class="flex flex-wrap items-center gap-1 border-b px-2 py-1">
        <Button
            variant="ghost"
            size="sm"
            class="h-8 w-8 p-0"
            :class="{ 'bg-accent': editor.isActive('bold') }"
            @click="editor.chain().focus().toggleBold().run()"
        >
            <Bold class="h-4 w-4" />
        </Button>
        <Button
            variant="ghost"
            size="sm"
            class="h-8 w-8 p-0"
            :class="{ 'bg-accent': editor.isActive('italic') }"
            @click="editor.chain().focus().toggleItalic().run()"
        >
            <Italic class="h-4 w-4" />
        </Button>
        <Button
            variant="ghost"
            size="sm"
            class="h-8 w-8 p-0"
            :class="{ 'bg-accent': editor.isActive('underline') }"
            @click="editor.chain().focus().toggleUnderline().run()"
        >
            <Underline class="h-4 w-4" />
        </Button>
        <Button
            variant="ghost"
            size="sm"
            class="h-8 w-8 p-0"
            :class="{ 'bg-accent': editor.isActive('strike') }"
            @click="editor.chain().focus().toggleStrike().run()"
        >
            <Strikethrough class="h-4 w-4" />
        </Button>

        <div class="mx-1 h-6 w-px bg-border" />

        <!-- Size dropdown -->
        <DropdownMenu>
            <DropdownMenuTrigger as-child>
                <Button variant="ghost" size="sm" class="h-8 px-2 text-xs">Size</Button>
            </DropdownMenuTrigger>
            <DropdownMenuContent>
                <DropdownMenuItem
                    v-for="s in sizes"
                    :key="s.value"
                    @click="setSize(s.value)"
                    :class="{ 'bg-accent': editor.isActive('textSize', { size: s.value }) }"
                >
                    {{ s.label }}
                </DropdownMenuItem>
            </DropdownMenuContent>
        </DropdownMenu>

        <template v-if="isFullMode">
            <Button
                variant="ghost"
                size="sm"
                class="h-8 w-8 p-0"
                @click="editor.chain().focus().setHorizontalRule().run()"
            >
                <Minus class="h-4 w-4" />
            </Button>
        </template>

        <div class="mx-1 h-6 w-px bg-border" />

        <!-- Symbols dropdown -->
        <DropdownMenu>
            <DropdownMenuTrigger as-child>
                <Button variant="ghost" size="sm" class="h-8 px-2">
                    <span class="font-[symbolFont] text-lg">c</span>
                    <span class="ml-1 text-xs">Symbols</span>
                </Button>
            </DropdownMenuTrigger>
            <DropdownMenuContent class="w-48">
                <template v-for="(group, gi) in symbolGroups" :key="group.label">
                    <DropdownMenuSeparator v-if="gi > 0" />
                    <DropdownMenuLabel>{{ group.label }}</DropdownMenuLabel>
                    <DropdownMenuItem
                        v-for="sym in group.symbols"
                        :key="sym"
                        @click="insertSymbol(sym)"
                    >
                        <span class="font-[symbolFont] mr-2 text-lg">{{ SYMBOL_FONT_MAP[sym] }}</span>
                        {{ sym }}
                    </DropdownMenuItem>
                </template>
            </DropdownMenuContent>
        </DropdownMenu>

        <div class="mx-1 h-6 w-px bg-border" />

        <!-- Reference buttons -->
        <Button variant="ghost" size="sm" class="h-8 px-2 text-xs" @click="emit('openTooltip')">
            Tooltip
        </Button>
        <Button variant="ghost" size="sm" class="h-8 px-2 text-xs" @click="emit('openLink')">
            Link
        </Button>

        <template v-if="isFullMode">
            <div class="mx-1 h-6 w-px bg-border" />
            <Button
                variant="ghost"
                size="sm"
                class="h-8 px-2 text-xs"
                @click="emit('openEmbed', 'index')"
            >
                Index Embed
            </Button>
            <Button
                variant="ghost"
                size="sm"
                class="h-8 px-2 text-xs"
                @click="emit('openEmbed', 'section')"
            >
                Section Embed
            </Button>
        </template>
    </div>
</template>
