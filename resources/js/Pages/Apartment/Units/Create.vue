<script setup lang="ts">
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import InputError from '@/Components/InputError.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { Button } from '@/Components/ui/button';
import { Input } from '@/Components/ui/input';
import { Label } from '@/Components/ui/label';
import { Card, CardContent, CardHeader, CardTitle } from '@/Components/ui/card';

const form = useForm({
    flat_number: '',
    flat_type: '2BHK',
    floor: '0',
    area_sqft: '',
});

const submit = () => {
    form.post(route('units.store'));
};
</script>

<template>
    <Head title="Add Unit" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-xl font-semibold leading-tight text-foreground">
                Add Unit
            </h2>
        </template>

        <div class="max-w-2xl">
                <Card>
                    <CardHeader>
                        <CardTitle>Unit Details</CardTitle>
                    </CardHeader>
                    <CardContent>
                        <form @submit.prevent="submit" class="space-y-6">
                            <div>
                                <Label for="flat_number">Flat Number</Label>
                                <Input
                                    id="flat_number"
                                    type="text"
                                    class="mt-1"
                                    v-model="form.flat_number"
                                    required
                                    autofocus
                                />
                                <InputError class="mt-2" :message="form.errors.flat_number" />
                            </div>

                            <div>
                                <Label for="flat_type">Flat Type</Label>
                                <select
                                    id="flat_type"
                                    v-model="form.flat_type"
                                    class="mt-1 flex h-9 w-full rounded-md border border-input bg-transparent px-3 py-1 text-sm shadow-sm transition-colors focus-visible:outline-none focus-visible:ring-1 focus-visible:ring-ring"
                                    required
                                >
                                    <option value="1BHK">1BHK</option>
                                    <option value="2BHK">2BHK</option>
                                    <option value="3BHK">3BHK</option>
                                </select>
                                <InputError class="mt-2" :message="form.errors.flat_type" />
                            </div>

                            <div>
                                <Label for="floor">Floor</Label>
                                <Input
                                    id="floor"
                                    type="number"
                                    class="mt-1"
                                    v-model="form.floor"
                                    required
                                    min="0"
                                />
                                <InputError class="mt-2" :message="form.errors.floor" />
                            </div>

                            <div>
                                <Label for="area_sqft">Area (sqft)</Label>
                                <Input
                                    id="area_sqft"
                                    type="number"
                                    class="mt-1"
                                    v-model="form.area_sqft"
                                    min="0"
                                    step="0.01"
                                />
                                <InputError class="mt-2" :message="form.errors.area_sqft" />
                            </div>

                            <div class="flex items-center gap-4">
                                <Button type="submit" :disabled="form.processing">
                                    Save
                                </Button>
                                <Link
                                    :href="route('units.index')"
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
