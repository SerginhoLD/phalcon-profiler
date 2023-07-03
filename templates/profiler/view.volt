{# profiler/view #}
{% extends '@profiler/profiler.volt' %}

{% block panel %}
    <h1>View</h1>
    <div class="table-responsive">
        <table class="table table-bordered">
            <thead class="table-active">
            <tr>
                <th scope="col">Active render paths</th>
            </tr>
            </thead>
            <tbody>
            {% for path in activeRenderPaths %}
                <tr>
                    <td><code>{{ path }}</code></td>
                </tr>
            {% endfor %}
            </tbody>
        </table>
    </div>
{% endblock %}
