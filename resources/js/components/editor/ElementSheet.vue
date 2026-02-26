<script setup lang="ts">
import { SquareMinus, SquarePlus, FileChartColumnIncreasing } from 'lucide-vue-next';
import { Sheet, SheetContent, SheetDescription, SheetHeader, SheetTitle, SheetTrigger } from '@/components/ui/sheet';
import { Tooltip, TooltipContent, TooltipProvider, TooltipTrigger } from '@/components/ui/tooltip';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Tabs, TabsContent, TabsList, TabsTrigger } from '@/components/ui/tabs';
import type { LinkableEntity } from '@/types/content';

const props = defineProps<{
    open: boolean;
    selectedIndex: LinkableEntity | null;
    selectedSection: LinkableEntity | null;
    filteredIndices: LinkableEntity[];
    filteredSections: LinkableEntity[];
    indexFilterText: string;
    sectionFilterText: string;
}>();

const emit = defineEmits<{
    'update:open': [value: boolean];
    'update:selectedIndex': [value: LinkableEntity | null];
    'update:selectedSection': [value: LinkableEntity | null];
    'update:indexFilterText': [value: string];
    'update:sectionFilterText': [value: string];
    'insert-index': [];
    'insert-section': [];
    'cancel': [];
}>();
</script>

<template>
    <Sheet :open="props.open">
        <SheetTrigger>
            <TooltipProvider>
                <Tooltip>
                    <TooltipTrigger>
                        <div class="bg-primary rounded !p-1 border border-primary text-secondary" @click="emit('update:open', true)">
                            <FileChartColumnIncreasing class="mx-auto w-4 h-4" />
                        </div>
                    </TooltipTrigger>
                    <TooltipContent>
                        Add Page Elements
                    </TooltipContent>
                </Tooltip>
            </TooltipProvider>
        </SheetTrigger>
        <SheetContent>
            <SheetHeader>
                <SheetTitle>Add Page Elements</SheetTitle>
                <SheetDescription class="text-primary">
                    <Tabs default-value="index" class="w-full">
                        <TabsList class="grid w-full grid-cols-2">
                            <TabsTrigger value="index">Index</TabsTrigger>
                            <TabsTrigger value="section">Section</TabsTrigger>
                        </TabsList>
                        <TabsContent value="index">
                            <div class="flex flex-col w-full mt-4 space-y-1.5">
                                <Label>Selected Index</Label>
                                <div class="w-full p-2 border border-secondary my-1 flex justify-between hover:bg-secondary" v-if="props.selectedIndex">
                                    <div class="my-auto">{{ props.selectedIndex.title }}</div>
                                    <SquareMinus class="my-auto" @click="emit('update:selectedIndex', null)" />
                                </div>
                                <div v-else class="w-full p-2 my-1 text-red-500">
                                    None
                                </div>
                            </div>
                            <div class="flex w-full mt-4 space-y-1.5 justify-end gap-1">
                                <Button :disabled="!props.selectedIndex" class="bg-green-500" @click="emit('insert-index')">Add Page Element</Button>
                                <Button class="bg-red-500" @click="emit('cancel')">Cancel</Button>
                            </div>

                            <div class="flex flex-col w-full mt-12 space-y-1.5">
                                <hr class="border-primary" />
                                <Label for="indexFilter">Select An Index</Label>
                                <Input id="indexFilter" type="text" :model-value="props.indexFilterText" @update:model-value="emit('update:indexFilterText', $event)" placeholder="Search..." />
                            </div>
                            <div class="max-h-100 overflow-y-auto">
                                <div v-for="index in props.filteredIndices" :key="index.id" class="w-full p-2 border border-secondary my-1 flex justify-between hover:bg-secondary">
                                    <div class="my-auto">{{ index.title }}</div>
                                    <SquarePlus class="my-auto" @click="emit('update:selectedIndex', index)" />
                                </div>
                            </div>
                        </TabsContent>
                        <TabsContent value="section">
                            <div class="flex flex-col w-full mt-4 space-y-1.5">
                                <Label>Selected Section</Label>
                                <div class="w-full p-2 border border-secondary my-1 flex justify-between hover:bg-secondary" v-if="props.selectedSection">
                                    <div class="my-auto">{{ props.selectedSection.title }}</div>
                                    <SquareMinus class="my-auto" @click="emit('update:selectedSection', null)" />
                                </div>
                                <div v-else class="w-full p-2 my-1 text-red-500">
                                    None
                                </div>
                            </div>
                            <div class="flex w-full mt-4 space-y-1.5 justify-end gap-1">
                                <Button :disabled="!props.selectedSection" class="bg-green-500" @click="emit('insert-section')">Add Page Element</Button>
                                <Button class="bg-red-500" @click="emit('cancel')">Cancel</Button>
                            </div>

                            <div class="flex flex-col w-full mt-12 space-y-1.5">
                                <hr class="border-primary" />
                                <Label for="sectionFilter">Select A Section</Label>
                                <Input id="sectionFilter" type="text" :model-value="props.sectionFilterText" @update:model-value="emit('update:sectionFilterText', $event)" placeholder="Search..." />
                            </div>
                            <div class="max-h-100 overflow-y-auto">
                                <div v-for="section in props.filteredSections" :key="section.id" class="w-full p-2 border border-secondary my-1 flex justify-between hover:bg-secondary">
                                    <div class="my-auto">{{ section.title }}</div>
                                    <SquarePlus class="my-auto" @click="emit('update:selectedSection', section)" />
                                </div>
                            </div>
                        </TabsContent>
                    </Tabs>
                </SheetDescription>
            </SheetHeader>
        </SheetContent>
    </Sheet>
</template>
