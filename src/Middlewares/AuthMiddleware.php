<?php

declare(strict_types=1);

namespace App\Middlewares;

use App\Models\Department;
use App\Models\User;
use Laminas\Diactoros\Response;
use Laminas\Diactoros\Response\RedirectResponse;
use Psr\Http\Message\ServerRequestInterface as ServerRequest;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface as RequestHandler;

class AuthMiddleware extends Middleware implements MiddlewareInterface
{
    /** @inheritdoc */
    public function process(ServerRequest $request, RequestHandler $handler): Response|RedirectResponse
    {
        $user = $this->session->pull('user');
        $user = User::newQuery()->where(['id' => $user['id'] ?? false])->get();

        if (empty($user)) {
            $this->session->set('message', [
                'type' => 'is-warning',
                'body' => 'Você deve fazer login primeiro!',
            ]);

            return new RedirectResponse('/login');
        }

        if ($user['status'] == false) {
            $this->session->set('message', [
                'type' => 'is-warning',
                'body' => 'Sua conta encontra-se inativa',
            ]);

            return new RedirectResponse('/login');
        }

        if ($user['department_id']) {
            $user['department'] = Department::newQuery()->where([
                'id' => $user['department_id'],
                'status' => true,
            ])->get() ?: [];
        }

        $this->session->set('user', filter_protected($user, User::$protected));

        return $handler->handle($request);
    }
}
