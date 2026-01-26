<script setup>
import { computed, ref, onMounted } from 'vue';
import { useForm, usePage, Link } from '@inertiajs/vue3';
import DashboardCard from '@/Components/DashboardCard.vue';
import TimeClockModal from '@/Components/TimeClockModal.vue';

const page = usePage();
const props = defineProps({
    timeclockStatus: Object,
});

const isModalOpen = ref(false);
const currentTime = ref(new Date());
const isLoading = ref(false);
const liveClockedIn = ref(false);
let timeInterval = null;

const stats = computed(() => page.props.stats);

onMounted(() => {
    liveClockedIn.value = !!stats.value?.is_clocked_in;
    updateTime();
    timeInterval = setInterval(updateTime, 1000);
});

onMounted(() => {
    updateTime();
    timeInterval = setInterval(updateTime, 1000);
});

const updateTime = () => {
    currentTime.value = new Date();
};

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

const currentStatus = computed(() => {
    return liveClockedIn.value ? 'out' : 'in';
});

const actionButtonHref = computed(() => {
    return liveClockedIn.value ? '/attendance/clock-out' : '/attendance/clock-in';
});

const actionButtonIcon = computed(() => {
    return liveClockedIn.value ? 'fa-sign-out-alt' : 'fa-sign-in-alt';
});

const actionButtonColor = computed(() => {
    return liveClockedIn.value 
        ? 'bg-orange-500 hover:bg-orange-600' 
        : 'bg-brand-yellow hover:bg-yellow-500';
});

const actionButtonTooltip = computed(() => {
    return liveClockedIn.value ? 'Time Out' : 'Time In';
});

const openModal = () => {
    syncLatestStatus().finally(() => {
        isModalOpen.value = true;
    });
};

const closeModal = () => {
    isModalOpen.value = false;
};

const handleSubmit = async (payload) => {
    if (payload?.data?.type) {
        liveClockedIn.value = payload.data.type === 'clock_in';
    }
    await page.reload();
};

const syncLatestStatus = async () => {
    try {
        const response = await fetch('/time-logs/latest');
        const data = await response.json();
        if (response.ok && data?.data) {
            liveClockedIn.value = !!data.data.isClocked;
        }
    } catch (error) {
        console.error('Failed to sync status', error);
    }
};
</script>

<template>
    <DashboardCard title="Time at Work" icon="fas fa-clock">

        <div class="flex items-center gap-4 mb-4">
              <div class="w-12 h-12 rounded-full flex items-center justify-center border-2 shrink-0 overflow-hidden relative"
                  :class="liveClockedIn ? 'border-brand-yellow' : 'border-gray-200'">
                 <i class="fas fa-user text-2xl text-gray-300"></i>
            </div>
            <div class="flex-1">
                <h4 class="text-sm font-bold text-brand-dark">
                    {{ stats.last_action_label }}
                </h4>
                <p class="text-[11px] text-gray-500">
                    {{ stats.last_action_sub_label }} {{ stats.last_action_time_formatted }} (GMT 8)
                </p>
            </div>
        </div>

        <!-- Current Time Display -->
        <div class="mb-4 p-3 bg-gray-50 rounded-lg border border-gray-100">
            <p class="text-xs text-gray-600 mb-1">Current Server Time (GMT +8:00)</p>
            <p class="text-lg text-gray-900 font-sans font-bold">
                {{ todayTime }}
            </p>
        </div>

        <div class="flex items-center gap-3 mb-6">
            <div class="flex-1 bg-gray-100 rounded-full py-2 px-4 text-center text-sm font-semibold text-brand-dark shadow-inner">
                {{ stats?.today_duration || '0h 0m' }} Today
            </div>

            <button
                @click="openModal"
                :disabled="isLoading"
                :title="actionButtonTooltip"
                class="w-10 h-10 rounded-full flex items-center justify-center text-white shadow-md hover:shadow-lg transition-all transform hover:scale-110 active:scale-95 shrink-0 disabled:opacity-50"
                :class="actionButtonColor"
            >
                <i :class="['fas', actionButtonIcon]"></i>
            </button>
        </div>

        <hr class="border-gray-100 mb-4">

        <div class="flex justify-between items-end mb-4">
            <div class="text-[11px] text-brand-dark font-semibold">
                This Week<br>
                <span class="font-normal text-gray-500">
                   {{ stats.week_date_range_formatted }}
                </span>
            </div>
            <div class="bg-gray-100 px-3 py-1 rounded-full text-[11px] font-bold text-gray-600 flex items-center gap-1">
                <i class="far fa-clock text-gray-400"></i>
                {{ stats?.week_duration || '0h 0m' }}
            </div>
        </div>

        <div class="flex justify-between items-stretch h-[160px] pt-4">
            <div v-for="(day, index) in stats?.chart" :key="index" class="flex flex-col items-center gap-2 w-7">
                <div class="w-full flex-1 bg-gray-100 rounded-full relative shadow-inner">
                    <span
                        v-if="day.hours > 0"
                        class="absolute w-full text-center text-[9px] text-[#f97316] font-bold pb-1 transition-all duration-700"
                        :style="{ bottom: day.height + '%' }"
                    >
                        {{ day.hours }}
                    </span>
                    <div
                        class="absolute bottom-0 w-full rounded-full transition-all duration-700 ease-out"
                        :class="day.hours > 0 ? 'bg-[#f97316]' : 'bg-transparent'"
                        :style="{ height: day.height + '%' }"
                    ></div>
                </div>
                <span class="text-[10px] text-gray-400 font-medium">{{ day.day }}</span>
            </div>
        </div>
    </DashboardCard>

    <!-- Clock Modal -->
    <TimeClockModal
        :isOpen="isModalOpen"
        :currentStatus="currentStatus"
        @close="closeModal"
        @submit="handleSubmit"
    />
</template>
