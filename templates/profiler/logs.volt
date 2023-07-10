{# profiler/logger #}
{% extends '@profiler/profiler.volt' %}

{% block panel %}
    <h1>Logs</h1>
    {% if items is empty %}
        <div class="border p-2">No logs. Use <code>$di->getShared('profilerLoggerAdapter')</code>.</div>
    {% else %}
        <div class="btn-group btn-group-sm mb-2">
            {% for name in buttons %}
                <a href="#" class="btn btn-secondary" data-bs-toggle="collapse" data-bs-target=".lvl-{{ name|e }}" aria-expanded="true">{{ name|e }}</a>
            {% endfor %}
        </div>
        <div class="table-responsive">
            <table class="table border">
                <thead class="table-active">
                <tr>
                    <th scope="col" style="width: 7rem">Level</th>
                    <th scope="col" style="width: 14rem">Time</th>
                    <th scope="col">Message</th>
                </tr>
                </thead>
                <tbody>
                {% for idx, item in items %}
                    <tr class="collapse show lvl-{{ item['level']|e }}">
                        <td>
                            <span class="badge text-bg-light">{{ item['level']|e }}</span>
                        </td>
                        <td>{{ item['datetime'].format('c') }}</td>
                        <td>
                            <div class="block-break-all mb-2 text-light-emphasis">{{ item['message']|e }}</div>
                            <a class="me-2 text-decoration-none" data-bs-toggle="collapse" href="#collapseContext_{{ idx }}" role="button" aria-expanded="false">
                                context
                            </a>
                            <a class="text-decoration-none" data-bs-toggle="collapse" href="#collapseTrace_{{ idx }}" role="button" aria-expanded="false">
                                trace
                            </a>
                            <div class="mt-2 collapse" id="collapseContext_{{ idx }}">
                                <pre class="mb-0">{{ dump(item['context']) }}</pre>
                            </div>
                            <div class="mt-2 collapse" id="collapseTrace_{{ idx }}">
                                {{ profiler_dump(item['backtrace']) }}
                            </div>
                        </td>
                    </tr>
                {% endfor %}
                </tbody>
            </table>
        </div>
    {% endif %}
{% endblock %}
