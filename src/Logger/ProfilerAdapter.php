<?php
declare(strict_types=1);

namespace SerginhoLD\Phalcon\WebProfiler\Logger;

use Phalcon\Events\ManagerInterface;
use Phalcon\Logger\Adapter\AbstractAdapter;
use Phalcon\Logger\Item;

class ProfilerAdapter extends AbstractAdapter
{
    public function __construct(private ManagerInterface $eventsManager) {}

    public function process(Item $item): void
    {
        $this->eventsManager->fire('profiler:log', $this, [
            'item' => $item,
            'backtrace' => array_slice(debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS), 2),
        ]);
    }

    public function close(): bool
    {
        return true;
    }
}
