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
          <!--span
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
              cursor-pointer
            "
            @click="filter.channel = line.channel"
          >
            {{ line.channel }}
          </span-->
          <span
            class="
              inline-flex
              items-center
              justify-center
              px-2
              py-0.5
              rounded
              text-xs
              font-bold
              w-20
              mr-1
              h-6
              cursor-pointer
            "
            :class="badgeClass(line.level_name)"
            @click="filter.level_name = line.level_name"
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
            <p class="text-sm">{{ line.message }}</p>
            <div v-if="line.has_details_displayed === true" class="text-sm">
              <div v-if="line.context.length !== 0">
                <p class="font-bold">Context</p>
                <ul class="ml-2 m-h-64 overflow-y-auto">
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
            v-if="line.context.length !== 0 || line.extra.length !== 0"
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

    <filter-bar
      v-if="available_filters !== undefined"
      :available_filters="available_filters"
      v-model="filter"
    ></filter-bar>
  </div>
</template>

<script>
import Clipboard from "../../mixins/clipboard";
import filterBar from "./partials/filter-bar.vue";
const axios = require("axios");

export default {
  components: { filterBar },
  mixins: [Clipboard],
  data() {
    return {
      lines: [],
      available_filters: {
        app_names: [],
        channels: [],
        level_names: [],
      },
      default_filters: {
        app_name: "",
        channel: "",
        level_name: "",
        query: "",
      },
      filter: {
        app_name: "",
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
      axios
        .post("/logger-ui/logs", this.filter)
        .then((response) => {
          this.lines = response.data.lines;
          this.available_filters = response.data.available_filters;
          this.default_filters = response.data.default_filters;

          setTimeout(() => {
            window.scrollTo(0, document.body.scrollHeight);
          }, 400);
        })
        .catch((error) => {
          console.log(error);
        });
    },
    toggleDetails(line) {
      line.has_details_displayed = !line.has_details_displayed;
    },
    badgeClass(levelName) {
      switch (levelName) {
        case "ERROR":
        case "CRITICAL":
        case "ALERT":
        case "EMERGENCY":
          return "bg-red-800 text-red-200";
        case "WARN":
        case "WARNING":
          return "bg-yellow-700 text-yellow-200";
        case "NOTICE":
          return "bg-blue-800 text-blue-200";
        default:
          return "bg-gray-800 text-gray-200";
      }
    },
  },
  watch: {
    filter: {
      deep: true,
      handler: function (newValue, oldValue) {
        clearTimeout(this.timeoutId);

        this.timeoutId = setTimeout(() => {
          this.applyFilters();
        }, 500);
      },
    },
  },
};
</script>
