<?php

declare(strict_types=1);

namespace App\Controllers;

use Laminas\Diactoros\Response;

class HomeController extends Controller
{
    public function __invoke(): Response
    {
        $posts = $this->post->homePosts();
        $birthdays = $this->user->homeBirthdays();
        $warnings = $this->warning->homeWarnings();
        $departments = $this->department->homeDepartments();

        return $this->view('home', compact('warnings', 'departments', 'birthdays', 'posts'));
    }
}
