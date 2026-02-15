<script setup lang="ts">
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import InputError from '@/Components/InputError.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { Button } from '@/Components/ui/button';
import { Input } from '@/Components/ui/input';
import { Label } from '@/Components/ui/label';
import { Card, CardContent, CardHeader, CardTitle } from '@/Components/ui/card';

const form = useForm({
    flat_type: '2BHK',
    amount: '',
    effective_from: '',
});

const submit = () => {
    form.post(route('maintenance-slabs.store'));
};
</script>

<template>
    <Head title="Add Maintenance Slab" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-xl font-semibold leading-tight text-foreground">
                Add Maintenance Slab
            </h2>
        </template>

        <div class="max-w-2xl">
                <Card>
                    <CardHeader>
                        <CardTitle>New Maintenance Slab</CardTitle>
                    </CardHeader>
                    <CardContent>
                        <form @submit.prevent="submit" class="space-y-6">
                            <div class="space-y-2">
                                <Label for="flat_type">Flat Type</Label>
                                <select
                                    id="flat_type"
                                    v-model="form.flat_type"
                                    class="flex h-9 w-full rounded-md border border-input bg-transparent px-3 py-1 text-sm shadow-xs transition-colors file:border-0 file:bg-transparent file:text-sm file:font-medium file:text-foreground placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-1 focus-visible:ring-ring disabled:cursor-not-allowed disabled:opacity-50"
                                    required
                                >
                                    <option value="1BHK">1BHK</option>
                                    <option value="2BHK">2BHK</option>
                                    <option value="3BHK">3BHK</option>
                                </select>
                                <InputError :message="form.errors.flat_type" />
                            </div>

                            <div class="space-y-2">
                                <Label for="amount">Amount</Label>
                                <Input
                                    id="amount"
                                    type="number"
                                    v-model="form.amount"
                                    required
                                    min="0"
                                    step="0.01"
                                />
                                <InputError :message="form.errors.amount" />
                            </div>

                            <div class="space-y-2">
                                <Label for="effective_from">Effective From</Label>
                                <Input
                                    id="effective_from"
                                    type="date"
                                    v-model="form.effective_from"
                                    required
                                />
                                <InputError :message="form.errors.effective_from" />
                            </div>

                            <div class="flex items-center gap-4">
                                <Button type="submit" :disabled="form.processing">
                                    Save
                                </Button>
                                <Button variant="link" as-child>
                                    <Link :href="route('maintenance-slabs.index')">
                                        Cancel
                                    </Link>
                                </Button>
                            </div>
                        </form>
                    </CardContent>
                </Card>
        </div>
    </AuthenticatedLayout>
</template>
