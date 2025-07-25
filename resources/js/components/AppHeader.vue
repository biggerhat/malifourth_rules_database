<script setup lang="ts">
import AppLogo from '@/components/AppLogo.vue';
import AppLogoIcon from '@/components/AppLogoIcon.vue';
import Breadcrumbs from '@/components/Breadcrumbs.vue';
import { Avatar, AvatarFallback, AvatarImage } from '@/components/ui/avatar';
import { Button } from '@/components/ui/button';
import { DropdownMenu, DropdownMenuContent, DropdownMenuTrigger } from '@/components/ui/dropdown-menu';
import { NavigationMenu, NavigationMenuItem, NavigationMenuList, navigationMenuTriggerStyle } from '@/components/ui/navigation-menu';
import { Sheet, SheetContent, SheetHeader, SheetTitle, SheetTrigger } from '@/components/ui/sheet';
import { Tooltip, TooltipContent, TooltipProvider, TooltipTrigger } from '@/components/ui/tooltip';
import UserMenuContent from '@/components/UserMenuContent.vue';
import { getInitials } from '@/composables/useInitials';
import type { BreadcrumbItem, NavItem } from '@/types';
import { Link, usePage } from '@inertiajs/vue3';
import { BookOpen, Folder, LayoutGrid, Menu, Search } from 'lucide-vue-next';
import {computed, onMounted, ref} from 'vue';
import { router } from "@inertiajs/vue3";
import {
    Command,
    CommandDialog,
    CommandEmpty,
    CommandGroup,
    CommandInput,
    CommandItem,
    CommandList,
    CommandSeparator,
} from '@/components/ui/command'
import axios from 'axios';
import {ZiggyVue} from "ziggy-js";

interface Props {
    breadcrumbs?: BreadcrumbItem[];
}

const props = withDefaults(defineProps<Props>(), {
    breadcrumbs: () => [],
});

const open = ref(false);
const commandSearch = ref({});

const commandRoute = (route) => {
    router.get(route);
    open.value = false;
};

function toggleDialog() {
    if (!commandSearch.value.length) {
        axios.get(route('command'))
            .then(function (response) {
                commandSearch.value = response.data;
            });
    }
    open.value = true;
}

const page = usePage();
const auth = computed(() => page.props.auth);

const isCurrentRoute = computed(() => (url: string) => route().current(url));

const activeItemStyles = computed(
    () => (url: string) => (isCurrentRoute.value(url) ? 'text-neutral-900 dark:bg-neutral-800 dark:text-neutral-100' : ''),
);

const mainNavItems: NavItem[] = [
    {
        title: 'Rules',
        href: route('rules.index'),
        route: 'rules.index',
        icon: LayoutGrid,
    },{
        title: 'Gaining Grounds',
        href: route('rules.index'),
        route: 'dashboard',
        icon: LayoutGrid,
    },{
        title: 'FAQ',
        href: route('rules.index'),
        route: 'dashboard',
        icon: LayoutGrid,
    },{
        title: 'Errata',
        href: route('rules.index'),
        route: 'dashboard',
        icon: LayoutGrid,
    },
];

const rightNavItems: NavItem[] = [];
</script>

<template>
    <div>
        <div class="border-b border-sidebar-border/80">
            <div class="mx-auto flex h-16 items-center px-4 md:max-w-7xl">
                <!-- Mobile Menu -->
                <div class="lg:hidden">
                    <Sheet>
                        <SheetTrigger :as-child="true">
                            <Button variant="ghost" size="icon" class="mr-2 h-9 w-9">
                                <Menu class="h-5 w-5" />
                            </Button>
                        </SheetTrigger>
                        <SheetContent side="left" class="w-[300px] p-6">
                            <SheetTitle class="sr-only">Navigation Menu</SheetTitle>
                            <SheetHeader class="flex justify-start text-left">
                                <AppLogoIcon class="size-6 fill-current text-black dark:text-white" />
                            </SheetHeader>
                            <div class="flex h-full flex-1 flex-col justify-between space-y-4 py-6">
                                <nav class="-mx-3 space-y-1">
                                    <Link
                                        v-for="item in mainNavItems"
                                        :key="item.title"
                                        :href="item.href"
                                        class="flex items-center gap-x-3 rounded-lg px-3 py-2 text-sm font-medium hover:bg-accent"
                                        :class="activeItemStyles(item.href)"
                                    >
                                        <component v-if="item.icon" :is="item.icon" class="h-5 w-5" />
                                        {{ item.title }}
                                    </Link>
                                </nav>
                                <div class="flex flex-col space-y-4">
                                    <a
                                        v-for="item in rightNavItems"
                                        :key="item.title"
                                        :href="item.href"
                                        target="_blank"
                                        rel="noopener noreferrer"
                                        class="flex items-center space-x-2 text-sm font-medium"
                                    >
                                        <component v-if="item.icon" :is="item.icon" class="h-5 w-5" />
                                        <span>{{ item.title }}</span>
                                    </a>
                                </div>
                            </div>
                        </SheetContent>
                    </Sheet>
                </div>

                <Link :href="route('index')" class="flex items-center gap-x-2" v-if="!route().current('index')">
                    <AppLogo />
                </Link>

                <!-- Desktop Menu -->
                <div class="hidden h-full lg:flex lg:flex-1">
                    <NavigationMenu class="ml-10 flex h-full items-stretch">
                        <NavigationMenuList class="flex h-full items-stretch space-x-2">
                            <NavigationMenuItem v-for="(item, index) in mainNavItems" :key="index" class="relative flex h-full items-center">
                                <Link
                                    :class="[navigationMenuTriggerStyle(), activeItemStyles(item.href), 'h-9 cursor-pointer px-3']"
                                    :href="item.href"
                                >
                                    <component v-if="item.icon" :is="item.icon" class="mr-2 h-4 w-4" />
                                    {{ item.title }}
                                </Link>
                                <div
                                    v-if="route().current(item.route)"
                                    class="absolute bottom-0 left-0 h-0.5 w-full translate-y-px bg-black dark:bg-white"
                                ></div>
                            </NavigationMenuItem>
                        </NavigationMenuList>
                    </NavigationMenu>
                </div>

                <div class="ml-auto flex items-center space-x-2">
                    <div class="relative flex items-center space-x-1">
                        <Button variant="ghost" size="icon" class="group h-9 w-9 cursor-pointer">
                            <Search class="size-5 opacity-80 group-hover:opacity-100" />
                        </Button>
                        <BookOpen @click="toggleDialog" class="inline-block cursor-pointer" />

                        <div class="hidden space-x-1 lg:flex">

<!--                            <template v-for="item in rightNavItems" :key="item.title">-->
<!--                                <TooltipProvider :delay-duration="0">-->
<!--                                    <Tooltip>-->
<!--                                        <TooltipTrigger>-->
<!--                                            <Button variant="ghost" size="icon" as-child class="group h-9 w-9 cursor-pointer">-->
<!--                                                <a :href="item.href" target="_blank" rel="noopener noreferrer">-->
<!--                                                    <span class="sr-only">{{ item.title }}</span>-->
<!--                                                    <component :is="item.icon" class="size-5 opacity-80 group-hover:opacity-100" />-->
<!--                                                </a>-->
<!--                                            </Button>-->
<!--                                        </TooltipTrigger>-->
<!--                                        <TooltipContent>-->
<!--                                            <p>{{ item.title }}</p>-->
<!--                                        </TooltipContent>-->
<!--                                    </Tooltip>-->
<!--                                </TooltipProvider>-->
<!--                            </template>-->
                        </div>
                    </div>

                    <DropdownMenu v-if="auth.user">
                        <DropdownMenuTrigger :as-child="true">
                            <Button
                                variant="ghost"
                                size="icon"
                                class="relative size-10 w-auto rounded-full p-1 focus-within:ring-2 focus-within:ring-primary"
                            >
                                <Avatar class="size-8 overflow-hidden rounded-full">
                                    <AvatarImage v-if="auth.user?.avatar" :src="auth.user.avatar" :alt="auth.user.name" />
                                    <AvatarFallback class="rounded-lg bg-neutral-200 font-semibold text-black dark:bg-neutral-700 dark:text-white">
                                        {{ getInitials(auth.user?.name) }}
                                    </AvatarFallback>
                                </Avatar>
                            </Button>
                        </DropdownMenuTrigger>
                        <DropdownMenuContent align="end" class="w-56">
                            <UserMenuContent :user="auth.user" />
                        </DropdownMenuContent>
                    </DropdownMenu>
                </div>
            </div>
        </div>

        <div v-if="props.breadcrumbs.length > 1" class="flex w-full border-b border-sidebar-border/70">
            <div class="mx-auto flex h-12 w-full items-center justify-start px-4 text-neutral-500 md:max-w-7xl">
                <Breadcrumbs :breadcrumbs="breadcrumbs" />
            </div>
        </div>
    </div>

    <div>
        <CommandDialog v-model:open="open">
            <CommandInput placeholder="Search for a topic..." />
            <CommandList>
                <CommandEmpty>No results found.</CommandEmpty>
                <CommandGroup heading="Pages">
                    <CommandItem v-for="page in commandSearch.pages" v-bind:key="page.slug" @select="commandRoute(page.route)" value="page.slug">
                        {{ page.title }}
                    </CommandItem>
                </CommandGroup>
                <CommandSeparator />
                <CommandGroup heading="Sections">
                    <CommandItem v-for="section in commandSearch.sections" v-bind:key="section.slug" @select="commandRoute(section.route)" value="section.slug">
                        {{ section.title }}
                    </CommandItem>
                </CommandGroup>
                <CommandSeparator />
                <CommandGroup heading="Indices">
                    <CommandItem v-for="index in commandSearch.indices" v-bind:key="index.slug" @select="commandRoute(index.route)" value="index.slug">
                        {{ index.title }}
                    </CommandItem>
                </CommandGroup>
            </CommandList>
        </CommandDialog>
    </div>
</template>
