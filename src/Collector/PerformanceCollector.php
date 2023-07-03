<?php
declare(strict_types=1);

namespace SerginhoLD\Phalcon\WebProfiler\Collector;

use Phalcon\Db\Adapter\AdapterInterface;
use Phalcon\Dispatcher\AbstractDispatcher;
use Phalcon\Events\EventInterface;
use Phalcon\Http\ResponseInterface;
use Phalcon\Mvc\Application;
use Phalcon\Mvc\View\Engine\AbstractEngine;
use SerginhoLD\Phalcon\WebProfiler\Service\Stopwatch;

class PerformanceCollector implements CollectorInterface
{
    private float $maxScale = 0;

    public function __construct(private Stopwatch $stopwatch) {}

    public function templatePath(): string
    {
        return '@profiler/profiler/performance';
    }

    public function name(): string
    {
        return 'Performance';
    }

    public function icon(): string
    {
        return <<<HTML
<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-bar-chart-steps" viewBox="0 0 16 16">
    <path d="M.5 0a.5.5 0 0 1 .5.5v15a.5.5 0 0 1-1 0V.5A.5.5 0 0 1 .5 0zM2 1.5a.5.5 0 0 1 .5-.5h4a.5.5 0 0 1 .5.5v1a.5.5 0 0 1-.5.5h-4a.5.5 0 0 1-.5-.5v-1zm2 4a.5.5 0 0 1 .5-.5h7a.5.5 0 0 1 .5.5v1a.5.5 0 0 1-.5.5h-7a.5.5 0 0 1-.5-.5v-1zm2 4a.5.5 0 0 1 .5-.5h6a.5.5 0 0 1 .5.5v1a.5.5 0 0 1-.5.5h-6a.5.5 0 0 1-.5-.5v-1zm2 4a.5.5 0 0 1 .5-.5h7a.5.5 0 0 1 .5.5v1a.5.5 0 0 1-.5.5h-7a.5.5 0 0 1-.5-.5v-1z"/>
</svg>
HTML;
    }

    public function collect(): array
    {
        $data = ['labels' => ['']];

        foreach ($this->stopwatch->events() as $name => $events) {
            $dataset = ['labelShort' => $name];
            $sumDuration = 0;

            foreach ($events as $event) {
                $dataset['data'][] = ['y' => '', 'x' => [$event->start, $event->stop], 'duration' => $event->duration];
                $sumDuration += $event->duration;
            }

            $dataset['label'] = $name . ': ' . $sumDuration . ' ms';
            $data['datasets'][] = $dataset;
        }

        return [
            'data' => $data,
            'maxScale' => $this->maxScale,
        ];
    }

    public function beforeSendResponse(EventInterface $event, Application $app, ResponseInterface $response): void
    {
        $this->maxScale = $this->stopwatch->now($this->stopwatch->origin());
    }

    public function boot(EventInterface $event, Application $app): bool
    {
        $this->stopwatch->start('router');
        return true;
    }

    public function beforeHandleRequest(EventInterface $event, Application $app): bool
    {
        $this->stopwatch->stop('router');
        return true;
    }

    public function beforeDispatch(EventInterface $event, AbstractDispatcher $dispatcher): bool
    {
        $this->stopwatch->start('dispatch');
        return true;
    }

    public function afterBinding(EventInterface $event, AbstractDispatcher $dispatcher): bool
    {
        $this->stopwatch->stop('dispatch');
        return true;
    }

    public function beforeQuery(EventInterface $event, AdapterInterface $conn): bool
    {
        $this->stopwatch->start('db');
        return true;
    }

    public function afterQuery(EventInterface $event, AdapterInterface $conn): void
    {
        $this->stopwatch->stop('db');
    }

    public function beforeCompile(EventInterface $event, AbstractEngine $engine): bool
    {
        $this->stopwatch->start('view');
        return true;
    }

    public function afterCompile(EventInterface $event, AbstractEngine $engine): bool
    {
        $this->stopwatch->stop('view');
        return true;
    }
}
