<script setup>
import { ref, onMounted, onBeforeUnmount } from 'vue';

const props = defineProps({
    onCapture: {
        type: Function,
        required: true
    },
    onClose: {
        type: Function,
        required: false
    }
});

const videoRef = ref(null);
const canvasRef = ref(null);
const stream = ref(null);
const capturedImage = ref(null);
const hasPermission = ref(null);
const isLoading = ref(false);
const error = ref(null);

onMounted(async () => {
    await requestCameraPermission();
});

onBeforeUnmount(() => {
    stopStream();
});

const requestCameraPermission = async () => {
    isLoading.value = true;
    error.value = null;

    try {
        console.log('Requesting camera access...');
        
        const constraints = {
            video: {
                facingMode: 'user',
                width: { ideal: 1280 },
                height: { ideal: 720 }
            },
            audio: false
        };

        const mediaStream = await navigator.mediaDevices.getUserMedia(constraints);
        console.log('Camera access granted, stream:', mediaStream);
        
        stream.value = mediaStream;
        hasPermission.value = true;

        // Wait for DOM to be ready, then attach stream
        await new Promise(resolve => setTimeout(resolve, 100));

        if (videoRef.value) {
            console.log('Attaching stream to video element...');
            videoRef.value.srcObject = mediaStream;
            
            // Handle video metadata loaded
            videoRef.value.onloadedmetadata = () => {
                console.log('Video metadata loaded, video dimensions:', videoRef.value.videoWidth, 'x', videoRef.value.videoHeight);
                const playPromise = videoRef.value.play();
                if (playPromise !== undefined) {
                    playPromise
                        .then(() => {
                            console.log('Video playing successfully');
                        })
                        .catch(err => {
                            console.error('Play error:', err);
                            error.value = 'Failed to play video: ' + err.message;
                        });
                }
            };

            // Fallback: try playing immediately
            videoRef.value.play().catch(err => {
                console.warn('Immediate play failed, will retry on metadata:', err);
            });
        } else {
            console.error('Video ref not found!');
            error.value = 'Video element not found';
        }
    } catch (err) {
        console.error('Camera error:', err);
        hasPermission.value = false;
        
        if (err.name === 'NotAllowedError') {
            error.value = 'Camera permission denied. Please allow camera access to continue.';
        } else if (err.name === 'NotFoundError') {
            error.value = 'No camera found. Please check your camera connection.';
        } else if (err.name === 'NotReadableError') {
            error.value = 'Camera is already in use by another application.';
        } else {
            error.value = 'Unable to access camera: ' + err.message;
        }
    } finally {
        isLoading.value = false;
    }
};

const capturePhoto = () => {
    if (!videoRef.value || !canvasRef.value) return;

    try {
        const context = canvasRef.value.getContext('2d');
        canvasRef.value.width = videoRef.value.videoWidth;
        canvasRef.value.height = videoRef.value.videoHeight;
        context.drawImage(videoRef.value, 0, 0);

        capturedImage.value = canvasRef.value.toDataURL('image/jpeg', 0.9);
        stopStream();
    } catch (err) {
        error.value = 'Failed to capture photo. Please try again.';
        console.error('Capture error:', err);
    }
};

const retakePhoto = () => {
    capturedImage.value = null;
    requestCameraPermission();
};

const submitPhoto = async () => {
    if (!capturedImage.value) return;

    isLoading.value = true;
    error.value = null;

    try {
        // Convert data URL to blob
        const response = await fetch(capturedImage.value);
        const blob = await response.blob();

        // Call parent callback with blob
        props.onCapture(blob);
    } catch (err) {
        error.value = 'Failed to process photo. Please try again.';
        console.error('Submit error:', err);
    } finally {
        isLoading.value = false;
    }
};

const stopStream = () => {
    if (stream.value) {
        stream.value.getTracks().forEach(track => track.stop());
        stream.value = null;
    }
};

const close = () => {
    stopStream();
    if (props.onClose) {
        props.onClose();
    }
};
</script>

<template>
    <div class="fixed inset-0 bg-black/50 flex items-center justify-center z-50">
        <div class="bg-white rounded-lg shadow-xl max-w-md w-full mx-4 p-6">
            <!-- Header -->
            <div class="flex justify-between items-center mb-4">
                <h3 class="text-lg font-bold text-gray-900">Capture Photo</h3>
                <button 
                    @click="close"
                    class="text-gray-400 hover:text-gray-600 transition-colors"
                >
                    <i class="fas fa-times text-xl"></i>
                </button>
            </div>

            <!-- Error message -->
            <div v-if="error" class="mb-4 p-3 bg-red-50 border border-red-200 rounded-lg">
                <p class="text-sm text-red-700 flex items-center gap-2">
                    <i class="fas fa-exclamation-circle"></i>
                    {{ error }}
                </p>
                <button 
                    @click="requestCameraPermission"
                    class="mt-2 text-sm text-red-600 hover:text-red-800 font-medium"
                >
                    Try Again
                </button>
            </div>

            <!-- Camera loading state -->
            <div v-if="isLoading && !capturedImage" class="mb-4 text-center py-8">
                <i class="fas fa-spinner fa-spin text-3xl text-brand-yellow mb-2"></i>
                <p class="text-gray-600 text-sm">Accessing camera...</p>
            </div>

            <!-- Video preview (live camera) -->
            <div v-if="hasPermission && !capturedImage" class="mb-4 relative bg-black rounded-lg overflow-hidden" style="aspect-ratio: 4/3;">
                <video
                    ref="videoRef"
                    autoplay
                    muted
                    playsinline
                    webkit-playsinline="true"
                    class="w-full h-full object-cover"
                    style="transform: scaleX(-1); display: block;"
                ></video>
                <!-- Fallback loading state for video -->
                <div v-if="isLoading" class="absolute inset-0 flex items-center justify-center bg-black/50">
                    <div class="text-center">
                        <i class="fas fa-video text-2xl text-gray-300 mb-2"></i>
                        <p class="text-gray-300 text-sm">Loading camera...</p>
                    </div>
                </div>
            </div>

            <!-- Captured image preview -->
            <div v-if="capturedImage" class="mb-4">
                <img
                    :src="capturedImage"
                    alt="Captured photo"
                    class="w-full rounded-lg"
                    style="aspect-ratio: 4/3; object-fit: cover;"
                />
            </div>

            <!-- Permission denied state -->
            <div v-if="hasPermission === false && !capturedImage" class="mb-4 text-center py-8">
                <i class="fas fa-camera-slash text-4xl text-gray-300 mb-2"></i>
                <p class="text-gray-600 text-sm">Camera access denied</p>
            </div>

            <!-- Hidden canvas for photo capture -->
            <canvas ref="canvasRef" style="display: none;"></canvas>

            <!-- Action buttons -->
            <div class="flex gap-3">
                <button
                    v-if="!capturedImage"
                    @click="close"
                    class="flex-1 px-4 py-2 text-gray-700 bg-gray-100 hover:bg-gray-200 rounded-lg font-medium transition-colors"
                >
                    Cancel
                </button>
                <button
                    v-if="!capturedImage && hasPermission"
                    @click="capturePhoto"
                    class="flex-1 px-4 py-2 text-white bg-brand-yellow hover:bg-yellow-500 rounded-lg font-medium transition-colors flex items-center justify-center gap-2"
                >
                    <i class="fas fa-camera"></i>
                    Capture
                </button>

                <button
                    v-if="capturedImage"
                    @click="retakePhoto"
                    class="flex-1 px-4 py-2 text-gray-700 bg-gray-100 hover:bg-gray-200 rounded-lg font-medium transition-colors flex items-center justify-center gap-2"
                >
                    <i class="fas fa-redo"></i>
                    Retake
                </button>
                <button
                    v-if="capturedImage"
                    @click="submitPhoto"
                    :disabled="isLoading"
                    class="flex-1 px-4 py-2 text-white bg-brand-yellow hover:bg-yellow-500 disabled:opacity-50 rounded-lg font-medium transition-colors flex items-center justify-center gap-2"
                >
                    <i v-if="!isLoading" class="fas fa-check"></i>
                    <i v-else class="fas fa-spinner fa-spin"></i>
                    {{ isLoading ? 'Processing...' : 'Use Photo' }}
                </button>
            </div>

            <!-- Info text -->
            <p class="text-xs text-gray-500 text-center mt-4">
                Make sure your face is clearly visible in good lighting
            </p>
        </div>
    </div>
</template>

<style scoped>
/* Video mirror effect is applied inline to ensure it works with srcObject */
</style>
