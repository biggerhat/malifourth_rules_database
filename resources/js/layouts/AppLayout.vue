<script setup lang="ts">
// import AppLayout from '@/layouts/app/AppSidebarLayout.vue';
import AppLayout from '@/layouts/app/AppHeaderLayout.vue';
import type { BreadcrumbItemType } from '@/types';
import { Link } from '@inertiajs/vue3';

interface Props {
    breadcrumbs?: BreadcrumbItemType[];
}

withDefaults(defineProps<Props>(), {
    breadcrumbs: () => [],
});
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbs">
        <slot />
    </AppLayout>
    <footer class="border-t border-dashed py-4 print:hidden">
        <div class="max-w-4xl mx-auto px-4 flex flex-col items-center gap-2 text-xs text-muted-foreground text-center">
            <nav class="flex items-center gap-x-4">
                <Link :href="route('changelog.index')" class="hover:underline hover:text-foreground">Changelog</Link>
                <Link :href="route('transparency.index')" class="hover:underline hover:text-foreground">Transparency</Link>
            </nav>
            <div>
                Materials used are copyrighted works of <a href="https://www.wyrd-games.net/" target="_wyrd" class="hover:underline" title="Wyrd Miniatures">Wyrd Miniatures, LLC</a>, in the United
                States of America and elsewhere. All rights reserved, Wyrd Miniatures, LLC.
            </div>
        </div>
    </footer>
</template>
