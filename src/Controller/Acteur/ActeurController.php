<?php

namespace App\Controller\Acteur;

use App\Controller\ServicePermissionController;
use App\Controller\Admin\RequestController;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Session\SessionInterface;

class ActeurController extends AbstractController
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

    // GESTION DES ACTEURS
    //recuperer la liste des acteurs
    private function data($offset)
    {      
        $this->permission->userAccess();
        try {  
            $databd=$this->apiController->doGet("acteurs?offset={$offset}&limit=50") ;
            return $databd;
        } catch (\Exception $e) {
            $this->permission->writeLog($e->getMessage());
            $this->session->set('flash', 'error');
            $this->addFlash('error',"Une erreur est survenue impossible de charger les données !");

            return [];
        }
    }

    //recuperer un seul acteurs
    private function dataSelect($id)
    {            
        $this->permission->userAccess();
        try {  
            $databd=$this->apiController->doGet("acteur/{$id}") ;
            return $databd;
        } catch (\Exception $e) {
            $this->permission->writeLog($e->getMessage());
            $this->session->set('flash', 'error');
            $this->addFlash('error',"Une erreur est survenue impossible de charger les données !");

            return [
                'id'=> $id,
                'nom_prenoms'=> '',
                'contact'=> '',
                'email'=> '',
                'hierarchy'=> [],
                'type_acteur'=> '',
                'typeCompte'=> '',
                'username'=> '',
                'roles'=> ''
            ];
        }
    }

    
    //get partenaire acteur 
    #[Route('/dataPartActeur/{id}', name: 'dataPartActeur')]
    public function dataPartActeur(Request $request,$id): Response
    {        
                    
        $this->permission->userAccess();
        try {  
            $ville=$this->apiController->doGet("partenaire/{$id}/acteurs");
            $response = new Response(json_encode($ville));
            return $response;
        } catch (\Exception $e) {
            $this->permission->writeLog($e->getMessage());
            $this->session->set('flash', 'error');
            $this->addFlash('error',"Une erreur est survenue impossible de charger les données !");

            return [];
        }
    }

    //get acteur PDV
    private function dataActeurPdv($id)
    {            
        $this->permission->userAccess();
        try {  
            $databd=$this->apiController->doGet("acteur/{$id}/pdvs") ;
            return $databd;
        } catch (\Exception $e) {
            $this->permission->writeLog($e->getMessage());
            $this->session->set('flash', 'error');
            $this->addFlash('error',"Une erreur est survenue impossible de charger les données !");

            return [
                //'id'=> $id,
                'nom_prenoms'=> '',
                'contact'=> '',
                'email'=> '',
                'hierarchy'=> [],
                'type_acteur'=> '',
                'typeCompte'=> '',
                'username'=> '',
                'roles'=> ''
            ];
        }
    }

    //get acteur PDV
    #[Route('/dataActeurPdv1/{id}', name: 'dataActeurPdv1')]
    public function dataActeurPdv1(Request $request,$id): Response
    {        
                    
        $this->permission->userAccess();
        try {  
            $ville=$this->apiController->doGet("acteur/{$id}/pdvs");
            $response = new Response(json_encode($ville));
            return $response;
        } catch (\Exception $e) {
            $this->permission->writeLog($e->getMessage());
            $this->session->set('flash', 'error');
            $this->addFlash('error',"Une erreur est survenue impossible de charger les données !");

            return [];
        }
    }


    //recuperer liste des partenaires
    private function partenaire(){
                             
        $this->permission->userAccess();
        try {  
            $databd=$this->apiController->doGet("partenaires") ;
            return $databd;
        } catch (\Exception $e) {
            $this->permission->writeLog($e->getMessage());
            $this->session->set('flash', 'error');
            $this->addFlash('error',"Une erreur est survenue impossible de charger les données !");

            return [];
        }
    }
    
    //recuperer liste des roles
    private function role()
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
    
    //recuperer liste des types d'acteur
    private function typeacteur()
    {      
        $this->permission->userAccess();
        try {  
            $databd=$this->apiController->doGet("type-acteurs") ;
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
            "1" => array ('acteur','acteur')
        ]; 
        return $menu;
    }

    //liste des statuts
    private function status(){
        return $this->config["status"];
    }

    //liste des acteurs
    #[Route('/liste_acteur/{id}', name: 'listeacteurs')]
    public function  getHiarachies(Request $request,$id): Response
    {   
        $this->permission->userAccess();
        try {  
            $ville=$this->apiController->doGet("typeActeur/{$id}/acteurs");
            $response = new Response(json_encode($ville));
            return $response;
        } catch (\Exception $e) {
            $this->permission->writeLog($e->getMessage());
            $this->session->set('flash', 'error');
            $this->addFlash('error',"Une erreur est survenue impossible de charger les données !");

            return [];
        }
    }


    //recherche dans la liste des acteurs
    #[Route('/getSearchActeur/{id}', name: 'getSearchActeur')]
    public function getSearchActeur($id): Response
    {        

        
        $num = $_GET['iDisplayStart'];
        $op=(int)$num/50;
         (int)$offset=$op;

        try {
            $pdv_data=$this->apiController->doGet("acteurs?{$id}&offset={$offset}&limit=50");
            $acteurs_data = $pdv_data['content'];
            $data= [
                'iTotalRecords'=>$pdv_data['totalElements'],
                'iTotalDisplayRecords'=>$pdv_data['totalElements'],
                "aaData" => $acteurs_data,
            ];
        } catch (\Exception $e) {
            $data= [
                'iTotalRecords'=>0,
                'iTotalDisplayRecords'=>0,
                "aaData" => [],
            ];
        }
        $response = new Response(json_encode($data));
        return $response;
    }

    //pargination de la liste des acteurs
    #[Route('/getActeur', name: 'getActeur')]
    public function getActeur(): Response
    {
        
        $num = $_GET['iDisplayStart'];
        $op=(int)$num/50;
         (int)$offset=$op;

        try {
            $pdv_data = $this->data($offset);
            $acteurs_data = $pdv_data['content'];
            $data= [
                'iTotalRecords'=>$pdv_data['totalElements'],
                'iTotalDisplayRecords'=>$pdv_data['totalElements'],
                "aaData" => $acteurs_data,
            ];
        } catch (\Exception $e) {
            $data= [
                'iTotalRecords'=>0,
                'iTotalDisplayRecords'=>0,
                "aaData" => [],
            ];
        }
        $response = new Response(json_encode($data));
        return $response;
    }


    #[Route('/acteurs', name: 'acteur')]
    public function acteur(): Response
    {
        $data= [
            "databd" => true,
            "statut" => $this->status(),
            "role" => $this->role(),
            "typeacteur" => $this->typeacteur(),
            "partenaire" => $this->partenaire(),
            "tab" => 'lister',
            "filtre" => false,
            "menu" => $this->menu(),
        ];
        $this->permission->writeLog('liste des acteurs');

        return $this->render("acteur/acteur.html.twig",$data);
    }


    #[Route('/acteurs/pdvs/{id}', name: 'acteurPdv')]
    public function acteurPdv(Request $request,$id): Response
    {
        $data= [
            "dataActeurPdv" => $this->dataActeurPdv($id),
            "dataActeur" => $this->dataSelect($id),
            "idActeur" => $id,
            "tab" => 'pdvs',
            "menu" => $this->menu(),
        ]; 
        return $this->render("acteur/acteur.html.twig",$data);
    }


    #[Route('/acteurs/ajouter', name: 'addacteur')]
    public function addacteur(Request $request): Response
    {
        if ($request->getMethod()=='POST') {
            
            $this->permission->userAccess();
                                
            //recupération des infos à enregistrer
            $nom_prenoms=$request->get('nom_prenoms');
            $contact=$request->get('contact');
            $email=$request->get('email');
            $typeacteur=$request->get('typeacteur');
            $roles=$request->get('roles');
            $username=$request->get('username');
            $connexion=$request->get('connexion');
            $hierarchy=$request->get('hierarchy');

            $body=[
                'nom_prenoms'=> $nom_prenoms,
                'contact'=> $contact,
                'email'=> $email,
                'username'=> $username,
                'type_acteur'=> $typeacteur,
                'typeCompte'=> $connexion,
                'hierarchy'=> $hierarchy,
                'roles'=> $roles
            ];

            try {
                
                $sendApi=$this->apiController->doPost('register',$body,'un acteur crée avec succès');

                if($sendApi < 300){
                    $this->addFlash('info',$this->session->get('response'));
                    $this->session->set('flash', 'info');
                    $this->permission->writeLog('Ajouter un acteur');
                }
                else{
                    $this->session->set('flash', 'error');
                    $this->addFlash('error','Ajouter un acteur a été une échec');
                    $this->permission->writeLog(' Ajouter un acteur a été une échec ');
                }

                return $this->redirectToRoute('acteur');
                
            } catch (\Exception $e) {
                $this->session->set('flash', 'error');
                $this->addFlash('error',$this->session->get('response'));
                $this->permission->writeLog($e->getMessage());

                return $this->redirectToRoute('addacteur');

            }

        }else {
            $data= [
                "role" => $this->role(),
                "typeacteur" => $this->typeacteur(),
                "tab" => 'ajouter',
                "menu" => $this->menu(),
            ]; 
            return $this->render("acteur/acteur.html.twig",$data);  
        }
    }

    #[Route('/acteurs/modifier/{id}', name: 'editacteur')]
    public function editacteur(Request $request,$id): Response
    {
        if ($request->getMethod()=='POST') {
            
            $this->permission->userAccess();

            //recupération des infos à enregistrer
            $nom_prenoms=$request->get('nom_prenoms');
            $contact=$request->get('contact');
            $email=$request->get('email');
            $typeacteur=$request->get('typeacteur');
            $roles=$request->get('roles');
            $connexion=$request->get('connexion');
            $username=$request->get('username');
            $hierarchy=$request->get('hierarchy');

            $body=[
                'nom_prenoms'=> $nom_prenoms,
                'contact'=> $contact,
                'username'=> $username,
                'email'=> $email,
                'type_acteur'=> $typeacteur,
                'typeCompte'=> $connexion,
                'hierarchy'=> $hierarchy,
                'roles'=> $roles
            ];

            try {

                $sendApi=$this->apiController->doPut("acteur/{$id}/update",$body,'un acteur a été modifié avec succès');

                if($sendApi < 300){
                    $this->addFlash('info',$this->session->get('response'));
                    $this->session->set('flash', 'info');
                    $this->permission->writeLog('Modifier un acteur identifiant :'.$id);

                }
                else
                {
                    $this->session->set('flash', 'error');
                    $this->addFlash('error','Erreur de mise à jour, vous devez réessayer en modifiant un seul champs');
                    $this->permission->writeLog('Echec de  Modification  acteur identifiant :'.$id);
                }

                return $this->redirectToRoute('acteur');

            } catch (\Exception $e) {
                $this->session->set('flash', 'error');
                $this->addFlash('error',$this->session->get('response'));
                $this->permission->writeLog($e->getMessage());

                return $this->redirectToRoute('editacteur',array('id'=>$id));
            }
        } else {

            $data= [
                "dataSelect" => $this->dataSelect($id),
                "role" => $this->role(),
                "typeacteur" => $this->typeacteur(),
                "tab" => 'modifier',
                "menu" => $this->menu(),
            ]; 
            return $this->render("acteur/acteur.html.twig",$data);            

        }
    }

    #[Route('/acteurs/debloqueacteur/{id}', name: 'debloqueacteur')]
    public function debloqueacteur(Request $request,$id): Response
    {
        if ($request->getMethod()=='POST') {
            
            $this->permission->userAccess();

            try {
                $sendApi=$this->apiController->doDelete("acteur/{$id}/unlock",'acteur a été débloqué avec succès');
                if($sendApi <300){
                    $this->addFlash('info',$this->session->get('response'));
                    $this->session->set('flash', 'info');
                    $this->permission->writeLog('Debloquer acteur identifiant :'.$id);
                }
                else{

                    $this->session->set('flash', 'error');
                    $this->addFlash('error','erreur de débogage acteur'.$id);
                    $this->permission->writeLog('erreur de débogage acteur'.$id);

                }

                return $this->redirectToRoute('acteur');

            } catch (\Exception $e) {

                $this->addFlash('error',$this->session->get('response'));
                $this->session->set('flash', 'error');
                $this->permission->writeLog($e->getMessage());

                return $this->redirectToRoute('acteur');
            }
        }
    }


    #[Route('/acteurs/supprimer/{id}', name: 'delacteur')]
    public function delacteur(Request $request,$id): Response
    {
        if ($request->getMethod()=='POST') {
            
            $this->permission->userAccess();

            try {
                
                $sendApi=$this->apiController->doDelete("acteur/{$id}/delete",'acteur a été supprimé avec succès');
         if ($sendApi <300){

             $this->addFlash('info',$this->session->get('response'));
             $this->session->set('flash', 'info');
             $this->permission->writeLog('Supprimer un acteur identifiant :'.$id);

         }
         else{

             $this->session->set('flash', 'error');
             $this->addFlash('error','Erreur de suppression acteur');
             $this->permission->writeLog('Erreur de suppression acteur :'.$id);
         }

                return $this->redirectToRoute('acteur');

            } catch (\Exception $e) { 

                $this->addFlash('error',$this->session->get('response'));
                $this->session->set('flash', 'error');
                $this->permission->writeLog($e->getMessage());
                return $this->redirectToRoute('acteur');
            }
        }
    }

    #[Route('/acteurs/desactiver/{id}', name: 'desactiveracteur')]
    public function desactiveracteur(Request $request,$id): Response
    {
        if ($request->getMethod()=='POST') {
            
            $this->permission->userAccess();
            try {
                
                $sendApi=$this->apiController->doActivet("acteur/{$id}/desactivate",'acteur a été desactivé avec succès');
              if($sendApi < 300){
                  $this->addFlash('info',$this->session->get('response'));
                  $this->session->set('flash', 'info');
                  $this->permission->writeLog('Desactivation acteur identifiant :'.$id);

              }else{
                  $this->session->set('flash', 'error');
                  $this->addFlash('error','Erreur de desactivation acteur');
                  $this->permission->writeLog('Erreur de desactivation acteur :'.$id);
              }
               return $this->redirectToRoute('acteur');

           } catch (\Exception $e) { 

               $this->addFlash('error',$this->session->get('response'));
               $this->session->set('flash', 'error');
               $this->permission->writeLog($e->getMessage());

               return $this->redirectToRoute('acteur');
           }
        }
    }

    #[Route('/acteurs/activer/{id}', name: 'activeracteur')]
    public function activeracteur(Request $request,$id): Response
    {
        if ($request->getMethod()=='POST') {
            
            $this->permission->userAccess();
            try {
                
                $sendApi=$this->apiController->doActivet("acteur/{$id}/activate",'acteur a été activé avec succès');

                if($sendApi < 300){
                    $this->addFlash('info',$this->session->get('response'));
                    $this->session->set('flash', 'info');
                    $this->permission->writeLog('Activation acteur : données : '.$id);
                }
                else{

                    $this->session->set('flash', 'error');
                    $this->addFlash('error','Erreur de activation acteur');
                    $this->permission->writeLog('Erreur de activation acteur :'.$id);

                }
               return $this->redirectToRoute('acteur');

           } catch (\Exception $e) { 

               $this->addFlash('error',$this->session->get('response'));
               $this->session->set('flash', 'error');
               $this->permission->writeLog($e->getMessage());

               return $this->redirectToRoute('acteur');
           }
        }
    }

    #[Route('/acteurs/import', name: 'importacteur')]
    public function importacteur(Request $request): Response
    {
        if ($request->getMethod()=='POST') {
            
            $this->permission->userAccess();

            $hierarchy=$request->get('hierarchy');
            $typeacteur=$request->get('typeacteur');
            $roles=$request->get('roles');
            $connexion=$request->get('connexion');
            $filedata = $_FILES['listeacteurs']['tmp_name'];

            $body=[
                'roles' => implode(" , " , $roles),
                'typeActeur' => $typeacteur,
                'typeCompte' => $connexion,
                'hierarchy' => $hierarchy,
                'file' => curl_file_create($filedata , 'text/csv')
            ];

            try {

                $sendApi=$this->apiController->doImport('acteurs/upload',$body);
                if($sendApi <  300){

                    $this->addFlash('info',"Importation de(s) partenaire(s) reussie");
                    $this->session->set('flash', 'info');
                    $this->permission->writeLog('Importation de partenaire');
                }
                else{
                    $this->addFlash('error',"Echec d'importation acteurs");
                    $this->session->set('flash', 'info');
                    $this->permission->writeLog("Echec d'importation acteurs");
                }


                return $this->redirectToRoute('acteur');

            } catch (\Exception $e) {
                $this->session->set('flash', 'error');
                $this->addFlash('error',$this->session->get('response'));
                $this->permission->writeLog($e->getMessage());

                return $this->redirectToRoute('importacteur');

            }

        }else {
            $data= [
                "typeacteur" => $this->typeacteur(),
                "role" => $this->role(),
                "tab" => 'importer',
                "menu" => $this->menu(),
            ]; 
            return $this->render("acteur/acteur.html.twig",$data);
        }
    }

    #[Route('/acteurs/reinitialiser/{id}', name: 'reinitialiseracteur')]
    public function reinitialiseracteur(Request $request,$id): Response
    {
        if ($request->getMethod()=='POST') {
            
            $this->permission->userAccess();
            try {
                $sendApi=$this->apiController->doActivet("acteur/{$id}/update-passwd",'mot de passe acteur a été réinitialisé avec succès');
               
                if($sendApi < 300){
                    $this->addFlash('info',$this->session->get('response'));
                    $this->session->set('flash', 'info');
                    $this->permission->writeLog('Réinitialisation de mot de passe : '.$id);
                }
                else{
                    $this->session->set('flash', 'error');
                    $this->addFlash('error','Erreur de réinitialisation de mot de passe');
                    $this->permission->writeLog('Erreur de réinitialisation de mot de passe :'.$id);
                }

                return $this->redirectToRoute('acteur');

           } catch (\Exception $e) { 

                $this->session->set('flash', 'error');
                $this->addFlash('error',$this->session->get('response'));
                $this->permission->writeLog($e->getMessage());

                return $this->redirectToRoute('acteur');

           }
        }
    }
}
