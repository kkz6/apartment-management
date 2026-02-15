<script setup lang="ts">
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, router, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';

interface Upload {
    id: number;
    file_path: string;
    type: string;
    status: string;
    processed_at: string | null;
    uploaded_by: string | null;
    created_at: string;
    transactions_count: number;
}

defineProps<{
    uploads: Upload[];
}>();

const flash = computed(() => usePage().props.flash as { success?: string } | undefined);

const deleteUpload = (id: number) => {
    if (confirm('Are you sure you want to delete this upload?')) {
        router.delete(route('uploads.destroy', id));
    }
};

const typeLabel = (type: string): string => {
    switch (type) {
        case 'gpay_screenshot':
            return 'GPay Screenshot';
        case 'bank_statement':
            return 'Bank Statement';
        default:
            return type;
    }
};

const typeBadgeClass = (type: string): string => {
    switch (type) {
        case 'gpay_screenshot':
            return 'inline-flex rounded-full bg-purple-100 px-2 text-xs font-semibold leading-5 text-purple-800';
        case 'bank_statement':
            return 'inline-flex rounded-full bg-indigo-100 px-2 text-xs font-semibold leading-5 text-indigo-800';
        default:
            return 'inline-flex rounded-full bg-gray-100 px-2 text-xs font-semibold leading-5 text-gray-800';
    }
};

const statusBadgeClass = (status: string): string => {
    switch (status) {
        case 'pending':
            return 'inline-flex rounded-full bg-yellow-100 px-2 text-xs font-semibold leading-5 text-yellow-800';
        case 'processing':
            return 'inline-flex rounded-full bg-blue-100 px-2 text-xs font-semibold leading-5 text-blue-800';
        case 'processed':
            return 'inline-flex rounded-full bg-green-100 px-2 text-xs font-semibold leading-5 text-green-800';
        case 'failed':
            return 'inline-flex rounded-full bg-red-100 px-2 text-xs font-semibold leading-5 text-red-800';
        default:
            return 'inline-flex rounded-full bg-gray-100 px-2 text-xs font-semibold leading-5 text-gray-800';
    }
};

const fileName = (path: string): string => {
    return path.split('/').pop() ?? path;
};
</script>

<template>
    <Head title="Uploads" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center justify-between">
                <h2 class="text-xl font-semibold leading-tight text-gray-800">
                    Uploads
                </h2>
                <Link
                    :href="route('uploads.create')"
                    class="inline-flex items-center rounded-md border border-transparent bg-gray-800 px-4 py-2 text-xs font-semibold uppercase tracking-widest text-white transition duration-150 ease-in-out hover:bg-gray-700 focus:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 active:bg-gray-900"
                >
                    Upload File
                </Link>
            </div>
        </template>

        <div class="py-12">
            <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
                <div
                    v-if="flash?.success"
                    class="mb-4 rounded-md bg-green-50 p-4"
                >
                    <p class="text-sm font-medium text-green-800">
                        {{ flash.success }}
                    </p>
                </div>

                <div class="overflow-hidden bg-white shadow-sm sm:rounded-lg">
                    <div class="p-6 text-gray-900">
                        <table class="min-w-full divide-y divide-gray-200" v-if="uploads.length">
                            <thead>
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">
                                        File
                                    </th>
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
                                        Uploaded By
                                    </th>
                                    <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">
                                        Date
                                    </th>
                                    <th class="px-6 py-3 text-right text-xs font-medium uppercase tracking-wider text-gray-500">
                                        Actions
                                    </th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200 bg-white">
                                <tr v-for="upload in uploads" :key="upload.id">
                                    <td class="whitespace-nowrap px-6 py-4 text-sm text-gray-900">
                                        {{ fileName(upload.file_path) }}
                                    </td>
                                    <td class="whitespace-nowrap px-6 py-4 text-sm">
                                        <span :class="typeBadgeClass(upload.type)">
                                            {{ typeLabel(upload.type) }}
                                        </span>
                                    </td>
                                    <td class="whitespace-nowrap px-6 py-4 text-sm">
                                        <span :class="statusBadgeClass(upload.status)">
                                            {{ upload.status }}
                                        </span>
                                    </td>
                                    <td class="whitespace-nowrap px-6 py-4 text-right text-sm text-gray-900">
                                        {{ upload.transactions_count }}
                                    </td>
                                    <td class="whitespace-nowrap px-6 py-4 text-sm text-gray-500">
                                        {{ upload.uploaded_by ?? '-' }}
                                    </td>
                                    <td class="whitespace-nowrap px-6 py-4 text-sm text-gray-500">
                                        {{ upload.created_at }}
                                    </td>
                                    <td class="whitespace-nowrap px-6 py-4 text-right text-sm font-medium">
                                        <button
                                            @click="deleteUpload(upload.id)"
                                            class="text-red-600 hover:text-red-900"
                                        >
                                            Delete
                                        </button>
                                    </td>
                                </tr>
                            </tbody>
                        </table>

                        <p v-else class="text-gray-500">
                            No uploads found. Click "Upload File" to get started.
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
