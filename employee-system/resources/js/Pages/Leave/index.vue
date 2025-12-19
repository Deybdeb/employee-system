<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Link, router } from '@inertiajs/vue3';

const props = defineProps({
    requests: Array,
});

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

const cancelRequest = (id) => {
    if (confirm('Are you sure you want to cancel this leave request?')) {
        router.post(route('leave-requests.cancel', id));
    }
};
</script>

<template>
    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center justify-between">
                <h2 class="text-xl font-semibold leading-tight text-gray-800">My Leave Requests</h2>
                <Link
                    :href="route('leave-requests.create')"
                    class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700 focus:bg-indigo-700 active:bg-indigo-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150"
                >
                    <i class="fas fa-plus mr-2"></i>
                    New Request
                </Link>
            </div>
        </template>

        <div class="py-12">
            <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
                <div class="bg-white shadow sm:rounded-lg overflow-hidden">
                    <div v-if="!requests || requests.length === 0" class="p-12 text-center">
                        <i class="fas fa-calendar-times text-6xl text-gray-300 mb-4"></i>
                        <p class="text-gray-500 mb-4">You haven't submitted any leave requests yet.</p>
                        <Link
                            :href="route('leave-requests.create')"
                            class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700"
                        >
                            <i class="fas fa-plus mr-2"></i>
                            Submit Your First Request
                        </Link>
                    </div>

                    <div v-else class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Type</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Dates</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Reason</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Submitted</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                <tr v-for="request in requests" :key="request.id" class="hover:bg-gray-50">
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="flex items-center">
                                            <i :class="`fas ${getTypeIcon(request.type)} text-indigo-600 mr-2`"></i>
                                            <span class="text-sm font-medium text-gray-900">{{ request.type }}</span>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm text-gray-900">{{ request.start_date }}</div>
                                        <div class="text-sm text-gray-500">to {{ request.end_date }}</div>
                                    </td>
                                    <td class="px-6 py-4">
                                        <div class="text-sm text-gray-900 max-w-xs truncate" :title="request.reason">
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
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                        {{ request.created_at }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                        <button
                                            v-if="request.status === 'Pending'"
                                            @click="cancelRequest(request.id)"
                                            class="text-red-600 hover:text-red-900"
                                        >
                                            <i class="fas fa-times-circle mr-1"></i>
                                            Cancel
                                        </button>
                                        <span v-else class="text-gray-400">-</span>
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