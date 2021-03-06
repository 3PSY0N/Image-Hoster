<?php

namespace App\Services;

class Flash
{

    /**
     * @return string|null
     */
    public function getFlash()
    {
        if (Session::get('flashMsg')) {

            $flashMsg = '';

            foreach (Session::get('flashMsg') as $flash) {
                $autoClose = ($flash['autoClose']) ? 'autoclose' : null;

                $flashMsg .= '<div class="callout alert ' . $autoClose . ' ' . $flash['color'] . ' fade show" role="alert">';

                if (is_null($autoClose)) {
                    $flashMsg .= '<button type="button" class="close" data-dismiss="alert" aria-label="Close">';
                    $flashMsg .= '<span aria-hidden="true">&times;</span>';
                    $flashMsg .= '</button>';
                }

                if ($flash['title']) {
                    $flashMsg .= '<span class="title">' . $flash['title'] . '</span>';
                }

                $flashMsg .= '<span>' . $flash['message'] . '</span>';
                $flashMsg .= '</div>';
            }

            return $flashMsg;
        }

        return null;
    }

    /**
     * @param $color
     * @param $message
     * @param null $title
     * @param bool $dismiss
     * @param null $redirectUrl
     */
    public function setFlash($color, $message, $title = null, $dismiss = false, $redirectUrl = null)
    {
        static $no_calls = 0;
        ++$no_calls;
        $_SESSION['flashMsg'][$no_calls]['color']     = $color;
        $_SESSION['flashMsg'][$no_calls]['title']     = $title;
        $_SESSION['flashMsg'][$no_calls]['message']   = $message;
        $_SESSION['flashMsg'][$no_calls]['dismiss']   = ($dismiss ? 'alert-dismissible' : null);
        $_SESSION['flashMsg'][$no_calls]['autoClose'] = false;

        if (!$dismiss) {
            $_SESSION['flashMsg'][$no_calls]['autoClose'] = true;
        }

        if (!is_null($redirectUrl)) {
            Toolset::redirect($redirectUrl);
        }
    }

    public function getToast()
    {
        if (Session::get('toastMsg')) {

            $toastMsg = '<div class="toaster">';

            foreach (Session::get('toastMsg') as $toast) {
                $toastMsg .= '<div role="alert" aria-live="assertive" aria-atomic="true" class="toast callout ml-auto ' . $toast['color'] . '" data-delay="5000" data-autohide="true">';
                if ($toast['title']) {
                    $toastMsg .= '<span class="title">' . $toast['title'] . '</span>';
                }
                $toastMsg .= $toast['message'];
                $toastMsg .= '</div>';
            }
            $toastMsg .= '</div>';
            return $toastMsg;
        }

        return null;
    }

    /**
     * @param $color
     * @param $message
     * @param null $title
     * @param null $redirectUrl
     */
    public function setToast($color, $message, $title = null, $redirectUrl = null)
    {
        static $no_calls = 0;
        ++$no_calls;
        $_SESSION['toastMsg'][$no_calls]['color']     = $color;
        $_SESSION['toastMsg'][$no_calls]['title']     = $title;
        $_SESSION['toastMsg'][$no_calls]['message']   = $message;

        if (!is_null($redirectUrl)) {
            Toolset::redirect($redirectUrl);
        }
    }

    public function clear()
    {
        Session::destroy('flashMsg');
        Session::destroy('toastMsg');
    }
}