<script setup lang="ts">
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';

interface UnitOption {
    id: number;
    flat_number: string;
}

interface Charge {
    id: number;
    unit_id: number;
    type: string;
    description: string;
    amount: string;
    billing_month: string;
    due_date: string | null;
}

const props = defineProps<{
    charge: Charge;
    units: UnitOption[];
}>();

const form = useForm({
    unit_id: String(props.charge.unit_id),
    type: props.charge.type,
    description: props.charge.description,
    amount: props.charge.amount,
    billing_month: props.charge.billing_month,
    due_date: props.charge.due_date ?? '',
});

const submit = () => {
    form.put(route('charges.update', props.charge.id));
};
</script>

<template>
    <Head title="Edit Charge" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-xl font-semibold leading-tight text-gray-800">
                Edit Charge
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
                                        :value="String(unit.id)"
                                    >
                                        {{ unit.flat_number }}
                                    </option>
                                </select>
                                <InputError class="mt-2" :message="form.errors.unit_id" />
                            </div>

                            <div>
                                <InputLabel for="type" value="Type" />
                                <select
                                    id="type"
                                    v-model="form.type"
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                    required
                                >
                                    <option value="maintenance">Maintenance</option>
                                    <option value="ad-hoc">Ad-hoc</option>
                                </select>
                                <InputError class="mt-2" :message="form.errors.type" />
                            </div>

                            <div>
                                <InputLabel for="description" value="Description" />
                                <TextInput
                                    id="description"
                                    type="text"
                                    class="mt-1 block w-full"
                                    v-model="form.description"
                                    required
                                    autofocus
                                />
                                <InputError class="mt-2" :message="form.errors.description" />
                            </div>

                            <div>
                                <InputLabel for="amount" value="Amount" />
                                <TextInput
                                    id="amount"
                                    type="number"
                                    class="mt-1 block w-full"
                                    v-model="form.amount"
                                    required
                                    min="0"
                                    step="0.01"
                                />
                                <InputError class="mt-2" :message="form.errors.amount" />
                            </div>

                            <div>
                                <InputLabel for="billing_month" value="Billing Month" />
                                <input
                                    id="billing_month"
                                    type="month"
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                    v-model="form.billing_month"
                                    required
                                />
                                <InputError class="mt-2" :message="form.errors.billing_month" />
                            </div>

                            <div>
                                <InputLabel for="due_date" value="Due Date" />
                                <TextInput
                                    id="due_date"
                                    type="date"
                                    class="mt-1 block w-full"
                                    v-model="form.due_date"
                                />
                                <InputError class="mt-2" :message="form.errors.due_date" />
                            </div>

                            <div class="flex items-center gap-4">
                                <PrimaryButton :disabled="form.processing">Update</PrimaryButton>
                                <Link
                                    :href="route('charges.index')"
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
