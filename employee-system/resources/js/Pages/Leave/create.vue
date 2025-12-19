<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Link } from '@inertiajs/vue3';

// The 'leaveTypes' prop is being passed from the LeaveRequestController
defineProps({
    leaveTypes: Array,
});
</script>

<template>
    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-xl font-semibold leading-tight text-gray-800">New Leave Request</h2>
        </template>

        <div class="py-12">
            <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
                <div class="bg-white p-6 shadow sm:rounded-lg">
                    <p class="text-sm text-gray-500">
                        This is the Leave Request Creation Form Page. You can now build your form here!
                    </p>
                    <Link :href="route('leave-requests.index')" class="mt-4 inline-block text-blue-600 hover:text-blue-800">
                        ← Back to History
                    </Link>

                    <div class="mt-4 p-4 border border-dashed rounded-lg">
                        <p>Available Leave Types: {{ leaveTypes.join(', ') }}</p>
                        <p class="mt-2 text-red-500">Form implementation goes here.</p>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>