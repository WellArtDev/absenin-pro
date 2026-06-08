<?php

class AuthMiddleware
{
    public static function api(): object
    {
        return Auth::requireJwt();
    }

    public static function web(): array
    {
        return Auth::requireSession();
    }
}
