<div class="p-6 bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700">
    <div class="flex flex-wrap items-center justify-between gap-4 mb-4">
        <div>
            <h3 class="text-lg font-bold text-gray-900 dark:text-white">
                شبكة تتبع العلاقات والتحويلات المالية
            </h3>
            <p class="text-sm text-gray-500 dark:text-gray-400">
                عرض الحساب والمستفيدين والأجهزة المرتبطة من قاعدة البيانات الحقيقية.
            </p>
        </div>

        <div class="flex flex-wrap items-center gap-3">
            <!-- قائمة اختيار الحساب قيد الفحص -->
            <select wire:model.live="accountId" class="text-xs rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white py-2 px-3">
                @foreach($availableAccounts as $acc)
                    <option value="{{ $acc['id'] }}">
                        {{ $acc['account_number'] }} ({{ number_format($acc['balance'], 0) }})
                    </option>
                @endforeach
            </select>

            <!-- زر تصدير التقرير PDF -->
            <button
                wire:click="exportAlertPdf"
                wire:loading.attr="disabled"
                class="inline-flex items-center gap-2 px-4 py-2 bg-red-600 hover:bg-red-700 disabled:opacity-50 text-white text-xs font-semibold rounded-lg shadow-sm transition cursor-pointer"
            >
                <span wire:loading.remove wire:target="exportAlertPdf">تصدير تقرير (PDF)</span>
                <span wire:loading wire:target="exportAlertPdf">جاري التوليد...</span>
            </button>
        </div>
    </div>

    <!-- مفتاح الألوان -->
    <div class="flex flex-wrap items-center gap-4 text-xs font-medium mb-3">
        <span class="flex items-center gap-1.5 text-red-600">
            <span class="w-3 h-3 rounded-full bg-red-600 inline-block"></span> الحساب قيد التحقيق
        </span>
        <span class="flex items-center gap-1.5 text-amber-500">
            <span class="w-3 h-3 rounded-full bg-amber-500 inline-block"></span> مستفيد عالي الخطورة
        </span>
        <span class="flex items-center gap-1.5 text-blue-600">
            <span class="w-3 h-3 rounded-full bg-blue-600 inline-block"></span> مستفيد اعتيادي
        </span>
        <span class="flex items-center gap-1.5 text-purple-600">
            <span class="w-3 h-3 rounded-full bg-purple-600 inline-block"></span> جهاز مستخدم
        </span>
        <span class="flex items-center gap-1.5 text-orange-600 font-bold">

            <span class="w-3 h-3 rounded-full bg-orange-600 inline-block"></span> حساب مرتبط
        </span> 
    </div>

    <!-- حاوية الرسم -->

    <div wire:ignore>
        <div id="cy-network" class="w-full h-[520px] bg-gray-50 dark:bg-gray-900 rounded-lg border border-gray-200 dark:border-gray-700"></div>
    </div>

    <!-- شريط عرض التفاصيل عند النقر -->
    <div id="cy-node-details" class="mt-4 p-3 bg-gray-100 dark:bg-gray-700/50 rounded-lg text-sm text-gray-700 dark:text-gray-200 hidden">
    </div>
</div>

@script
<script>
    let cyInstance = null;

    function initCytoscape(elementsData) {
        const container = document.getElementById('cy-network');
        if (!container || typeof window.cytoscape !== 'function') return;

        if (cyInstance) {
            cyInstance.destroy();
        }

        cyInstance = window.cytoscape({
            container: container,
            elements: elementsData,
            style: [
                {
                    selector: 'node',
                    style: {
                        'label': 'data(label)',
                        'text-valign': 'bottom',
                        'text-margin-y': 6,
                        'font-size': '11px',
                        'color': '#374151',
                        'text-wrap': 'wrap',
                        'width': 42,
                        'height': 42,
                        'background-color': '#2563eb',
                        'border-width': 2,
                        'border-color': '#ffffff'
                    }
                },
                {
                    selector: 'node[type = "suspect"]',
                    style: {
                        'background-color': '#dc2626',
                        'width': 54,
                        'height': 54,
                        'font-weight': 'bold'
                    }
                },
                {
                    selector: 'node[type = "high_risk"]',
                    style: {
                        'background-color': '#f59e0b',
                        'width': 46,
                        'height': 46
                    }
                },
                {
                    selector: 'node[type = "device"]',
                    style: {
                        'background-color': '#7c3aed',
                        'shape': 'round-rectangle',
                        'width': 40,
                        'height': 40
                    }
                },
                {
                    selector: 'node[type = "linked_account"]',
                    style: {
                        'background-color': '#ea580c',
                        'width': 48,
                        'height': 48,
                        'border-width': 4,
                        'border-color': '#fed7aa',
                        'font-weight': 'bold'
                    }
                },
                {
                    selector: 'edge',
                    style: {
                        'width': 2,
                        'line-color': '#9ca3af',
                        'target-arrow-color': '#9ca3af',
                        'target-arrow-shape': 'triangle',
                        'curve-style': 'bezier',
                        'label': 'data(amount)',
                        'font-size': '10px',
                        'text-rotation': 'autorotate',
                        'text-background-color': '#ffffff',
                        'text-background-opacity': 0.85,
                        'text-background-padding': '2px'
                    }
                },
                {
                    selector: 'edge[?is_flagged]',
                    style: {
                        'width': 3,
                        'line-color': '#ef4444',
                        'target-arrow-color': '#ef4444',
                        'line-style': 'dashed'
                    }
                }
            ],
            layout: {
                name: 'cose',
                animate: true,
                padding: 40,
                nodeRepulsion: 8500
            }
        });

        cyInstance.on('tap', 'node, edge', function (evt) {
            const data = evt.target.data();
            const detailsBox = document.getElementById('cy-node-details');
            detailsBox.classList.remove('hidden');

            const title = data.source ? `<strong>تفاصيل التحويل:</strong>` : `<strong>${data.label.replace('\n', ' - ')}:</strong>`;
            detailsBox.innerHTML = `${title} ${data.details ?? ''}`;
        });
    }

    initCytoscape($wire.graphElements);

    $wire.on('refresh-graph', (event) => {
        initCytoscape(event.elements);
    });
</script>
@endscript