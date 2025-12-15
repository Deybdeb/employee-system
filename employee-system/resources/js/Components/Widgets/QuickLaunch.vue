<script setup>
import DashboardCard from '@/Components/DashboardCard.vue';
</script>

<template>
    <DashboardCard title="Quick Launch" icon="fas fa-bolt">
        <div class="flex justify-around py-8">
            <div class="flex flex-col items-center gap-2.5 cursor-pointer group" v-for="(item, i) in [
                {icon: 'fas fa-sign-out-alt', text: 'Apply Leave'},
                {icon: 'fas fa-umbrella-beach', text: 'My Leave'},
                {icon: 'far fa-clock', text: 'My Timesheet'}
            ]" :key="i">
                <div class="w-12 h-12 bg-brand-light rounded-full flex items-center justify-center text-lg text-gray-500 group-hover:bg-gray-200 group-hover:text-brand-dark transition-all duration-200 shadow-sm border border-transparent group-hover:border-gray-300">
                    <i :class="item.icon"></i>
                </div>
                <span class="text-[11px] font-semibold text-gray-500 group-hover:text-brand-dark transition-colors">{{ item.text }}</span>
            </div>
        </div>
    </DashboardCard>
</template>
