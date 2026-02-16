<script setup lang="ts">
import type { ColumnDef } from '@tanstack/vue-table';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, router, usePage } from '@inertiajs/vue3';
import { computed, h, ref } from 'vue';
import type { PaginationData } from '@/types';
import { Badge } from '@/Components/ui/badge';
import { Button } from '@/Components/ui/button';
import { DatePicker } from '@/Components/ui/date-picker';
import { Input } from '@/Components/ui/input';
import { Label } from '@/Components/ui/label';
import { Alert, AlertDescription } from '@/Components/ui/alert';
import { DataTable, DataTableColumnHeader } from '@/Components/ui/data-table';
import {
    Dialog,
    DialogContent,
    DialogDescription,
    DialogFooter,
    DialogHeader,
    DialogTitle,
} from '@/Components/ui/dialog';
import {
    AlertDialog,
    AlertDialogAction,
    AlertDialogCancel,
    AlertDialogContent,
    AlertDialogDescription,
    AlertDialogFooter,
    AlertDialogHeader,
    AlertDialogTitle,
} from '@/Components/ui/alert-dialog';
import ReviewQueueRowActions from './ReviewQueueRowActions.vue';

interface Resident {
    id: number;
    unit_id: number;
    name: string;
}

interface Unit {
    id: number;
    flat_number: string;
    residents: Resident[];
}

interface Transaction {
    id: number;
    sender_name: string | null;
    amount: string;
    date: string;
    direction: string;
    raw_text: string | null;
    upload_type: string | null;
}

const props = defineProps<{
    transactions: PaginationData<Transaction>;
    units: Unit[];
}>();

const flash = computed(() => usePage().props.flash as { success?: string } | undefined);

const dialogOpen = ref(false);
const dialogAction = ref<'payment' | 'expense' | null>(null);
const activeTxn = ref<Transaction | null>(null);
const selectedUnitId = ref<string>('');
const selectedCategory = ref<string>('');
const description = ref<string>('');
const selectedDate = ref<string>('');
const dismissDialogOpen = ref(false);
const dismissTxnId = ref<number | null>(null);

const openDialog = (txn: Transaction, action: 'payment' | 'expense') => {
    activeTxn.value = txn;
    dialogAction.value = action;
    selectedUnitId.value = '';
    selectedCategory.value = '';
    description.value = '';
    selectedDate.value = txn.date ?? '';
    dialogOpen.value = true;
};

const closeDialog = () => {
    dialogOpen.value = false;
    activeTxn.value = null;
    dialogAction.value = null;
};

const openDismissDialog = (txnId: number) => {
    dismissTxnId.value = txnId;
    dismissDialogOpen.value = true;
};

const assignPayment = () => {
    if (!activeTxn.value || !selectedUnitId.value) {
        return;
    }

    router.post(route('review-queue.assign-payment', activeTxn.value.id), {
        unit_id: selectedUnitId.value,
        description: description.value,
        date: selectedDate.value || null,
    }, {
        onSuccess: closeDialog,
    });
};

const assignExpense = () => {
    if (!activeTxn.value || !selectedCategory.value) {
        return;
    }

    router.post(route('review-queue.assign-expense', activeTxn.value.id), {
        category: selectedCategory.value,
        description: description.value,
        date: selectedDate.value || null,
    }, {
        onSuccess: closeDialog,
    });
};

const dismiss = () => {
    if (!dismissTxnId.value) {
        return;
    }

    router.post(route('review-queue.dismiss', dismissTxnId.value), {}, {
        onSuccess: () => {
            dismissDialogOpen.value = false;
            dismissTxnId.value = null;
        },
    });
};

const formatCurrency = (amount: string): string => {
    return new Intl.NumberFormat('en-IN', {
        style: 'currency',
        currency: 'INR',
        minimumFractionDigits: 0,
    }).format(Number(amount));
};

const formatDate = (date: string | null): string => {
    if (!date) {
        return '-';
    }

    const parsed = new Date(date + 'T00:00:00');

    if (isNaN(parsed.getTime())) {
        return '-';
    }

    return parsed.toLocaleDateString('en-IN', {
        year: 'numeric',
        month: 'short',
        day: 'numeric',
    });
};

const directionBadgeVariant = (direction: string): 'default' | 'destructive' | 'outline' => {
    switch (direction) {
        case 'credit':
            return 'default';
        case 'debit':
            return 'destructive';
        default:
            return 'outline';
    }
};

const sourceLabel = (type: string | null): string => {
    switch (type) {
        case 'gpay_screenshot':
            return 'GPay';
        case 'bank_statement':
            return 'Bank';
        default:
            return 'Unknown';
    }
};

const columns: ColumnDef<Transaction>[] = [
    {
        accessorKey: 'sender_name',
        header: ({ column }) => h(DataTableColumnHeader, { column, title: 'Sender' }),
        cell: ({ row }) => h('span', { class: 'font-medium' }, row.getValue('sender_name') ?? '-'),
    },
    {
        accessorKey: 'amount',
        header: ({ column }) => h(DataTableColumnHeader, { column, title: 'Amount', class: 'justify-end' }),
        cell: ({ row }) => h('div', { class: 'whitespace-nowrap text-right' }, formatCurrency(row.getValue('amount'))),
    },
    {
        accessorKey: 'date',
        header: ({ column }) => h(DataTableColumnHeader, { column, title: 'Date' }),
        cell: ({ row }) => h('div', { class: 'whitespace-nowrap text-muted-foreground' }, formatDate(row.getValue('date'))),
    },
    {
        accessorKey: 'direction',
        header: 'Direction',
        cell: ({ row }) => h(Badge, {
            variant: directionBadgeVariant(row.getValue('direction')),
        }, () => row.getValue('direction')),
    },
    {
        accessorKey: 'upload_type',
        header: 'Source',
        cell: ({ row }) => h(Badge, { variant: 'outline' }, () => sourceLabel(row.getValue('upload_type'))),
    },
    {
        id: 'actions',
        cell: ({ row }) => h(ReviewQueueRowActions, {
            onAssignPayment: () => openDialog(row.original, 'payment'),
            onAssignExpense: () => openDialog(row.original, 'expense'),
            onDismiss: () => openDismissDialog(row.original.id),
        }),
    },
];
</script>

<template>
    <Head title="Review Queue" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center justify-between">
                <h2 class="text-xl font-semibold leading-tight text-foreground">
                    Review Queue
                </h2>
                <Badge v-if="transactions.total" variant="outline">
                    {{ transactions.total }} unmatched
                </Badge>
            </div>
        </template>

        <Alert v-if="flash?.success" class="mb-4">
            <AlertDescription>{{ flash.success }}</AlertDescription>
        </Alert>

        <DataTable :columns="columns" :data="transactions.data" :pagination="transactions">
            <template #empty>
                No unmatched transactions. All transactions have been reviewed.
            </template>
        </DataTable>

        <!-- Assign to Unit Dialog -->
        <Dialog :open="dialogOpen && dialogAction === 'payment'" @update:open="closeDialog">
            <DialogContent v-if="activeTxn">
                <DialogHeader>
                    <DialogTitle>Assign to Unit</DialogTitle>
                    <DialogDescription>
                        {{ activeTxn.sender_name ?? 'Unknown' }} &mdash;
                        {{ formatCurrency(activeTxn.amount) }} on {{ formatDate(activeTxn.date) }}
                    </DialogDescription>
                </DialogHeader>

                <div class="space-y-4">
                    <div>
                        <Label>Select Unit</Label>
                        <select
                            v-model="selectedUnitId"
                            class="mt-1 flex h-9 w-full rounded-md border border-input bg-transparent px-3 py-1 text-sm shadow-sm transition-colors focus-visible:outline-none focus-visible:ring-1 focus-visible:ring-ring"
                        >
                            <option value="" disabled>Choose a unit</option>
                            <option
                                v-for="unit in units"
                                :key="unit.id"
                                :value="unit.id"
                            >
                                {{ unit.flat_number }}
                                <template v-if="unit.residents.length">
                                    - {{ unit.residents.map(r => r.name).join(', ') }}
                                </template>
                            </option>
                        </select>
                    </div>
                    <div>
                        <Label>Date</Label>
                        <DatePicker
                            v-model="selectedDate"
                            placeholder="Pick a date"
                            class="mt-1"
                        />
                    </div>
                    <div>
                        <Label>Description (optional)</Label>
                        <Input
                            v-model="description"
                            class="mt-1"
                            placeholder="e.g. Q1 2025 maintenance, advance payment"
                        />
                    </div>
                </div>

                <DialogFooter>
                    <Button variant="outline" @click="closeDialog">Cancel</Button>
                    <Button :disabled="!selectedUnitId || !selectedDate" @click="assignPayment">
                        Assign
                    </Button>
                </DialogFooter>
            </DialogContent>
        </Dialog>

        <!-- Expense Dialog -->
        <Dialog :open="dialogOpen && dialogAction === 'expense'" @update:open="closeDialog">
            <DialogContent v-if="activeTxn">
                <DialogHeader>
                    <DialogTitle>Mark as Expense</DialogTitle>
                    <DialogDescription>
                        {{ activeTxn.sender_name ?? 'Unknown' }} &mdash;
                        {{ formatCurrency(activeTxn.amount) }} on {{ formatDate(activeTxn.date) }}
                    </DialogDescription>
                </DialogHeader>

                <div class="space-y-4">
                    <div>
                        <Label>Expense Category</Label>
                        <select
                            v-model="selectedCategory"
                            class="mt-1 flex h-9 w-full rounded-md border border-input bg-transparent px-3 py-1 text-sm shadow-sm transition-colors focus-visible:outline-none focus-visible:ring-1 focus-visible:ring-ring"
                        >
                            <option value="" disabled>Choose a category</option>
                            <option value="electricity">Electricity</option>
                            <option value="water">Water</option>
                            <option value="maintenance">Maintenance</option>
                            <option value="service">Service</option>
                            <option value="other">Other</option>
                        </select>
                    </div>
                    <div>
                        <Label>Date</Label>
                        <DatePicker
                            v-model="selectedDate"
                            placeholder="Pick a date"
                            class="mt-1"
                        />
                    </div>
                    <div>
                        <Label>Description (optional)</Label>
                        <Input
                            v-model="description"
                            class="mt-1"
                            placeholder="e.g. Common area electricity bill, plumber repair"
                        />
                    </div>
                </div>

                <DialogFooter>
                    <Button variant="outline" @click="closeDialog">Cancel</Button>
                    <Button :disabled="!selectedCategory || !selectedDate" @click="assignExpense">
                        Mark as Expense
                    </Button>
                </DialogFooter>
            </DialogContent>
        </Dialog>

        <!-- Dismiss Dialog -->
        <AlertDialog :open="dismissDialogOpen" @update:open="dismissDialogOpen = false">
            <AlertDialogContent>
                <AlertDialogHeader>
                    <AlertDialogTitle>Dismiss transaction?</AlertDialogTitle>
                    <AlertDialogDescription>
                        This will mark the transaction as reconciled. This action cannot be undone.
                    </AlertDialogDescription>
                </AlertDialogHeader>
                <AlertDialogFooter>
                    <AlertDialogCancel>Cancel</AlertDialogCancel>
                    <AlertDialogAction @click="dismiss">
                        Dismiss
                    </AlertDialogAction>
                </AlertDialogFooter>
            </AlertDialogContent>
        </AlertDialog>
    </AuthenticatedLayout>
</template>
