<script setup lang="ts">
import { computed, ref, onMounted, onUnmounted } from 'vue';

type Props = {
    options: Record<string, any>;
    constructorType?: 'chart' | 'stockChart' | 'mapChart' | 'ganttChart';
    callback?: Function;
    updateArgs?: any[];
    deepCopyOnUpdate?: boolean;
};

const props = withDefaults(defineProps<Props>(), {
    constructorType: 'chart',
    deepCopyOnUpdate: true
});

const isClient = ref(false);
const HighchartsComponent = ref<any>(null);

// Detect theme mutations (dark vs light mode)
const isDark = ref(false);

const checkTheme = () => {
    if (typeof document !== 'undefined') {
        isDark.value = document.documentElement.classList.contains('dark');
    }
};

let observer: MutationObserver | null = null;

// Dynamic options computed based on light/dark mode
const themedOptions = computed(() => {
    const textColor = isDark.value ? '#ededec' : '#1b1b18';
    
    // Create copy of options and inject theme variables
    const opts = { ...props.options };
    
    opts.chart = {
        ...(opts.chart || {}),
        backgroundColor: 'transparent'
    };
    
    if (opts.title) {
        opts.title = {
            ...opts.title,
            style: {
                color: textColor,
                fontFamily: 'Inter, sans-serif',
                fontWeight: '600',
                ...(opts.title.style || {})
            }
        };
    }

    if (opts.subtitle) {
        opts.subtitle = {
            ...opts.subtitle,
            style: {
                color: isDark.value ? '#a1a09a' : '#706f6c',
                fontFamily: 'Inter, sans-serif',
                ...(opts.subtitle.style || {})
            }
        };
    }
    
    if (opts.legend) {
        opts.legend = {
            ...opts.legend,
            itemStyle: {
                color: textColor,
                fontFamily: 'Inter, sans-serif',
                fontSize: '11px',
                fontWeight: '500',
                ...(opts.legend.itemStyle || {})
            },
            itemHoverStyle: {
                color: isDark.value ? '#ffffff' : '#000000',
                ...(opts.legend.itemHoverStyle || {})
            }
        };
    }
    
    // Adapt tooltip background
    opts.tooltip = {
        ...(opts.tooltip || {}),
        backgroundColor: isDark.value ? '#1c1c1a' : '#ffffff',
        borderColor: isDark.value ? '#2e2e2a' : '#e4e4e7',
        style: {
            color: textColor,
            fontFamily: 'Inter, sans-serif',
            fontSize: '12px',
            ...(opts.tooltip?.style || {})
        }
    };
    
    // Check plotOptions -> pie -> dataLabels styles
    if (opts.plotOptions?.pie) {
        opts.plotOptions.pie = {
            ...opts.plotOptions.pie,
            dataLabels: {
                ...(opts.plotOptions.pie.dataLabels || {}),
                style: {
                    color: textColor,
                    textOutline: 'none',
                    fontFamily: 'Inter, sans-serif',
                    fontSize: '11px',
                    fontWeight: '500',
                    ...(opts.plotOptions.pie.dataLabels?.style || {})
                }
            }
        };
    }
    
    return opts;
});

const hcInstance = ref<any>(null);

onMounted(async () => {
    // Check theme initially
    checkTheme();
    
    // Set up MutationObserver to react to dark mode toggle class changes
    if (typeof document !== 'undefined') {
        observer = new MutationObserver(checkTheme);
        observer.observe(document.documentElement, {
            attributes: true,
            attributeFilter: ['class']
        });
    }

    // SSR-Safe dynamic import of Highcharts and Highcharts-3D
    const HighchartsModule = (await import('highcharts')) as any;
    const Highcharts = HighchartsModule.default || HighchartsModule;

    const Highcharts3DModule = (await import('highcharts/highcharts-3d')) as any;
    const Highcharts3D = Highcharts3DModule.default || Highcharts3DModule;
    
    // In Highcharts v12+, modules apply themselves automatically upon import.
    // Only invoke it if it's a legacy initializer function (not the Highcharts instance itself).
    if (typeof Highcharts3D === 'function' && Highcharts3D !== Highcharts && Highcharts3D !== HighchartsModule) {
        (Highcharts3D as any)(Highcharts);
    }

    hcInstance.value = Highcharts;

    // Enable point-level showInLegend config (e.g. hiding individual pie slices from legend)
    if (Highcharts && Highcharts.Legend && Highcharts.Legend.prototype) {
        Highcharts.wrap(Highcharts.Legend.prototype, 'getAllItems', function (this: any, proceed: any) {
            const allItems = proceed.apply(this, Array.prototype.slice.call(arguments, 1));
            return allItems.filter((item: any) => {
                return !item || (item.showInLegend !== false && (!item.options || item.options.showInLegend !== false));
            });
        });
    }

    // SSR-Safe dynamic import of highcharts-vue (which resolves peer highcharts)
    const module = (await import('highcharts-vue')) as any;
    HighchartsComponent.value = module.Chart || module.default?.Chart || module.default;
    isClient.value = true;
});

onUnmounted(() => {
    if (observer) {
        observer.disconnect();
    }
});
</script>

<template>
    <div class="w-full h-full min-h-[300px] flex items-center justify-center">
        <!-- Render dynamic component locally on client-only -->
        <component
            :is="HighchartsComponent"
            v-if="isClient && HighchartsComponent"
            :options="themedOptions"
            :constructor-type="constructorType"
            :callback="callback"
            :update-args="updateArgs"
            :deep-copy-on-update="deepCopyOnUpdate"
            :highcharts="hcInstance"
            class="w-full"
        />
    </div>
</template>
