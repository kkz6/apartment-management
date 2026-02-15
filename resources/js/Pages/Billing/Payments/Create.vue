<script setup lang="ts">
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import InputError from '@/Components/InputError.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { computed, watch } from 'vue';
import { Button } from '@/Components/ui/button';
import { Input } from '@/Components/ui/input';
import { Label } from '@/Components/ui/label';
import { Card, CardContent, CardHeader, CardTitle } from '@/Components/ui/card';

interface UnitOption {
    id: number;
    flat_number: string;
}

interface PendingCharge {
    id: number;
    unit_id: number;
    description: string;
    billing_month: string;
    amount: string;
    status: string;
    unit: UnitOption | null;
}

const props = defineProps<{
    units: UnitOption[];
    pendingCharges: PendingCharge[];
}>();

const form = useForm({
    unit_id: '',
    charge_id: '',
    amount: '',
    paid_date: '',
    source: 'gpay',
    reference_number: '',
});

const filteredCharges = computed(() => {
    if (!form.unit_id) {
        return [];
    }

    return props.pendingCharges.filter(
        (charge) => charge.unit_id === Number(form.unit_id)
    );
});

watch(() => form.unit_id, () => {
    form.charge_id = '';
});

const submit = () => {
    form.post(route('payments.store'));
};

const formatCurrency = (amount: string): string => {
    return new Intl.NumberFormat('en-IN', {
        style: 'currency',
        currency: 'INR',
        minimumFractionDigits: 0,
    }).format(Number(amount));
};
</script>

<template>
    <Head title="Record Payment" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-xl font-semibold leading-tight text-foreground">
                Record Payment
            </h2>
        </template>

        <div class="max-w-2xl">
                <Card>
                    <CardHeader>
                        <CardTitle>Payment Details</CardTitle>
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
                                <Label for="charge_id">Charge (optional)</Label>
                                <select
                                    id="charge_id"
                                    v-model="form.charge_id"
                                    class="mt-1 flex h-9 w-full rounded-md border border-input bg-transparent px-3 py-1 text-sm shadow-sm transition-colors focus-visible:outline-none focus-visible:ring-1 focus-visible:ring-ring"
                                >
                                    <option value="">No linked charge</option>
                                    <option
                                        v-for="charge in filteredCharges"
                                        :key="charge.id"
                                        :value="charge.id"
                                    >
                                        {{ charge.description }} - {{ charge.billing_month }} ({{ formatCurrency(charge.amount) }}, {{ charge.status }})
                                    </option>
                                </select>
                                <p v-if="form.unit_id && filteredCharges.length === 0" class="mt-1 text-sm text-muted-foreground">
                                    No pending charges for this unit.
                                </p>
                                <InputError class="mt-2" :message="form.errors.charge_id" />
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
                                <Button type="submit" :disabled="form.processing">Save</Button>
                                <Button variant="link" as-child>
                                    <Link :href="route('payments.index')">
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
