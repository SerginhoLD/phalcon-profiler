<?php
declare(strict_types=1);

namespace SerginhoLD\Phalcon\WebProfiler\View;

class Compiler extends \Phalcon\Mvc\View\Engine\Volt\Compiler
{
    protected function getFinalPath(string $path): string
    {
        return $this->view->preparePath((string)parent::getFinalPath($path));
    }
}
