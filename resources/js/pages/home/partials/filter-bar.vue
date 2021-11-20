<template>
  <div>
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
      <select class="bg-gray-800 w-64 mr-2" v-model="value.app_name">
        <option value="">ALL</option>
        <option
          v-for="(app, index) in available_filters.app_names"
          :key="index"
        >
          {{ app }}
        </option>
      </select>

      <select class="bg-gray-800 w-64 mr-2" v-model="value.channel">
        <option value="">ALL</option>
        <option
          v-for="(channel, index) in available_filters.channels"
          :key="index"
        >
          {{ channel }}
        </option>
      </select>

      <select class="bg-gray-800 w-64 mr-2" v-model="value.level_name">
        <option value="">ALL</option>
        <option
          v-for="(level_name, index) in available_filters.level_names"
          :key="index"
        >
          {{ level_name }}
        </option>
      </select>

      <input
        type="search"
        class="bg-gray-800"
        v-model="value.query"
        placeholder="Type text here"
      />
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
