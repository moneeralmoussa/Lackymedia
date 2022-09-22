<?php

// LogoutListener.php - Change the namespace according to the location of this class in your bundle
namespace UserBundle\Listeners;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Http\Logout\LogoutHandlerInterface;
use FOS\UserBundle\Model\UserManagerInterface;
use Carbon\Carbon;
use Doctrine\ORM\EntityManager;

class LogoutListener implements LogoutHandlerInterface {
    protected $userManager;
    protected $em;
    public function __construct(UserManagerInterface $userManager, EntityManager $em){
        $this->userManager = $userManager;
        $this->em = $em;
    }

    public function logout(Request $Request, Response $Response, TokenInterface $Token) {

        $user = $Token->getUser();
        $user->setLastLogout(new Carbon());
        $this->em->persist($user);
        $this->em->flush();
    }
}
