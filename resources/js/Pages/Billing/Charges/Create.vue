<script setup lang="ts">
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import InputError from '@/Components/InputError.vue';
import { Button } from '@/Components/ui/button';
import { Input } from '@/Components/ui/input';
import { Label } from '@/Components/ui/label';
import { Checkbox } from '@/Components/ui/checkbox';
import { Card, CardContent, CardHeader, CardTitle } from '@/Components/ui/card';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { ref, watch } from 'vue';

interface UnitOption {
    id: number;
    flat_number: string;
}

defineProps<{
    units: UnitOption[];
}>();

const currentYear = new Date().getFullYear();
const currentQuarter = Math.ceil((new Date().getMonth() + 1) / 3);

const year = ref(String(currentYear));
const quarter = ref(String(currentQuarter));

const form = useForm({
    apply_to_all: false,
    unit_id: '',
    type: 'maintenance',
    description: '',
    amount: '',
    billing_month: `${year.value}-Q${quarter.value}`,
    due_date: '',
});

watch([year, quarter], () => {
    form.billing_month = `${year.value}-Q${quarter.value}`;
});

const submit = () => {
    form.post(route('charges.store'));
};
</script>

<template>
    <Head title="Add Charge" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-xl font-semibold leading-tight text-foreground">
                Add Charge
            </h2>
        </template>

        <div class="max-w-2xl">
                <Card>
                    <CardHeader>
                        <CardTitle>New Charge</CardTitle>
                    </CardHeader>
                    <CardContent>
                        <form @submit.prevent="submit" class="space-y-6">
                            <div class="flex items-center gap-2">
                                <Checkbox
                                    id="apply_to_all"
                                    :checked="form.apply_to_all"
                                    @update:checked="form.apply_to_all = $event as boolean"
                                />
                                <Label for="apply_to_all">Apply to all units</Label>
                            </div>

                            <div v-if="!form.apply_to_all">
                                <Label for="unit_id">Unit</Label>
                                <select
                                    id="unit_id"
                                    v-model="form.unit_id"
                                    class="mt-1 flex h-9 w-full rounded-md border border-input bg-transparent px-3 py-1 text-sm shadow-sm transition-colors focus-visible:outline-none focus-visible:ring-1 focus-visible:ring-ring"
                                    :required="!form.apply_to_all"
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
                                <Label for="type">Type</Label>
                                <select
                                    id="type"
                                    v-model="form.type"
                                    class="mt-1 flex h-9 w-full rounded-md border border-input bg-transparent px-3 py-1 text-sm shadow-sm transition-colors focus-visible:outline-none focus-visible:ring-1 focus-visible:ring-ring"
                                    required
                                >
                                    <option value="maintenance">Maintenance</option>
                                    <option value="ad-hoc">Ad-hoc</option>
                                </select>
                                <InputError class="mt-2" :message="form.errors.type" />
                            </div>

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
                                    min="0"
                                    step="0.01"
                                />
                                <InputError class="mt-2" :message="form.errors.amount" />
                            </div>

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
                                <Button :disabled="form.processing">Save</Button>
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
