<script setup lang="ts">
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';

interface MaintenanceSlab {
    id: number;
    flat_type: string;
    amount: string;
    effective_from: string;
}

const props = defineProps<{
    slab: MaintenanceSlab;
}>();

const form = useForm({
    flat_type: props.slab.flat_type,
    amount: props.slab.amount,
    effective_from: props.slab.effective_from.substring(0, 10),
});

const submit = () => {
    form.put(route('maintenance-slabs.update', props.slab.id));
};
</script>

<template>
    <Head title="Edit Maintenance Slab" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-xl font-semibold leading-tight text-gray-800">
                Edit Maintenance Slab
            </h2>
        </template>

        <div class="py-12">
            <div class="mx-auto max-w-2xl sm:px-6 lg:px-8">
                <div class="overflow-hidden bg-white shadow-sm sm:rounded-lg">
                    <div class="p-6 text-gray-900">
                        <form @submit.prevent="submit" class="space-y-6">
                            <div>
                                <InputLabel for="flat_type" value="Flat Type" />
                                <select
                                    id="flat_type"
                                    v-model="form.flat_type"
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                    required
                                >
                                    <option value="1BHK">1BHK</option>
                                    <option value="2BHK">2BHK</option>
                                    <option value="3BHK">3BHK</option>
                                </select>
                                <InputError class="mt-2" :message="form.errors.flat_type" />
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
                                <InputLabel for="effective_from" value="Effective From" />
                                <TextInput
                                    id="effective_from"
                                    type="date"
                                    class="mt-1 block w-full"
                                    v-model="form.effective_from"
                                    required
                                />
                                <InputError class="mt-2" :message="form.errors.effective_from" />
                            </div>

                            <div class="flex items-center gap-4">
                                <PrimaryButton :disabled="form.processing">Update</PrimaryButton>
                                <Link
                                    :href="route('maintenance-slabs.index')"
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
