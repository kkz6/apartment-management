<script setup lang="ts">
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import InputError from '@/Components/InputError.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { Button } from '@/Components/ui/button';
import { Input } from '@/Components/ui/input';
import { Label } from '@/Components/ui/label';
import { Checkbox } from '@/Components/ui/checkbox';
import { Card, CardContent, CardHeader, CardTitle } from '@/Components/ui/card';

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
            <h2 class="text-xl font-semibold leading-tight text-foreground">
                Edit Resident
            </h2>
        </template>

        <div class="max-w-2xl">
                <Card>
                    <CardHeader>
                        <CardTitle>Edit Resident</CardTitle>
                    </CardHeader>
                    <CardContent>
                        <form @submit.prevent="submit" class="space-y-6">
                            <div>
                                <Label for="unit_id">Unit</Label>
                                <select
                                    id="unit_id"
                                    v-model="form.unit_id"
                                    class="mt-1 flex h-9 w-full rounded-md border border-input bg-transparent px-3 py-1 text-sm shadow-sm transition-colors focus-visible:outline-none focus-visible:ring-1 focus-visible:ring-ring"
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
                                <Label for="name">Name</Label>
                                <Input
                                    id="name"
                                    type="text"
                                    class="mt-1"
                                    v-model="form.name"
                                    required
                                    autofocus
                                />
                                <InputError class="mt-2" :message="form.errors.name" />
                            </div>

                            <div>
                                <Label for="phone">Phone</Label>
                                <Input
                                    id="phone"
                                    type="text"
                                    class="mt-1"
                                    v-model="form.phone"
                                />
                                <InputError class="mt-2" :message="form.errors.phone" />
                            </div>

                            <div>
                                <Label for="email">Email</Label>
                                <Input
                                    id="email"
                                    type="email"
                                    class="mt-1"
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
                                <Label for="is_owner" class="ml-2">Is Owner</Label>
                                <InputError class="mt-2" :message="form.errors.is_owner" />
                            </div>

                            <div>
                                <Label for="gpay_name">GPay Name</Label>
                                <Input
                                    id="gpay_name"
                                    type="text"
                                    class="mt-1"
                                    v-model="form.gpay_name"
                                />
                                <InputError class="mt-2" :message="form.errors.gpay_name" />
                            </div>

                            <div class="flex items-center gap-4">
                                <Button type="submit" :disabled="form.processing">Update</Button>
                                <Link
                                    :href="route('residents.index')"
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
