<script setup lang="ts">
import { ref } from 'vue'
import { Link } from '@inertiajs/vue3'
import { MoreHorizontal } from 'lucide-vue-next'
import { Button } from '@/Components/ui/button'
import {
    DropdownMenu,
    DropdownMenuContent,
    DropdownMenuItem,
    DropdownMenuSeparator,
    DropdownMenuTrigger,
} from '@/Components/ui/dropdown-menu'
import {
    AlertDialog,
    AlertDialogAction,
    AlertDialogCancel,
    AlertDialogContent,
    AlertDialogDescription,
    AlertDialogFooter,
    AlertDialogHeader,
    AlertDialogTitle,
} from '@/Components/ui/alert-dialog'

interface Props {
    editHref?: string
    deleteMessage?: string
}

const props = withDefaults(defineProps<Props>(), {
    deleteMessage: 'Are you sure? This action cannot be undone.',
})

const emit = defineEmits<{
    delete: []
}>()

const showDeleteDialog = ref(false)
</script>

<template>
    <div class="flex justify-end">
        <AlertDialog v-model:open="showDeleteDialog">
            <DropdownMenu>
                <DropdownMenuTrigger as-child>
                    <Button variant="ghost" class="h-8 w-8 p-0">
                        <span class="sr-only">Open menu</span>
                        <MoreHorizontal class="h-4 w-4" />
                    </Button>
                </DropdownMenuTrigger>
                <DropdownMenuContent align="end">
                    <DropdownMenuItem v-if="editHref" as-child>
                        <Link :href="editHref">Edit</Link>
                    </DropdownMenuItem>
                    <DropdownMenuSeparator v-if="editHref" />
                    <DropdownMenuItem
                        class="text-destructive focus:text-destructive"
                        @click="showDeleteDialog = true"
                    >
                        Delete
                    </DropdownMenuItem>
                </DropdownMenuContent>
            </DropdownMenu>

            <AlertDialogContent>
                <AlertDialogHeader>
                    <AlertDialogTitle>Are you sure?</AlertDialogTitle>
                    <AlertDialogDescription>
                        {{ deleteMessage }}
                    </AlertDialogDescription>
                </AlertDialogHeader>
                <AlertDialogFooter>
                    <AlertDialogCancel>Cancel</AlertDialogCancel>
                    <AlertDialogAction
                        class="bg-destructive text-destructive-foreground hover:bg-destructive/90"
                        @click="emit('delete')"
                    >
                        Delete
                    </AlertDialogAction>
                </AlertDialogFooter>
            </AlertDialogContent>
        </AlertDialog>
    </div>
</template>
