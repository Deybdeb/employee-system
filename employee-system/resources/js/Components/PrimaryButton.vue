<script setup>
defineProps({
    type: {
        type: String,
        default: "submit",
    },
    disabled: {
        type: Boolean,
        default: false,
    },
    processing: {
        type: Boolean,
        default: false,
    },
});
</script>

<template>
    <button
        :type="type"
        :disabled="disabled || processing"
        class="w-full bg-brand-yellow text-brand-dark font-medium py-3.5 px-6 rounded-full transition duration-200 shadow-md hover:bg-brand-yellow-hover disabled:opacity-70 disabled:cursor-not-allowed flex justify-center items-center gap-2"
    >
        <span v-if="processing">Processing...</span>
        <slot v-else />
    </button>
</template>
