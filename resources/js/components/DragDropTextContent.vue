<script setup lang="ts">
import {onMounted, ref} from 'vue'
import draggable from "vuedraggable";
import { CircleXIcon, CircleEllipsisIcon } from "lucide-vue-next";
import Mask from "@/components/symbols/Mask.vue";

const props = defineProps({
    content: {
        type: [Object, Array, String],
        required: false,
    }
});

const textContent = ref([]);
onMounted(() => {
    textContent.value = JSON.parse(JSON.stringify(props.content));
});

const emit = defineEmits([
    'update:textOrder'
]);

const textChange = (event) => {
    emit('update:textOrder', textContent.value);
}

const replaceNewline = (text) => {
    return text.replace(/\n/g, '');
};
</script>

<template>
    <div class="m-2 leading-8">
        <draggable
            @change="textChange"
            v-model="textContent"
            item-key="text"
        >
            <template #item="{element}">
                <span v-if="element.type === 'text'" class="border-0">
                {{ ' ' + replaceNewline(element.content) }}
                </span>
                <span v-else-if="element.type === 'br'" class="border border-blue-500">
                    [line break]<br />
                </span>
                <div
                    v-else-if="element.type === 'indexTooltip'"
                    class="bg-secondary p-1 rounded inline"
                >
                    {{ ' ' + element.content.text }} (Tooltip)
                    <div class="inline"><CircleEllipsisIcon class="w-5 text-green-500 inline" /></div>
                </div>
                <div
                    v-else-if="element.type === 'mask'"
                    class="bg-secondary p-1 rounded inline"
                >
                    <Mask class="h-4" />
                    <div class="inline"><CircleEllipsisIcon class="w-5 text-green-500 inline" /></div>
                </div>
                <div
                    v-else
                    class="bg-secondary p-1 rounded inline"
                >
                    {{ ' ' + element.content }}
                    <div class="inline"><CircleEllipsisIcon class="w-5 text-green-500 inline" /></div>
                </div>
            </template>
        </draggable>
    </div>
</template>
