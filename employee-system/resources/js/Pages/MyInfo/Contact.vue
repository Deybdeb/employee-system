<script setup>
import MyInfoLayout from '@/Layouts/MyInfoLayout.vue';
import { useForm } from '@inertiajs/vue3';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';

const props = defineProps({
    employee: Object,
    address: Object,
});

const form = useForm({
    street_1: props.address?.street_1 || '',
    street_2: props.address?.street_2 || '',
    city: props.address?.city || '',
    state_province: props.address?.state_province || '',
    postal_code: props.address?.postal_code || '',
    country_id: props.address?.country_id || '',

    home_phone: props.employee.home_phone || '',
    mobile_phone: props.employee.mobile_phone || '',
    work_phone: props.employee.work_phone || '',

    work_email: props.employee.work_email || '',
    personal_email: props.employee.personal_email || '',
});

const submit = () => {
    form.post('/my-info/contact', {
        preserveScroll: true,
        onSuccess: () => {
        },
    });
};
</script>

<template>
    <MyInfoLayout>
        <div class="bg-white rounded-card p-8 shadow-card">
            <h3 class="text-lg font-semibold text-gray-700 mb-6 border-b border-gray-100 pb-4">Contact Details</h3>

            <form @submit.prevent="submit">
                <h4 class="text-md font-medium text-gray-600 mb-4">Address</h4>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-x-6">
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

                <div class="grid grid-cols-1 md:grid-cols-3 gap-x-6">
                    <TextInput v-model="form.state_province" placeholder="State/Province" :error="form.errors.state_province">
                        <template #label>State/Province</template>
                    </TextInput>
                    <TextInput v-model="form.postal_code" placeholder="Zip/Postal Code" :error="form.errors.postal_code">
                        <template #label>Zip/Postal Code</template>
                    </TextInput>
                     <div class="mb-5">
                        <label class="block text-xs text-brand-grey font-medium mb-2 ml-1">Country</label>
                        <select v-model="form.country_id" class="w-full p-3 pl-4 text-sm border border-gray-200 rounded-md focus:outline-none focus:border-brand-yellow bg-white text-gray-600">
                            <option value="">-- Select --</option>
                            <option value="1">Philippines</option>
                            <option value="2">United States</option>
                        </select>
                    </div>
                </div>

                <hr class="my-6 border-gray-100">

                <h4 class="text-md font-medium text-gray-600 mb-4">Telephone</h4>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-x-6">
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

                <hr class="my-6 border-gray-100">

                <h4 class="text-md font-medium text-gray-600 mb-4">Email</h4>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-x-6">
                    <TextInput v-model="form.work_email" placeholder="Work Email" :error="form.errors.work_email">
                        <template #label>Work Email</template>
                    </TextInput>
                     <TextInput v-model="form.personal_email" placeholder="Other Email" :error="form.errors.personal_email">
                        <template #label>Other Email</template>
                    </TextInput>
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