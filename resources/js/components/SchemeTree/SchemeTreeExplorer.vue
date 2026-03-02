<script setup lang="ts">
import { computed, provide, ref, type Ref, watch } from 'vue'
import SchemeTreeNode from './SchemeTreeNode.vue'
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select'
import { GitBranch } from 'lucide-vue-next'

export interface Scheme {
    id: number
    title: string
    slug: string
    front_image: string | null
    next_scheme_1: number | null
    next_scheme_2: number | null
    next_scheme_3: number | null
    prerequisites: any[]
    reveal: any[]
    scoring: any[]
    additional: any[]
}

const props = defineProps<{
    schemes: Scheme[]
}>()

const MAX_DEPTH = 4
const selectedId = ref<string>('')

const schemeMap = computed(() => {
    const map = new Map<number, Scheme>()
    for (const scheme of props.schemes) {
        map.set(scheme.id, scheme)
    }
    return map
})

const rootScheme = computed(() => {
    const id = Number(selectedId.value)
    return id ? schemeMap.value.get(id) ?? null : null
})

provide<Ref<Map<number, Scheme>>>('schemeMap', schemeMap)
provide('maxDepth', MAX_DEPTH)
</script>

<template>
    <div>
        <!-- Picker -->
        <div class="mb-6">
            <label class="block text-sm font-medium mb-2">Choose a starting scheme</label>
            <Select v-model="selectedId">
                <SelectTrigger class="w-full sm:w-72">
                    <SelectValue placeholder="Select a scheme..." />
                </SelectTrigger>
                <SelectContent>
                    <SelectItem
                        v-for="scheme in props.schemes"
                        :key="scheme.id"
                        :value="String(scheme.id)"
                    >
                        {{ scheme.title }}
                    </SelectItem>
                </SelectContent>
            </Select>
        </div>

        <!-- Empty state -->
        <div v-if="!rootScheme" class="text-center py-12">
            <GitBranch class="size-10 mx-auto text-muted-foreground/40 mb-3" />
            <p class="text-sm text-muted-foreground max-w-sm mx-auto">
                Pick a starting scheme to explore how schemes connect through their escalation chains.
                Each scheme leads to 3 possible next schemes.
            </p>
        </div>

        <!-- Root card (re-mounts when selection changes) -->
        <SchemeTreeNode
            v-if="rootScheme"
            :key="rootScheme.id"
            :scheme="rootScheme"
            :depth="0"
        />
    </div>
</template>
