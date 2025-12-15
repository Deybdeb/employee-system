<script setup>
defineProps({
    title: String,
    icon: String,
    actionIcon: String,
    colSpan: {
        type: Number,
        default: 1,
    },
});
</script>

<template>
    <div
        class="bg-white rounded-card p-5 shadow-card flex flex-col h-full relative"
        :class="{
            'col-span-1': colSpan === 1,
            'col-span-1 md:col-span-2': colSpan === 2,
            'col-span-1 md:col-span-3': colSpan === 3,
        }"
    >
        <div
            class="flex items-center gap-2.5 text-sm font-semibold text-gray-700 mb-5"
        >
            <i v-if="icon" :class="[icon, 'text-gray-500 w-4 text-center']"></i>
            {{ title }}
            <div class="flex-1"></div>
            <i
                v-if="actionIcon"
                :class="[
                    actionIcon,
                    'cursor-pointer text-gray-400 hover:text-gray-600 transition',
                ]"
            ></i>
        </div>

        <div class="flex-1 flex flex-col">
            <slot />
        </div>
    </div>
</template>
