<script setup lang="ts">
import { computed, ref, onMounted, onUnmounted } from 'vue';
import HighchartsChart from '@/components/ui/highcharts/HighchartsChart.vue';
import type { CompanyStatistics } from '@/types';

interface CompanyLevel {
    name: string;
    y: number;
    color: string;
    sliced?: boolean;
}

const props = defineProps<{
    statistics: CompanyStatistics;
}>();

// Theme detection
const isDark = ref(false);
const isInView = ref(false);
const containerRef = ref<HTMLElement | null>(null);

const checkTheme = () => {
    isDark.value = document.documentElement.classList.contains('dark');
};

let observer: MutationObserver | null = null;
let intersectionObserver: IntersectionObserver | null = null;

onMounted(() => {
    checkTheme();
    observer = new MutationObserver(checkTheme);
    observer.observe(document.documentElement, {
        attributes: true,
        attributeFilter: ['class'],
    });

    if (containerRef.value && typeof IntersectionObserver !== 'undefined') {
        intersectionObserver = new IntersectionObserver(
            (entries) => {
                entries.forEach((entry) => {
                    if (entry.isIntersecting) {
                        isInView.value = true;

                        if (intersectionObserver) {
                            intersectionObserver.disconnect();
                            intersectionObserver = null;
                        }
                    }
                });
            },
            {
                threshold: 0.1,
            },
        );
        intersectionObserver.observe(containerRef.value);
    } else {
        isInView.value = true;
    }
});

onUnmounted(() => {
    if (observer) {
        observer.disconnect();
    }

    if (intersectionObserver) {
        intersectionObserver.disconnect();
    }
});

// Process chart data, start angle, and slices
const chartDetails = computed(() => {
    const isAllZero = props.statistics.multinational === 0 &&
                      props.statistics.national === 0 &&
                      props.statistics.startup === 0;

    if (isAllZero) {
        return {
            data: [
                {
                    name: 'Mahasiswa Siap Magang',
                    y: props.statistics.havenot || 1,
                    color: '#10b981', // Emerald 500
                    sliced: false,
                    showInLegend: true,
                    dataLabels: {
                        enabled: true,
                        format: '{point.percentage:.1f}%',
                    },
                }
            ],
            startAngle: -45,
        };
    }

    // We only have 3 active slices and 1 inactive (Belum Magang)
    // Force Perusahaan Multinasional to be sliced/exploded
    const companies: CompanyLevel[] = [
        {
            name: 'Perusahaan Multinasional',
            y: props.statistics.multinational,
            color: '#059669', // Emerald 600
            sliced: true,
        },
        {
            name: 'Perusahaan Nasional',
            y: props.statistics.national,
            color: '#34d399', // Emerald 400
            sliced: false,
        },
        {
            name: 'Startup Teknologi',
            y: props.statistics.startup,
            color: '#a7f3d0', // Emerald 200
            sliced: false,
        },
    ];

    const totalCompanySum = companies.reduce((sum, item) => sum + item.y, 0);
    const totalSum = totalCompanySum + props.statistics.havenot;

    // The first slice (Multinasional) is explicitly selected to be exploded and face top-left
    const explodedValue = companies[0]?.y || 0;

    // To make the exploded slice face top-left (-45 degrees):
    // startAngle of the first slice should align its midpoint at -45 deg.
    // midpoint = startAngle + (p * 360) / 2 = -45
    // startAngle = -45 - 180 * p
    const p = totalSum > 0 ? explodedValue / totalSum : 0;
    const startAngle = -45 - 180 * p;

    // Combine data: biggest company level is first (index 0), then other company levels, then havenot
    const data = [
        ...companies,
        {
            name: 'Belum Magang',
            y: props.statistics.havenot,
            color: isDark.value ? '#27272a' : '#e4e4e7', // neutral zinc color
            sliced: false, // never slice havenot
            showInLegend: false, // don't show in legend
            dataLabels: {
                enabled: false, // don't show percentage labels for havenot
            },
            states: {
                hover: {
                    enabled: false, // disable hover states completely
                    halo: null,
                },
            },
            cursor: 'default', // no hover pointer reaction
        },
    ];

    return {
        data,
        startAngle,
    };
});

// Highcharts options configuration
const chartOptions = computed(() => {
    const details = chartDetails.value;

    return {
        chart: {
            type: 'pie',
            spacingTop: 10,
            spacingBottom: 10,
            spacingLeft: 0,
            spacingRight: 0,
            height: 400,
            options3d: {
                enabled: true,
                alpha: -10,
                beta: -10,
            },
        },
        title: {
            text: '', // Title is handled by outer card header
        },
        credits: {
            enabled: false,
        },
        tooltip: {
            formatter: function (this: any) {
                // Completely skip tooltip for havenot
                if (this.point.name === 'Belum Magang') {
                    return false;
                }

                return (
                    '<span style="color:' +
                    this.point.color +
                    '">\u25CF</span> <b>' +
                    this.point.name +
                    '</b>: <b>' +
                    this.point.y +
                    '</b> (' +
                    this.point.percentage.toFixed(1) +
                    '%)'
                );
            },
        },
        accessibility: {
            point: {
                valueSuffix: '%',
            },
        },
        plotOptions: {
            pie: {
                allowPointSelect: true,
                cursor: 'pointer',
                depth: 35,
                startAngle: details.startAngle,
                slicedOffset: 12,
                showInLegend: true,
                states: {
                    hover: {
                        enabled: true,
                        brightness: 0.01, // high contrast brightness highlight
                        halo: {
                            size: 0, // remove fuzzy blurry shadow halo
                        },
                    },
                    inactive: {
                        opacity: 0.15, // keep other slices readable
                    },
                },
                dataLabels: {
                    enabled: true,
                    format: '{point.percentage:.1f}%',
                    distance: 15,
                    connectorWidth: 1,
                    connectorColor: isDark.value ? '#3e3e3a' : '#e3e3e0',
                },
            },
        },
        legend: {
            enabled: true,
            layout: 'horizontal',
            align: 'center',
            verticalAlign: 'bottom',
            itemMarginTop: 5,
            itemMarginBottom: 5,
        },
        series: [
            {
                name: 'Kategori Magang',
                colorByPoint: true,
                data: details.data,
            },
        ],
    };
});
</script>

<template>
    <div ref="containerRef" class="min-h-[400px] w-full">
        <HighchartsChart v-if="isInView" :options="chartOptions" />
    </div>
</template>
