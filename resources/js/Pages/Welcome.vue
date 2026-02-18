<script setup lang="ts">
import { Button } from '@/Components/ui/button';
import { Card, CardContent } from '@/Components/ui/card';
import { Separator } from '@/Components/ui/separator';
import { Head, Link } from '@inertiajs/vue3';
import {
    Building2,
    Receipt,
    CreditCard,
    FileSearch,
    BotMessageSquare,
    Sheet,
} from 'lucide-vue-next';

const features = [
    {
        icon: Building2,
        title: 'Unit & Resident Management',
        description: 'Track all apartments, residents, and ownership details in one place.',
    },
    {
        icon: Receipt,
        title: 'Maintenance Billing',
        description: 'Generate quarterly maintenance charges with slab-based pricing.',
    },
    {
        icon: CreditCard,
        title: 'Payment Tracking',
        description: 'Record payments and track pending dues across all units.',
    },
    {
        icon: FileSearch,
        title: 'Payment Reconciliation',
        description: 'Import GPay screenshots and bank statements to auto-match payments.',
    },
    {
        icon: Sheet,
        title: 'Google Sheets Sync',
        description: 'Automatically sync billing data to Google Sheets for shared access.',
    },
    {
        icon: BotMessageSquare,
        title: 'Telegram Bot',
        description: 'Check dues, record payments, and get notifications via Telegram.',
    },
];
</script>

<template>
    <Head title="JCP Association" />

    <div class="min-h-screen bg-background">
        <!-- Nav -->
        <header class="border-b bg-card">
            <div class="mx-auto flex h-14 max-w-5xl items-center justify-between px-6">
                <div class="flex items-center gap-2">
                    <div class="flex h-8 w-8 items-center justify-center rounded-lg bg-primary text-primary-foreground">
                        <Building2 class="h-4 w-4" />
                    </div>
                    <span class="text-lg font-semibold text-foreground">JCP Association</span>
                </div>

                <nav>
                    <template v-if="$page.props.auth.user">
                        <Button as-child size="sm">
                            <Link :href="route('dashboard')">Dashboard</Link>
                        </Button>
                    </template>
                    <template v-else>
                        <Button as-child size="sm">
                            <Link :href="route('login')">Log in</Link>
                        </Button>
                    </template>
                </nav>
            </div>
        </header>

        <!-- Hero -->
        <section class="py-20 sm:py-28">
            <div class="mx-auto max-w-5xl px-6 text-center">
                <div class="mx-auto mb-6 flex h-16 w-16 items-center justify-center rounded-2xl bg-primary text-primary-foreground">
                    <Building2 class="h-8 w-8" />
                </div>

                <h1 class="text-4xl font-bold tracking-tight text-foreground sm:text-5xl">
                    Apartment Management
                </h1>
                <p class="mx-auto mt-4 max-w-lg text-lg text-muted-foreground">
                    Simplify maintenance billing, payment tracking, and reconciliation for your apartment association.
                </p>

                <div class="mt-8">
                    <template v-if="$page.props.auth.user">
                        <Button as-child size="lg">
                            <Link :href="route('dashboard')">Go to Dashboard</Link>
                        </Button>
                    </template>
                    <template v-else>
                        <Button as-child size="lg">
                            <Link :href="route('login')">Log in</Link>
                        </Button>
                    </template>
                </div>
            </div>
        </section>

        <Separator />

        <!-- Features -->
        <section class="py-16 sm:py-20">
            <div class="mx-auto max-w-5xl px-6">
                <h2 class="text-center text-2xl font-semibold text-foreground">
                    Everything you need to manage your apartment
                </h2>
                <p class="mt-2 text-center text-muted-foreground">
                    Built for Indian apartment associations
                </p>

                <div class="mt-12 grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-3">
                    <Card v-for="feature in features" :key="feature.title" class="border bg-card">
                        <CardContent class="p-6">
                            <div class="mb-3 flex h-10 w-10 items-center justify-center rounded-lg bg-secondary">
                                <component :is="feature.icon" class="h-5 w-5 text-foreground" />
                            </div>
                            <h3 class="font-medium text-foreground">
                                {{ feature.title }}
                            </h3>
                            <p class="mt-1 text-sm text-muted-foreground">
                                {{ feature.description }}
                            </p>
                        </CardContent>
                    </Card>
                </div>
            </div>
        </section>

        <!-- Footer -->
        <footer class="border-t py-8">
            <div class="mx-auto max-w-5xl px-6 text-center text-sm text-muted-foreground">
                JCP Association
            </div>
        </footer>
    </div>
</template>
