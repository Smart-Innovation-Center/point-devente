<?php

namespace App\Controller\Parametres;

use App\Controller\ServicePermissionController;
use App\Controller\Admin\RequestController;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Session\SessionInterface;

class RoleController extends AbstractController
{
    public $apiController;
    public $config;
    private $session;
    private $permission;

    public function __construct(SessionInterface $session,ServicePermissionController $permission)
    {       
        $this->apiController=new RequestController();
        $this->permission= $permission;
        $this->session = $session;

        $json=file_get_contents('assets/json/path.json');
        $this->config=json_decode($json,true);
    }

    // GESTION DES ROLES
    //recuperer la liste des roles
    private function data()
    {                          
        $this->permission->userAccess();
        try {  
            $databd=$this->apiController->doGet("roles") ;
            return $databd;
        } catch (\Exception $e) {
            $this->permission->writeLog($e->getMessage());
            $this->session->set('flash', 'error');
            $this->addFlash('error',"Une erreur est survenue impossible de charger les données !");

            return [];
        }       
    }

    //recuperer un seuls roles
    public function dataSelect($id)
    {                          
        $this->permission->userAccess();
        try {  
            $databd=$this->apiController->doGet("role/{$id}") ;
            return $databd;
        } catch (\Exception $e) {
            $this->permission->writeLog($e->getMessage());
            $this->session->set('flash', 'error');
            $this->addFlash('error',"Une erreur est survenue impossible de charger les données !");

            return [
                'id'=> $id,
                'libelle'=> $libelle,
                'description'=> $description
            ];
        }     
    }

    //recuperer la liste des type de visites
    private function typevisite()
    {                          
        $this->permission->userAccess();
        try {  
            $databd=$this->apiController->doGet("type-visites") ;
            return $databd;
        } catch (\Exception $e) {
            $this->permission->writeLog($e->getMessage());
            $this->session->set('flash', 'error');
            $this->addFlash('error',"Une erreur est survenue impossible de charger les données !");

            return [];
        }
    }

    //recuperer la liste des actions
    private function action()
    {                          
        $this->permission->userAccess();
        try {  
            $databd=$this->apiController->doGet("permissions") ;
            return $databd;
        } catch (\Exception $e) {
            $this->permission->writeLog($e->getMessage());
            $this->session->set('flash', 'error');
            $this->addFlash('error',"Une erreur est survenue impossible de charger les données !");

            return [];
        }
    }

    //menu
    private function menu(){
        $menu= [
            "1" => array ('parametre','role')
        ]; 
        return $menu;
    }

    #[Route('/role', name: 'role')]
    public function role(): Response
    {
        $data= [
            "databd" => $this->data(),
            "action" => $this->action(),
            "typevisite" => $this->typevisite(),
            "tab" => 'lister',
            "menu" => $this->menu(),
        ];
        $this->permission->writeLog('liste des rôles');
        return $this->render("parametres/role.html.twig",$data);
    }

    #[Route('/role/ajouter', name: 'addrole')]
    public function addrole(Request $request): Response
    {
        if ($request->getMethod()=='POST') {
            
            $this->permission->userAccess();
                                
            //recupération des infos à enregistrer           
            $libelle=$request->get('libelle');
            $description=$request->get('description');
            $action=$request->get('action');
            $listevisite=$request->get('listevisite');
            $connexion=$request->get('connexion');

            $body=[
                'role'=> $libelle,
                'description'=> $description,
                'permissions'=> $action,
                'type_visites'=> $listevisite,
                'typeCompte'=> $connexion,
            ];

            try {
                $sendApi=$this->apiController->doPost("roles",$body,'Rôle ajouté avec succès');

                if($sendApi < 300){

                    $this->addFlash('info',$this->session->get('response'));
                    $this->session->set('flash', 'info');
                    $this->permission->writeLog('Ajouter un rôle');
                }
                else{
                    $this->session->set('flash', 'error');
                    $this->addFlash('error',"Erreur  d'ajoute de role  ");
                    $this->permission->writeLog("Erreur  d'ajoute de role ");
                }

                return $this->redirectToRoute('role');
                
            } catch (\Exception $e) {
                $this->addFlash('error',$this->session->get('response'));
                $this->session->set('flash', 'error');
                $this->permission->writeLog($e->getMessage());

                return $this->redirectToRoute('addrole');
            }

        }else {
            $data= [
                "action" => $this->action(),
                "typevisite" => $this->typevisite(),
                "tab" => 'ajouter',
                "menu" => $this->menu(),
                "flash" => true
            ]; 
            return $this->render("parametres/role.html.twig",$data);  
        }
    }

    #[Route('/role/modifier/{id}', name: 'editrole')]
    public function editrole(Request $request,$id): Response
    {
        if ($request->getMethod()=='POST') {
            
            $this->permission->userAccess();           

            //recupération des infos à enregistrer
            $libelle=$request->get('libelle');
            $description=$request->get('description');
            $action=$request->get('action');
            $listevisite=$request->get('listevisite');
            $connexion=$request->get('connexion');

            $body=[
                'role'=> $libelle,
                'description'=> $description,
                'permissions'=> $action,
                'type_visites'=> $listevisite,
                'typeCompte'=> $connexion,
            ];

            try {
                $sendApi=$this->apiController->doPut("role/{$id}/update",$body,'Rôle modifié avec succès');

                if($sendApi < 300){
                    $this->session->set('flash', 'info');
                    $this->addFlash('info',$this->session->get('response'));
                    $this->permission->writeLog('Modifier un rôle identifiant :'.$id);
                }
                else{

                    $this->session->set('flash', 'error');
                    $this->addFlash('error',"Erreur  de modification de role  ");
                    $this->permission->writeLog("Erreur  de modification  de role ".$id);
                }

                return $this->redirectToRoute('role');

            } catch (\Exception $e) {
                $this->addFlash('error',$this->session->get('response'));
                $this->session->set('flash', 'error');
                $this->permission->writeLog($e->getMessage());

                return $this->redirectToRoute('editrole',array('id' => $id));
            }
        }else {
            $data= [
                "dataSelect" => $this->dataSelect($id),
                "action" => $this->action(),
                "typevisite" => $this->typevisite(),
                "tab" => 'modifier',
                "menu" => $this->menu(),
            ]; 
            return $this->render("parametres/role.html.twig",$data);            

        }
    }

    #[Route('/role/supprimer/{id}', name: 'delrole')]
    public function delrole(Request $request,$id): Response
    {
        if ($request->getMethod()=='POST') {
            
            $this->permission->userAccess();

            try {
                $sendApi=$this->apiController->doDelete("role/{$id}/delete",'Rôle supprimé avec succès');

                if ($sendApi < 300 ){

                    $this->session->set('flash', 'info');
                    $this->addFlash('info',$this->session->get('response'));
                    $this->permission->writeLog('Supprimer un rôle identifiant :'.$id);
                }
                else{

                    $this->session->set('flash', 'error');
                    $this->addFlash('error',"Erreur  de suppression  de role  ");
                    $this->permission->writeLog("Erreur  de suppression  de role ".$id);
                }

                return $this->redirectToRoute('role');

            } catch (\Exception $e) { 

                $this->addFlash('error',$this->session->get('response'));
                $this->session->set('flash', 'error');
                $this->permission->writeLog($e->getMessage());

                return $this->redirectToRoute('role');
            }
        }
    }

    #[Route('/role/desactiver/{id}', name: 'desactiverrole')]
    public function desactiverrole(Request $request,$id): Response
    {
        if ($request->getMethod()=='POST') {
            
            $this->permission->userAccess();
            try {
                $sendApi=$this->apiController->doActivet("role/{$id}/desactivate",'Rôle desactivé avec succès');

                if($sendApi < 300){

                    $this->addFlash('info',$this->session->get('response'));
                    $this->session->set('flash', 'info');
                    $this->permission->writeLog('Desactivation rôle identifiant :'.$id);
                }
                else{

                    $this->session->set('flash', 'error');
                    $this->addFlash('error',"Erreur  de desactivation   de role  ");
                    $this->permission->writeLog("Erreur  de desactivation   de role ".$id);
                }

               return $this->redirectToRoute('role');

           } catch (\Exception $e) { 

               $this->addFlash('error',$this->session->get('response'));
               $this->session->set('flash', 'error');
               $this->permission->writeLog($e->getMessage());

               return $this->redirectToRoute('role');
           }
        }
    }

    #[Route('/role/activer/{id}', name: 'activerrole')]
    public function activerrole(Request $request,$id): Response
    {
        if ($request->getMethod()=='POST') {
            
            $this->permission->userAccess();
            try {
                $sendApi=$this->apiController->doActivet("role/{$id}/activate",'Rôle activé avec succès');

                if($sendApi < 300){

                    $this->addFlash('info',$this->session->get('response'));
                    $this->session->set('flash', 'info');
                    $this->permission->writeLog('Activation rôle : données : '.$id);
                }
                else{

                    $this->session->set('flash', 'error');
                    $this->addFlash('error',"Erreur  d'activation   de role  ");
                    $this->permission->writeLog("Erreur  d'activation   de role ".$id);
                }

               return $this->redirectToRoute('role');

           } catch (\Exception $e) { 

               $this->addFlash('error',$this->session->get('response'));
               $this->session->set('flash', 'error');
               $this->permission->writeLog($e->getMessage());

               return $this->redirectToRoute('role');
           }
        }
    }
}
