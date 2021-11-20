<template>
  <div class="h-screen flex flex-col py-16 px-2 overflow-y-scroll">
    <div
      class="
        fixed
        top-0
        right-0
        left-0
        h-16
        bg-black
        text-gray-100
        font-bold
        text-2xl
      "
    >
      Logger UI
    </div>

    <div class="flex flex-1 items-end">
      <ul class="flex flex-col w-full">
        <li class="flex mb-1 group" v-for="line in lines" :key="line.id">
          <span
            class="
              inline-flex
              items-center
              justify-center
              px-2
              py-0.5
              rounded
              text-xs
              bg-gray-800
              text-gray-200
              font-bold
              w-16
              mr-1
              h-6
            "
          >
            {{ line.channel }}
          </span>
          <span
            class="
              inline-flex
              items-center
              justify-center
              px-2
              py-0.5
              rounded
              text-xs
              bg-gray-800
              text-gray-200
              font-bold
              w-16
              mr-1
              h-6
            "
          >
            {{ line.level_name }}</span
          >
          <span
            class="
              inline-flex
              items-center
              justify-center
              px-2
              py-0.5
              rounded
              text-xs
              bg-gray-800
              text-gray-200
              font-bold
              mr-1
              h-6
            "
          >
            {{ line.formatted_logged_at }}</span
          >
          <div class="flex-1 text-gray-400 group-hover:bg-gray-800">
            <p>{{ line.message }}</p>
            <div v-if="line.has_details_displayed" class="text-sm">
              <div v-if="line.context.length !== 0">
                <p class="font-bold">Context</p>
                <ul class="ml-2 h-64 overflow-y-scroll">
                  <li
                    class="mb-1"
                    v-for="(value, key) of line.context"
                    :key="key"
                  >
                    <span class="font-bold">{{ key }}:</span>
                    <div
                      class="whitespace-normal"
                      v-if="key !== 'exception'"
                      v-html="value"
                    ></div>
                    <template v-else>
                      <ul class="ml-4">
                        <li
                          class="mb-1"
                          v-for="(subValue, subKey) of value"
                          :key="subKey"
                        >
                          <span class="font-bold">{{ subKey }}:</span>
                          <div
                            class="whitespace-normal"
                            v-html="subValue"
                          ></div>
                        </li>
                      </ul>
                    </template>
                  </li>
                </ul>
                <hr class="border-gray-700" />
              </div>

              <div v-if="line.extra.length !== 0">
                <p class="font-bold">Extra</p>
                <ul>
                  <li v-for="(value, key) in line.extra" :key="key">
                    <span class="font-bold">{{ key }}:</span>
                    <div class="whitespace-normal" v-html="value"></div>
                  </li>
                </ul>
                <hr class="border-gray-700" />
              </div>
            </div>
          </div>
          <a
            href="#"
            class="
              ml-1
              inline-flex
              items-center
              justify-center
              bg-gray-800
              text-gray-300
              p-3
              h-6
              w-6
              font-bold
            "
            @click.prevent="toggleDetails(line)"
          >
            <span v-if="line.has_details_displayed === false">+</span>
            <span v-else>-</span>
          </a>
        </li>
      </ul>
    </div>

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
        text-2xl
        flex
        items-center
        p-2
      "
    >
      <select class="bg-gray-800 w-64 mr-2" v-model="filter.channel">
        <option value="">ALL</option>
        <option
          v-for="(channel, index) in available_filters.channels"
          :key="index"
        >
          {{ channel }}
        </option>
      </select>

      <select class="bg-gray-800 w-64 mr-2" v-model="filter.level_name">
        <option value="">ALL</option>
        <option
          v-for="(level_name, index) in available_filters.level_names"
          :key="index"
        >
          {{ level_name }}
        </option>
      </select>

      <input type="search" class="bg-gray-800" v-model="filter.query" />
    </div>
  </div>
</template>

<script>
import Clipboard from "../../mixins/clipboard";
const axios = require("axios");

export default {
  mixins: [Clipboard],
  data() {
    return {
      lines: [],
      available_filters: {
        channels: [],
        level_names: [],
      },
      filter: {
        channel: "",
        level_name: "",
        query: "",
      },
      timeoutId: undefined,
    };
  },
  mounted() {
    this.applyFilters();
  },
  methods: {
    applyFilters() {
      console.log(this.filter);
      axios
        .post("/logger-ui/logs", this.filter)
        .then((response) => {
          this.lines = response.data.lines;
          this.available_filters = response.data.available_filters;
        })
        .catch((error) => {
          console.log(error);
        });
    },
    toggleDetails(line) {
      line.has_details_displayed = !line.has_details_displayed;
    },
  },
  watch: {
    filter: {
      deep: true,
      handler: function () {
        clearTimeout(this.timeoutId);

        this.timeoutId = setTimeout(() => {
          this.applyFilters();
        }, 500);
      },
    },
  },
};
</script>
