<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { useForm } from '@inertiajs/vue3';

const props = defineProps({
    employees: Array,
    filters: Object,
    total: Number,
});

// Form for search and filters
const form = useForm({
    search: props.filters.search || '',
    job_title: props.filters.job_title || '',
    location: props.filters.location || '',
});

// Apply filters
const applyFilters = () => {
    form.get('/admin/employees', {
        preserveState: true,
        preserveScroll: true,
    });
};

// Reset filters
const resetFilters = () => {
    form.reset();
    form.get('/admin/employees', {
        preserveState: false,
        preserveScroll: false,
    });
};

// Get random color for avatar background
const getAvatarColor = (name) => {
    const colors = [
        'bg-blue-500',
        'bg-green-500',
        'bg-purple-500',
        'bg-pink-500',
        'bg-indigo-500',
        'bg-red-500',
        'bg-yellow-500',
        'bg-teal-500',
    ];
    const index = name.charCodeAt(0) % colors.length;
    return colors[index];
};
</script>

<template>
    <AuthenticatedLayout>
        <!-- Page Header -->
        <div class="mb-6">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-bold text-gray-900 mb-2">Directory</h1>
                    <p class="text-sm text-gray-600">
                        Company-wide contact list of employees with job titles and departments.
                    </p>
                </div>
            </div>
        </div>

        <!-- Search and Filter Section -->
        <div class="bg-white rounded-lg shadow-sm p-6 mb-6">
            <h2 class="text-lg font-semibold text-gray-900 mb-4 flex items-center">
                <i class="fas fa-filter text-brand-yellow mr-2"></i>
                Directory
            </h2>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-4">
                <!-- Employee Name Search -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        Employee Name
                    </label>
                    <input
                        v-model="form.search"
                        type="text"
                        placeholder="Type to search..."
                        class="w-full px-4 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-brand-yellow focus:border-transparent text-sm"
                        @keyup.enter="applyFilters"
                    />
                </div>

                <!-- Job Title Filter -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        Job Title
                    </label>
                    <select
                        v-model="form.job_title"
                        class="w-full px-4 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-brand-yellow focus:border-transparent text-sm bg-white"
                    >
                        <option value="">-- Select --</option>
                        <option value="Employee">Employee</option>
                        <option value="Manager">Manager</option>
                        <option value="HR">HR</option>
                        <option value="Developer">Developer</option>
                        <option value="Designer">Designer</option>
                    </select>
                </div>

                <!-- Location Filter -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        Location
                    </label>
                    <select
                        v-model="form.location"
                        class="w-full px-4 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-brand-yellow focus:border-transparent text-sm bg-white"
                    >
                        <option value="">-- Select --</option>
                        <option value="Manila">Manila</option>
                        <option value="Cebu">Cebu</option>
                        <option value="Davao">Davao</option>
                        <option value="Remote">Remote</option>
                    </select>
                </div>
            </div>

            <!-- Action Buttons -->
            <div class="flex justify-end gap-3">
                <button
                    type="button"
                    @click="resetFilters"
                    class="px-6 py-2 border border-gray-300 rounded-full text-sm font-medium text-gray-700 hover:bg-gray-50 transition-colors"
                >
                    Reset
                </button>
                <button
                    type="button"
                    @click="applyFilters"
                    class="px-6 py-2 bg-gray-900 text-white rounded-full text-sm font-medium hover:bg-gray-800 transition-colors"
                >
                    Search
                </button>
            </div>
        </div>

        <!-- Results Count -->
        <div class="mb-4">
            <p class="text-base font-medium text-gray-700">
                ({{ total }}) Records Found
            </p>
        </div>

        <!-- Employee Grid -->
        <div v-if="employees.length > 0" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
            <div
                v-for="employee in employees"
                :key="employee.id"
                class="bg-white rounded-lg shadow-sm hover:shadow-md transition-shadow p-6 border border-gray-100"
            >
                <!-- 2FA Badge -->
                <div v-if="employee.two_factor_enabled" class="absolute top-3 right-3">
                    <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-green-100 text-green-800" title="2FA Enabled">
                        <i class="fas fa-shield-alt mr-1"></i>
                        2FA
                    </span>
                </div>

                <!-- Profile Image/Avatar -->
                <div class="flex justify-center mb-4">
                    <div
                        :class="[
                            'w-20 h-20 rounded-full flex items-center justify-center text-white text-2xl font-bold',
                            getAvatarColor(employee.name)
                        ]"
                    >
                        {{ employee.initials }}
                    </div>
                </div>

                <!-- Employee Name -->
                <h3 class="text-center text-lg font-semibold text-gray-900 mb-1 truncate">
                    {{ employee.name }}
                </h3>

                <!-- Job Title -->
                <p class="text-center text-sm text-gray-600 mb-3">
                    {{ employee.job_title }}
                </p>

                <!-- Divider -->
                <div class="border-t border-gray-200 my-3"></div>

                <!-- Contact Information -->
                <div class="space-y-2 text-xs">
                    <div class="flex items-start">
                        <i class="fas fa-building text-gray-400 w-4 mt-0.5 mr-2 flex-shrink-0"></i>
                        <span class="text-gray-700 truncate">{{ employee.department }}</span>
                    </div>
                    <div class="flex items-start">
                        <i class="fas fa-envelope text-gray-400 w-4 mt-0.5 mr-2 flex-shrink-0"></i>
                        <span class="text-gray-700 truncate" :title="employee.email">
                            {{ employee.email || 'N/A' }}
                        </span>
                    </div>
                    <div class="flex items-start">
                        <i class="fas fa-phone text-gray-400 w-4 mt-0.5 mr-2 flex-shrink-0"></i>
                        <span class="text-gray-700 truncate">
                            {{ employee.phone || 'N/A' }}
                        </span>
                    </div>
                    <div class="flex items-start">
                        <i class="fas fa-map-marker-alt text-gray-400 w-4 mt-0.5 mr-2 flex-shrink-0"></i>
                        <span class="text-gray-700 truncate" :title="employee.location">
                            {{ employee.location }}
                        </span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Empty State -->
        <div v-else class="bg-white rounded-lg shadow-sm p-12 text-center">
            <i class="fas fa-users text-gray-300 text-6xl mb-4"></i>
            <h3 class="text-xl font-semibold text-gray-900 mb-2">No employees found</h3>
            <p class="text-gray-600 mb-4">
                Try adjusting your search criteria or filters.
            </p>
            <button
                type="button"
                @click="resetFilters"
                class="px-6 py-2 bg-brand-yellow text-gray-900 rounded-full text-sm font-medium hover:bg-yellow-400 transition-colors"
            >
                Reset Filters
            </button>
        </div>
    </AuthenticatedLayout>
</template>

<style scoped>
/* Ensure 2FA badge is positioned correctly */
.grid > div {
    position: relative;
}
</style>
