<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { router, Link } from '@inertiajs/vue3';
import { ref, computed } from 'vue';

// Access global route helper
const route = window.route;

const props = defineProps({
    attendances: Array,
    employees: Array,
    startDate: String,
    endDate: String,
    selectedEmployee: String,
    serverTime: String,
});

const localStartDate = ref(props.startDate);
const localEndDate = ref(props.endDate);
const localEmployeeId = ref(props.selectedEmployee || '');

const applyFilters = () => {
    const params = {
        start_date: localStartDate.value,
        end_date: localEndDate.value,
    };
    if (localEmployeeId.value) {
        params.employee_id = localEmployeeId.value;
    }
    router.get(route('attendance.admin'), params, {
        preserveScroll: true,
    });
};

const clearFilters = () => {
    localEmployeeId.value = '';
    router.get(route('attendance.admin'));
};

const getDayOfWeek = (dateString) => {
    const date = new Date(dateString);
    return date.toLocaleDateString('en-US', { weekday: 'short' });
};

// Group attendances by employee for summary view
const summaryByEmployee = computed(() => {
    const summary = {};
    props.attendances?.forEach(record => {
        if (!summary[record.employee_id]) {
            summary[record.employee_id] = {
                id: record.employee_id,
                name: record.employee_name,
                records: 0,
                totalHours: 0,
            };
        }
        summary[record.employee_id].records++;
        // Parse duration to add to total
        if (record.duration && record.duration !== 'In Progress') {
            const match = record.duration.match(/(\d+)\s*hrs?\s*(\d+)?\s*mins?/i);
            if (match) {
                const hours = parseInt(match[1]) || 0;
                const mins = parseInt(match[2]) || 0;
                summary[record.employee_id].totalHours += hours + (mins / 60);
            }
        }
    });
    return Object.values(summary);
});
</script>

<template>
    <AuthenticatedLayout>
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100">
            <!-- Header -->
            <div class="border-b border-gray-100 px-8 py-6">
                <h2 class="text-2xl font-bold text-gray-900 flex items-center gap-3">
                    <i class="fas fa-users-clock text-brand-yellow"></i>
                    Attendance Management
                </h2>
                <p class="text-sm text-gray-500 mt-1">View and manage employee attendance records and punch in/out times.</p>
            </div>

            <!-- Filters -->
            <div class="px-8 py-4 border-b border-gray-100 bg-gray-50">
                <div class="flex items-center gap-4 flex-wrap">
                    <div class="flex items-center gap-2">
                        <label class="text-sm text-gray-600">Employee:</label>
                        <select 
                            v-model="localEmployeeId"
                            class="px-3 py-2 border border-gray-200 rounded-lg text-sm focus:ring-1 focus:ring-brand-yellow focus:border-brand-yellow min-w-[180px]"
                        >
                            <option value="">All Employees</option>
                            <option v-for="emp in employees" :key="emp.id" :value="emp.id">
                                {{ emp.name }}
                            </option>
                        </select>
                    </div>
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
                        @click="applyFilters"
                        class="px-4 py-2 bg-brand-yellow text-brand-dark rounded-lg text-sm font-medium hover:bg-yellow-400 transition-colors"
                    >
                        <i class="fas fa-search mr-2"></i>Apply
                    </button>
                    <button 
                        @click="clearFilters"
                        class="px-4 py-2 border border-gray-300 text-gray-600 rounded-lg text-sm font-medium hover:bg-gray-50 transition-colors"
                    >
                        Clear
                    </button>
                </div>
            </div>

            <!-- Employee Summary Cards (when no specific employee selected) -->
            <div v-if="!selectedEmployee && summaryByEmployee.length > 0" class="px-8 py-6 border-b border-gray-100">
                <h3 class="text-lg font-semibold text-gray-800 mb-4">Employee Summary</h3>
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                    <Link 
                        v-for="emp in summaryByEmployee" 
                        :key="emp.id"
                        :href="route('attendance.employee', emp.id)"
                        class="border border-gray-200 rounded-xl p-4 hover:shadow-md hover:border-brand-yellow transition-all cursor-pointer"
                    >
                        <div class="flex items-center justify-between mb-3">
                            <span class="font-semibold text-gray-900">{{ emp.name }}</span>
                            <i class="fas fa-chevron-right text-gray-400"></i>
                        </div>
                        <div class="flex items-center justify-between text-sm">
                            <span class="text-gray-500">Records: {{ emp.records }}</span>
                            <span class="text-brand-dark font-medium">{{ emp.totalHours.toFixed(1) }} hrs</span>
                        </div>
                    </Link>
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
                                <th class="text-left py-4 px-3 text-sm font-semibold text-gray-700">Employee</th>
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
                                    <Link 
                                        :href="route('attendance.employee', record.employee_id)"
                                        class="hover:text-brand-yellow transition-colors"
                                    >
                                        {{ record.employee_name }}
                                    </Link>
                                </td>
                                <td class="py-4 px-3 text-gray-700">
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
