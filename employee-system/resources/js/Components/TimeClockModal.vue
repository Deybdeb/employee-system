<script setup>
import { ref, computed } from 'vue';
import { usePage } from '@inertiajs/vue3';
import WebcamCapture from '@/Components/WebcamCapture.vue';

const page = usePage();

const props = defineProps({
    isOpen: {
        type: Boolean,
        default: false
    },
    currentStatus: {
        type: String,
        enum: ['in', 'out'],
        required: true
    },
    onClose: {
        type: Function,
        required: true
    },
    onSubmit: {
        type: Function,
        required: true
    }
});

const showWebcam = ref(false);
const notes = ref('');
const capturedPhoto = ref(null);
const capturedPhotoUrl = ref(null);
const isSubmitting = ref(false);
const error = ref(null);
const success = ref(null);

const buttonLabel = computed(() => {
    return props.currentStatus === 'in' ? 'Time In' : 'Time Out';
});

const actionText = computed(() => {
    return props.currentStatus === 'in' ? 'time in' : 'time out';
});

const closeModal = () => {
    resetForm();
    props.onClose();
};

const resetForm = () => {
    notes.value = '';
    capturedPhoto.value = null;
    capturedPhotoUrl.value = null;
    showWebcam.value = false;
    error.value = null;
    success.value = null;
    isSubmitting.value = false;
};

const handlePhotoCapture = (blob) => {
    capturedPhoto.value = blob;
    capturedPhotoUrl.value = URL.createObjectURL(blob);
    showWebcam.value = false;
    success.value = 'Photo captured successfully!';
    setTimeout(() => {
        success.value = null;
    }, 3000);
};

const handleWebcamClose = () => {
    showWebcam.value = false;
};

const submitClockEntry = async () => {
    if (!capturedPhoto.value) {
        error.value = 'Please capture a photo before submitting';
        return;
    }

    isSubmitting.value = true;
    error.value = null;

    try {
        const formData = new FormData();
        formData.append('photo', capturedPhoto.value, 'clock-' + Date.now() + '.jpg');
        formData.append('type', props.currentStatus === 'in' ? 'clock_in' : 'clock_out');
        formData.append('notes', notes.value);

        console.log('Submitting clock entry...');

        // Get CSRF token from page props
        const csrfToken = page.props.csrf_token;

        const response = await fetch('/time-logs', {
            method: 'POST',
            body: formData,
            headers: {
                'X-CSRF-TOKEN': csrfToken,
            }
        });

        console.log('Response received:', response.status);

        const data = await response.json();

        if (!response.ok) {
            throw new Error(data.message || 'Failed to submit clock entry');
        }

        console.log('Clock entry submitted successfully:', data);

        // Show success toast at bottom left
        if (window.showToast) {
            window.showToast(data.message || 'Clock entry recorded successfully!', 'success');
        }
        
        // Call parent callback with success
        props.onSubmit(data);
        
        // Close modal after brief delay
        setTimeout(() => {
            resetForm();
            closeModal();
        }, 500);

    } catch (err) {
        const errorMsg = err.message || 'Failed to submit clock entry. Please try again.';
        error.value = errorMsg;
        
        // Show error toast at bottom left
        if (window.showToast) {
            window.showToast(errorMsg, 'error');
        }
        
        console.error('Submit error:', err);
    } finally {
        isSubmitting.value = false;
    }
};
</script>

<template>
    <div v-if="isOpen" class="fixed inset-0 bg-black/50 flex items-center justify-center z-40 p-4">
        <div class="bg-white rounded-lg shadow-xl max-w-md w-full max-h-[90vh] overflow-y-auto p-5">
            <!-- Header -->
            <div class="flex justify-between items-center mb-4">
                <h2 class="text-xl font-bold text-gray-900">{{ buttonLabel }}</h2>
                <button 
                    @click="closeModal"
                    class="text-gray-400 hover:text-gray-600 transition-colors"
                >
                    <i class="fas fa-times text-lg"></i>
                </button>
            </div>

            <!-- Error message -->
            <div v-if="error" class="mb-3 p-2.5 bg-red-50 border border-red-200 rounded-lg">
                <p class="text-xs text-red-700 flex items-center gap-2">
                    <i class="fas fa-exclamation-circle"></i>
                    {{ error }}
                </p>
            </div>

            <!-- Success message -->
            <div v-if="success" class="mb-3 p-2.5 bg-green-50 border border-green-200 rounded-lg">
                <p class="text-xs text-green-700 flex items-center gap-2">
                    <i class="fas fa-check-circle"></i>
                    {{ success }}
                </p>
            </div>

            <!-- Photo section -->
            <div class="mb-4">
                <label class="block text-xs font-semibold text-gray-900 mb-2">
                    <i class="fas fa-camera text-brand-yellow mr-1.5"></i>
                    Photo <span class="text-red-500">*</span>
                </label>

                <div v-if="!capturedPhoto" class="p-6 bg-gray-50 rounded-lg text-center">
                    <i class="fas fa-camera text-3xl text-gray-300 mb-2"></i>
                    <p class="text-xs text-gray-600 mb-3">
                        Capture a photo to {{ actionText }}
                    </p>
                    <button
                        @click="showWebcam = true"
                        class="px-3 py-1.5 bg-brand-yellow text-gray-900 rounded-lg text-sm font-medium hover:bg-yellow-500 transition-colors"
                    >
                        <i class="fas fa-camera mr-1.5"></i>
                        Capture Photo
                    </button>
                </div>

                <div v-else class="relative">
                    <img
                        :src="capturedPhotoUrl"
                        alt="Captured photo"
                        class="w-full rounded-lg max-h-48 object-cover"
                    />
                    <button
                        @click="showWebcam = true"
                        class="absolute top-2 right-2 px-2.5 py-1 bg-white text-gray-900 rounded-lg font-medium text-xs hover:bg-gray-100 transition-colors flex items-center gap-1.5"
                    >
                        <i class="fas fa-redo text-xs"></i>
                        Retake
                    </button>
                </div>
            </div>

            <!-- Notes section -->
            <div class="mb-4">
                <label class="block text-xs font-semibold text-gray-900 mb-2">
                    <i class="fas fa-sticky-note text-brand-yellow mr-1.5"></i>
                    Notes <span class="text-gray-400">(optional)</span>
                </label>
                <textarea
                    v-model="notes"
                    placeholder="Add any notes (e.g., 'Started morning shift', 'Returned from break')"
                    maxlength="500"
                    rows="2"
                    class="w-full px-2.5 py-2 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-brand-yellow focus:border-transparent resize-none"
                ></textarea>
                <p class="text-[10px] text-gray-500 mt-1">
                    {{ notes.length }}/500 characters
                </p>
            </div>

            <!-- Current time display -->
            <div class="mb-4 p-3 bg-blue-50 rounded-lg">
                <p class="text-xs text-gray-600 mb-0.5">Current Server Time (GMT +8:00)</p>
                <p class="text-base font-bold text-gray-900">
                    {{ new Date().toLocaleString('en-US', { 
                        year: 'numeric', 
                        month: 'short', 
                        day: '2-digit',
                        hour: '2-digit',
                        minute: '2-digit',
                        second: '2-digit',
                        timeZone: 'Asia/Manila'
                    }) }}
                </p>
            </div>

            <!-- Action buttons -->
            <div class="flex gap-2.5">
                <button
                    @click="closeModal"
                    class="flex-1 px-4 py-2 text-sm text-gray-700 bg-gray-100 hover:bg-gray-200 rounded-lg font-medium transition-colors"
                >
                    Cancel
                </button>
                <button
                    @click="submitClockEntry"
                    :disabled="isSubmitting || !capturedPhoto"
                    class="flex-1 px-4 py-2 text-sm text-gray-900 bg-brand-yellow hover:bg-yellow-500 disabled:opacity-50 rounded-lg font-medium transition-colors flex items-center justify-center gap-2"
                >
                    <i v-if="!isSubmitting" class="fas fa-check"></i>
                    <i v-else class="fas fa-spinner fa-spin"></i>
                    {{ isSubmitting ? 'Submitting...' : buttonLabel }}
                </button>
            </div>
        </div>
    </div>

    <!-- Webcam modal -->
    <WebcamCapture 
        v-if="showWebcam"
        :onCapture="handlePhotoCapture"
        :onClose="handleWebcamClose"
    />
</template>
