<script setup lang="ts">
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link } from '@inertiajs/vue3';

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
    collectedThisMonth: number;
    pendingDues: number;
    totalExpensesThisMonth: number;
    unitBalances: UnitBalance[];
    reconciliation: Reconciliation;
    recentUploads: RecentUpload[];
    currentMonth: string;
}>();

const formatCurrency = (amount: number): string => {
    return new Intl.NumberFormat('en-IN', {
        style: 'currency',
        currency: 'INR',
        minimumFractionDigits: 0,
    }).format(amount);
};

const uploadTypeBadgeClass = (type: string): string => {
    switch (type) {
        case 'gpay_screenshot':
            return 'inline-flex rounded-full bg-blue-100 px-2 text-xs font-semibold leading-5 text-blue-800';
        case 'bank_statement':
            return 'inline-flex rounded-full bg-purple-100 px-2 text-xs font-semibold leading-5 text-purple-800';
        default:
            return 'inline-flex rounded-full bg-gray-100 px-2 text-xs font-semibold leading-5 text-gray-800';
    }
};

const uploadStatusBadgeClass = (status: string): string => {
    switch (status) {
        case 'processed':
            return 'inline-flex rounded-full bg-green-100 px-2 text-xs font-semibold leading-5 text-green-800';
        case 'processing':
            return 'inline-flex rounded-full bg-yellow-100 px-2 text-xs font-semibold leading-5 text-yellow-800';
        case 'failed':
            return 'inline-flex rounded-full bg-red-100 px-2 text-xs font-semibold leading-5 text-red-800';
        default:
            return 'inline-flex rounded-full bg-gray-100 px-2 text-xs font-semibold leading-5 text-gray-800';
    }
};

const formatUploadType = (type: string): string => {
    return type.replace(/_/g, ' ').replace(/\b\w/g, (c) => c.toUpperCase());
};
</script>

<template>
    <Head title="Dashboard" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-xl font-semibold leading-tight text-gray-800">
                Dashboard &mdash; {{ currentMonth }}
            </h2>
        </template>

        <div class="py-12">
            <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
                <!-- Collection Overview -->
                <div class="mb-8">
                    <h3 class="mb-4 text-lg font-medium text-gray-900">
                        Collection Overview
                    </h3>
                    <div class="grid grid-cols-1 gap-6 sm:grid-cols-3">
                        <div class="overflow-hidden rounded-lg bg-green-50 shadow-sm">
                            <div class="p-6">
                                <div class="text-sm font-medium text-green-600">
                                    Collected This Month
                                </div>
                                <div class="mt-2 text-3xl font-semibold text-green-900">
                                    {{ formatCurrency(collectedThisMonth) }}
                                </div>
                            </div>
                        </div>

                        <div class="overflow-hidden rounded-lg bg-amber-50 shadow-sm">
                            <div class="p-6">
                                <div class="text-sm font-medium text-amber-600">
                                    Pending Dues
                                </div>
                                <div class="mt-2 text-3xl font-semibold text-amber-900">
                                    {{ formatCurrency(pendingDues) }}
                                </div>
                            </div>
                        </div>

                        <div class="overflow-hidden rounded-lg bg-red-50 shadow-sm">
                            <div class="p-6">
                                <div class="text-sm font-medium text-red-600">
                                    Expenses This Month
                                </div>
                                <div class="mt-2 text-3xl font-semibold text-red-900">
                                    {{ formatCurrency(totalExpensesThisMonth) }}
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Unit-wise Balance -->
                <div class="mb-8">
                    <h3 class="mb-4 text-lg font-medium text-gray-900">
                        Unit-wise Balance
                    </h3>
                    <div class="overflow-hidden bg-white shadow-sm sm:rounded-lg">
                        <div class="p-6 text-gray-900">
                            <table class="min-w-full divide-y divide-gray-200" v-if="unitBalances.length">
                                <thead>
                                    <tr>
                                        <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">
                                            Unit
                                        </th>
                                        <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">
                                            Resident
                                        </th>
                                        <th class="px-6 py-3 text-right text-xs font-medium uppercase tracking-wider text-gray-500">
                                            Total Due
                                        </th>
                                        <th class="px-6 py-3 text-right text-xs font-medium uppercase tracking-wider text-gray-500">
                                            Total Paid
                                        </th>
                                        <th class="px-6 py-3 text-right text-xs font-medium uppercase tracking-wider text-gray-500">
                                            Balance
                                        </th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-200 bg-white">
                                    <tr v-for="unit in unitBalances" :key="unit.id">
                                        <td class="whitespace-nowrap px-6 py-4 text-sm font-medium text-gray-900">
                                            {{ unit.flat_number }}
                                            <span class="ml-1 text-xs text-gray-400">{{ unit.flat_type }}</span>
                                        </td>
                                        <td class="whitespace-nowrap px-6 py-4 text-sm text-gray-500">
                                            {{ unit.resident }}
                                        </td>
                                        <td class="whitespace-nowrap px-6 py-4 text-right text-sm text-gray-900">
                                            {{ formatCurrency(unit.total_due) }}
                                        </td>
                                        <td class="whitespace-nowrap px-6 py-4 text-right text-sm text-gray-900">
                                            {{ formatCurrency(unit.total_paid) }}
                                        </td>
                                        <td class="whitespace-nowrap px-6 py-4 text-right text-sm font-medium"
                                            :class="unit.balance > 0 ? 'text-red-600' : 'text-green-600'">
                                            {{ formatCurrency(unit.balance) }}
                                        </td>
                                    </tr>
                                </tbody>
                            </table>

                            <p v-else class="text-gray-500">
                                No units found.
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Reconciliation Status -->
                <div class="mb-8">
                    <h3 class="mb-4 text-lg font-medium text-gray-900">
                        Reconciliation Status
                    </h3>
                    <div class="grid grid-cols-1 gap-6 sm:grid-cols-3">
                        <div class="overflow-hidden rounded-lg bg-white shadow-sm">
                            <div class="p-6">
                                <div class="text-sm font-medium text-yellow-600">
                                    Pending Verification
                                </div>
                                <div class="mt-2 text-3xl font-semibold text-gray-900">
                                    {{ reconciliation.pending_verification }}
                                </div>
                            </div>
                        </div>

                        <div class="overflow-hidden rounded-lg bg-white shadow-sm">
                            <div class="p-6">
                                <div class="text-sm font-medium text-green-600">
                                    Bank Verified
                                </div>
                                <div class="mt-2 text-3xl font-semibold text-gray-900">
                                    {{ reconciliation.bank_verified }}
                                </div>
                            </div>
                        </div>

                        <div class="overflow-hidden rounded-lg bg-white shadow-sm">
                            <div class="p-6">
                                <div class="text-sm font-medium text-red-600">
                                    Unmatched
                                </div>
                                <div class="mt-2 text-3xl font-semibold text-gray-900">
                                    {{ reconciliation.unmatched }}
                                </div>
                                <Link
                                    :href="route('review-queue.index')"
                                    class="mt-2 inline-block text-sm text-indigo-600 hover:text-indigo-900"
                                    v-if="reconciliation.unmatched > 0"
                                >
                                    Review Queue &rarr;
                                </Link>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Recent Uploads -->
                <div class="mb-8">
                    <div class="mb-4 flex items-center justify-between">
                        <h3 class="text-lg font-medium text-gray-900">
                            Recent Uploads
                        </h3>
                        <Link
                            :href="route('uploads.index')"
                            class="text-sm text-indigo-600 hover:text-indigo-900"
                        >
                            View All &rarr;
                        </Link>
                    </div>
                    <div class="overflow-hidden bg-white shadow-sm sm:rounded-lg">
                        <div class="p-6 text-gray-900">
                            <table class="min-w-full divide-y divide-gray-200" v-if="recentUploads.length">
                                <thead>
                                    <tr>
                                        <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">
                                            Type
                                        </th>
                                        <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">
                                            Status
                                        </th>
                                        <th class="px-6 py-3 text-right text-xs font-medium uppercase tracking-wider text-gray-500">
                                            Transactions
                                        </th>
                                        <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">
                                            Date
                                        </th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-200 bg-white">
                                    <tr v-for="upload in recentUploads" :key="upload.id">
                                        <td class="whitespace-nowrap px-6 py-4 text-sm">
                                            <span :class="uploadTypeBadgeClass(upload.type)">
                                                {{ formatUploadType(upload.type) }}
                                            </span>
                                        </td>
                                        <td class="whitespace-nowrap px-6 py-4 text-sm">
                                            <span :class="uploadStatusBadgeClass(upload.status)">
                                                {{ upload.status }}
                                            </span>
                                        </td>
                                        <td class="whitespace-nowrap px-6 py-4 text-right text-sm text-gray-900">
                                            {{ upload.transactions_count }}
                                        </td>
                                        <td class="whitespace-nowrap px-6 py-4 text-sm text-gray-500">
                                            {{ upload.created_at }}
                                        </td>
                                    </tr>
                                </tbody>
                            </table>

                            <p v-else class="text-gray-500">
                                No uploads yet.
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
