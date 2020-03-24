<?php

namespace App\Controllers;

use App\Core\Twig;
use App\Services\Flash;
use App\Services\Session;
use Gitlab\Client;

class GitStatus
{
    private $token = GITLAB_API_TOKEN;
    private $projectId = GITLAB_PROJECT_ID;
    private $flash;
    private $twig;

    public function __construct()
    {
        $this->twig = new Twig();
        $this->flash = new Flash();
    }

    public function showCommits()
    {
        try {
            $client = Client::create('https://gitlab.com');
            $client->authenticate($this->token, Client::AUTH_URL_TOKEN);

            $repository = $client->repositories();

            $getLastMergeRequest = $client->mergeRequests()->all($this->projectId)[0];
            $getLastCommits      = $repository->commits($this->projectId);

        } catch (\Exception $e) {
            $this->flash->setFlash('info', 'Dev Status is unavailable at this time.', 'Dev Status', false, '/');
        }

        echo $this->twig->render('gitstatus.twig', [
            'profileLink'      => Session::get('userName'),
            'isConnected'      => Session::get('isConnected'),
            'lastCommits'      => $getLastCommits,
            'lastMergeRequest' => $getLastMergeRequest
        ]);
    }
}

