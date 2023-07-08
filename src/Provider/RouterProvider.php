<?php
declare(strict_types=1);

namespace SerginhoLD\Phalcon\WebProfiler\Provider;

use Phalcon\Config\ConfigInterface;
use Phalcon\Di\DiInterface;
use Phalcon\Di\ServiceProviderInterface;
use Phalcon\Mvc\Dispatcher;
use Phalcon\Mvc\Router;
use SerginhoLD\Phalcon\WebProfiler\Controller\ProfilerController;
use SerginhoLD\Phalcon\WebProfiler\ProfilerRoute;

class RouterProvider implements ServiceProviderInterface
{
    public function register(DiInterface $di): void
    {
        if (!$di->has('router')) {
            return;
        }

        $routerService = $di->getService('router');
        $routerDefinition = $routerService->getDefinition();

        if ($routerService->isResolved()) {
            throw new \RuntimeException('Service "router" is resolved');
        }

        foreach ($this->getRoutes($di->getShared('profilerConfig')) as $route) {
            $routerDefinition['calls'][] = [
                'method' => 'attach',
                'arguments' => [
                    ['type' => 'parameter', 'value' => $route]
                ],
            ];
        }

        $routerService->setDefinition($routerDefinition);
    }

    private function getRoutes(ConfigInterface $config): array
    {
        return [
            (new ProfilerRoute($config['routePrefix'], [
                'controller' => ProfilerController::class,
                'action' => 'indexAction',
            ], 'GET'))->beforeMatch($this->beforeMatchRoute())->setName('_profiler'),
            (new ProfilerRoute($config['routePrefix'] . '/tag/{tag}', [
                'controller' => ProfilerController::class,
                'action' => 'tagAction',
            ], 'GET'))->beforeMatch($this->beforeMatchRoute())->setName('_profiler-tag'),
            (new ProfilerRoute($config['routePrefix'] . '/bar/{tag}', [
                'controller' => ProfilerController::class,
                'action' => 'barAction',
            ], 'GET'))->beforeMatch($this->beforeMatchRoute())->setName('_profiler-bar'),
        ];
    }

    private function beforeMatchRoute(): callable
    {
        return function (string $uri, ProfilerRoute $route, Router $router) {
            /** @var Dispatcher $dispatcher */
            $dispatcher = $router->getDI()->getShared('dispatcher');
            $paths = $route->getPaths();

            // https://github.com/phalcon/cphalcon/issues/16238#issuecomment-1613262031
            if (preg_match(sprintf('/(.+)\\\\((.+)%s)$/', $dispatcher->getHandlerSuffix()), $paths['controller'], $matches)) {
                // 'ProfilerController' => 'Profiler'
                $route->setProfilerController($matches[1], $matches[3]);
            }

            // 'indexAction' => 'index'
            $route->setProfilerAction(str_replace($dispatcher->getActionSuffix(), '', (string)$paths['action']));
            return true;
        };
    }
}
