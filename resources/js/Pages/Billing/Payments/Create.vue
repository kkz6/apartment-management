<script setup lang="ts">
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { computed, watch } from 'vue';

interface UnitOption {
    id: number;
    flat_number: string;
}

interface PendingCharge {
    id: number;
    unit_id: number;
    description: string;
    billing_month: string;
    amount: string;
    status: string;
    unit: UnitOption | null;
}

const props = defineProps<{
    units: UnitOption[];
    pendingCharges: PendingCharge[];
}>();

const form = useForm({
    unit_id: '',
    charge_id: '',
    amount: '',
    paid_date: '',
    source: 'gpay',
    reference_number: '',
});

const filteredCharges = computed(() => {
    if (!form.unit_id) {
        return [];
    }

    return props.pendingCharges.filter(
        (charge) => charge.unit_id === Number(form.unit_id)
    );
});

watch(() => form.unit_id, () => {
    form.charge_id = '';
});

const submit = () => {
    form.post(route('payments.store'));
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
    <Head title="Record Payment" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-xl font-semibold leading-tight text-gray-800">
                Record Payment
            </h2>
        </template>

        <div class="py-12">
            <div class="mx-auto max-w-2xl sm:px-6 lg:px-8">
                <div class="overflow-hidden bg-white shadow-sm sm:rounded-lg">
                    <div class="p-6 text-gray-900">
                        <form @submit.prevent="submit" class="space-y-6">
                            <div>
                                <InputLabel for="unit_id" value="Unit" />
                                <select
                                    id="unit_id"
                                    v-model="form.unit_id"
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                    required
                                >
                                    <option value="" disabled>Select a unit</option>
                                    <option
                                        v-for="unit in units"
                                        :key="unit.id"
                                        :value="unit.id"
                                    >
                                        {{ unit.flat_number }}
                                    </option>
                                </select>
                                <InputError class="mt-2" :message="form.errors.unit_id" />
                            </div>

                            <div>
                                <InputLabel for="charge_id" value="Charge (optional)" />
                                <select
                                    id="charge_id"
                                    v-model="form.charge_id"
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                >
                                    <option value="">No linked charge</option>
                                    <option
                                        v-for="charge in filteredCharges"
                                        :key="charge.id"
                                        :value="charge.id"
                                    >
                                        {{ charge.description }} - {{ charge.billing_month }} ({{ formatCurrency(charge.amount) }}, {{ charge.status }})
                                    </option>
                                </select>
                                <p v-if="form.unit_id && filteredCharges.length === 0" class="mt-1 text-sm text-gray-500">
                                    No pending charges for this unit.
                                </p>
                                <InputError class="mt-2" :message="form.errors.charge_id" />
                            </div>

                            <div>
                                <InputLabel for="amount" value="Amount" />
                                <TextInput
                                    id="amount"
                                    type="number"
                                    class="mt-1 block w-full"
                                    v-model="form.amount"
                                    required
                                    min="0.01"
                                    step="0.01"
                                />
                                <InputError class="mt-2" :message="form.errors.amount" />
                            </div>

                            <div>
                                <InputLabel for="paid_date" value="Date Paid" />
                                <TextInput
                                    id="paid_date"
                                    type="date"
                                    class="mt-1 block w-full"
                                    v-model="form.paid_date"
                                    required
                                />
                                <InputError class="mt-2" :message="form.errors.paid_date" />
                            </div>

                            <div>
                                <InputLabel for="source" value="Source" />
                                <select
                                    id="source"
                                    v-model="form.source"
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                    required
                                >
                                    <option value="gpay">GPay</option>
                                    <option value="bank_transfer">Bank Transfer</option>
                                    <option value="cash">Cash</option>
                                </select>
                                <InputError class="mt-2" :message="form.errors.source" />
                            </div>

                            <div>
                                <InputLabel for="reference_number" value="Reference Number" />
                                <TextInput
                                    id="reference_number"
                                    type="text"
                                    class="mt-1 block w-full"
                                    v-model="form.reference_number"
                                />
                                <InputError class="mt-2" :message="form.errors.reference_number" />
                            </div>

                            <div class="flex items-center gap-4">
                                <PrimaryButton :disabled="form.processing">Save</PrimaryButton>
                                <Link
                                    :href="route('payments.index')"
                                    class="text-sm text-gray-600 underline hover:text-gray-900"
                                >
                                    Cancel
                                </Link>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
