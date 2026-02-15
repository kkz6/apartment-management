<script setup lang="ts">
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import InputError from '@/Components/InputError.vue';
import { Button } from '@/Components/ui/button';
import { Input } from '@/Components/ui/input';
import { Label } from '@/Components/ui/label';
import { Card, CardContent, CardHeader, CardTitle } from '@/Components/ui/card';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { ref, watch } from 'vue';

interface UnitOption {
    id: number;
    flat_number: string;
}

interface Charge {
    id: number;
    unit_id: number;
    type: string;
    description: string;
    amount: string;
    billing_month: string;
    due_date: string | null;
}

const props = defineProps<{
    charge: Charge;
    units: UnitOption[];
}>();

const currentYear = new Date().getFullYear();

const parseQuarter = (bm: string) => {
    const match = bm.match(/^(\d{4})-Q([1-4])$/);

    return match
        ? { year: match[1], quarter: match[2] }
        : { year: String(currentYear), quarter: '1' };
};

const parsed = parseQuarter(props.charge.billing_month);
const year = ref(parsed.year);
const quarter = ref(parsed.quarter);

const form = useForm({
    unit_id: String(props.charge.unit_id),
    type: props.charge.type,
    description: props.charge.description,
    amount: props.charge.amount,
    billing_month: props.charge.billing_month,
    due_date: props.charge.due_date ?? '',
});

watch([year, quarter], () => {
    form.billing_month = `${year.value}-Q${quarter.value}`;
});

const submit = () => {
    form.put(route('charges.update', props.charge.id));
};
</script>

<template>
    <Head title="Edit Charge" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-xl font-semibold leading-tight text-foreground">
                Edit Charge
            </h2>
        </template>

        <div class="max-w-2xl">
                <Card>
                    <CardHeader>
                        <CardTitle>Edit Charge</CardTitle>
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
                                        :value="String(unit.id)"
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
                                <Button :disabled="form.processing">Update</Button>
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
