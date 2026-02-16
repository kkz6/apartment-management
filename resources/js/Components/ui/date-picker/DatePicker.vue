<script setup lang="ts">
import type { DateValue } from 'reka-ui';
import { CalendarDate, getLocalTimeZone, today } from '@internationalized/date';
import { CalendarIcon } from 'lucide-vue-next';
import { computed, ref, watch } from 'vue';
import { cn } from '@/lib/utils';
import { Button } from '@/Components/ui/button';
import { Calendar } from '@/Components/ui/calendar';
import { Popover, PopoverContent, PopoverTrigger } from '@/Components/ui/popover';

const props = defineProps<{
    modelValue?: string | null;
    placeholder?: string;
}>();

const emit = defineEmits<{
    'update:modelValue': [value: string | null];
}>();

const open = ref(false);

const parseDate = (value: string | null | undefined): DateValue | undefined => {
    if (!value) {
        return undefined;
    }

    const [year, month, day] = value.split('-').map(Number);

    if (!year || !month || !day) {
        return undefined;
    }

    return new CalendarDate(year, month, day);
};

const dateValue = ref<DateValue | undefined>(parseDate(props.modelValue));

watch(() => props.modelValue, (val) => {
    dateValue.value = parseDate(val);
});

watch(dateValue, (val) => {
    if (!val) {
        emit('update:modelValue', null);
        return;
    }

    const formatted = `${val.year}-${String(val.month).padStart(2, '0')}-${String(val.day).padStart(2, '0')}`;
    emit('update:modelValue', formatted);
    open.value = false;
});

const displayDate = computed(() => {
    if (!dateValue.value) {
        return null;
    }

    const jsDate = new Date(dateValue.value.year, dateValue.value.month - 1, dateValue.value.day);

    return jsDate.toLocaleDateString('en-IN', {
        year: 'numeric',
        month: 'short',
        day: 'numeric',
    });
});
</script>

<template>
    <Popover v-model:open="open">
        <PopoverTrigger as-child>
            <Button
                variant="outline"
                :class="cn(
                    'w-full justify-start text-left font-normal',
                    !dateValue && 'text-muted-foreground',
                )"
            >
                <CalendarIcon class="mr-2 h-4 w-4" />
                {{ displayDate ?? (placeholder || 'Pick a date') }}
            </Button>
        </PopoverTrigger>
        <PopoverContent class="w-auto p-0" align="start">
            <Calendar
                :model-value="(dateValue as any)"
                initial-focus
                layout="month-and-year"
                @update:model-value="(v: any) => { dateValue = v }"
            />
        </PopoverContent>
    </Popover>
</template>
