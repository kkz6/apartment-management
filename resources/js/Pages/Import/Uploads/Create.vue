<script setup lang="ts">
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import InputError from '@/Components/InputError.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { computed } from 'vue';
import { Card, CardContent, CardHeader, CardTitle } from '@/Components/ui/card';
import { Label } from '@/Components/ui/label';
import { Input } from '@/Components/ui/input';
import { Button } from '@/Components/ui/button';

const form = useForm({
    file: null as File | null,
    type: 'gpay_screenshot',
    password: '',
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
            <h2 class="text-xl font-semibold leading-tight text-foreground">
                Upload File
            </h2>
        </template>

        <div class="max-w-2xl">
            <Card>
                <CardHeader>
                    <CardTitle>Upload File</CardTitle>
                </CardHeader>
                <CardContent>
                    <form @submit.prevent="submit" class="space-y-6">
                        <div>
                            <Label for="type">File Type</Label>
                            <select
                                id="type"
                                v-model="form.type"
                                class="mt-1 flex h-9 w-full rounded-md border border-input bg-transparent px-3 py-1 text-sm shadow-sm transition-colors focus-visible:outline-none focus-visible:ring-1 focus-visible:ring-ring"
                                required
                            >
                                <option value="gpay_screenshot">GPay Screenshot</option>
                                <option value="bank_statement">Bank Statement (PDF)</option>
                            </select>
                            <InputError class="mt-2" :message="form.errors.type" />
                        </div>

                        <div>
                            <Label for="file">File</Label>
                            <input
                                id="file"
                                type="file"
                                :accept="acceptedFileTypes"
                                @change="handleFileChange"
                                class="mt-1 block w-full text-sm text-muted-foreground file:mr-4 file:rounded-md file:border-0 file:bg-primary file:px-4 file:py-2 file:text-sm file:font-semibold file:text-primary-foreground hover:file:bg-primary/90"
                                required
                            />
                            <p class="mt-1 text-sm text-muted-foreground">
                                <template v-if="form.type === 'gpay_screenshot'">
                                    Upload a screenshot of a GPay transaction. Max 10MB.
                                </template>
                                <template v-else>
                                    Upload an HDFC bank statement PDF. Max 10MB.
                                </template>
                            </p>
                            <InputError class="mt-2" :message="form.errors.file" />
                        </div>

                        <div v-if="form.type === 'bank_statement'">
                            <Label for="password">PDF Password (optional)</Label>
                            <Input
                                id="password"
                                type="password"
                                v-model="form.password"
                                class="mt-1 block w-full"
                                placeholder="Leave blank if not password-protected"
                            />
                            <p class="mt-1 text-sm text-muted-foreground">
                                If your bank statement PDF is password-protected, enter the password here.
                            </p>
                            <InputError class="mt-2" :message="form.errors.password" />
                        </div>

                        <div class="flex items-center gap-4">
                            <Button :disabled="form.processing">
                                Upload
                            </Button>
                            <Link
                                :href="route('uploads.index')"
                                class="text-sm text-muted-foreground underline hover:text-foreground"
                            >
                                Cancel
                            </Link>
                        </div>
                    </form>
                </CardContent>
            </Card>
        </div>
    </AuthenticatedLayout>
</template>
