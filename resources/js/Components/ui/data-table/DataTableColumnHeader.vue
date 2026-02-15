<script setup lang="ts">
import type { Column } from '@tanstack/vue-table'
import { cn } from '@/lib/utils'
import { Button } from '@/Components/ui/button'
import { ArrowDown, ArrowUp, ArrowUpDown } from 'lucide-vue-next'

interface Props {
    column: Column<any, any>
    title: string
    class?: string
}

const props = defineProps<Props>()
</script>

<template>
    <div :class="cn('flex items-center space-x-2', props.class)">
        <Button
            v-if="column.getCanSort()"
            variant="ghost"
            size="sm"
            class="-ml-3 h-8"
            @click="column.toggleSorting(column.getIsSorted() === 'asc')"
        >
            <span>{{ title }}</span>
            <ArrowDown v-if="column.getIsSorted() === 'desc'" class="ml-2 h-4 w-4" />
            <ArrowUp v-else-if="column.getIsSorted() === 'asc'" class="ml-2 h-4 w-4" />
            <ArrowUpDown v-else class="ml-2 h-4 w-4" />
        </Button>
        <span v-else>{{ title }}</span>
    </div>
</template>
