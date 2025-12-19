<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Link, useForm } from '@inertiajs/vue3';
import { computed } from 'vue';

const props = defineProps({
    requests: Array,
    leaveTypes: Array,
});

const form = useForm({
    type: '',
    start_date: '',
    end_date: '',
    reason: '',
});

const submit = () => {
    form.post(route('leave-requests.store'), {
        onSuccess: () => {
            form.reset();
        },
    });
};

const durationDays = computed(() => {
    if (!form.start_date || !form.end_date) return 0;
    const start = new Date(form.start_date);
    const end = new Date(form.end_date);
    const diffTime = Math.abs(end - start);
    const diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24)) + 1;
    return diffDays;
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
        form.post(route('leave-requests.cancel', id), {
            preserveScroll: true,
        });
    }
};
</script>

<template>
    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-xl font-semibold leading-tight text-gray-800">Leave Management</h2>
        </template>

        <div class="py-12">
            <div class="mx-auto max-w-7xl sm:px-6 lg:px-8 space-y-8">
                <!-- Request Form Section -->
                <div class="bg-white p-8 shadow sm:rounded-lg">
                    <div class="mb-6">
                        <h3 class="text-lg font-semibold text-gray-900">
                            <i class="fas fa-paper-plane text-indigo-600 mr-2"></i>
                            Request Leave
                        </h3>
                        <p class="text-sm text-gray-600 mt-1">Fill out the form below to submit a new leave request.</p>
                    </div>

                    <form @submit.prevent="submit" class="space-y-6">
                        <!-- Leave Type -->
                        <div>
                            <label for="type" class="block text-sm font-medium text-gray-700 mb-2">
                                Leave Type <span class="text-red-500">*</span>
                            </label>
                            <select
                                id="type"
                                v-model="form.type"
                                class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                :class="{ 'border-red-500': form.errors.type }"
                                required
                            >
                                <option value="">Select leave type</option>
                                <option v-for="type in leaveTypes" :key="type" :value="type">
                                    {{ type }}
                                </option>
                            </select>
                            <p v-if="form.errors.type" class="mt-1 text-sm text-red-600">{{ form.errors.type }}</p>
                        </div>

                        <!-- Date Range -->
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label for="start_date" class="block text-sm font-medium text-gray-700 mb-2">
                                    Start Date <span class="text-red-500">*</span>
                                </label>
                                <input
                                    id="start_date"
                                    type="date"
                                    v-model="form.start_date"
                                    class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                    :class="{ 'border-red-500': form.errors.start_date }"
                                    :min="new Date().toISOString().split('T')[0]"
                                    required
                                />
                                <p v-if="form.errors.start_date" class="mt-1 text-sm text-red-600">{{ form.errors.start_date }}</p>
                            </div>

                            <div>
                                <label for="end_date" class="block text-sm font-medium text-gray-700 mb-2">
                                    End Date <span class="text-red-500">*</span>
                                </label>
                                <input
                                    id="end_date"
                                    type="date"
                                    v-model="form.end_date"
                                    class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                    :class="{ 'border-red-500': form.errors.end_date }"
                                    :min="form.start_date || new Date().toISOString().split('T')[0]"
                                    required
                                />
                                <p v-if="form.errors.end_date" class="mt-1 text-sm text-red-600">{{ form.errors.end_date }}</p>
                            </div>
                        </div>

                        <!-- Duration Display -->
                        <div v-if="durationDays > 0" class="bg-blue-50 border border-blue-200 rounded-lg p-4">
                            <p class="text-sm text-blue-800">
                                <i class="fas fa-info-circle mr-2"></i>
                                <strong>Duration:</strong> {{ durationDays }} {{ durationDays === 1 ? 'day' : 'days' }}
                            </p>
                        </div>

                        <!-- Reason -->
                        <div>
                            <label for="reason" class="block text-sm font-medium text-gray-700 mb-2">
                                Reason <span class="text-red-500">*</span>
                            </label>
                            <textarea
                                id="reason"
                                v-model="form.reason"
                                rows="4"
                                class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                :class="{ 'border-red-500': form.errors.reason }"
                                placeholder="Please provide a detailed reason for your leave request..."
                                required
                            ></textarea>
                            <p class="mt-1 text-sm text-gray-500">{{ form.reason?.length || 0 }}/1000 characters</p>
                            <p v-if="form.errors.reason" class="mt-1 text-sm text-red-600">{{ form.errors.reason }}</p>
                        </div>

                        <!-- Submit Button -->
                        <div class="flex items-center justify-end pt-4 border-t">
                            <button
                                type="submit"
                                :disabled="form.processing"
                                class="px-6 py-2 text-sm font-medium text-white bg-indigo-600 border border-transparent rounded-md hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 disabled:opacity-50 disabled:cursor-not-allowed"
                            >
                                <span v-if="form.processing">
                                    <i class="fas fa-spinner fa-spin mr-2"></i>Submitting...
                                </span>
                                <span v-else>
                                    <i class="fas fa-paper-plane mr-2"></i>Submit Request
                                </span>
                            </button>
                        </div>
                    </form>
                </div>

                <!-- Leave History Section -->
                <div class="bg-white shadow sm:rounded-lg overflow-hidden">
                    <div class="px-8 py-6 border-b border-gray-200">
                        <h3 class="text-lg font-semibold text-gray-900">
                            <i class="fas fa-history text-indigo-600 mr-2"></i>
                            Leave Request History
                        </h3>
                        <p class="text-sm text-gray-600 mt-1">View and manage your previous leave requests.</p>
                    </div>

                    <div v-if="!requests || requests.length === 0" class="p-12 text-center">
                        <i class="fas fa-calendar-times text-6xl text-gray-300 mb-4"></i>
                        <p class="text-gray-500">No leave requests yet. Submit your first request above!</p>
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