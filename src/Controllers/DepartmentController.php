<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Request;
use Laminas\Diactoros\Response;
use Laminas\Diactoros\Response\RedirectResponse;
use Laminas\Diactoros\ServerRequest;
use League\Route\Http\Exception\ForbiddenException;
use League\Route\Http\Exception\NotFoundException;

class DepartmentController extends Controller
{
    public function index(): Response
    {
        $departments = $this->department->where([
            'status' => true,
        ])->select([
            'id',
            'name',
            'shortname',
            'is_super',
        ]);

        $cta = [
            'icon' => 'fa-plus',
            'label' => 'Cadastrar departamento',
            'url' => '/departamentos/cadastrar'
        ];

        return $this->view('departments', [
            'title' => 'Departamentos',
            'departments' => $departments,
            'cta' => $cta,
        ]);
    }

    public function create(): Response
    {
        if (can('create') == false) {
            throw new ForbiddenException;
        }

        return $this->view('_template/admin', [
            '_form' => 'department',
            'title' => 'Cadastrar departamento',
            'colors' => get_colors(),
        ]);
    }

    public function store(ServerRequest $request): RedirectResponse
    {
        if (can('create') == false) {
            throw new ForbiddenException;
        }

        $body = $this->validate($request);

        if (empty($body)) {
            return $this->redirect('/departamentos/cadastrar', 'Erro no preenchimento do formulário', 'is-danger');
        }

        unset($body['owner_id']);

        $department_id = $this->department->insert($body);

        return $this->redirect("/departamentos/{$department_id}", 'Departamento cadastrado com sucesso', 'is-success');
    }

    public function edit(ServerRequest $request, array $args)
    {
        $department = $this->department->where([
            'id' => $args['id'],
            'status' => true,
        ])->get();

        if (empty($department)) {
            throw new NotFoundException;
        }

        if (can('update', $department['id']) == false) {
            throw new ForbiddenException;
        }

        $users = $this->user->where(['department_id' => $department['id']])->select(['id', 'name']);

        return $this->view('_template/admin', [
            '_form' => 'department',
            'title' => "Editar departamento '{$department['name']}'",
            'colors' => get_colors(),
            'department' => $department,
            'users' => $users,
        ]);
    }

    public function update(ServerRequest $request, array $args): RedirectResponse
    {
        $department = $this->department->where([
            'id' => $args['id'],
            'status' => true,
        ])->get(['id']);

        if (empty($department['id'])) {
            throw new NotFoundException;
        }

        if (can('update', $department['id']) == false) {
            throw new ForbiddenException;
        }

        $body = $this->validate($request, $department['id']);

        if (empty($body)) {
            return $this->redirect("/departamentos/{$department['id']}", 'Erro no preenchimento do formulário', 'is-danger');
        }

        if (can('create') == false) {
            $body['is_super'] = false;
        }

        if ($body['is_super']) {
            $this->department->newQuery()->update(['is_super' => false]);
        }

        $this->department->newQuery()->update($body, ['id' => $department['id']]);

        return $this->redirect("/departamentos/{$department['id']}", "Departamento editado com sucesso", 'is-success');
    }

    public function destroy(ServerRequest $request, array $args): RedirectResponse
    {
        $department = $this->department->where([
            'id' => $args['id'],
            'status' => true,
        ])->get(['id', 'name', 'shortname']);

        if (empty($department)) {
            throw new NotFoundException;
        }

        if (can('destroy') == false) {
            throw new ForbiddenException;
        }

        $this->department->newQuery()->update([
            'name' => "_{$department['name']}",
            'shortname' => "_{$department['shortname']}",
            'owner_id' => null,
            'is_super' => false,
            'status' => false,
        ], ['id' => $department['id']]);

        $this->user->newQuery()->update([
            'department_id' => null,
        ], ['department_id' => $department['id']]);

        return $this->redirect('/departamentos', "Departamento '{$department['name']}' removido!");
    }

    private function validate(ServerRequest $request, ?int $id = null): bool|array
    {
        $body = Request::validate($request, [
            'name' => fn ($v, $value, $field) => $v->alpha(' ', 'á', 'ã', 'â', 'à', 'é', 'ê', 'í', 'ó', 'õ', 'ô', 'ú', 'ç')->length(3, 255)->equals($this->department->newQuery()->isUnique($field, $value, $id)),
            'shortname' => fn ($v, $value, $field) => $v->alpha()->length(2, 3)->equals($this->department->newQuery()->isUnique($field, $value, $id)),
            'owner_id' => fn ($v, $value) => $v->equals($this->user->newQuery()->where(['id' => $value, 'department_id' => $id])->get('id')),
            'color' => fn ($v) => $v->in(get_colors(true)),
            'is_super' => fn ($v) => $v->boolVal(),
        ]);

        if (empty($body)) {
            return false;
        }

        $body['name'] = ucwords(strtolower($body['name']));
        $body['shortname'] = strtoupper($body['shortname']);

        if (empty($body['owner_id'])) {
            $body['owner_id'] = null;
        }

        if (empty($body['is_super'])) {
            $body['is_super'] = false;
        }

        return $body;
    }
}
