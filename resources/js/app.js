import axios from "axios";
import Base from "./base";
import moment from "moment-timezone";
import Routes from "./routes";
import Vue from "vue";
import VueJsonPretty from "vue-json-pretty";
import VueRouter from "vue-router";

const token = document.head.querySelector('meta[name="csrf-token"]');

if (token) {
    axios.defaults.headers.common["X-CSRF-TOKEN"] = token.content;
}

Vue.use(VueRouter);

moment.tz.setDefault("utc");

const router = new VueRouter({
    routes: Routes,
    mode: "history",
    base: "/logger-ui",
});

router.beforeEach((to, from, next) => {
    to.meta.title = to.meta.createTitle(to.params);

    document.title = "Logger UI - " + to.meta.title;

    next();
});

Vue.component(
    "clipboard-copy",
    require("./components/icons/ClipboardCopy.vue").default
);

Vue.mixin(Base);

new Vue({
    el: "#logger-ui",
    router,
});
