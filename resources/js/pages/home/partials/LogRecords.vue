<template>
    <table class="table table-xs table-pin-rows">
        <thead>
            <tr class="bg-base-300">
                <th v-if="settings.showId">ID</th>
                <th v-if="settings.showLevel">Level</th>
                <th v-if="settings.showLevel">Date</th>
                <th>Message</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <template v-for="record in records" :key="record.id">
                <LogRecord :record="record" :settings="settings"/>
            </template>
        </tbody>
    </table>
</template>
<script setup lang="ts">
import {LogFiltersType, LogRecordSettings, LogRecordType} from "../../../types";
import LogRecord from "./LogRecord.vue";

const props = defineProps<{
    filters: LogFiltersType,
    records: LogRecordType[],
    settings: LogRecordSettings,
}>();
</script>
