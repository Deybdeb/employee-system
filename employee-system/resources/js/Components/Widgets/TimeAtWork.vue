<script setup>
import { computed } from 'vue';
import { useForm, usePage } from '@inertiajs/vue3';
import DashboardCard from '@/Components/DashboardCard.vue';

const page = usePage();
const stats = computed(() => page.props.stats);
const isClockedIn = computed(() => stats.value.is_clocked_in);

const clockInForm = useForm({});
const clockOutForm = useForm({});

const toggleClock = () => {
    if (isClockedIn.value) {
        clockOutForm.post('/attendance/clock-out', { preserveState: false, preserveScroll: true });
    } else {
        clockInForm.post('/attendance/clock-in', { preserveState: false, preserveScroll: true });
    }
};
</script>

<template>
    <DashboardCard title="Time at Work" icon="fas fa-clock">

        <div class="flex items-center gap-4 mb-4">
            <div class="w-12 h-12 rounded-full flex items-center justify-center border-2 shrink-0 overflow-hidden relative"
                 :class="isClockedIn ? 'border-brand-yellow' : 'border-gray-200'">
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


        <div class="flex items-center gap-3 mb-6">
            <div class="flex-1 bg-gray-100 rounded-full py-2 px-4 text-center text-sm font-semibold text-brand-dark shadow-inner">
                {{ stats?.today_duration || '0h 0m' }} Today
            </div>

            <button
                @click="toggleClock"
                :disabled="clockInForm.processing || clockOutForm.processing"
                class="w-10 h-10 rounded-full flex items-center justify-center text-white shadow-md hover:shadow-lg transition-all transform active-scale-95 shrink-0"
                :class="isClockedIn ? 'bg-orange-500 hover:bg-orange-600' : 'bg-brand-yellow hover:bg-yellow-500'"
            >
                <i class="fas" :class="isClockedIn ? 'fa-stop' : 'fa-play'"></i>
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
</template>
