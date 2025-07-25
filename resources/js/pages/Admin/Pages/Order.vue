<script setup lang="ts">
import draggable from "vuedraggable";
import {onMounted, ref} from "vue";
import { router } from '@inertiajs/vue3';
import {
    Card,
    CardContent,
    CardDescription,
    CardFooter,
    CardHeader,
    CardTitle,
} from '@/components/ui/card';
import Button from "@/components/ui/button/Button.vue";
import {hasPermission} from "@/composables/hasPermission";
import axios from "axios";
import {
    Drawer,
    DrawerClose,
    DrawerContent,
    DrawerDescription,
    DrawerFooter,
    DrawerTrigger
} from "@/components/ui/drawer";
import {Component, Eye} from "lucide-vue-next";
import PageContent from "@/components/PageContent.vue";

const props = defineProps({
    pages: {
        type: [Object, Array],
        required: false,
        default() {
            return {};
        }
    }
})

const pageList = ref([]);

const resetList = () => {
    pageList.value = JSON.parse(JSON.stringify(props.pages));
    resetPageNumbers();
}

const resetPageNumbers = () => {
    let i = 1;
    pageList.value.forEach((page) => {
        page.page_number = i;
        i++;
    });
}

onMounted(() => {
    resetList();
});

const back = () => {
    history.back();
};

const viewData = ref(null);

const fetchViewData = (slug) => {
    axios.get(route('admin.pages.view', slug)).then((response) => {
        viewData.value = response.data;
    });
}

const saveOrder = () => {
    router.post(route('admin.pages.order.update'), pageList.value);
}
</script>

<template>
    <Head title="Set Page Order" />

    <Card>
        <CardHeader>
            <CardTitle>Set Page Order</CardTitle>
            <CardDescription>
                Drag and drop the pages to the order you want. <br />
                Click the eye to preview them to make sure you have the right page.
            </CardDescription>
        </CardHeader>
        <CardContent>
            <draggable
                v-model="pageList"
                item-key="page_number"
                @change="resetPageNumbers()"
            >
                <template #item="{element}">
                    <div class="border border-primary hover:bg-secondary mx-2 my-1 flex justify-between p-2">
                        <div class="my-auto">{{element.page_number}}. {{ element.title }}</div>
                        <div class="my-auto">
                            <Drawer>
                                <DrawerTrigger as-child>
                                    <Button class="bg-purple-500 mx-2" @click="fetchViewData(element.slug)">
                                        <Eye class="h-4 w-4" />
                                    </Button>
                                </DrawerTrigger>
                                <DrawerContent>
                                    <div class="mx-auto w-full mt-2 container overflow-y-auto">
                                        <DrawerDescription>
                                            <component
                                                v-if="viewData"
                                                :is="PageContent"
                                                v-bind="viewData"
                                            />
                                        </DrawerDescription>
                                        <DrawerFooter>
                                            <DrawerClose as-child>
                                                <Button type="button" class="mx-auto w-25" variant="destructive">
                                                    Close
                                                </Button>
                                            </DrawerClose>
                                        </DrawerFooter>
                                    </div>
                                </DrawerContent>
                            </Drawer>
                        </div>
                    </div>
                </template>
            </draggable>
        </CardContent>
        <CardFooter>
            <div class="flex ml-auto my-auto">
                <Button class="bg-destructive my-auto" @click="resetList()">
                    Reset List
                </Button>
                <Button class="bg-green-500 my-auto ml-2" @click="saveOrder()">
                    Save Order
                </Button>
                <Button @click="back()" class="bg-destructive my-auto ml-2">
                    Cancel
                </Button>
            </div>
        </CardFooter>
    </Card>
</template>
