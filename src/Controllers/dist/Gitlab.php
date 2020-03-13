<?php

namespace App\Controllers\dist;

use App\Core\Twig;
use App\Services\Session;
use Gitlab\Client;

class Gitlab
{
    private $token = 'PRIVATE_TOKEN';
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

        $getIssues           = $client->issues()->all();
        $getLastMergeRequest = $client->mergeRequests()->all($this->projectId)[0];
        $getLastRelease      = $repository->releases($this->projectId);
        $getLastCommits      = array_slice($repository->commits($this->projectId), 0, 20);

        $this->twig->render('/git/gitlab_commits.twig', [
            'profileLink'      => Session::get('userName'),
            'isConnected'      => Session::get('isConnected'),
            'lastCommits'      => $getLastCommits,
            'lastMergeRequest' => $getLastMergeRequest
        ]);
    }
}

