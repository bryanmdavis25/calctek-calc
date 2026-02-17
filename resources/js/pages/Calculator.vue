<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import { ref } from 'vue';
import Calculator from '@/components/Calculator.vue';
import TickerTape from '@/components/TickerTape.vue';

const refreshTrigger = ref(0);

const handleCalculationAdded = () => {
    refreshTrigger.value += 1;
};
</script>

<template>
    <Head title="CalcTek Calculator">
        <link rel="preconnect" href="https://rsms.me/" />
        <link rel="stylesheet" href="https://rsms.me/inter/inter.css" />
    </Head>

    <div class="min-h-screen bg-gradient-to-br from-blue-50 via-indigo-50 to-purple-50 p-6 py-12">
        <div class="mx-auto max-w-7xl">
            <!-- Header -->
            <div class="mb-12 text-center">
                <h1 class="mb-2 text-5xl font-bold bg-gradient-to-r from-blue-600 to-indigo-600 bg-clip-text text-transparent">
                    CalcTek
                </h1>
                <p class="text-lg text-gray-600">Your API-Driven Calculator</p>
                <p class="text-sm text-gray-500 mt-2">Support for basic operations and advanced math functions</p>
            </div>

            <!-- Main Content -->
            <div class="grid lg:grid-cols-2 gap-8 items-start">
                <!-- Calculator Section -->
                <div class="flex justify-center lg:justify-end">
                    <div style="width: 100%; max-width: 380px;">
                        <Calculator :on-calculation-added="handleCalculationAdded" />
                    </div>
                </div>

                <!-- Ticker Tape Section -->
                <div class="flex justify-center lg:justify-start">
                    <div style="width: 100%; max-width: 500px;">
                        <TickerTape :refresh-trigger="refreshTrigger" />
                    </div>
                </div>
            </div>

            <!-- Footer -->
            <div class="mt-16 text-center text-sm text-gray-500">
                <p>Built with Laravel 12, Vue 3, and Inertia.js</p>
            </div>
        </div>
    </div>
</template>
