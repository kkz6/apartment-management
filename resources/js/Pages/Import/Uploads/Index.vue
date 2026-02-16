<script setup lang="ts">
import type { ColumnDef } from '@tanstack/vue-table'
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, router, usePage } from '@inertiajs/vue3';
import { computed, h, ref } from 'vue';
import { Badge } from '@/Components/ui/badge';
import { Button } from '@/Components/ui/button';
import { Input } from '@/Components/ui/input';
import { Label } from '@/Components/ui/label';
import { Alert, AlertDescription } from '@/Components/ui/alert';
import { DataTable, DataTableColumnHeader } from '@/Components/ui/data-table';
import UploadRowActions from './UploadRowActions.vue';
import {
    AlertDialog,
    AlertDialogAction,
    AlertDialogCancel,
    AlertDialogContent,
    AlertDialogDescription,
    AlertDialogFooter,
    AlertDialogHeader,
    AlertDialogTitle,
} from '@/Components/ui/alert-dialog';
import {
    Dialog,
    DialogContent,
    DialogDescription,
    DialogFooter,
    DialogHeader,
    DialogTitle,
} from '@/Components/ui/dialog';
import type { PaginationData } from '@/types';

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

const props = defineProps<{
    uploads: PaginationData<Upload>;
}>();

const flash = computed(() => usePage().props.flash as { success?: string; error?: string } | undefined);

const showRetryConfirm = ref(false);
const retryUploadId = ref<number | null>(null);

const showBankRetryDialog = ref(false);
const bankRetryUploadId = ref<number | null>(null);
const bankRetryPassword = ref('');

const canRetry = (status: string): boolean => {
    return status === 'failed' || status === 'processed';
};

const startRetry = (upload: Upload): void => {
    if (upload.type === 'bank_statement') {
        bankRetryUploadId.value = upload.id;
        bankRetryPassword.value = '';
        showBankRetryDialog.value = true;
    } else {
        retryUploadId.value = upload.id;
        showRetryConfirm.value = true;
    }
};

const confirmGpayRetry = (): void => {
    if (!retryUploadId.value) {
        return;
    }

    router.post(route('uploads.retry', retryUploadId.value));
    retryUploadId.value = null;
};

const confirmBankRetry = (): void => {
    if (!bankRetryUploadId.value) {
        return;
    }

    router.post(route('uploads.retry', bankRetryUploadId.value), {
        password: bankRetryPassword.value || null,
    });
    showBankRetryDialog.value = false;
    bankRetryUploadId.value = null;
    bankRetryPassword.value = '';
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

const typeBadgeVariant = (type: string): 'default' | 'secondary' | 'outline' => {
    switch (type) {
        case 'gpay_screenshot':
            return 'default';
        case 'bank_statement':
            return 'secondary';
        default:
            return 'outline';
    }
};

const statusBadgeVariant = (status: string): 'default' | 'secondary' | 'destructive' | 'outline' => {
    switch (status) {
        case 'processed':
            return 'default';
        case 'processing':
            return 'secondary';
        case 'failed':
            return 'destructive';
        case 'pending':
            return 'outline';
        default:
            return 'outline';
    }
};

const fileName = (path: string): string => {
    return path.split('/').pop() ?? path;
};

const columns: ColumnDef<Upload>[] = [
    {
        accessorKey: 'file_path',
        header: 'File',
        cell: ({ row }) => h('div', { class: 'font-medium' }, fileName(row.getValue('file_path'))),
    },
    {
        accessorKey: 'type',
        header: 'Type',
        cell: ({ row }) => h(Badge, { variant: typeBadgeVariant(row.getValue('type')) }, () => typeLabel(row.getValue('type'))),
    },
    {
        accessorKey: 'status',
        header: 'Status',
        cell: ({ row }) => h(Badge, { variant: statusBadgeVariant(row.getValue('status')) }, () => row.getValue('status')),
    },
    {
        accessorKey: 'transactions_count',
        header: ({ column }) => h(DataTableColumnHeader, { column, title: 'Transactions', class: 'justify-end' }),
        cell: ({ row }) => h('div', { class: 'text-right' }, row.getValue('transactions_count')),
    },
    {
        accessorKey: 'uploaded_by',
        header: 'Uploaded By',
        cell: ({ row }) => h('span', { class: 'text-muted-foreground' }, row.getValue('uploaded_by') ?? '-'),
    },
    {
        accessorKey: 'created_at',
        header: 'Date',
        cell: ({ row }) => h('span', { class: 'text-muted-foreground' }, row.getValue('created_at')),
    },
    {
        id: 'actions',
        cell: ({ row }) => h(UploadRowActions, {
            showRetry: canRetry(row.original.status),
            onRetry: () => startRetry(row.original),
            onDelete: () => router.delete(route('uploads.destroy', row.original.id)),
        }),
    },
];
</script>

<template>
    <Head title="Uploads" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center justify-between">
                <h2 class="text-xl font-semibold leading-tight text-foreground">
                    Uploads
                </h2>
                <Button as-child>
                    <Link :href="route('uploads.create')">
                        Upload File
                    </Link>
                </Button>
            </div>
        </template>

        <Alert v-if="flash?.success" class="mb-4">
            <AlertDescription>{{ flash.success }}</AlertDescription>
        </Alert>

        <Alert v-if="flash?.error" variant="destructive" class="mb-4">
            <AlertDescription>{{ flash.error }}</AlertDescription>
        </Alert>

        <DataTable :columns="columns" :data="uploads.data" :pagination="uploads">
            <template #empty>
                No uploads found. Click "Upload File" to get started.
            </template>
        </DataTable>

        <AlertDialog v-model:open="showRetryConfirm">
            <AlertDialogContent>
                <AlertDialogHeader>
                    <AlertDialogTitle>Retry processing?</AlertDialogTitle>
                    <AlertDialogDescription>
                        This will delete all existing parsed transactions for this upload and reprocess the file.
                    </AlertDialogDescription>
                </AlertDialogHeader>
                <AlertDialogFooter>
                    <AlertDialogCancel>Cancel</AlertDialogCancel>
                    <AlertDialogAction @click="confirmGpayRetry">
                        Retry
                    </AlertDialogAction>
                </AlertDialogFooter>
            </AlertDialogContent>
        </AlertDialog>

        <Dialog v-model:open="showBankRetryDialog">
            <DialogContent>
                <DialogHeader>
                    <DialogTitle>Retry bank statement processing</DialogTitle>
                    <DialogDescription>
                        This will delete all existing parsed transactions and reprocess the file.
                        If the PDF is password-protected, enter the password below.
                    </DialogDescription>
                </DialogHeader>
                <div class="grid gap-2 py-4">
                    <Label for="retry-password">PDF Password (optional)</Label>
                    <Input
                        id="retry-password"
                        v-model="bankRetryPassword"
                        type="password"
                        placeholder="Leave blank if not encrypted"
                        @keydown.enter="confirmBankRetry"
                    />
                </div>
                <DialogFooter>
                    <Button variant="outline" @click="showBankRetryDialog = false">
                        Cancel
                    </Button>
                    <Button @click="confirmBankRetry">
                        Retry
                    </Button>
                </DialogFooter>
            </DialogContent>
        </Dialog>
    </AuthenticatedLayout>
</template>
