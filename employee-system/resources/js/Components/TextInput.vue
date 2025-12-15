<script setup>
defineProps({
    modelValue: String,
    icon: String,
    placeholder: String,
    type: { type: String, default: "text" },
    error: String,
});

defineEmits(["update:modelValue"]);
</script>

<template>
    <div class="mb-5">
        <label
            v-if="$slots.label"
            class="block text-xs text-brand-grey font-medium mb-2 ml-1"
        >
            <slot name="label" />
        </label>

        <div class="relative">
            <input
                :type="type"
                :value="modelValue"
                @input="$emit('update:modelValue', $event.target.value)"
                :placeholder="placeholder"
                class="w-full p-3 pl-4 text-sm border border-gray-200 rounded-md focus:outline-none focus:border-brand-yellow transition-colors text-gray-600 placeholder-gray-300"
                :class="{ 'border-brand-danger bg-brand-danger-bg': error }"
            />
        </div>

        <div
            v-if="error"
            class="text-brand-danger text-[11px] font-medium mt-1.5 flex items-center gap-1.5"
        >
            <i class="fas fa-exclamation-circle"></i> {{ error }}
        </div>
    </div>
</template>
