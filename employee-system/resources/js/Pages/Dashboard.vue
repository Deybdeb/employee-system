<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import TimeAtWork from '@/Components/Widgets/TimeAtWork.vue';
import { useForm, Link } from '@inertiajs/vue3';
import { ref, watch } from 'vue';

const props = defineProps({
    isLocal: Boolean,
    flash: Object,
    stats: Object,
    serverTime: String,
});

const flashMessage = ref(props.flash?.success_message);

watch(() => props.flash?.success_message, (newMessage) => {
    flashMessage.value = newMessage;
    if (newMessage) {
        setTimeout(() => (flashMessage.value = null), 3000);
    }
});

const adjustTime = (unit, amount) => {
    const form = useForm({});
    form.post(`/testing/time-travel/${unit}/${amount}`, {
        preserveScroll: true,
        preserveState: false,
    });
};

const resetTime = () => {
    const form = useForm({});
    form.post('/testing/time-reset', {
        preserveScroll: true,
        preserveState: false,
    });
};
</script>

<template>
    <AuthenticatedLayout>
        <div v-if="isLocal" class="mb-6 p-4 bg-yellow-100 border border-yellow-300 rounded-lg shadow-sm">
            <h3 class="font-bold text-yellow-800 mb-2">Developer Time Travel</h3>
            <p class="text-sm text-gray-700 mb-3">
                Current Server Time: <strong class="font-semibold text-blue-700">{{ serverTime }} (UTC)</strong>
            </p>

            <div v-if="flashMessage" class="mb-3 p-2 text-sm bg-green-100 text-green-700 rounded-md">
                {{ flashMessage }}
            </div>

            <div class="flex items-center gap-2 flex-wrap">

                <button @click="adjustTime('hour', -1)" type="button" class="bg-gray-600 text-white px-3 py-2 rounded-md hover:bg-gray-700 text-sm">-1 Hour</button>
                <button @click="adjustTime('hour', 1)" type="button" class="bg-blue-600 text-white px-3 py-2 rounded-md hover:bg-blue-700 text-sm">+1 Hour</button>
                <button @click="adjustTime('day', 1)" type="button" class="bg-blue-600 text-white px-3 py-2 rounded-md hover:bg-blue-700 text-sm">+1 Day</button>


                <button @click="resetTime" type="button" class="bg-gray-500 text-white px-3 py-2 rounded-md hover:bg-gray-600 text-sm">Reset Time</button>


                <Link
                    href="/testing/clear-attendances"
                    method="post" as="button" type="button"
                    class="bg-red-600 text-white px-3 py-2 rounded-md hover:bg-red-700 text-sm"
                    :preserve-scroll="true" :preserve-state="false"
                >
                    Clear All Entries
                </Link>
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-6">
            <TimeAtWork />
        </div>
    </AuthenticatedLayout>
</template>