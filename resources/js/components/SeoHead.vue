<script setup lang="ts">
import { Head } from '@inertiajs/vue3';

const props = defineProps<{
    title: string;
    description?: string | null;
}>();
</script>

<template>
    <Head :title="props.title">
        <meta v-if="props.description" head-key="description" name="description" :content="props.description" />
        <meta head-key="og:title" property="og:title" :content="props.title" />
        <meta v-if="props.description" head-key="og:description" property="og:description" :content="props.description" />
        <meta head-key="og:type" property="og:type" content="website" />
        <meta head-key="twitter:card" name="twitter:card" content="summary" />
    </Head>
</template>
