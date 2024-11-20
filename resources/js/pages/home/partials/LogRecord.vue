<template>
    <tr class="hover:bg-base-200">
        <td v-if="settings.showId">
            {{ record.id }}
        </td>
        <td v-if="settings.showLevel" :class="borderClass">
            <span v-if="record.level >= 500" class="badge text-xs rounded-md bg-purple-500 text-black font-bold w-full">{{ record.level_name }}</span>
            <span v-else-if="record.level >= 400" class="badge text-xs rounded-md badge-error font-bold w-full">{{ record.level_name }}</span>
            <span v-else-if="record.level >= 300" class="badge text-xs rounded-md badge-warning font-bold w-full">{{ record.level_name }}</span>
            <span v-else-if="record.level >= 250" class="badge text-xs rounded-md badge-info font-bold w-full">{{ record.level_name }}</span>
            <span v-else class="badge text-xs rounded-md bg-base-300 font-bold w-full">{{ record.level_name }}</span>
        </td>
        <td v-if="settings.showDate">
            <span class="badge text-xs rounded-md bg-base-300 font-bold w-full text-nowrap">
                {{ record.formatted_logged_at }}
            </span>
        </td>
        <td class="w-full">
            <template v-if="record.message.length > 500">
                <div :id="'shortMessage'+record.id">
                    {{ record.message.substring(0, 500) }}...
                </div>

                <div :id="'showMore'+record.id" class="hidden">
                    {{ record.message }}
                </div>
            </template>
            <template v-else>
                {{ record.message.substring(0, 1000) }}
            </template>
            <div v-if="isDetailsDisplayed">
                <div class="grid grid-cols-2 gap-2 p-10">
                    <div>Context</div>
                    <div>Extra</div>
                    <div>{{ record.context }}</div>
                    <div>{{ record.extra }}</div>
                </div>
            </div>
        </td>
        <td>
            <div class="flex gap-4">
                <button class="btn btn-xs btn-circle hover:text-accent">
                    <CopyIcon class="size-4" />
                </button>
                <button class="btn btn-xs btn-circle hover:text-accent" @click="showDetails">
                    <EllipsisIcon class="size-4" />
                </button>
            </div>
        </td>
    </tr>
</template>
<script setup lang="ts">
import {LogRecordSettings, LogRecordType} from "../../../types";
import {computed, ref} from "vue";
import CopyIcon from "../../../icons/CopyIcon.vue";
import EllipsisIcon from "../../../icons/EllipsisIcon.vue";

const props = defineProps<{
    record: LogRecordType,
    settings: LogRecordSettings,
}>();

const isDetailsDisplayed = ref(false);

const borderClass = computed(() => {
    if (props.record.level >= 500) {
        return 'border-l-4 border-purple-500';
    }

    if (props.record.level >= 400) {
        return 'border-l-4 border-error';
    }

    if (props.record.level >= 300) {
        return 'border-l-4 border-warning';
    }

    if (props.record.level >= 250) {
        return 'border-l-4 border-info';
    }

    return 'border-l-4 border-base-300';
});

const showDetails = () => {
    const id = props.record.id;

    isDetailsDisplayed.value = !isDetailsDisplayed.value

    const showMore = document.getElementById('showMore' + id);
    const shortMessage = document.getElementById('shortMessage' + id);
    const shortMoreBtn = document.getElementById('showMoreBtn' + id);

    if (showMore) {
        showMore.classList.toggle('hidden');
    }

    if (shortMessage) {
        shortMessage.classList.toggle('hidden');
    }

    if (shortMoreBtn) {
        shortMoreBtn.innerText = shortMoreBtn.innerText === 'Expand' ? 'Collapse' : 'Expand';
    }
};
</script>
