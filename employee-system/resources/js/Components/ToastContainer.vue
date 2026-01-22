<template>
    <div class="fixed bottom-4 left-4 z-50 space-y-3 w-[360px] max-w-[92vw]">
        <transition-group name="toast">
            <div
                v-for="toast in toasts"
                :key="toast.id"
                class="flex items-start gap-3 rounded-xl border px-4 py-3 shadow-md bg-white"
                :class="toastBorders[toast.type] || toastBorders.info"
            >
                <div class="flex items-center justify-center w-8 h-8 rounded-full" :class="toastIcons[toast.type]?.bg || toastIcons.info.bg">
                    <i :class="toastIcons[toast.type]?.icon || toastIcons.info.icon"></i>
                </div>
                <div class="flex-1 min-w-0">
                    <div class="flex items-start justify-between gap-2">
                        <div>
                            <p class="text-sm font-semibold text-gray-800">{{ toast.title || defaultTitles[toast.type] || 'Notice' }}</p>
                            <p class="text-sm text-gray-600 leading-snug break-words">{{ toast.message }}</p>
                        </div>
                        <button
                            class="text-gray-400 hover:text-gray-600 transition-colors"
                            @click="dismiss(toast.id)"
                            aria-label="Close notification"
                        >
                            <i class="fas fa-times"></i>
                        </button>
                    </div>
                </div>
            </div>
        </transition-group>
    </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';

const toasts = ref([]);
let toastId = 0;

const defaultTitles = {
    success: 'Success',
    error: 'Error',
    warning: 'Warning',
    info: 'Notice',
};

const toastBorders = {
    success: 'border-green-200 bg-green-50',
    error: 'border-red-200 bg-red-50',
    warning: 'border-yellow-200 bg-yellow-50',
    info: 'border-blue-200 bg-blue-50',
};

const toastIcons = {
    success: { icon: 'fas fa-check text-green-600', bg: 'bg-green-100' },
    error: { icon: 'fas fa-times text-red-600', bg: 'bg-red-100' },
    warning: { icon: 'fas fa-exclamation text-yellow-600', bg: 'bg-yellow-100' },
    info: { icon: 'fas fa-info text-blue-600', bg: 'bg-blue-100' },
};

const showToast = (message, type = 'info', duration = 3000, title = '') => {
    const id = toastId++;
    const toast = { id, message, type, title };
    
    toasts.value.push(toast);
    
    if (duration) {
        setTimeout(() => dismiss(id), duration);
    }
};

const dismiss = (id) => {
    toasts.value = toasts.value.filter(t => t.id !== id);
};

onMounted(() => {
    window.showToast = showToast;
});

defineExpose({ showToast });
</script>

<style scoped>
.toast-enter-active,
.toast-leave-active {
    transition: all 0.25s ease;
}

.toast-enter-from {
    opacity: 0;
    transform: translateY(6px);
}

.toast-leave-to {
    opacity: 0;
    transform: translateY(6px);
}
</style>
