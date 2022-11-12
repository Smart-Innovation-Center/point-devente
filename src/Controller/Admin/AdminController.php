<?php

namespace App\Controller\Admin;

use App\Controller\ServicePermissionController;
use App\Controller\Admin\RequestController;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Finder\Finder;

class AdminController extends AbstractController
{
    private $session;
    private $apiController;
    private $permission;

    public function __construct(SessionInterface $session,ServicePermissionController $permission)
    {
        $this->apiController=new RequestController();
        $this->permission= $permission;

        $this->session = $session;
    }
    //menu
    private function menu(){
        $menu= [
            "1" => array ('dashborad','ok')
        ];
        return $menu;
    }

    // vue connexion
    #[Route('/', name: 'admin')]
    public function index(): Response
    {
        $this->permission->userAccess();
        return $this->render("login.html.twig");
    }

    // recuperation des stats du dashboard
    #[Route('/dashboardStats', name: 'dashboardStats')]
    public function data(): Response
    {

        $databd=$this->apiController->doGet("statistics");
        $response = new Response(json_encode($databd));
        return $response;
    }

    // recuperation des fichiers logs
    #[Route('/logs', name: 'logs')]
    public function logs(Request $request)
    {
        $finder = new Finder();
        $finder->files()->name('*.log')->in('assets/logs');
        $tab="Les fichiers existants : </br>";
        foreach ($finder as $file) {
            $tab = $tab." ".$file->getRelativePathname().'</br>';
        }
        die($tab);
    }

    // recuperation d'un fichier log
    #[Route('/logs/{logname}', name: 'log')]
    public function log(Request $request,$logname)
    {
       if (file_exists('assets/logs/log_'.$logname.'.log')) {
           $json = file_get_contents('assets/logs/log_' . $logname . '.log');
           $json = implode("</br>{", explode("{", $json));
           die($json);
       }else{
           $finder = new Finder();
           $finder->files()->name('*.log')->in('assets/logs');
           $tab="Les fichiers existants : </br>";
           foreach ($finder as $file) {
               $tab = $tab." ".$file->getRelativePathname().'</br>';
           }
           die($tab);
       }

    }

    // recuperation d'un fichier log
    #[Route('/superadmin', name: 'superadmin')]
    public function superadmin(Request $request): Response
    {

        $sendApi=$this->apiController->doConnectSuperadmin("admin/login") ;

        foreach ($_POST as $k=> $v){
            unset($_POST[$k]);
        }
        if ($sendApi<300){

            $this->permission->writeLog('Connexion du super admin');
            return $this->redirectToRoute('accueil');
        }else{
            if (isset($sendApi['message'])) {
                $sendApi = $sendApi['message'];
            }
            $this->session->set('flash', 'error');
            $this->addFlash('error',$sendApi);
            $this->permission->writeLog('Connexion échouee pour l\'utilisateur du super admin erreur :'.$sendApi);

            return $this->redirectToRoute('admin');
        }

    }

    // deconnexion
    #[Route('/logout', name: 'logout')]
    public function logout(): Response
    {
        $this->permission->writeLog('Deconnexion de l\'utilisateur '.$this->session->get('username'));
        $this->session->clear();
        unset($this->session);
        return $this->redirectToRoute('admin');
    }

    // connexion
    #[Route('/admin/home', name: 'login',methods:'post')]
    public function login(Request $request): Response
    {
        $username=$request->get('username');
        $password=$request->get('password');

        $body=[
            'password'=> $password,
            'username'=>$username
        ];

        $sendApi=$this->apiController->doConnect("web-login",$body) ;

        foreach ($_POST as $k=> $v){
            unset($_POST[$k]);
        }
        if ($sendApi<300){

            $this->permission->writeLog('Connexion de l\'utilisateur '.$username);

            return $this->redirectToRoute('accueil');
        }else{
            if (isset($sendApi['message'])) {
                $sendApi = $sendApi['message'];
            }
            $this->session->set('flash', 'error');
            $this->addFlash('error',$sendApi);
            $this->permission->writeLog('Connexion échouee pour l\'utilisateur '.$username.' erreur :'.$sendApi);

            return $this->redirectToRoute('admin');
        }
    }

    /**
     * @Route("/home",name="accueil")
    */

    // connexion
    #[Route('/home', name: 'accueil')]
    public function dashboard(): Response
    {
        $this->permission->userAccess();
        $this->permission->writeLog('Connexion reussi');

        $data= [
            "menu" => $this->menu(),
        ];
        $this->session->set('flash', 'info');
        $this->addFlash('info',"Chargement de données en cours...");
        return $this->render("admin/dashboard.html.twig",$data);
    }
}
