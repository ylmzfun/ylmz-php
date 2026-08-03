<?php

namespace Ylmz;

use Ylmz\Http\Request;
use Ylmz\Http\Response;

abstract class Controller
{
    protected Container $container;
    protected Request $request;
    protected Response $response;

    public function __construct(Container $container)
    {
        $this->container = $container;
        $this->request = $container->make(Request::class);
        $this->response = $container->make(Response::class);
    }

    protected function assign(string $key, mixed $value): void
    {
        $this->response->withViewData($key, $value);
    }

    protected function display(string $template): Response
    {
        $viewPath = APP_PATH . '/view/' . $template;

        if (!is_file($viewPath)) {
            $this->response->setStatusCode(500);
            $this->response->setContent("View not found: {$template}");
            return $this->response;
        }

        $loader = new \Twig\Loader\FilesystemLoader(APP_PATH . '/view/');
        $twig = new \Twig\Environment($loader, [
            'cache' => RUNTIME_PATH . '/twig/',
            'debug' => Config::getBool('APP_DEBUG', false),
        ]);

        $content = $twig->render($template, $this->response->getViewData());
        $this->response->setContent($content);

        return $this->response;
    }

    protected function json(array $data, int $code = 200): Response
    {
        return $this->response->json($data, $code);
    }

    protected function redirect(string $url, int $code = 302): Response
    {
        return $this->response->redirect($url, $code);
    }
}
