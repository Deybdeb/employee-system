<script setup>
import DashboardCard from '@/Components/DashboardCard.vue';
</script>

<template>
    <DashboardCard title="Buzz Latest Posts" icon="fas fa-camera">
        <div class="flex gap-3 mb-4">
            
            <img src="https://i.pravatar.cc/48?u=elaine" alt="User avatar" class="w-12 h-12 rounded-full object-cover">
            <div>
                <h5 class="text-[13px] font-bold text-brand-dark m-0 leading-tight">Elaine Mariz Garcia Arcilla</h5>
                <span class="text-[10px] text-gray-400">2025-11-19 11:42 AM</span>
            </div>
        </div>
        <div class="text-xs text-gray-600 leading-relaxed border-t border-dashed border-gray-200 pt-4">
            <p class="mb-2">Hi Everyone!</p>
            <p class="mb-2">As our Christmas Party is fast approaching, here are a few updates and reminders:</p>
            <ol class="list-decimal pl-5 mb-2 space-y-1">
                <li>Theme: Safari</li>
                <li>Exchange Gift: Minimum of ₱500</li>
            </ol>
            
            <p class="text-blue-600 underline cursor-pointer mb-2 truncate">https://example.com/post/christmas-party-updates</p>
        </div>
    </DashboardCard>
</template>