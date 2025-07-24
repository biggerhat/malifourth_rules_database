<script setup lang="ts">
import PlaceholderPattern from '../components/PlaceholderPattern.vue';
import {Head} from "@inertiajs/vue3";
import { Search } from 'lucide-vue-next'
import { Input } from '@/components/ui/input'
import { ref } from "vue";
import TextEditor from "@/components/TextEditor.vue";
import InputError from "@/components/InputError.vue";

const raw_test = ref('{{b}}Test{{/b}} This is normal text blah {{i}}Blah{{/i}} blah {{indexTooltip=7-measuring-example-1}}Measuring Example 1  (Published){{/indexTooltip}}');

</script>

<template>
    <div class="min-h-screen border border-primary pt-6">
        <img src="/Images/Logos/M4E-Logo_Bayou-B.png" alt="Malifaux Fourth Edition" />
        <div class="relative w-full items-center text-4xl mx-auto text-center mt-6">
            Comprehensive Rules Database
        </div>
        <div class="size-full mt-6">
<!--            <div class="flex flex-col space-y-1.5">-->
<!--                <TextEditor-->
<!--                    placeholder="Add Change Notes"-->
<!--                    label="Change Notes"-->
<!--                    v-model="raw_test"-->
<!--                />-->
<!--            </div>-->
            <div class="relative w-full max-w-xl mt-12 items-center mx-auto my-auto">
                <Input id="search" type="text" placeholder="Search..." class="pl-10" />
                <span class="absolute start-0 inset-y-0 flex items-center justify-center px-2">
                <Search class="size-6 text-muted-foreground" />
            </span>
            </div>
        </div>
    </div>
</template>
