<script setup lang="ts">
import { Card, CardHeader, CardTitle } from "@/components/ui/card"
import { Tabs, TabsList, TabsTrigger, TabsContent } from "@/components/ui/tabs"
import { ref } from "vue"

const props = defineProps({
    query: String,
    pages: {
        type: [Object, Array],
        required: false,
        default() {
            return {};
        }
    },
    sections: {
        type: [Object, Array],
        required: false,
        default() {
            return {};
        }
    },
    indices: {
        type: [Object, Array],
        required: false,
        default() {
            return {};
        }
    },
    faqs: {
        type: [Object, Array],
        required: false,
        default() {
            return {};
        }
    },
})

const activeTab = ref("pages")
</script>

<template>
    <div class="p-6 space-y-6">
        <h1 class="text-2xl font-bold">Search results for "{{ props.query }}"</h1>
        <Tabs v-model="activeTab">
            <TabsList>
                <TabsTrigger value="pages">Pages ({{ props.pages.length }})</TabsTrigger>
                <TabsTrigger value="sections">Sections ({{ props.sections.length }})</TabsTrigger>
                <TabsTrigger value="indices">Indices ({{ props.indices.length }})</TabsTrigger>
                <TabsTrigger value="faqs">FAQs ({{ props.faqs.length }})</TabsTrigger>
            </TabsList>

            <!-- Posts -->
            <TabsContent value="pages">
                <div v-if="props.pages.length" class="grid gap-4">
                    <Card v-for="page in props.pages" :key="page.id" class="shadow-md">
                        <CardHeader>
                            <CardTitle><Link :href="route('rules.page.view', page.slug)"><span v-html="page.title"></span></Link></CardTitle>
                        </CardHeader>
                    </Card>
                </div>
                <p v-else class="text-gray-500">No pages found.</p>
            </TabsContent>

            <!-- Users -->
            <TabsContent value="sections">
                <div v-if="props.sections.length" class="grid gap-4">
                    <Card v-for="section in props.sections" :key="section.id" class="shadow-md">
                        <CardHeader>
                            <CardTitle><Link :href="route('rules.section.view', section.slug)"><span v-html="section.title"></span></Link></CardTitle>
                        </CardHeader>
                    </Card>
                </div>
                <p v-else class="text-gray-500">No sections found.</p>
            </TabsContent>

            <!-- Comments -->
            <TabsContent value="indices">
                <div v-if="props.indices.length" class="grid gap-4">
                    <Card v-for="index in props.indices" :key="index.id" class="shadow-md">
                        <CardHeader>
                            <CardTitle><Link :href="route('rules.index.view', index.slug)"><span v-html="index.title"></span></Link></CardTitle>
                        </CardHeader>
                    </Card>
                </div>
                <p v-else class="text-gray-500">No indices found.</p>
            </TabsContent>

            <TabsContent value="faqs">
                <div v-if="props.faqs.length" class="grid gap-4">
                    <Card v-for="faq in props.faqs" :key="faq.id" class="shadow-md">
                        <CardHeader>
                            <CardTitle><Link :href="route('rules.faq.view', faq.slug)"><span v-html="faq.title"></span></Link></CardTitle>
                        </CardHeader>
                    </Card>
                </div>
                <p v-else class="text-gray-500">No FAQs found.</p>
            </TabsContent>
        </Tabs>
    </div>
</template>
