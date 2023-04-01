<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Models\Department;
use App\Models\Post;
use App\Models\User;
use App\Models\Warning;
use App\Request;
use App\Session;
use Josantonius\Session\Session as SessionManager;
use Laminas\Diactoros\Response;
use Laminas\Diactoros\Response\RedirectResponse;
use Laminas\Diactoros\ServerRequest;
use Twig\Environment;
use Twig\Extra\Html\HtmlExtension;
use Twig\Extra\String\StringExtension;
use Twig\Loader\FilesystemLoader;
use Twig\TwigFunction;

class Controller
{
    private $twig;

    protected ServerRequest $request;

    protected SessionManager $session;

    protected User $user;

    protected Department $department;

    protected Warning $warning;

    protected Post $post;

    public function __construct()
    {
        $this->request = Request::getInstance();
        $this->session = Session::getInstance();

        $this->user = User::newQuery();
        $this->department = Department::newQuery();
        $this->warning = Warning::newQuery();
        $this->post = Post::newQuery();

        $this->twig = new Environment(new FilesystemLoader(ROOT . '/src/views'));
        $this->twig->addExtension(new StringExtension);
        $this->twig->addExtension(new HtmlExtension);
        $this->twig->addFunction(new TwigFunction('can', fn ($permission, $department_id = null) => can($permission, $department_id)));
    }

    protected function view(string $view, array $context = [], int $status = 200): Response
    {
        $context = array_merge($context, [
            '_user' => $this->session->get('user'),
            '_message' => $this->session->pull('message'),
            '_old' => $this->session->pull('old'),
            '_validation' => $this->session->pull('validation'),
        ]);

        $body = $this->twig->render("{$view}.twig", $context);
        $response = new Response;

        $response->getBody()->write($body);

        return $response->withStatus($status);
    }

    protected function redirect(string $uri, string $message = null, string $messageType = 'is-info'): RedirectResponse
    {
        if ($message) {
            $this->session->set('message', [
                'type' => $messageType,
                'body' => $message,
            ]);
        }

        $this->session->set('old', $this->request->getParsedBody());

        return new RedirectResponse($uri);
    }
}
