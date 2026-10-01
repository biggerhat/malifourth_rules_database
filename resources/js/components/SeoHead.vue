<script setup lang="ts">
import { Head } from '@inertiajs/vue3';

const props = defineProps<{
    title: string;
    description?: string | null;
    /** Absolute URL of the canonical/live version of this content. Omit on pages that are already the live version and don't need one. */
    canonical?: string | null;
    /** Set true on superseded/history views so they don't compete with the live page for ranking. */
    noindex?: boolean;
    /** Absolute URL of a social preview image. Falls back to no image tag when omitted. */
    ogImage?: string | null;
}>();
</script>

<template>
    <Head :title="props.title">
        <meta v-if="props.description" head-key="description" name="description" :content="props.description" />
        <link v-if="props.canonical" head-key="canonical" rel="canonical" :href="props.canonical" />
        <meta v-if="props.noindex" head-key="robots" name="robots" content="noindex, follow" />
        <meta head-key="og:title" property="og:title" :content="props.title" />
        <meta v-if="props.description" head-key="og:description" property="og:description" :content="props.description" />
        <meta head-key="og:type" property="og:type" content="website" />
        <meta v-if="props.ogImage" head-key="og:image" property="og:image" :content="props.ogImage" />
        <meta head-key="twitter:card" :name="'twitter:card'" :content="props.ogImage ? 'summary_large_image' : 'summary'" />
        <meta v-if="props.ogImage" head-key="twitter:image" name="twitter:image" :content="props.ogImage" />
    </Head>
</template>
