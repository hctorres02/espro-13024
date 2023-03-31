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

    public static function validate(ServerRequest $request, array $rules, array $defaults = [])
    {
        $body = array_merge($defaults, $request->getParsedBody());
        $session = Session::getInstance();

        foreach ($rules as $field => $rule) {
            if (is_array($rule) == false) {
                $rule = [$rule];
            }

            foreach ($rule as $i => $validator) {
                $value = $body[$field] ?? null;

                if (($i == 0 && $validator(new Validator)->validate($value)) || ($i > 0 && $validator($field, $value))) {
                    continue;
                }

                $session->replace([
                    'validation' => array_merge($session->get('validation', []), [$field => true]),
                ]);

                break;
            }
        }

        if ($session->get('validation')) {
            return false;
        }

        return $body;
    }
}
