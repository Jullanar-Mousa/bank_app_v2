<div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-6">
    <!-- المخطط الأول: توزيع أولوية التنبيهات (Donut Chart) -->
    <div class="p-6 bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700">
        <div class="mb-4">
            <h3 class="text-base font-bold text-gray-900 dark:text-white">
                توزيع التنبيهات حسب الخطورة
            </h3>
            <p class="text-xs text-gray-500 dark:text-gray-400">
                تصنيف التنبيهات المسجلة في النظام
            </p>
        </div>

        <div wire:ignore>
            <div id="chart-alert-priorities" class="w-full min-h-[280px]"></div>
        </div>
    </div>

    <!-- المخطط الثاني: اتجاه المعاملات السليمة والمشبوهة (Area Chart) -->
    <div class="lg:col-span-2 p-6 bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700">
        <div class="flex flex-wrap items-center justify-between gap-2 mb-4">
            <div>
                <h3 class="text-base font-bold text-gray-900 dark:text-white">
                    نشاط المعاملات وحالات الحظر
                </h3>
                <p class="text-xs text-gray-500 dark:text-gray-400">
                    مقارنة المعاملات المعتمدة بالمعاملات المحظورة أو قيد المراجعة
                </p>
            </div>
            <div class="flex items-center gap-2">
                <select wire:model.live="daysRange" class="text-xs rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white py-1.5 px-3">
                    <option value="7">آخر 7 أيام</option>
                    <option value="14">آخر 14 يوماً</option>
                    <option value="30">آخر 30 يوماً</option>
                </select>
                <button
                    wire:click="exportExcel"
                    wire:loading.attr="disabled"
                    class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-emerald-600 hover:bg-emerald-700 disabled:opacity-50 text-white text-xs font-semibold rounded-lg shadow-sm transition cursor-pointer"
                >
                    <span wire:loading.remove wire:target="exportExcel">تصدير Excel</span>
                    <span wire:loading wire:target="exportExcel">جاري التجهيز...</span>
                </button>
            </div>
        </div>

        <div wire:ignore>
            <div id="chart-transaction-trends" class="w-full min-h-[280px]"></div>
        </div>
    </div>
</div>

@script
<script>
    let priorityChart = null;
    let trendChart = null;

    function renderCharts(priorityData, trendData) {
        if (typeof window.ApexCharts !== 'function') return;

        // 1. تهيئة مخطط الأولويات (Donut)
        const priorityEl = document.querySelector('#chart-alert-priorities');
        if (priorityEl) {
            if (priorityChart) priorityChart.destroy();
            priorityChart = new window.ApexCharts(priorityEl, {
                chart: {
                    type: 'donut',
                    height: 290,
                    fontFamily: 'inherit'
                },
                series: priorityData.series,
                labels: priorityData.labels,
                colors: ['#dc2626', '#f97316', '#eab308', '#3b82f6'],
                legend: {
                    position: 'bottom',
                    fontSize: '12px'
                },
                dataLabels: {
                    enabled: true
                },
                plotOptions: {
                    pie: {
                        donut: {
                            size: '65%'
                        }
                    }
                }
            });
            priorityChart.render();
        }

        // 2. تهيئة مخطط اتجاه المعاملات (Area)
        const trendEl = document.querySelector('#chart-transaction-trends');
        if (trendEl) {
            if (trendChart) trendChart.destroy();
            trendChart = new window.ApexCharts(trendEl, {
                chart: {
                    type: 'area',
                    height: 290,
                    toolbar: { show: false },
                    fontFamily: 'inherit'
                },
                series: [
                    {
                        name: 'معاملات معتمدة',
                        data: trendData.approved
                    },
                    {
                        name: 'محظورة / قيد المراجعة',
                        data: trendData.flagged
                    }
                ],
                colors: ['#10b981', '#ef4444'],
                dataLabels: { enabled: false },
                stroke: { curve: 'smooth', width: 2 },
                xaxis: {
                    categories: trendData.categories
                },
                fill: {
                    type: 'gradient',
                    gradient: {
                        shadeIntensity: 1,
                        opacityFrom: 0.4,
                        opacityTo: 0.05
                    }
                },
                legend: {
                    position: 'top'
                }
            });
            trendChart.render();
        }
    }

    // الرسم الأولي عند تحميل الصفحة
    renderCharts($wire.priorityData, $wire.transactionTrends);

    // تحديث المخططات عند تغيير النطاق الزمني
    $wire.on('update-charts', (payload) => {
        const data = Array.isArray(payload) ? payload[0] : payload;
        renderCharts(data.priority, data.trends);
    });
</script>
@endscript