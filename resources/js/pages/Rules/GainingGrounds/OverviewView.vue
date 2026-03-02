<script setup lang="ts">
import SchemeTreeExplorer from '@/components/SchemeTree/SchemeTreeExplorer.vue'
import ScrollToTop from '@/components/ScrollToTop.vue'
import { Tabs, TabsContent, TabsList, TabsTrigger } from '@/components/ui/tabs'
import { ChevronLeft, ChevronRight } from 'lucide-vue-next'

const props = defineProps({
    season: {
        type: Object,
        required: true,
    },
    strategies: {
        type: Array,
        required: false,
        default: () => [],
    },
    schemes: {
        type: Array,
        required: false,
        default: () => [],
    },
})

const suitAccent = (suit: string) => {
    switch (suit) {
        case 'rams': return 'border-l-red-500 dark:border-l-red-400'
        case 'crows': return 'border-l-green-500 dark:border-l-green-400'
        case 'masks': return 'border-l-purple-500 dark:border-l-purple-400'
        case 'tomes': return 'border-l-blue-500 dark:border-l-blue-400'
        default: return ''
    }
}

const suitBadge = (suit: string) => {
    switch (suit) {
        case 'rams': return 'bg-red-100 text-red-800 dark:bg-red-900/50 dark:text-red-200'
        case 'crows': return 'bg-green-100 text-green-800 dark:bg-green-900/50 dark:text-green-200'
        case 'masks': return 'bg-purple-100 text-purple-800 dark:bg-purple-900/50 dark:text-purple-200'
        case 'tomes': return 'bg-blue-100 text-blue-800 dark:bg-blue-900/50 dark:text-blue-200'
        default: return 'bg-muted text-muted-foreground'
    }
}
</script>

<template>
    <Head :title="`${props.season.title} — Overview`" />

    <div class="max-w-4xl mx-auto px-2 sm:px-4 text-primary leading-6 text-md">
        <!-- Back link -->
        <div class="pt-4 mb-2">
            <Link
                :href="route('rules.gaining-grounds.season', props.season.slug)"
                class="inline-flex items-center gap-1 text-sm text-muted-foreground hover:text-primary transition-colors"
            >
                <ChevronLeft class="size-4" />
                {{ props.season.title }}
            </Link>
        </div>

        <!-- Banner -->
        <div class="w-full text-center text-xl py-4">
            <img src="/Images/page_banner_top.png" alt="" class="w-3/4 sm:w-1/2 lg:w-1/3 mx-auto" />
            <span>Season Overview</span>
            <img src="/Images/page_banner_bottom.png" alt="" class="w-3/4 sm:w-1/2 lg:w-1/3 mx-auto" />
        </div>

        <!-- Tabs -->
        <Tabs default-value="overview" class="mb-8">
            <TabsList class="w-full grid grid-cols-2">
                <TabsTrigger value="overview">Strategies &amp; Schemes</TabsTrigger>
                <TabsTrigger value="explorer">Scheme Explorer</TabsTrigger>
            </TabsList>

            <!-- Tab 1: Strategies & Schemes -->
            <TabsContent value="overview" class="mt-6">
                <!-- Strategies -->
                <div v-if="props.strategies.length > 0" class="mb-8">
                    <h2 class="font-semibold text-base sm:text-lg mb-4 pb-2 border-b">Strategies</h2>
                    <div class="grid grid-cols-2 lg:grid-cols-4 gap-3">
                        <Link
                            v-for="strategy in props.strategies"
                            :key="strategy.id"
                            :href="route('rules.gaining-grounds.strategy', strategy.slug)"
                            class="group block"
                        >
                            <div
                                class="rounded-lg border border-l-4 bg-card p-3 sm:p-4 h-full transition-colors hover:bg-muted/50"
                                :class="suitAccent(strategy.suit)"
                            >
                                <img v-if="strategy.front_image" :src="strategy.front_image" :alt="strategy.title" class="w-full rounded mb-3" />
                                <div class="text-sm font-medium group-hover:underline">{{ strategy.title }}</div>
                                <span
                                    v-if="strategy.suit_label"
                                    class="inline-block mt-1.5 text-[11px] px-1.5 py-0.5 rounded font-medium"
                                    :class="suitBadge(strategy.suit)"
                                >
                                    {{ strategy.suit_label }}
                                </span>
                            </div>
                        </Link>
                    </div>
                </div>

                <!-- Schemes -->
                <div v-if="props.schemes.length > 0">
                    <h2 class="font-semibold text-base sm:text-lg mb-4 pb-2 border-b">Schemes</h2>
                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-2">
                        <Link
                            v-for="scheme in props.schemes"
                            :key="scheme.id"
                            :href="route('rules.gaining-grounds.scheme', scheme.slug)"
                            class="flex items-center gap-3 rounded-lg border bg-card px-3 py-2.5 hover:bg-muted/50 transition-colors"
                        >
                            <img v-if="scheme.front_image" :src="scheme.front_image" :alt="scheme.title" class="size-8 rounded object-cover shrink-0" />
                            <span class="text-sm font-medium truncate">{{ scheme.title }}</span>
                            <ChevronRight class="size-3.5 shrink-0 text-muted-foreground ml-auto" />
                        </Link>
                    </div>
                </div>
            </TabsContent>

            <!-- Tab 2: Scheme Explorer -->
            <TabsContent value="explorer" class="mt-6">
                <SchemeTreeExplorer :schemes="props.schemes" />
            </TabsContent>
        </Tabs>

        <ScrollToTop />
    </div>
</template>
