<?php

namespace Config;

use CodeIgniter\Config\BaseService;

/**
 * Services Configuration file.
 *
 * Services are simply other classes/libraries that the system uses
 * to do its job. This is used by CodeIgniter to allow the core of the
 * framework to be swapped out easily without affecting the usage within
 * the rest of your application.
 *
 * This file holds any application-specific services, or service overrides
 * that you might need. An example has been included with the general
 * method format you should use for your service methods. For more examples,
 * see the core Services file at system/Config/Services.php.
 */
class Services extends BaseService
{
    /**
     * Serviço de criptografia da ouvidoria.
     */
    public static function encryption($getShared = true)
    {
        if ($getShared) {
            return static::getSharedInstance('encryption');
        }
        return new \App\Services\EncryptionService();
    }

    /**
     * Serviço de autorização da ouvidoria.
     */
    public static function authorization($getShared = true)
    {
        if ($getShared) {
            return static::getSharedInstance('authorization');
        }
        return new \App\Services\AuthorizationService();
    }

    /**
     * Serviço de SLA.
     */
    public static function sla($getShared = true)
    {
        if ($getShared) {
            return static::getSharedInstance('sla');
        }
        return new \App\Services\SlaService();
    }

    /**
     * Serviço de e-mail da ouvidoria.
     */
    public static function emailOuvidoria($getShared = true)
    {
        if ($getShared) {
            return static::getSharedInstance('emailOuvidoria');
        }
        return new \App\Services\EmailService();
    }
}
