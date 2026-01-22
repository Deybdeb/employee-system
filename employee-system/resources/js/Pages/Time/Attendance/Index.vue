<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Link, router, usePage } from '@inertiajs/vue3';
import { ref } from 'vue';

const route = window.route;
const page = usePage();

const props = defineProps({
    timeLogs: Object,
    filters: Object,
    serverTime: String,
});

const form = ref({
    start_date: props.filters?.start_date || '',
    end_date: props.filters?.end_date || '',
    type: props.filters?.type || '',
});

const applyFilters = () => {
    router.get(route('attendance.index'), {
        start_date: form.value.start_date,
        end_date: form.value.end_date,
        type: form.value.type,
    }, {
        preserveScroll: true,
    });
};

const resetFilters = () => {
    router.get(route('attendance.index'));
};

const formatDate = (timestamp) => {
    const date = new Date(timestamp);
    return date.toLocaleDateString('en-US', {
        month: 'short',
        day: 'numeric',
        year: 'numeric'
    });
};

const formatTime = (timestamp) => {
    const date = new Date(timestamp);
    return date.toLocaleTimeString('en-US', {
        hour: 'numeric',
        minute: '2-digit',
        hour12: true
    });
};

const getDayOfWeek = (timestamp) => {
    const date = new Date(timestamp);
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
                <p class="text-sm text-gray-500 mt-1">View your time in/out records and attendance history.</p>
            </div>

            <!-- Filters -->
            <div class="px-8 py-4 border-b border-gray-100 bg-gray-50">
                <div class="flex items-center gap-4 flex-wrap">
                    <div class="flex items-center gap-2">
                        <label class="text-sm text-gray-600">From:</label>
                        <input 
                            v-model="form.start_date"
                            type="date"
                            class="px-3 py-2 border border-gray-200 rounded-lg text-sm focus:ring-1 focus:ring-brand-yellow focus:border-brand-yellow"
                        />
                    </div>
                    <div class="flex items-center gap-2">
                        <label class="text-sm text-gray-600">To:</label>
                        <input 
                            v-model="form.end_date"
                            type="date"
                            class="px-3 py-2 border border-gray-200 rounded-lg text-sm focus:ring-1 focus:ring-brand-yellow focus:border-brand-yellow"
                        />
                    </div>
                    <div class="flex items-center gap-2">
                        <label class="text-sm text-gray-600">Type:</label>
                        <select
                            v-model="form.type"
                            class="px-3 py-2 border border-gray-200 rounded-lg text-sm focus:ring-1 focus:ring-brand-yellow focus:border-brand-yellow"
                        >
                            <option value="">All Types</option>
                            <option value="clock_in">Time In</option>
                            <option value="clock_out">Time Out</option>
                        </select>
                    </div>
                    <button 
                        @click="applyFilters"
                        class="px-4 py-2 bg-brand-yellow text-brand-dark rounded-lg text-sm font-medium hover:bg-yellow-400 transition-colors"
                    >
                        <i class="fas fa-search mr-2"></i>Apply
                    </button>
                    <button 
                        @click="resetFilters"
                        class="px-4 py-2 bg-gray-200 text-gray-700 rounded-lg text-sm font-medium hover:bg-gray-300 transition-colors"
                    >
                        <i class="fas fa-redo mr-2"></i>Reset
                    </button>
                </div>
            </div>

            <!-- Content -->
            <div class="p-8">
                <!-- Empty State -->
                <div v-if="!timeLogs?.data || timeLogs.data.length === 0" class="text-center py-12 text-gray-500">
                    <i class="fas fa-calendar-times text-4xl text-gray-300 mb-4"></i>
                    <p>No attendance records found for this period.</p>
                </div>

                <!-- Table -->
                <div v-else class="overflow-x-auto">
                    <table class="w-full">
                        <thead>
                            <tr class="border-b border-gray-200">
                                <th class="text-left py-4 px-3 text-sm font-semibold text-gray-700">Date</th>
                                <th class="text-left py-4 px-3 text-sm font-semibold text-gray-700">Day</th>
                                <th class="text-left py-4 px-3 text-sm font-semibold text-gray-700">Time</th>
                                <th class="text-center py-4 px-3 text-sm font-semibold text-gray-700">Type</th>
                                <th class="text-left py-4 px-3 text-sm font-semibold text-gray-700">Notes</th>
                                <th class="text-right py-4 px-3 text-sm font-semibold text-gray-700">Elapsed Time</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="log in timeLogs.data" :key="log.id" class="border-b border-gray-100 hover:bg-gray-50">
                                <td class="py-4 px-3 font-medium text-gray-900">
                                    {{ formatDate(log.timestamp) }}
                                </td>
                                <td class="py-4 px-3 text-gray-600">
                                    {{ getDayOfWeek(log.timestamp) }}
                                </td>
                                <td class="py-4 px-3">
                                    <span :class="[
                                        'font-medium',
                                        log.type === 'clock_in' ? 'text-green-600' : 'text-orange-600'
                                    ]">
                                        <i :class="[
                                            'fas mr-1',
                                            log.type === 'clock_in' ? 'fa-sign-in-alt' : 'fa-sign-out-alt'
                                        ]"></i>
                                        {{ formatTime(log.timestamp) }}
                                    </span>
                                </td>
                                <td class="py-4 px-3 text-center">
                                    <span :class="[
                                        'px-3 py-1 rounded-full text-xs font-medium',
                                        log.type === 'clock_in' 
                                            ? 'bg-green-100 text-green-700' 
                                            : 'bg-orange-100 text-orange-700'
                                    ]">
                                        {{ log.type === 'clock_in' ? 'Time In' : 'Time Out' }}
                                    </span>
                                </td>
                                <td class="py-4 px-3 text-gray-600 text-sm">
                                    {{ log.notes || '-' }}
                                </td>
                                <td class="py-4 px-3 text-right">
                                    <span v-if="log.elapsed_seconds && log.type === 'clock_out'" class="text-blue-600 font-medium text-sm">
                                        {{ log.formatted_elapsed_time }}
                                    </span>
                                    <span v-else class="text-gray-400">-</span>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                <div v-if="timeLogs?.links && timeLogs.links.length > 0" class="mt-6 flex items-center justify-between">
                    <div class="text-sm text-gray-600">
                        Showing {{ timeLogs.from }} to {{ timeLogs.to }} of {{ timeLogs.total }} entries
                    </div>
                    <div class="flex gap-1">
                        <template v-for="link in timeLogs.links" :key="link.label">
                            <Link
                                v-if="!link.url"
                                class="px-3 py-1 text-sm text-gray-500 bg-gray-100 rounded-lg cursor-not-allowed"
                            >
                                <span v-html="link.label.replace('&laquo;', '←').replace('&raquo;', '→')"></span>
                            </Link>
                            <Link
                                v-else
                                :href="link.url"
                                class="px-3 py-1 text-sm rounded-lg transition-colors"
                                :class="link.active 
                                    ? 'text-white bg-brand-yellow' 
                                    : 'text-gray-700 bg-gray-100 hover:bg-gray-200'"
                            >
                                <span v-html="link.label.replace('&laquo;', '←').replace('&raquo;', '→')"></span>
                            </Link>
                        </template>
                    </div>
                </div>

                <!-- Summary -->
                <div v-if="timeLogs?.data && timeLogs.data.length > 0" class="mt-6 pt-6 border-t border-gray-200">
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div class="bg-green-50 rounded-lg p-4 border border-green-100">
                            <div class="flex items-center justify-between">
                                <div>
                                    <p class="text-xs text-green-600 font-medium">Time In Records</p>
                                    <p class="text-2xl font-bold text-green-700 mt-1">
                                        {{ timeLogs.data.filter(log => log.type === 'clock_in').length }}
                                    </p>
                                </div>
                                <i class="fas fa-sign-in-alt text-3xl text-green-300"></i>
                            </div>
                        </div>
                        <div class="bg-orange-50 rounded-lg p-4 border border-orange-100">
                            <div class="flex items-center justify-between">
                                <div>
                                    <p class="text-xs text-orange-600 font-medium">Time Out Records</p>
                                    <p class="text-2xl font-bold text-orange-700 mt-1">
                                        {{ timeLogs.data.filter(log => log.type === 'clock_out').length }}
                                    </p>
                                </div>
                                <i class="fas fa-sign-out-alt text-3xl text-orange-300"></i>
                            </div>
                        </div>
                        <div class="bg-blue-50 rounded-lg p-4 border border-blue-100">
                            <div class="flex items-center justify-between">
                                <div>
                                    <p class="text-xs text-blue-600 font-medium">Total Records</p>
                                    <p class="text-2xl font-bold text-blue-700 mt-1">
                                        {{ timeLogs.total }}
                                    </p>
                                </div>
                                <i class="fas fa-list text-3xl text-blue-300"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
