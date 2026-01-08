<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { useForm } from '@inertiajs/vue3';
import { computed, ref } from 'vue';

const props = defineProps({
    requests: Array,
});

const activeTab = ref('apply');

const form = useForm({
    date: '',
    start_time: '',
    end_time: '',
    reason: '',
});

const submit = () => {
    form.post(route('overtime-requests.store'), {
        onSuccess: () => {
            form.reset();
            activeTab.value = 'my-overtime';
        },
    });
};

const calculatedHours = computed(() => {
    if (!form.start_time || !form.end_time) return '0.00';
    
    const [startHour, startMin] = form.start_time.split(':').map(Number);
    const [endHour, endMin] = form.end_time.split(':').map(Number);
    
    const startMinutes = startHour * 60 + startMin;
    const endMinutes = endHour * 60 + endMin;
    
    if (endMinutes <= startMinutes) return '0.00';
    
    const diffMinutes = endMinutes - startMinutes;
    const hours = (diffMinutes / 60).toFixed(2);
    
    return hours;
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

const cancelRequest = (id) => {
    if (confirm('Are you sure you want to cancel this overtime request?')) {
        form.post(route('overtime-requests.cancel', id), {
            preserveScroll: true,
        });
    }
};
</script>

<template>
    <AuthenticatedLayout>
        <div class="bg-white rounded-lg shadow-sm">
            <!-- Header Section -->
            <div class="border-b border-gray-200 px-8 py-6">
                <h2 class="text-2xl font-bold text-gray-900">
                    <i class="fas fa-clock text-brand-yellow mr-3"></i>
                    Overtime
                </h2>
            </div>

            <!-- Tab Navigation -->
            <div class="border-b border-gray-200 px-8">
                <div class="flex gap-1 bg-brand-light p-1 rounded-t-lg inline-flex">
                    <button
                        @click="activeTab = 'apply'"
                        :class="activeTab === 'apply' 
                            ? 'bg-brand-yellow text-brand-dark font-bold shadow-sm' 
                            : 'text-gray-500 hover:text-brand-dark font-medium'"
                        class="px-6 py-2 rounded-md text-sm transition-colors"
                    >
                        Apply
                    </button>
                    <button
                        @click="activeTab = 'my-overtime'"
                        :class="activeTab === 'my-overtime' 
                            ? 'bg-brand-yellow text-brand-dark font-bold shadow-sm' 
                            : 'text-gray-500 hover:text-brand-dark font-medium'"
                        class="px-6 py-2 rounded-md text-sm transition-colors"
                    >
                        My Overtime
                    </button>
                </div>
            </div>

            <!-- Tab Content -->
            <div class="p-8">
                <!-- Apply Overtime Tab -->
                <div v-if="activeTab === 'apply'" class="max-w-3xl">
                    <h3 class="text-xl font-bold text-gray-900 mb-6">Apply Overtime</h3>
                    
                    <form @submit.prevent="submit" class="space-y-6">
                        <!-- Date -->
                        <div>
                            <label for="date" class="block text-sm font-semibold text-gray-700 mb-2">
                                Date*
                            </label>
                            <input
                                id="date"
                                type="date"
                                v-model="form.date"
                                class="w-full rounded-lg border-gray-300 shadow-sm focus:border-brand-yellow focus:ring-brand-yellow"
                                :class="{ 'border-red-500': form.errors.date }"
                                required
                            >
                            <p v-if="form.errors.date" class="mt-1 text-sm text-red-600">{{ form.errors.date }}</p>
                        </div>

                        <!-- Start Time -->
                        <div>
                            <label for="start_time" class="block text-sm font-semibold text-gray-700 mb-2">
                                Start Time*
                            </label>
                            <input
                                id="start_time"
                                type="time"
                                v-model="form.start_time"
                                class="w-full rounded-lg border-gray-300 shadow-sm focus:border-brand-yellow focus:ring-brand-yellow"
                                :class="{ 'border-red-500': form.errors.start_time }"
                                required
                            >
                            <p v-if="form.errors.start_time" class="mt-1 text-sm text-red-600">{{ form.errors.start_time }}</p>
                        </div>

                        <!-- End Time -->
                        <div>
                            <label for="end_time" class="block text-sm font-semibold text-gray-700 mb-2">
                                End Time*
                            </label>
                            <input
                                id="end_time"
                                type="time"
                                v-model="form.end_time"
                                class="w-full rounded-lg border-gray-300 shadow-sm focus:border-brand-yellow focus:ring-brand-yellow"
                                :class="{ 'border-red-500': form.errors.end_time }"
                                required
                            >
                            <p v-if="form.errors.end_time" class="mt-1 text-sm text-red-600">{{ form.errors.end_time }}</p>
                        </div>

                        <!-- Calculated Hours Display -->
                        <div v-if="form.start_time && form.end_time" class="bg-brand-light border border-brand-yellow rounded-lg p-4">
                            <div class="flex items-center justify-between">
                                <span class="text-sm font-medium text-gray-700">Total Hours:</span>
                                <span class="text-2xl font-bold text-brand-dark">{{ calculatedHours }}</span>
                            </div>
                        </div>

                        <!-- Reason -->
                        <div>
                            <label for="reason" class="block text-sm font-semibold text-gray-700 mb-2">
                                Reason*
                            </label>
                            <textarea
                                id="reason"
                                v-model="form.reason"
                                rows="4"
                                class="w-full rounded-lg border-gray-300 shadow-sm focus:border-brand-yellow focus:ring-brand-yellow"
                                :class="{ 'border-red-500': form.errors.reason }"
                                placeholder="Please provide a reason for overtime..."
                                required
                            ></textarea>
                            <p v-if="form.errors.reason" class="mt-1 text-sm text-red-600">{{ form.errors.reason }}</p>
                        </div>

                        <!-- Required Note -->
                        <p class="text-sm text-gray-500">* Required</p>

                        <!-- Submit Button -->
                        <div class="flex justify-end">
                            <button
                                type="submit"
                                :disabled="form.processing"
                                class="bg-gray-900 text-white px-8 py-3 rounded-full font-bold hover:bg-gray-800 disabled:opacity-50 disabled:cursor-not-allowed transition-colors"
                            >
                                {{ form.processing ? 'Submitting...' : 'Apply' }}
                            </button>
                        </div>
                    </form>
                </div>

                <!-- My Overtime Tab -->
                <div v-else-if="activeTab === 'my-overtime'">
                    <h3 class="text-xl font-bold text-gray-900 mb-6">My Overtime</h3>
                    
                    <div v-if="requests.length === 0" class="text-center py-12">
                        <i class="fas fa-clock text-gray-300 text-6xl mb-4"></i>
                        <p class="text-gray-500 text-lg">No overtime requests found.</p>
                        <button
                            @click="activeTab = 'apply'"
                            class="mt-4 text-brand-yellow hover:text-yellow-600 font-medium"
                        >
                            Submit your first overtime request
                        </button>
                    </div>

                    <div v-else class="overflow-x-auto rounded-lg border border-gray-200">
                        <table class="w-full text-sm">
                            <thead class="bg-gray-50 border-b border-gray-200">
                                <tr>
                                    <th class="px-6 py-4 text-left font-semibold text-gray-700">Date</th>
                                    <th class="px-6 py-4 text-left font-semibold text-gray-700">Start</th>
                                    <th class="px-6 py-4 text-left font-semibold text-gray-700">End</th>
                                    <th class="px-6 py-4 text-left font-semibold text-gray-700">Hours</th>
                                    <th class="px-6 py-4 text-left font-semibold text-gray-700">Status</th>
                                    <th class="px-6 py-4 text-left font-semibold text-gray-700">Actions</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-100">
                                <tr v-for="request in requests" :key="request.id" class="hover:bg-gray-50 transition-colors">
                                    <td class="px-6 py-4 font-medium text-gray-900">{{ request.date }}</td>
                                    <td class="px-6 py-4 text-gray-600">{{ request.start_time }}</td>
                                    <td class="px-6 py-4 text-gray-600">{{ request.end_time }}</td>
                                    <td class="px-6 py-4 font-semibold text-brand-dark">{{ request.hours }}</td>
                                    <td class="px-6 py-4">
                                        <span
                                            :class="getStatusClass(request.status)"
                                            class="inline-flex px-3 py-1 text-xs font-semibold rounded-full border"
                                        >
                                            {{ request.status }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4">
                                        <button
                                            v-if="request.status === 'Pending'"
                                            @click="cancelRequest(request.id)"
                                            class="text-red-600 hover:text-red-800 font-medium text-sm"
                                        >
                                            Cancel
                                        </button>
                                        <span v-else class="text-gray-400 text-sm">-</span>
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
