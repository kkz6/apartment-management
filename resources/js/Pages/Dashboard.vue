<script setup lang="ts">
import type { ColumnDef } from '@tanstack/vue-table'
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link } from '@inertiajs/vue3';
import { h } from 'vue';
import { Card, CardContent, CardHeader, CardTitle } from '@/Components/ui/card';
import { Badge } from '@/Components/ui/badge';
import { Button } from '@/Components/ui/button';
import { DataTable, DataTableColumnHeader } from '@/Components/ui/data-table';

interface UnitBalance {
    id: number;
    flat_number: string;
    flat_type: string;
    resident: string;
    total_due: number;
    total_paid: number;
    balance: number;
}

interface Reconciliation {
    pending_verification: number;
    bank_verified: number;
    unmatched: number;
}

interface RecentUpload {
    id: number;
    type: string;
    status: string;
    created_at: string;
    transactions_count: number;
}

const props = defineProps<{
    collectedThisQuarter: number;
    pendingDues: number;
    totalExpensesThisQuarter: number;
    unitBalances: UnitBalance[];
    reconciliation: Reconciliation;
    recentUploads: RecentUpload[];
    currentQuarter: string;
}>();

const formatCurrency = (amount: number): string => {
    return new Intl.NumberFormat('en-IN', {
        style: 'currency',
        currency: 'INR',
        minimumFractionDigits: 0,
    }).format(amount);
};

const uploadTypeBadgeVariant = (type: string): 'default' | 'secondary' | 'outline' => {
    switch (type) {
        case 'gpay_screenshot':
            return 'default';
        case 'bank_statement':
            return 'secondary';
        default:
            return 'outline';
    }
};

const uploadStatusBadgeVariant = (status: string): 'default' | 'secondary' | 'destructive' | 'outline' => {
    switch (status) {
        case 'processed':
            return 'default';
        case 'processing':
            return 'secondary';
        case 'failed':
            return 'destructive';
        default:
            return 'outline';
    }
};

const formatUploadType = (type: string): string => {
    return type.replace(/_/g, ' ').replace(/\b\w/g, (c) => c.toUpperCase());
};

const unitBalanceColumns: ColumnDef<UnitBalance>[] = [
    {
        accessorKey: 'flat_number',
        header: 'Unit',
        cell: ({ row }) => h('div', { class: 'font-medium' }, [
            h('span', row.getValue('flat_number') as string),
            h(Badge, { variant: 'outline', class: 'ml-1' }, () => row.original.flat_type),
        ]),
    },
    {
        accessorKey: 'resident',
        header: 'Resident',
    },
    {
        accessorKey: 'total_due',
        header: ({ column }) => h(DataTableColumnHeader, { column, title: 'Total Due', class: 'justify-end' }),
        cell: ({ row }) => h('div', { class: 'text-right' }, formatCurrency(row.getValue('total_due'))),
    },
    {
        accessorKey: 'total_paid',
        header: ({ column }) => h(DataTableColumnHeader, { column, title: 'Total Paid', class: 'justify-end' }),
        cell: ({ row }) => h('div', { class: 'text-right' }, formatCurrency(row.getValue('total_paid'))),
    },
    {
        accessorKey: 'balance',
        header: ({ column }) => h(DataTableColumnHeader, { column, title: 'Balance', class: 'justify-end' }),
        cell: ({ row }) => {
            const balance = row.getValue('balance') as number;
            return h('div', {
                class: ['text-right font-medium', balance > 0 ? 'text-red-600' : 'text-green-600'],
            }, formatCurrency(balance));
        },
    },
];

const recentUploadColumns: ColumnDef<RecentUpload>[] = [
    {
        accessorKey: 'type',
        header: 'Type',
        cell: ({ row }) => h(Badge, { variant: uploadTypeBadgeVariant(row.getValue('type')) }, () => formatUploadType(row.getValue('type'))),
    },
    {
        accessorKey: 'status',
        header: 'Status',
        cell: ({ row }) => h(Badge, { variant: uploadStatusBadgeVariant(row.getValue('status')) }, () => row.getValue('status')),
    },
    {
        accessorKey: 'transactions_count',
        header: () => h('div', { class: 'text-right' }, 'Transactions'),
        cell: ({ row }) => h('div', { class: 'text-right' }, row.getValue('transactions_count')),
    },
    {
        accessorKey: 'created_at',
        header: 'Date',
        cell: ({ row }) => h('div', { class: 'text-muted-foreground' }, row.getValue('created_at')),
    },
];
</script>

<template>
    <Head title="Dashboard" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-xl font-semibold leading-tight text-foreground">
                Dashboard &mdash; {{ currentQuarter }}
            </h2>
        </template>

        <!-- Collection Overview -->
        <div class="mb-8">
            <h3 class="mb-4 text-lg font-medium text-foreground">
                Collection Overview
            </h3>
            <div class="grid grid-cols-1 gap-6 sm:grid-cols-3">
                <Card>
                    <CardHeader class="pb-2">
                        <CardTitle class="text-sm font-medium text-green-600">
                            Collected This Quarter
                        </CardTitle>
                    </CardHeader>
                    <CardContent>
                        <div class="text-3xl font-semibold">
                            {{ formatCurrency(collectedThisQuarter) }}
                        </div>
                    </CardContent>
                </Card>

                <Card>
                    <CardHeader class="pb-2">
                        <CardTitle class="text-sm font-medium text-amber-600">
                            Pending Dues
                        </CardTitle>
                    </CardHeader>
                    <CardContent>
                        <div class="text-3xl font-semibold">
                            {{ formatCurrency(pendingDues) }}
                        </div>
                    </CardContent>
                </Card>

                <Card>
                    <CardHeader class="pb-2">
                        <CardTitle class="text-sm font-medium text-red-600">
                            Expenses This Quarter
                        </CardTitle>
                    </CardHeader>
                    <CardContent>
                        <div class="text-3xl font-semibold">
                            {{ formatCurrency(totalExpensesThisQuarter) }}
                        </div>
                    </CardContent>
                </Card>
            </div>
        </div>

        <!-- Unit-wise Balance -->
        <div class="mb-8">
            <h3 class="mb-4 text-lg font-medium text-foreground">
                Unit-wise Balance
            </h3>
            <DataTable :columns="unitBalanceColumns" :data="unitBalances">
                <template #empty>No units found.</template>
            </DataTable>
        </div>

        <!-- Reconciliation Status -->
        <div class="mb-8">
            <h3 class="mb-4 text-lg font-medium text-foreground">
                Reconciliation Status
            </h3>
            <div class="grid grid-cols-1 gap-6 sm:grid-cols-3">
                <Card>
                    <CardHeader class="pb-2">
                        <CardTitle class="text-sm font-medium text-yellow-600">
                            Pending Verification
                        </CardTitle>
                    </CardHeader>
                    <CardContent>
                        <div class="text-3xl font-semibold">
                            {{ reconciliation.pending_verification }}
                        </div>
                    </CardContent>
                </Card>

                <Card>
                    <CardHeader class="pb-2">
                        <CardTitle class="text-sm font-medium text-green-600">
                            Bank Verified
                        </CardTitle>
                    </CardHeader>
                    <CardContent>
                        <div class="text-3xl font-semibold">
                            {{ reconciliation.bank_verified }}
                        </div>
                    </CardContent>
                </Card>

                <Card>
                    <CardHeader class="pb-2">
                        <CardTitle class="text-sm font-medium text-red-600">
                            Unmatched
                        </CardTitle>
                    </CardHeader>
                    <CardContent>
                        <div class="text-3xl font-semibold">
                            {{ reconciliation.unmatched }}
                        </div>
                        <Button variant="link" as-child class="mt-1 h-auto p-0" v-if="reconciliation.unmatched > 0">
                            <Link :href="route('review-queue.index')">
                                Review Queue &rarr;
                            </Link>
                        </Button>
                    </CardContent>
                </Card>
            </div>
        </div>

        <!-- Recent Uploads -->
        <div class="mb-8">
            <div class="mb-4 flex items-center justify-between">
                <h3 class="text-lg font-medium text-foreground">
                    Recent Uploads
                </h3>
                <Button variant="link" as-child class="h-auto p-0">
                    <Link :href="route('uploads.index')">View All &rarr;</Link>
                </Button>
            </div>
            <DataTable :columns="recentUploadColumns" :data="recentUploads">
                <template #empty>No uploads yet.</template>
            </DataTable>
        </div>
    </AuthenticatedLayout>
</template>
