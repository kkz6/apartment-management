<script setup lang="ts">
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';

const form = useForm({
    description: '',
    amount: '',
    paid_date: '',
    category: 'maintenance',
    source: 'gpay',
    reference_number: '',
});

const submit = () => {
    form.post(route('expenses.store'));
};
</script>

<template>
    <Head title="Add Expense" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-xl font-semibold leading-tight text-gray-800">
                Add Expense
            </h2>
        </template>

        <div class="py-12">
            <div class="mx-auto max-w-2xl sm:px-6 lg:px-8">
                <div class="overflow-hidden bg-white shadow-sm sm:rounded-lg">
                    <div class="p-6 text-gray-900">
                        <form @submit.prevent="submit" class="space-y-6">
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
                                <InputLabel for="category" value="Category" />
                                <select
                                    id="category"
                                    v-model="form.category"
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                    required
                                >
                                    <option value="electricity">Electricity</option>
                                    <option value="water">Water</option>
                                    <option value="maintenance">Maintenance</option>
                                    <option value="service">Service</option>
                                    <option value="other">Other</option>
                                </select>
                                <InputError class="mt-2" :message="form.errors.category" />
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
                                    :href="route('expenses.index')"
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
