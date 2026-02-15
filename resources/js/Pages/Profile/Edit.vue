<script setup lang="ts">
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import DeleteUserForm from './Partials/DeleteUserForm.vue';
import UpdatePasswordForm from './Partials/UpdatePasswordForm.vue';
import UpdateProfileInformationForm from './Partials/UpdateProfileInformationForm.vue';
import { Card, CardContent } from '@/Components/ui/card';
import { Head } from '@inertiajs/vue3';

defineProps<{
    mustVerifyEmail?: boolean;
    status?: string;
}>();
</script>

<template>
    <Head title="Profile" />

    <AuthenticatedLayout>
        <template #header>
            <h2
                class="text-xl font-semibold leading-tight text-foreground"
            >
                Profile
            </h2>
        </template>

        <div class="max-w-3xl space-y-6">
            <Card>
                <CardContent>
                    <UpdateProfileInformationForm
                        :must-verify-email="mustVerifyEmail"
                        :status="status"
                        class="max-w-xl"
                    />
                </CardContent>
            </Card>

            <Card>
                <CardContent>
                    <UpdatePasswordForm class="max-w-xl" />
                </CardContent>
            </Card>

            <Card>
                <CardContent>
                    <DeleteUserForm class="max-w-xl" />
                </CardContent>
            </Card>
        </div>
    </AuthenticatedLayout>
</template>
