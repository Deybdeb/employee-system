<script setup>
import { computed } from 'vue';

const props = defineProps({
    message: String,
    type: {
        type: String,
        default: 'info', // 'success', 'error', 'warning', 'info'
    },
    duration: {
        type: Number,
        default: 4000,
    },
    show: {
        type: Boolean,
        default: false,
    },
});

const emit = defineEmits(['close']);

const backgroundColor = computed(() => {
    switch (props.type) {
        case 'success':
            return 'bg-green-100 border-green-400';
        case 'error':
            return 'bg-red-100 border-red-400';
        case 'warning':
            return 'bg-yellow-100 border-yellow-400';
        default:
            return 'bg-blue-100 border-blue-400';
    }
});

const textColor = computed(() => {
    switch (props.type) {
        case 'success':
            return 'text-green-800';
        case 'error':
            return 'text-red-800';
        case 'warning':
            return 'text-yellow-800';
        default:
            return 'text-blue-800';
    }
});

const iconClass = computed(() => {
    switch (props.type) {
        case 'success':
            return 'fas fa-check-circle';
        case 'error':
            return 'fas fa-exclamation-circle';
        case 'warning':
            return 'fas fa-exclamation-triangle';
        default:
            return 'fas fa-info-circle';
    }
});

const closeToast = () => {
    emit('close');
};

// Auto-close after duration if show is true
if (props.show && props.duration > 0) {
    setTimeout(() => {
        closeToast();
    }, props.duration);
}
</script>

<template>
    <Transition
        name="toast"
        @enter="(el) => el.offsetHeight"
        @leave="(el) => el.offsetHeight"
    >
        <div
            v-if="show"
            :class="[
                'fixed bottom-6 left-6 max-w-sm px-6 py-4 border-l-4 rounded-lg shadow-lg flex items-start gap-4 z-50 animate-slideIn',
                backgroundColor,
            ]"
        >
            <div class="flex-shrink-0 pt-0.5">
                <i :class="[iconClass, textColor, 'text-xl']"></i>
            </div>
            <div class="flex-grow">
                <p :class="[textColor, 'font-medium']">{{ message }}</p>
            </div>
            <button
                @click="closeToast"
                :class="[textColor, 'text-xl hover:opacity-75 transition-opacity']"
            >
                <i class="fas fa-times"></i>
            </button>
        </div>
    </Transition>
</template>

<style scoped>
.toast-enter-active,
.toast-leave-active {
    transition: all 0.3s ease;
}

.toast-enter-from {
    opacity: 0;
    transform: translateX(-20px);
}

.toast-leave-to {
    opacity: 0;
    transform: translateX(-20px);
}

@keyframes slideIn {
    from {
        opacity: 0;
        transform: translateX(-20px);
    }
    to {
        opacity: 1;
        transform: translateX(0);
    }
}

.animate-slideIn {
    animation: slideIn 0.3s ease-out;
}
</style>
