<script setup lang="ts">
import UserInfo from '@/components/UserInfo.vue';
import { DropdownMenuGroup, DropdownMenuItem, DropdownMenuLabel, DropdownMenuSeparator } from '@/components/ui/dropdown-menu';
import type { User } from '@/types';
import { Link, router } from '@inertiajs/vue3';
import { LogOut, Settings, PencilRuler } from 'lucide-vue-next';
import { hasPermission } from "@/composables/hasPermission";

interface Props {
    user: User;
}

const handleLogout = () => {
    router.flushAll();
};

defineProps<Props>();
</script>

<template>
    <DropdownMenuLabel class="p-0 font-normal">
        <div class="flex items-center gap-2 px-1 py-1.5 text-left text-sm">
            <UserInfo :user="user" :show-email="true" />
        </div>
    </DropdownMenuLabel>
    <DropdownMenuSeparator />
    <DropdownMenuGroup>
        <DropdownMenuItem :as-child="true">
            <Link class="block w-full" :href="route('profile.edit')" prefetch as="button">
                <Settings class="mr-2 h-4 w-4" />
                Settings
            </Link>
        </DropdownMenuItem>
    </DropdownMenuGroup>
    <DropdownMenuSeparator />
    <DropdownMenuGroup>
        <DropdownMenuItem :as-child="true" v-if="hasPermission('view_user')">
            <Link class="block w-full" :href="route('admin.users.index')" prefetch as="button">
                <PencilRuler class="mr-2 h-4 w-4" />
                Users
            </Link>
        </DropdownMenuItem>
        <DropdownMenuItem :as-child="true" v-if="hasPermission('view_role')">
            <Link class="block w-full" :href="route('admin.roles.index')" prefetch as="button">
                <PencilRuler class="mr-2 h-4 w-4" />
                Roles
            </Link>
        </DropdownMenuItem>
    </DropdownMenuGroup>
    <DropdownMenuSeparator />
    <DropdownMenuGroup>
        <DropdownMenuItem :as-child="true" v-if="hasPermission('view_batch')">
            <Link class="block w-full" :href="route('admin.batches.index')" prefetch as="button">
                <PencilRuler class="mr-2 h-4 w-4" />
                Batches
            </Link>
        </DropdownMenuItem>
        <DropdownMenuItem :as-child="true">
            <Link class="block w-full" :href="route('admin.approvals.index')" prefetch as="button">
                <PencilRuler class="mr-2 h-4 w-4" />
                Approvals
            </Link>
        </DropdownMenuItem>
        <DropdownMenuItem :as-child="true" v-if="hasPermission('view_index')">
            <Link class="block w-full" :href="route('admin.indices.index')" prefetch as="button">
                <PencilRuler class="mr-2 h-4 w-4" />
                Indexes
            </Link>
        </DropdownMenuItem>
        <DropdownMenuItem :as-child="true" v-if="hasPermission('view_section')">
            <Link class="block w-full" :href="route('admin.sections.index')" prefetch as="button">
                <PencilRuler class="mr-2 h-4 w-4" />
                Sections
            </Link>
        </DropdownMenuItem>
        <DropdownMenuItem :as-child="true" v-if="hasPermission('view_page')">
            <Link class="block w-full" :href="route('admin.pages.index')" prefetch as="button">
                <PencilRuler class="mr-2 h-4 w-4" />
                Pages
            </Link>
        </DropdownMenuItem>
    </DropdownMenuGroup>
    <DropdownMenuSeparator />
    <DropdownMenuItem :as-child="true">
        <Link class="block w-full" method="post" :href="route('logout')" @click="handleLogout" as="button">
            <LogOut class="mr-2 h-4 w-4" />
            Log out
        </Link>
    </DropdownMenuItem>
</template>
