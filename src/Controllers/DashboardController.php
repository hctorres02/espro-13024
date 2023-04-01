<?php

declare(strict_types=1);

namespace App\Controllers;

use Laminas\Diactoros\Response;

class DashboardController extends Controller
{
    public function __invoke(): Response
    {
        $departments = $this->department->where([
            'status' => true,
        ]);

        $users = $this->user->where([
            'is_super' => 0,
            'status' => true,
        ]);

        $posts = $this->post->where([
            'status' => ['draft', 'published'],
        ]);

        $warnings = $this->warning->where([
            'status' => ['draft', 'published'],
        ]);

        return $this->view('dashboard', [
            'title' => 'Visão geral',
            'departments' => $departments->count(),
            'users' => $users->count(),
            'posts' => $posts->count(),
            'warnings' => $warnings->count(),
        ]);
    }
}
