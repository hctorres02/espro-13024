<?php

declare(strict_types=1);

namespace App\Controllers;

use Laminas\Diactoros\Response;

class ErrorController extends Controller
{
    public function error403(): Response
    {
        return $this->view('error', [
            'code' => 403,
            'message' => 'Acesso negado'
        ], 403);
    }

    public function error404(): Response
    {
        return $this->view('error', [
            'code' => 404,
            'message' => 'Não encontrado'
        ], 404);
    }

    public function error500(): Response
    {
        return $this->view('error', [
            'code' => 500,
            'message' => 'Error interno do servidor'
        ], 500);
    }
}
