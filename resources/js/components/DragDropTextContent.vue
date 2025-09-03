<script setup lang="ts">
import {onMounted, ref} from 'vue'
import draggable from "vuedraggable";
import { CircleXIcon, CircleEllipsisIcon } from "lucide-vue-next";
import Mask from "@/components/symbols/Mask.vue";
import SignatureAction from "@/components/symbols/SignatureAction.vue";
import Soulstone from "@/components/symbols/Soulstone.vue";
import Tome from "@/components/symbols/Tome.vue";
import UnusualDefense from "@/components/symbols/UnusualDefense.vue";
import Ram from "@/components/symbols/Ram.vue";
import Pulse from "@/components/symbols/Pulse.vue";
import Positive from "@/components/symbols/Positive.vue";
import Fortitude from "@/components/symbols/Fortitude.vue";
import Negative from "@/components/symbols/Negative.vue";
import Missile from "@/components/symbols/Missile.vue";
import Melee from "@/components/symbols/Melee.vue";
import Warding from "@/components/symbols/Warding.vue";
import Magic from "@/components/symbols/Magic.vue";
import Crow from "@/components/symbols/Crow.vue";

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
<!--                    <div class="inline"><CircleEllipsisIcon class="w-5 text-green-500 inline" /></div>-->
                </div>
                <div
                    v-else-if="element.type === 'sectionLink' || element.type === 'pageLink' ||element.type === 'Link'"
                    class="bg-secondary p-1 rounded inline"
                >
                    {{ ' ' + element.content.text }} (Link)
                </div>
                <div
                    v-else-if="element.type === 'mask'"
                    class="bg-secondary p-1 rounded inline"
                >
                    <Mask class="h-4" />
<!--                    <div class="inline"><CircleEllipsisIcon class="w-5 text-green-500 inline" /></div>-->
                </div>
                <div
                    v-else-if="element.type === 'crow'"
                    class="bg-secondary p-1 rounded inline"
                >
                    <Crow class="h-4" />
<!--                    <div class="inline"><CircleEllipsisIcon class="w-5 text-green-500 inline" /></div>-->
                </div>
                <div
                    v-else-if="element.type === 'magic'"
                    class="bg-secondary p-1 rounded inline"
                >
                    <Magic class="h-4" />
<!--                    <div class="inline"><CircleEllipsisIcon class="w-5 text-green-500 inline" /></div>-->
                </div>
                <div
                    v-else-if="element.type === 'warding'"
                    class="bg-secondary p-1 rounded inline"
                >
                    <Warding class="h-4" />
<!--                    <div class="inline"><CircleEllipsisIcon class="w-5 text-green-500 inline" /></div>-->
                </div>
                <div
                    v-else-if="element.type === 'melee'"
                    class="bg-secondary p-1 rounded inline"
                >
                    <Melee class="h-4" />
<!--                    <div class="inline"><CircleEllipsisIcon class="w-5 text-green-500 inline" /></div>-->
                </div>
                <div
                    v-else-if="element.type === 'missile'"
                    class="bg-secondary p-1 rounded inline"
                >
                    <Missile class="h-4" />
<!--                    <div class="inline"><CircleEllipsisIcon class="w-5 text-green-500 inline" /></div>-->
                </div>
                <div
                    v-else-if="element.type === 'negative'"
                    class="bg-secondary p-1 rounded inline"
                >
                    <Negative class="h-4" />
<!--                    <div class="inline"><CircleEllipsisIcon class="w-5 text-green-500 inline" /></div>-->
                </div>
                <div
                    v-else-if="element.type === 'fortitude'"
                    class="bg-secondary p-1 rounded inline"
                >
                    <Fortitude class="h-4" />
<!--                    <div class="inline"><CircleEllipsisIcon class="w-5 text-green-500 inline" /></div>-->
                </div>
                <div
                    v-else-if="element.type === 'positive'"
                    class="bg-secondary p-1 rounded inline"
                >
                    <Positive class="h-4" />
<!--                    <div class="inline"><CircleEllipsisIcon class="w-5 text-green-500 inline" /></div>-->
                </div>
                <div
                    v-else-if="element.type === 'pulse'"
                    class="bg-secondary p-1 rounded inline"
                >
                    <Pulse class="h-4" />
<!--                    <div class="inline"><CircleEllipsisIcon class="w-5 text-green-500 inline" /></div>-->
                </div>
                <div
                    v-else-if="element.type === 'ram'"
                    class="bg-secondary p-1 rounded inline"
                >
                    <Ram class="h-4" />
<!--                    <div class="inline"><CircleEllipsisIcon class="w-5 text-green-500 inline" /></div>-->
                </div>
                <div
                    v-else-if="element.type === 'signatureaction'"
                    class="bg-secondary p-1 rounded inline"
                >
                    <SignatureAction class="h-4" />
<!--                    <div class="inline"><CircleEllipsisIcon class="w-5 text-green-500 inline" /></div>-->
                </div>
                <div
                    v-else-if="element.type === 'soulstone'"
                    class="bg-secondary p-1 rounded inline"
                >
                    <Soulstone class="h-4" />
<!--                    <div class="inline"><CircleEllipsisIcon class="w-5 text-green-500 inline" /></div>-->
                </div>
                <div
                    v-else-if="element.type === 'tome'"
                    class="bg-secondary p-1 rounded inline"
                >
                    <Tome class="h-4" />
<!--                    <div class="inline"><CircleEllipsisIcon class="w-5 text-green-500 inline" /></div>-->
                </div>
                <div
                    v-else-if="element.type === 'unusualdefense'"
                    class="bg-secondary p-1 rounded inline"
                >
                    <UnusualDefense class="h-4" />
<!--                    <div class="inline"><CircleEllipsisIcon class="w-5 text-green-500 inline" /></div>-->
                </div>
                <div
                    v-else
                    class="bg-secondary p-1 rounded inline"
                >
                    {{ ' ' + element.content }}
<!--                    <div class="inline"><CircleEllipsisIcon class="w-5 text-green-500 inline" /></div>-->
                </div>
            </template>
        </draggable>
    </div>
</template>
