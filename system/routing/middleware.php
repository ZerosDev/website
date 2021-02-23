<?php

namespace System\Routing;

defined('DS') or exit('No direct script access.');

use System\Str;
use System\Package;

class Middleware
{
    /**
     * Berisi list seluruh middleware yang terdaftar.
     *
     * @var array
     */
    public static $middlewares = [];

    /**
     * Berisi list middleware yang berbasis pola URI.
     *
     * @var array
     */
    public static $patterns = [];

    /**
     * Berisi list alias untuk setiap middleware yang terdaftar.
     *
     * @var array
     */
    public static $aliases = [];

    /**
     * Daftarkan sebuah middleware.
     *
     * <code>
     *
     *      // Daftarkan sebuah middleware via closure
     *      Middleware::register('before', function() { });
     *
     *      // Daftarkan sebuah middleware via callback
     *      Middleware::register('before', ['ClassName', 'method']);
     *
     * </code>
     *
     * @param string $name
     * @param mixed  $callback
     */
    public static function register($name, $callback)
    {
        if (isset(static::$aliases[$name])) {
            $name = static::$aliases[$name];
        }

        if (Str::starts_with($name, 'pattern: ')) {
            $patterns = explode(', ', substr($name, 9));

            foreach ($patterns as $pattern) {
                static::$patterns[$pattern] = $callback;
            }
        } else {
            static::$middlewares[$name] = $callback;
        }
    }

    /**
     * Buat nama alias untuk sebuah middleware agar bisa dipanggil dengan nama lain.
     * Ini memudahkan untuk memperpendek pemanggilan middleware bawaan sebuah paket.
     *
     *
     * @param string $middleware
     * @param string $alias
     */
    public static function alias($middleware, $alias)
    {
        static::$aliases[$alias] = $middleware;
    }

    /**
     * Parse definisi middleware ke bentuk array.
     *
     * @param string|array $middlewares
     *
     * @return array
     */
    public static function parse($middlewares)
    {
        if (is_string($middlewares)) {
            return explode('|', $middlewares);
        }

        return (array) $middlewares;
    }

    /**
     * Panggil satu atau beberapa middleware.
     *
     * @param array $collections
     * @param array $pass
     * @param bool  $override
     *
     * @return mixed
     */
    public static function run($collections, $pass = [], $override = false)
    {
        foreach ($collections as $collection) {
            foreach ($collection->middlewares as $middleware) {
                list($middleware, $parameters) = $collection->get($middleware);

                Package::boot(Package::name($middleware));

                if (! isset(static::$middlewares[$middleware])) {
                    continue;
                }

                $callback = static::$middlewares[$middleware];
                $response = call_user_func_array($callback, array_merge($pass, $parameters));

                if (! is_null($response) && $override) {
                    return $response;
                }
            }
        }
    }
}
