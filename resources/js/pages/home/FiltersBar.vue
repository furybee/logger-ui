<template>
    <div>
        <div
            id="filterBar"
            class="hidden md:block bg-base-300 font-bold text-sm flex items-center p-4"
        >
            <div class="form-control w-full">
                <input type="text" placeholder="Search" v-model="model.query" class="input input-sm input-bordered w-full" />
            </div>

            <div class="py-4 w-full items-center grid grid-cols-1 md:grid-cols-4 gap-4">
                <div class="shadow-sm">
                    <div class="label">
                        <span class="label-text">From</span>
                    </div>
                    <input type="date" v-model="model.date_from" class="input input-sm input-bordered w-full" :disabled="isLoading" />
                </div>

                <label class="form-control w-full">
                    <div class="label">
                        <span class="label-text">To</span>
                    </div>
                    <input type="date" v-model="model.date_to" class="input input-sm input-bordered w-full" :disabled="isLoading" />
                </label>
            </div>

            <div class="py-4 w-full items-center grid grid-cols-1 md:grid-cols-4 gap-4">
                <div class="shadow-sm">
                    <div class="label">
                        <span class="label-text">App</span>
                    </div>
                    <select
                        class="select select-sm w-full"
                        v-model="model.app_name"
                        :disabled="isLoading"
                    >
                        <option value="">ALL</option>
                        <option v-for="(app, index) in props.availableFilters.app_names" :key="index">
                            {{ app }}
                        </option>
                    </select>
                </div>

                <label class="form-control w-full">
                    <div class="label">
                        <span class="label-text">Env</span>
                    </div>
                    <select
                        class="select select-sm w-full"
                        v-model="model.environment"
                        :disabled="isLoading"
                    >
                        <option value="">ALL</option>
                        <option v-for="(environment, index) in props.availableFilters.environments" :key="index">
                            {{ environment }}
                        </option>
                    </select>
                </label>

                <label class="form-control w-full">
                    <div class="label">
                        <span class="label-text">Channel</span>
                    </div>
                    <select
                        class="select select-sm w-full"
                        v-model="model.channel"
                        :disabled="isLoading"
                    >
                        <option value="">ALL</option>
                        <option v-for="(channel, index) in props.availableFilters.channels" :key="index">
                            {{ channel }}
                        </option>
                    </select>
                </label>

                <label class="form-control w-full">
                    <div class="label">
                        <span class="label-text">Level</span>
                    </div>
                    <select
                        class="select select-sm w-full"
                        v-model="model.level_name"
                        :disabled="isLoading"
                    >
                        <option value="">ALL</option>
                        <option v-for="(level_name, index) in props.availableFilters.level_names" :key="index">
                            {{ level_name }}
                        </option>
                    </select>
                </label>
            </div>
        </div>
    </div>
</template>

<script setup lang="ts">
import {defineModel, defineProps} from "vue";

const props = defineProps({
    isLoading: false,
    availableFilters: {
        type: Object,
        required: true,
        default: () => {
            return {
                app_names: [],
                environments: [],
                channels: [],
                level_names: [],
            };
        },
    }
});

const model = defineModel();
</script>
