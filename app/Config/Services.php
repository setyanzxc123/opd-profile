<?php

namespace Config;

use App\Services\DashboardAdminService;
use App\Services\NewsMediaService;
use App\Services\ProfileAdminService;
use App\Services\ProfileLocationService;
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
    public static function dashboardAdmin(bool $getShared = true): DashboardAdminService
    {
        if ($getShared) {
            return static::getSharedInstance('dashboardAdmin');
        }

        return new DashboardAdminService();
    }

    public static function profileAdmin(bool $getShared = true): ProfileAdminService
    {
        if ($getShared) {
            return static::getSharedInstance('profileAdmin');
        }

        return new ProfileAdminService();
    }

    public static function profileLocation(bool $getShared = true): ProfileLocationService
    {
        if ($getShared) {
            return static::getSharedInstance('profileLocation');
        }

        return new ProfileLocationService();
    }

    public static function newsMedia(bool $getShared = true): NewsMediaService
    {
        if ($getShared) {
            return static::getSharedInstance('newsMedia');
        }

        return new NewsMediaService();
    }

    /*
     * public static function example($getShared = true)
     * {
     *     if ($getShared) {
     *         return static::getSharedInstance('example');
     *     }
     *
     *     return new \CodeIgniter\Example();
     * }
     */
}
