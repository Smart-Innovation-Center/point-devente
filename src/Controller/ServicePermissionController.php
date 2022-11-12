<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Session\SessionInterface;

class ServicePermissionController extends AbstractController
{

    private $session;
    private $url;

    public function __construct(SessionInterface $session)
    {
        $this->session = $session;
    }

    // #[Route('/service/permission', name: 'app_service_permission')]
    public function index(): Response
    {
        return $this->render('service_permission/index.html.twig', [
            'controller_name' => 'ServicePermissionController',
        ]);
    }

    public function userAccess()
    {

        // $this->session = new Session();
        $route_name = str_replace('/' , '' , $_SERVER['REQUEST_URI']);

        $inactive = 1800;
        if (empty($this->session->get('timeToOut'))){
            $this->session->set('timeToOut', time());
        }

        $session_life = time() - $this->session->get('timeToOut');

        if($session_life > $inactive)
        {
            $this->session->set('timeToOut', '');
            $this->session->set('flashConnect','ok');
        }

        $this->session->set('timeToOut', time());


        if ($route_name !== '' && empty($_POST)){

            if(!empty($this->session->get('ApiToken')))
            {
                $route_name1=substr($route_name, 0, 11);
                $route_name2=substr($route_name, 0, 13);

                $roleUser=$this->session->get('monRole');

                switch ($roleUser) {
                    case 'SUPER ADMIN':
                     /*   if ($route_name2 == 'prevalisation')
                        {
                            $this->session->set('flash', 'error');
                            $this->addFlash('error','Vous n\'avez pas accès à cette interface !');
                            die($this->redirectToRoute('accueil'));
                        }*/
                        break;
                    case 'SENIOR MANAGER':

                        if ($route_name1 !== 'home' && $route_name1 !== 'historiques')
                        {
                            $this->session->set('flash', 'error');
                            $this->addFlash('error','Vous n\'avez pas accès à cette interface autre !');
                            die($this->redirectToRoute('historiques'));
                        }
                        break;
                    case 'COORDINATION DRDI':

                        if ($route_name1 !== 'home' && $route_name1 !== 'historiques')
                        {
                            $this->session->set('flash', 'error');
                            $this->addFlash('error','Vous n\'avez pas accès à cette interface autre !');
                            die($this->redirectToRoute('historiques'));
                        }
                        break;
                    case 'BOOM':
                        if ($route_name1 !== 'home' && $route_name2 !== 'prevalisation')
                        {
                            $this->session->set('flash', 'error');
                            $this->addFlash('error','Vous n\'avez pas accès à cette interface autre !');
                            die($this->redirectToRoute('prevalisation'));
                        }

                        break;
                    case 'MANAGER':

                        if ($route_name1 !== 'home' && $route_name1 !== 'historiques')
                        {
                            $this->session->set('flash', 'error');
                            $this->addFlash('error','Vous n\'avez pas accès à cette interface autre !');
                            die($this->redirectToRoute('historiques'));
                        }
                        break;

                    default:

                        $this->session->set('flash', 'error');
                        $this->addFlash('error','Vous n\'avez pas accès à cette interface autre 6 !');
                        die($this->redirectToRoute('logout'));
                        break;
                }


            }else {
                $this->session->set('flash', 'error');
                $this->addFlash('error','Votre session a expirer veillez-vous connecter !');
                die($this->redirectToRoute('logout'));
            }
        }
        if ($route_name == '' && !empty($this->session->get('username'))){
            die($this->redirectToRoute('accueil'));
        }




    }

    public function writeLog($log_message = ''){

        $ip = $_SERVER['REMOTE_ADDR'];
        $hostname = gethostbyaddr($_SERVER['REMOTE_ADDR']);
        $request_method = $_SERVER["REQUEST_METHOD"];
        $user_agent = $_SERVER['HTTP_USER_AGENT'];
        $route_name = $_SERVER['REQUEST_URI'];

        $log="{".date('H:i:s')."} IP: {$ip} | HOST:{$hostname} | METHOD:{$request_method} | URI:{$route_name} | ";
        if(strtolower($_SERVER["REQUEST_METHOD"]) == 'get'){
            $log.='Données GET :'.json_encode($_GET).' | ';
        }elseif(strtolower($_SERVER["REQUEST_METHOD"]) == 'post'){
            $log.='Données POST :'.json_encode($_POST).' | ';
        }
        if(!empty($_SESSION['user_username'])){
            $log.="user_username: {$_SESSION['user_username']} | MESSAGE>>> ";
        }
        $log.="{$log_message}".PHP_EOL;
        //Save string to log, use FILE_APPEND to append.
        file_put_contents('./assets/logs/log_'.date("j.n.Y").'.log', $log, FILE_APPEND);
        return 'log written';
    }
}
