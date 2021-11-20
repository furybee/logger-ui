<template>
  <div
    class="
      fixed
      bottom-0
      right-0
      left-0
      h-16
      bg-black
      text-gray-100
      font-bold
      text-sm
      flex
      items-center
      p-2
    "
  >
    <div class="container mx-auto flex items-center">
      <div class="flex rounded-md shadow-sm">
        <span
          class="
            inline-flex
            items-center
            px-3
            rounded-l-md
            border border-r-0 border-gray-900
            bg-gray-900
            text-gray-500
            sm:text-sm
          "
        >
          App
        </span>
        <select
          class="bg-gray-800 flex-1 w-40 mr-2 rounded-r-md"
          v-model="value.app_name"
        >
          <option value="">ALL</option>
          <option
            v-for="(app, index) in available_filters.app_names"
            :key="index"
          >
            {{ app }}
          </option>
        </select>
      </div>

      <div class="flex rounded-md shadow-sm">
        <span
          class="
            inline-flex
            items-center
            px-3
            rounded-l-md
            border border-r-0 border-gray-900
            bg-gray-900
            text-gray-500
            sm:text-sm
          "
        >
          Channel
        </span>
        <select
          class="bg-gray-800 w-40 mr-2 rounded-r-md"
          v-model="value.channel"
        >
          <option value="">ALL</option>
          <option
            v-for="(channel, index) in available_filters.channels"
            :key="index"
          >
            {{ channel }}
          </option>
        </select>
      </div>

      <div class="flex rounded-md shadow-sm">
        <span
          class="
            inline-flex
            items-center
            px-3
            rounded-l-md
            border border-r-0 border-gray-900
            bg-gray-900
            text-gray-500
            sm:text-sm
          "
        >
          Level
        </span>
        <select
          class="bg-gray-800 w-40 mr-2 rounded-r-md"
          v-model="value.level_name"
        >
          <option value="">ALL</option>
          <option
            v-for="(level_name, index) in available_filters.level_names"
            :key="index"
          >
            {{ level_name }}
          </option>
        </select>
      </div>

      <div class="flex rounded-md shadow-sm w-full">
        <span
          class="
            inline-flex
            items-center
            px-3
            rounded-l-md
            border border-r-0 border-gray-900
            bg-gray-900
            text-gray-500
            sm:text-sm
          "
        >
          <svg
            xmlns="http://www.w3.org/2000/svg"
            class="h-5 w-5"
            viewBox="0 0 20 20"
            fill="currentColor"
          >
            <path
              fill-rule="evenodd"
              d="M8 4a4 4 0 100 8 4 4 0 000-8zM2 8a6 6 0 1110.89 3.476l4.817 4.817a1 1 0 01-1.414 1.414l-4.816-4.816A6 6 0 012 8z"
              clip-rule="evenodd"
            />
          </svg>
        </span>
        <input
          type="search"
          class="bg-gray-800 w-full rounded-r-md"
          v-model="value.query"
          placeholder="Search..."
        />
      </div>
    </div>
  </div>
</template>

<script>
export default {
  name: "filter-bar",
  props: {
    value: {
      type: Object,
      required: true,
    },
    available_filters: {
      type: Object,
      required: true,
    },
  },
};
</script>

<style></style>
