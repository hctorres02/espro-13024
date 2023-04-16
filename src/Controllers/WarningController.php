<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Request;
use Laminas\Diactoros\Response;
use Laminas\Diactoros\ServerRequest;
use League\Route\Http\Exception\ForbiddenException;
use League\Route\Http\Exception\NotFoundException;

class WarningController extends Controller
{
    public function index(): Response
    {
        $warnings = $this->warning->join([
            '[><]departments' => ['department_id' => 'id'],
        ])->where([
            'warnings.status' => ['draft', 'published'],
            'warnings.published_at[<=]' => today(),
            'warnings.expires_at[>=]' => today(),
        ])->select([
            'warnings.id',
            'warnings.title',
            'warnings.status',
            'department' => [
                'departments.shortname',
            ],
        ]);

        $cta = [
            'icon' => 'fa fa-plus',
            'label' => 'Novo comunicado',
            'url' => '/comunicados/cadastrar'
        ];

        return $this->view('warnings', [
            'title' => 'Comunicados',
            'cta' => $cta,
            'warnings' => $warnings,
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
            'title' => 'Novo comunicado',
            '_form' => 'warning',
            'departments' => $departments,
            'statuses' => get_statuses(),
        ]);
    }

    public function store(ServerRequest $request)
    {
        if (can('create') == false) {
            throw new ForbiddenException;
        }

        $body = $this->validate($request);

        if (empty($body)) {
            return $this->redirect('/comunicados/cadastrar', 'Erro no preenchimento do formulário', 'is-danger');
        }

        $warning_id = $this->warning->insert($body);

        return $this->redirect("/comunicados/{$warning_id}", 'Comunicado publicado com sucesso', 'is-success');
    }

    public function edit(ServerRequest $request, array $args)
    {
        $warning = $this->warning->where([
            'id' => $args['id'],
            'status' => ['draft', 'published'],
        ])->get();

        if (empty($warning)) {
            throw new NotFoundException;
        }

        if (can('update', $warning['department_id']) == false) {
            throw new ForbiddenException;
        }

        $departments = $this->department->where([
            'status' => true,
        ])->select(['id', 'name']);

        return $this->view('_template/admin', [
            'title' => 'Editar Comunicado',
            '_form' => 'warning',
            'departments' => $departments,
            'statuses' => get_statuses(),
            'warning' => $warning,
        ]);
    }

    public function update(ServerRequest $request, array $args)
    {
        $warning = $this->warning->where([
            'id' => $args['id'],
            'status' => ['draft', 'published'],
        ])->get();

        if (empty($warning)) {
            throw new NotFoundException;
        }

        if (can('update', $warning['department_id']) == false) {
            throw new ForbiddenException;
        }

        $body = $this->validate($request, $warning['id']);

        if (empty($body)) {
            return $this->redirect("/comunicados/{$warning['id']}", 'Erro no preenchimento do formulário', 'is-danger');
        }

        $this->warning->update($body, ['id' => $warning['id']]);

        return $this->redirect("/comunicados/{$warning['id']}", 'Comunicado editado com sucesso', 'is-success');
    }

    public function destroy(ServerRequest $Request, array $args)
    {
        $warning = $this->warning->where([
            'id' => $args['id'],
            'status' => ['draft', 'published'],
        ])->get();

        if (empty($warning)) {
            throw new NotFoundException;
        }

        if (can('destroy') == false) {
            throw new ForbiddenException;
        }

        $this->warning->newQuery()->update([
            'status' => 'trash',
        ], ['id' => $warning['id']]);

        return $this->redirect('/comunicados', 'Comunicado removido!');
    }

    private function validate(ServerRequest $request, ?int $warning_id = null): array|false
    {
        $currentUser = $this->session->get('user');

        $rules = [
            'department_id' => fn ($v) => $v->equals($currentUser['department_id']),
            'title' => fn ($v) => $v->alnum(' ', 'á', 'ã', 'â', 'à', 'é', 'ê', 'í', 'ó', 'õ', 'ô', 'ú', 'ç', 'ª', 'º', '!', '@', '#', '$', '%', '?', ',', '.', ':', ';'),
            'body' => fn ($v) => $v->stringVal()->length(20, 255),
            'status' => fn ($v) => $v->in(get_statuses(true)),
            'published_at' => fn ($v) => $v->date(),
            'expires_at' => fn ($v) => $v->date(),
        ];

        if (empty($warning_id)) {
            $rules = array_merge($rules, [
                'author_id' => fn ($v) => $v->equals($currentUser['id']),
            ]);
        }

        if ($currentUser['is_super'] || $currentUser['department']['is_super']) {
            $rules = array_merge($rules, [
                'department_id' => fn ($v, $value) => $v->equals($this->department->newQuery()->where(['id' => $value])->get('id')),
            ]);
        }

        $body = Request::validate($request, $rules);

        if (empty($body)) {
            return false;
        }

        $body['body'] = strip_tags(trim($body['body']));

        return $body;
    }
}
