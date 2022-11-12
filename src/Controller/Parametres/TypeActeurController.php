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

class TypeActeurController extends AbstractController
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

    // GESTION DES TYPES D'ACTEURS
    //recuperer la liste de type d'acteur
    public function data()
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

    //recuperer un seul type de acteur
    public function dataSelect($id)
    {      
        $this->permission->userAccess();
        try {      
            $dataSelect=$this->apiController->doGet("type-acteur/{$id}") ;
            return $dataSelect;
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
            "1" => array ('parametre','typeacteur')
        ]; 
        return $menu;
    }

    //liste des statuts
    private function status(){
        return $this->config["status"];
    }

    #[Route('/type_acteur', name: 'typeacteur')]
    public function typeacteur(): Response
    {   
        $data= [
            "databd" => $this->data(),
            "statut" => $this->status(),
            "tab" => 'lister',
            "menu" => $this->menu(),
        ];
       // die(var_dump($this->data()));
        $this->permission->writeLog('liste des acteurs');
        return $this->render("parametres/typeacteur.html.twig",$data);
    }

    #[Route('/type_acteur/ajouter', name: 'addtypeacteur')]
    public function addtypeacteur(Request $request): Response
    {
        if ($request->getMethod()=='POST') {
            
            $this->permission->userAccess();
                                
            //recupération des infos à enregistrer
            $libelle=$request->get('libelle');
            $description=$request->get('description');
            $hierarchy=$request->get('hierarchy');

            $body=[
                'libelle'=> $libelle,
                'description'=> $description,
                'hierarchy'=> $hierarchy
            ];
         
            try {
                $sendApi=$this->apiController->doPost("type-acteurs",$body,'Acteur ajouté avec succès');

                if($sendApi < 300){

                    $this->session->set('flash', 'info');
                    $this->addFlash('info',$this->session->get('response'));
                    $this->permission->writeLog('Ajouter un type controleur');
                }
                else{

                    $this->session->set('flash', 'error');
                    $this->addFlash('error',"Erreur  d'ajoute   d'acteur  ");
                    $this->permission->writeLog("Erreur  d'ajoute   d'acteur ");
                }

                return $this->redirectToRoute('typeacteur'); 
                
            } catch (\Exception $e) {
                $this->session->set('flash', 'error');
                $this->addFlash('error',$this->session->get('response'));
                $this->permission->writeLog($e->getMessage());

                return $this->redirectToRoute('addtypeacteur');
            }

        }else {
            $data= [
                "typeacteur" => $this->data(),
                "tab" => 'ajouter',
                "menu" => $this->menu(),
            ]; 
            return $this->render("parametres/typeacteur.html.twig",$data);  
        }
    }

    #[Route('/type_acteur/modifier/{id}', name: 'edittypeacteur')]
    public function edittypeacteur(Request $request,$id): Response
    {
        if ($request->getMethod()=='POST') {
            $this->permission->userAccess();

            //recupération des infos à enregistrer
            $libelle=$request->get('libelle');
            $description=$request->get('description');
            $hierarchy=$request->get('hierarchy');

            $body=[
                'libelle'=> $libelle,
                'description'=> $description,
                'hierarchy'=> $hierarchy
            ];

            try {
                $sendApi=$this->apiController->doPut("type-acteur/{$id}/update",$body,'Acteur modifié avec succès');

                if($sendApi < 300){
                    $this->addFlash('info',$this->session->get('response'));
                    $this->session->set('flash', 'info');;
                    $this->permission->writeLog('Modifier un type controleur identifiant :'.$id);

                }
                else{

                    $this->session->set('flash', 'error');
                    $this->addFlash('error',"Erreur  de modification    d'acteur  ");
                    $this->permission->writeLog("Erreur  de modification   d'acteur ".$id);
                }

                return $this->redirectToRoute('typeacteur'); 

            } catch (\Exception $e) {
                $this->session->set('flash', 'error');
                $this->addFlash('error',$this->session->get('response'));
                $this->permission->writeLog($e->getMessage());

                return $this->redirectToRoute('edittypeacteur',array('id'=>$id));
            }
        }else {  

            $data= [
                "typeacteur" => $this->data(),
                "dataSelect" => $this->dataSelect($id),
                "tab" => 'modifier',
                "menu" => $this->menu(),
            ]; 
            return $this->render("parametres/typeacteur.html.twig",$data);            

        }
    }

    #[Route('/type_acteur/supprimer/{id}', name: 'deltypeacteur')]
    public function deltypeacteur(Request $request,$id): Response
    {
        if ($request->getMethod()=='POST') {
            $this->permission->userAccess();

            try {
                    $sendApi=$this->apiController->doDelete("type-acteur/{$id}/delete",'Acteur supprimé avec succès');
        
                    if($sendApi < 300){
                        $this->addFlash('info',$this->session->get('response'));
                        $this->session->set('flash', 'info');
                        $this->permission->writeLog('Supprimer un type controleur identifiant :'.$id);
    
                    }
                    else{
    
                        $this->session->set('flash', 'error');
                        $this->addFlash('error',"Erreur  de suppression d'acteur  ");
                        $this->permission->writeLog("Erreur  de suprression d'acteur ".$id);
                    }

                return $this->redirectToRoute('typeacteur'); 

            } catch (\Exception $e) { 

                $this->addFlash('error',$this->session->get('response'));
                $this->session->set('flash', 'error');
                $this->permission->writeLog($e->getMessage());

                return $this->redirectToRoute('typeacteur'); 
            }
        }
    }

    #[Route('/type_acteur/desactiver/{id}', name: 'desactivertypeacteur')]
    public function desactivertypeacteur(Request $request,$id): Response
    {
        if ($request->getMethod()=='POST') {
            try {
                $sendApi=$this->apiController->doActivet("type-acteur/{$id}/desactivate",'Acteur desactivé avec succès');

                if($sendApi < 300){
                    $this->addFlash('info',$this->session->get('response'));
                    $this->session->set('flash', 'info');
                    $this->permission->writeLog('Desactivation type controleur identifiant :'.$id);
                    return $this->redirectToRoute('typeacteur');
                }
                else{

                    $this->session->set('flash', 'error');
                    $this->addFlash('error',"Erreur  de desactivation d'acteur  ");
                    $this->permission->writeLog("Erreur  de desactivation d'acteur ".$id);
                }

                return $this->redirectToRoute('typeacteur'); 

           } catch (\Exception $e) { 

               $this->addFlash('error',$this->session->get('response'));
               $this->session->set('flash', 'error');
                $this->permission->writeLog($e->getMessage());

               return $this->redirectToRoute('typeacteur'); 
           }
        }
    }

    #[Route('/type_acteur/activer/{id}', name: 'activertypeacteur')]
    public function activertypeacteur(Request $request,$id): Response
    {
        if ($request->getMethod()=='POST') {
            try {
                $sendApi=$this->apiController->doActivet("type-acteur/{$id}/activate",'Acteur activé avec succès');

                if($sendApi < 300){
                    $this->addFlash('info',$this->session->get('response'));
                    $this->session->set('flash', 'info');
                    $this->permission->writeLog('Activation type controleur : données : '.$id);
                }
                else{

                    $this->session->set('flash', 'error');
                    $this->addFlash('error',"Erreur  d'activation     d'acteur  ");
                    $this->permission->writeLog("Erreur  d'activation   d'acteur ".$id);
                }

                return $this->redirectToRoute('typeacteur'); 

           } catch (\Exception $e) { 

               $this->addFlash('error',$this->session->get('response'));
               $this->session->set('flash', 'error');
                $this->permission->writeLog($e->getMessage());

               return $this->redirectToRoute('typeacteur'); 
           }
        }
    }

    #[Route('/type_acteur/filtre', name: 'recherchetypeacteur')]
    public function recherche(Request $request): Response
    {
        if ($request->getMethod()=='POST') {
                               
            //recupération des infos à enregistrer
            $libelle=$request->get('libelle');
            $status=$request->get('status');

            $saisie=[
                'libelle'=> $libelle,
                'status'=> $status
            ];

            $body="libelle=$libelle&actived=$status";

            try {
                $sendApi=$this->apiController->doGet("type-acteurs?$body");
                $data= [
                    "databd" => $sendApi,
                    "saisie" => $saisie,
                    "statut" => $this->status(),
                    "tab" => 'lister',
                    "menu" => $this->menu(),
                ];
                $this->addFlash('info',$this->session->get('response'));
                $this->session->set('flash', 'info');

                return $this->render("parametres/typeacteur.html.twig",$data);

            } catch (\Exception $e) {
                $this->addFlash('error',$this->session->get('response'));
                $this->session->set('flash', 'error');
                $this->permission->writeLog($e->getMessage());

                return $this->redirectToRoute('typeacteur');           
            }

        }
    }

    #[Route('/type_acteur/import', name: 'importtypeacteur')]
    public function importtypeacteur(Request $request): Response
    {
        if ($request->getMethod()=='POST') {

            $filedata = $_FILES['listetypeacteur']['tmp_name'];

                $body=[
                    'file' => curl_file_create($filedata , 'text/csv')
                ];
                
                try {
                    $sendApi=$this->apiController->doImport("type-acteurs/upload",$body) ;
                    if ($sendApi <300){

                        $this->addFlash('info',"Importation type controleur reussie");
                        $this->session->set('flash', 'info');
                        $this->permission->writeLog(' success Importation de partenaire');
                    }
                    else{
                        $this->addFlash('error',"Echec d'importation partenaire(s)");
                        $this->session->set('flash', 'info');
                        $this->permission->writeLog('Echec Importation de partenaire');
                    }

                    return $this->redirectToRoute('typeacteur'); 

                } catch (\Exception $e) {
                    $this->session->set('flash', 'error');
                    $this->addFlash('error',$this->session->get('response'));
                    $this->permission->writeLog($e->getMessage());

                    return $this->redirectToRoute('importtypeacteur');
                }

        }else {
            $data= [
                "tab" => 'importer',
                "menu" => $this->menu(),
            ]; 
            return $this->render("parametres/typeacteur.html.twig",$data);
        }
    }
}
