<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { useForm, usePage, router } from '@inertiajs/vue3';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import { ref, watch, computed } from 'vue';

const props = defineProps({
    employees: Array,
    emergencyContacts: Array,
});

const page = usePage();

const showForm = ref(false);
const editingContactId = ref(null);
const selectedEmployeeId = ref(null);
const confirmDeleteId = ref(null);
const searchQuery = ref('');
const filterEmployeeId = ref('');

// Philippine phone validation functions
const validatePhilippineMobile = (number) => {
    const mobileRegex = /^(\+63|0)9\d{9}$/;
    return mobileRegex.test(number.replace(/[-\s]/g, ''));
};

const validatePhilippineLandline = (number) => {
    const landlineRegex = /^(\+63|0)?\d{1,2}\s*[-(]?\d{4}[-)]?\s*\d{4}$/;
    return landlineRegex.test(number.replace(/[-\s()]/g, '').replace(/\s/g, ''));
};

const isNumeric = (value) => {
    if (!value) return true;
    return /^[\d\s+\-()]+$/.test(value);
};

const phoneErrors = ref({
    home_phone: '',
    mobile_phone: '',
    work_phone: '',
});

const form = useForm({
    employee_id: '',
    name: '',
    relationship: '',
    home_phone: '',
    mobile_phone: '',
    work_phone: '',
});

const displayToast = (message, type = 'info', duration = 4000) => {
    if (typeof window !== 'undefined' && window.showToast) {
        window.showToast(message, type, duration);
    }
};

// Watch for flash messages from Laravel
watch(() => page.props.flash, (flash) => {
    if (flash && flash.success && window.showToast) {
        window.showToast(flash.success, 'success');
    }
    if (flash && flash.error && window.showToast) {
        window.showToast(flash.error, 'error');
    }
}, { deep: true, immediate: true });

const validatePhoneNumbers = () => {
    phoneErrors.value = {
        home_phone: '',
        mobile_phone: '',
        work_phone: '',
    };

    let hasErrors = false;

    const hasAnyPhone = form.home_phone || form.mobile_phone || form.work_phone;
    if (!hasAnyPhone) {
        displayToast('At least one phone number must be provided', 'error');
        hasErrors = true;
        return false;
    }

    if (form.home_phone) {
        if (!isNumeric(form.home_phone)) {
            phoneErrors.value.home_phone = 'Home phone can only contain numbers, spaces, dashes, parentheses, and +';
            hasErrors = true;
        } else if (!validatePhilippineLandline(form.home_phone)) {
            phoneErrors.value.home_phone = 'Invalid landline format. Use format like (02) 1234-5678 or +63 2 1234 5678';
            hasErrors = true;
        }
    }

    if (form.mobile_phone) {
        if (!isNumeric(form.mobile_phone)) {
            phoneErrors.value.mobile_phone = 'Mobile phone can only contain numbers, spaces, dashes, parentheses, and +';
            hasErrors = true;
        } else if (!validatePhilippineMobile(form.mobile_phone)) {
            phoneErrors.value.mobile_phone = 'Invalid mobile format. Use +63-9XX-XXX-XXXX or 09XX-XXX-XXXX';
            hasErrors = true;
        }
    }

    if (form.work_phone) {
        if (!isNumeric(form.work_phone)) {
            phoneErrors.value.work_phone = 'Work phone can only contain numbers, spaces, dashes, parentheses, and +';
            hasErrors = true;
        } else if (!validatePhilippineMobile(form.work_phone)) {
            phoneErrors.value.work_phone = 'Invalid work phone format. Use +63-9XX-XXX-XXXX or 09XX-XXX-XXXX';
            hasErrors = true;
        }
    }

    if (hasErrors) {
        displayToast('Please fix the phone number errors', 'error');
    }

    return !hasErrors;
};

const openAddForm = (employeeId = null) => {
    form.reset();
    form.employee_id = employeeId || '';
    editingContactId.value = null;
    phoneErrors.value = { home_phone: '', mobile_phone: '', work_phone: '' };
    showForm.value = true;
};

const openEditForm = (contact) => {
    form.employee_id = contact.employee_id;
    form.name = contact.name;
    form.relationship = contact.relationship;
    form.home_phone = contact.home_phone || '';
    form.mobile_phone = contact.mobile_phone || '';
    form.work_phone = contact.work_phone || '';
    editingContactId.value = contact.id;
    phoneErrors.value = { home_phone: '', mobile_phone: '', work_phone: '' };
    showForm.value = true;
};

const closeForm = () => {
    form.reset();
    editingContactId.value = null;
    phoneErrors.value = { home_phone: '', mobile_phone: '', work_phone: '' };
    showForm.value = false;
};

const submitForm = () => {
    if (!form.employee_id) {
        displayToast('Please select an employee', 'error');
        return;
    }

    if (!validatePhoneNumbers()) {
        return;
    }

    if (editingContactId.value) {
        form.put(`/admin/emergency-contacts/${editingContactId.value}`, {
            preserveScroll: true,
            onSuccess: () => {
                closeForm();
                displayToast('Emergency contact updated successfully', 'success');
            },
            onError: () => {
                displayToast('Failed to update emergency contact', 'error');
            },
        });
    } else {
        form.post('/admin/emergency-contacts', {
            preserveScroll: true,
            onSuccess: () => {
                closeForm();
                displayToast('Emergency contact added successfully', 'success');
            },
            onError: () => {
                displayToast('Failed to add emergency contact', 'error');
            },
        });
    }
};

const confirmDelete = (id) => {
    confirmDeleteId.value = id;
};

const cancelDelete = () => {
    confirmDeleteId.value = null;
};

const deleteContact = (id) => {
    form.delete(`/admin/emergency-contacts/${id}`, {
        preserveScroll: true,
        onSuccess: () => {
            confirmDeleteId.value = null;
            displayToast('Emergency contact deleted successfully', 'success');
        },
        onError: () => {
            confirmDeleteId.value = null;
            displayToast('Failed to delete emergency contact', 'error');
        },
    });
};

// Filtered and searched contacts
const filteredContacts = computed(() => {
    let contacts = props.emergencyContacts;

    // Filter by employee
    if (filterEmployeeId.value) {
        contacts = contacts.filter(c => c.employee_id === filterEmployeeId.value);
    }

    // Search by name or relationship
    if (searchQuery.value) {
        const query = searchQuery.value.toLowerCase();
        contacts = contacts.filter(c => 
            c.name.toLowerCase().includes(query) ||
            c.relationship.toLowerCase().includes(query) ||
            c.employee?.first_name?.toLowerCase().includes(query) ||
            c.employee?.last_name?.toLowerCase().includes(query)
        );
    }

    return contacts;
});

// Employees without emergency contacts
const employeesWithoutContacts = computed(() => {
    const employeeIdsWithContacts = new Set(props.emergencyContacts.map(c => c.employee_id));
    return props.employees.filter(e => !employeeIdsWithContacts.has(e.id));
});

const getEmployeeName = (employeeId) => {
    const employee = props.employees.find(e => e.id === employeeId);
    return employee ? `${employee.first_name} ${employee.last_name}` : 'Unknown';
};
</script>

<template>
    <AuthenticatedLayout>
        <div class="mb-6">
            <h1 class="text-2xl font-bold text-gray-800">Emergency Contacts Management</h1>
            <p class="text-sm text-gray-600 mt-1">Manage emergency contacts for all employees</p>
        </div>

        <!-- Stats Cards -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
            <div class="bg-white rounded-card shadow-card p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-gray-600">Total Contacts</p>
                        <p class="text-3xl font-bold text-brand-dark mt-2">{{ emergencyContacts.length }}</p>
                    </div>
                    <div class="w-12 h-12 bg-blue-100 rounded-full flex items-center justify-center">
                        <i class="fas fa-address-book text-blue-600 text-xl"></i>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-card shadow-card p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-gray-600">Employees with Contacts</p>
                        <p class="text-3xl font-bold text-green-600 mt-2">{{ employees.length - employeesWithoutContacts.length }}</p>
                    </div>
                    <div class="w-12 h-12 bg-green-100 rounded-full flex items-center justify-center">
                        <i class="fas fa-check-circle text-green-600 text-xl"></i>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-card shadow-card p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-gray-600">Missing Contacts</p>
                        <p class="text-3xl font-bold text-red-600 mt-2">{{ employeesWithoutContacts.length }}</p>
                    </div>
                    <div class="w-12 h-12 bg-red-100 rounded-full flex items-center justify-center">
                        <i class="fas fa-exclamation-triangle text-red-600 text-xl"></i>
                    </div>
                </div>
            </div>
        </div>

        <!-- Employees Without Contacts Alert -->
        <div v-if="employeesWithoutContacts.length > 0" class="bg-yellow-50 border-l-4 border-yellow-400 p-4 mb-6 rounded">
            <div class="flex">
                <div class="flex-shrink-0">
                    <i class="fas fa-exclamation-triangle text-yellow-400"></i>
                </div>
                <div class="ml-3">
                    <p class="text-sm text-yellow-700">
                        <strong>{{ employeesWithoutContacts.length }} employee(s)</strong> don't have emergency contacts:
                        <span class="font-medium">
                            {{ employeesWithoutContacts.slice(0, 3).map(e => `${e.first_name} ${e.last_name}`).join(', ') }}
                            <span v-if="employeesWithoutContacts.length > 3">and {{ employeesWithoutContacts.length - 3 }} more...</span>
                        </span>
                    </p>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-card p-8 shadow-card">
            <!-- Header with Actions -->
            <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4 mb-6 border-b border-gray-100 pb-4">
                <h3 class="text-lg font-semibold text-gray-700">All Emergency Contacts</h3>
                <button
                    @click="openAddForm()"
                    class="px-4 py-2 bg-brand-yellow text-brand-dark font-medium rounded-lg hover:bg-yellow-300 transition-colors"
                >
                    + Add Emergency Contact
                </button>
            </div>

            <!-- Filters -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">
                <div>
                    <label class="block text-xs font-semibold text-gray-500 mb-2">Search by Name or Relationship</label>
                    <input
                        v-model="searchQuery"
                        type="text"
                        placeholder="Search contacts..."
                        class="w-full px-4 py-2.5 border border-gray-200 rounded-lg focus:ring-2 focus:ring-brand-yellow focus:border-transparent transition-all text-sm"
                    />
                </div>
                <div>
                    <label class="block text-xs font-semibold text-gray-500 mb-2">Filter by Employee</label>
                    <select
                        v-model="filterEmployeeId"
                        class="w-full px-4 py-2.5 border border-gray-200 rounded-lg focus:ring-2 focus:ring-brand-yellow focus:border-transparent transition-all text-sm"
                    >
                        <option value="">All Employees</option>
                        <option v-for="emp in employees" :key="emp.id" :value="emp.id">
                            {{ emp.first_name }} {{ emp.last_name }}
                        </option>
                    </select>
                </div>
            </div>

            <!-- Form Section -->
            <div v-if="showForm" class="mb-8 p-6 bg-gray-50 rounded-lg border-2 border-brand-yellow">
                <h4 class="text-md font-semibold text-gray-700 mb-4">
                    {{ editingContactId ? 'Edit Emergency Contact' : 'Add Emergency Contact' }}
                </h4>

                <form @submit.prevent="submitForm">
                    <!-- Employee Selection -->
                    <div class="mb-6">
                        <label class="block text-xs font-semibold text-gray-500 mb-2">Employee*</label>
                        <select
                            v-model="form.employee_id"
                            :disabled="editingContactId !== null"
                            class="w-full px-4 py-2.5 border border-gray-200 rounded-lg focus:ring-2 focus:ring-brand-yellow focus:border-transparent transition-all text-sm"
                            :class="{ 'bg-gray-100': editingContactId !== null }"
                            required
                        >
                            <option value="">-- Select Employee --</option>
                            <option v-for="emp in employees" :key="emp.id" :value="emp.id">
                                {{ emp.first_name }} {{ emp.last_name }}
                            </option>
                        </select>
                    </div>

                    <!-- Name and Relationship -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                        <TextInput v-model="form.name" placeholder="Full Name" :error="form.errors.name" required>
                            <template #label>Name*</template>
                        </TextInput>
                        <TextInput v-model="form.relationship" placeholder="e.g., Spouse, Parent, Sibling" :error="form.errors.relationship" required>
                            <template #label>Relationship*</template>
                        </TextInput>
                    </div>

                    <!-- Telephone Section -->
                    <h5 class="text-sm font-medium text-gray-600 mb-4">Contact Numbers (At least one required)</h5>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
                        <div>
                            <TextInput v-model="form.home_phone" placeholder="(02) 1234-5678 or +63 2 1234 5678" :error="phoneErrors.home_phone || form.errors.home_phone">
                                <template #label>Home Phone (Landline)</template>
                            </TextInput>
                            <p v-if="phoneErrors.home_phone" class="mt-2 text-xs text-red-600">{{ phoneErrors.home_phone }}</p>
                        </div>
                        <div>
                            <TextInput v-model="form.mobile_phone" placeholder="+63-9XX-XXX-XXXX or 09XX-XXX-XXXX" :error="phoneErrors.mobile_phone || form.errors.mobile_phone">
                                <template #label>Mobile Phone</template>
                            </TextInput>
                            <p v-if="phoneErrors.mobile_phone" class="mt-2 text-xs text-red-600">{{ phoneErrors.mobile_phone }}</p>
                        </div>
                        <div>
                            <TextInput v-model="form.work_phone" placeholder="+63-9XX-XXX-XXXX or 09XX-XXX-XXXX" :error="phoneErrors.work_phone || form.errors.work_phone">
                                <template #label>Work Phone</template>
                            </TextInput>
                            <p v-if="phoneErrors.work_phone" class="mt-2 text-xs text-red-600">{{ phoneErrors.work_phone }}</p>
                        </div>
                    </div>

                    <div v-if="form.errors.phones" class="mb-4 p-3 bg-red-50 border border-red-200 rounded text-sm text-red-600">
                        {{ form.errors.phones }}
                    </div>

                    <div class="flex justify-end gap-4 pt-4 border-t border-gray-200">
                        <button
                            @click="closeForm"
                            type="button"
                            class="px-6 py-2 border border-gray-300 text-gray-700 font-medium rounded-lg hover:bg-gray-50 transition-colors"
                        >
                            Cancel
                        </button>
                        <PrimaryButton :processing="form.processing">
                            {{ editingContactId ? 'Update' : 'Save' }}
                        </PrimaryButton>
                    </div>
                </form>
            </div>

            <!-- Contacts List Section -->
            <div class="overflow-x-auto">
                <div v-if="filteredContacts.length === 0 && !showForm" class="text-center py-12">
                    <i class="fas fa-address-book text-gray-300 text-6xl mb-4"></i>
                    <p class="text-gray-500 text-sm">No emergency contacts found.</p>
                    <p class="text-gray-400 text-xs mt-2">Click "Add Emergency Contact" to get started.</p>
                </div>

                <table v-else-if="filteredContacts.length > 0" class="w-full">
                    <thead>
                        <tr class="border-b-2 border-gray-200">
                            <th class="px-6 py-3 text-left text-sm font-semibold text-gray-700">Employee</th>
                            <th class="px-6 py-3 text-left text-sm font-semibold text-gray-700">Contact Name</th>
                            <th class="px-6 py-3 text-left text-sm font-semibold text-gray-700">Relationship</th>
                            <th class="px-6 py-3 text-left text-sm font-semibold text-gray-700">Home Phone</th>
                            <th class="px-6 py-3 text-left text-sm font-semibold text-gray-700">Mobile Phone</th>
                            <th class="px-6 py-3 text-left text-sm font-semibold text-gray-700">Work Phone</th>
                            <th class="px-6 py-3 text-center text-sm font-semibold text-gray-700">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="contact in filteredContacts" :key="contact.id" class="border-b border-gray-100 hover:bg-gray-50 transition-colors">
                            <td class="px-6 py-4 text-sm font-medium text-gray-900">
                                {{ contact.employee ? `${contact.employee.first_name} ${contact.employee.last_name}` : getEmployeeName(contact.employee_id) }}
                            </td>
                            <td class="px-6 py-4 text-sm text-gray-900">{{ contact.name }}</td>
                            <td class="px-6 py-4 text-sm text-gray-700">{{ contact.relationship }}</td>
                            <td class="px-6 py-4 text-sm text-gray-600">{{ contact.home_phone || '-' }}</td>
                            <td class="px-6 py-4 text-sm text-gray-600">{{ contact.mobile_phone || '-' }}</td>
                            <td class="px-6 py-4 text-sm text-gray-600">{{ contact.work_phone || '-' }}</td>
                            <td class="px-6 py-4 text-center">
                                <div class="flex justify-center gap-2">
                                    <button
                                        @click="openEditForm(contact)"
                                        class="px-3 py-1 bg-blue-100 text-blue-700 text-xs font-medium rounded hover:bg-blue-200 transition-colors"
                                    >
                                        Edit
                                    </button>
                                    <button
                                        @click="confirmDelete(contact.id)"
                                        class="px-3 py-1 bg-red-100 text-red-700 text-xs font-medium rounded hover:bg-red-200 transition-colors"
                                    >
                                        Delete
                                    </button>
                                </div>

                                <!-- Delete Confirmation Modal -->
                                <div v-if="confirmDeleteId === contact.id" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
                                    <div class="bg-white rounded-lg p-6 max-w-sm mx-4">
                                        <h4 class="text-lg font-semibold text-gray-900 mb-2">Delete Contact</h4>
                                        <p class="text-gray-600 mb-4">Are you sure you want to delete <strong>{{ contact.name }}</strong>?</p>
                                        <div class="flex justify-end gap-3">
                                            <button
                                                @click="cancelDelete"
                                                class="px-4 py-2 border border-gray-300 text-gray-700 font-medium rounded-lg hover:bg-gray-50 transition-colors"
                                            >
                                                Cancel
                                            </button>
                                            <button
                                                @click="deleteContact(contact.id)"
                                                class="px-4 py-2 bg-red-600 text-white font-medium rounded-lg hover:bg-red-700 transition-colors"
                                            >
                                                Delete
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

<style scoped>
/* Component-specific styles */
</style>
