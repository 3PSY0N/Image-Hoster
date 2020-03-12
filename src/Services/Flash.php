<?php

namespace App\Services;

class Flash
{

    private $redirectUrl;

    /**
     * @return string|null
     */
    public function getFlash()
    {
        if (Session::get('flashMsg')) {

            $flashMsg = '';

            foreach (Session::get('flashMsg') as $flash) {
                $autoClose = ($flash['autoClose']) ? 'autoclose' : null;

                $flashMsg .= '<div class="alert ' . $autoClose . ' alert-' . $flash['color'] . ' fade show" role="alert">';
                $flashMsg .= $flash['message'];

                if (is_null($autoClose)) {
                    $flashMsg .= '<button type="button" class="close" data-dismiss="alert" aria-label="Close">';
                    $flashMsg .= '<span aria-hidden="true">&times;</span>';
                    $flashMsg .= '</button>';
                }

                $flashMsg .= '</div>';
            }

            return $flashMsg;
        }

        return null;
    }

    /**
     * @param $color
     * @param $message
     * @param bool $dismissBtn
     * @param null $redirectUrl
     */
    public function setFlash($color, $message, $dismissBtn = false, $redirectUrl = null)
    {
        static $no_calls = 0;
        ++$no_calls;
        $_SESSION['flashMsg'][$no_calls]['color']     = $color;
        $_SESSION['flashMsg'][$no_calls]['message']   = $message;
        $_SESSION['flashMsg'][$no_calls]['dismiss']   = ($dismissBtn ? 'alert-dismissible' : null);
        $_SESSION['flashMsg'][$no_calls]['autoClose'] = false;

        if (!$dismissBtn) {
            $_SESSION['flashMsg'][$no_calls]['autoClose'] = true;
        }

        if (!is_null($redirectUrl)) {
            Toolset::redirect($redirectUrl);
        }
    }

    public function clear()
    {
        Session::destroy('flashMsg');
    }
}