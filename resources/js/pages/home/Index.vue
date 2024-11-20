<template>
    <div class="flex flex-col h-screen overflow-hidden">
        <div class="navbar bg-base-200">
            <div class="flex-0">
                <a class="btn btn-ghost text-xl">LoggerUI</a>
            </div>

            <div class="flex-1 gap-2">
                <div class="form-control w-full">
                    <input type="text" placeholder="Search" class="input input-sm input-bordered w-full lg:w-1/2" />
                </div>
                <div class="flex gap-2 items-center">
                    <div class="mx-2">
                        <div v-if="isPlaying" class="radial-progress text-success flex items-center justify-center" :style="'--size:2rem; --value:'+ intervalTickerPercentage +';'" role="progressbar">
                            <button class="btn btn-xs btn-ghost btn-circle relative z-20" @click="pause()">
                                <PauseIcon class="size-4" />
                            </button>
                        </div>

                        <div v-else-if="isIdle" class="radial-progress text-success flex items-center justify-center" :style="'--size:2rem; --value:100;'" role="progressbar">
                            <button class="btn btn-xs btn-ghost btn-circle relative z-20" @click="pause()">
                                <PauseIcon class="size-4" />
                            </button>
                        </div>

                        <div v-else class="radial-progress text-warning flex items-center justify-center" :style="'--size:2rem; --value:100;'" role="progressbar">
                            <button class="btn btn-xs btn-ghost btn-circle relative z-20" @click="play()">
                                <PlayIcon class="size-4" />
                            </button>
                        </div>
                    </div>

                    <button class="btn btn-xs btn-ghost btn-circle relative z-20" @click="toggleFilters()">
                        <FilterIcon />
                    </button>

                    <div class="dropdown dropdown-end">
                        <div tabindex="0" role="button" class="btn btn-ghost btn-circle">
                            <div class="w-10 rounded-full flex items-center justify-center">
                                <SettingsIcon />
                            </div>
                        </div>
                        <ul
                            tabindex="0"
                            class="menu menu-sm dropdown-content bg-base-100 rounded-box z-[1000] mt-3 w-52 p-2 shadow">
                            <li><a>Settings</a></li>
                            <li><a>Logout</a></li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
        <div id="logLines" ref="logLines" class="flex-1 bg-base-100 overflow-y-auto overflow-x-hidden">
            <LogRecords :filters="filters" :records="records"/>
        </div>

        <div class="bg-base-300 text-center p-1 text-xs">
            <div>
                <button
                    v-if="prevPagination"
                    class="btn btn-xs"
                    :disabled="!prevPagination.next_page_url"
                    @click="loadPrev(prevPagination.next_page_url)"
                >
                    Previous
                </button>

                <button
                    class="btn btn-xs"
                    @click="loadNext()"
                >
                    Next
                </button>
            </div>

            Last Log At: {{ records && records.length ? records[records.length - 1].formatted_logged_at : "-" }}
        </div>
    </div>
</template>
<script setup lang="ts">
import SettingsIcon from "../../icons/SettingsIcon.vue";
import {computed, onMounted, onUnmounted, reactive, ref, useTemplateRef, watch, watchEffect} from "vue";
import LogRecords from "./partials/LogRecords.vue";
import axios from "axios";
import collect from "collect.js";
import PauseIcon from "../../icons/PauseIcon.vue";
import PlayIcon from "../../icons/PlayIcon.vue";
import {PAGINATION_MODE, PaginatorType, RefreshLogParamsType} from "../../types";
import DoubleChevronUp from "../../icons/DoubleChevronUp.vue";
import FilterIcon from "../../icons/FilterIcon.vue";

const settings = ref( {
    showDate: true,
    showLevel: true,
});

const allowedFilters = [
    "date_from",
    "date_to",
    "app_name",
    "channel",
    "level_name",
    "query",
    "page",
];

const nextPagination = ref<PaginatorType>(null);
const prevPagination = ref<PaginatorType>(null);

const refreshSeconds = ref(10);
const isIdle = ref(false);

const filters = ref({
    date_to: "",
    date_from: "",
    app_name: "",
    environment: "",
    channel: "",
    level_name: "",
    query: "",
    page: 1,
});

const records = ref([]);
const availableFilters = ref({
    app_names: [],
    environments: [],
    channels: [],
    level_names: [],
});

const logLines = ref('');
const scrollToBottom = () => {
    if (!logLines.value) {
        return;
    }

    const scrollTimeout = setTimeout(() => {
        logLines.value.scrollTo(0, logLines.value.scrollHeight);

        clearTimeout(scrollTimeout);
    }, 400);
};

const scrollToTop = () => {
    if (!logLines.value) {
        return;
    }

    const scrollTimeout = setTimeout(() => {
        logLines.value.scrollTo(0, 0);

        clearTimeout(scrollTimeout);
    }, 400);
};

const loadMore = async (params: RefreshLogParamsType = { url: "/logger-ui/logs", pagination_mode: PAGINATION_MODE.INIT, forceScrollToBottom: false, customFilters: {} }) => {
    const { forceScrollToBottom = false, customFilters = {} } = params;
    const requestFilters = {...filters.value, ...customFilters};

    isIdle.value = true;

    const response = await axios.post(params.url ?? "/logger-ui/logs", requestFilters);

    let newLines = response.data.lines.filter(
        (line) => !records.value.some((record) => record.id === line.id)
    );

    if ([PAGINATION_MODE.INIT, PAGINATION_MODE.PREV].includes(params.pagination_mode)) {
        prevPagination.value = response.data.pagination;
    }

    if ([PAGINATION_MODE.INIT, PAGINATION_MODE.NEXT].includes(params.pagination_mode)) {
        nextPagination.value = response.data.pagination;
    }

    // Sort by 'logged_at' in asc order
    newLines = collect(newLines).sortBy("logged_at").all();

    let newRecords = [];
    switch (params.pagination_mode) {
        case PAGINATION_MODE.INIT:
            newRecords = newLines;
            break;
        case PAGINATION_MODE.NEXT:
            newRecords = [...records.value, ...newLines];
            break;
        case PAGINATION_MODE.PREV:
            newRecords = [...newLines, ...records.value];
            break;
    }

    const tolerance = 50;
    const isNearBottom = logLines.value.scrollHeight - logLines.value.scrollTop <= logLines.value.clientHeight + tolerance;

    records.value = newRecords;

    isIdle.value = true;

    availableFilters.value = response.data.availableFilters;

    if ([PAGINATION_MODE.INIT, PAGINATION_MODE.NEXT].includes(params.pagination_mode)) {
        if (forceScrollToBottom || isNearBottom) {
            scrollToBottom();
        }
    } else if (params.pagination_mode === PAGINATION_MODE.PREV) {
        scrollToTop();
    }
    //
};

onMounted(async() => {
    const url = new URL(window.location);

    allowedFilters.forEach((value) => {
        filters.value[value] = "";
    });

    url.searchParams.forEach((value, key) => {
        filters.value[key] = value;
    });

    await loadMore({ pagination_mode: PAGINATION_MODE.INIT });

    play();
});

onUnmounted(() => {
    clearInterval(intervalRefreshLogs);
    clearInterval(intervalTicker);

});

const loadNext = async () => {
    await loadMore({ pagination_mode: PAGINATION_MODE.NEXT });
};

const loadPrev = async (url) => {
    await loadMore({ url, pagination_mode: PAGINATION_MODE.PREV });
};

const isPlaying = ref(false);
let intervalRefreshLogs = null;
let intervalTicker = null;
let intervalTickerElapsed = ref(0);
let intervalTickerPercentage = ref(0);

const startTicker = () => {
    intervalTicker = setInterval(() => {
        intervalTickerElapsed.value++;
        intervalTickerPercentage.value = (intervalTickerElapsed.value / refreshSeconds.value) * 100;

        if (intervalTickerElapsed.value >= refreshSeconds.value) {
            intervalTickerElapsed.value = 0;
            intervalTickerPercentage.value = 100;
        }
    }, 1000);
};

const pause = () => {
    isIdle.value = false;
    isPlaying.value = false;

    intervalTickerElapsed.value = 0;
    intervalTickerPercentage.value = 0;

    clearInterval(intervalRefreshLogs);
    clearInterval(intervalTicker);
};

const play = () => {
    pause();

    isPlaying.value = true;

    if (filters.value.page !== 1) {
        filters.value.page = 1;
    }

    startTicker();

    intervalRefreshLogs = setInterval(async () => {
        pause();

        await loadNext();

        play();
    }, refreshSeconds.value * 1000);
}
</script>
