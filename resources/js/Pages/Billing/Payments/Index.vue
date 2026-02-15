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
}

interface ChargeInfo {
    id: number;
    description: string;
    billing_month: string;
    amount: string;
}

interface Payment {
    id: number;
    unit_id: number;
    unit: Unit | null;
    charge_id: number | null;
    charge: ChargeInfo | null;
    amount: string;
    paid_date: string;
    source: string;
    reference_number: string | null;
    reconciliation_status: string;
}

const props = defineProps<{
    payments: PaginationData<Payment>;
}>();

const formatCurrency = (amount: string): string => {
    return new Intl.NumberFormat('en-IN', {
        style: 'currency',
        currency: 'INR',
        minimumFractionDigits: 0,
    }).format(Number(amount));
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

const columns: ColumnDef<Payment>[] = [
    {
        accessorKey: 'paid_date',
        header: ({ column }) => h(DataTableColumnHeader, { column, title: 'Date' }),
        cell: ({ row }) => h('div', { class: 'whitespace-nowrap' }, formatDate(row.getValue('paid_date'))),
    },
    {
        accessorKey: 'unit',
        header: 'Unit',
        cell: ({ row }) => h('span', { class: 'whitespace-nowrap text-muted-foreground' }, row.original.unit?.flat_number ?? '-'),
    },
    {
        accessorKey: 'amount',
        header: ({ column }) => h(DataTableColumnHeader, { column, title: 'Amount', class: 'justify-end' }),
        cell: ({ row }) => h('div', { class: 'whitespace-nowrap text-right' }, formatCurrency(row.getValue('amount'))),
    },
    {
        accessorKey: 'source',
        header: 'Source',
        cell: ({ row }) => h(Badge, { variant: 'outline' }, () => sourceLabel(row.getValue('source'))),
    },
    {
        accessorKey: 'charge',
        header: 'Charge',
        cell: ({ row }) => {
            const charge = row.original.charge;
            if (!charge) {
                return h('span', { class: 'text-muted-foreground' }, '-');
            }
            return h('span', { class: 'text-muted-foreground' }, `${charge.description} (${charge.billing_month})`);
        },
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
            editHref: route('payments.edit', row.original.id),
            onDelete: () => router.delete(route('payments.destroy', row.original.id)),
            deleteMessage: 'Are you sure you want to delete this payment? This action cannot be undone.',
        }),
    },
];
</script>

<template>
    <Head title="Payments" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center justify-between">
                <h2 class="text-xl font-semibold leading-tight text-foreground">
                    Payments
                </h2>
                <Button as-child>
                    <Link :href="route('payments.create')">
                        Record Payment
                    </Link>
                </Button>
            </div>
        </template>

        <DataTable :columns="columns" :data="payments.data" :pagination="payments">
            <template #empty>
                No payments found. Click "Record Payment" to add one.
            </template>
        </DataTable>
    </AuthenticatedLayout>
</template>
