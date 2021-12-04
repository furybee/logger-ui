<template>
  <div class="h-screen flex flex-col px-2">
    <div
      class="
        fixed
        top-0
        right-0
        left-0
        h-16
        bg-grey-950
        text-gray-100
        font-bold
        text-2xl
        flex
        items-center
        px-10
      "
    >
      <div class="flex w-full items-center justify-between">
        <h1>Logger UI</h1>

        <div class="text-sm flex items-center">
          <button class="mr-3" @click.prevent="loadOldest">Load Oldest</button>

          <button
            class="border-green-400 border-2 rounded-full focus:outline-none"
            v-show="is_live === true"
            @click.prevent="pause"
          >
            <svg
              xmlns="http://www.w3.org/2000/svg"
              class="h-10 w-10"
              viewBox="0 0 20 20"
              fill="currentColor"
            >
              <path
                fill-rule="evenodd"
                d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zM7 8a1 1 0 012 0v4a1 1 0 11-2 0V8zm5-1a1 1 0 00-1 1v4a1 1 0 102 0V8a1 1 0 00-1-1z"
                clip-rule="evenodd"
              />
            </svg>
          </button>
          <button
            class="border-yellow-400 border-2 rounded-full focus:outline-none"
            v-show="is_live === false"
            @click.prevent="play"
          >
            <svg
              xmlns="http://www.w3.org/2000/svg"
              class="h-10 w-10"
              viewBox="0 0 20 20"
              fill="currentColor"
            >
              <path
                fill-rule="evenodd"
                d="M10 18a8 8 0 100-16 8 8 0 000 16zM9.555 7.168A1 1 0 008 8v4a1 1 0 001.555.832l3-2a1 1 0 000-1.664l-3-2z"
                clip-rule="evenodd"
              />
            </svg>
          </button>
        </div>
      </div>
    </div>

    <div class="flex flex-1 items-end py-20">
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
              font-bold
              w-20
              mr-1
              h-6
              cursor-pointer
            "
            :class="badgeClass(line.level)"
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
                <p v-if="typeof line.context === 'string'">
                  {{ line.context }}
                </p>
                <ul v-else class="ml-2 m-h-64 overflow-y-auto">
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
                <p v-if="typeof line.extra === 'string'">
                  {{ line.extra }}
                </p>
                <ul v-else>
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
import collect from "collect.js";
import Clipboard from "../../mixins/clipboard";
import filterBar from "./partials/filter-bar.vue";
const axios = require("axios");

export default {
  components: { filterBar },
  mixins: [Clipboard],
  data() {
    return {
      is_live: true,
      lines: [],
      allowedFilters: [
        "date",
        "app_name",
        "channel",
        "level_name",
        "query",
        "page",
      ],
      available_filters: {
        app_names: [],
        environnements: [],
        channels: [],
        level_names: [],
      },
      loadedPages: [],
      pagination: null,
      filter: {
        date: "",
        app_name: "",
        environnement: "",
        channel: "",
        level_name: "",
        query: "",
        page: 1,
      },
      timeoutId: undefined,
      intervalTimeout: null,
    };
  },
  mounted() {
    const url = new URL(window.location);

    const filters = {};

    this.allowedFilters.forEach((value) => {
      filters[value] = "";
    });

    url.searchParams.forEach((value, key) => {
      filters[key] = value;
    });

    this.filter = filters;

    this.play(true);
  },
  methods: {
    play(skipApplyFilter) {
      this.is_live = true;

      if (this.filter.page !== 1) {
        this.filter.page = 1;
      }

      if (skipApplyFilter === false) {
        this.applyFilters();
      }

      this.intervalTimeout = setInterval(() => {
        this.applyFilters();
      }, 10000);
    },
    pause() {
      this.is_live = false;

      clearInterval(this.intervalTimeout);
    },
    loadOldest() {
      if (this.pagination.next_page_url === null) {
        console.log("No more data..");
      }

      this.filter.page++;
    },
    loadNewest() {
      if (this.pagination.prev_page_url === null) {
        console.log("No more data..");

        this.filter.page = 1;
      }

      this.filter.page--;
    },
    applyFilters() {
      if (this.filter.page !== 1) {
        this.pause();
      }

      axios
        .post("/logger-ui/logs", this.filter)
        .then((response) => {
          this.pagination = response.data.pagination;

          this.lines = collect(this.lines)
            .when(this.filter.date !== "", (lines) => {
              return lines.where("logged_at", "<=", this.filter.date);
            })
            .when(this.filter.app_name !== "", (lines) => {
              return lines.where("app_name", this.filter.app_name);
            })
            .when(this.filter.environnement !== "", (lines) => {
              return lines.where("environnement", this.filter.environnement);
            })
            .when(this.filter.channel !== "", (lines) => {
              return lines.where("channel", this.filter.channel);
            })
            .when(this.filter.level_name !== "", (lines) => {
              return lines.where("level_name", this.filter.level_name);
            })
            .when(this.filter.query !== "", (lines) => {
              return lines.filter((line) => {
                return (
                  line.message.includes(this.filter.query) ||
                  JSON.stringify(line.context).includes(this.filter.query)
                );
              });
            })
            .merge(response.data.lines)
            .unique("id")
            .sortBy("logged_at")
            .all();

          this.available_filters = response.data.available_filters;

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
    badgeClass(level) {
      if (level >= 400) {
        return "bg-red-800 text-red-200";
      }

      if (level >= 300) {
        return "bg-yellow-700 text-yellow-200";
      }

      if (level >= 250) {
        return "bg-blue-800 text-blue-200";
      }

      return "bg-gray-800 text-gray-200";
    },
  },
  watch: {
    "filter.page": {
      handler: function (newValue) {
        this.loadedPages = collect(this.loadedPages)
          .push(newValue)
          .unique()
          .all();
      },
    },
    filter: {
      deep: true,
      handler: function (newValue, oldValue) {
        clearTimeout(this.timeoutId);

        const url = new URL(window.location);

        for (const [key, value] of Object.entries(newValue)) {
          if (typeof value === "number" && value <= 0) {
            url.searchParams.delete(key);

            continue;
          }

          if (typeof value === "string" && value.trim().length === 0) {
            url.searchParams.delete(key);

            continue;
          }

          url.searchParams.set(key, value);
        }

        window.history.pushState({}, "", url);

        this.timeoutId = setTimeout(() => {
          this.applyFilters();
        }, 500);
      },
    },
  },
};
</script>
