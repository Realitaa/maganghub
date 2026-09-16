<script setup lang="ts">
import { ref, onMounted, onUnmounted, watch, markRaw } from 'vue';
import {
    Chart as ChartJS,
    Title,
    Tooltip,
    Legend,
    ArcElement,
    CategoryScale,
    LinearScale,
    PieController,
    DoughnutController,
    type ChartConfiguration,
    type ChartData,
    type ChartOptions,
    type Plugin,
} from 'chart.js';
// Register standard modules
ChartJS.register(
    Title,
    Tooltip,
    Legend,
    ArcElement,
    CategoryScale,
    LinearScale,
    PieController,
    DoughnutController,
);

const props = defineProps<{
    type: 'pie' | 'doughnut' | 'bar' | 'line';
    data: ChartData;
    options?: ChartOptions;
    plugins?: Plugin[];
}>();

const canvasRef = ref<HTMLCanvasElement | null>(null);
let chartInstance: ChartJS | null = null;

const initChart = () => {
    if (!canvasRef.value) return;

    if (chartInstance) {
        chartInstance.destroy();
        chartInstance = null;
    }

    const config: ChartConfiguration = {
        type: props.type,
        data: JSON.parse(JSON.stringify(props.data)),
        options: props.options ? JSON.parse(JSON.stringify(props.options)) : {},
        plugins: props.plugins || [],
    };

    // Use markRaw to prevent Vue from wrapping the Chart.js instance in reactive proxies
    chartInstance = markRaw(new ChartJS(canvasRef.value, config));
};

onMounted(() => {
    initChart();
});

watch(
    () => props.data,
    (newData) => {
        if (chartInstance) {
            chartInstance.data = JSON.parse(JSON.stringify(newData));
            chartInstance.update();
        }
    },
    { deep: true },
);

watch(
    () => props.options,
    (newOptions) => {
        if (chartInstance) {
            chartInstance.options = newOptions
                ? JSON.parse(JSON.stringify(newOptions))
                : {};
            chartInstance.update();
        }
    },
    { deep: true },
);

onUnmounted(() => {
    if (chartInstance) {
        chartInstance.destroy();
        chartInstance = null;
    }
});
</script>

<template>
    <div class="relative flex h-full w-full items-center justify-center">
        <canvas ref="canvasRef" />
    </div>
</template>
