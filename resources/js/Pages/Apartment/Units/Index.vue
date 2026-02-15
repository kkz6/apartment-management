<script setup lang="ts">
import type { ColumnDef } from '@tanstack/vue-table'
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { h } from 'vue';
import { Button } from '@/Components/ui/button';
import { Badge } from '@/Components/ui/badge';
import { DataTable, DataTableColumnHeader, DataTableRowActions } from '@/Components/ui/data-table';
import type { PaginationData } from '@/types';

interface Unit {
    id: number;
    flat_number: string;
    flat_type: string;
    floor: number;
    area_sqft: number | null;
    residents_count: number;
}

const props = defineProps<{
    units: PaginationData<Unit>;
}>();

const columns: ColumnDef<Unit>[] = [
    {
        accessorKey: 'flat_number',
        header: 'Flat Number',
        cell: ({ row }) => h('div', { class: 'font-medium' }, row.getValue('flat_number')),
    },
    {
        accessorKey: 'flat_type',
        header: 'Type',
        cell: ({ row }) => h(Badge, { variant: 'secondary' }, () => row.getValue('flat_type')),
    },
    {
        accessorKey: 'floor',
        header: ({ column }) => h(DataTableColumnHeader, { column, title: 'Floor' }),
    },
    {
        accessorKey: 'area_sqft',
        header: ({ column }) => h(DataTableColumnHeader, { column, title: 'Area (sqft)' }),
        cell: ({ row }) => row.getValue('area_sqft') ?? '-',
    },
    {
        accessorKey: 'residents_count',
        header: 'Residents',
    },
    {
        id: 'actions',
        cell: ({ row }) => h(DataTableRowActions, {
            editHref: route('units.edit', row.original.id),
            onDelete: () => router.delete(route('units.destroy', row.original.id)),
            deleteMessage: `This will permanently delete unit "${row.original.flat_number}". This action cannot be undone.`,
        }),
    },
];
</script>

<template>
    <Head title="Units" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center justify-between">
                <h2 class="text-xl font-semibold leading-tight text-foreground">
                    Units
                </h2>
                <Link :href="route('units.create')">
                    <Button>Add Unit</Button>
                </Link>
            </div>
        </template>

        <DataTable :columns="columns" :data="units.data" :pagination="units">
            <template #empty>
                No units found. Click "Add Unit" to create one.
            </template>
        </DataTable>
    </AuthenticatedLayout>
</template>
