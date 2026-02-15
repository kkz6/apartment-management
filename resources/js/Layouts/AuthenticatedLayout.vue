<script setup lang="ts">
import { ref } from 'vue';
import ApplicationLogo from '@/Components/ApplicationLogo.vue';
import { Link, usePage, router } from '@inertiajs/vue3';
import { Button } from '@/Components/ui/button';
import { Separator } from '@/Components/ui/separator';
import { Sheet, SheetContent, SheetTrigger, SheetTitle } from '@/Components/ui/sheet';
import {
    DropdownMenu,
    DropdownMenuContent,
    DropdownMenuItem,
    DropdownMenuSeparator,
    DropdownMenuTrigger,
} from '@/Components/ui/dropdown-menu';
import { Toaster } from '@/Components/ui/sonner';
import {
    LayoutDashboard,
    Building2,
    Users,
    Sliders,
    Receipt,
    CreditCard,
    Wallet,
    Upload,
    ListChecks,
    Menu,
    User,
    LogOut,
    ChevronDown,
} from 'lucide-vue-next';

const page = usePage();
const mobileOpen = ref(false);

interface NavItem {
    label: string;
    href: string;
    routeMatch: string;
    icon: any;
}

interface NavGroup {
    title: string;
    items: NavItem[];
}

const navGroups: NavGroup[] = [
    {
        title: '',
        items: [
            { label: 'Dashboard', href: route('dashboard'), routeMatch: 'dashboard', icon: LayoutDashboard },
        ],
    },
    {
        title: 'Apartment',
        items: [
            { label: 'Units', href: route('units.index'), routeMatch: 'units.*', icon: Building2 },
            { label: 'Residents', href: route('residents.index'), routeMatch: 'residents.*', icon: Users },
            { label: 'Maintenance Slabs', href: route('maintenance-slabs.index'), routeMatch: 'maintenance-slabs.*', icon: Sliders },
        ],
    },
    {
        title: 'Billing',
        items: [
            { label: 'Charges', href: route('charges.index'), routeMatch: 'charges.*', icon: Receipt },
            { label: 'Payments', href: route('payments.index'), routeMatch: 'payments.*', icon: CreditCard },
            { label: 'Expenses', href: route('expenses.index'), routeMatch: 'expenses.*', icon: Wallet },
        ],
    },
    {
        title: 'Import',
        items: [
            { label: 'Uploads', href: route('uploads.index'), routeMatch: 'uploads.*', icon: Upload },
            { label: 'Review Queue', href: route('review-queue.index'), routeMatch: 'review-queue.*', icon: ListChecks },
        ],
    },
];

const isActive = (match: string): boolean => {
    return route().current(match) ?? false;
};

const logout = () => {
    router.post(route('logout'));
};

const navigateMobile = (href: string) => {
    mobileOpen.value = false;
    router.visit(href);
};
</script>

<template>
    <div class="min-h-screen bg-background">
        <Toaster position="top-right" />

        <!-- Desktop Sidebar -->
        <aside class="fixed inset-y-0 left-0 z-30 hidden w-64 flex-col border-r bg-card md:flex">
            <!-- Logo -->
            <div class="flex h-14 items-center border-b px-4">
                <Link :href="route('dashboard')" class="flex items-center gap-2">
                    <ApplicationLogo class="h-8 w-auto fill-current text-foreground" />
                    <span class="text-lg font-semibold text-foreground">Apartment</span>
                </Link>
            </div>

            <!-- Navigation -->
            <nav class="flex-1 overflow-y-auto px-3 py-4">
                <template v-for="(group, gi) in navGroups" :key="gi">
                    <Separator v-if="gi > 0" class="my-3" />
                    <p
                        v-if="group.title"
                        class="mb-1 px-2 text-xs font-semibold uppercase tracking-wider text-muted-foreground"
                    >
                        {{ group.title }}
                    </p>
                    <Link
                        v-for="item in group.items"
                        :key="item.routeMatch"
                        :href="item.href"
                        class="flex items-center gap-3 rounded-md px-2 py-2 text-sm font-medium transition-colors"
                        :class="isActive(item.routeMatch)
                            ? 'bg-accent text-accent-foreground'
                            : 'text-muted-foreground hover:bg-accent hover:text-accent-foreground'"
                    >
                        <component :is="item.icon" class="h-4 w-4" />
                        {{ item.label }}
                    </Link>
                </template>
            </nav>

            <!-- User Dropdown at bottom -->
            <div class="border-t p-3">
                <DropdownMenu>
                    <DropdownMenuTrigger as-child>
                        <Button variant="ghost" class="w-full justify-start gap-2">
                            <User class="h-4 w-4" />
                            <span class="truncate">{{ $page.props.auth.user.name }}</span>
                            <ChevronDown class="ml-auto h-4 w-4" />
                        </Button>
                    </DropdownMenuTrigger>
                    <DropdownMenuContent align="start" class="w-56">
                        <DropdownMenuItem as-child>
                            <Link :href="route('profile.edit')" class="flex w-full items-center gap-2">
                                <User class="h-4 w-4" />
                                Profile
                            </Link>
                        </DropdownMenuItem>
                        <DropdownMenuSeparator />
                        <DropdownMenuItem @click="logout" class="flex items-center gap-2">
                            <LogOut class="h-4 w-4" />
                            Log Out
                        </DropdownMenuItem>
                    </DropdownMenuContent>
                </DropdownMenu>
            </div>
        </aside>

        <!-- Mobile Header -->
        <div class="sticky top-0 z-20 flex h-14 items-center border-b bg-card px-4 md:hidden">
            <Sheet v-model:open="mobileOpen">
                <SheetTrigger as-child>
                    <Button variant="ghost" size="icon">
                        <Menu class="h-5 w-5" />
                    </Button>
                </SheetTrigger>
                <SheetContent side="left" class="w-64 p-0">
                    <SheetTitle class="sr-only">Navigation</SheetTitle>
                    <div class="flex h-14 items-center border-b px-4">
                        <ApplicationLogo class="h-8 w-auto fill-current text-foreground" />
                        <span class="ml-2 text-lg font-semibold text-foreground">Apartment</span>
                    </div>
                    <nav class="flex-1 overflow-y-auto px-3 py-4">
                        <template v-for="(group, gi) in navGroups" :key="gi">
                            <Separator v-if="gi > 0" class="my-3" />
                            <p
                                v-if="group.title"
                                class="mb-1 px-2 text-xs font-semibold uppercase tracking-wider text-muted-foreground"
                            >
                                {{ group.title }}
                            </p>
                            <button
                                v-for="item in group.items"
                                :key="item.routeMatch"
                                @click="navigateMobile(item.href)"
                                class="flex w-full items-center gap-3 rounded-md px-2 py-2 text-sm font-medium transition-colors"
                                :class="isActive(item.routeMatch)
                                    ? 'bg-accent text-accent-foreground'
                                    : 'text-muted-foreground hover:bg-accent hover:text-accent-foreground'"
                            >
                                <component :is="item.icon" class="h-4 w-4" />
                                {{ item.label }}
                            </button>
                        </template>
                    </nav>
                    <div class="border-t p-3">
                        <button
                            @click="navigateMobile(route('profile.edit'))"
                            class="flex w-full items-center gap-2 rounded-md px-2 py-2 text-sm text-muted-foreground hover:bg-accent"
                        >
                            <User class="h-4 w-4" />
                            Profile
                        </button>
                        <button
                            @click="logout"
                            class="flex w-full items-center gap-2 rounded-md px-2 py-2 text-sm text-muted-foreground hover:bg-accent"
                        >
                            <LogOut class="h-4 w-4" />
                            Log Out
                        </button>
                    </div>
                </SheetContent>
            </Sheet>
            <span class="ml-3 text-lg font-semibold text-foreground">Apartment</span>
        </div>

        <!-- Main Content -->
        <div class="md:pl-64">
            <!-- Page Heading -->
            <header v-if="$slots.header" class="sticky top-0 z-20 flex h-14 items-center border-b bg-card px-4 sm:px-6 lg:px-8">
                <div class="w-full">
                    <slot name="header" />
                </div>
            </header>

            <!-- Page Content -->
            <main class="p-4 sm:p-6 lg:p-8">
                <slot />
            </main>
        </div>
    </div>
</template>
