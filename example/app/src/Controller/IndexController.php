<?php
declare(strict_types=1);

namespace App\Controller;

use Phalcon\Mvc\Controller;
use SerginhoLD\Phalcon\WebProfiler\Service\Stopwatch;

/**
 * @property-read Stopwatch|null $stopwatch
 */
class IndexController extends Controller
{
    /**
     * @Get('/', name='home')
     */
    public function indexAction(): void
    {
        $this->stopwatch?->start('metric');

        // data
        usleep(rand(500, 1000));

        $this->stopwatch?->stop('metric');

        // render
    }

    /**
     * @Get('/test', name='test')
     */
    public function testAction(): void
    {
        throw new \LogicException('Test');
    }
}
