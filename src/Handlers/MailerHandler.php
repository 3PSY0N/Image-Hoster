<?php

namespace App\Handlers;

use App\Services\Mailer;
use App\Services\Toolset;

class MailerHandler extends Mailer
{
    /**
     * @param $email
     * @param $regToken
     * @throws \Twig\Error\LoaderError
     * @throws \Twig\Error\RuntimeError
     * @throws \Twig\Error\SyntaxError
     */
    public function registrationMail($email, $regToken)
    {
        $this->sendmail($email, [
            'subject' => 'ImageHoster Account registration',
            'template' => 'regmail'
        ], [
            'reglink' => Toolset::siteUrl() . '/register?token=' . $regToken,
            'logoLink' => Toolset::siteUrl() . '/assets/img/logo.png'
        ]);
    }
}