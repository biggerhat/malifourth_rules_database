<script setup lang="ts">
import ParsedContent from "@/components/ParsedContent.vue";
import { Share2, Clipboard } from "lucide-vue-next";
import {DropdownMenu, DropdownMenuContent, DropdownMenuLabel, DropdownMenuTrigger} from '@/components/ui/dropdown-menu';
import {getInitials} from "@/composables/useInitials";
import UserInfo from "@/components/UserInfo.vue";
import { ref } from "vue";
import { toast } from "vue-sonner";

const props =defineProps({
    title: {
        type: String,
        required: false,
        default() {
            return '';
        }
    },
    slug: {
        type: String,
        required: false,
        default() {
            return '';
        }
    },
    content: {
        type: [Object, Array, String],
        required: false,
        default() {
            return '';
        }
    },
    published_at: {
        type: String,
        required: false,
        default() {
            return '';
        }
    },
    published_by: {
        type: String,
        required: false,
        default() {
            return '';
        }
    }
});

const copyLink = () => {
    navigator.clipboard.writeText(route('rules.section.view', props.slug));

    toast('Link Copied');

    shareOpen.value = false;
}

const shareOpen = ref(false);

</script>

<template>
    <div>
        <div class="flex justify-between">
            <div class="border-b text-xl mb-2">{{ props.title }}</div>
            <div @click="copyLink">
                <Clipboard class="w-4" />
<!--                <DropdownMenu v-model:open="shareOpen">-->
<!--                    <DropdownMenuTrigger :as-child="true">-->
<!--                        <Share2 class="w-4" />-->
<!--                    </DropdownMenuTrigger>-->
<!--                    <DropdownMenuContent align="end" class="w-56">-->
<!--                        <DropdownMenuLabel class="p-0 font-normal">-->
<!--                            <div-->
<!--                                @click="copyLink"-->
<!--                                class="flex items-center my-auto gap-2 px-1 py-1.5 text-left text-sm"-->
<!--                            >-->
<!--                                <Clipboard /> Copy Link-->
<!--                            </div>-->
<!--                        </DropdownMenuLabel>-->
<!--                    </DropdownMenuContent>-->
<!--                </DropdownMenu>-->
            </div>
        </div>

        <ParsedContent :content="props.content" />
    </div>
</template>
