import { createApp } from "vue";
import App from "./App.vue";
import "../css/app.css";
import axios from "axios";

console.log("Vite JS loaded!");


axios.defaults.baseURL = "/api";
const app = createApp(App);
app.config.globalProperties.$http = axios;
app.mount("#app");
