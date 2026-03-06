<script setup lang="ts">
import { ref, watch } from 'vue';
import {
    Dialog,
    DialogContent,
    DialogDescription,
    DialogHeader,
    DialogTitle,
} from '@/components/ui/dialog';
import { Tabs, TabsContent, TabsList, TabsTrigger } from '@/components/ui/tabs';
import { Input } from '@/components/ui/input';
import { Button } from '@/components/ui/button';
import { Label } from '@/components/ui/label';
import axios from 'axios';
import { useDebounceFn } from '@vueuse/core';

export type EntitySearchMode = 'tooltip' | 'link' | 'embed';

interface EntityResult {
    id: number;
    title: string;
    slug: string;
    display_name: string;
    type: string;
}

const props = defineProps<{
    open: boolean;
    mode: EntitySearchMode;
}>();

const emit = defineEmits<{
    'update:open': [value: boolean];
    select: [data: { referenceType: string; slug: string; label: string; url?: string; embedType?: string; title?: string }];
}>();

const searchQuery = ref('');
const results = ref<EntityResult[]>([]);
const loading = ref(false);
const selectedTab = ref('index');
const externalUrl = ref('');
const externalLabel = ref('');

const tabsForMode = {
    tooltip: ['index'],
    link: ['index', 'section', 'page', 'external'],
    embed: ['index', 'section'],
};

const tabLabels: Record<string, string> = {
    index: 'Index',
    section: 'Section',
    page: 'Page',
    external: 'External URL',
};

watch(
    () => props.open,
    (val) => {
        if (val) {
            searchQuery.value = '';
            results.value = [];
            externalUrl.value = '';
            externalLabel.value = '';
            const tabs = tabsForMode[props.mode];
            selectedTab.value = tabs[0];
        }
    },
);

const doSearch = useDebounceFn(async () => {
    if (searchQuery.value.length < 2 || selectedTab.value === 'external') return;
    loading.value = true;
    try {
        const response = await axios.get(route('admin.entity-search'), {
            params: { q: searchQuery.value, type: selectedTab.value },
        });
        results.value = response.data;
    } catch {
        results.value = [];
    } finally {
        loading.value = false;
    }
}, 300);

watch(searchQuery, doSearch);
watch(selectedTab, () => {
    results.value = [];
    if (searchQuery.value.length >= 2) doSearch();
});

const selectEntity = (entity: EntityResult) => {
    if (props.mode === 'tooltip') {
        emit('select', {
            referenceType: 'indexTooltip',
            slug: entity.slug,
            label: entity.display_name || entity.title,
        });
    } else if (props.mode === 'link') {
        const refTypeMap: Record<string, string> = {
            index: 'indexTooltip',
            section: 'sectionLink',
            page: 'pageLink',
        };
        emit('select', {
            referenceType: refTypeMap[selectedTab.value] ?? 'indexTooltip',
            slug: entity.slug,
            label: entity.display_name || entity.title,
        });
    } else if (props.mode === 'embed') {
        emit('select', {
            referenceType: 'embed',
            slug: entity.slug,
            label: entity.display_name || entity.title,
            embedType: selectedTab.value,
            title: entity.display_name || entity.title,
        });
    }
    emit('update:open', false);
};

const submitExternal = () => {
    if (!externalUrl.value || !externalLabel.value) return;
    emit('select', {
        referenceType: 'Link',
        slug: externalUrl.value,
        label: externalLabel.value,
        url: externalUrl.value,
    });
    emit('update:open', false);
};
</script>

<template>
    <Dialog :open="props.open" @update:open="emit('update:open', $event)">
        <DialogContent class="max-w-lg">
            <DialogHeader>
                <DialogTitle>
                    {{
                        mode === 'tooltip'
                            ? 'Insert Tooltip'
                            : mode === 'link'
                              ? 'Insert Link'
                              : 'Insert Embed'
                    }}
                </DialogTitle>
                <DialogDescription>Search for an entity to insert.</DialogDescription>
            </DialogHeader>

            <Tabs v-model="selectedTab">
                <TabsList>
                    <TabsTrigger
                        v-for="tab in tabsForMode[mode]"
                        :key="tab"
                        :value="tab"
                    >
                        {{ tabLabels[tab] }}
                    </TabsTrigger>
                </TabsList>

                <TabsContent
                    v-for="tab in tabsForMode[mode].filter((t) => t !== 'external')"
                    :key="tab"
                    :value="tab"
                    class="mt-4"
                >
                    <Input
                        v-model="searchQuery"
                        placeholder="Search..."
                        class="mb-3"
                    />
                    <div class="max-h-60 overflow-y-auto">
                        <p v-if="loading" class="py-4 text-center text-sm text-muted-foreground">
                            Searching...
                        </p>
                        <p
                            v-else-if="!results.length && searchQuery.length >= 2"
                            class="py-4 text-center text-sm text-muted-foreground"
                        >
                            No results found.
                        </p>
                        <p
                            v-else-if="searchQuery.length < 2"
                            class="py-4 text-center text-sm text-muted-foreground"
                        >
                            Type at least 2 characters to search.
                        </p>
                        <button
                            v-for="entity in results"
                            :key="entity.id"
                            class="flex w-full items-center rounded px-3 py-2 text-left text-sm hover:bg-accent"
                            @click="selectEntity(entity)"
                        >
                            <span>{{ entity.display_name || entity.title }}</span>
                            <span class="ml-auto text-xs text-muted-foreground">{{ entity.slug }}</span>
                        </button>
                    </div>
                </TabsContent>

                <TabsContent v-if="tabsForMode[mode].includes('external')" value="external" class="mt-4">
                    <div class="space-y-3">
                        <div>
                            <Label>URL</Label>
                            <Input v-model="externalUrl" placeholder="https://..." />
                        </div>
                        <div>
                            <Label>Label</Label>
                            <Input v-model="externalLabel" placeholder="Link text" />
                        </div>
                        <Button
                            :disabled="!externalUrl || !externalLabel"
                            @click="submitExternal"
                        >
                            Insert Link
                        </Button>
                    </div>
                </TabsContent>
            </Tabs>
        </DialogContent>
    </Dialog>
</template>
