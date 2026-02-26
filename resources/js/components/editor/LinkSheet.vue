<script setup lang="ts">
import { SquareMinus, SquarePlus, Link2 } from 'lucide-vue-next';
import { Sheet, SheetContent, SheetDescription, SheetHeader, SheetTitle, SheetTrigger } from '@/components/ui/sheet';
import { Tooltip, TooltipContent, TooltipProvider, TooltipTrigger } from '@/components/ui/tooltip';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Tabs, TabsContent, TabsList, TabsTrigger } from '@/components/ui/tabs';
import type { LinkableEntity } from '@/types/content';

const props = defineProps<{
    open: boolean;
    linkText: string;
    linkUrl: string | null;
    selectedSection: LinkableEntity | null;
    selectedPage: LinkableEntity | null;
    filteredSections: LinkableEntity[];
    filteredPages: LinkableEntity[];
    sectionFilterText: string;
    pageFilterText: string;
}>();

const emit = defineEmits<{
    'update:open': [value: boolean];
    'update:linkText': [value: string];
    'update:linkUrl': [value: string | null];
    'update:selectedSection': [value: LinkableEntity | null];
    'update:selectedPage': [value: LinkableEntity | null];
    'update:sectionFilterText': [value: string];
    'update:pageFilterText': [value: string];
    'open-sheet': [];
    'insert-section-link': [];
    'insert-page-link': [];
    'insert-external-link': [];
    'cancel': [];
}>();
</script>

<template>
    <Sheet :open="props.open">
        <SheetTrigger>
            <TooltipProvider>
                <Tooltip>
                    <TooltipTrigger>
                        <div class="bg-primary rounded !p-1 mx-1 border border-primary text-secondary" @click="emit('open-sheet')">
                            <Link2 class="mx-auto w-4 h-4" />
                        </div>
                    </TooltipTrigger>
                    <TooltipContent>
                        Add Link
                    </TooltipContent>
                </Tooltip>
            </TooltipProvider>
        </SheetTrigger>
        <SheetContent>
            <SheetHeader>
                <SheetTitle>Add Link</SheetTitle>
                <SheetDescription class="text-primary">
                    <Tabs default-value="section" class="w-full">
                        <TabsList class="grid w-full grid-cols-3">
                            <TabsTrigger value="section">Section</TabsTrigger>
                            <TabsTrigger value="page">Page</TabsTrigger>
                            <TabsTrigger value="external">External</TabsTrigger>
                        </TabsList>
                        <TabsContent value="section">
                            <div class="flex flex-col w-full mt-4 space-y-1.5">
                                <Label for="label">Label</Label>
                                <Input id="label" type="text" autofocus :model-value="props.linkText" @update:model-value="emit('update:linkText', $event)" placeholder="Add Label" />
                            </div>
                            <div class="flex flex-col w-full mt-4 space-y-1.5">
                                <Label>Selected Section</Label>
                                <div class="w-full p-2 border border-secondary my-1 flex justify-between hover:bg-secondary" v-if="props.selectedSection">
                                    <div @click="emit('update:linkText', props.selectedSection.title)" class="my-auto">{{ props.selectedSection.display_name }}</div>
                                    <SquareMinus class="my-auto" @click="emit('update:selectedSection', null)" />
                                </div>
                                <div v-else class="w-full p-2 my-1 text-red-500">
                                    None
                                </div>
                            </div>
                            <div class="flex w-full mt-4 space-y-1.5 justify-end gap-1">
                                <Button :disabled="!props.selectedSection" class="bg-green-500" @click="emit('insert-section-link')">Add Link</Button>
                                <Button class="bg-red-500" @click="emit('cancel')">Cancel</Button>
                            </div>

                            <div class="flex flex-col w-full mt-12 space-y-1.5">
                                <hr class="border-primary" />
                                <Label for="sectionFilter">Select A Section</Label>
                                <Input id="sectionFilter" type="text" :model-value="props.sectionFilterText" @update:model-value="emit('update:sectionFilterText', $event)" placeholder="Search..." />
                            </div>
                            <div class="max-h-100 overflow-y-auto">
                                <div v-for="section in props.filteredSections" :key="section.id" class="w-full p-2 border border-secondary my-1 flex justify-between hover:bg-secondary">
                                    <div @click="emit('update:linkText', section.title)" class="my-auto">{{ section.display_name }}</div>
                                    <SquarePlus class="my-auto" @click="emit('update:selectedSection', section)" />
                                </div>
                            </div>
                        </TabsContent>
                        <TabsContent value="page">
                            <div class="flex flex-col w-full mt-4 space-y-1.5">
                                <Label for="label">Label</Label>
                                <Input id="label" type="text" autofocus :model-value="props.linkText" @update:model-value="emit('update:linkText', $event)" placeholder="Add Label" />
                            </div>
                            <div class="flex flex-col w-full mt-4 space-y-1.5">
                                <Label>Selected Page</Label>
                                <div class="w-full p-2 border border-secondary my-1 flex justify-between hover:bg-secondary" v-if="props.selectedPage">
                                    <div @click="emit('update:linkText', props.selectedPage.title)" class="my-auto">{{ props.selectedPage.display_name }}</div>
                                    <SquareMinus class="my-auto" @click="emit('update:selectedPage', null)" />
                                </div>
                                <div v-else class="w-full p-2 my-1 text-red-500">
                                    None
                                </div>
                            </div>
                            <div class="flex w-full mt-4 space-y-1.5 justify-end gap-1">
                                <Button :disabled="!props.selectedPage" class="bg-green-500" @click="emit('insert-page-link')">Add Link</Button>
                                <Button class="bg-red-500" @click="emit('cancel')">Cancel</Button>
                            </div>

                            <div class="flex flex-col w-full mt-12 space-y-1.5">
                                <hr class="border-primary" />
                                <Label for="pageFilter">Select A Page</Label>
                                <Input id="pageFilter" type="text" :model-value="props.pageFilterText" @update:model-value="emit('update:pageFilterText', $event)" placeholder="Search..." />
                            </div>
                            <div class="max-h-100 overflow-y-auto">
                                <div v-for="page in props.filteredPages" :key="page.id" class="w-full p-2 border border-secondary my-1 flex justify-between hover:bg-secondary">
                                    <div @click="emit('update:linkText', page.title)" class="my-auto">{{ page.display_name }}</div>
                                    <SquarePlus class="my-auto" @click="emit('update:selectedPage', page)" />
                                </div>
                            </div>
                        </TabsContent>
                        <TabsContent value="external">
                            <div class="flex flex-col w-full mt-4 space-y-1.5">
                                <Label for="label">Label</Label>
                                <Input id="label" type="text" autofocus :model-value="props.linkText" @update:model-value="emit('update:linkText', $event)" placeholder="Add Label" />
                            </div>
                            <div class="flex flex-col w-full mt-4 space-y-1.5">
                                <Label for="url">URL</Label>
                                <Input id="url" type="text" :model-value="props.linkUrl" @update:model-value="emit('update:linkUrl', $event)" placeholder="Add URL" />
                            </div>
                            <div class="flex w-full mt-4 space-y-1.5 justify-end gap-1">
                                <Button :disabled="!props.linkUrl || !props.linkText" class="bg-green-500" @click="emit('insert-external-link')">Add Link</Button>
                                <Button class="bg-red-500" @click="emit('cancel')">Cancel</Button>
                            </div>
                        </TabsContent>
                    </Tabs>
                </SheetDescription>
            </SheetHeader>
        </SheetContent>
    </Sheet>
</template>
