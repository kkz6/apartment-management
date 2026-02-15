<script setup lang="ts">
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';

const form = useForm({
    billing_month: '',
    due_date: '',
});

const submit = () => {
    form.post(route('charges.generate.store'));
};
</script>

<template>
    <Head title="Generate Monthly Charges" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-xl font-semibold leading-tight text-gray-800">
                Generate Monthly Charges
            </h2>
        </template>

        <div class="py-12">
            <div class="mx-auto max-w-2xl sm:px-6 lg:px-8">
                <div class="overflow-hidden bg-white shadow-sm sm:rounded-lg">
                    <div class="p-6 text-gray-900">
                        <p class="mb-6 text-sm text-gray-600">
                            This will generate maintenance charges for all units based on their flat type and the current maintenance slab rates. Units that already have a maintenance charge for the selected month will be skipped.
                        </p>

                        <form @submit.prevent="submit" class="space-y-6">
                            <div>
                                <InputLabel for="billing_month" value="Billing Month" />
                                <input
                                    id="billing_month"
                                    type="month"
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                    v-model="form.billing_month"
                                    required
                                    autofocus
                                />
                                <InputError class="mt-2" :message="form.errors.billing_month" />
                            </div>

                            <div>
                                <InputLabel for="due_date" value="Due Date" />
                                <input
                                    id="due_date"
                                    type="date"
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                    v-model="form.due_date"
                                />
                                <InputError class="mt-2" :message="form.errors.due_date" />
                            </div>

                            <div class="flex items-center gap-4">
                                <PrimaryButton :disabled="form.processing">
                                    Generate Charges
                                </PrimaryButton>
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
