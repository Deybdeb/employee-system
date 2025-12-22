<script setup>
import MyInfoLayout from '@/Layouts/MyInfoLayout.vue';
import { useForm } from '@inertiajs/vue3';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';

const props = defineProps({
    employee: Object,
});

const address = props.employee.addresses && props.employee.addresses.length > 0 
    ? props.employee.addresses[0] 
    : {};

const form = useForm({
    personal_email: props.employee.personal_email || props.employee.email || '',
    work_email: props.employee.work_email || '',
    mobile_phone: props.employee.mobile_phone || '',
    home_phone: props.employee.home_phone || '',
    work_phone: props.employee.work_phone || '',
    street_1: address.street_1 || '',
    street_2: address.street_2 || '',
    city: address.city || '',
    state_province: address.state_province || '',
    zip_postal_code: address.zip_postal_code || address.postal_code || '',
    country: address.country || '',
});

const submit = () => {
    form.post('/my-info/contact', {
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
            <h3 class="text-lg font-semibold text-gray-700 mb-6 border-b border-gray-100 pb-4">Contact Details</h3>

            <form @submit.prevent="submit">
                <!-- Email Section -->
                <h4 class="text-md font-medium text-gray-600 mb-4">Email</h4>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                    <TextInput v-model="form.personal_email" placeholder="Personal Email" :error="form.errors.personal_email" required>
                        <template #label>Personal Email*</template>
                    </TextInput>
                    <TextInput v-model="form.work_email" placeholder="Work Email" :error="form.errors.work_email">
                        <template #label>Work Email</template>
                    </TextInput>
                </div>

                <!-- Telephone Section -->
                <h4 class="text-md font-medium text-gray-600 mb-4 mt-8">Telephone</h4>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
                    <TextInput v-model="form.home_phone" placeholder="Home Phone" :error="form.errors.home_phone">
                        <template #label>Home</template>
                    </TextInput>
                    <TextInput v-model="form.mobile_phone" placeholder="Mobile Phone" :error="form.errors.mobile_phone">
                        <template #label>Mobile</template>
                    </TextInput>
                    <TextInput v-model="form.work_phone" placeholder="Work Phone" :error="form.errors.work_phone">
                        <template #label>Work</template>
                    </TextInput>
                </div>

                <!-- Address Section -->
                <h4 class="text-md font-medium text-gray-600 mb-4 mt-8">Address</h4>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
                    <TextInput v-model="form.street_1" placeholder="Street 1" :error="form.errors.street_1">
                        <template #label>Street 1</template>
                    </TextInput>
                    <TextInput v-model="form.street_2" placeholder="Street 2" :error="form.errors.street_2">
                        <template #label>Street 2</template>
                    </TextInput>
                    <TextInput v-model="form.city" placeholder="City" :error="form.errors.city">
                        <template #label>City</template>
                    </TextInput>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
                    <TextInput v-model="form.state_province" placeholder="State/Province" :error="form.errors.state_province">
                        <template #label>State/Province</template>
                    </TextInput>
                    <TextInput v-model="form.zip_postal_code" placeholder="Zip/Postal Code" :error="form.errors.zip_postal_code">
                        <template #label>Zip/Postal Code</template>
                    </TextInput>
                    <div>
                        <label class="block text-xs font-semibold text-gray-500 mb-2">Country</label>
                        <select 
                            v-model="form.country"
                            class="w-full px-4 py-2.5 border border-gray-200 rounded-lg focus:ring-2 focus:ring-brand-yellow focus:border-transparent transition-all text-sm"
                        >
                            <option value="">-- Select --</option>
                            <option value="Philippines">Philippines</option>
                            <option value="United States">United States</option>
                            <option value="China">China</option>
                            <option value="Japan">Japan</option>
                            <option value="Singapore">Singapore</option>
                            <option value="Malaysia">Malaysia</option>
                            <option value="Other">Other</option>
                        </select>
                    </div>
                </div>

                <div class="flex justify-between items-center pt-6 border-t border-gray-100 mt-8">
                    <p class="text-xs text-red-500">* Required</p>
                    <div class="w-32">
                        <PrimaryButton :processing="form.processing">Save</PrimaryButton>
                    </div>
                </div>
            </form>
        </div>
    </MyInfoLayout>
</template>