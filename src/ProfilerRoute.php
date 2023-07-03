<?php
declare(strict_types=1);

namespace SerginhoLD\Phalcon\WebProfiler;

use Phalcon\Mvc\Router\Route;

class ProfilerRoute extends Route
{
    public function setProfilerController(string $namespace, string $controller): static
    {
        $this->paths['namespace'] = $namespace;
        $this->paths['controller'] = $controller;
        return $this;
    }

    public function setProfilerAction(string $name): static
    {
        $this->paths['action'] = $name;
        return $this;
    }
}
