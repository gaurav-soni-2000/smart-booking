<template>
    <div>
        <h2>Admin: Working Rules</h2>
        <form @submit.prevent="createRule" style="margin-bottom: 12px">
            <label>Weekday</label>
            <select v-model.number="form.weekday">
                <option v-for="(name, i) in weekdays" :value="i" :key="i">
                    {{ name }}
                </option>
            </select>

            <label>Start time</label>
            <input type="time" v-model="form.start_time" required />

            <label>End time</label>
            <input type="time" v-model="form.end_time" required />

            <label>Interval (min)</label>
            <input
                type="number"
                v-model.number="form.slot_interval"
                min="5"
                max="240"
            />

            <button type="submit">Add Rule</button>
        </form>

        <br />
        <h3>Existing rules</h3>
        <ul style="padding-left: 0">
            <li
                v-for="r in rules"
                :key="r.id"
                style="list-style: none; margin-bottom: 8px"
            >
                <strong>{{ weekdays[r.weekday] }}</strong
                >: {{ r.start_time }} - {{ r.end_time }} ({{
                    r.slot_interval
                }}m)
                <button @click="remove(r.id)" style="margin-left: 10px">
                    Delete
                </button>
            </li>
        </ul>
    </div>
</template>

<script>
import axios from "axios";

export default {
    data() {
        return {
            rules: [],
            weekdays: ["Sun", "Mon", "Tue", "Wed", "Thu", "Fri", "Sat"],
            form: {
                weekday: 1,
                start_time: "09:00:00",
                end_time: "17:00:00",
                slot_interval: 30,
            },
        };
    },
    mounted() {
        this.load();
    },
    methods: {
        load() {
            axios.get("/admin/rules").then((r) => (this.rules = r.data));
        },
        createRule() {
            axios
                .post("/admin/rules", this.form)
                .then(() => {
                    this.load();
                })
                .catch((e) => alert(e.response?.data?.message || "Error"));
        },
        remove(id) {
            axios.delete("/admin/rules/" + id).then(() => this.load());
        },
    },
};
</script>
