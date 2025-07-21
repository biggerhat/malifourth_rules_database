<script setup lang="ts">
import { Info } from "lucide-vue-next";
import Button from "@/components/ui/button/Button.vue";
import {
    Drawer,
    DrawerClose,
    DrawerContent,
    DrawerDescription,
    DrawerFooter,
    DrawerHeader,
    DrawerTitle,
    DrawerTrigger,
} from '@/components/ui/drawer'

const props = defineProps({
    title: {
        type: String,
        required: true,
    },
    internal_note: {
        type: String,
        required: false,
        default() {
            return '';
        }
    }
})

</script>

<template>
    <Drawer>
        <DrawerTrigger as-child>
            <div class="w-full hover:cursor-pointer">
                {{ props.title }} <Info class="inline-block text-green-500 pb-2" />
            </div>
        </DrawerTrigger>
        <DrawerContent>
            <div class="mx-auto w-full max-w-sm">
                <DrawerHeader>
                    <DrawerTitle>Internal Notes</DrawerTitle>
                </DrawerHeader>
                <DrawerDescription class="px-6">
                    {{ props.internal_note }}
                </DrawerDescription>
                <DrawerFooter>
                    <DrawerClose as-child>
                        <Button type="button" variant="destructive">
                            Cancel
                        </Button>
                    </DrawerClose>
                </DrawerFooter>
            </div>
        </DrawerContent>
    </Drawer>
</template>
