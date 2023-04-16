<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Request;
use Laminas\Diactoros\Response;
use Laminas\Diactoros\ServerRequest;
use League\Route\Http\Exception\ForbiddenException;
use League\Route\Http\Exception\NotFoundException;

class PostController extends Controller
{
    public function index(): Response
    {
        $posts = $this->post->join([
            '[><]departments' => ['department_id' => 'id'],
        ])->where([
            'posts.status' => ['draft', 'published'],
            'posts.published_at[<=]' => today(),
        ])->select([
            'posts.id',
            'posts.title',
            'posts.status',
            'department' => [
                'departments.shortname',
            ],
        ]);

        $cta = [
            'icon' => 'fa fa-plus',
            'label' => 'Nova postagem',
            'url' => '/postagens/cadastrar'
        ];

        return $this->view('posts', [
            'title' => 'Postagens',
            'cta' => $cta,
            'posts' => $posts,
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
            'title' => 'Nova postagem',
            '_form' => 'post',
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
            return $this->redirect('/postagens/cadastrar', 'Erro no preenchimento do formulário', 'is-danger');
        }

        $post_id = $this->post->insert($body);

        return $this->redirect("/postagens/{$post_id}", 'Postagem publicada com sucesso', 'is-success');
    }

    public function edit(ServerRequest $request, array $args)
    {
        $post = $this->post->where([
            'id' => $args['id'],
            'status' => ['draft', 'published'],
        ])->get();

        if (empty($post)) {
            throw new NotFoundException;
        }

        if (can('update', $post['department_id']) == false) {
            throw new ForbiddenException;
        }

        $departments = $this->department->where([
            'status' => true,
        ])->select(['id', 'name']);

        return $this->view('_template/admin', [
            'title' => 'Editar postagem',
            '_form' => 'post',
            'departments' => $departments,
            'statuses' => get_statuses(),
            'post' => $post,
        ]);
    }

    public function update(ServerRequest $request, array $args)
    {
        $post = $this->post->where([
            'id' => $args['id'],
            'status' => ['draft', 'published'],
        ])->get();

        if (empty($post)) {
            throw new NotFoundException;
        }

        if (can('update', $post['department_id']) == false) {
            throw new ForbiddenException;
        }

        $body = $this->validate($request, $post['id']);

        if (empty($body)) {
            return $this->redirect("/postagens/{$post['id']}", 'Erro no preenchimento do formulário', 'is-danger');
        }

        $this->post->update($body, ['id' => $post['id']]);

        return $this->redirect("/postagens/{$post['id']}", 'Postagem editada com sucesso', 'is-success');
    }

    public function destroy(ServerRequest $Request, array $args)
    {
        $post = $this->post->where([
            'id' => $args['id'],
            'status' => ['draft', 'published'],
        ])->get();

        if (empty($post)) {
            throw new NotFoundException;
        }

        if (can('destroy') == false) {
            throw new ForbiddenException;
        }

        $this->post->newQuery()->update([
            'status' => 'trash',
        ], ['id' => $post['id']]);

        return $this->redirect('/postagens', 'Postagem removida!');
    }

    private function validate(ServerRequest $request, ?int $post_id = null): array|false
    {
        $currentUser = $this->session->get('user');

        $rules = [
            'department_id' => fn ($v) => $v->equals($currentUser['department_id']),
            'title' => fn ($v) => $v->alnum(' ', 'á', 'ã', 'â', 'à', 'é', 'ê', 'í', 'ó', 'õ', 'ô', 'ú', 'ç', 'ª', 'º', '!', '@', '#', '$', '%', '?', ',', '.', ':', ';'),
            'body' => fn ($v) => $v->stringVal(),
            'status' => fn ($v) => $v->in(get_statuses(true)),
            'published_at' => fn ($v) => $v->date(),
        ];

        if (empty($post_id)) {
            $rules = array_merge($rules, [
                'author_id' => fn ($v) => $v->equals($currentUser['id']),
                'image' => fn ($v) => $v->uploaded()->image()->size(null, '1MB'),
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

        if (empty($body['image'])) {
            $body['image'] = $this->post->newQuery()->where(['id' => $post_id])->get('image');
        } else {
            $body['image'] = upload_image($body['image'], 720, 405);
        }

        return $body;
    }
}
