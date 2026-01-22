<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { router, Link, usePage } from '@inertiajs/vue3';
import { ref, computed } from 'vue';

const route = window.route;
const page = usePage();

const props = defineProps({
    timeLogs: Object,
    employees: Array,
    filters: Object,
    serverTime: String,
});

const form = ref({
    start_date: props.filters?.start_date || '',
    end_date: props.filters?.end_date || '',
    employee_id: props.filters?.employee_id || '',
    type: props.filters?.type || '',
});

// Modal state for Edit and Manual Entry
const showEditModal = ref(false);
const showManualModal = ref(false);
const selectedLogForEdit = ref(null);

const editForm = ref({
    timestamp: '',
    notes: '',
});

const manualEntryForm = ref({
    user_id: '',
    type: 'clock_in',
    timestamp: new Date().toISOString().slice(0, 16),
    notes: '',
});

// Edit Modal Functions
const openEditModal = (log) => {
    selectedLogForEdit.value = log;
    editForm.value = {
        timestamp: new Date(log.timestamp).toISOString().slice(0, 16),
        notes: log.notes || '',
    };
    showEditModal.value = true;
};

const closeEditModal = () => {
    showEditModal.value = false;
    selectedLogForEdit.value = null;
};

const submitEdit = () => {
    if (selectedLogForEdit.value) {
        router.put(route('attendance.update', selectedLogForEdit.value.id), editForm.value, {
            onSuccess: () => closeEditModal(),
        });
    }
};

// Manual Entry Modal Functions
const openManualModal = () => {
    showManualModal.value = true;
};

const closeManualModal = () => {
    showManualModal.value = false;
    manualEntryForm.value = {
        user_id: '',
        type: 'clock_in',
        timestamp: new Date().toISOString().slice(0, 16),
        notes: '',
    };
};

const submitManualEntry = () => {
    router.post(route('attendance.create-manual'), manualEntryForm.value, {
        onSuccess: () => closeManualModal(),
    });
};

const deleteTimeLog = async (id) => {
    if (confirm('Are you sure you want to delete this time entry? This action cannot be undone.')) {
        try {
            const csrfToken = page.props.csrf_token;
            const response = await fetch(route('attendance.destroy', id), {
                method: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': csrfToken,
                    'Accept': 'application/json',
                    'Content-Type': 'application/json',
                },
            });

            const data = await response.json();

            if (response.ok && data.success) {
                // Force full page reload to get fresh data from DB
                window.location.href = route('attendance.admin');
            } else {
                alert('Failed to delete: ' + (data.message || 'Unknown error'));
            }
        } catch (error) {
            console.error('Delete error:', error);
            alert('Failed to delete time entry. Please try again.');
        }
    }
};

const applyFilters = () => {
    router.get(route('attendance.admin'), {
        start_date: form.value.start_date,
        end_date: form.value.end_date,
        employee_id: form.value.employee_id,
        type: form.value.type,
    }, {
        preserveScroll: true,
    });
};

const resetFilters = () => {
    router.get(route('attendance.admin'));
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

// Summary statistics
const summaryStats = computed(() => {
    const data = props.timeLogs?.data || [];
    return {
        timeInCount: data.filter(log => log.type === 'clock_in').length,
        timeOutCount: data.filter(log => log.type === 'clock_out').length,
        uniqueEmployees: new Set(data.map(log => log.user_id)).size,
        totalRecords: props.timeLogs?.total || 0,
    };
});
</script>

<template>
    <AuthenticatedLayout>
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100">
            <!-- Header -->
            <div class="border-b border-gray-100 px-8 py-6 flex items-center justify-between">
                <div>
                    <h2 class="text-2xl font-bold text-gray-900 flex items-center gap-3">
                        <i class="fas fa-users-clock text-brand-yellow"></i>
                        Attendance Management
                    </h2>
                    <p class="text-sm text-gray-500 mt-1">View and manage employee attendance records and time in/out times.</p>
                </div>
                <div class="flex gap-2">
                    <button 
                        @click="openManualModal"
                        class="px-4 py-2 bg-brand-yellow text-brand-dark rounded-lg text-sm font-medium hover:bg-yellow-400 transition-colors"
                    >
                        <i class="fas fa-plus mr-2"></i>Manual Entry
                    </button>
                </div>
            </div>

            <!-- Filters -->
            <div class="px-8 py-4 border-b border-gray-100 bg-gray-50">
                <div class="flex items-center gap-4 flex-wrap">
                    <div class="flex items-center gap-2">
                        <label class="text-sm text-gray-600">Employee:</label>
                        <select 
                            v-model="form.employee_id"
                            class="px-3 py-2 border border-gray-200 rounded-lg text-sm focus:ring-1 focus:ring-brand-yellow focus:border-brand-yellow min-w-[180px]"
                        >
                            <option value="">All Employees</option>
                            <option v-for="emp in employees" :key="emp.id" :value="emp.id">
                                {{ emp.name }}
                            </option>
                        </select>
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
                    <button 
                        @click="applyFilters"
                        class="px-4 py-2 bg-brand-yellow text-brand-dark rounded-lg text-sm font-medium hover:bg-yellow-400 transition-colors"
                    >
                        <i class="fas fa-search mr-2"></i>Apply
                    </button>
                    <button 
                        @click="resetFilters"
                        class="px-4 py-2 border border-gray-300 text-gray-600 rounded-lg text-sm font-medium hover:bg-gray-50 transition-colors"
                    >
                        Clear
                    </button>
                </div>
            </div>

            <!-- Summary Statistics -->
            <div v-if="timeLogs?.data && timeLogs.data.length > 0" class="px-8 py-6 border-b border-gray-100 bg-gray-50">
                <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                    <div class="bg-blue-50 rounded-lg p-4 border border-blue-100">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-xs text-blue-600 font-medium">Total Records</p>
                                <p class="text-2xl font-bold text-blue-700 mt-1">{{ summaryStats.totalRecords }}</p>
                            </div>
                            <i class="fas fa-list text-3xl text-blue-300"></i>
                        </div>
                    </div>
                    <div class="bg-green-50 rounded-lg p-4 border border-green-100">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-xs text-green-600 font-medium">Time In</p>
                                <p class="text-2xl font-bold text-green-700 mt-1">{{ summaryStats.timeInCount }}</p>
                            </div>
                            <i class="fas fa-sign-in-alt text-3xl text-green-300"></i>
                        </div>
                    </div>
                    <div class="bg-orange-50 rounded-lg p-4 border border-orange-100">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-xs text-orange-600 font-medium">Time Out</p>
                                <p class="text-2xl font-bold text-orange-700 mt-1">{{ summaryStats.timeOutCount }}</p>
                            </div>
                            <i class="fas fa-sign-out-alt text-3xl text-orange-300"></i>
                        </div>
                    </div>
                    <div class="bg-purple-50 rounded-lg p-4 border border-purple-100">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-xs text-purple-600 font-medium">Employees</p>
                                <p class="text-2xl font-bold text-purple-700 mt-1">{{ summaryStats.uniqueEmployees }}</p>
                            </div>
                            <i class="fas fa-users text-3xl text-purple-300"></i>
                        </div>
                    </div>
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
                                <th class="text-left py-4 px-3 text-sm font-semibold text-gray-700">Employee</th>
                                <th class="text-left py-4 px-3 text-sm font-semibold text-gray-700">Date</th>
                                <th class="text-left py-4 px-3 text-sm font-semibold text-gray-700">Day</th>
                                <th class="text-left py-4 px-3 text-sm font-semibold text-gray-700">Time</th>
                                <th class="text-center py-4 px-3 text-sm font-semibold text-gray-700">Type</th>
                                <th class="text-left py-4 px-3 text-sm font-semibold text-gray-700">Notes</th>
                                <th class="text-right py-4 px-3 text-sm font-semibold text-gray-700">Elapsed Time</th>
                                <th class="text-center py-4 px-3 text-sm font-semibold text-gray-700">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="log in timeLogs.data" :key="log.id" class="border-b border-gray-100 hover:bg-gray-50">
                                <td class="py-4 px-3 font-medium text-gray-900">
                                    {{ log.user?.name || 'Unknown' }}
                                </td>
                                <td class="py-4 px-3 text-gray-700">
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
                                <td class="py-4 px-3 text-center">
                                    <div class="flex gap-2 justify-center">
                                        <button 
                                            @click="openEditModal(log)"
                                            class="text-blue-600 hover:text-blue-800 text-xs font-medium"
                                            title="Edit"
                                        >
                                            <i class="fas fa-edit"></i>
                                        </button>
                                        <button 
                                            @click="deleteTimeLog(log.id)"
                                            class="text-red-600 hover:text-red-800 text-xs font-medium"
                                            title="Delete"
                                        >
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </div>
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
            </div>
        </div>

        <!-- Manual Entry Modal -->
        <div v-if="showManualModal" class="fixed inset-0 flex items-center justify-center z-50 backdrop-blur-md">
            <div class="bg-white rounded-lg shadow-xl w-full max-w-md p-6">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-lg font-semibold text-gray-900">Create Manual Entry</h3>
                    <button @click="closeManualModal" class="text-gray-400 hover:text-gray-600">
                        <i class="fas fa-times"></i>
                    </button>
                </div>

                <form @submit.prevent="submitManualEntry" class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Employee</label>
                        <select v-model="manualEntryForm.user_id" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-1 focus:ring-brand-yellow">
                            <option value="">Select Employee</option>
                            <option v-for="emp in employees" :key="emp.id" :value="emp.id">
                                {{ emp.name }}
                            </option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Type</label>
                        <select v-model="manualEntryForm.type" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-1 focus:ring-brand-yellow">
                            <option value="clock_in">Time In</option>
                            <option value="clock_out">Time Out</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Date & Time</label>
                        <input v-model="manualEntryForm.timestamp" type="datetime-local" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-1 focus:ring-brand-yellow">
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Notes</label>
                        <textarea v-model="manualEntryForm.notes" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-1 focus:ring-brand-yellow" rows="3"></textarea>
                    </div>

                    <div class="flex gap-2 pt-4">
                        <button type="submit" class="flex-1 px-4 py-2 bg-brand-yellow text-brand-dark rounded-lg font-medium hover:bg-yellow-400 transition-colors">
                            Create Entry
                        </button>
                        <button type="button" @click="closeManualModal" class="flex-1 px-4 py-2 border border-gray-300 text-gray-700 rounded-lg font-medium hover:bg-gray-50 transition-colors">
                            Cancel
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Edit Modal -->
        <div v-if="showEditModal && selectedLogForEdit" class="fixed inset-0 flex items-center justify-center z-50 backdrop-blur-md">
            <div class="bg-white rounded-lg shadow-xl w-full max-w-md p-6">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-lg font-semibold text-gray-900">Edit Time Log</h3>
                    <button @click="closeEditModal" class="text-gray-400 hover:text-gray-600">
                        <i class="fas fa-times"></i>
                    </button>
                </div>

                <form @submit.prevent="submitEdit" class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Date & Time</label>
                        <input v-model="editForm.timestamp" type="datetime-local" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-1 focus:ring-brand-yellow">
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Notes</label>
                        <textarea v-model="editForm.notes" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-1 focus:ring-brand-yellow" rows="3"></textarea>
                    </div>

                    <div class="flex gap-2 pt-4">
                        <button type="submit" class="flex-1 px-4 py-2 bg-blue-600 text-white rounded-lg font-medium hover:bg-blue-700 transition-colors">
                            Update
                        </button>
                        <button type="button" @click="closeEditModal" class="flex-1 px-4 py-2 border border-gray-300 text-gray-700 rounded-lg font-medium hover:bg-gray-50 transition-colors">
                            Cancel
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
