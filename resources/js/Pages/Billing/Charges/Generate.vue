<script setup lang="ts">
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import InputError from '@/Components/InputError.vue';
import { Button } from '@/Components/ui/button';
import { Input } from '@/Components/ui/input';
import { Label } from '@/Components/ui/label';
import { Card, CardContent, CardHeader, CardTitle } from '@/Components/ui/card';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { ref, watch } from 'vue';

const currentYear = new Date().getFullYear();
const currentQuarter = Math.ceil((new Date().getMonth() + 1) / 3);

const year = ref(String(currentYear));
const quarter = ref(String(currentQuarter));

const form = useForm({
    billing_month: `${year.value}-Q${quarter.value}`,
    due_date: '',
});

watch([year, quarter], () => {
    form.billing_month = `${year.value}-Q${quarter.value}`;
});

const submit = () => {
    form.post(route('charges.generate.store'));
};
</script>

<template>
    <Head title="Generate Quarterly Charges" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-xl font-semibold leading-tight text-foreground">
                Generate Quarterly Charges
            </h2>
        </template>

        <div class="max-w-2xl">
                <Card>
                    <CardHeader>
                        <CardTitle>Generate Charges</CardTitle>
                    </CardHeader>
                    <CardContent>
                        <p class="mb-6 text-sm text-muted-foreground">
                            This will generate maintenance charges for all units based on their flat type and the current maintenance slab rates (quarterly = 3x monthly rate). Units that already have a maintenance charge for the selected quarter will be skipped.
                        </p>

                        <form @submit.prevent="submit" class="space-y-6">
                            <div>
                                <Label for="billing_quarter">Billing Quarter</Label>
                                <div class="mt-1 flex gap-3">
                                    <select
                                        id="billing_year"
                                        v-model="year"
                                        class="flex h-9 w-1/2 rounded-md border border-input bg-transparent px-3 py-1 text-sm shadow-sm transition-colors focus-visible:outline-none focus-visible:ring-1 focus-visible:ring-ring"
                                    >
                                        <option v-for="y in [currentYear - 1, currentYear, currentYear + 1]" :key="y" :value="String(y)">
                                            {{ y }}
                                        </option>
                                    </select>
                                    <select
                                        id="billing_quarter"
                                        v-model="quarter"
                                        class="flex h-9 w-1/2 rounded-md border border-input bg-transparent px-3 py-1 text-sm shadow-sm transition-colors focus-visible:outline-none focus-visible:ring-1 focus-visible:ring-ring"
                                    >
                                        <option value="1">Q1 (Jan - Mar)</option>
                                        <option value="2">Q2 (Apr - Jun)</option>
                                        <option value="3">Q3 (Jul - Sep)</option>
                                        <option value="4">Q4 (Oct - Dec)</option>
                                    </select>
                                </div>
                                <InputError class="mt-2" :message="form.errors.billing_month" />
                            </div>

                            <div>
                                <Label for="due_date">Due Date</Label>
                                <Input
                                    id="due_date"
                                    type="date"
                                    class="mt-1"
                                    v-model="form.due_date"
                                />
                                <InputError class="mt-2" :message="form.errors.due_date" />
                            </div>

                            <div class="flex items-center gap-4">
                                <Button :disabled="form.processing">
                                    Generate Charges
                                </Button>
                                <Link
                                    :href="route('charges.index')"
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
