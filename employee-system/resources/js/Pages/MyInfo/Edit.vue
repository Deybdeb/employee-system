<script setup>
import MyInfoLayout from '@/Layouts/MyInfoLayout.vue';
import { useForm, usePage } from '@inertiajs/vue3';
import { ref, onMounted, onBeforeUnmount } from 'vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import QRCode from 'qrcode.vue';

const page = usePage();
const props = defineProps({
    employee: Object,
    twoFactorEnabled: Boolean,
    twoFactorData: Object,
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

// 2FA States
const showSuccessPopup = ref(false);
const showErrorPopup = ref(false);
const successMessage = ref('');
const errorMessage = ref('');
const twoFAForm = useForm({
    code: '',
});
const setupStarted = ref(false);
const qrCodeUrl = ref('');
const secret = ref('');
const verificationCode = ref('');
let countdownInterval = null;

const submit = () => {
    form.post('/my-info/personal', {
        preserveScroll: true,
        onSuccess: () => {
            // Optional: show success message
        },
    });
};

const generateCode = async () => {
    try {
        const response = await fetch(route('my-info.2fa.setup'), {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': page.props.csrf_token,
            },
        });

        if (!response.ok) {
            throw new Error('Failed to generate QR code');
        }

        const data = await response.json();
        
        qrCodeUrl.value = data.qrCodeUrl || '';
        secret.value = data.secret || '';
        setupStarted.value = true;
        verificationCode.value = '';
        successMessage.value = 'Scan the QR code with Google Authenticator app, then enter the 6-digit code below.';
        showSuccessPopup.value = true;
        setTimeout(() => {
            showSuccessPopup.value = false;
        }, 5000);
    } catch (error) {
        console.error('Error generating QR code:', error);
        errorMessage.value = 'Failed to generate QR code. Please try again.';
        showErrorPopup.value = true;
        setTimeout(() => {
            showErrorPopup.value = false;
        }, 3000);
    }
};

const submitCode = async () => {
    try {
        const response = await fetch(route('my-info.2fa.enable'), {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': page.props.csrf_token,
            },
            body: JSON.stringify({ code: verificationCode.value }),
        });

        if (response.ok) {
            setupStarted.value = false;
            verificationCode.value = '';
            qrCodeUrl.value = '';
            secret.value = '';
            successMessage.value = '2FA has been enabled successfully!';
            showSuccessPopup.value = true;
            setTimeout(() => {
                showSuccessPopup.value = false;
                window.location.reload();
            }, 3000);
        } else if (response.status === 422) {
            const data = await response.json();
            errorMessage.value = data.errors?.code || 'Invalid code. Please try again.';
            showErrorPopup.value = true;
            setTimeout(() => {
                showErrorPopup.value = false;
            }, 3000);
        } else {
            throw new Error('Failed to enable 2FA');
        }
    } catch (error) {
        console.error('Error enabling 2FA:', error);
        errorMessage.value = 'An error occurred. Please try again.';
        showErrorPopup.value = true;
        setTimeout(() => {
            showErrorPopup.value = false;
        }, 3000);
    }
};

const regenerateCode = async () => {
    try {
        const response = await fetch(route('my-info.2fa.regenerate'), {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': page.props.csrf_token,
            },
        });

        if (!response.ok) {
            throw new Error('Failed to regenerate QR code');
        }

        const data = await response.json();
        
        qrCodeUrl.value = data.qrCodeUrl || '';
        secret.value = data.secret || '';
        verificationCode.value = '';
        successMessage.value = 'New QR code generated. Scan it again with Google Authenticator.';
        showSuccessPopup.value = true;
        setTimeout(() => {
            showSuccessPopup.value = false;
        }, 3000);
    } catch (error) {
        console.error('Error regenerating QR code:', error);
        errorMessage.value = 'Failed to regenerate QR code. Please try again.';
        showErrorPopup.value = true;
        setTimeout(() => {
            showErrorPopup.value = false;
        }, 3000);
    }
};

const disable2FA = async () => {
    if (confirm('Are you sure you want to disable 2FA?')) {
        try {
            const response = await fetch(route('my-info.2fa.disable'), {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': page.props.csrf_token,
                },
            });

            if (response.ok) {
                successMessage.value = '2FA has been disabled';
                showSuccessPopup.value = true;
                setTimeout(() => {
                    showSuccessPopup.value = false;
                    window.location.reload();
                }, 3000);
            } else {
                throw new Error('Failed to disable 2FA');
            }
        } catch (error) {
            console.error('Error disabling 2FA:', error);
            errorMessage.value = 'Failed to disable 2FA. Please try again.';
            showErrorPopup.value = true;
            setTimeout(() => {
                showErrorPopup.value = false;
            }, 3000);
        }
    }
};

const closeSuccessPopup = () => {
    showSuccessPopup.value = false;
};

const closeErrorPopup = () => {
    showErrorPopup.value = false;
};

onBeforeUnmount(() => {
    if (countdownInterval) {
        clearInterval(countdownInterval);
    }
});
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
                    <p class="text-sm font-semibold text-gray-900">Success</p>
                    <p class="text-xs text-gray-600">{{ successMessage }}</p>
                </div>
                <button
                    @click="closeSuccessPopup"
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

            <!-- 2FA Section -->
            <div class="mt-12 border-t border-gray-200 pt-8">
                <h4 class="text-lg font-semibold text-gray-700 mb-6">Two-Factor Authentication (2FA)</h4>

                <!-- Status Section -->
                <div v-if="!setupStarted" class="mb-8 p-6 bg-blue-50 rounded-lg border border-blue-200">
                    <div class="flex items-start gap-4">
                        <div class="flex items-center justify-center w-10 h-10 bg-blue-100 rounded-full shrink-0 mt-1">
                            <i class="fas fa-info-circle text-blue-600"></i>
                        </div>
                        <div>
                            <p v-if="twoFactorEnabled" class="text-sm font-semibold text-blue-900">
                                ✓ 2FA is currently enabled
                            </p>
                            <p v-else class="text-sm font-semibold text-blue-900">
                                2FA is not enabled for your account
                            </p>
                            <p class="text-xs text-blue-700 mt-2">
                                Two-factor authentication adds an extra layer of security to your account. When enabled, you'll need to enter a 6-digit code after logging in.
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Setup Section -->
                <div v-if="!setupStarted && !twoFactorEnabled" class="mb-8">
                    <button
                        type="button"
                        @click="generateCode"
                        :disabled="twoFAForm.processing"
                        class="inline-flex items-center gap-2 px-6 py-3 bg-brand-yellow hover:bg-brand-yellow/90 text-brand-dark font-semibold rounded-lg transition-colors disabled:opacity-50 disabled:cursor-not-allowed"
                    >
                        <i class="fas fa-shield-alt"></i>
                        Enable 2FA
                    </button>
                </div>

                <!-- Code Generation & Verification Section -->
                <div v-if="setupStarted" class="bg-gray-50 p-6 rounded-lg border border-gray-200 mb-8">
                    <div class="mb-6">
                        <p class="text-sm font-semibold text-gray-700 mb-4">Step 1: Scan QR Code with Google Authenticator</p>
                        <div class="flex justify-center bg-white border-2 border-brand-yellow rounded-lg p-6 mb-6">
                            <QRCode 
                                v-if="qrCodeUrl" 
                                :value="qrCodeUrl"
                                :size="250"
                                level="H"
                                foreground="#1a1a1a"
                                background="#ffffff"
                            />
                        </div>

                        <div class="bg-blue-50 border border-blue-200 rounded-lg p-4 mb-6">
                            <p class="text-sm text-blue-900">
                                <strong>Instructions:</strong> Open Google Authenticator app on your phone and tap the "+" button to scan this QR code.
                            </p>
                        </div>
                    </div>

                    <hr class="mb-6" />

                    <!-- Verification Input -->
                    <div class="mb-6">
                        <p class="text-sm font-semibold text-gray-700 mb-4">Step 2: Enter 6-Digit Code</p>
                        <label class="block text-xs font-semibold text-gray-500 mb-3">
                            Enter the code from Google Authenticator to confirm
                        </label>
                        <input
                            v-model="verificationCode"
                            type="text"
                            inputmode="numeric"
                            maxlength="6"
                            placeholder="000000"
                            class="w-full px-4 py-3 text-center text-2xl font-mono border-2 border-gray-300 rounded-lg focus:outline-none focus:border-brand-yellow transition-colors"
                        />
                    </div>

                    <!-- Action Buttons -->
                    <div class="flex gap-3">
                        <button
                            type="button"
                            @click="submitCode"
                            :disabled="twoFAForm.processing || verificationCode.length !== 6"
                            class="flex-1 px-6 py-3 bg-brand-yellow hover:bg-brand-yellow/90 text-brand-dark font-semibold rounded-lg transition-colors disabled:opacity-50 disabled:cursor-not-allowed"
                        >
                            <i class="fas fa-check mr-2"></i>
                            Verify & Enable 2FA
                        </button>
                        <button
                            type="button"
                            @click="setupStarted = false"
                            class="flex-1 px-6 py-3 bg-gray-300 hover:bg-gray-400 text-gray-900 font-semibold rounded-lg transition-colors"
                        >
                            Cancel
                        </button>
                    </div>

                    <!-- Regenerate QR Code -->
                    <div class="mt-4 text-center">
                        <button
                            type="button"
                            @click="regenerateCode"
                            :disabled="twoFAForm.processing"
                            class="text-sm text-gray-600 hover:text-gray-900 font-semibold transition-colors"
                        >
                            <i class="fas fa-sync-alt mr-1"></i>
                            Regenerate QR Code
                        </button>
                    </div>
                </div>

                <!-- Disable Section -->
                <div v-if="!setupStarted && twoFactorEnabled" class="border-t border-gray-200 pt-6">
                    <p class="text-sm text-gray-600 mb-4">
                        To disable 2FA, click the button below. You'll no longer need to enter a code when logging in.
                    </p>
                    <button
                        type="button"
                        @click="disable2FA"
                        :disabled="twoFAForm.processing"
                        class="inline-flex items-center gap-2 px-6 py-3 bg-red-600 hover:bg-red-700 text-white font-semibold rounded-lg transition-colors disabled:opacity-50 disabled:cursor-not-allowed"
                    >
                        <i class="fas fa-times-circle"></i>
                        Disable 2FA
                    </button>
                </div>
            </div>
        </div>
    </MyInfoLayout>
</template>

<style scoped>
@keyframes slide-in {
    from {
        transform: translateX(-100%);
        opacity: 0;
    }
    to {
        transform: translateX(0);
        opacity: 1;
    }
}

.animate-slide-in {
    animation: slide-in 0.3s ease-out;
}
</style>