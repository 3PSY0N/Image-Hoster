<?php

namespace App\Controllers;

use App\Core\Twig;
use App\Services\Session;
use Gitlab\Client;

class GitStatus
{
    private $token = 'API_TOKEN';
    private $projectId = PROJECT_ID;
    public function __construct()
    {
        $this->twig = new Twig();
    }

    public function showCommits()
    {
        $client = Client::create('https://gitlab.com')
                ->authenticate($this->token, Client::AUTH_URL_TOKEN);

        $repository = $client->repositories();

        $getLastMergeRequest = $client->mergeRequests()->all($this->projectId)[0];
        $getLastCommits      = $repository->commits($this->projectId);
        $getIssues           = $client->issues()->all();
        $getLastRelease      = $repository->releases($this->projectId);

        $this->twig->render('gitstatus.twig', [
            'profileLink'      => Session::get('userName'),
            'isConnected'      => Session::get('isConnected'),
            'lastCommits'      => $getLastCommits,
            'lastMergeRequest' => $getLastMergeRequest
        ]);
    }
}

