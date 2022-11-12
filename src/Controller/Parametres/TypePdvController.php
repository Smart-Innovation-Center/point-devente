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

class TypePdvController extends AbstractController
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

    // GESTION DES TYPES DE PDV
    //recuperer la liste de type de pdv
    private function data()
    {       
        $this->permission->userAccess();
        try {  
            $databd=$this->apiController->doGet("type-pdv") ;
            return $databd;
        } catch (\Exception $e) {
            $this->permission->writeLog($e->getMessage());
            $this->session->set('flash', 'error');
            $this->addFlash('error',"Une erreur est survenue impossible de charger les données !");

            return [];
        }       
    }

    //recuperer un seul type de pdv
    private function dataSelect($id)
    {            
        $this->permission->userAccess();
        try {  
            $databd=$this->apiController->doGet("type-pdv/{$id}") ;
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
            "1" => array ('parametre','typepdv')
        ]; 
        return $menu;
    }

    //liste des statuts
    private function status(){
        return $this->config["status"];
    }

    #[Route('/type_pdv', name: 'typepdv')]
    public function typepdv(): Response
    {   
        $data= [
            "databd" => $this->data(),
            "statut" => $this->status(),
            "tab" => 'lister',
            "menu" => $this->menu(),
        ];
        $this->permission->writeLog('liste des type de PDV');
        return $this->render("parametres/typepdv.html.twig",$data);
    }

    #[Route('/type_pdv/ajouter', name: 'addtypepdv')]
    public function addtypepdv(Request $request): Response
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
                $sendApi=$this->apiController->doPost("type-pdv",$body,'PDV ajouté avec succès');

                if($sendApi < 300 ){
                    $this->addFlash('info',$this->session->get('response'));
                    $this->session->set('flash', 'info');
                    $this->permission->writeLog('Ajouter un type PDV');
                }
                else{

                    $this->session->set('flash', 'error');
                    $this->addFlash('error',"Erreur  d'activation     de PDV  ");
                    $this->permission->writeLog("Erreur  d'ajoute   de PDV ");
                }

                return $this->redirectToRoute('typepdv');   
                
            } catch (\Exception $e) {
                $this->session->set('flash', 'error');
                $this->addFlash('error',$this->session->get('response'));
                $this->permission->writeLog($e->getMessage());

                return $this->redirectToRoute('addtypepdv');
            }

        }else {
            $data= [
                "tab" => 'ajouter',
                "menu" => $this->menu(),
            ]; 
            return $this->render("parametres/typepdv.html.twig",$data);  
        }
    }

    #[Route('/type_pdv/modifier/{id}', name: 'edittypepdv')]
    public function edittypepdv(Request $request,$id): Response
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
                $sendApi=$this->apiController->doPut("type-pdv/{$id}/update",$body,'PDV modifié avec succès');

                if($sendApi < 300){

                    $this->addFlash('info',$this->session->get('response'));
                    $this->session->set('flash', 'info');
                    $this->permission->writeLog('Modifier un type PDV identifiant :'.$id);
                }
                else{

                    $this->session->set('flash', 'error');
                    $this->addFlash('error',"Erreur  de modification du PDV  ");
                    $this->permission->writeLog("Erreur  de modififcation    du PDV ".$id);
                }

                return $this->redirectToRoute('typepdv');   

            } catch (\Exception $e) {
                $this->session->set('flash', 'error');
                $this->addFlash('error',$this->session->get('response'));
                $this->permission->writeLog($e->getMessage());

                return $this->redirectToRoute('edittypepdv',array('id'=>$id));
            }
        }else {  

            $data= [
                "dataSelect" => $this->dataSelect($id),
                "tab" => 'modifier',
                "menu" => $this->menu(),
            ]; 
            return $this->render("parametres/typepdv.html.twig",$data);            

        }
    }

    #[Route('/type_pdv/supprimer/{id}', name: 'deltypepdv')]
    public function deltypepdv(Request $request,$id): Response
    {
        if ($request->getMethod()=='POST') {
            
            $this->permission->userAccess();

            try {
                $sendApi=$this->apiController->doDelete("type-pdv/{$id}/delete",'PDV supprimé avec succès');

                if($sendApi < 300){

                    $this->addFlash('info',$this->session->get('response'));
                    $this->session->set('flash', 'info');
                    $this->permission->writeLog('Supprimer un type PDV identifiant :'.$id);

                }
                else{

                    $this->session->set('flash', 'error');
                    $this->addFlash('error',"Erreur  de suppression du PDV  ");
                    $this->permission->writeLog("Erreur  de suppression    du PDV ".$id);
                }

                return $this->redirectToRoute('typepdv');   

            } catch (\Exception $e) { 

                $this->addFlash('error',$this->session->get('response'));
                $this->session->set('flash', 'error');
                $this->permission->writeLog($e->getMessage());

                return $this->redirectToRoute('typepdv');   
            }
        }
    }

    #[Route('/type_pdv/desactiver/{id}', name: 'desactivertypepdv')]
    public function desactivertypepdv(Request $request,$id): Response
    {
        if ($request->getMethod()=='POST') {
            
            $this->permission->userAccess();
            try {
                $sendApi=$this->apiController->doActivet("type-pdv/{$id}/desactivate",'PDV desactivé avec succès');

                if($sendApi < 300){
                    $this->addFlash('info',$this->session->get('response'));
                    $this->session->set('flash', 'info');
                    $this->permission->writeLog('Desactivation type PDV identifiant :'.$id);
                }
                else{
                    $this->session->set('flash', 'error');
                    $this->addFlash('error',"Erreur  de desactivation  du PDV  ");
                    $this->permission->writeLog("Erreur  de desactivation    du PDV ".$id);
                }

               return $this->redirectToRoute('typepdv');   

           } catch (\Exception $e) { 

               $this->addFlash('error',$this->session->get('response'));
               $this->session->set('flash', 'error');
               $this->permission->writeLog($e->getMessage());

               return $this->redirectToRoute('typepdv');   
           }
        }
    }

    #[Route('/type_pdv/activer/{id}', name: 'activertypepdv')]
    public function activertypepdv(Request $request,$id): Response
    {
        if ($request->getMethod()=='POST') {
            
            $this->permission->userAccess();
            try {
                $sendApi=$this->apiController->doActivet("type-pdv/{$id}/activate",'PDV activé avec succès');

                if($sendApi < 300 ){

                    $this->addFlash('info',$this->session->get('response'));
                    $this->session->set('flash', 'info');
                    $this->permission->writeLog('Activation type PDV : données : '.$id);
                }
                else{

                    $this->session->set('flash', 'error');
                    $this->addFlash('error',"Erreur  d'activation  du PDV  ");
                    $this->permission->writeLog("Erreur  d'activation    du PDV ".$id);
                }

               return $this->redirectToRoute('typepdv');   

           } catch (\Exception $e) {
               $this->addFlash('error',$this->session->get('response'));
               $this->session->set('flash', 'error');
               $this->permission->writeLog($e->getMessage());

               return $this->redirectToRoute('typepdv');   
           }
        }
    }

    #[Route('/type_pdv/filtre', name: 'recherchetypepdv')]
    public function recherche(Request $request): Response
    {
        if ($request->getMethod()=='POST') {
            
            $this->permission->userAccess();
                               
            //recupération des infos à enregistrer
            $libelle=$request->get('libelle');
            $status=$request->get('status');

            $saisie=[
                'libelle'=> $libelle,
                'status'=> $status
            ];

            $body="libelle=$libelle&actived=$status";
         
            try {
                $sendApi=$this->apiController->doGet("type-pdv?$body");
                $data= [
                    "databd" => $sendApi,
                    "saisie" => $saisie,
                    "statut" => $this->status(),
                    "tab" => 'lister',
                    "menu" => $this->menu(),
                ];
                $this->addFlash('info',$this->session->get('response'));
                $this->session->set('flash', 'info');

                return $this->render("parametres/typepdv.html.twig",$data);

            } catch (\Exception $e) {
                $this->addFlash('error',$this->session->get('response'));
                $this->session->set('flash', 'error');
                $this->permission->writeLog($e->getMessage());

                return $this->redirectToRoute('typepdv');           
            }

        }
    }

    #[Route('/type_pdv/import', name: 'importtypepdv')]
    public function importtypepdv(Request $request): Response
    {
        if ($request->getMethod()=='POST') {
            
            $this->permission->userAccess();

            $filedata = $_FILES['listetypepdv']['tmp_name'];

            $body=[
                'file' => curl_file_create($filedata , 'text/csv')
            ];

            try {
                $sendApi=$this->apiController->doImport('type-pdv/upload',$body) ;

                if($sendApi < 300){

                    $this->addFlash('info',"Importation type controleur reussie");
                    $this->session->set('flash', 'info');
                    $this->permission->writeLog('Importation de partenaire');
                }
                else{

                    $this->addFlash('error',"Echec d'Importation type controleur ");
                    $this->session->set('flash', 'info');
                    $this->permission->writeLog("Echec d'Importation type controleur ");
                }

                return $this->redirectToRoute('typepdv');

            } catch (\Exception $e) {
                $this->session->set('flash', 'error');
                $this->addFlash('error',$this->session->get('response'));
                $this->permission->writeLog($e->getMessage());

                return $this->redirectToRoute('importtypepdv');
            }

        }else {
            $data= [
                "tab" => 'importer',
                "menu" => $this->menu(),
            ]; 
            return $this->render("parametres/typepdv.html.twig",$data);
        }
    }
}
