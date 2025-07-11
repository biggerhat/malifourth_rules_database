<script setup lang="ts">
import AppContent from '@/components/AppContent.vue';
import AppHeader from '@/components/AppHeader.vue';
import AppShell from '@/components/AppShell.vue';
import type { BreadcrumbItemType } from '@/types';
import AlertMessage from "@/components/AlertMessage.vue";
import { hasPermission } from "@/composables/hasPermission";

interface Props {
    breadcrumbs?: BreadcrumbItemType[];
}

withDefaults(defineProps<Props>(), {
    breadcrumbs: () => [],
});
</script>

<template>
    <AppShell class="flex-col">
        <AppHeader :breadcrumbs="breadcrumbs" />
        <AppContent class="mt-6">
            <AlertMessage v-if="$page.props.flash?.message" :message="$page.props.flash.message" :message-title="$page.props.flash.messageTitle ?? null" :message-type="$page.props.flash.messageType ?? null" />
            <slot />
        </AppContent>
    </AppShell>
</template>
