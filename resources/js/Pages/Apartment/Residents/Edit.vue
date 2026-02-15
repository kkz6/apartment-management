<script setup lang="ts">
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import Checkbox from '@/Components/Checkbox.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';

interface UnitOption {
    id: number;
    flat_number: string;
}

interface Resident {
    id: number;
    unit_id: number;
    name: string;
    phone: string | null;
    email: string | null;
    is_owner: boolean;
    gpay_name: string | null;
}

const props = defineProps<{
    resident: Resident;
    units: UnitOption[];
}>();

const form = useForm({
    unit_id: props.resident.unit_id,
    name: props.resident.name,
    phone: props.resident.phone ?? '',
    email: props.resident.email ?? '',
    is_owner: props.resident.is_owner,
    gpay_name: props.resident.gpay_name ?? '',
});

const submit = () => {
    form.put(route('residents.update', props.resident.id));
};
</script>

<template>
    <Head title="Edit Resident" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-xl font-semibold leading-tight text-gray-800">
                Edit Resident
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
                                <InputLabel for="name" value="Name" />
                                <TextInput
                                    id="name"
                                    type="text"
                                    class="mt-1 block w-full"
                                    v-model="form.name"
                                    required
                                    autofocus
                                />
                                <InputError class="mt-2" :message="form.errors.name" />
                            </div>

                            <div>
                                <InputLabel for="phone" value="Phone" />
                                <TextInput
                                    id="phone"
                                    type="text"
                                    class="mt-1 block w-full"
                                    v-model="form.phone"
                                />
                                <InputError class="mt-2" :message="form.errors.phone" />
                            </div>

                            <div>
                                <InputLabel for="email" value="Email" />
                                <TextInput
                                    id="email"
                                    type="email"
                                    class="mt-1 block w-full"
                                    v-model="form.email"
                                />
                                <InputError class="mt-2" :message="form.errors.email" />
                            </div>

                            <div class="flex items-center">
                                <Checkbox
                                    id="is_owner"
                                    :checked="form.is_owner"
                                    @update:checked="form.is_owner = $event"
                                />
                                <InputLabel for="is_owner" value="Is Owner" class="ml-2" />
                                <InputError class="mt-2" :message="form.errors.is_owner" />
                            </div>

                            <div>
                                <InputLabel for="gpay_name" value="GPay Name" />
                                <TextInput
                                    id="gpay_name"
                                    type="text"
                                    class="mt-1 block w-full"
                                    v-model="form.gpay_name"
                                />
                                <InputError class="mt-2" :message="form.errors.gpay_name" />
                            </div>

                            <div class="flex items-center gap-4">
                                <PrimaryButton :disabled="form.processing">Update</PrimaryButton>
                                <Link
                                    :href="route('residents.index')"
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
