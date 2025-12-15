<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import TimeAtWork from '@/Components/Widgets/TimeAtWork.vue';
import QuickLaunch from '@/Components/Widgets/QuickLaunch.vue';
import BuzzPosts from '@/Components/Widgets/BuzzPosts.vue';
import LeaveCalendar from '@/Components/Widgets/LeaveCalendar.vue';
import DashboardCard from '@/Components/DashboardCard.vue';
</script>

<template>
    <AuthenticatedLayout>
        <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-6">
            <TimeAtWork />

            <DashboardCard title="My Actions" icon="fas fa-list-ul">
                <div class="flex-1 flex flex-col items-center justify-center min-h-[200px]">
                    <i class="far fa-clipboard text-5xl text-gray-200 mb-3"></i>
                    <p class="text-xs text-gray-400 font-medium">No Pending Actions</p>
                </div>
            </DashboardCard>

            <QuickLaunch />
            <BuzzPosts />
            <LeaveCalendar />
        </div>
    </AuthenticatedLayout>
</template>
