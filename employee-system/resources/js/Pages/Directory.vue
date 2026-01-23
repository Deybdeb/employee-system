<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { ref, computed } from 'vue';
import { router, useForm, usePage } from '@inertiajs/vue3';

const page = usePage();

const props = defineProps({
    employees: Array,
    filters: Object,
    total: Number,
});

// Check if user is admin
const isAdmin = computed(() => page.props.auth?.user?.is_admin);

// Form for search and filters
const form = useForm({
    search: props.filters.search || '',
    job_title: props.filters.job_title || '',
    location: props.filters.location || '',
});

// Create employee modal state
const showCreateModal = ref(false);
const isCreating = ref(false);
const createErrors = ref({});

// Create employee form
const createForm = ref({
    first_name: '',
    middle_name: '',
    last_name: '',
    personal_email: '',
    password: '',
    password_confirmation: '',
    work_email: '',
    mobile_phone: '',
    gender: '',
});

// Toast helper
const showToast = (message, type = 'info', title = '') => {
    if (typeof window !== 'undefined' && window.showToast) {
        window.showToast(message, type, 4000, title);
    }
};

// Open create modal
const openCreateModal = () => {
    createForm.value = {
        first_name: '',
        middle_name: '',
        last_name: '',
        personal_email: '',
        password: '',
        password_confirmation: '',
        work_email: '',
        mobile_phone: '',
        gender: '',
    };
    createErrors.value = {};
    showCreateModal.value = true;
};

// Close create modal
const closeCreateModal = () => {
    showCreateModal.value = false;
    createErrors.value = {};
};

// Submit create employee form
const submitCreateEmployee = async () => {
    isCreating.value = true;
    createErrors.value = {};

    try {
        // Get CSRF token from meta tag or cookie
        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') 
                        || document.cookie.match(/XSRF-TOKEN=([^;]+)/)?.[1];

        const response = await fetch('/admin/employees', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': csrfToken,
                'X-Requested-With': 'XMLHttpRequest',
            },
            body: JSON.stringify(createForm.value),
        });

        const data = await response.json();

        if (response.ok) {
            showToast(data.message || 'Employee created successfully', 'success', 'Employee Created');
            closeCreateModal();
            // Refresh the page to show new employee
            router.reload();
        } else if (response.status === 422) {
            createErrors.value = data.errors || {};
            showToast('Please fix the validation errors', 'error', 'Validation Error');
        } else {
            showToast(data.error || 'Failed to create employee', 'error', 'Error');
        }
    } catch (error) {
        console.error('Error creating employee:', error);
        showToast('An error occurred. Please try again.', 'error', 'Error');
    } finally {
        isCreating.value = false;
    }
};

// Apply filters
const applyFilters = () => {
    form.get('/directory', {
        preserveState: true,
        preserveScroll: true,
    });
};

// Reset filters
const resetFilters = () => {
    form.reset();
    form.get('/directory', {
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

// Navigate to edit employee (admin only)
const editEmployee = (id) => {
    router.visit(`/admin/employees/${id}`);
};
</script>

<template>
    <AuthenticatedLayout>
        <!-- Create Employee Modal -->
        <div v-if="showCreateModal" class="fixed inset-0 z-50 flex items-center justify-center">
            <div class="fixed inset-0 bg-gray-900/40 backdrop-blur-sm" @click="closeCreateModal"></div>
            <div class="relative bg-white rounded-xl shadow-2xl p-6 max-w-2xl w-full mx-4 z-10 border border-gray-100 max-h-[90vh] overflow-y-auto">
                <div class="flex items-center justify-between mb-6">
                    <h3 class="text-xl font-semibold text-gray-900">Create New Employee</h3>
                    <button @click="closeCreateModal" class="text-gray-400 hover:text-gray-600 transition-colors">
                        <i class="fas fa-times text-xl"></i>
                    </button>
                </div>

                <form @submit.prevent="submitCreateEmployee">
                    <!-- Name Section -->
                    <div class="mb-6">
                        <h4 class="text-sm font-semibold text-gray-600 mb-3">Name</h4>
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                            <div>
                                <label class="block text-xs font-medium text-gray-700 mb-1">First Name *</label>
                                <input
                                    v-model="createForm.first_name"
                                    type="text"
                                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-brand-yellow focus:border-transparent text-sm"
                                    :class="{ 'border-red-500': createErrors.first_name }"
                                    placeholder="John"
                                />
                                <p v-if="createErrors.first_name" class="mt-1 text-xs text-red-500">{{ createErrors.first_name[0] }}</p>
                            </div>
                            <div>
                                <label class="block text-xs font-medium text-gray-700 mb-1">Middle Name</label>
                                <input
                                    v-model="createForm.middle_name"
                                    type="text"
                                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-brand-yellow focus:border-transparent text-sm"
                                    placeholder="Middle"
                                />
                            </div>
                            <div>
                                <label class="block text-xs font-medium text-gray-700 mb-1">Last Name *</label>
                                <input
                                    v-model="createForm.last_name"
                                    type="text"
                                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-brand-yellow focus:border-transparent text-sm"
                                    :class="{ 'border-red-500': createErrors.last_name }"
                                    placeholder="Doe"
                                />
                                <p v-if="createErrors.last_name" class="mt-1 text-xs text-red-500">{{ createErrors.last_name[0] }}</p>
                            </div>
                        </div>
                    </div>

                    <!-- Account Section -->
                    <div class="mb-6">
                        <h4 class="text-sm font-semibold text-gray-600 mb-3">Account Credentials</h4>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div class="md:col-span-2">
                                <label class="block text-xs font-medium text-gray-700 mb-1">Personal Email (Login) *</label>
                                <input
                                    v-model="createForm.personal_email"
                                    type="email"
                                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-brand-yellow focus:border-transparent text-sm"
                                    :class="{ 'border-red-500': createErrors.personal_email }"
                                    placeholder="john.doe@email.com"
                                />
                                <p v-if="createErrors.personal_email" class="mt-1 text-xs text-red-500">{{ createErrors.personal_email[0] }}</p>
                            </div>
                            <div>
                                <label class="block text-xs font-medium text-gray-700 mb-1">Password *</label>
                                <input
                                    v-model="createForm.password"
                                    type="password"
                                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-brand-yellow focus:border-transparent text-sm"
                                    :class="{ 'border-red-500': createErrors.password }"
                                    placeholder="Minimum 8 characters"
                                />
                                <p v-if="createErrors.password" class="mt-1 text-xs text-red-500">{{ createErrors.password[0] }}</p>
                            </div>
                            <div>
                                <label class="block text-xs font-medium text-gray-700 mb-1">Confirm Password *</label>
                                <input
                                    v-model="createForm.password_confirmation"
                                    type="password"
                                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-brand-yellow focus:border-transparent text-sm"
                                    placeholder="Confirm password"
                                />
                            </div>
                        </div>
                    </div>

                    <!-- Optional Info Section -->
                    <div class="mb-6">
                        <h4 class="text-sm font-semibold text-gray-600 mb-3">Additional Information (Optional)</h4>
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                            <div>
                                <label class="block text-xs font-medium text-gray-700 mb-1">Work Email</label>
                                <input
                                    v-model="createForm.work_email"
                                    type="email"
                                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-brand-yellow focus:border-transparent text-sm"
                                    placeholder="john.doe@company.com"
                                />
                            </div>
                            <div>
                                <label class="block text-xs font-medium text-gray-700 mb-1">Mobile Phone</label>
                                <input
                                    v-model="createForm.mobile_phone"
                                    type="text"
                                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-brand-yellow focus:border-transparent text-sm"
                                    placeholder="+63 9XX XXX XXXX"
                                />
                            </div>
                            <div>
                                <label class="block text-xs font-medium text-gray-700 mb-1">Gender</label>
                                <select
                                    v-model="createForm.gender"
                                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-brand-yellow focus:border-transparent text-sm bg-white"
                                >
                                    <option value="">-- Select --</option>
                                    <option value="Male">Male</option>
                                    <option value="Female">Female</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <!-- Actions -->
                    <div class="flex justify-end gap-3 pt-4 border-t border-gray-100">
                        <button
                            type="button"
                            @click="closeCreateModal"
                            class="px-4 py-2 border border-gray-300 rounded-lg text-sm font-medium text-gray-700 hover:bg-gray-50 transition-colors"
                        >
                            Cancel
                        </button>
                        <button
                            type="submit"
                            :disabled="isCreating"
                            class="px-4 py-2 bg-brand-yellow text-gray-900 rounded-lg text-sm font-medium hover:bg-yellow-400 transition-colors disabled:opacity-50 flex items-center gap-2"
                        >
                            <i v-if="isCreating" class="fas fa-spinner fa-spin"></i>
                            <span>{{ isCreating ? 'Creating...' : 'Create Employee' }}</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Page Header -->
        <div class="mb-6">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-bold text-gray-900 mb-2">Directory</h1>
                    <p class="text-sm text-gray-600">
                        Company-wide contact list of employees with job titles and departments.
                    </p>
                </div>
                <!-- Admin: Create Employee Button -->
                <button
                    v-if="isAdmin"
                    @click="openCreateModal"
                    class="px-4 py-2 bg-brand-yellow text-gray-900 rounded-lg text-sm font-medium hover:bg-yellow-400 transition-colors flex items-center gap-2"
                >
                    <i class="fas fa-plus"></i>
                    Create Employee
                </button>
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
                        placeholder="Type for hints..."
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

                <!-- Admin Edit Button -->
                <div v-if="isAdmin" class="mt-4 pt-3 border-t border-gray-100">
                    <button
                        @click="editEmployee(employee.id)"
                        class="w-full px-4 py-2 bg-brand-yellow text-gray-900 rounded-lg text-sm font-medium hover:bg-yellow-400 transition-colors flex items-center justify-center gap-2"
                    >
                        <i class="fas fa-edit"></i>
                        Edit Employee
                    </button>
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
/* Custom scrollbar styling if needed */
</style>
