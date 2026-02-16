<script setup lang="ts">
import { RotateCcw } from 'lucide-vue-next';
import { Button } from '@/Components/ui/button';
import { DataTableRowActions } from '@/Components/ui/data-table';

defineProps<{
    showRetry: boolean;
}>();

const emit = defineEmits<{
    retry: [];
    delete: [];
}>();
</script>

<template>
    <div class="flex items-center justify-end gap-1">
        <Button
            v-if="showRetry"
            variant="ghost"
            size="sm"
            class="h-8 px-2"
            @click="emit('retry')"
        >
            <RotateCcw class="mr-1 h-3.5 w-3.5" />
            Retry
        </Button>
        <DataTableRowActions
            delete-message="Are you sure you want to delete this upload? This action cannot be undone."
            @delete="emit('delete')"
        />
    </div>
</template>
