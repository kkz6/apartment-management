<script setup lang="ts">
import type { ColumnDef } from '@tanstack/vue-table'
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, router, usePage } from '@inertiajs/vue3';
import { computed, h } from 'vue';
import { Badge } from '@/Components/ui/badge';
import { Button } from '@/Components/ui/button';
import { Alert, AlertDescription } from '@/Components/ui/alert';
import { DataTable, DataTableColumnHeader, DataTableRowActions } from '@/Components/ui/data-table';
import type { PaginationData } from '@/types';

interface Upload {
    id: number;
    file_path: string;
    type: string;
    status: string;
    processed_at: string | null;
    uploaded_by: string | null;
    created_at: string;
    transactions_count: number;
}

const props = defineProps<{
    uploads: PaginationData<Upload>;
}>();

const flash = computed(() => usePage().props.flash as { success?: string } | undefined);

const typeLabel = (type: string): string => {
    switch (type) {
        case 'gpay_screenshot':
            return 'GPay Screenshot';
        case 'bank_statement':
            return 'Bank Statement';
        default:
            return type;
    }
};

const typeBadgeVariant = (type: string): 'default' | 'secondary' | 'outline' => {
    switch (type) {
        case 'gpay_screenshot':
            return 'default';
        case 'bank_statement':
            return 'secondary';
        default:
            return 'outline';
    }
};

const statusBadgeVariant = (status: string): 'default' | 'secondary' | 'destructive' | 'outline' => {
    switch (status) {
        case 'processed':
            return 'default';
        case 'processing':
            return 'secondary';
        case 'failed':
            return 'destructive';
        case 'pending':
            return 'outline';
        default:
            return 'outline';
    }
};

const fileName = (path: string): string => {
    return path.split('/').pop() ?? path;
};

const columns: ColumnDef<Upload>[] = [
    {
        accessorKey: 'file_path',
        header: 'File',
        cell: ({ row }) => h('div', { class: 'font-medium' }, fileName(row.getValue('file_path'))),
    },
    {
        accessorKey: 'type',
        header: 'Type',
        cell: ({ row }) => h(Badge, { variant: typeBadgeVariant(row.getValue('type')) }, () => typeLabel(row.getValue('type'))),
    },
    {
        accessorKey: 'status',
        header: 'Status',
        cell: ({ row }) => h(Badge, { variant: statusBadgeVariant(row.getValue('status')) }, () => row.getValue('status')),
    },
    {
        accessorKey: 'transactions_count',
        header: ({ column }) => h(DataTableColumnHeader, { column, title: 'Transactions', class: 'justify-end' }),
        cell: ({ row }) => h('div', { class: 'text-right' }, row.getValue('transactions_count')),
    },
    {
        accessorKey: 'uploaded_by',
        header: 'Uploaded By',
        cell: ({ row }) => h('span', { class: 'text-muted-foreground' }, row.getValue('uploaded_by') ?? '-'),
    },
    {
        accessorKey: 'created_at',
        header: 'Date',
        cell: ({ row }) => h('span', { class: 'text-muted-foreground' }, row.getValue('created_at')),
    },
    {
        id: 'actions',
        cell: ({ row }) => h(DataTableRowActions, {
            onDelete: () => router.delete(route('uploads.destroy', row.original.id)),
            deleteMessage: 'Are you sure you want to delete this upload? This action cannot be undone.',
        }),
    },
];
</script>

<template>
    <Head title="Uploads" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center justify-between">
                <h2 class="text-xl font-semibold leading-tight text-foreground">
                    Uploads
                </h2>
                <Button as-child>
                    <Link :href="route('uploads.create')">
                        Upload File
                    </Link>
                </Button>
            </div>
        </template>

        <Alert v-if="flash?.success" class="mb-4">
            <AlertDescription>{{ flash.success }}</AlertDescription>
        </Alert>

        <DataTable :columns="columns" :data="uploads.data" :pagination="uploads">
            <template #empty>
                No uploads found. Click "Upload File" to get started.
            </template>
        </DataTable>
    </AuthenticatedLayout>
</template>
