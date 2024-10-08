import axios from "axios";
import Base from "./base";
import moment from "moment-timezone";
import VueJsonPretty from "vue-json-pretty";
import { createApp } from 'vue';
import router from './routes';

const token = document.head.querySelector('meta[name="csrf-token"]');

if (token) {
    axios.defaults.headers.common["X-CSRF-TOKEN"] = token.content;
}

moment.tz.setDefault("utc");

// import ClipboardCopy from "./components/icons/ClipboardCopy.vue";

createApp({
    components: {
        VueJsonPretty,
        // ClipboardCopy,
    }
})
    .mixin(Base)
    .use(router)
    .mount('#logger-ui');
