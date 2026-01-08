<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { router } from '@inertiajs/vue3';
import { ref, computed } from 'vue';

const props = defineProps({
    requests: Array,
});

const filterStatus = ref('all');

const filteredRequests = () => {
    if (filterStatus.value === 'all') {
        return props.requests;
    }
    return props.requests.filter(r => r.status === filterStatus.value);
};

const getStatusClass = (status) => {
    const classes = {
        'Pending': 'bg-yellow-100 text-yellow-800 border-yellow-200',
        'Approved': 'bg-green-100 text-green-800 border-green-200',
        'Rejected': 'bg-red-100 text-red-800 border-red-200',
        'Cancelled': 'bg-gray-100 text-gray-800 border-gray-200',
    };
    return classes[status] || 'bg-gray-100 text-gray-800';
};

const approveRequest = (id) => {
    if (confirm('Are you sure you want to approve this overtime request?')) {
        router.post(route('overtime-requests.approve', id));
    }
};

const declineRequest = (id) => {
    if (confirm('Are you sure you want to decline this overtime request?')) {
        router.post(route('overtime-requests.decline', id));
    }
};

const stats = computed(() => {
    return {
        total: props.requests.length,
        pending: props.requests.filter(r => r.status === 'Pending').length,
        approved: props.requests.filter(r => r.status === 'Approved').length,
        rejected: props.requests.filter(r => r.status === 'Rejected').length,
        totalHours: props.requests
            .filter(r => r.status === 'Approved')
            .reduce((sum, r) => sum + parseFloat(r.hours), 0)
            .toFixed(2),
    };
});
</script>

<template>
    <AuthenticatedLayout>
        <div class="bg-white rounded-lg shadow-sm">
            <!-- Header Section -->
            <div class="border-b border-gray-200 px-8 py-6">
                <h2 class="text-2xl font-bold text-gray-900">
                    <i class="fas fa-user-shield text-brand-yellow mr-3"></i>
                    Overtime Management (HR Admin)
                </h2>
            </div>

            <div class="p-8 space-y-6">
                <!-- Statistics Cards -->
                <div class="grid grid-cols-1 md:grid-cols-5 gap-4">
                    <div class="bg-gradient-to-br from-blue-50 to-blue-100 rounded-lg shadow-sm p-6 border border-blue-200">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-sm font-medium text-blue-700">Total Requests</p>
                                <p class="text-3xl font-bold text-blue-900">{{ stats.total }}</p>
                            </div>
                            <i class="fas fa-clipboard-list text-3xl text-blue-500"></i>
                        </div>
                    </div>

                    <div class="bg-gradient-to-br from-yellow-50 to-yellow-100 rounded-lg shadow-sm p-6 border border-yellow-200">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-sm font-medium text-yellow-700">Pending</p>
                                <p class="text-3xl font-bold text-yellow-900">{{ stats.pending }}</p>
                            </div>
                            <i class="fas fa-clock text-3xl text-yellow-500"></i>
                        </div>
                    </div>

                    <div class="bg-gradient-to-br from-green-50 to-green-100 rounded-lg shadow-sm p-6 border border-green-200">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-sm font-medium text-green-700">Approved</p>
                                <p class="text-3xl font-bold text-green-900">{{ stats.approved }}</p>
                            </div>
                            <i class="fas fa-check-circle text-3xl text-green-500"></i>
                        </div>
                    </div>

                    <div class="bg-gradient-to-br from-red-50 to-red-100 rounded-lg shadow-sm p-6 border border-red-200">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-sm font-medium text-red-700">Rejected</p>
                                <p class="text-3xl font-bold text-red-900">{{ stats.rejected }}</p>
                            </div>
                            <i class="fas fa-times-circle text-3xl text-red-500"></i>
                        </div>
                    </div>

                    <div class="bg-gradient-to-br from-purple-50 to-purple-100 rounded-lg shadow-sm p-6 border border-purple-200">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-sm font-medium text-purple-700">Total Hours</p>
                                <p class="text-3xl font-bold text-purple-900">{{ stats.totalHours }}</p>
                            </div>
                            <i class="fas fa-business-time text-3xl text-purple-500"></i>
                        </div>
                    </div>
                </div>

                <!-- Filters -->
                <div class="bg-gray-50 rounded-lg p-4 border border-gray-200">
                    <div class="flex items-center gap-2">
                        <label class="text-sm font-semibold text-gray-700">Filter by Status:</label>
                        <button
                            @click="filterStatus = 'all'"
                            :class="filterStatus === 'all' ? 'bg-brand-yellow text-brand-dark font-bold shadow-sm' : 'bg-white text-gray-600 hover:bg-gray-100'"
                            class="px-4 py-2 rounded-md text-sm font-medium transition border border-gray-200"
                        >
                            All
                        </button>
                        <button
                            @click="filterStatus = 'Pending'"
                            :class="filterStatus === 'Pending' ? 'bg-yellow-500 text-white font-bold shadow-sm' : 'bg-white text-gray-600 hover:bg-gray-100'"
                            class="px-4 py-2 rounded-md text-sm font-medium transition border border-gray-200"
                        >
                            Pending
                        </button>
                        <button
                            @click="filterStatus = 'Approved'"
                            :class="filterStatus === 'Approved' ? 'bg-green-500 text-white font-bold shadow-sm' : 'bg-white text-gray-600 hover:bg-gray-100'"
                            class="px-4 py-2 rounded-md text-sm font-medium transition border border-gray-200"
                        >
                            Approved
                        </button>
                        <button
                            @click="filterStatus = 'Rejected'"
                            :class="filterStatus === 'Rejected' ? 'bg-red-500 text-white font-bold shadow-sm' : 'bg-white text-gray-600 hover:bg-gray-100'"
                            class="px-4 py-2 rounded-md text-sm font-medium transition border border-gray-200"
                        >
                            Rejected
                        </button>
                    </div>
                </div>

                <!-- Overtime Requests Table -->
                <div v-if="!requests || requests.length === 0" class="text-center py-12 bg-gray-50 rounded-lg border border-gray-200">
                    <i class="fas fa-inbox text-gray-300 text-6xl mb-4"></i>
                    <p class="text-gray-500 text-lg">No overtime requests found.</p>
                </div>

                <div v-else class="overflow-x-auto rounded-lg border border-gray-200">
                    <table class="w-full text-sm">
                        <thead class="bg-gray-50 border-b border-gray-200">
                            <tr>
                                <th class="px-6 py-4 text-left font-semibold text-gray-700">Employee</th>
                                <th class="px-6 py-4 text-left font-semibold text-gray-700">Date</th>
                                <th class="px-6 py-4 text-left font-semibold text-gray-700">Start</th>
                                <th class="px-6 py-4 text-left font-semibold text-gray-700">End</th>
                                <th class="px-6 py-4 text-left font-semibold text-gray-700">Hours</th>
                                <th class="px-6 py-4 text-left font-semibold text-gray-700">Reason</th>
                                <th class="px-6 py-4 text-left font-semibold text-gray-700">Status</th>
                                <th class="px-6 py-4 text-left font-semibold text-gray-700">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-100">
                            <tr v-for="request in filteredRequests()" :key="request.id" class="hover:bg-gray-50 transition-colors">
                                <td class="px-6 py-4">
                                    <div class="flex items-center">
                                        <div class="flex-shrink-0 h-10 w-10 bg-brand-yellow rounded-full flex items-center justify-center">
                                            <span class="text-brand-dark font-semibold text-sm">
                                                {{ request.user_name.split(' ').map(n => n[0]).join('').substring(0, 2) }}
                                            </span>
                                        </div>
                                        <div class="ml-4">
                                            <div class="text-sm font-semibold text-gray-900">{{ request.user_name }}</div>
                                            <div class="text-xs text-gray-500">{{ request.user_email }}</div>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4">
                                    <div class="text-sm font-medium text-gray-900">{{ request.date }}</div>
                                    <div class="text-xs text-gray-400">{{ request.created_at }}</div>
                                </td>
                                <td class="px-6 py-4 text-gray-600 font-medium">{{ request.start_time }}</td>
                                <td class="px-6 py-4 text-gray-600 font-medium">{{ request.end_time }}</td>
                                <td class="px-6 py-4">
                                    <span class="text-lg font-bold text-brand-dark">{{ request.hours }}</span>
                                </td>
                                <td class="px-6 py-4">
                                    <div class="text-sm text-gray-700 max-w-xs truncate" :title="request.reason">
                                        {{ request.reason }}
                                    </div>
                                </td>
                                <td class="px-6 py-4">
                                    <span
                                        :class="getStatusClass(request.status)"
                                        class="inline-flex px-3 py-1 text-xs font-semibold rounded-full border"
                                    >
                                        {{ request.status }}
                                    </span>
                                    <div v-if="request.reviewer_name" class="text-xs text-gray-500 mt-1">
                                        by {{ request.reviewer_name }}
                                        <div class="text-xs text-gray-400">{{ request.reviewed_at }}</div>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div v-if="request.status === 'Pending'" class="flex gap-2">
                                        <button
                                            @click="approveRequest(request.id)"
                                            class="inline-flex items-center px-3 py-1.5 bg-green-600 text-white text-xs font-semibold rounded-md hover:bg-green-700 transition shadow-sm"
                                        >
                                            <i class="fas fa-check mr-1"></i>
                                            Approve
                                        </button>
                                        <button
                                            @click="declineRequest(request.id)"
                                            class="inline-flex items-center px-3 py-1.5 bg-red-600 text-white text-xs font-semibold rounded-md hover:bg-red-700 transition shadow-sm"
                                        >
                                            <i class="fas fa-times mr-1"></i>
                                            Decline
                                        </button>
                                    </div>
                                    <div v-else class="text-gray-400 text-xs">
                                        <i class="fas fa-lock mr-1"></i>
                                        Processed
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
