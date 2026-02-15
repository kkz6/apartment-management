<script setup lang="ts">
import type { ColumnDef } from '@tanstack/vue-table'
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { h } from 'vue';
import { Button } from '@/Components/ui/button';
import { Badge } from '@/Components/ui/badge';
import { DataTable, DataTableRowActions } from '@/Components/ui/data-table';
import type { PaginationData } from '@/types';

interface Resident {
    id: number;
    name: string;
    phone: string | null;
    email: string | null;
    is_owner: boolean;
    gpay_name: string | null;
    unit: {
        id: number;
        flat_number: string;
    } | null;
}

const props = defineProps<{
    residents: PaginationData<Resident>;
}>();

const columns: ColumnDef<Resident>[] = [
    {
        accessorKey: 'name',
        header: 'Name',
        cell: ({ row }) => h('div', { class: 'font-medium' }, row.getValue('name')),
    },
    {
        accessorKey: 'unit',
        header: 'Unit',
        cell: ({ row }) => row.original.unit?.flat_number ?? '-',
    },
    {
        accessorKey: 'phone',
        header: 'Phone',
        cell: ({ row }) => row.getValue('phone') ?? '-',
    },
    {
        accessorKey: 'is_owner',
        header: 'Status',
        cell: ({ row }) => h(Badge, {
            variant: row.getValue('is_owner') ? 'default' : 'secondary',
        }, () => row.getValue('is_owner') ? 'Owner' : 'Tenant'),
    },
    {
        accessorKey: 'gpay_name',
        header: 'GPay Name',
        cell: ({ row }) => row.getValue('gpay_name') ?? '-',
    },
    {
        id: 'actions',
        cell: ({ row }) => h(DataTableRowActions, {
            editHref: route('residents.edit', row.original.id),
            onDelete: () => router.delete(route('residents.destroy', row.original.id)),
            deleteMessage: `This will permanently delete the resident "${row.original.name}". This action cannot be undone.`,
        }),
    },
];
</script>

<template>
    <Head title="Residents" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center justify-between">
                <h2 class="text-xl font-semibold leading-tight text-foreground">
                    Residents
                </h2>
                <Link :href="route('residents.create')">
                    <Button>Add Resident</Button>
                </Link>
            </div>
        </template>

        <DataTable :columns="columns" :data="residents.data" :pagination="residents">
            <template #empty>
                No residents found. Click "Add Resident" to create one.
            </template>
        </DataTable>
    </AuthenticatedLayout>
</template>
