<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Link, useForm } from '@inertiajs/vue3';
import { computed } from 'vue';

const props = defineProps({
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
</script>

<template>
    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center justify-between">
                <h2 class="text-xl font-semibold leading-tight text-gray-800">New Leave Request</h2>
                <Link :href="route('leave-requests.index')" class="text-sm text-gray-600 hover:text-gray-900">
                    <i class="fas fa-arrow-left mr-2"></i>Back to History
                </Link>
            </div>
        </template>

        <div class="py-12">
            <div class="mx-auto max-w-3xl sm:px-6 lg:px-8">
                <div class="bg-white p-8 shadow sm:rounded-lg">
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
                                rows="5"
                                class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                :class="{ 'border-red-500': form.errors.reason }"
                                placeholder="Please provide a detailed reason for your leave request..."
                                required
                            ></textarea>
                            <p class="mt-1 text-sm text-gray-500">{{ form.reason?.length || 0 }}/1000 characters</p>
                            <p v-if="form.errors.reason" class="mt-1 text-sm text-red-600">{{ form.errors.reason }}</p>
                        </div>

                        <!-- Submit Buttons -->
                        <div class="flex items-center justify-end gap-4 pt-4 border-t">
                            <Link
                                :href="route('leave-requests.index')"
                                class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-md hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500"
                            >
                                Cancel
                            </Link>
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
            </div>
        </div>
    </AuthenticatedLayout>
</template>