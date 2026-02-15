<script setup lang="ts">
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { computed } from 'vue';

const form = useForm({
    file: null as File | null,
    type: 'gpay_screenshot',
});

const acceptedFileTypes = computed(() => {
    if (form.type === 'gpay_screenshot') {
        return 'image/*';
    }

    return '.pdf';
});

const handleFileChange = (event: Event) => {
    const target = event.target as HTMLInputElement;

    if (target.files && target.files.length > 0) {
        form.file = target.files[0];
    }
};

const submit = () => {
    form.post(route('uploads.store'), {
        forceFormData: true,
    });
};
</script>

<template>
    <Head title="Upload File" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-xl font-semibold leading-tight text-gray-800">
                Upload File
            </h2>
        </template>

        <div class="py-12">
            <div class="mx-auto max-w-2xl sm:px-6 lg:px-8">
                <div class="overflow-hidden bg-white shadow-sm sm:rounded-lg">
                    <div class="p-6 text-gray-900">
                        <form @submit.prevent="submit" class="space-y-6">
                            <div>
                                <InputLabel for="type" value="File Type" />
                                <select
                                    id="type"
                                    v-model="form.type"
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                    required
                                >
                                    <option value="gpay_screenshot">GPay Screenshot</option>
                                    <option value="bank_statement">Bank Statement (PDF)</option>
                                </select>
                                <InputError class="mt-2" :message="form.errors.type" />
                            </div>

                            <div>
                                <InputLabel for="file" value="File" />
                                <input
                                    id="file"
                                    type="file"
                                    :accept="acceptedFileTypes"
                                    @change="handleFileChange"
                                    class="mt-1 block w-full text-sm text-gray-500 file:mr-4 file:rounded-md file:border-0 file:bg-gray-800 file:px-4 file:py-2 file:text-sm file:font-semibold file:text-white hover:file:bg-gray-700"
                                    required
                                />
                                <p class="mt-1 text-sm text-gray-500">
                                    <template v-if="form.type === 'gpay_screenshot'">
                                        Upload a screenshot of a GPay transaction. Max 10MB.
                                    </template>
                                    <template v-else>
                                        Upload an HDFC bank statement PDF. Max 10MB.
                                    </template>
                                </p>
                                <InputError class="mt-2" :message="form.errors.file" />
                            </div>

                            <div class="flex items-center gap-4">
                                <PrimaryButton :disabled="form.processing">
                                    Upload
                                </PrimaryButton>
                                <Link
                                    :href="route('uploads.index')"
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
