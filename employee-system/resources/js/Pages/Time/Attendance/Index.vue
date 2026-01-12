<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { router } from '@inertiajs/vue3';
import { ref } from 'vue';

// Access global route helper
const route = window.route;

const props = defineProps({
    attendances: Array,
    startDate: String,
    endDate: String,
    serverTime: String,
});

const localStartDate = ref(props.startDate);
const localEndDate = ref(props.endDate);

const applyDateFilter = () => {
    router.get(route('attendance.index'), {
        start_date: localStartDate.value,
        end_date: localEndDate.value,
    }, {
        preserveScroll: true,
    });
};

const formatDuration = (duration) => {
    return duration || '-';
};

const getDayOfWeek = (dateString) => {
    const date = new Date(dateString);
    return date.toLocaleDateString('en-US', { weekday: 'short' });
};
</script>

<template>
    <AuthenticatedLayout>
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100">
            <!-- Header -->
            <div class="border-b border-gray-100 px-8 py-6">
                <h2 class="text-2xl font-bold text-gray-900 flex items-center gap-3">
                    <i class="fas fa-calendar-check text-brand-yellow"></i>
                    My Attendance
                </h2>
                <p class="text-sm text-gray-500 mt-1">View your punch in/out records and attendance history.</p>
            </div>

            <!-- Filters -->
            <div class="px-8 py-4 border-b border-gray-100 bg-gray-50">
                <div class="flex items-center gap-4 flex-wrap">
                    <div class="flex items-center gap-2">
                        <label class="text-sm text-gray-600">From:</label>
                        <input 
                            v-model="localStartDate"
                            type="date"
                            class="px-3 py-2 border border-gray-200 rounded-lg text-sm focus:ring-1 focus:ring-brand-yellow focus:border-brand-yellow"
                        />
                    </div>
                    <div class="flex items-center gap-2">
                        <label class="text-sm text-gray-600">To:</label>
                        <input 
                            v-model="localEndDate"
                            type="date"
                            class="px-3 py-2 border border-gray-200 rounded-lg text-sm focus:ring-1 focus:ring-brand-yellow focus:border-brand-yellow"
                        />
                    </div>
                    <button 
                        @click="applyDateFilter"
                        class="px-4 py-2 bg-brand-yellow text-brand-dark rounded-lg text-sm font-medium hover:bg-yellow-400 transition-colors"
                    >
                        <i class="fas fa-search mr-2"></i>Apply
                    </button>
                </div>
            </div>

            <!-- Content -->
            <div class="p-8">
                <div v-if="attendances?.length === 0" class="text-center py-12 text-gray-500">
                    <i class="fas fa-calendar-times text-4xl text-gray-300 mb-4"></i>
                    <p>No attendance records found for this period.</p>
                </div>

                <div v-else class="overflow-x-auto">
                    <table class="w-full">
                        <thead>
                            <tr class="border-b border-gray-200">
                                <th class="text-left py-4 px-3 text-sm font-semibold text-gray-700">Date</th>
                                <th class="text-left py-4 px-3 text-sm font-semibold text-gray-700">Day</th>
                                <th class="text-left py-4 px-3 text-sm font-semibold text-gray-700">Punch In</th>
                                <th class="text-left py-4 px-3 text-sm font-semibold text-gray-700">Punch Out</th>
                                <th class="text-left py-4 px-3 text-sm font-semibold text-gray-700">Duration</th>
                                <th class="text-center py-4 px-3 text-sm font-semibold text-gray-700">Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="record in attendances" :key="record.id" class="border-b border-gray-100 hover:bg-gray-50">
                                <td class="py-4 px-3 font-medium text-gray-900">
                                    {{ record.date }}
                                </td>
                                <td class="py-4 px-3 text-gray-600">
                                    {{ getDayOfWeek(record.date) }}
                                </td>
                                <td class="py-4 px-3">
                                    <span class="text-green-600 font-medium">
                                        <i class="fas fa-sign-in-alt mr-1"></i>
                                        {{ record.clock_in_display }}
                                    </span>
                                </td>
                                <td class="py-4 px-3">
                                    <span v-if="record.clock_out" class="text-red-600 font-medium">
                                        <i class="fas fa-sign-out-alt mr-1"></i>
                                        {{ record.clock_out_display }}
                                    </span>
                                    <span v-else class="text-gray-400">-</span>
                                </td>
                                <td class="py-4 px-3 font-medium text-gray-700">
                                    {{ record.duration }}
                                </td>
                                <td class="py-4 px-3 text-center">
                                    <span 
                                        :class="[
                                            'px-3 py-1 rounded-full text-xs font-medium',
                                            record.clock_out 
                                                ? 'bg-green-100 text-green-700' 
                                                : 'bg-yellow-100 text-yellow-700'
                                        ]"
                                    >
                                        {{ record.clock_out ? 'Complete' : 'In Progress' }}
                                    </span>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- Summary -->
                <div v-if="attendances?.length > 0" class="mt-6 pt-6 border-t border-gray-200">
                    <div class="flex items-center justify-between text-sm text-gray-600">
                        <span>Total Records: {{ attendances.length }}</span>
                        <span>Server Time: {{ serverTime }}</span>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
