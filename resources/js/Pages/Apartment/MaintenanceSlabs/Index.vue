<script setup lang="ts">
import type { ColumnDef } from '@tanstack/vue-table'
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { h } from 'vue';
import { Button } from '@/Components/ui/button';
import { Badge } from '@/Components/ui/badge';
import { DataTable, DataTableColumnHeader, DataTableRowActions } from '@/Components/ui/data-table';
import type { PaginationData } from '@/types';

interface MaintenanceSlab {
    id: number;
    flat_type: string;
    amount: string;
    effective_from: string;
}

const props = defineProps<{
    slabs: PaginationData<MaintenanceSlab>;
}>();

const formatDate = (date: string): string => {
    return new Date(date).toLocaleDateString('en-IN', {
        year: 'numeric',
        month: 'short',
        day: 'numeric',
    });
};

const formatCurrency = (amount: string): string => {
    return new Intl.NumberFormat('en-IN', {
        style: 'currency',
        currency: 'INR',
        minimumFractionDigits: 0,
    }).format(Number(amount));
};

const columns: ColumnDef<MaintenanceSlab>[] = [
    {
        accessorKey: 'flat_type',
        header: 'Flat Type',
        cell: ({ row }) => h(Badge, { variant: 'secondary' }, () => row.getValue('flat_type')),
    },
    {
        accessorKey: 'amount',
        header: ({ column }) => h(DataTableColumnHeader, { column, title: 'Amount' }),
        cell: ({ row }) => formatCurrency(row.getValue('amount')),
    },
    {
        accessorKey: 'effective_from',
        header: 'Effective From',
        cell: ({ row }) => formatDate(row.getValue('effective_from')),
    },
    {
        id: 'actions',
        cell: ({ row }) => h(DataTableRowActions, {
            editHref: route('maintenance-slabs.edit', row.original.id),
            onDelete: () => router.delete(route('maintenance-slabs.destroy', row.original.id)),
            deleteMessage: 'Are you sure you want to delete this maintenance slab? This action cannot be undone.',
        }),
    },
];
</script>

<template>
    <Head title="Maintenance Slabs" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center justify-between">
                <h2 class="text-xl font-semibold leading-tight text-foreground">
                    Maintenance Slabs
                </h2>
                <Button as-child>
                    <Link :href="route('maintenance-slabs.create')">
                        Add Slab
                    </Link>
                </Button>
            </div>
        </template>

        <DataTable :columns="columns" :data="slabs.data" :pagination="slabs">
            <template #empty>
                No maintenance slabs found. Click "Add Slab" to create one.
            </template>
        </DataTable>
    </AuthenticatedLayout>
</template>
