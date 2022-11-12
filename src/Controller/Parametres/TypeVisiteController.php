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

class TypeVisiteController extends AbstractController
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

    // GESTION DES TYPES DE VISITE
    //recuperer la liste de type de visite
    private function data()
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

    //recuperer un seul type de visite
    private function dataSelect($id)
    {                 
        $this->permission->userAccess();
        try {  
            $databd=$this->apiController->doGet("type-visites/{$id}") ;
            return $databd;
        } catch (\Exception $e) {
            $this->permission->writeLog($e->getMessage());
            $this->session->set('flash', 'error');
            $this->addFlash('error',"Une erreur est survenue impossible de charger les données !");

            return [
                'id'=> $id,                
                'libelle'=> '',
                'description'=> '',
                'type_pdvs'=> [],
                'typeActeurs'=> []
            ];
        } 
    }

    //liste des types de pdv
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
    
    //liste des types de pdv
    private function typepdv(){
                    
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

    //menu
    private function menu(){
        $menu= [
            "1" => array ('parametre','typevisite')
        ]; 
        return $menu;
    }

    //liste des statuts
    private function status(){
        return $this->config["status"];
    }

    #[Route('/type_visite', name: 'typevisite')]
    public function typevisite(): Response
    {
        $data= [
            "databd" => $this->data(),
            "typepdv" => $this->typepdv(),
            "typeacteur" => $this->typeacteur(),
            "statut" => $this->status(),
            "tab" => 'lister',
            "menu" => $this->menu(),
        ];
        $this->permission->writeLog('liste des type de visites');
        return $this->render("parametres/typevisite.html.twig",$data);
    }

    #[Route('/type_visite/ajouter', name: 'addtypevisite')]
    public function addtypevisite(Request $request): Response
    {
        if ($request->getMethod()=='POST') {
            
            $this->permission->userAccess();
                                
            //recupération des infos à enregistrer
            $libelle=$request->get('libelle');
            $description=$request->get('description');
            $type_pdv=$request->get('typepdv');
            $typeacteur=$request->get('typeActeurs');

            if (!empty($type_pdv) && !empty($typeacteur)) {

                $body=[
                    'libelle'=> $libelle,
                    'description'=> $description,
                    'type_pdv'=> $type_pdv,
                    'typeActeurs'=> $typeacteur
                ];
             
                try {
                    $sendApi=$this->apiController->doPost("type-visites",$body,'Visite ajouté avec succès');

                    if ($sendApi < 300){
                        $this->session->set('flash', 'info');
                        $this->addFlash('info',$this->session->get('response'));
                        $this->permission->writeLog('Ajouter un type de visite');

                    }

                    else{

                        $this->session->set('flash', 'error');
                        $this->addFlash('error',"Erreur d'ajoute de la visite ");
                        $this->permission->writeLog(" Erreur d'ajoute de la visite");
                    }

                    return $this->redirectToRoute('typevisite');   
                    
                } catch (\Exception $e) {
                    $this->session->set('flash', 'error');
                    $this->addFlash('error',$e->getMessage());
                    $this->permission->writeLog($e->getMessage());

                    return $this->redirectToRoute('addtypevisite');
                }

            }else {
                $this->session->set('flash', 'error');
                $this->addFlash('error','Impossible d\'effectuer l\'enregistrement, selectionner les différents types !');

                return $this->redirectToRoute('addtypevisite');
            }

        }else {
            $data= [
                "typeacteur" => $this->typeacteur(),
                "typepdv" => $this->typepdv(),
                "tab" => 'ajouter',
                "menu" => $this->menu(),
            ]; 
            return $this->render("parametres/typevisite.html.twig",$data);  
        }
    }

    #[Route('/type_visite/modifier/{id}', name: 'edittypevisite')]
    public function edittypevisite(Request $request,$id): Response
    {
        if ($request->getMethod()=='POST') {
            
            $this->permission->userAccess();

            //recupération des infos à enregistrer
            $libelle=$request->get('libelle');
            $description=$request->get('description');
            $type_pdv=$request->get('typepdv');
            $typeacteur=$request->get('typeActeurs');

            $body=[
                'libelle'=> $libelle,
                'description'=> $description,
                'type_pdv'=> [],
                'typeActeurs'=> []
            ];

            try {
                $sendApi=$this->apiController->doPut("type-visite/{$id}/update",$body,'Type de visite modifié avec succès');

                if ($sendApi < 300 ){
                    $this->addFlash('info',$this->session->get('response'));
                    $this->session->set('flash', 'info');
                    $this->permission->writeLog('Modifier un type de visite identifiant :'.$id);
                }
                else{

                    $this->session->set('flash', 'error');
                    $this->addFlash('error',"Erreur  de modification   de la visite");
                    $this->permission->writeLog("Erreur  de modification   de la visite".$id);
                }

                return $this->redirectToRoute('typevisite');    

            } catch (\Exception $e) {
                $this->session->set('flash', 'error');
                $this->addFlash('error',$this->session->get('response'));
                $this->permission->writeLog($e->getMessage());

                return $this->redirectToRoute('edittypevisite',array('id'=>$id));
            }
        }else {  

            $data= [
                "dataSelect" => $this->dataSelect($id),
                "typeacteur" => $this->typeacteur(),
                "typepdv" => $this->typepdv(),
                "tab" => 'modifier',
                "menu" => $this->menu(),
            ]; 
            return $this->render("parametres/typevisite.html.twig",$data);            

        }
    }

    #[Route('/type_visite/supprimer/{id}', name: 'deltypevisite')]
    public function deltypevisite(Request $request,$id): Response
    {
        if ($request->getMethod()=='POST') {
            
            $this->permission->userAccess();

            try {
                $sendApi=$this->apiController->doDelete("type-visite/{$id}/delete",'Visite supprimé avec succès');

                if($sendApi < 300 ){
                    $this->addFlash('info',$this->session->get('response'));
                    $this->session->set('flash', 'info');
                    $this->permission->writeLog('Supprimer un typede visite identifiant :'.$id);
                }
                else{

                    $this->session->set('flash', 'error');
                    $this->addFlash('error',"Erreur  de suppression   de la visite");
                    $this->permission->writeLog("Erreur  de suppression   de la visite".$id);
                }

                return $this->redirectToRoute('typevisite');    

            } catch (\Exception $e) { 
                $this->session->set('flash', 'error');
                $this->addFlash('error',$this->session->get('response'));
                $this->permission->writeLog($e->getMessage());

                return $this->redirectToRoute('typevisite');
            }
        }
    }

    #[Route('/type_visite/desactiver/{id}', name: 'desactivertypevisite')]
    public function desactivertypevisite(Request $request,$id): Response
    {
        if ($request->getMethod()=='POST') {
            
            $this->permission->userAccess();
            try {
                $sendApi=$this->apiController->doActivet("type-visite/{$id}/desactivate",'Visite desactivé avec succès');

                if($sendApi < 300){
                    $this->addFlash('info',$this->session->get('response'));
                    $this->session->set('flash', 'info');
                    $this->permission->writeLog('Desactivation type de visite identifiant :'.$id);

                }
                else{

                    $this->session->set('flash', 'error');
                    $this->addFlash('error',"Erreur  de desactivation    de la visite");
                    $this->permission->writeLog("Erreur  de desactivation    de la visite".$id);
                }

               return $this->redirectToRoute('typevisite');    

           } catch (\Exception $e) { 

               $this->addFlash('error',$this->session->get('response'));
               $this->session->set('flash', 'error');
               $this->permission->writeLog($e->getMessage());

               return $this->redirectToRoute('typevisite');    
           }
        }
    }

    #[Route('/type_visite/activer/{id}', name: 'activertypevisite')]
    public function activertypevisite(Request $request,$id): Response
    {
        if ($request->getMethod()=='POST') {
            
            $this->permission->userAccess();
            try {
                $sendApi=$this->apiController->doActivet("type-visite/{$id}/activate",'Visite activé avec succès');

                if($sendApi < 300){

                    $this->addFlash('info',$this->session->get('response'));
                    $this->session->set('flash', 'info');
                    $this->permission->writeLog('Activation type de visite : données : '.$id);
                }
                else{

                    $this->session->set('flash', 'error');
                    $this->addFlash('error',"Erreur  d'activation    de la visite");
                    $this->permission->writeLog("Erreur  d'activation    de la visite".$id);
                }

               return $this->redirectToRoute('typevisite');    

           } catch (\Exception $e) { 

               $this->addFlash('error',$this->session->get('response'));
               $this->session->set('flash', 'error');
               $this->permission->writeLog($e->getMessage());

               return $this->redirectToRoute('typevisite');    
           }
        }
    }

    #[Route('/type_visite/filtre', name: 'recherchetypevisite')]
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
                $sendApi=$this->apiController->doGet("type-visites?$body");
                $data= [
                    "databd" => $sendApi,
                    "saisie" => $saisie,
                    "typepdv" => $this->typepdv(),
                    "statut" => $this->status(),
                    "tab" => 'lister',
                    "menu" => $this->menu(),
                ];
                $this->addFlash('info',$this->session->get('response'));
                $this->session->set('flash', 'info');

                return $this->render("parametres/typevisite.html.twig",$data);

            } catch (\Exception $e) {
                $this->addFlash('error',$this->session->get('response'));
                $this->session->set('flash', 'error');
                $this->permission->writeLog($e->getMessage());

                return $this->redirectToRoute('typevisite');              
            }

        }
    }

    #[Route('/type_visite/import', name: 'importtypevisite')]
    public function importtypevisite(Request $request): Response
    {
        if ($request->getMethod()=='POST') {
            
            $this->permission->userAccess();

            $typepdv=$request->get('typepdv');
            $filedata = $_FILES['listevisite']['tmp_name'];

            $body=[
                    'type_pdv' => implode(" , " , $typepdv),
                    'file' => curl_file_create($filedata , 'text/csv')
                ];

                try {
                    $sendApi=$this->apiController->doImport("type-visites/upload",$body) ;

                    if($sendApi < 300){
                        $this->addFlash('info',"Importation type de visite reussie");
                        $this->session->set('flash', 'info');
                        $this->permission->writeLog('Importation de type de visite');

                    }else{
                        $this->addFlash('error',"Echec d'Importation type de visite ");
                        $this->session->set('flash', 'info');
                        $this->permission->writeLog("Echec d'Importation type de visite");
                    }

                    return $this->redirectToRoute('typevisite');    

                } catch (\Exception $e) {
                    $this->session->set('flash', 'error');
                    $this->addFlash('error',$this->session->get('response'));
                    $this->permission->writeLog($e->getMessage());

                    return $this->redirectToRoute('importtypevisite');
                }

        }else {
            $data= [
                "typepdv" => $this->typepdv(),
                "tab" => 'importer',
                "menu" => $this->menu(),
            ]; 
            return $this->render("parametres/typevisite.html.twig",$data);
        }
    }
}
