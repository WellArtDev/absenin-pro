<?php

class TenantMiddleware
{
    public static function fromJwt(object $jwt): string
    {
        if (empty($jwt->tenant_id)) {
            Response::unauthorized('Tenant context tidak ditemukan');
        }
        return $jwt->tenant_id;
    }

    public static function fromSession(): string
    {
        return Auth::requireSession()['tenant_id'];
    }
}
