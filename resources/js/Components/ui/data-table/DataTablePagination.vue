<script setup lang="ts">
import { router } from '@inertiajs/vue3'
import { Button } from '@/Components/ui/button'
import { ChevronLeft, ChevronRight } from 'lucide-vue-next'

interface Props {
    from: number | null
    to: number | null
    total: number
    links: Array<{ url: string | null; label: string; active: boolean }>
}

const props = defineProps<Props>()

const pageLinks = computed(() => props.links.slice(1, -1))

const previousLink = computed(() => props.links[0] ?? null)
const nextLink = computed(() => props.links[props.links.length - 1] ?? null)

import { computed } from 'vue'

const navigate = (url: string | null) => {
    if (!url) {
        return
    }

    router.get(url, {}, { preserveState: true, preserveScroll: true })
}
</script>

<template>
    <div class="flex items-center justify-between px-2 py-4">
        <div class="text-sm text-muted-foreground">
            <template v-if="from && to">
                Showing {{ from }} to {{ to }} of {{ total }} results
            </template>
            <template v-else>
                No results
            </template>
        </div>

        <div v-if="total > 0" class="flex items-center gap-1">
            <Button
                variant="outline"
                size="icon"
                class="h-8 w-8"
                :disabled="!previousLink?.url"
                @click="navigate(previousLink?.url ?? null)"
            >
                <ChevronLeft class="h-4 w-4" />
            </Button>

            <Button
                v-for="link in pageLinks"
                :key="link.label"
                :variant="link.active ? 'default' : 'outline'"
                size="sm"
                class="h-8 min-w-8"
                :disabled="!link.url"
                @click="navigate(link.url)"
            >
                {{ link.label }}
            </Button>

            <Button
                variant="outline"
                size="icon"
                class="h-8 w-8"
                :disabled="!nextLink?.url"
                @click="navigate(nextLink?.url ?? null)"
            >
                <ChevronRight class="h-4 w-4" />
            </Button>
        </div>
    </div>
</template>
