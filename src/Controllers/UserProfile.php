<?php

namespace App\Controllers;

use App\Core\Twig;
use App\Models\ImgModel;
use App\Models\UserModel;
use App\Services\Flash;
use App\Services\Session;
use Pagerfanta\Adapter\ArrayAdapter;
use Pagerfanta\Pagerfanta;
use App\Services\PagerView;
use Siler\Http\Request;

class UserProfile extends Twig
{
    private $maxPerPage = 5;

    public function displayUserProfile()
    {

        Session::checkUserIsConnected();

        $view       = new PagerView();
        $imgModel   = new ImgModel();
        $msg        = new Flash();
        $adapter    = new ArrayAdapter($imgModel->getImagesFromUser(base64_decode(Session::get('userSlug'))));
        $pagerfanta = new Pagerfanta($adapter);

        $getPage     = (int)Request\get('page');
        $currentPage = $getPage ? $getPage : 1;

        $pagerfanta->setNormalizeOutOfRangePages(true)
                   ->setMaxPerPage($this->maxPerPage)
                   ->setCurrentPage($currentPage);

        $currentPageResults = $pagerfanta->getCurrentPageResults();

        $options = [
            'proximity'    => 1,
            'prev_message' => '<i class="fas fa-chevron-left"></i>',
            'next_message' => '<i class="fas fa-chevron-right"></i>',
        ];

        $routeGenerator = function ($currentPage) {
            return '/profile?page=' . $currentPage;
        };

        $pagination = $view->render($pagerfanta, $routeGenerator, $options);

        echo $this->twig->render('profile.twig', [
            'imagesList'  => $currentPageResults,
            'profileLink' => Session::get('userName'),
            'isConnected' => Session::get('isConnected'),
            'pagination'  => $pagination
        ]);

        $msg->clear();
    }

    public function logout()
    {
        $msg = new Flash();
        $msg->setFlash('success', 'You are now logged out, see you soon !', false);
        Session::checkUserIsConnected();
        Session::logout();
    }
}