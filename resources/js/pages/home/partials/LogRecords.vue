<template>
    <table class="table table-xs table-pin-rows">
        <thead>
            <tr class="bg-base-300">
                <th>Level</th>
                <th>Date</th>
                <th>Message</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <template v-for="record in records" :key="record.id">
                <LogRecord :record="record"/>
            </template>
        </tbody>
    </table>
</template>
<script setup lang="ts">
import {LogFiltersType, LogRecordType} from "../../../types";
import LogRecord from "./LogRecord.vue";

const props = defineProps<{
    filters: LogFiltersType,
    records: LogRecordType[],
}>();
</script>
