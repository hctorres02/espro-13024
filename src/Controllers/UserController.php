<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Request;
use Laminas\Diactoros\Response;
use Laminas\Diactoros\Response\RedirectResponse;
use Laminas\Diactoros\ServerRequest;
use League\Route\Http\Exception\ForbiddenException;
use League\Route\Http\Exception\NotFoundException;

class UserController extends Controller
{
    public function index(): Response
    {
        $users = $this->user->where([
            'is_super' => false,
            'status' => true,
        ])->select([
            'id',
            'name',
        ]);

        $cta = [
            'icon' => 'fa-plus',
            'label' => 'Inserir novo',
            'url' => '/aprendizes/cadastrar'
        ];

        return $this->view('users', [
            'title' => 'Aprendizes',
            'users' => $users,
            'cta' => $cta,
        ]);
    }

    public function create(): Response
    {
        if (can('create') == false) {
            throw new ForbiddenException;
        }

        $departments = $this->department->where([
            'status' => true,
        ])->select(['id', 'name']);

        return $this->view('_template/admin', [
            '_form' => 'user',
            'title' => 'Cadastrar aprendiz',
            'departments' => $departments,
        ]);
    }

    public function store(ServerRequest $request): RedirectResponse
    {
        if (can('create') == false) {
            throw new ForbiddenException;
        }

        $body = $this->validate($request);

        if (empty($body)) {
            return $this->redirect('/aprendizes/cadastrar', 'Erro no preenchimento do formulário', 'is-danger');
        }

        $user_id = $this->user->insert($body);

        return $this->redirect("/aprendizes/{$user_id}", 'Aprendiz cadastrado com sucesso', 'is-success');
    }

    public function edit(ServerRequest $request, array $args)
    {
        $user = $this->user->where([
            'id' => $args['id'],
            'status' => true,
        ])->get();

        if (empty($user)) {
            throw new NotFoundException;
        }

        if ($user['id'] != $this->session->get('user')['id'] && can('update', $user['department_id']) == false) {
            throw new ForbiddenException;
        }

        $departments = $this->department->where([
            'status' => true,
        ])->select(['id', 'name']);

        return $this->view('_template/admin', [
            '_form' => 'user',
            'title' => 'Editar aprendiz',
            'departments' => $departments,
            'user' => $user,
        ]);
    }

    public function update(ServerRequest $request, array $args): RedirectResponse
    {
        $user = $this->user->where([
            'id' => $args['id'],
            'status' => true,
        ])->get(['id', 'department_id']);

        if (empty($user)) {
            throw new NotFoundException;
        }

        if ($user['id'] != $this->session->get('user')['id'] && can('update', $user['department_id']) == false) {
            throw new ForbiddenException;
        }

        $body = $this->validate($request, $user['id']);

        if (empty($body)) {
            return $this->redirect("/aprendizes/{$user['id']}", 'Erro no preenchimento do formulário', 'is-danger');
        }

        if ($this->user->update($body, ['id' => $user['id']]) == false) {
            return $this->redirect("/aprendizes/{$user['id']}", 'Ocorreu um erro ao tentar atualizar aprendiz', 'is-warning');
        }

        return $this->redirect("/aprendizes/{$user['id']}", 'Aprendiz editado com sucesso', 'is-success');
    }

    public function destroy(ServerRequest $request, array $args): RedirectResponse
    {
        $user = $this->user->where([
            'id' => $args['id'],
            'status' => true
        ])->get(['id', 'name']);

        if (empty($user)) {
            return throw new NotFoundException;
        }

        if (can('destroy') == false) {
            throw new ForbiddenException;
        }

        $this->user->newQuery()->update([
            'department_id' => null,
            'is_super' => false,
            'status' => false,
        ], ['id' => $user['id']]);

        $this->department->newQuery()->update([
            'owner_id' => null,
        ], ['owner_id' => $user['id']]);

        return $this->redirect('/departartamentos', "Aprendiz '{$user['name']}' removido!");
    }

    private function validate(ServerRequest $request, ?int $id = null): bool|array
    {
        $rules = [
            'name' => fn ($v) => $v->alpha(' ', 'á', 'ã', 'â', 'é', 'ê', 'í', 'ó', 'ô', 'õ', 'ú')->length(4, 60),
            'department_id' => fn ($v, $value) => $v->equals($this->department->newQuery()->where(['id' => $value])->get('id')),
            'phone' => fn ($v, $value, $field) => $v->phone('BR')->equals($this->user->newQuery()->isUnique($field, $value, $id)),
            'email' => fn ($v, $value, $field, $v2) => $v->optional($v2->email()),
            'birth_date' => fn ($v, $value, $field, $v2) => $v->optional($v2->date()),
        ];

        if (empty($id)) {
            $rules['password'] = fn ($v, $value, $field, $v2) => $v->length(6, 18);
        }

        $body = Request::validate($request, $rules);

        if (empty($body)) {
            return false;
        }

        $body['name'] = ucwords(strtolower($body['name']));

        if (empty($body['department_id'])) {
            $body['department_id'] = null;
        }

        if (empty($body['password'])) {
            unset($body['password']);
        } else {
            $body['password'] = password_hash($body['password'], PASSWORD_DEFAULT);
        }

        if (empty($body['email'])) {
            $body['email'] = null;
        } else {
            $body['email'] = strtolower($body['email']);
        }

        if (empty($body['birth_date'])) {
            $body['birth_date'] = null;
        }

        return $body;
    }
}
