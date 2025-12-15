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
    other_id: props.employee.other_id || '',
    drivers_license_number: props.employee.drivers_license_number || '',
    license_expiry_date: props.employee.license_expiry_date || '',
    date_of_birth: props.employee.date_of_birth || '',
    gender: props.employee.gender || '',
    marital_status: props.employee.marital_status || '',
});

const submit = () => {
    form.post('/my-info/personal', {
        preserveScroll: true,
        onSuccess: () => {
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
                    <label class="block text-xs font-semibold text-gray-500 mb-2">Employee Full Name*</label>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <TextInput v-model="form.first_name" placeholder="First Name" :error="form.errors.first_name" />
                        <TextInput v-model="form.middle_name" placeholder="Middle Name" :error="form.errors.middle_name" />
                        <TextInput v-model="form.last_name" placeholder="Last Name" :error="form.errors.last_name" />
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-2">
                    <TextInput :model-value="employee.id" label="Employee Id" :disabled="true">
                        <template #label>Employee Id</template>
                    </TextInput>
                    <TextInput v-model="form.other_id" placeholder="Other Id" :error="form.errors.other_id">
                        <template #label>Other Id</template>
                    </TextInput>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-2">
                    <TextInput v-model="form.drivers_license_number" placeholder="Driver's License Number" :error="form.errors.drivers_license_number">
                        <template #label>Driver's License Number</template>
                    </TextInput>
                    <TextInput v-model="form.license_expiry_date" type="date" :error="form.errors.license_expiry_date">
                         <template #label>License Expiry Date</template>
                    </TextInput>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-2">
                     <div class="mb-5">
                        <label class="block text-xs text-brand-grey font-medium mb-2 ml-1">Nationality</label>
                        <select class="w-full p-3 pl-4 text-sm border border-gray-200 rounded-md focus:outline-none focus:border-brand-yellow bg-gray-100 text-gray-400" disabled>
                            <option value="">-- Select --</option>
                        </select>
                    </div>
                    <div class="mb-5">
                        <label class="block text-xs text-brand-grey font-medium mb-2 ml-1">Marital Status</label>
                        <select v-model="form.marital_status" class="w-full p-3 pl-4 text-sm border border-gray-200 rounded-md focus:outline-none focus:border-brand-yellow bg-white text-gray-600">
                            <option value="">-- Select --</option>
                            <option value="Single">Single</option>
                            <option value="Married">Married</option>
                            <option value="Divorced">Divorced</option>
                            <option value="Widowed">Widowed</option>
                        </select>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8">
                    <TextInput v-model="form.date_of_birth" type="date" :error="form.errors.date_of_birth">
                        <template #label>Date of Birth</template>
                    </TextInput>

                    <div class="mb-5">
                        <label class="block text-xs text-brand-grey font-medium mb-2 ml-1">Gender</label>
                        <div class="flex gap-4 mt-3">
                            <label class="flex items-center gap-2 text-sm text-gray-600 cursor-pointer">
                                <input type="radio" value="Male" v-model="form.gender" class="accent-brand-yellow"> Male
                            </label>
                            <label class="flex items-center gap-2 text-sm text-gray-600 cursor-pointer">
                                <input type="radio" value="Female" v-model="form.gender" class="accent-brand-yellow"> Female
                            </label>
                        </div>
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