<script setup>
import MyInfoLayout from '@/Layouts/MyInfoLayout.vue';
import { useForm, usePage } from '@inertiajs/vue3';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import Toast from '@/Components/Toast.vue';
import { ref, watch, computed } from 'vue';

const props = defineProps({
    employee: Object,
    emergencyContacts: Array,
});

const page = usePage();

const showForm = ref(false);
const editingContactId = ref(null);
const confirmDeleteId = ref(null);
const toastMessage = ref('');
const toastType = ref('info');
const showToast = ref(false);

// Philippine phone validation functions
const validatePhilippineMobile = (number) => {
    // Accept formats: +63xxxxxxxxxx or 09xxxxxxxxxx
    const mobileRegex = /^(\+63|0)9\d{9}$/;
    return mobileRegex.test(number.replace(/[-\s]/g, ''));
};

const validatePhilippineLandline = (number) => {
    // Accept formats: (02) xxxx-xxxx or +63 2 xxxx xxxx or area code patterns
    const landlineRegex = /^(\+63|0)?\d{1,2}\s*[-(]?\d{4}[-)]?\s*\d{4}$/;
    return landlineRegex.test(number.replace(/[-\s()]/g, '').replace(/\s/g, ''));
};

const isNumeric = (value) => {
    if (!value) return true; // empty is okay for nullable fields
    return /^[\d\s+\-()]+$/.test(value);
};

const phoneErrors = ref({
    home_phone: '',
    mobile_phone: '',
    work_phone: '',
});

const form = useForm({
    name: '',
    relationship: '',
    home_phone: '',
    mobile_phone: '',
    work_phone: '',
});

const displayToast = (message, type = 'info', duration = 4000) => {
    toastMessage.value = message;
    toastType.value = type;
    showToast.value = true;
    if (duration > 0) {
        setTimeout(() => {
            showToast.value = false;
        }, duration);
    }
};

// Watch for flash messages from Laravel
watch(() => page.props.flash, (flash) => {
    if (flash && flash.success) {
        displayToast(flash.success, 'success', 4000);
    }
}, { deep: true, immediate: true });

const validatePhoneNumbers = () => {
    phoneErrors.value = {
        home_phone: '',
        mobile_phone: '',
        work_phone: '',
    };

    let hasErrors = false;

    // Check if at least one phone number is provided
    const hasAnyPhone = form.home_phone || form.mobile_phone || form.work_phone;
    if (!hasAnyPhone) {
        displayToast('At least one phone number must be provided', 'error');
        hasErrors = true;
        return false;
    }

    // Validate home phone if provided
    if (form.home_phone) {
        if (!isNumeric(form.home_phone)) {
            phoneErrors.value.home_phone = 'Home phone can only contain numbers, spaces, dashes, parentheses, and +';
            hasErrors = true;
        } else if (!validatePhilippineLandline(form.home_phone)) {
            phoneErrors.value.home_phone = 'Invalid landline format. Use format like (02) 1234-5678 or +63 2 1234 5678';
            hasErrors = true;
        }
    }

    // Validate mobile phone if provided
    if (form.mobile_phone) {
        if (!isNumeric(form.mobile_phone)) {
            phoneErrors.value.mobile_phone = 'Mobile phone can only contain numbers, spaces, dashes, parentheses, and +';
            hasErrors = true;
        } else if (!validatePhilippineMobile(form.mobile_phone)) {
            phoneErrors.value.mobile_phone = 'Invalid mobile format. Use +63-9XX-XXX-XXXX or 09XX-XXX-XXXX';
            hasErrors = true;
        }
    }

    // Validate work phone if provided
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

const openAddForm = () => {
    form.reset();
    editingContactId.value = null;
    phoneErrors.value = { home_phone: '', mobile_phone: '', work_phone: '' };
    showForm.value = true;
};

const openEditForm = (contact) => {
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
    // Validate phone numbers first
    if (!validatePhoneNumbers()) {
        return;
    }

    if (editingContactId.value) {
        form.put(`/my-info/emergency-contacts/${editingContactId.value}`, {
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
        form.post('/my-info/emergency-contacts', {
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
    form.delete(`/my-info/emergency-contacts/${id}`, {
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
</script>

<template>
    <MyInfoLayout>
        <!-- Toast Notification -->
        <Toast
            :message="toastMessage"
            :type="toastType"
            :show="showToast"
            @close="() => (showToast = false)"
        />

        <div class="bg-white rounded-card p-8 shadow-card">
            <div class="flex justify-between items-center mb-6 border-b border-gray-100 pb-4">
                <h3 class="text-lg font-semibold text-gray-700">Emergency Contacts</h3>
                <button
                    @click="openAddForm"
                    class="px-4 py-2 bg-brand-yellow text-brand-dark font-medium rounded-lg hover:bg-yellow-300 transition-colors"
                >
                    + Add Emergency Contact
                </button>
            </div>

            <!-- Form Section -->
            <div v-if="showForm" class="mb-8 p-6 bg-gray-50 rounded-lg border-2 border-brand-yellow">
                <h4 class="text-md font-semibold text-gray-700 mb-4">
                    {{ editingContactId ? 'Edit Emergency Contact' : 'Add Emergency Contact' }}
                </h4>

                <form @submit.prevent="submitForm">
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
                <div v-if="emergencyContacts.length === 0 && !showForm" class="text-center py-12">
                    <p class="text-gray-500 text-sm">No emergency contacts added yet.</p>
                    <p class="text-gray-400 text-xs mt-2">Click "Add Emergency Contact" to get started.</p>
                </div>

                <table v-else-if="emergencyContacts.length > 0" class="w-full">
                    <thead>
                        <tr class="border-b-2 border-gray-200">
                            <th class="px-6 py-3 text-left text-sm font-semibold text-gray-700">Name</th>
                            <th class="px-6 py-3 text-left text-sm font-semibold text-gray-700">Relationship</th>
                            <th class="px-6 py-3 text-left text-sm font-semibold text-gray-700">Home Phone</th>
                            <th class="px-6 py-3 text-left text-sm font-semibold text-gray-700">Mobile Phone</th>
                            <th class="px-6 py-3 text-left text-sm font-semibold text-gray-700">Work Phone</th>
                            <th class="px-6 py-3 text-center text-sm font-semibold text-gray-700">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="contact in emergencyContacts" :key="contact.id" class="border-b border-gray-100 hover:bg-gray-50 transition-colors">
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
    </MyInfoLayout>
</template>

<style scoped>
/* Add any component-specific styles here */
</style>
