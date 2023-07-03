<?php
use Phalcon\Autoload\Loader;
use Phalcon\Di\FactoryDefault;
use Phalcon\Mvc\Application;
use SerginhoLD\Phalcon\WebProfiler\WebProfiler;

set_error_handler(function (int $errno, string $message, string $file, int $line) {
    if (!(error_reporting() & $errno)) {
        return false;
    }

    switch ($errno) {
        case E_NOTICE:
        case E_WARNING:
            throw new \ErrorException(message: $message, severity: $errno, filename: $file, line: $line);
    }

    return false;
});

$rootPath = dirname(__DIR__);

(new Loader())
    ->setNamespaces([
        'App' => $rootPath . '/src',
        'SerginhoLD\Phalcon\WebProfiler' => realpath(__DIR__ . '/../../../src'),
    ])
    ->register()
;

try {
    $container = new FactoryDefault();
    $config = require_once $rootPath . '/config/config.php';

    $container->setShared('config', function () use ($config) {
        return $config;
    });

    $container->loadFromYaml($rootPath . '/config/services.yaml', [
        '!rootPath' => static function (string $filePath) use ($rootPath) {
            // https://www.php.net/manual/en/yaml.callbacks.parse.php
            return $rootPath . $filePath;
        },
        '!config' => function (string $path) use ($config) {
            return $config->path($path);
        },
    ]);

    $container->register(new WebProfiler()); // if dev

    $container->setShared('stopwatch', function () use ($container) {
        return $container->has('profilerStopwatch') ? $container->getShared('profilerStopwatch') : null;
    });

    $application = new Application($container);
    $application->setEventsManager($container->getShared('eventsManager'));
    $application->handle($_SERVER['REQUEST_URI'])->send();
} catch (\Throwable $e) {
    echo '<pre>'; var_dump($e); echo '</pre>';
}
