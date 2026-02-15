<script setup lang="ts">
import type { ColumnDef } from '@tanstack/vue-table'
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { h, ref } from 'vue';
import { Button } from '@/Components/ui/button';
import { Input } from '@/Components/ui/input';
import { Badge } from '@/Components/ui/badge';
import { DataTable, DataTableColumnHeader, DataTableRowActions } from '@/Components/ui/data-table';
import type { PaginationData } from '@/types';

interface Unit {
    id: number;
    flat_number: string;
}

interface Charge {
    id: number;
    unit_id: number;
    unit: Unit | null;
    type: string;
    description: string;
    amount: string;
    billing_month: string;
    due_date: string | null;
    status: string;
}

interface Filters {
    billing_month: string;
}

const props = defineProps<{
    charges: PaginationData<Charge>;
    filters: Filters;
}>();

const billingMonth = ref(props.filters.billing_month);

const applyFilter = () => {
    router.get(route('charges.index'), {
        billing_month: billingMonth.value || undefined,
    }, {
        preserveState: true,
        replace: true,
    });
};

const clearFilter = () => {
    billingMonth.value = '';
    router.get(route('charges.index'), {}, {
        preserveState: true,
        replace: true,
    });
};

const statusBadgeVariant = (status: string): 'default' | 'secondary' | 'destructive' => {
    switch (status) {
        case 'paid':
            return 'default';
        case 'partial':
            return 'secondary';
        default:
            return 'destructive';
    }
};

const formatCurrency = (amount: string): string => {
    return new Intl.NumberFormat('en-IN', {
        style: 'currency',
        currency: 'INR',
        minimumFractionDigits: 0,
    }).format(Number(amount));
};

const columns: ColumnDef<Charge>[] = [
    {
        accessorKey: 'billing_month',
        header: 'Billing Quarter',
        cell: ({ row }) => h('div', { class: 'font-medium' }, row.getValue('billing_month')),
    },
    {
        accessorKey: 'unit',
        header: 'Unit',
        cell: ({ row }) => h('span', { class: 'text-muted-foreground' }, row.original.unit?.flat_number ?? '-'),
    },
    {
        accessorKey: 'type',
        header: 'Type',
        cell: ({ row }) => h(Badge, { variant: 'outline' }, () => row.getValue('type')),
    },
    {
        accessorKey: 'description',
        header: 'Description',
        cell: ({ row }) => h('span', { class: 'text-muted-foreground' }, row.getValue('description')),
    },
    {
        accessorKey: 'amount',
        header: ({ column }) => h(DataTableColumnHeader, { column, title: 'Amount', class: 'justify-end' }),
        cell: ({ row }) => h('div', { class: 'text-right' }, formatCurrency(row.getValue('amount'))),
    },
    {
        accessorKey: 'status',
        header: 'Status',
        cell: ({ row }) => {
            const status = row.getValue('status') as string;
            return h(Badge, {
                variant: statusBadgeVariant(status),
                class: status === 'paid' ? 'bg-green-600' : '',
            }, () => status);
        },
    },
    {
        id: 'actions',
        cell: ({ row }) => h(DataTableRowActions, {
            editHref: route('charges.edit', row.original.id),
            onDelete: () => router.delete(route('charges.destroy', row.original.id)),
            deleteMessage: 'Are you sure you want to delete this charge? This action cannot be undone.',
        }),
    },
];
</script>

<template>
    <Head title="Charges" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center justify-between">
                <h2 class="text-xl font-semibold leading-tight text-foreground">
                    Charges
                </h2>
                <div class="flex items-center gap-3">
                    <Link :href="route('charges.generate')">
                        <Button variant="outline">Generate Quarterly</Button>
                    </Link>
                    <Link :href="route('charges.create')">
                        <Button>Add Charge</Button>
                    </Link>
                </div>
            </div>
        </template>

        <DataTable :columns="columns" :data="charges.data" :pagination="charges">
            <template #toolbar>
                <div class="mb-4 flex items-center gap-3">
                    <Input
                        type="text"
                        v-model="billingMonth"
                        placeholder="e.g. 2026-Q1"
                        class="max-w-xs"
                    />
                    <Button @click="applyFilter" size="sm">Filter</Button>
                    <Button
                        v-if="billingMonth"
                        @click="clearFilter"
                        variant="link"
                        size="sm"
                    >
                        Clear
                    </Button>
                </div>
            </template>
            <template #empty>
                No charges found. Click "Add Charge" to create one or "Generate Quarterly" for bulk generation.
            </template>
        </DataTable>
    </AuthenticatedLayout>
</template>
