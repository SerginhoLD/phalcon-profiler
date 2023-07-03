{# partials/collectors.nav #}
<div class="card">
    <div class="card-body">
        <ul class="navbar-nav">
            {% for collector in this.profilerManager.collectors() %}
                <li class="nav-item">
                    <a href="{{ url(['for': '_profiler-tag', 'tag': _tag], ['panel': collector.name()]) }}" class="nav-link icon-link {{ collector.name() === _panel ? 'active' : '' }}">
                        {{ collector.icon() }}{{ collector.name()|e }}
                    </a>
                </li>
            {% endfor %}
        </ul>
    </div>
</div>
