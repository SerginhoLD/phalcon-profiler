<?php
declare(strict_types=1);

namespace SerginhoLD\Phalcon\WebProfiler\View;

/**
 * @property-read View view
 */
class Compiler extends \Phalcon\Mvc\View\Engine\Volt\Compiler
{
    protected function getFinalPath(string $path): string
    {
        /**
         * @psalm-suppress PossiblyNullReference
         * @psalm-suppress UndefinedInterfaceMethod
         */
        return $this->view->preparePath((string)parent::getFinalPath($path));
    }
}
