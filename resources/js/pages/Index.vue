<script setup lang="ts">
import { Search } from 'lucide-vue-next'
import { Input } from '@/components/ui/input'
import { ref } from "vue";
import {
    Card,
    CardContent,
    CardDescription,
    CardFooter,
    CardHeader,
    CardTitle,
} from '@/components/ui/card';

const raw_test = ref('{{b}}Test{{/b}} This is normal text blah {{i}}Blah{{/i}} blah {{indexTooltip=7-measuring-example-1}}Measuring Example 1  (Published){{/indexTooltip}}');

const props = defineProps({
    wyrd_news: {
        type: [Object, Array],
        required: false,
        default() {
            return {};
        }
    }
});

</script>

<template>
    <div class="min-h-screen pt-6">
        <img src="/Images/Logos/M4E-Logo_Bayou-B.png" alt="Malifaux Fourth Edition" />
        <div class="relative w-full items-center text-4xl mx-auto text-center mt-6">
            Comprehensive Rules Database
        </div>
        <div class="size-full mt-6">
            <div class="relative w-full max-w-xl mt-12 items-center mx-auto my-auto">
                <Input id="search" type="text" autofocus placeholder="Search..." class="pl-10" />
                <span class="absolute start-0 inset-y-0 flex items-center justify-center px-2">
                    <Search class="size-6 text-muted-foreground" />
                </span>
            </div>
        </div>
        <div class="container grid lg:grid-cols-2 mt-6 gap-2">
            <Card class="mx-2 lg:mx-0">
                <CardHeader>
                    <CardTitle>Latest Updates</CardTitle>
                </CardHeader>
                <CardContent>

                </CardContent>
            </Card>
            <Card class="mx-2 lg:mx-0">
                <CardHeader class="my-auto">
                    <CardTitle><img src="/Images/wyrd-logo.png" alt="Wyrd Games" class="h-8 my-auto inline-block mr-2" />Latest Wyrd News</CardTitle>
                </CardHeader>
                <CardContent>
                    <a v-for="news in props.wyrd_news" :href="news.url" :key="news.url" target="_blank" class="hover:bg-secondary px-1 py-2 block">
                        {{ news.title }}
                    </a>
                </CardContent>
            </Card>
        </div>
    </div>
</template>
