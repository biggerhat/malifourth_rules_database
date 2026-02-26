<script setup lang="ts">
import { ref } from 'vue';
import { SquareMinus, SquarePlus } from 'lucide-vue-next';
import { Sheet, SheetContent, SheetDescription, SheetHeader, SheetTitle, SheetTrigger } from '@/components/ui/sheet';
import { Tooltip, TooltipContent, TooltipProvider, TooltipTrigger } from '@/components/ui/tooltip';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { MousePointerClick } from 'lucide-vue-next';
import type { LinkableEntity } from '@/types/content';

const props = defineProps<{
    open: boolean;
    filteredIndices: LinkableEntity[];
    indexFilterText: string;
    tooltipText: string;
    selectedIndex: LinkableEntity | null;
}>();

const emit = defineEmits<{
    'update:open': [value: boolean];
    'update:indexFilterText': [value: string];
    'update:tooltipText': [value: string];
    'update:selectedIndex': [value: LinkableEntity | null];
    'open-sheet': [];
    'insert-tooltip': [];
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
                            <MousePointerClick class="mx-auto w-4 h-4" />
                        </div>
                    </TooltipTrigger>
                    <TooltipContent>
                        Add Index Tooltip
                    </TooltipContent>
                </Tooltip>
            </TooltipProvider>
        </SheetTrigger>
        <SheetContent>
            <SheetHeader>
                <SheetTitle>Add Index Tooltip</SheetTitle>
                <SheetDescription class="text-primary">
                    <div class="flex flex-col w-full mt-4 space-y-1.5">
                        <Label for="label">Tooltip Label</Label>
                        <Input id="label" type="text" autofocus :model-value="props.tooltipText" @update:model-value="emit('update:tooltipText', $event)" placeholder="Tooltip Label" />
                    </div>
                    <div class="flex flex-col w-full mt-4 space-y-1.5">
                        <Label>Selected Index</Label>
                        <div class="w-full p-2 border border-secondary my-1 flex justify-between hover:bg-secondary" v-if="props.selectedIndex">
                            <div @click="emit('update:tooltipText', props.selectedIndex.title)" class="my-auto">{{ props.selectedIndex.display_name }}</div>
                            <SquareMinus class="my-auto" @click="emit('update:selectedIndex', null)" />
                        </div>
                        <div v-else class="w-full p-2 my-1 text-red-500">
                            None
                        </div>
                    </div>
                    <div class="flex w-full mt-4 space-y-1.5 justify-end gap-1">
                        <Button :disabled="!props.selectedIndex" class="bg-green-500" @click="emit('insert-tooltip')">Add Tooltip</Button>
                        <Button class="bg-red-500" @click="emit('cancel')">Cancel</Button>
                    </div>

                    <div class="flex flex-col w-full mt-12 space-y-1.5">
                        <hr class="border-primary" />
                        <Label for="indexFilter">Select An Index</Label>
                        <Input id="indexFilter" type="text" :model-value="props.indexFilterText" @update:model-value="emit('update:indexFilterText', $event)" placeholder="Search..." />
                    </div>
                    <div class="max-h-100 overflow-y-auto">
                        <div v-for="index in props.filteredIndices" :key="index.id" class="w-full p-2 border border-secondary my-1 flex justify-between hover:bg-secondary">
                            <div @click="emit('update:tooltipText', index.title)" class="my-auto">{{ index.display_name }}</div>
                            <SquarePlus class="my-auto" @click="emit('update:selectedIndex', index)" />
                        </div>
                    </div>
                </SheetDescription>
            </SheetHeader>
        </SheetContent>
    </Sheet>
</template>
