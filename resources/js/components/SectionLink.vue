<script setup lang="ts">
import {
    Tooltip,
    TooltipContent,
    TooltipProvider,
    TooltipTrigger
} from '@/components/ui/tooltip'
import { onMounted, computed, defineProps } from "vue";
import ParsedContent from "@/components/ParsedContent.vue";

const props = defineProps({
    slug: String,
    text: {
        type: [Object,Array,String],
        required: false,
        default() {
            return '';
        }
    },
    title: {
        type: String,
        required: false,
        default() {
            return '';
        }
    }
});

</script>

<template>
    <Link :href="route('rules.section.view', props.slug)" class="text-blue-500 hover:underline">
        {{ props.text }}
    </Link>
</template>
