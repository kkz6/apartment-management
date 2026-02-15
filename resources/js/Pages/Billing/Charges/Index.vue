<script setup lang="ts">
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { ref } from 'vue';

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
    charges: Charge[];
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

const deleteCharge = (id: number) => {
    if (confirm('Are you sure you want to delete this charge?')) {
        router.delete(route('charges.destroy', id));
    }
};

const statusBadgeClass = (status: string): string => {
    switch (status) {
        case 'paid':
            return 'inline-flex rounded-full bg-green-100 px-2 text-xs font-semibold leading-5 text-green-800';
        case 'partial':
            return 'inline-flex rounded-full bg-yellow-100 px-2 text-xs font-semibold leading-5 text-yellow-800';
        default:
            return 'inline-flex rounded-full bg-red-100 px-2 text-xs font-semibold leading-5 text-red-800';
    }
};

const formatCurrency = (amount: string): string => {
    return new Intl.NumberFormat('en-IN', {
        style: 'currency',
        currency: 'INR',
        minimumFractionDigits: 0,
    }).format(Number(amount));
};
</script>

<template>
    <Head title="Charges" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center justify-between">
                <h2 class="text-xl font-semibold leading-tight text-gray-800">
                    Charges
                </h2>
                <div class="flex items-center gap-3">
                    <Link
                        :href="route('charges.generate')"
                        class="inline-flex items-center rounded-md border border-gray-300 bg-white px-4 py-2 text-xs font-semibold uppercase tracking-widest text-gray-700 shadow-sm transition duration-150 ease-in-out hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2"
                    >
                        Generate Monthly
                    </Link>
                    <Link
                        :href="route('charges.create')"
                        class="inline-flex items-center rounded-md border border-transparent bg-gray-800 px-4 py-2 text-xs font-semibold uppercase tracking-widest text-white transition duration-150 ease-in-out hover:bg-gray-700 focus:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 active:bg-gray-900"
                    >
                        Add Charge
                    </Link>
                </div>
            </div>
        </template>

        <div class="py-12">
            <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
                <div class="mb-4 flex items-center gap-3">
                    <input
                        type="month"
                        v-model="billingMonth"
                        class="rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                        placeholder="Filter by month"
                    />
                    <button
                        @click="applyFilter"
                        class="inline-flex items-center rounded-md border border-transparent bg-indigo-600 px-3 py-2 text-sm font-medium text-white hover:bg-indigo-700"
                    >
                        Filter
                    </button>
                    <button
                        v-if="billingMonth"
                        @click="clearFilter"
                        class="text-sm text-gray-500 underline hover:text-gray-700"
                    >
                        Clear
                    </button>
                </div>

                <div class="overflow-hidden bg-white shadow-sm sm:rounded-lg">
                    <div class="p-6 text-gray-900">
                        <table class="min-w-full divide-y divide-gray-200" v-if="charges.length">
                            <thead>
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">
                                        Billing Month
                                    </th>
                                    <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">
                                        Unit
                                    </th>
                                    <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">
                                        Type
                                    </th>
                                    <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">
                                        Description
                                    </th>
                                    <th class="px-6 py-3 text-right text-xs font-medium uppercase tracking-wider text-gray-500">
                                        Amount
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
                                <tr v-for="charge in charges" :key="charge.id">
                                    <td class="whitespace-nowrap px-6 py-4 text-sm text-gray-900">
                                        {{ charge.billing_month }}
                                    </td>
                                    <td class="whitespace-nowrap px-6 py-4 text-sm text-gray-500">
                                        {{ charge.unit?.flat_number ?? '-' }}
                                    </td>
                                    <td class="whitespace-nowrap px-6 py-4 text-sm text-gray-500">
                                        {{ charge.type }}
                                    </td>
                                    <td class="px-6 py-4 text-sm text-gray-500">
                                        {{ charge.description }}
                                    </td>
                                    <td class="whitespace-nowrap px-6 py-4 text-right text-sm text-gray-900">
                                        {{ formatCurrency(charge.amount) }}
                                    </td>
                                    <td class="whitespace-nowrap px-6 py-4 text-sm">
                                        <span :class="statusBadgeClass(charge.status)">
                                            {{ charge.status }}
                                        </span>
                                    </td>
                                    <td class="whitespace-nowrap px-6 py-4 text-right text-sm font-medium">
                                        <Link
                                            :href="route('charges.edit', charge.id)"
                                            class="text-indigo-600 hover:text-indigo-900"
                                        >
                                            Edit
                                        </Link>
                                        <button
                                            @click="deleteCharge(charge.id)"
                                            class="ml-4 text-red-600 hover:text-red-900"
                                        >
                                            Delete
                                        </button>
                                    </td>
                                </tr>
                            </tbody>
                        </table>

                        <p v-else class="text-gray-500">
                            No charges found. Click "Add Charge" to create one or "Generate Monthly" for bulk generation.
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
