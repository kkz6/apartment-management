<script setup lang="ts">
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';

interface Unit {
    id: number;
    flat_number: string;
    flat_type: string;
    floor: number;
    area_sqft: number | null;
}

const props = defineProps<{
    unit: Unit;
}>();

const form = useForm({
    flat_number: props.unit.flat_number,
    flat_type: props.unit.flat_type,
    floor: String(props.unit.floor),
    area_sqft: props.unit.area_sqft ? String(props.unit.area_sqft) : '',
});

const submit = () => {
    form.put(route('units.update', props.unit.id));
};
</script>

<template>
    <Head title="Edit Unit" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-xl font-semibold leading-tight text-gray-800">
                Edit Unit
            </h2>
        </template>

        <div class="py-12">
            <div class="mx-auto max-w-2xl sm:px-6 lg:px-8">
                <div class="overflow-hidden bg-white shadow-sm sm:rounded-lg">
                    <div class="p-6 text-gray-900">
                        <form @submit.prevent="submit" class="space-y-6">
                            <div>
                                <InputLabel for="flat_number" value="Flat Number" />
                                <TextInput
                                    id="flat_number"
                                    type="text"
                                    class="mt-1 block w-full"
                                    v-model="form.flat_number"
                                    required
                                    autofocus
                                />
                                <InputError class="mt-2" :message="form.errors.flat_number" />
                            </div>

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
                                <InputLabel for="floor" value="Floor" />
                                <TextInput
                                    id="floor"
                                    type="number"
                                    class="mt-1 block w-full"
                                    v-model="form.floor"
                                    required
                                    min="0"
                                />
                                <InputError class="mt-2" :message="form.errors.floor" />
                            </div>

                            <div>
                                <InputLabel for="area_sqft" value="Area (sqft)" />
                                <TextInput
                                    id="area_sqft"
                                    type="number"
                                    class="mt-1 block w-full"
                                    v-model="form.area_sqft"
                                    min="0"
                                    step="0.01"
                                />
                                <InputError class="mt-2" :message="form.errors.area_sqft" />
                            </div>

                            <div class="flex items-center gap-4">
                                <PrimaryButton :disabled="form.processing">Update</PrimaryButton>
                                <Link
                                    :href="route('units.index')"
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
