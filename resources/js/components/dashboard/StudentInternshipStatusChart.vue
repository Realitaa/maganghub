<script setup lang="ts">
import type { ChartData, ChartOptions } from 'chart.js';
import { computed, ref, onMounted, onUnmounted } from 'vue';
import BaseChart from '@/components/ui/chart/BaseChart.vue';
import type { StudentInternshipStatusItem } from '@/types';

const props = defineProps<{
    items: StudentInternshipStatusItem[];
    totalStudents?: number;
}>();

const isDark = ref(false);

const checkTheme = () => {
    if (typeof document !== 'undefined') {
        isDark.value = document.documentElement.classList.contains('dark');
    }
};

let observer: MutationObserver | null = null;

onMounted(() => {
    checkTheme();

    if (typeof document !== 'undefined') {
        observer = new MutationObserver(checkTheme);
        observer.observe(document.documentElement, {
            attributes: true,
            attributeFilter: ['class'],
        });
    }
});

onUnmounted(() => {
    if (observer) {
        observer.disconnect();
    }
});

const hasData = computed(() => {
    return (
        props.items &&
        props.items.length > 0 &&
        props.items.some((item) => item.y > 0)
    );
});

const chartData = computed<ChartData<'pie'>>(() => {
    const labels = props.items.map((item) => item.name);
    const data = props.items.map((item) => item.y);
    const backgroundColor = props.items.map((item) => item.color);

    // Find the index of "Akan/Melaksanakan Magang" to explode/highlight it
    const offset = props.items.map((item) =>
        item.status === 'akan_melaksanakan' ||
        item.name.toLowerCase().includes('akan')
            ? 16
            : 0,
    );

    return {
        labels,
        datasets: [
            {
                data,
                backgroundColor,
                borderColor: isDark.value ? '#18181b' : '#ffffff',
                borderWidth: 2,
                // Highlight / exploded slice for Akan/Melaksanakan Magang
                offset,
                hoverOffset: 20,
            },
        ],
    };
});

const chartOptions = computed<ChartOptions<'pie'>>(() => {
    const textColor = isDark.value ? '#ededec' : '#1b1b18';

    return {
        responsive: true,
        maintainAspectRatio: false,
        layout: {
            padding: 16,
        },
        plugins: {
            legend: {
                display: true,
                position: 'bottom',
                labels: {
                    color: textColor,
                    padding: 12,
                    usePointStyle: true,
                    pointStyle: 'circle',
                    font: {
                        family: 'Inter, sans-serif',
                        size: 11,
                        weight: 500,
                    },
                    generateLabels: (chart) => {
                        const data = chart.data;

                        if (!data.labels || !data.datasets.length) {
                            return [];
                        }

                        const dataset = data.datasets[0];

                        return data.labels.map((label, i) => {
                            const val = (dataset.data[i] as number) || 0;

                            return {
                                text: `${label} (${val})`,
                                fillStyle: (
                                    dataset.backgroundColor as string[]
                                )[i],
                                strokeStyle: (
                                    dataset.backgroundColor as string[]
                                )[i],
                                lineWidth: 0,
                                hidden: !chart.getDataVisibility(i),
                                index: i,
                                pointStyle: 'circle',
                            };
                        });
                    },
                },
            },
            tooltip: {
                backgroundColor: isDark.value ? '#18181b' : '#ffffff',
                titleColor: isDark.value ? '#ffffff' : '#09090b',
                bodyColor: isDark.value ? '#e4e4e7' : '#27272a',
                borderColor: isDark.value ? '#27272a' : '#e4e4e7',
                borderWidth: 1,
                padding: 10,
                boxPadding: 4,
                usePointStyle: true,
                callbacks: {
                    label: (context) => {
                        const label = context.label || '';
                        const value = context.parsed;
                        const total = (context.dataset.data as number[]).reduce(
                            (a, b) => a + b,
                            0,
                        );
                        const pct =
                            total > 0
                                ? ((value / total) * 100).toFixed(1)
                                : '0.0';

                        return ` ${label}: ${value} mahasiswa (${pct}%)`;
                    },
                },
            },
        },
    };
});
</script>

<template>
    <div class="flex min-h-[340px] w-full items-center justify-center">
        <div v-if="hasData" class="h-[340px] w-full">
            <BaseChart type="pie" :data="chartData" :options="chartOptions" />
        </div>
        <div
            v-else
            class="flex flex-col items-center justify-center p-8 text-center text-muted-foreground"
        >
            <div
                class="mb-3 flex h-12 w-12 items-center justify-center rounded-full bg-muted"
            >
                <span class="text-xl">🎓</span>
            </div>
            <p class="text-sm font-medium">Belum ada data mahasiswa</p>
            <p class="mt-1 text-xs text-muted-foreground">
                Data status mahasiswa magang akan muncul ketika mahasiswa mulai
                terdaftar di sistem.
            </p>
        </div>
    </div>
</template>
