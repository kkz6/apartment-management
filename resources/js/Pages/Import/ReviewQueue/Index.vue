<script setup lang="ts">
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, router, usePage } from '@inertiajs/vue3';
import { computed, ref } from 'vue';
import { Card, CardContent } from '@/Components/ui/card';
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/Components/ui/table';
import { Badge } from '@/Components/ui/badge';
import { Button } from '@/Components/ui/button';
import { Label } from '@/Components/ui/label';
import { Alert, AlertDescription } from '@/Components/ui/alert';
import {
    AlertDialog,
    AlertDialogAction,
    AlertDialogCancel,
    AlertDialogContent,
    AlertDialogDescription,
    AlertDialogFooter,
    AlertDialogHeader,
    AlertDialogTitle,
    AlertDialogTrigger,
} from '@/Components/ui/alert-dialog';

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
    transactions: Transaction[];
    units: Unit[];
}>();

const flash = computed(() => usePage().props.flash as { success?: string } | undefined);

const expandedRow = ref<number | null>(null);
const activeAction = ref<string | null>(null);
const selectedUnitId = ref<string>('');
const selectedCategory = ref<string>('');

const toggleRow = (id: number, action: string) => {
    if (expandedRow.value === id && activeAction.value === action) {
        expandedRow.value = null;
        activeAction.value = null;
        return;
    }

    expandedRow.value = id;
    activeAction.value = action;
    selectedUnitId.value = '';
    selectedCategory.value = '';
};

const assignPayment = (transactionId: number) => {
    if (!selectedUnitId.value) {
        return;
    }

    router.post(route('review-queue.assign-payment', transactionId), {
        unit_id: selectedUnitId.value,
    }, {
        onSuccess: () => {
            expandedRow.value = null;
            activeAction.value = null;
        },
    });
};

const assignExpense = (transactionId: number) => {
    if (!selectedCategory.value) {
        return;
    }

    router.post(route('review-queue.assign-expense', transactionId), {
        category: selectedCategory.value,
    }, {
        onSuccess: () => {
            expandedRow.value = null;
            activeAction.value = null;
        },
    });
};

const dismiss = (transactionId: number) => {
    router.post(route('review-queue.dismiss', transactionId));
};

const formatCurrency = (amount: string): string => {
    return new Intl.NumberFormat('en-IN', {
        style: 'currency',
        currency: 'INR',
        minimumFractionDigits: 0,
    }).format(Number(amount));
};

const formatDate = (date: string): string => {
    return new Date(date).toLocaleDateString('en-IN', {
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
</script>

<template>
    <Head title="Review Queue" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center justify-between">
                <h2 class="text-xl font-semibold leading-tight text-foreground">
                    Review Queue
                </h2>
                <Badge v-if="transactions.length" variant="outline">
                    {{ transactions.length }} unmatched
                </Badge>
            </div>
        </template>

        <Alert v-if="flash?.success" class="mb-4">
                    <AlertDescription>{{ flash.success }}</AlertDescription>
                </Alert>

                <Card>
                    <CardContent class="p-0">
                        <div v-if="transactions.length">
                            <Table>
                                <TableHeader>
                                    <TableRow>
                                        <TableHead>Sender</TableHead>
                                        <TableHead class="text-right">Amount</TableHead>
                                        <TableHead>Date</TableHead>
                                        <TableHead>Direction</TableHead>
                                        <TableHead>Source</TableHead>
                                        <TableHead class="text-right">Actions</TableHead>
                                    </TableRow>
                                </TableHeader>
                                <TableBody>
                                    <template v-for="txn in transactions" :key="txn.id">
                                        <TableRow>
                                            <TableCell class="font-medium">
                                                {{ txn.sender_name ?? '-' }}
                                            </TableCell>
                                            <TableCell class="text-right">
                                                {{ formatCurrency(txn.amount) }}
                                            </TableCell>
                                            <TableCell class="text-muted-foreground">
                                                {{ formatDate(txn.date) }}
                                            </TableCell>
                                            <TableCell>
                                                <Badge :variant="directionBadgeVariant(txn.direction)">
                                                    {{ txn.direction }}
                                                </Badge>
                                            </TableCell>
                                            <TableCell>
                                                <Badge variant="outline">
                                                    {{ sourceLabel(txn.upload_type) }}
                                                </Badge>
                                            </TableCell>
                                            <TableCell class="text-right">
                                                <div class="flex items-center justify-end gap-1">
                                                    <Button
                                                        variant="ghost"
                                                        size="sm"
                                                        @click="toggleRow(txn.id, 'payment')"
                                                    >
                                                        Assign to Unit
                                                    </Button>
                                                    <Button
                                                        variant="ghost"
                                                        size="sm"
                                                        @click="toggleRow(txn.id, 'expense')"
                                                    >
                                                        Expense
                                                    </Button>
                                                    <AlertDialog>
                                                        <AlertDialogTrigger as-child>
                                                            <Button
                                                                variant="ghost"
                                                                size="sm"
                                                                class="text-muted-foreground"
                                                            >
                                                                Dismiss
                                                            </Button>
                                                        </AlertDialogTrigger>
                                                        <AlertDialogContent>
                                                            <AlertDialogHeader>
                                                                <AlertDialogTitle>Dismiss transaction?</AlertDialogTitle>
                                                                <AlertDialogDescription>
                                                                    Are you sure you want to dismiss this transaction? This action cannot be undone.
                                                                </AlertDialogDescription>
                                                            </AlertDialogHeader>
                                                            <AlertDialogFooter>
                                                                <AlertDialogCancel>Cancel</AlertDialogCancel>
                                                                <AlertDialogAction @click="dismiss(txn.id)">
                                                                    Dismiss
                                                                </AlertDialogAction>
                                                            </AlertDialogFooter>
                                                        </AlertDialogContent>
                                                    </AlertDialog>
                                                </div>
                                            </TableCell>
                                        </TableRow>

                                        <TableRow v-if="expandedRow === txn.id && activeAction === 'payment'" class="bg-muted/50">
                                            <TableCell :colspan="6">
                                                <div class="flex items-end gap-4">
                                                    <div class="flex-1">
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
                                                    <Button
                                                        size="sm"
                                                        :disabled="!selectedUnitId"
                                                        @click="assignPayment(txn.id)"
                                                    >
                                                        Assign
                                                    </Button>
                                                    <Button
                                                        variant="outline"
                                                        size="sm"
                                                        @click="expandedRow = null; activeAction = null"
                                                    >
                                                        Cancel
                                                    </Button>
                                                </div>
                                            </TableCell>
                                        </TableRow>

                                        <TableRow v-if="expandedRow === txn.id && activeAction === 'expense'" class="bg-muted/50">
                                            <TableCell :colspan="6">
                                                <div class="flex items-end gap-4">
                                                    <div class="flex-1">
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
                                                    <Button
                                                        size="sm"
                                                        :disabled="!selectedCategory"
                                                        @click="assignExpense(txn.id)"
                                                    >
                                                        Mark as Expense
                                                    </Button>
                                                    <Button
                                                        variant="outline"
                                                        size="sm"
                                                        @click="expandedRow = null; activeAction = null"
                                                    >
                                                        Cancel
                                                    </Button>
                                                </div>
                                            </TableCell>
                                        </TableRow>
                                    </template>
                                </TableBody>
                            </Table>
                        </div>

                        <p v-else class="p-6 text-muted-foreground">
                            No unmatched transactions. All transactions have been reviewed.
                        </p>
                    </CardContent>
                </Card>
    </AuthenticatedLayout>
</template>
