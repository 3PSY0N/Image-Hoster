<?php

namespace App\Controllers;

use App\Core\Twig;
use App\Handlers\ImgHandler;
use App\Handlers\UserHandler;
use App\Services\Flash;
use App\Services\Session;
use Pagerfanta\Adapter\ArrayAdapter;
use Pagerfanta\Pagerfanta;
use App\Services\PagerView;
use Siler\Http\Request;

class UserProfile
{
    private $maxPerPage = 16;
    /** @var Flash */
    private $flash;
    /** @var ImgHandler */
    private $imgHandler;
    /** @var UserHandler */
    private $userHandler;
    /** @var Twig */
    private $twig;

    public function __construct()
    {
        $this->twig        = new Twig();
        $this->flash       = new Flash();
        $this->imgHandler  = new ImgHandler();
        $this->userHandler = new UserHandler();
    }

    /**
     * @throws \Twig\Error\LoaderError
     * @throws \Twig\Error\RuntimeError
     * @throws \Twig\Error\SyntaxError
     */
    public function displayUserProfile()
    {
        Session::checkUserIsConnected();
        $getImageList = $this->imgHandler->getImagesFromUserModel(base64_decode(Session::get('userSlug')));
        $view       = new PagerView();
        $adapter    = new ArrayAdapter($getImageList);
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
            'flashMsg'    => $this->flash->getFlash(),
            'imagesList'  => $currentPageResults,
            'profileLink' => Session::get('userName'),
            'isConnected' => Session::get('isConnected'),
            'pagination'  => $pagination,
            'currentPage' => $currentPage
        ]);

        $this->flash->clear();
    }

    public function logout()
    {
        $msg = new Flash();
        $msg->setFlash('success', 'You are now logged out, see you soon !', null, false);
        Session::checkUserIsConnected();
        Session::logout();
    }
}