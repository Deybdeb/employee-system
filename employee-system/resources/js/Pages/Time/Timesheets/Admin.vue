<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { useForm, router } from '@inertiajs/vue3';
import { ref, computed } from 'vue';

// Access global route helper
const route = window.route;

const props = defineProps({
    pendingTimesheets: Array,
    allTimesheets: Array,
});

const activeTab = ref('pending');
const rejectModal = ref(false);
const selectedTimesheet = ref(null);
const rejectReason = ref('');

const openRejectModal = (timesheet) => {
    selectedTimesheet.value = timesheet;
    rejectReason.value = '';
    rejectModal.value = true;
};

const closeRejectModal = () => {
    rejectModal.value = false;
    selectedTimesheet.value = null;
    rejectReason.value = '';
};

const approveTimesheet = (id) => {
    if (confirm('Are you sure you want to approve this timesheet?')) {
        router.post(route('timesheets.approve', id), {}, {
            preserveScroll: true,
        });
    }
};

const rejectTimesheet = () => {
    if (!rejectReason.value.trim()) {
        alert('Please provide a reason for rejection.');
        return;
    }
    
    router.post(route('timesheets.reject', selectedTimesheet.value.id), {
        reason: rejectReason.value,
    }, {
        preserveScroll: true,
        onSuccess: () => {
            closeRejectModal();
        },
    });
};

const getStatusClass = (status) => {
    const classes = {
        'draft': 'bg-gray-100 text-gray-700',
        'submitted': 'bg-yellow-100 text-yellow-800',
        'approved': 'bg-green-100 text-green-800',
        'rejected': 'bg-red-100 text-red-800',
    };
    return classes[status] || 'bg-gray-100 text-gray-700';
};

const formatDate = (dateString) => {
    if (!dateString) return '-';
    const date = new Date(dateString);
    return date.toLocaleDateString('en-US', { year: 'numeric', month: 'short', day: 'numeric' });
};

const getTotalHours = (timesheet) => {
    if (!timesheet.entries) return '0.00';
    return timesheet.entries.reduce((sum, entry) => sum + parseFloat(entry.hours || 0), 0).toFixed(2);
};
</script>

<template>
    <AuthenticatedLayout>
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100">
            <!-- Header -->
            <div class="border-b border-gray-100 px-8 py-6">
                <h2 class="text-2xl font-bold text-gray-900 flex items-center gap-3">
                    <i class="fas fa-user-clock text-brand-yellow"></i>
                    Timesheet Management
                </h2>
                <p class="text-sm text-gray-500 mt-1">Review and manage employee timesheets.</p>
            </div>

            <!-- Tabs -->
            <div class="border-b border-gray-100 px-8 pt-4">
                <div class="flex gap-2">
                    <button
                        @click="activeTab = 'pending'"
                        :class="[
                            'px-6 py-3 rounded-t-xl text-sm transition-all',
                            activeTab === 'pending'
                                ? 'bg-brand-yellow text-brand-dark font-bold shadow-sm'
                                : 'text-gray-500 hover:text-brand-dark font-medium'
                        ]"
                    >
                        Pending Approval
                        <span v-if="pendingTimesheets?.length" class="ml-2 px-2 py-0.5 bg-yellow-500 text-white rounded-full text-xs">
                            {{ pendingTimesheets.length }}
                        </span>
                    </button>
                    <button
                        @click="activeTab = 'all'"
                        :class="[
                            'px-6 py-3 rounded-t-xl text-sm transition-all',
                            activeTab === 'all'
                                ? 'bg-brand-yellow text-brand-dark font-bold shadow-sm'
                                : 'text-gray-500 hover:text-brand-dark font-medium'
                        ]"
                    >
                        All Timesheets
                    </button>
                </div>
            </div>

            <!-- Content -->
            <div class="p-8">
                <!-- Pending Timesheets -->
                <div v-if="activeTab === 'pending'">
                    <div v-if="pendingTimesheets?.length === 0" class="text-center py-12 text-gray-500">
                        <i class="fas fa-check-circle text-4xl text-green-300 mb-4"></i>
                        <p>No pending timesheets to review.</p>
                    </div>
                    
                    <div v-else class="space-y-4">
                        <div 
                            v-for="timesheet in pendingTimesheets" 
                            :key="timesheet.id"
                            class="border border-gray-200 rounded-xl p-6 hover:shadow-md transition-shadow"
                        >
                            <div class="flex items-center justify-between mb-4">
                                <div>
                                    <h4 class="font-semibold text-gray-900">
                                        {{ timesheet.employee?.first_name }} {{ timesheet.employee?.last_name }}
                                    </h4>
                                    <p class="text-sm text-gray-500">
                                        Week: {{ formatDate(timesheet.week_start_date) }} - {{ formatDate(timesheet.week_end_date) }}
                                    </p>
                                </div>
                                <div class="flex items-center gap-3">
                                    <span class="text-lg font-bold text-gray-900">
                                        {{ getTotalHours(timesheet) }} hrs
                                    </span>
                                    <span :class="['px-3 py-1 rounded-full text-xs font-medium', getStatusClass(timesheet.status)]">
                                        Pending
                                    </span>
                                </div>
                            </div>

                            <!-- Entries Preview -->
                            <div v-if="timesheet.entries?.length" class="mb-4">
                                <table class="w-full text-sm">
                                    <thead>
                                        <tr class="border-b border-gray-100">
                                            <th class="text-left py-2 text-gray-600 font-medium">Project</th>
                                            <th class="text-left py-2 text-gray-600 font-medium">Activity</th>
                                            <th class="text-right py-2 text-gray-600 font-medium">Hours</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr v-for="entry in timesheet.entries.slice(0, 5)" :key="entry.id" class="border-b border-gray-50">
                                            <td class="py-2">{{ entry.project || '-' }}</td>
                                            <td class="py-2">{{ entry.activity || '-' }}</td>
                                            <td class="py-2 text-right">{{ entry.hours }}</td>
                                        </tr>
                                        <tr v-if="timesheet.entries.length > 5">
                                            <td colspan="3" class="py-2 text-center text-gray-400 text-xs">
                                                +{{ timesheet.entries.length - 5 }} more entries
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>

                            <div class="flex items-center justify-between pt-4 border-t border-gray-100">
                                <span class="text-xs text-gray-400">
                                    Submitted: {{ formatDate(timesheet.submitted_at) }}
                                </span>
                                <div class="flex items-center gap-2">
                                    <button 
                                        @click="openRejectModal(timesheet)"
                                        class="px-4 py-2 border border-red-300 text-red-600 rounded-lg text-sm font-medium hover:bg-red-50 transition-colors"
                                    >
                                        <i class="fas fa-times mr-1"></i> Reject
                                    </button>
                                    <button 
                                        @click="approveTimesheet(timesheet.id)"
                                        class="px-4 py-2 bg-green-600 text-white rounded-lg text-sm font-medium hover:bg-green-700 transition-colors"
                                    >
                                        <i class="fas fa-check mr-1"></i> Approve
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- All Timesheets -->
                <div v-if="activeTab === 'all'">
                    <div v-if="allTimesheets?.length === 0" class="text-center py-12 text-gray-500">
                        <i class="fas fa-folder-open text-4xl text-gray-300 mb-4"></i>
                        <p>No timesheets found.</p>
                    </div>

                    <div v-else class="overflow-x-auto">
                        <table class="w-full">
                            <thead>
                                <tr class="border-b border-gray-200">
                                    <th class="text-left py-4 px-3 text-sm font-semibold text-gray-700">Employee</th>
                                    <th class="text-left py-4 px-3 text-sm font-semibold text-gray-700">Period</th>
                                    <th class="text-center py-4 px-3 text-sm font-semibold text-gray-700">Total Hours</th>
                                    <th class="text-center py-4 px-3 text-sm font-semibold text-gray-700">Status</th>
                                    <th class="text-center py-4 px-3 text-sm font-semibold text-gray-700">Submitted</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-for="timesheet in allTimesheets" :key="timesheet.id" class="border-b border-gray-100 hover:bg-gray-50">
                                    <td class="py-4 px-3">
                                        <span class="font-medium text-gray-900">
                                            {{ timesheet.employee?.first_name }} {{ timesheet.employee?.last_name }}
                                        </span>
                                    </td>
                                    <td class="py-4 px-3 text-sm text-gray-600">
                                        {{ formatDate(timesheet.week_start_date) }} - {{ formatDate(timesheet.week_end_date) }}
                                    </td>
                                    <td class="py-4 px-3 text-center font-medium text-gray-900">
                                        {{ getTotalHours(timesheet) }}
                                    </td>
                                    <td class="py-4 px-3 text-center">
                                        <span :class="['px-3 py-1 rounded-full text-xs font-medium', getStatusClass(timesheet.status)]">
                                            {{ timesheet.status === 'draft' ? 'Draft' : 
                                               timesheet.status === 'submitted' ? 'Pending' :
                                               timesheet.status === 'approved' ? 'Approved' :
                                               timesheet.status === 'rejected' ? 'Rejected' : timesheet.status }}
                                        </span>
                                    </td>
                                    <td class="py-4 px-3 text-center text-sm text-gray-500">
                                        {{ timesheet.submitted_at ? formatDate(timesheet.submitted_at) : '-' }}
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- Reject Modal -->
        <div v-if="rejectModal" class="fixed inset-0 bg-black/50 flex items-center justify-center z-50">
            <div class="bg-white rounded-2xl p-6 w-full max-w-md mx-4 shadow-xl">
                <h3 class="text-xl font-bold text-gray-900 mb-4">Reject Timesheet</h3>
                <p class="text-sm text-gray-600 mb-4">
                    Please provide a reason for rejecting this timesheet. This will be visible to the employee.
                </p>
                <textarea 
                    v-model="rejectReason"
                    rows="4"
                    class="w-full border border-gray-300 rounded-lg p-3 text-sm focus:ring-2 focus:ring-red-500 focus:border-red-500"
                    placeholder="Enter rejection reason..."
                ></textarea>
                <div class="flex items-center justify-end gap-3 mt-4">
                    <button 
                        @click="closeRejectModal"
                        class="px-4 py-2 border border-gray-300 rounded-lg text-sm font-medium text-gray-700 hover:bg-gray-50"
                    >
                        Cancel
                    </button>
                    <button 
                        @click="rejectTimesheet"
                        class="px-4 py-2 bg-red-600 text-white rounded-lg text-sm font-medium hover:bg-red-700"
                    >
                        Reject Timesheet
                    </button>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
