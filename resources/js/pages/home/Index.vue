<template>
    <div class="flex flex-col h-screen overflow-hidden">
        <div class="navbar bg-base-200">
            <div class="flex-0">
                <a class="btn btn-ghost text-xl">LoggerUI</a>
            </div>
            <div class="flex-1 gap-2">
                <div class="form-control w-full">
                    <input type="text" placeholder="Search" class="input input-bordered w-full" />
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

        <div v-if="hasLoadMore">
            <div v-if="isFilterShowed">
                Filters
                <!--            <FiltersBar :available-filters="availableFilters" v-model="filters"/>-->
            </div>

            <div class="text-center p-1 text-xs">
                <button class="btn btn-xs btn-primary" @click="loadMore()" :disabled="isLoadingMore">
                    <DoubleChevronUp class="size-4" />
                    Load Previous Logs
                    <DoubleChevronUp class="size-4" />
                </button>
            </div>
        </div>

        <div id="logLines" ref="logLines" class="flex-1 bg-base-100 overflow-y-auto overflow-x-hidden">
            <LogRecords :filters="filters" :records="records"/>
        </div>

        <div v-if="pagination" class="bg-base-300 text-center p-1 text-xs">
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
import FiltersBar from "./FiltersBar.vue";
import {LogFiltersType, RefreshLogParamsType} from "../../types";
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

const pagination = ref(null);
const refreshSeconds = ref(10);
const isLoadingMore = ref(false);

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

let records = ref([]);
let availableFilters = ref({
    app_names: [],
    environments: [],
    channels: [],
    level_names: [],
});

let intervalRefreshLogs = null;
let intervalTicker = null;
let intervalTickerElapsed = ref(0);
let intervalTickerPercentage = ref(0);

const isPlaying = ref(false);
const isIdle = ref(false);

const lastPreviousPageLoaded = ref(1);

const loadMore = async () => {
    if (isLoadingMore.value) {
        return;
    }

    isLoadingMore.value = true;

    if (!pagination.value.next_page_url) {
        return;
    }

    lastPreviousPageLoaded.value = pagination.value.current_page + 1;

    await refreshLogs({
        customFilters: {
            page: lastPreviousPageLoaded.value
        }
    });

    isLoadingMore.value = false;

    scrollToTop();
};

onMounted(async () => {
    const url = new URL(window.location);

    allowedFilters.forEach((value) => {
        filters.value[value] = "";
    });

    url.searchParams.forEach((value, key) => {
        filters.value[key] = value;
    });

    // if (logLines.value) {
    //     logLines.value.addEventListener('scroll', handleScroll);
    // }

    await refreshLogs({forceScrollToBottom: true});

    play();
});

onUnmounted(() => {
    clearInterval(intervalRefreshLogs);
    clearInterval(intervalTicker);

    // if (logLines.value) {
    //     logLines.value.removeEventListener('scroll', handleScroll);
    // }
});

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

const isFilterShowed = ref(false);

const toggleFilters = () => {
    isFilterShowed.value = !isFilterShowed.value;
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

        await refreshLogs();

        play();
    }, refreshSeconds.value * 1000);
}

const pause = () => {
    isIdle.value = false;
    isPlaying.value = false;

    intervalTickerElapsed.value = 0;
    intervalTickerPercentage.value = 0;

    clearInterval(intervalRefreshLogs);
    clearInterval(intervalTicker);
};

const hasLoadMore = computed(() => {
    return pagination.value && pagination.value.next_page_url;
});


let timeoutFilter = null;

watch(
    filters.value,
    (newValue, oldValue) => {
        clearTimeout(timeoutFilter);

        const url = new URL(window.location);

        for (const [key, value] of Object.entries(newValue)) {
            if (typeof value === 'number' && value <= 0) {
                url.searchParams.delete(key);
                continue;
            }

            if (typeof value === 'string' && value.trim().length === 0) {
                url.searchParams.delete(key);
                continue;
            }

            url.searchParams.set(key, value);
        }

        window.history.pushState({}, '', url);

        timeoutFilter = setTimeout(async () => {
            pause();

            await refreshLogs();

            play();
        }, 250);
    },
    { deep: true }
);

const scrollToBottom = (force: boolean = false) => {
    if (!logLines.value) {
        return;
    }

    const scrollTimeout = setTimeout(() => {
        const tolerance = 50;
        const isNearBottom = logLines.value.scrollHeight - logLines.value.scrollTop <= logLines.value.clientHeight + tolerance;

        if (isNearBottom || force) {
            logLines.value.scrollTo(0, logLines.value.scrollHeight);
        }

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

const refreshLogs = async (params: LogFiltersType = { forceScrollToBottom: false, customFilters: {} }) => {
    const { forceScrollToBottom = false, customFilters = {} } = params;
    const requestFilters = {...filters.value, ...customFilters};

    console.log("Refreshing logs..", requestFilters);

    isIdle.value = true;

    const response = await axios.post("/logger-ui/logs", requestFilters);

    pagination.value = response.data.pagination;

    const newLines = response.data.lines.filter(
        (line) => !records.value.some((record) => record.id === line.id)
    );

    const updatedRecords = [...records.value, ...newLines];

    records.value = collect(updatedRecords)
        .when(requestFilters.date_to, (lines) => lines.where("logged_at", ">=", requestFilters.date_to))
        .when(requestFilters.date_from, (lines) => lines.where("logged_at", "<=", requestFilters.date_from))
        .when(requestFilters.app_name, (lines) => lines.where("app_name", requestFilters.app_name))
        .when(requestFilters.environment, (lines) => lines.where("environment", requestFilters.environment))
        .when(requestFilters.channel, (lines) => lines.where("channel", requestFilters.channel))
        .when(requestFilters.level_name, (lines) => lines.where("level_name", requestFilters.level_name))
        .when(requestFilters.query, (lines) =>
            lines.filter((line) =>
                line.message.includes(requestFilters.query) ||
                JSON.stringify(line.context).includes(requestFilters.query)
            )
        )
        .sortBy("logged_at")
        .all();

    isIdle.value = true;

    availableFilters.value = response.data.availableFilters;

    scrollToBottom(forceScrollToBottom);
};
</script>
