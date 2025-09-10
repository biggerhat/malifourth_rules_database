<script setup lang="ts">
// import AppLayout from '@/layouts/app/AppSidebarLayout.vue';
import AppLayout from '@/layouts/app/AppHeaderLayout.vue';
import type { BreadcrumbItemType } from '@/types';

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
    <footer class="py-3 p-toolbar p-component border-dashed border-t !rounded-none !border-l-0 !border-r-0 !border-b-0 !block">
        <div class="container mx-auto">
            <div class="block mx-auto px-2 my-2 text-xs lg:px-0 text-center">
                Materials used are copyrighted works of <a href="https://www.wyrd-games.net/" target="_wyrd" class="hover:underline" alt="Wyrd Miniatures">Wyrd Miniatures, LLC</a>, in the United
                States of America and elsewhere. All rights reserved, Wyrd Miniatures, LLC.
            </div>
        </div>
    </footer>
</template>
