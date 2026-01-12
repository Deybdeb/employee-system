<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { router, Link } from '@inertiajs/vue3';
import { ref } from 'vue';

// Access global route helper
const route = window.route;

const props = defineProps({
    employee: Object,
    attendances: Array,
    startDate: String,
    endDate: String,
    serverTime: String,
});

const localStartDate = ref(props.startDate);
const localEndDate = ref(props.endDate);

const applyDateFilter = () => {
    router.get(route('attendance.employee', props.employee.id), {
        start_date: localStartDate.value,
        end_date: localEndDate.value,
    }, {
        preserveScroll: true,
    });
};

const getDayOfWeek = (dateString) => {
    const date = new Date(dateString);
    return date.toLocaleDateString('en-US', { weekday: 'short' });
};

// Calculate total hours
const totalHours = props.attendances?.reduce((total, record) => {
    if (record.duration && record.duration !== 'In Progress') {
        const match = record.duration.match(/(\d+)\s*hrs?\s*(\d+)?\s*mins?/i);
        if (match) {
            const hours = parseInt(match[1]) || 0;
            const mins = parseInt(match[2]) || 0;
            return total + hours + (mins / 60);
        }
    }
    return total;
}, 0);
</script>

<template>
    <AuthenticatedLayout>
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100">
            <!-- Header -->
            <div class="border-b border-gray-100 px-8 py-6">
                <div class="flex items-center gap-4">
                    <Link 
                        :href="route('attendance.admin')"
                        class="text-gray-500 hover:text-gray-700 transition-colors"
                    >
                        <i class="fas fa-arrow-left"></i>
                    </Link>
                    <div>
                        <h2 class="text-2xl font-bold text-gray-900 flex items-center gap-3">
                            <i class="fas fa-user-clock text-brand-yellow"></i>
                            {{ employee.name }}'s Attendance
                        </h2>
                        <p class="text-sm text-gray-500 mt-1">Detailed punch in/out records for this employee.</p>
                    </div>
                </div>
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

            <!-- Summary Stats -->
            <div class="px-8 py-6 border-b border-gray-100 bg-gradient-to-r from-brand-yellow/10 to-transparent">
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <div class="bg-white rounded-xl p-4 shadow-sm border border-gray-100">
                        <div class="text-sm text-gray-500 mb-1">Total Records</div>
                        <div class="text-2xl font-bold text-gray-900">{{ attendances?.length || 0 }}</div>
                    </div>
                    <div class="bg-white rounded-xl p-4 shadow-sm border border-gray-100">
                        <div class="text-sm text-gray-500 mb-1">Total Hours</div>
                        <div class="text-2xl font-bold text-brand-dark">{{ totalHours?.toFixed(1) || '0' }} hrs</div>
                    </div>
                    <div class="bg-white rounded-xl p-4 shadow-sm border border-gray-100">
                        <div class="text-sm text-gray-500 mb-1">Period</div>
                        <div class="text-lg font-semibold text-gray-700">{{ startDate }} to {{ endDate }}</div>
                    </div>
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

                <!-- Footer -->
                <div v-if="attendances?.length > 0" class="mt-6 pt-6 border-t border-gray-200">
                    <div class="flex items-center justify-between text-sm text-gray-600">
                        <Link 
                            :href="route('attendance.admin')"
                            class="text-brand-yellow hover:text-yellow-600 font-medium"
                        >
                            <i class="fas fa-arrow-left mr-2"></i>Back to All Employees
                        </Link>
                        <span>Server Time: {{ serverTime }}</span>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
