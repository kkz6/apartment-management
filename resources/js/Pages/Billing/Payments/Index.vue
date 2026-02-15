<script setup lang="ts">
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';

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

defineProps<{
    payments: Payment[];
}>();

const deletePayment = (id: number) => {
    if (confirm('Are you sure you want to delete this payment?')) {
        router.delete(route('payments.destroy', id));
    }
};

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

const reconciliationBadgeClass = (status: string): string => {
    switch (status) {
        case 'matched':
            return 'inline-flex rounded-full bg-green-100 px-2 text-xs font-semibold leading-5 text-green-800';
        case 'manual':
            return 'inline-flex rounded-full bg-blue-100 px-2 text-xs font-semibold leading-5 text-blue-800';
        default:
            return 'inline-flex rounded-full bg-gray-100 px-2 text-xs font-semibold leading-5 text-gray-800';
    }
};
</script>

<template>
    <Head title="Payments" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center justify-between">
                <h2 class="text-xl font-semibold leading-tight text-gray-800">
                    Payments
                </h2>
                <Link
                    :href="route('payments.create')"
                    class="inline-flex items-center rounded-md border border-transparent bg-gray-800 px-4 py-2 text-xs font-semibold uppercase tracking-widest text-white transition duration-150 ease-in-out hover:bg-gray-700 focus:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 active:bg-gray-900"
                >
                    Record Payment
                </Link>
            </div>
        </template>

        <div class="py-12">
            <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
                <div class="overflow-hidden bg-white shadow-sm sm:rounded-lg">
                    <div class="p-6 text-gray-900">
                        <table class="min-w-full divide-y divide-gray-200" v-if="payments.length">
                            <thead>
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">
                                        Date
                                    </th>
                                    <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">
                                        Unit
                                    </th>
                                    <th class="px-6 py-3 text-right text-xs font-medium uppercase tracking-wider text-gray-500">
                                        Amount
                                    </th>
                                    <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">
                                        Source
                                    </th>
                                    <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">
                                        Charge
                                    </th>
                                    <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">
                                        Status
                                    </th>
                                    <th class="px-6 py-3 text-right text-xs font-medium uppercase tracking-wider text-gray-500">
                                        Actions
                                    </th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200 bg-white">
                                <tr v-for="payment in payments" :key="payment.id">
                                    <td class="whitespace-nowrap px-6 py-4 text-sm text-gray-900">
                                        {{ payment.paid_date }}
                                    </td>
                                    <td class="whitespace-nowrap px-6 py-4 text-sm text-gray-500">
                                        {{ payment.unit?.flat_number ?? '-' }}
                                    </td>
                                    <td class="whitespace-nowrap px-6 py-4 text-right text-sm text-gray-900">
                                        {{ formatCurrency(payment.amount) }}
                                    </td>
                                    <td class="whitespace-nowrap px-6 py-4 text-sm text-gray-500">
                                        {{ sourceLabel(payment.source) }}
                                    </td>
                                    <td class="px-6 py-4 text-sm text-gray-500">
                                        <template v-if="payment.charge">
                                            {{ payment.charge.description }} ({{ payment.charge.billing_month }})
                                        </template>
                                        <template v-else>
                                            -
                                        </template>
                                    </td>
                                    <td class="whitespace-nowrap px-6 py-4 text-sm">
                                        <span :class="reconciliationBadgeClass(payment.reconciliation_status)">
                                            {{ payment.reconciliation_status }}
                                        </span>
                                    </td>
                                    <td class="whitespace-nowrap px-6 py-4 text-right text-sm font-medium">
                                        <Link
                                            :href="route('payments.edit', payment.id)"
                                            class="text-indigo-600 hover:text-indigo-900"
                                        >
                                            Edit
                                        </Link>
                                        <button
                                            @click="deletePayment(payment.id)"
                                            class="ml-4 text-red-600 hover:text-red-900"
                                        >
                                            Delete
                                        </button>
                                    </td>
                                </tr>
                            </tbody>
                        </table>

                        <p v-else class="text-gray-500">
                            No payments found. Click "Record Payment" to add one.
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
