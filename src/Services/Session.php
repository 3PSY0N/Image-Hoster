<?php

namespace App\Services;

class Session
{
    public static function start()
    {
        if (!session_id()) {
            session_start();
        }
    }

    public static function logout()
    {
        if (isset($_GET['logout']) && Session::get('isConnected')) {
            foreach ($_SESSION as $key => $value) {
                if ($key == 'flashMsg') continue;
                self::destroy($key);
            }

            Toolset::redirect('/');
        }
    }

    /**
     * @param $param
     * @return mixed
     */
    public static function get($param = null)
    {
        if (isset($_SESSION[$param])) {
            return $_SESSION[$param];
        }

        return null;
    }

    /**
     * @param $param
     */
    public static function destroy($param)
    {
        if (isset($_SESSION[$param])) {
            unset($_SESSION[$param]);
        }
    }

    /**
     * @param $param
     * @param $value
     */
    public static function update($param, $value)
    {
        $_SESSION[$param] = $value;
    }

    /**
     * @param bool $status
     */
    public static function setAdmin($status = false)
    {
        self::set('isAdmin', $status);
    }

    /**
     * @param $param
     * @param $value
     */
    public static function set($param, $value)
    {
        if (!isset($_SESSION[$param])) {
            $_SESSION[$param] = $value;
        }
    }

    /**
     * @param bool $status
     */
    public static function setConnected($status = false)
    {
        self::set('isConnected', $status);
    }

    public static function checkUserIsConnected()
    {
        if (!self::isConnected()) {
            Toolset::redirect('/');
        }
    }

    /**
     * @return bool
     */
    public static function isConnected()
    {
        if (isset($_SESSION['isConnected'])) {
            return $_SESSION['isConnected'] === true;
        }

        return false;
    }

    public static function checkUserIsAdmin()
    {
        if (!self::isAdmin()) {
            Toolset::redirect('/');
        }
    }

    /**
     * @return bool
     */
    public static function isAdmin()
    {
        if (isset($_SESSION['isAdmin'])) {
            return $_SESSION['isAdmin'] === true;
        }

        return false;
    }
}