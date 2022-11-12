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

class PartenaireController extends AbstractController
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

    // GESTION DES PARTENAIRE
    //recuperer la liste des partenaires
    private function data()
    {                            
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

    //recuperer un seuls partenaires
    private function dataSelect($id)
    {                          
        $this->permission->userAccess();
        try {  
            $databd=$this->apiController->doGet("partenaire/{$id}") ;
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

    //recuperer liste des types d'acteur
    private function typeacteur(){
                            
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

    private function  dataActeur($id)
    {   
        $this->permission->userAccess();
        try {  
            $pdv_data=$this->apiController->doGet("acteurs?typeActeur=191");
            $response = $pdv_data['content'];
            return $response;

        } catch (\Exception $e) {
            $this->permission->writeLog($e->getMessage());
            $this->session->set('flash', 'error');
            $this->addFlash('error',"Une erreur est survenue impossible de charger les données !");

            return [];
        }
    }

    //menu courant
    private function menu(){
        $menu= [
            "1" => array ('parametre','partenaire')
        ]; 
        return $menu;
    }

    //liste des statuts
    private function status(){
        return $this->config["status"];
    }

    #[Route('/partenaire', name: 'partenaire')]
    public function partenaire(): Response
    {                   
        $data= [
            "databd" => $this->data(),
            "statut" => $this->status(),
            "tab" => 'lister',
            "menu" => $this->menu(),
        ];
        $this->permission->writeLog('liste des partenaires');

        return $this->render("parametres/partenaire.html.twig",$data);
    }

    #[Route('/partenaire/ajouter', name: 'addpartenaire')]
    public function addpartenaire(Request $request): Response
    {
        if ($request->getMethod()=='POST') {
            
            $this->permission->userAccess();
            //recupération des infos à enregistrer
            $libelle=$request->get('libelle');
            $description=$request->get('description');

            $body=[
                'libelle'=> $libelle,
                'description'=> $description,
            ];

            try {
                $sendApi=$this->apiController->doPost("partenaires",$body,'Partenaire ajouté avec succès');

                if ($sendApi < 300){

                    $this->addFlash('info',$this->session->get('response'));
                    $this->session->set('flash', 'info');
                    return $this->redirectToRoute('partenaire');
                }
                else{

                    $this->session->set('flash', 'error');
                    $this->addFlash('error',"Erreur  d'ajout de partenaire ");
                    $this->permission->writeLog("Erreur  d'ajout de partenaire ");
                }

                return $this->redirectToRoute('partenaire');
            }
            catch (\Exception $e)
            {
                if (isset($sendApi['message'])) {
                    $sendApi = $sendApi['message'];
                }
                $this->session->set('flash', 'error');
                $this->addFlash('error',$sendApi);
                return $this->redirectToRoute('addpartenaire');
            }

        }else {
            $data= [
                "tab" => 'ajouter',
                "menu" => $this->menu(),
            ]; 
            return $this->render("parametres/partenaire.html.twig",$data);  
        }
    }

    #[Route('/partenaire/modifier/{id}', name: 'editpartenaire')]
    public function editpartenaire(Request $request,$id): Response
    {
        if ($request->getMethod()=='POST') {
            
            $this->permission->userAccess();

            //recupération des infos à enregistrer
            $libelle=$request->get('libelle');
            $description=$request->get('description');

            $body=[
                'libelle'=> $libelle,
                'description'=> $description,
            ];

            try {
                $sendApi=$this->apiController->doPut("partenaire/{$id}/update",$body,'Partenaire modifié avec succès');

                if($sendApi < 300 ){
                    $this->addFlash('info',$this->session->get('response'));
                    $this->session->set('flash', 'info');
                    $this->permission->writeLog('Modifier un partenaire identifiant :'.$id);
                }
                else{

                    $this->session->set('flash', 'error');
                    $this->addFlash('error',"Erreur  de modification  de partenaire ");
                    $this->permission->writeLog("Erreur  de modification  de partenaire ");
                }
                
                $this->permission->writeLog('Modifier un partenaire identifiant :'.$id);

                return $this->redirectToRoute('partenaire');

            } catch (\Exception $e) {
                $this->session->set('flash', 'error');
                $this->addFlash('error',$this->session->get('response'));
                $this->permission->writeLog($e->getMessage());

                return $this->redirectToRoute('editpartenaire',array('id'=>$id));
            }
        }else {  

            $data= [
                "dataSelect" => $this->dataSelect($id),
                "tab" => 'modifier',
                "menu" => $this->menu(),
            ]; 
            return $this->render("parametres/partenaire.html.twig",$data);            

        }
    }

    #[Route('/partenaire/liste_acteurs/{id}/{partenaire}', name: 'addActeurPartenaire')]
    public function addActeurPartenaire(Request $request,$id,$partenaire): Response
    {
        if ($request->getMethod()=='POST') {
            
            $this->permission->userAccess();

            //recupération des infos à enregistrer
            $acteurs=$request->get('listeActeurs');
            $acteursT = explode(',',$acteurs);
            $acteursTT = array_map('intval',$acteursT);

            $body=$acteursT;

            try {
                $sendApi=$this->apiController->doPost("partenaire/{$id}/acteurs",$body,'Partenaire id modifié avec succès');

                if ($sendApi < 300){
                    $this->addFlash('info',$this->session->get('response'));
                    $this->session->set('flash', 'info');
                    $this->permission->writeLog('Modifier un partenaire identifiant :'.$id);
                }
                else{
                    $this->session->set('flash', 'error');
                    $this->addFlash('error',"Erreur  de modification id  du partenaire ");
                    $this->permission->writeLog("Erreur  de modification  id du  partenaire ".$id);
                }

                return $this->redirectToRoute('partenaire');

            } catch (\Exception $e) {
                $this->session->set('flash', 'error');
                $this->addFlash('error',$this->session->get('response'));
                $this->permission->writeLog($e->getMessage());

                return $this->redirectToRoute('editpartenaire',array('id'=>$id));
            }
        }else {  

            $data= [
                "id" => $id,
                "partenaire" => $partenaire,
                "typeacteur" => $this->typeacteur(),
                "dataActeur" => $this->dataActeur($id),
                "tab" => 'controleur',
                "menu" => $this->menu(),
            ]; 
            return $this->render("parametres/partenaire.html.twig",$data);            

        }
    }

    #[Route('/partenaire/supprimer/{id}', name: 'delpartenaire')]
    public function delpartenaire(Request $request,$id): Response
    {
        if ($request->getMethod()=='POST') {
            
            $this->permission->userAccess();

            try {
                $sendApi=$this->apiController->doDelete("partenaire/{$id}/delete",'Partenaire supprimé avec succès');

                if($sendApi < 300 ){
                    $this->addFlash('info',$this->session->get('response'));
                    $this->session->set('flash', 'info');
                    $this->permission->writeLog('Supprimer un partenaire identifiant :'.$id);
                }
                else{
                    $this->session->set('flash', 'error');
                    $this->addFlash('error',"Erreur  de suppression   du partenaire ");
                    $this->permission->writeLog("Erreur  de suppression   id du  partenaire ".$id);
                }

                return $this->redirectToRoute('partenaire');

            } catch (\Exception $e) { 

                $this->addFlash('error',$this->session->get('response'));
                $this->session->set('flash', 'error');
                $this->permission->writeLog($e->getMessage());

                return $this->redirectToRoute('partenaire');
            }
        }
    }

    #[Route('/partenaire_desactiver/{id}', name: 'desactiverpartenaire')]
    public function desactiverpartenaire(Request $request,$id): Response
    {
        if ($request->getMethod()=='POST') {
            
            try {
                $sendApi=$this->apiController->doActivet("partenaire/{$id}/desactivate",'Partenaire desactivé avec succès');

                if($sendApi < 300){
                    $this->addFlash('info',$this->session->get('response'));
                    $this->session->set('flash', 'info');
                    $this->permission->writeLog('Desactivation partenaire identifiant :'.$id);
                }
                else{
                    $this->session->set('flash', 'error');
                    $this->addFlash('error',"Erreur  de desactivation  du partenaire ");
                    $this->permission->writeLog("Erreur  de desactivation    du  partenaire ".$id);
                }

               return $this->redirectToRoute('partenaire');

           } catch (\Exception $e) { 

               $this->addFlash('error',$this->session->get('response'));
               $this->session->set('flash', 'error');
               $this->permission->writeLog($e->getMessage());

               return $this->redirectToRoute('partenaire');
           }
        }
    }

    #[Route('/partenaire_activer/{id}', name: 'activerpartenaire')]
    public function activerpartenaire(Request $request,$id): Response
    {
        if ($request->getMethod()=='POST') {
            
            try {
                $sendApi=$this->apiController->doActivet("partenaire/{$id}/activate",'Partenaire activé avec succès');

                if($sendApi < 300){
                    $this->addFlash('info',$this->session->get('response'));
                    $this->session->set('flash', 'info');
                    $this->permission->writeLog('Activation partenaire : données : '.$id);
                }
                else{
                    $this->session->set('flash', 'error');
                    $this->addFlash('error',"Erreur  d'activation  du partenaire ");
                    $this->permission->writeLog("Erreur  d'activation    du  partenaire ".$id);
                }

               return $this->redirectToRoute('partenaire');

           } catch (\Exception $e) { 

               $this->addFlash('error',$this->session->get('response'));
               $this->session->set('flash', 'error');
               $this->permission->writeLog($e->getMessage());

               return $this->redirectToRoute('partenaire');
           }
        }
    }

    #[Route('/partenaire/filtre', name: 'recherchepartenaire')]
    public function recherche(Request $request): Response
    {
        if ($request->getMethod()=='POST') {
            
            $this->permission->userAccess();
                               
            //recupération des infos à rechercher
            $libelle=$request->get('libelle');
            $status=$request->get('status');

            $saisie=[
                'libelle'=> $libelle,
                'status'=> $status
            ];

            $body="libelle=$libelle&actived=$status";
         
            try {
                $sendApi=$this->apiController->doGet("partenaires?$body");
                $data= [
                    "databd" => $sendApi,
                    "saisie" => $saisie,
                    "statut" => $this->status(),
                    "tab" => 'lister',
                    "menu" => $this->menu(),
                ];
                $this->addFlash('info',$this->session->get('response'));
                $this->session->set('flash', 'info');

                return $this->render("parametres/partenaire.html.twig",$data);

            } catch (\Exception $e) {
                $this->addFlash('error',$this->session->get('response'));
                $this->session->set('flash', 'error');
                $this->permission->writeLog($e->getMessage());

                return $this->redirectToRoute('partenaire');           
            }

        }
    }

    #[Route('/partenaire/import', name: 'importpartenaire')]
    public function importpartenaire(Request $request): Response
    {
        if ($request->getMethod()=='POST') {
            
            $this->permission->userAccess();

            $filedata = $_FILES['listepartenaire']['tmp_name'];

            $body=[
                'file' => curl_file_create($filedata , 'text/csv')
            ];

            try {
                $sendApi=$this->apiController->doImport("partenaires/upload",$body) ;
                if($sendApi < 300){

                    $this->addFlash('info',"Importation de(s) partenaire(s) reussie");
                    $this->session->set('flash', 'info');
                    $this->permission->writeLog('Importation de partenaire');
                }
                else{

                    $this->addFlash('error',"Echec d'importation partenaire(s)");
                    $this->session->set('flash', 'info');
                    $this->permission->writeLog('Importation de partenaire');
                }

                return $this->redirectToRoute('partenaire');

            } catch (\Exception $e) {
                $this->session->set('flash', 'error');
                $this->addFlash('error',$this->session->get('response'));
                $this->permission->writeLog($e->getMessage());

                return $this->redirectToRoute('importpartenaire');
            }

        }else {
            $data= [
                "tab" => 'importer',
                "menu" => $this->menu(),
            ]; 
            return $this->render("parametres/partenaire.html.twig",$data);
        }
    }
}
