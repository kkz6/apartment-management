<script setup lang="ts">
import type { ColumnDef } from '@tanstack/vue-table'
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { h } from 'vue';
import { Button } from '@/Components/ui/button';
import { Badge } from '@/Components/ui/badge';
import { DataTable, DataTableColumnHeader, DataTableRowActions } from '@/Components/ui/data-table';
import type { PaginationData } from '@/types';

interface Expense {
    id: number;
    description: string;
    amount: string;
    paid_date: string;
    category: string;
    source: string;
    reference_number: string | null;
    reconciliation_status: string;
}

const props = defineProps<{
    expenses: PaginationData<Expense>;
}>();

const formatCurrency = (amount: string): string => {
    return new Intl.NumberFormat('en-IN', {
        style: 'currency',
        currency: 'INR',
        minimumFractionDigits: 0,
    }).format(Number(amount));
};

const categoryLabel = (category: string): string => {
    return category.charAt(0).toUpperCase() + category.slice(1);
};

const sourceLabel = (source: string): string => {
    switch (source) {
        case 'gpay':
            return 'GPay';
        case 'bank_transfer':
            return 'Bank Transfer';
        case 'cash':
            return 'Cash';
        default:
            return source;
    }
};

const formatDate = (date: string): string => {
    return new Date(date).toLocaleDateString('en-IN', {
        year: 'numeric',
        month: 'short',
        day: 'numeric',
    });
};

const reconciliationLabel = (status: string): string => {
    switch (status) {
        case 'pending_verification':
            return 'Pending';
        case 'bank_verified':
            return 'Bank Verified';
        case 'matched':
            return 'Matched';
        case 'manual':
            return 'Manual';
        default:
            return status.replace(/_/g, ' ').replace(/\b\w/g, c => c.toUpperCase());
    }
};

const reconciliationBadgeVariant = (status: string): 'default' | 'secondary' | 'outline' => {
    switch (status) {
        case 'bank_verified':
        case 'matched':
            return 'default';
        case 'manual':
            return 'secondary';
        default:
            return 'outline';
    }
};

const columns: ColumnDef<Expense>[] = [
    {
        accessorKey: 'paid_date',
        header: ({ column }) => h(DataTableColumnHeader, { column, title: 'Date' }),
        cell: ({ row }) => h('div', { class: 'whitespace-nowrap' }, formatDate(row.getValue('paid_date'))),
    },
    {
        accessorKey: 'description',
        header: 'Description',
        cell: ({ row }) => h('span', { class: 'text-muted-foreground' }, row.getValue('description')),
    },
    {
        accessorKey: 'amount',
        header: ({ column }) => h(DataTableColumnHeader, { column, title: 'Amount', class: 'justify-end' }),
        cell: ({ row }) => h('div', { class: 'whitespace-nowrap text-right' }, formatCurrency(row.getValue('amount'))),
    },
    {
        accessorKey: 'category',
        header: 'Category',
        cell: ({ row }) => h(Badge, { variant: 'outline' }, () => categoryLabel(row.getValue('category'))),
    },
    {
        accessorKey: 'source',
        header: 'Source',
        cell: ({ row }) => h('span', { class: 'whitespace-nowrap text-muted-foreground' }, sourceLabel(row.getValue('source'))),
    },
    {
        accessorKey: 'reconciliation_status',
        header: 'Status',
        cell: ({ row }) => h(Badge, {
            variant: reconciliationBadgeVariant(row.getValue('reconciliation_status')),
        }, () => reconciliationLabel(row.getValue('reconciliation_status'))),
    },
    {
        id: 'actions',
        cell: ({ row }) => h(DataTableRowActions, {
            editHref: route('expenses.edit', row.original.id),
            onDelete: () => router.delete(route('expenses.destroy', row.original.id)),
            deleteMessage: 'Are you sure you want to delete this expense? This action cannot be undone.',
        }),
    },
];
</script>

<template>
    <Head title="Expenses" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center justify-between">
                <h2 class="text-xl font-semibold leading-tight text-foreground">
                    Expenses
                </h2>
                <Button as-child>
                    <Link :href="route('expenses.create')">
                        Add Expense
                    </Link>
                </Button>
            </div>
        </template>

        <DataTable :columns="columns" :data="expenses.data" :pagination="expenses">
            <template #empty>
                No expenses found. Click "Add Expense" to record one.
            </template>
        </DataTable>
    </AuthenticatedLayout>
</template>
