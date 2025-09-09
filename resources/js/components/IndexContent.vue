<script setup lang="ts">
import ParsedContent from "@/components/ParsedContent.vue";
import {
    Card,
    CardContent,
    CardDescription,
    CardFooter,
    CardHeader,
    CardTitle,
} from '@/components/ui/card'
import {hasPermission} from "@/composables/hasPermission";
import {CircleX} from "lucide-vue-next";
import {Select, SelectContent, SelectItem, SelectTrigger, SelectValue} from "@/components/ui/select";
import {Button} from "@/components/ui/button";
import {
    Drawer,
    DrawerClose,
    DrawerContent,
    DrawerDescription,
    DrawerFooter,
    DrawerHeader, DrawerTitle,
    DrawerTrigger
} from "@/components/ui/drawer";
import {Switch} from "@/components/ui/switch";
import {Label} from "@/components/ui/label";

const props =defineProps({
    title: {
        type: String,
        required: false,
        default() {
            return '';
        }
    },
    type: {
        type: String,
        required: false,
        default() {
            return 'text';
        }
    },
    content: {
        type: [Object, Array, String],
        required: false,
        default() {
            return '';
        }
    },
    image: {
        type: String,
        required: false,
        default() {
            return null;
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

</script>

<template>
    <div v-if="props.type === 'image'">
        <Drawer>
            <DrawerTrigger>
                <img :src="props.image" :alt="props.title" class="mx-auto" v-if="props.type === 'image'" />
            </DrawerTrigger>
            <DrawerContent class="max-w-lg mx-auto">
                <DrawerHeader>
                    <DrawerTitle>{{ props.title }}</DrawerTitle>
                    <DrawerDescription>
                        <img :src="props.image" :alt="props.title" class="mx-auto" v-if="props.type === 'image'" />
                    </DrawerDescription>
                </DrawerHeader>
                <DrawerFooter class="container grid">
                    <DrawerClose>
                        <Button variant="destructive" class="w-full mx-auto">
                            Close
                        </Button>
                    </DrawerClose>
                </DrawerFooter>
            </DrawerContent>
        </Drawer>
    </div>
    <div v-if="props.type === 'text'">
        <strong>{{ props.title }}:</strong>
        <div class="px-2">
            <ParsedContent :content="props.content" v-if="props.type === 'text'" />
        </div>
    </div>
</template>
