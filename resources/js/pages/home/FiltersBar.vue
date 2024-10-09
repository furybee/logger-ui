<template>
    <div>
        <div
            id="filterBar"
            class="
      hidden md:block
      bg-grey-950
      text-gray-100
      font-bold
      text-sm
      flex
      items-center
      px-10
    "
        >
            <div class="py-4 w-full items-center grid grid-cols-1 md:grid-cols-5 gap-4">
                <div class="shadow-sm">
                    <span class="inline-flex items-center px-3 rounded-t-md border border-r-0 border-gray-900 bg-gray-900 text-gray-500 sm:text-sm">
                        App
                    </span>
                    <select
                        class="bg-gray-800 w-full rounded-r-md rounded-b-md"
                        v-model="model.app_name"
                    >
                        <option value="">ALL</option>
                        <option v-for="(app, index) in props.availableFilters.app_names" :key="index">
                            {{ app }}
                        </option>
                    </select>
                </div>

                <div class="rounded-md shadow-sm">
                    <span class="inline-flex items-center px-3 rounded-t-md border border-r-0 border-gray-900 bg-gray-900 text-gray-500 sm:text-sm">
                        Env
                    </span>
                    <select
                        class="bg-gray-800 w-full rounded-r-md rounded-b-md"
                        v-model="model.environment"
                    >
                        <option value="">ALL</option>
                        <option v-for="(environment, index) in props.availableFilters.environments" :key="index">
                            {{ environment }}
                        </option>
                    </select>
                </div>

                <div class="rounded-md shadow-sm">
                    <span class="inline-flex items-center px-3 rounded-t-md border border-r-0 border-gray-900 bg-gray-900 text-gray-500 sm:text-sm">
                        Channel
                    </span>
                    <select
                        class="bg-gray-800 w-full rounded-r-md rounded-b-md"
                        v-model="model.channel"
                    >
                        <option value="">ALL</option>
                        <option v-for="(channel, index) in props.availableFilters.channels" :key="index">
                            {{ channel }}
                        </option>
                    </select>
                </div>

                <div class="rounded-md shadow-sm">
                    <span class="inline-flex items-center px-3 rounded-t-md border border-r-0 border-gray-900 bg-gray-900 text-gray-500 sm:text-sm">
                        Level
                    </span>
                    <select
                        class="bg-gray-800 w-full rounded-r-md rounded-b-md"
                        v-model="model.level_name"
                    >
                        <option value="">ALL</option>
                        <option v-for="(level_name, index) in props.availableFilters.level_names" :key="index">
                            {{ level_name }}
                        </option>
                    </select>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup lang="ts">
import {defineModel, defineProps} from "vue";

const props = defineProps({
    availableFilters: {
        type: Object,
        required: true,
    }
});

const model = defineModel();
</script>
