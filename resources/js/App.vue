<template>
    <div style="padding: 20px; font-family: Arial">
        <h1>Smart Booking Scheduler</h1>
        <div style="display: flex; gap: 40px">
            <div style="flex: 1">
                <BookingWidget />
            </div>
            <div style="width: 420px">
                <AdminRules />
            </div>
        </div>
    </div>
</template>

<script>
import BookingWidget from "./components/BookingWidget.vue";
import AdminRules from "./components/AdminRules.vue";

export default {
    components: { BookingWidget, AdminRules },
};
</script>
