<script setup>
import { ref, computed, onMounted } from 'vue';
import TimeClockModal from '@/Components/TimeClockModal.vue';

const isModalOpen = ref(false);
const isClocked = ref(false);
const latestLog = ref(null);
const currentTime = ref(new Date());
const isLoading = ref(true);
const error = ref(null);
let timeInterval = null;

onMounted(async () => {
    updateTime();
    timeInterval = setInterval(updateTime, 1000);
    await fetchLatestLog();
});

const updateTime = () => {
    currentTime.value = new Date();
};

const fetchLatestLog = async () => {
    try {
        isLoading.value = true;
        const response = await fetch('/time-logs/latest', {
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content,
            }
        });

        const data = await response.json();

        if (data.success) {
            isClocked.value = data.data.isClocked;
            latestLog.value = data.data.latestLog;
        } else {
            error.value = 'Failed to load time log status';
        }
    } catch (err) {
        console.error('Fetch error:', err);
        error.value = 'Failed to load time log status';
    } finally {
        isLoading.value = false;
    }
};

const currentStatus = computed(() => {
    return isClocked.value ? 'out' : 'in';
});

const statusLabel = computed(() => {
    return isClocked.value ? 'Timed In' : 'Timed Out';
});

const statusBadgeClass = computed(() => {
    return isClocked.value 
        ? 'bg-green-100 text-green-700 border-green-300'
        : 'bg-gray-100 text-gray-700 border-gray-300';
});

const statusIcon = computed(() => {
    return isClocked.value 
        ? 'fas fa-check-circle text-green-600'
        : 'fas fa-times-circle text-gray-600';
});

const todayTime = computed(() => {
    return currentTime.value.toLocaleString('en-US', {
        year: 'numeric',
        month: 'short',
        day: '2-digit',
        hour: '2-digit',
        minute: '2-digit',
        second: '2-digit',
        timeZone: 'Asia/Manila'
    });
});

const latestLogTime = computed(() => {
    if (!latestLog.value) return 'No entry yet';
    return new Date(latestLog.value.timestamp).toLocaleString('en-US', {
        month: 'short',
        day: '2-digit',
        hour: '2-digit',
        minute: '2-digit',
        second: '2-digit',
        timeZone: 'Asia/Manila'
    });
});

const latestLogType = computed(() => {
    if (!latestLog.value) return '';
    return latestLog.value.type === 'clock_in' ? 'IN' : 'OUT';
});

const openModal = () => {
    isModalOpen.value = true;
};

const closeModal = () => {
    isModalOpen.value = false;
};

const handleSubmit = async () => {
    await fetchLatestLog();
};
</script>

<template>
    <div class="bg-white rounded-xl shadow-md p-6 border border-gray-100">
        <!-- Header with icon -->
        <div class="flex items-center gap-3 mb-4">
            <div class="w-10 h-10 bg-brand-yellow/20 rounded-lg flex items-center justify-center">
                <i class="fas fa-clock text-brand-yellow text-lg"></i>
            </div>
            <h2 class="text-xl font-bold text-gray-900">Time at Work</h2>
        </div>

        <!-- Current Status Badge -->
        <div class="mb-4 flex items-center gap-2">
            <span 
                class="inline-flex items-center gap-2 px-3 py-1 rounded-full border"
                :class="statusBadgeClass"
            >
                <i :class="statusIcon"></i>
                <span class="text-sm font-medium">{{ statusLabel }}</span>
            </span>
        </div>

        <!-- Current Time Display -->
        <div class="mb-6 p-4 bg-blue-50 rounded-lg">
            <p class="text-xs text-gray-600 mb-1">Current Server Time (GMT +8:00)</p>
            <p class="text-2xl font-bold text-gray-900 font-mono">
                {{ todayTime }}
            </p>
        </div>

        <!-- Latest Log Entry -->
        <div v-if="latestLog" class="mb-6 p-4 bg-gray-50 rounded-lg">
            <p class="text-xs text-gray-600 mb-2">Latest Entry</p>
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-semibold text-gray-900">
                        Clocked {{ latestLogType }}
                    </p>
                    <p class="text-xs text-gray-600 mt-1">{{ latestLogTime }}</p>
                </div>
                <div 
                    class="w-10 h-10 rounded-full flex items-center justify-center"
                    :class="latestLog.type === 'clock_in' 
                        ? 'bg-green-100' 
                        : 'bg-orange-100'"
                >
                    <i 
                        class="text-lg"
                        :class="latestLog.type === 'clock_in' 
                            ? 'fas fa-sign-in-alt text-green-600' 
                            : 'fas fa-sign-out-alt text-orange-600'"
                    ></i>
                </div>
            </div>
        </div>

        <!-- Notes if present -->
        <div v-if="latestLog && latestLog.notes" class="mb-6 p-3 bg-amber-50 rounded-lg">
            <p class="text-xs text-gray-600 mb-1">Last Note</p>
            <p class="text-sm text-gray-700">{{ latestLog.notes }}</p>
        </div>

        <!-- Time In/Out Button -->
        <button
            @click="openModal"
            :disabled="isLoading"
            class="w-full px-6 py-3 text-gray-900 font-bold rounded-lg transition-all duration-200 flex items-center justify-center gap-2 group"
            :class="isClocked 
                ? 'bg-orange-400 hover:bg-orange-500 shadow-lg hover:shadow-xl' 
                : 'bg-brand-yellow hover:bg-yellow-500 shadow-lg hover:shadow-xl'"
        >
            <i class="fas fa-clock text-xl group-hover:scale-110 transition-transform"></i>
            <span class="text-lg">
                {{ isClocked ? 'Time Out' : 'Time In' }}
            </span>
        </button>

        <!-- Error message -->
        <div v-if="error" class="mt-4 p-3 bg-red-50 border border-red-200 rounded-lg">
            <p class="text-sm text-red-700 flex items-center gap-2">
                <i class="fas fa-exclamation-circle"></i>
                {{ error }}
            </p>
        </div>

        <!-- Loading state -->
        <div v-if="isLoading" class="mt-4 text-center">
            <i class="fas fa-spinner fa-spin text-brand-yellow text-xl"></i>
            <p class="text-sm text-gray-600 mt-2">Loading...</p>
        </div>
    </div>

    <!-- Clock Modal -->
    <TimeClockModal
        :isOpen="isModalOpen"
        :currentStatus="currentStatus"
        @close="closeModal"
        @submit="handleSubmit"
    />
</template>
