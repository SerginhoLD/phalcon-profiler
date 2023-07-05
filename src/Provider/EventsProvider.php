<?php
declare(strict_types=1);

namespace SerginhoLD\Phalcon\WebProfiler\Provider;

use Phalcon\Di\DiInterface;
use Phalcon\Di\InjectionAwareInterface;
use Phalcon\Di\ServiceProviderInterface;
use Phalcon\Events\EventInterface;
use Phalcon\Events\Manager;
use Phalcon\Http\ResponseInterface;
use Phalcon\Mvc\RouterInterface;
use Phalcon\Mvc\ViewBaseInterface;
use SerginhoLD\Phalcon\WebProfiler\Collector;
use SerginhoLD\Phalcon\WebProfiler\Service\Manager as Profiler;

class EventsProvider implements ServiceProviderInterface
{
    private \DateTimeInterface $requestTime;

    private string $profilerTag;

    public function register(DiInterface $di): void
    {
        if (!$di->has('eventsManager')) {
            return;
        }

        $managerService = $di->getService('eventsManager');
        $managerDefinition = $managerService->getDefinition();

        if ($managerService->isResolved()) {
            throw new \RuntimeException('Service "eventsManager" is resolved');
        }

        $events = [
            // profiler
            ['application:boot', $this, 1024],
            ['view:beforeRender', $this, 1024],
            ['application:beforeSendResponse', $this, 0],
            // request
            ['application:beforeSendResponse',  Collector\RequestCollector::class],
            // performance
            ['application:beforeSendResponse', Collector\PerformanceCollector::class],
            ['application:boot', Collector\PerformanceCollector::class],
            ['application:beforeHandleRequest', Collector\PerformanceCollector::class],
            ['dispatch:beforeDispatch', Collector\PerformanceCollector::class, 1024],
            ['dispatch:afterBinding', Collector\PerformanceCollector::class, 1024],
            ['db:beforeQuery', Collector\PerformanceCollector::class],
            ['db:afterQuery', Collector\PerformanceCollector::class],
            ['view:beforeCompile', Collector\PerformanceCollector::class],
            ['view:afterCompile', Collector\PerformanceCollector::class],
            // db
            ['db:beforeQuery', Collector\DbCollector::class],
            ['db:afterQuery', Collector\DbCollector::class],
            // view
            ['view:afterCompile', Collector\ViewCollector::class],
        ];

        foreach ($events as $event) {
            [$name, $obj] = $event;
            $priority = $event[2] ?? Manager::DEFAULT_PRIORITY;
            $paramType = is_object($obj) ? 'parameter' : 'service';
            $paramName = is_object($obj) ? 'value' : 'name';

            $managerDefinition['calls'][] = [
                'method' => 'attach',
                'arguments' => [
                    ['type' => 'parameter', 'value' => $name],
                    ['type' => $paramType, $paramName => $obj],
                    ['type' => 'parameter', 'value' => $priority],
                ],
            ];
        }

        $managerService->setDefinition($managerDefinition);
    }

    public function boot(EventInterface $event, InjectionAwareInterface $app): bool
    {
        $this->requestTime = new \DateTimeImmutable();
        $this->profilerTag = uniqid();
        return true;
    }

    public function beforeRender(EventInterface $event, ViewBaseInterface $view): bool
    {
        $view->setVar('_profilerTag', $this->profilerTag);
        return true;
    }

    public function beforeSendResponse(EventInterface $event, InjectionAwareInterface $app, ResponseInterface $response): void
    {
        /** @var RouterInterface $router */
        $router = $app->getDI()->getShared('router');

        if (str_starts_with($router->getMatchedRoute()?->getName() ?? '', '_profiler')) {
            return;
        }

        $response->setHeader('X-Profiler-Tag', $this->profilerTag);

        /** @var Profiler $profiler */
        $profiler = $app->getDI()->getShared(Profiler::class);
        $profiler->save($this->profilerTag, $this->requestTime, $app, $response);
    }
}
