<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';

// The 'requests' prop is being passed from the LeaveRequestController
defineProps({
    requests: Array,
});
</script>

<template>
    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-xl font-semibold leading-tight text-gray-800">Leave Requests History</h2>
        </template>

        <div class="py-12">
            <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
                <div class="bg-white p-6 shadow sm:rounded-lg">
                    <p class="text-sm text-gray-500">
                        This is the Leave Request History Page. The controller and routing are now working!
                    </p>
                    <div v-if="requests && requests.length">
                        <ul class="mt-4 space-y-2">
                            <li v-for="request in requests" :key="request.id" class="border-b pb-2">
                                {{ request.type }} ({{ request.start_date }} to {{ request.end_date }}) - Status: {{ request.status }}
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>