<?php

namespace App\Handlers;

use App\Models\UserModel;

class UserHandler extends UserModel
{

    public function getUserBySlug(string $slug)
    {
        return $this->getUserBySlugModel($slug);
    }

    public function getUserByEmail(string $email)
    {
        return $this->getUserByEmailModel($email);
    }

    public function getUserByApiKey(string $publicKey)
    {
        return $this->getUserByApiKeyModel($publicKey);
    }
}
