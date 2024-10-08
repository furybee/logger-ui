<template>
    <div>
        <div
            class="p-2 bg-yellow-500 shadow-sm fixed bottom-5 right-5 cursor-pointer md:hidden"
            @click="toggleFilterBar"
        >
            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-white" fill="none" viewBox="0 0 24 24"
                 stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round"
                      d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"/>
            </svg>
        </div>

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
                        Date
                    </span>
                    <input
                        type="date"
                        :max="new Date().toISOString().split('T')[0]"
                        class="bg-gray-800 w-full rounded-r-md rounded-b-md"
                        v-model="internalValue.date"
                    />
                </div>

                <div class="shadow-sm">
                    <span class="inline-flex items-center px-3 rounded-t-md border border-r-0 border-gray-900 bg-gray-900 text-gray-500 sm:text-sm">
                        App
                    </span>
                    <select
                        class="bg-gray-800 w-full rounded-r-md rounded-b-md"
                        v-model="internalValue.app_name"
                    >
                        <option value="">ALL</option>
                        <option v-for="(app, index) in availableFilters.app_names" :key="index">
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
                        v-model="internalValue.environment"
                    >
                        <option value="">ALL</option>
                        <option v-for="(environment, index) in availableFilters.environments" :key="index">
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
                        v-model="internalValue.channel"
                    >
                        <option value="">ALL</option>
                        <option v-for="(channel, index) in availableFilters.channels" :key="index">
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
                        v-model="internalValue.level_name"
                    >
                        <option value="">ALL</option>
                        <option v-for="(level_name, index) in availableFilters.level_names" :key="index">
                            {{ level_name }}
                        </option>
                    </select>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { defineModel, defineProps } from 'vue';

defineProps({
    availableFilters: {
        type: Object,
        required: true,
    }
});

const internalValue = defineModel({
    modelValue: {
        type: Object,
        required: true
    }
});

const toggleFilterBar = () => {
    let filterBar = document.getElementById('filterBar');
    filterBar.classList.toggle('hidden');
};
</script>
