<script setup>
import MyInfoLayout from '@/Layouts/MyInfoLayout.vue';
import { useForm, usePage } from '@inertiajs/vue3';
import { ref, watch } from 'vue';

const props = defineProps({
    employee: Object,
});

const page = usePage();
const showSuccessPopup = ref(false);
const showErrorPopup = ref(false);
const errorMessage = ref('');

const form = useForm({
    current_password: '',
    password: '',
    password_confirmation: '',
});

const submit = () => {
    form.post('/my-info/password', {
        preserveScroll: true,
        onSuccess: () => {
            form.reset();
            showSuccessPopup.value = true;
            setTimeout(() => {
                showSuccessPopup.value = false;
            }, 3000);
        },
        onError: (errors) => {
            // Show error popup for current password mismatch or other validation errors
            if (errors.current_password) {
                errorMessage.value = errors.current_password;
            } else if (errors.password) {
                errorMessage.value = errors.password;
            } else if (errors.password_confirmation) {
                errorMessage.value = errors.password_confirmation;
            } else {
                errorMessage.value = 'An error occurred. Please try again.';
            }
            
            showErrorPopup.value = true;
            setTimeout(() => {
                showErrorPopup.value = false;
            }, 4000);
        },
    });
};

const cancel = () => {
    form.reset();
};

const closePopup = () => {
    showSuccessPopup.value = false;
};

const closeErrorPopup = () => {
    showErrorPopup.value = false;
};

</script>

<template>
    <MyInfoLayout>
        <!-- Success Popup -->
        <div 
            v-if="showSuccessPopup"
            class="fixed bottom-6 left-6 z-50 animate-slide-in"
        >
            <div 
                class="bg-white rounded-lg shadow-2xl border border-green-200 p-4 flex items-center gap-3 min-w-[300px]"
            >
                <div class="flex items-center justify-center w-10 h-10 bg-green-100 rounded-full shrink-0">
                    <i class="fas fa-check text-green-500 text-lg"></i>
                </div>
                <div class="flex-1">
                    <p class="text-sm font-semibold text-gray-900">Password Updated</p>
                    <p class="text-xs text-gray-600">Your password has been changed successfully.</p>
                </div>
                <button
                    @click="closePopup"
                    class="text-gray-400 hover:text-gray-600 transition-colors"
                >
                    <i class="fas fa-times"></i>
                </button>
            </div>
        </div>

        <!-- Error Popup -->
        <div 
            v-if="showErrorPopup"
            class="fixed bottom-6 left-6 z-50 animate-slide-in"
        >
            <div 
                class="bg-white rounded-lg shadow-2xl border border-red-200 p-4 flex items-center gap-3 min-w-[300px]"
            >
                <div class="flex items-center justify-center w-10 h-10 bg-red-100 rounded-full shrink-0">
                    <i class="fas fa-exclamation-circle text-red-500 text-lg"></i>
                </div>
                <div class="flex-1">
                    <p class="text-sm font-semibold text-gray-900">Error</p>
                    <p class="text-xs text-gray-600">{{ errorMessage }}</p>
                </div>
                <button
                    @click="closeErrorPopup"
                    class="text-gray-400 hover:text-gray-600 transition-colors"
                >
                    <i class="fas fa-times"></i>
                </button>
            </div>
        </div>

        <div class="bg-white rounded-card shadow-card p-8">
            <h2 class="text-xl font-bold text-brand-dark mb-8">Update Password</h2>

            <!-- Success Message -->
            <div v-if="page.props.flash?.success" class="mb-6 p-4 bg-green-50 border border-green-200 rounded-lg text-green-700 text-sm">
                {{ page.props.flash.success }}
            </div>

            <!-- Error Message -->
            <div v-if="page.props.flash?.error" class="mb-6 p-4 bg-red-50 border border-red-200 rounded-lg text-red-700 text-sm">
                {{ page.props.flash.error }}
            </div>

            <form @submit.prevent="submit">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8">
                    <!-- Username (readonly) -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            Username
                        </label>
                        <div class="text-sm text-gray-900 py-2">
                            {{ props.employee?.personal_email || page.props.auth?.user?.email || 'N/A' }}
                        </div>
                    </div>

                    <!-- Current Password -->
                    <div>
                        <label for="current_password" class="block text-sm font-medium text-gray-700 mb-2">
                            Current Password<span class="text-red-500">*</span>
                        </label>
                        <input
                            id="current_password"
                            v-model="form.current_password"
                            type="password"
                            class="w-full px-4 py-2.5 text-sm border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-brand-yellow focus:border-transparent transition-all"
                            :class="{ 'border-red-500': form.errors.current_password }"
                            required
                        />
                        <p v-if="form.errors.current_password" class="mt-1 text-sm text-red-600">
                            {{ form.errors.current_password }}
                        </p>
                    </div>

                    <!-- New Password -->
                    <div>
                        <label for="password" class="block text-sm font-medium text-gray-700 mb-2">
                            Password<span class="text-red-500">*</span>
                        </label>
                        <input
                            id="password"
                            v-model="form.password"
                            type="password"
                            class="w-full px-4 py-2.5 text-sm border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-brand-yellow focus:border-transparent transition-all"
                            :class="{ 'border-red-500': form.errors.password }"
                            required
                        />
                        <p v-if="form.errors.password" class="mt-1 text-sm text-red-600">
                            {{ form.errors.password }}
                        </p>
                    </div>

                    <!-- Confirm Password -->
                    <div>
                        <label for="password_confirmation" class="block text-sm font-medium text-gray-700 mb-2">
                            Confirm Password<span class="text-red-500">*</span>
                        </label>
                        <input
                            id="password_confirmation"
                            v-model="form.password_confirmation"
                            type="password"
                            class="w-full px-4 py-2.5 text-sm border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-brand-yellow focus:border-transparent transition-all"
                            :class="{ 'border-red-500': form.errors.password_confirmation }"
                            required
                        />
                        <p v-if="form.errors.password_confirmation" class="mt-1 text-sm text-red-600">
                            {{ form.errors.password_confirmation }}
                        </p>
                    </div>
                </div>

                <!-- Password Hint -->
                <div class="mb-8">
                    <p class="text-sm text-gray-600">
                        For a strong password, please use a hard to guess combination of text with upper and lower case characters, symbols and numbers
                    </p>
                </div>

                <!-- Required Notice -->
                <div class="mb-8">
                    <p class="text-sm text-gray-600">
                        <span class="text-red-500">*</span> Required
                    </p>
                </div>

                <!-- Action Buttons -->
                <div class="flex justify-end gap-4">
                    <button
                        type="button"
                        @click="cancel"
                        class="px-8 py-2.5 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-full hover:bg-gray-50 transition-colors"
                    >
                        Cancel
                    </button>
                    <button
                        type="submit"
                        :disabled="form.processing"
                        class="px-8 py-2.5 text-sm font-medium text-white bg-gray-900 rounded-full hover:bg-gray-800 transition-colors disabled:opacity-50 disabled:cursor-not-allowed"
                    >
                        {{ form.processing ? 'Saving...' : 'Save' }}
                    </button>
                </div>
            </form>
        </div>
    </MyInfoLayout>
</template>

<style scoped>
@keyframes slide-in {
    from {
        opacity: 0;
        transform: translateX(-100%);
    }
    to {
        opacity: 1;
        transform: translateX(0);
    }
}

.animate-slide-in {
    animation: slide-in 0.3s ease-out;
}
</style>
