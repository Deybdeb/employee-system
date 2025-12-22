<script setup>
import { useForm } from "@inertiajs/vue3";
import TextInput from "@/Components/TextInput.vue";
import PrimaryButton from "@/Components/PrimaryButton.vue";

const props = defineProps({
    email: String,
});

const form = useForm({
    email: props.email,
    password: "",
    password_confirmation: "",
});

const submit = () => {
    form.post("/reset-password");
};
</script>

<template>
    <div class="login-wrapper">
        <div class="white-shape"></div>

        <div class="login-container">
            <div
                class="bg-white px-10 py-4 rounded-xl shadow-lg border border-gray-50 flex flex-col items-center mb-8 w-full"
            >
                <div class="flex items-center gap-2.5 mb-1">
                    <div
                        class="bg-brand-yellow w-8 h-8 rounded-lg flex items-center justify-center text-brand-dark text-lg"
                    >
                        <i
                            class="fas fa-shield-alt transform rotate-180 text-sm"
                        ></i>
                    </div>
                    <div
                        class="text-2xl font-semibold text-brand-dark tracking-tight"
                    >
                        <span class="text-brand-yellow">Bee</span>Connected
                    </div>
                </div>
                <div class="text-[10px] text-gray-400 ml-auto mt-[-2px]">
                    Powered by
                    <span class="text-purple-700 font-semibold">PurpleBug</span>
                </div>
            </div>

            <h1 class="text-3xl font-normal text-brand-dark mb-3 tracking-wide">
                Reset Password
            </h1>
            
            <p class="text-gray-600 text-sm mb-8 text-center">
                Enter your new password below.
            </p>

            <form @submit.prevent="submit" class="w-full">
                <div
                    v-if="$page.props.flash?.status"
                    class="bg-green-50 text-green-600 p-3 rounded-md text-sm text-center mb-5 border border-green-100"
                >
                    {{ $page.props.flash.status }}
                </div>

                <div
                    v-if="
                        Object.keys(form.errors).length > 0 &&
                        !form.errors.email &&
                        !form.errors.password
                    "
                    class="bg-red-50 text-red-600 p-3 rounded-md text-sm text-center mb-5 border border-red-100"
                >
                    Something went wrong. Please try again.
                </div>

                <TextInput
                    v-model="form.email"
                    icon="far fa-envelope"
                    placeholder="your.email@company.com"
                    :error="form.errors.email"
                    readonly
                >
                    <template #label>Email Address</template>
                </TextInput>

                <TextInput
                    v-model="form.password"
                    type="password"
                    icon="fas fa-key"
                    placeholder="Enter new password"
                    :error="form.errors.password"
                >
                    <template #label>New Password</template>
                </TextInput>

                <TextInput
                    v-model="form.password_confirmation"
                    type="password"
                    icon="fas fa-key"
                    placeholder="Confirm new password"
                >
                    <template #label>Confirm Password</template>
                </TextInput>

                <PrimaryButton :processing="form.processing" class="mt-2 mb-6">
                    Reset Password
                </PrimaryButton>

                <div class="text-center">
                    <a
                        :href="route('login')"
                        class="text-indigo-600 hover:text-indigo-500 font-medium text-sm"
                    >
                        ← Back to Login
                    </a>
                </div>
            </form>
        </div>

        <div class="bee-badge-container">
            <div class="bee-shield"></div>
        </div>
    </div>
</template>

<style scoped>
.login-wrapper {
    --intersection-line: 75vw;
    --circle-radius: 180vmax;
    height: 100vh;
    width: 100vw;
    overflow: hidden;
    display: flex;
    background-color: #fdbd31;
    position: relative;
    font-family: "Poppins", sans-serif;
}
.white-shape {
    position: absolute;
    top: 50%;
    left: calc(var(--intersection-line) - var(--circle-radius));
    width: var(--circle-radius);
    height: var(--circle-radius);
    transform: translateY(-50%);
    background-color: #ffffff;
    border-radius: 50%;
    z-index: 1;
}
.bee-badge-container {
    position: absolute;
    top: 50%;
    left: var(--intersection-line);
    transform: translate(-50%, -50%);
    z-index: 2;
    width: 210px;
    height: 210px;
    background: white;
    border-radius: 50%;
    box-shadow: 0 10px 25px rgba(0, 0, 0, 0.1);
    display: flex;
    align-items: center;
    justify-content: center;
}
.bee-shield {
    width: 110px;
    height: 125px;
    background-color: #1a1a1a;
    border-radius: 50% 50% 50% 50% / 35% 35% 65% 65%;
    position: relative;
    overflow: hidden;
}
.bee-shield::before {
    content: "";
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: repeating-linear-gradient(
        to bottom,
        #fdbd31 0%,
        #fdbd31 28%,
        transparent 28%,
        transparent 45%
    );
    border-radius: inherit;
    margin-top: 22px;
}
.login-container {
    position: absolute;
    top: 50%;
    left: calc(var(--intersection-line) / 2);
    transform: translate(-50%, -50%);
    z-index: 10;
    display: flex;
    flex-direction: column;
    align-items: center;
    width: 400px;
}
</style>
