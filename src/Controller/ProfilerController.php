<?php
declare(strict_types=1);

namespace SerginhoLD\Phalcon\WebProfiler\Controller;

use Phalcon\Http\Response;
use Phalcon\Http\ResponseInterface;
use Phalcon\Mvc\Controller;
use SerginhoLD\Phalcon\WebProfiler\Service\Manager;
use SerginhoLD\Phalcon\WebProfiler\View\View;

class ProfilerController extends Controller
{
    public function indexAction(): ResponseInterface
    {
        /** @var Manager $service */
        $service = $this->getDI()->getShared(Manager::class);
        return $this->render('@profiler/profiler/requests', ['requests' => $service->requests()]);
    }

    public function tagAction(string $tag): ResponseInterface
    {
        $panel = $this->request->get('panel', null, '');

        /** @var Manager $service */
        $service = $this->getDI()->getShared(Manager::class);
        $data = $service->data($tag, $panel);

        return $this->render($data['_templatePath'], $data);
    }

    private function render(string $path, array $params): ResponseInterface
    {
        /** @var View $view */
        $view = $this->getDI()->getShared('profilerView');
        return new Response($view->render($view->preparePath($path), $params));
    }
}
