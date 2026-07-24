<script setup lang="ts">
import AppContent from '@/components/AppContent.vue';
import AppHeader from '@/components/AppHeader.vue';
import AppShell from '@/components/AppShell.vue';
import type { BreadcrumbItemType } from '@/types';
import AlertMessage from "@/components/AlertMessage.vue";

import { Toaster } from '@/components/ui/sonner';
import 'vue-sonner/style.css'

interface Props {
    breadcrumbs?: BreadcrumbItemType[];
}

withDefaults(defineProps<Props>(), {
    breadcrumbs: () => [],
});
</script>

<template>
    <AppShell variant="header" class="flex-col">
        <a
            href="#main-content"
            class="sr-only focus:not-sr-only focus:fixed focus:top-2 focus:left-2 focus:z-[100] focus:rounded-md focus:bg-primary focus:px-4 focus:py-2 focus:text-sm focus:font-medium focus:text-primary-foreground focus:shadow-lg"
        >Skip to main content</a>
        <AppHeader :breadcrumbs="breadcrumbs" />
        <AppContent id="main-content" class="mt-6">
            <AlertMessage v-if="$page.props.flash?.message" :message="$page.props.flash.message" :message-title="$page.props.flash.messageTitle ?? null" :message-type="$page.props.flash.messageType ?? null" />
            <slot />
        </AppContent>
        <Toaster />
    </AppShell>
</template>
