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

const getTypeIcon = (type) => {
    const icons = {
        'Vacation': 'fa-umbrella-beach',
        'Sick': 'fa-notes-medical',
        'Personal': 'fa-user',
        'Unpaid': 'fa-money-bill-wave',
        'Emergency': 'fa-exclamation-triangle',
    };
    return icons[type] || 'fa-calendar';
};

const approveRequest = (id) => {
    if (confirm('Are you sure you want to approve this leave request?')) {
        router.post(route('leave-requests.approve', id));
    }
};

const declineRequest = (id) => {
    if (confirm('Are you sure you want to decline this leave request?')) {
        router.post(route('leave-requests.decline', id));
    }
};

const deleteRequest = (id) => {
    if (confirm('Are you sure you want to permanently delete this leave request? This action cannot be undone.')) {
        router.delete(route('leave-requests.destroy', id));
    }
};

const stats = computed(() => {
    return {
        total: props.requests.length,
        pending: props.requests.filter(r => r.status === 'Pending').length,
        approved: props.requests.filter(r => r.status === 'Approved').length,
        rejected: props.requests.filter(r => r.status === 'Rejected').length,
    };
});
</script>

<template>
    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center justify-between">
                <h2 class="text-xl font-semibold leading-tight text-gray-800">
                    <i class="fas fa-user-shield mr-2"></i>
                    Leave Management (HR Admin)
                </h2>
            </div>
        </template>

        <div class="py-12">
            <div class="mx-auto max-w-7xl sm:px-6 lg:px-8 space-y-6">
                <!-- Statistics Cards -->
                <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                    <div class="bg-white rounded-lg shadow p-6 border-l-4 border-blue-500">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-sm font-medium text-gray-600">Total Requests</p>
                                <p class="text-3xl font-bold text-gray-900">{{ stats.total }}</p>
                            </div>
                            <i class="fas fa-clipboard-list text-3xl text-blue-500"></i>
                        </div>
                    </div>

                    <div class="bg-white rounded-lg shadow p-6 border-l-4 border-yellow-500">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-sm font-medium text-gray-600">Pending</p>
                                <p class="text-3xl font-bold text-gray-900">{{ stats.pending }}</p>
                            </div>
                            <i class="fas fa-clock text-3xl text-yellow-500"></i>
                        </div>
                    </div>

                    <div class="bg-white rounded-lg shadow p-6 border-l-4 border-green-500">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-sm font-medium text-gray-600">Approved</p>
                                <p class="text-3xl font-bold text-gray-900">{{ stats.approved }}</p>
                            </div>
                            <i class="fas fa-check-circle text-3xl text-green-500"></i>
                        </div>
                    </div>

                    <div class="bg-white rounded-lg shadow p-6 border-l-4 border-red-500">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-sm font-medium text-gray-600">Rejected</p>
                                <p class="text-3xl font-bold text-gray-900">{{ stats.rejected }}</p>
                            </div>
                            <i class="fas fa-times-circle text-3xl text-red-500"></i>
                        </div>
                    </div>
                </div>

                <!-- Filters -->
                <div class="bg-white rounded-lg shadow p-4">
                    <div class="flex items-center gap-2">
                        <label class="text-sm font-medium text-gray-700">Filter by Status:</label>
                        <button
                            @click="filterStatus = 'all'"
                            :class="filterStatus === 'all' ? 'bg-indigo-600 text-white' : 'bg-gray-200 text-gray-700'"
                            class="px-4 py-2 rounded-md text-sm font-medium transition"
                        >
                            All
                        </button>
                        <button
                            @click="filterStatus = 'Pending'"
                            :class="filterStatus === 'Pending' ? 'bg-yellow-600 text-white' : 'bg-gray-200 text-gray-700'"
                            class="px-4 py-2 rounded-md text-sm font-medium transition"
                        >
                            Pending
                        </button>
                        <button
                            @click="filterStatus = 'Approved'"
                            :class="filterStatus === 'Approved' ? 'bg-green-600 text-white' : 'bg-gray-200 text-gray-700'"
                            class="px-4 py-2 rounded-md text-sm font-medium transition"
                        >
                            Approved
                        </button>
                        <button
                            @click="filterStatus = 'Rejected'"
                            :class="filterStatus === 'Rejected' ? 'bg-red-600 text-white' : 'bg-gray-200 text-gray-700'"
                            class="px-4 py-2 rounded-md text-sm font-medium transition"
                        >
                            Rejected
                        </button>
                    </div>
                </div>

                <!-- Leave Requests Table -->
                <div class="bg-white shadow sm:rounded-lg overflow-hidden">
                    <div v-if="!requests || requests.length === 0" class="p-12 text-center">
                        <i class="fas fa-inbox text-6xl text-gray-300 mb-4"></i>
                        <p class="text-gray-500">No leave requests found.</p>
                    </div>

                    <div v-else class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Employee</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Type</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Dates</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Reason</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                <tr v-for="request in filteredRequests()" :key="request.id" class="hover:bg-gray-50">
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="flex items-center">
                                            <div class="flex-shrink-0 h-10 w-10 bg-indigo-100 rounded-full flex items-center justify-center">
                                                <span class="text-indigo-600 font-semibold text-sm">
                                                    {{ request.user_name.split(' ').map(n => n[0]).join('').substring(0, 2) }}
                                                </span>
                                            </div>
                                            <div class="ml-4">
                                                <div class="text-sm font-medium text-gray-900">{{ request.user_name }}</div>
                                                <div class="text-sm text-gray-500">{{ request.user_email }}</div>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="flex items-center">
                                            <i :class="`fas ${getTypeIcon(request.type)} text-indigo-600 mr-2`"></i>
                                            <span class="text-sm font-medium text-gray-900">{{ request.type }}</span>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm text-gray-900">{{ request.start_date }}</div>
                                        <div class="text-sm text-gray-500">to {{ request.end_date }}</div>
                                        <div class="text-xs text-gray-400 mt-1">Submitted: {{ request.created_at }}</div>
                                    </td>
                                    <td class="px-6 py-4">
                                        <div class="text-sm text-gray-900 max-w-md">
                                            {{ request.reason }}
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span
                                            :class="getStatusClass(request.status)"
                                            class="px-2 py-1 inline-flex text-xs leading-5 font-semibold rounded-full border"
                                        >
                                            {{ request.status }}
                                        </span>
                                        <div v-if="request.reviewer_name" class="text-xs text-gray-500 mt-1">
                                            by {{ request.reviewer_name }}
                                            <div class="text-xs text-gray-400">{{ request.reviewed_at }}</div>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                        <div v-if="request.status === 'Pending'" class="flex gap-2">
                                            <button
                                                @click="approveRequest(request.id)"
                                                class="inline-flex items-center px-3 py-1 bg-green-600 text-white rounded-md hover:bg-green-700 transition"
                                            >
                                                <i class="fas fa-check mr-1"></i>
                                                Approve
                                            </button>
                                            <button
                                                @click="declineRequest(request.id)"
                                                class="inline-flex items-center px-3 py-1 bg-red-600 text-white rounded-md hover:bg-red-700 transition"
                                            >
                                                <i class="fas fa-times mr-1"></i>
                                                Decline
                                            </button>
                                        </div>
                                        <div v-else class="flex gap-2">
                                            <span class="text-gray-400">
                                                <i class="fas fa-lock mr-1"></i>
                                                Processed
                                            </span>
                                        </div>
                                        <button
                                            @click="deleteRequest(request.id)"
                                            class="inline-flex items-center px-3 py-1 mt-2 bg-gray-700 text-white rounded-md hover:bg-gray-800 transition text-xs"
                                        >
                                            <i class="fas fa-trash mr-1"></i>
                                            Delete
                                        </button>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
