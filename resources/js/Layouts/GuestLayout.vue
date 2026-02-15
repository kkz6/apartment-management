<script setup lang="ts">
import ApplicationLogo from '@/Components/ApplicationLogo.vue';
import { Card, CardContent } from '@/Components/ui/card';
import { Link } from '@inertiajs/vue3';
</script>

<template>
    <div
        class="flex min-h-screen flex-col items-center bg-background pt-6 sm:justify-center sm:pt-0"
    >
        <div>
            <Link href="/">
                <ApplicationLogo class="h-20 w-20 fill-current text-muted-foreground" />
            </Link>
        </div>

        <Card class="mt-6 w-full sm:max-w-md">
            <CardContent class="p-6">
                <slot />
            </CardContent>
        </Card>
    </div>
</template>
