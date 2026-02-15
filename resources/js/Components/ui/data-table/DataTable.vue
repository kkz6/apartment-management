<script setup lang="ts" generic="TData, TValue">
import type { ColumnDef, SortingState } from '@tanstack/vue-table'
import {
    FlexRender,
    getCoreRowModel,
    getSortedRowModel,
    useVueTable,
} from '@tanstack/vue-table'
import { ref } from 'vue'
import { valueUpdater } from '@/lib/utils'
import {
    Table, TableBody, TableCell, TableHead, TableHeader, TableRow,
} from '@/Components/ui/table'
import DataTablePagination from './DataTablePagination.vue'

const props = defineProps<{
    columns: ColumnDef<TData, TValue>[]
    data: TData[]
    pagination?: {
        from: number | null
        to: number | null
        total: number
        links: Array<{ url: string | null; label: string; active: boolean }>
    }
}>()

const sorting = ref<SortingState>([])

const table = useVueTable({
    get data() { return props.data },
    get columns() { return props.columns },
    getCoreRowModel: getCoreRowModel(),
    getSortedRowModel: getSortedRowModel(),
    onSortingChange: updaterOrValue => valueUpdater(updaterOrValue, sorting),
    state: {
        get sorting() { return sorting.value },
    },
})
</script>

<template>
    <div class="w-full">
        <slot name="toolbar" />
        <div class="rounded-md border">
            <Table>
                <TableHeader>
                    <TableRow v-for="headerGroup in table.getHeaderGroups()" :key="headerGroup.id">
                        <TableHead v-for="header in headerGroup.headers" :key="header.id">
                            <FlexRender
                                v-if="!header.isPlaceholder"
                                :render="header.column.columnDef.header"
                                :props="header.getContext()"
                            />
                        </TableHead>
                    </TableRow>
                </TableHeader>
                <TableBody>
                    <template v-if="table.getRowModel().rows?.length">
                        <TableRow
                            v-for="row in table.getRowModel().rows"
                            :key="row.id"
                            :data-state="row.getIsSelected() && 'selected'"
                        >
                            <TableCell v-for="cell in row.getVisibleCells()" :key="cell.id">
                                <FlexRender :render="cell.column.columnDef.cell" :props="cell.getContext()" />
                            </TableCell>
                        </TableRow>
                    </template>
                    <TableRow v-else>
                        <TableCell :colspan="columns.length" class="h-24 text-center">
                            <slot name="empty">No results.</slot>
                        </TableCell>
                    </TableRow>
                </TableBody>
            </Table>
        </div>
        <DataTablePagination v-if="pagination" v-bind="pagination" />
    </div>
</template>
