<script setup lang="ts">
import { computed, inject, ref, type Ref } from 'vue'
import ParsedContent from '@/components/ParsedContent.vue'
import { Card, CardContent, CardFooter } from '@/components/ui/card'
import { ChevronDown, ExternalLink } from 'lucide-vue-next'

interface Scheme {
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
    scheme: Scheme
    depth: number
}>()

const schemeMap = inject<Ref<Map<number, Scheme>>>('schemeMap')!
const maxDepth = inject<number>('maxDepth', 4)

const openChildIds = ref(new Set<number>())

const sections = computed(() => {
    return [
        { key: 'prerequisites', label: 'Prerequisites', content: props.scheme.prerequisites },
        { key: 'reveal', label: 'Reveal', content: props.scheme.reveal },
        { key: 'scoring', label: 'Scoring', content: props.scheme.scoring },
        { key: 'additional', label: 'Additional VP', content: props.scheme.additional },
    ].filter(section => section.content && section.content.length > 0)
})

const nextSchemes = computed(() => {
    return [props.scheme.next_scheme_1, props.scheme.next_scheme_2, props.scheme.next_scheme_3]
        .filter((id): id is number => id !== null)
        .map(id => schemeMap.value.get(id))
        .filter((s): s is Scheme => !!s)
})

const openChildren = computed(() => {
    return nextSchemes.value.filter(s => openChildIds.value.has(s.id))
})

const atMaxDepth = computed(() => props.depth >= maxDepth - 1)

function toggleChild(id: number) {
    const next = new Set(openChildIds.value)
    if (next.has(id)) next.delete(id)
    else next.add(id)
    openChildIds.value = next
}
</script>

<template>
    <div :id="`scheme-card-${scheme.id}`" class="scroll-mt-4">
        <Card>
            <!-- Header -->
            <div class="flex items-center gap-3 px-4 py-3 border-b">
                <img
                    v-if="scheme.front_image"
                    :src="scheme.front_image"
                    :alt="scheme.title"
                    class="size-8 rounded object-cover shrink-0"
                />
                <span class="font-semibold text-sm flex-1 truncate">{{ scheme.title }}</span>
                <span class="text-[10px] text-muted-foreground tabular-nums shrink-0">
                    Level {{ depth + 1 }}
                </span>
                <Link
                    :href="route('rules.gaining-grounds.scheme', scheme.slug)"
                    class="inline-flex items-center gap-1 text-xs text-muted-foreground hover:text-primary transition-colors shrink-0"
                >
                    <ExternalLink class="size-3.5" />
                    <span class="hidden sm:inline">Full Page</span>
                </Link>
            </div>

            <!-- Content sections -->
            <CardContent v-if="sections.length > 0" class="p-4 text-sm leading-relaxed">
                <div
                    v-for="(section, idx) in sections"
                    :key="section.key"
                    :class="idx > 0 ? 'border-t pt-3 mt-3' : ''"
                >
                    <h4 class="text-xs font-semibold uppercase tracking-wide text-muted-foreground mb-1.5">
                        {{ section.label }}
                    </h4>
                    <ParsedContent :content="section.content" />
                </div>
            </CardContent>

            <!-- Next available footer -->
            <CardFooter v-if="nextSchemes.length > 0" class="border-t px-4 py-3 flex-col items-stretch gap-2">
                <span class="text-xs font-medium text-muted-foreground">Next Available</span>
                <div class="flex flex-wrap gap-1.5">
                    <button
                        v-for="next in nextSchemes"
                        :key="next.id"
                        type="button"
                        class="inline-flex items-center gap-1.5 rounded-md border bg-background px-2.5 py-1 text-xs font-medium transition-colors"
                        :class="[
                            openChildIds.has(next.id)
                                ? 'border-primary/40 text-primary hover:bg-primary/5'
                                : atMaxDepth
                                    ? 'opacity-50 cursor-not-allowed'
                                    : 'hover:bg-muted/50',
                        ]"
                        :disabled="atMaxDepth && !openChildIds.has(next.id)"
                        @click="toggleChild(next.id)"
                    >
                        <img
                            v-if="next.front_image"
                            :src="next.front_image"
                            :alt="next.title"
                            class="size-4 rounded object-cover"
                        />
                        {{ next.title }}
                        <ChevronDown
                            class="size-3 text-muted-foreground transition-transform duration-150"
                            :class="{ 'rotate-180': openChildIds.has(next.id) }"
                        />
                    </button>
                </div>
            </CardFooter>
        </Card>

        <!-- Opened children, indented with connector -->
        <div v-if="openChildren.length > 0" class="ml-4 sm:ml-6 mt-2 space-y-2 border-l-2 border-border pl-4 sm:pl-5">
            <SchemeTreeNode
                v-for="child in openChildren"
                :key="child.id"
                :scheme="child"
                :depth="depth + 1"
            />
        </div>
    </div>
</template>
