{# profiler/performance #}
{% extends '@profiler/profiler.volt' %}

{% block panel %}
    <h1>Performance</h1>
    <div class="row gx-3 mb-4">
        <div class="col-auto">
            <div class="card">
                <div class="card-body">
                    <div class="card-title text-info-emphasis text-center">Total execution time</div>
                    <div class="card-text text-light-emphasis text-center fs-5">{{ maxScale }}&nbsp;ms</div>
                </div>
            </div>
        </div>
    </div>
    <div class="mb-4">
        <div class="card">
            <div class="card-body">
                <canvas id="performance"></canvas>
            </div>
        </div>
    </div>
{% endblock %}

{% block js %}
    {{ this.profilerAssets.outputInlineFile('@profiler/templates/assets/chart.4.3.0.umd.js') }}
    <script>
        const ctx = document.getElementById('performance');

        document.addEventListener('DOMContentLoaded', () => {
            const data = {{ data|json_encode }};
            ctx.height = 20 + data.datasets.length * 10;

            new Chart(ctx, {
                type: 'bar',
                data: data,
                options: {
                    indexAxis: 'y',
                    animation: false,
                    elements: {
                        bar: {
                            borderSkipped: false,
                            borderWidth: 1
                        }
                    },
                    scales: {
                        x: {
                            max: {{ maxScale }},
                        }
                    },
                    plugins: {
                        legend: {
                            labels: {
                                color: '#adb5bd'
                            }
                        },
                        tooltip: {
                            callbacks: {
                                label: function(context) {
                                    return context.dataset.labelShort + ': ' + context.dataset.data[context.dataIndex].duration + ' ms';
                                }
                            }
                        }
                    }
                }
            });
        });
    </script>
{% endblock %}
