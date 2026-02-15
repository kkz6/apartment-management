<script setup lang="ts">
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import InputError from '@/Components/InputError.vue';
import { Label } from '@/Components/ui/label';
import { Input } from '@/Components/ui/input';
import { Button } from '@/Components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/Components/ui/card';
import { Head, Link, useForm } from '@inertiajs/vue3';

const form = useForm({
    description: '',
    amount: '',
    paid_date: '',
    category: 'maintenance',
    source: 'gpay',
    reference_number: '',
});

const submit = () => {
    form.post(route('expenses.store'));
};
</script>

<template>
    <Head title="Add Expense" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-xl font-semibold leading-tight text-foreground">
                Add Expense
            </h2>
        </template>

        <div class="max-w-2xl">
                <Card>
                    <CardHeader>
                        <CardTitle>New Expense</CardTitle>
                    </CardHeader>
                    <CardContent>
                        <form @submit.prevent="submit" class="space-y-6">
                            <div>
                                <Label for="description">Description</Label>
                                <Input
                                    id="description"
                                    type="text"
                                    class="mt-1"
                                    v-model="form.description"
                                    required
                                    autofocus
                                />
                                <InputError class="mt-2" :message="form.errors.description" />
                            </div>

                            <div>
                                <Label for="amount">Amount</Label>
                                <Input
                                    id="amount"
                                    type="number"
                                    class="mt-1"
                                    v-model="form.amount"
                                    required
                                    min="0.01"
                                    step="0.01"
                                />
                                <InputError class="mt-2" :message="form.errors.amount" />
                            </div>

                            <div>
                                <Label for="paid_date">Date Paid</Label>
                                <Input
                                    id="paid_date"
                                    type="date"
                                    class="mt-1"
                                    v-model="form.paid_date"
                                    required
                                />
                                <InputError class="mt-2" :message="form.errors.paid_date" />
                            </div>

                            <div>
                                <Label for="category">Category</Label>
                                <select
                                    id="category"
                                    v-model="form.category"
                                    class="mt-1 flex h-9 w-full rounded-md border border-input bg-transparent px-3 py-1 text-sm shadow-sm transition-colors focus-visible:outline-none focus-visible:ring-1 focus-visible:ring-ring"
                                    required
                                >
                                    <option value="electricity">Electricity</option>
                                    <option value="water">Water</option>
                                    <option value="maintenance">Maintenance</option>
                                    <option value="service">Service</option>
                                    <option value="other">Other</option>
                                </select>
                                <InputError class="mt-2" :message="form.errors.category" />
                            </div>

                            <div>
                                <Label for="source">Source</Label>
                                <select
                                    id="source"
                                    v-model="form.source"
                                    class="mt-1 flex h-9 w-full rounded-md border border-input bg-transparent px-3 py-1 text-sm shadow-sm transition-colors focus-visible:outline-none focus-visible:ring-1 focus-visible:ring-ring"
                                    required
                                >
                                    <option value="gpay">GPay</option>
                                    <option value="bank_transfer">Bank Transfer</option>
                                    <option value="cash">Cash</option>
                                </select>
                                <InputError class="mt-2" :message="form.errors.source" />
                            </div>

                            <div>
                                <Label for="reference_number">Reference Number</Label>
                                <Input
                                    id="reference_number"
                                    type="text"
                                    class="mt-1"
                                    v-model="form.reference_number"
                                />
                                <InputError class="mt-2" :message="form.errors.reference_number" />
                            </div>

                            <div class="flex items-center gap-4">
                                <Button :disabled="form.processing">Save</Button>
                                <Button variant="link" as-child>
                                    <Link :href="route('expenses.index')">
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
