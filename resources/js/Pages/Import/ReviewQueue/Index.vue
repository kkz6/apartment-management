<script setup lang="ts">
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, router, usePage } from '@inertiajs/vue3';
import { computed, ref } from 'vue';

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
    if (confirm('Are you sure you want to dismiss this transaction?')) {
        router.post(route('review-queue.dismiss', transactionId));
    }
};

const formatCurrency = (amount: string): string => {
    return new Intl.NumberFormat('en-IN', {
        style: 'currency',
        currency: 'INR',
        minimumFractionDigits: 0,
    }).format(Number(amount));
};

const directionBadgeClass = (direction: string): string => {
    switch (direction) {
        case 'credit':
            return 'inline-flex rounded-full bg-green-100 px-2 text-xs font-semibold leading-5 text-green-800';
        case 'debit':
            return 'inline-flex rounded-full bg-red-100 px-2 text-xs font-semibold leading-5 text-red-800';
        default:
            return 'inline-flex rounded-full bg-gray-100 px-2 text-xs font-semibold leading-5 text-gray-800';
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
                <h2 class="text-xl font-semibold leading-tight text-gray-800">
                    Review Queue
                </h2>
                <span
                    v-if="transactions.length"
                    class="inline-flex items-center rounded-full bg-yellow-100 px-3 py-1 text-sm font-medium text-yellow-800"
                >
                    {{ transactions.length }} unmatched
                </span>
            </div>
        </template>

        <div class="py-12">
            <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
                <div
                    v-if="flash?.success"
                    class="mb-4 rounded-md bg-green-50 p-4"
                >
                    <p class="text-sm font-medium text-green-800">
                        {{ flash.success }}
                    </p>
                </div>

                <div class="overflow-hidden bg-white shadow-sm sm:rounded-lg">
                    <div class="p-6 text-gray-900">
                        <div v-if="transactions.length">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead>
                                    <tr>
                                        <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">
                                            Sender
                                        </th>
                                        <th class="px-6 py-3 text-right text-xs font-medium uppercase tracking-wider text-gray-500">
                                            Amount
                                        </th>
                                        <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">
                                            Date
                                        </th>
                                        <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">
                                            Direction
                                        </th>
                                        <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">
                                            Source
                                        </th>
                                        <th class="px-6 py-3 text-right text-xs font-medium uppercase tracking-wider text-gray-500">
                                            Actions
                                        </th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-200 bg-white">
                                    <template v-for="txn in transactions" :key="txn.id">
                                        <tr>
                                            <td class="whitespace-nowrap px-6 py-4 text-sm text-gray-900">
                                                {{ txn.sender_name ?? '-' }}
                                            </td>
                                            <td class="whitespace-nowrap px-6 py-4 text-right text-sm text-gray-900">
                                                {{ formatCurrency(txn.amount) }}
                                            </td>
                                            <td class="whitespace-nowrap px-6 py-4 text-sm text-gray-500">
                                                {{ txn.date }}
                                            </td>
                                            <td class="whitespace-nowrap px-6 py-4 text-sm">
                                                <span :class="directionBadgeClass(txn.direction)">
                                                    {{ txn.direction }}
                                                </span>
                                            </td>
                                            <td class="whitespace-nowrap px-6 py-4 text-sm text-gray-500">
                                                {{ sourceLabel(txn.upload_type) }}
                                            </td>
                                            <td class="whitespace-nowrap px-6 py-4 text-right text-sm font-medium">
                                                <button
                                                    @click="toggleRow(txn.id, 'payment')"
                                                    class="text-indigo-600 hover:text-indigo-900"
                                                >
                                                    Assign to Unit
                                                </button>
                                                <button
                                                    @click="toggleRow(txn.id, 'expense')"
                                                    class="ml-3 text-amber-600 hover:text-amber-900"
                                                >
                                                    Expense
                                                </button>
                                                <button
                                                    @click="dismiss(txn.id)"
                                                    class="ml-3 text-gray-500 hover:text-gray-700"
                                                >
                                                    Dismiss
                                                </button>
                                            </td>
                                        </tr>

                                        <tr v-if="expandedRow === txn.id && activeAction === 'payment'">
                                            <td colspan="6" class="bg-gray-50 px-6 py-4">
                                                <div class="flex items-end gap-4">
                                                    <div class="flex-1">
                                                        <label class="block text-sm font-medium text-gray-700">
                                                            Select Unit
                                                        </label>
                                                        <select
                                                            v-model="selectedUnitId"
                                                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
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
                                                    <button
                                                        @click="assignPayment(txn.id)"
                                                        :disabled="!selectedUnitId"
                                                        class="inline-flex items-center rounded-md border border-transparent bg-indigo-600 px-4 py-2 text-sm font-medium text-white hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 disabled:opacity-50"
                                                    >
                                                        Assign
                                                    </button>
                                                    <button
                                                        @click="expandedRow = null; activeAction = null"
                                                        class="inline-flex items-center rounded-md border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50"
                                                    >
                                                        Cancel
                                                    </button>
                                                </div>
                                            </td>
                                        </tr>

                                        <tr v-if="expandedRow === txn.id && activeAction === 'expense'">
                                            <td colspan="6" class="bg-gray-50 px-6 py-4">
                                                <div class="flex items-end gap-4">
                                                    <div class="flex-1">
                                                        <label class="block text-sm font-medium text-gray-700">
                                                            Expense Category
                                                        </label>
                                                        <select
                                                            v-model="selectedCategory"
                                                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                                                        >
                                                            <option value="" disabled>Choose a category</option>
                                                            <option value="electricity">Electricity</option>
                                                            <option value="water">Water</option>
                                                            <option value="maintenance">Maintenance</option>
                                                            <option value="service">Service</option>
                                                            <option value="other">Other</option>
                                                        </select>
                                                    </div>
                                                    <button
                                                        @click="assignExpense(txn.id)"
                                                        :disabled="!selectedCategory"
                                                        class="inline-flex items-center rounded-md border border-transparent bg-amber-600 px-4 py-2 text-sm font-medium text-white hover:bg-amber-700 focus:outline-none focus:ring-2 focus:ring-amber-500 focus:ring-offset-2 disabled:opacity-50"
                                                    >
                                                        Mark as Expense
                                                    </button>
                                                    <button
                                                        @click="expandedRow = null; activeAction = null"
                                                        class="inline-flex items-center rounded-md border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50"
                                                    >
                                                        Cancel
                                                    </button>
                                                </div>
                                            </td>
                                        </tr>
                                    </template>
                                </tbody>
                            </table>
                        </div>

                        <p v-else class="text-gray-500">
                            No unmatched transactions. All transactions have been reviewed.
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
