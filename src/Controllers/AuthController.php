<?php

declare(strict_types=1);

namespace App\Controllers;

use Laminas\Diactoros\Response;
use Laminas\Diactoros\Response\RedirectResponse;
use Laminas\Diactoros\ServerRequest;

class AuthController extends Controller
{
    public function index(): Response|RedirectResponse
    {
        if ($this->session->get('user')) {
            return $this->redirect('/dashboard');
        }

        return $this->view('login');
    }

    public function store(ServerRequest $request): RedirectResponse
    {
        $body = array_merge([
            'phone' => '',
            'password' => ''
        ], $request->getParsedBody());

        $user = $this->user->where([
            'phone' => $body['phone'],
        ])->get(['id', 'password', 'status']);

        if (empty($user) || password_verify($body['password'], $user['password']) == false) {
            return $this->redirect('/login', 'Dados de login incorretos', 'is-danger');
        }

        if ($user['status'] == false) {
            return $this->redirect('/login', 'Sua conta encontra-se inativa', 'is-warning');
        }

        $this->session->set('user', ['id' => $user['id']]);

        return $this->redirect('/dashboard');
    }

    public function destroy(): RedirectResponse
    {
        $this->session->clear();

        return $this->redirect('/login', 'Você foi desconectado');
    }
}
