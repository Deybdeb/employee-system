<script setup>
import MyInfoLayout from '@/Layouts/MyInfoLayout.vue';
import { useForm } from '@inertiajs/vue3';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';

const props = defineProps({
    employee: Object,
});

const form = useForm({
    first_name: props.employee.first_name || '',
    middle_name: props.employee.middle_name || '',
    last_name: props.employee.last_name || '',
    date_of_birth: props.employee.date_of_birth || '',
    gender: props.employee.gender || '',
    marital_status: props.employee.marital_status || '',
    nationality_id: props.employee.nationality_id || '',
    other_id: props.employee.other_id || '',
    drivers_license_number: props.employee.drivers_license_number || '',
    license_expiry_date: props.employee.license_expiry_date || '',
});

const submit = () => {
    form.post('/my-info/personal', {
        preserveScroll: true,
        onSuccess: () => {
            // Optional: show success message
        },
    });
};
</script>

<template>
    <MyInfoLayout>
        <div class="bg-white rounded-card p-8 shadow-card">
            <h3 class="text-lg font-semibold text-gray-700 mb-6 border-b border-gray-100 pb-4">Personal Details</h3>

            <form @submit.prevent="submit">
                <div class="mb-6">
                    <label class="block text-xs font-semibold text-gray-500 mb-2">Full Name*</label>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <TextInput v-model="form.first_name" placeholder="First Name" :error="form.errors.first_name" required />
                        <TextInput v-model="form.middle_name" placeholder="Middle Name" :error="form.errors.middle_name" />
                        <TextInput v-model="form.last_name" placeholder="Last Name" :error="form.errors.last_name" required />
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                    <div>
                        <label class="block text-xs font-semibold text-gray-500 mb-2">Employee ID</label>
                        <TextInput v-model="form.other_id" placeholder="Employee ID" :error="form.errors.other_id" />
                    </div>
                    
                    <div>
                        <label class="block text-xs font-semibold text-gray-500 mb-2">Driver's License Number</label>
                        <TextInput v-model="form.drivers_license_number" placeholder="License Number" :error="form.errors.drivers_license_number" />
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                    <div>
                        <label class="block text-xs font-semibold text-gray-500 mb-2">License Expiry Date</label>
                        <input 
                            v-model="form.license_expiry_date" 
                            type="date"
                            class="w-full px-4 py-2.5 border border-gray-200 rounded-lg focus:ring-2 focus:ring-brand-yellow focus:border-transparent transition-all text-sm"
                            :class="form.errors.license_expiry_date ? 'border-red-500' : ''"
                        />
                        <div v-if="form.errors.license_expiry_date" class="text-red-600 text-xs mt-1">{{ form.errors.license_expiry_date }}</div>
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-gray-500 mb-2">Nationality</label>
                        <select 
                            v-model="form.nationality_id"
                            class="w-full px-4 py-2.5 border border-gray-200 rounded-lg focus:ring-2 focus:ring-brand-yellow focus:border-transparent transition-all text-sm"
                        >
                            <option value="">-- Select --</option>
                            <option value="1">Filipino</option>
                            <option value="2">American</option>
                            <option value="3">Chinese</option>
                            <option value="4">Japanese</option>
                            <option value="5">Other</option>
                        </select>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                    <div>
                        <label class="block text-xs font-semibold text-gray-500 mb-2">Marital Status</label>
                        <select 
                            v-model="form.marital_status"
                            class="w-full px-4 py-2.5 border border-gray-200 rounded-lg focus:ring-2 focus:ring-brand-yellow focus:border-transparent transition-all text-sm"
                        >
                            <option value="">-- Select --</option>
                            <option value="Single">Single</option>
                            <option value="Married">Married</option>
                            <option value="Divorced">Divorced</option>
                            <option value="Widowed">Widowed</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-gray-500 mb-2">Date of Birth</label>
                        <input 
                            v-model="form.date_of_birth" 
                            type="date"
                            class="w-full px-4 py-2.5 border border-gray-200 rounded-lg focus:ring-2 focus:ring-brand-yellow focus:border-transparent transition-all text-sm"
                            :class="form.errors.date_of_birth ? 'border-red-500' : ''"
                        />
                        <div v-if="form.errors.date_of_birth" class="text-red-600 text-xs mt-1">{{ form.errors.date_of_birth }}</div>
                    </div>
                </div>

                <div class="mb-6">
                    <label class="block text-xs font-semibold text-gray-500 mb-2">Gender</label>
                    <div class="flex gap-6">
                        <label class="flex items-center cursor-pointer">
                            <input 
                                v-model="form.gender" 
                                type="radio" 
                                value="Male"
                                class="mr-2 text-brand-yellow focus:ring-brand-yellow"
                            />
                            <span class="text-sm text-gray-700">Male</span>
                        </label>
                        <label class="flex items-center cursor-pointer">
                            <input 
                                v-model="form.gender" 
                                type="radio" 
                                value="Female"
                                class="mr-2 text-brand-yellow focus:ring-brand-yellow"
                            />
                            <span class="text-sm text-gray-700">Female</span>
                        </label>
                    </div>
                </div>

                <div class="flex justify-between items-center pt-4 border-t border-gray-50">
                    <p class="text-xs text-red-500">* Required</p>
                    <div class="w-32">
                        <PrimaryButton :processing="form.processing">Save</PrimaryButton>
                    </div>
                </div>
            </form>
        </div>
    </MyInfoLayout>
</template>