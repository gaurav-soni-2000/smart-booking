<template>
    <div>
        <h2>Book a service</h2>

        <label>Service</label><br />
        <select v-model="serviceId" @change="fetchSlots">
            <option v-for="s in services" :value="s.id" :key="s.id">
                {{ s.name }} ({{ s.duration_minutes }}m)
            </option>
        </select>

        <div style="margin-top: 10px">
            <label>Date</label><br />
            <input type="date" v-model="date" @change="fetchSlots" />
        </div>

        <div style="margin-top: 12px">
            <label>Available Slots</label>
            <div v-if="loadingSlots">Loading...</div>
            <div v-else-if="slots.length === 0">No available slots</div>
            <ul style="padding-left: 0">
                <li
                    v-for="slot in slots"
                    :key="slot.start"
                    style="list-style: none; margin: 6px 0"
                >
                    <button @click="selectSlot(slot)">
                        {{ slot.start }} - {{ slot.end }}
                    </button>
                </li>
            </ul>
        </div>

        <div
            v-if="selectedSlot"
            style="
                margin-top: 12px;
                border-top: 1px solid #eee;
                padding-top: 12px;
            "
        >
            <h3>
                Confirm booking: {{ selectedSlot.start }} -
                {{ selectedSlot.end }}
            </h3>
            <input v-model="clientEmail" placeholder="Your email" /><br /><br />
            <input
                v-model="clientName"
                placeholder="Your name (optional)"
            /><br /><br />
            <button @click="book" :disabled="booking">Book</button>
            <div v-if="message" style="margin-top: 8px">{{ message }}</div>
        </div>
    </div>
</template>

<script>
import axios from "axios";

export default {
    data() {
        return {
            services: [],
            serviceId: null,
            date: null,
            slots: [],
            selectedSlot: null,
            clientEmail: "",
            clientName: "",
            message: "",
            loadingSlots: false,
            booking: false,
        };
    },
    mounted() {
        axios.get("/services").then((r) => {
            this.services = r.data;
            if (this.services.length) {
                this.serviceId = this.services[0].id;
            }
        });
    },
    methods: {
        fetchSlots() {
            this.selectedSlot = null;
            this.slots = [];
            this.message = "";
            if (!this.date || !this.serviceId) return;
            this.loadingSlots = true;
            axios
                .get("/slots", {
                    params: { date: this.date, service_id: this.serviceId },
                })
                .then((r) => {
                    this.slots = r.data.slots || [];
                })
                .catch((e) => {
                    this.message =
                        e.response?.data?.message || "Error fetching slots";
                })
                .finally(() => (this.loadingSlots = false));
        },
        selectSlot(slot) {
            this.selectedSlot = slot;
            this.message = "";
        },
        book() {
            if (!this.clientEmail) {
                this.message = "Enter an email";
                return;
            }
            this.booking = true;
            axios
                .post("/book", {
                    service_id: this.serviceId,
                    date: this.date,
                    start_time: this.selectedSlot.start,
                    client_email: this.clientEmail,
                    client_name: this.clientName,
                })
                .then(() => {
                    this.message = "Booked successfully!";
                    this.fetchSlots();
                    this.selectedSlot = null;
                    this.clientEmail = "";
                    this.clientName = "";
                })
                .catch((e) => {
                    this.message =
                        e.response?.data?.message || "Booking failed";
                })
                .finally(() => {
                    this.booking = false;
                });
        },
    },
};
</script>
