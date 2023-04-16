<?php

declare(strict_types=1);

namespace App;

use Laminas\Diactoros\ServerRequest;
use Laminas\Diactoros\ServerRequestFactory;
use Respect\Validation\Validator;

class Request
{
    private static ?ServerRequest $request = null;

    private function __construct()
    {
        //
    }

    public static function getInstance(): ServerRequest
    {
        if (self::$request === null) {
            self::$request = ServerRequestFactory::fromGlobals();
        }

        return self::$request;
    }

    public static function validate(ServerRequest $request, array $rules): array|false
    {
        $body = array_filter($request->getParsedBody(), fn ($key) => array_key_exists($key, $rules), ARRAY_FILTER_USE_KEY);
        $session = Session::getInstance();


        foreach ($rules as $field => $rule) {
            $value = $body[$field] ?? ($_FILES[$field]['tmp_name'] ?? null);

            $v = $rule(new Validator, $value, $field);

            if ($v->validate($value)) {
                $body[$field] = $value;
                continue;
            }

            $session->replace([
                'validation' => array_merge($session->get('validation', []), [$field => true]),
            ]);

            break;
        }

        if ($session->get('validation')) {
            return false;
        }

        return $body;
    }
}
