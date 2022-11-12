<?php

namespace App\Controller\Pdv;

use App\Controller\ServicePermissionController;
use App\Controller\Admin\RequestController;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Session\SessionInterface;

class PdvController extends AbstractController
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

    // GESTION DES PDV
    //recuperer la liste de pdv
    private function data($offset)
    {                     
        $this->permission->userAccess();
        try {  
            $databd=$this->apiController->doGet("pdvs?offset={$offset}&limit=50") ;
            return $databd;
        } catch (\Exception $e) {
            $this->permission->writeLog($e->getMessage());
            $this->session->set('flash', 'error');
            $this->addFlash('error',"Une erreur est survenue impossible de charger les données !");

            return [];
        }
    }

    //recuperer un seul pdv
    private function dataSelect($id)
    {                     
        $this->permission->userAccess();
        try {  
            $databd=$this->apiController->doGet("pdv/{$id}") ;
            return $databd;
        } catch (\Exception $e) {
            $this->permission->writeLog($e->getMessage());
            $this->session->set('flash', 'error');
            $this->addFlash('error',"Une erreur est survenue impossible de charger les données !");

            return [];
        }
    }

    //liste des types pdv
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
    
    //liste des profiles
    private function profile(){
                             
        $this->permission->userAccess();
        try {  
            $databd=$this->apiController->doGet("profilesPdv") ;
            return $databd;
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
    //recuperer liste des region
    public function region(){
                             
        $this->permission->userAccess();
        try {  
            $databd=$this->apiController->doGet("regions") ;
            return $databd;
        } catch (\Exception $e) {
            $this->permission->writeLog($e->getMessage());
            $this->session->set('flash', 'error');
            $this->addFlash('error',"Une erreur est survenue impossible de charger les données !");

            return [];
        }
    }

    //recuperer liste des villes
    public function ville(){
                             
        $this->permission->userAccess();
        try {  
            $databd=$this->apiController->doGet("villes") ;
            return $databd;
        } catch (\Exception $e) {
            $this->permission->writeLog($e->getMessage());
            $this->session->set('flash', 'error');
            $this->addFlash('error',"Une erreur est survenue impossible de charger les données !");

            return [];
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

    
    //liste des acteurs
    #[Route('/pdv/listeActeurPartenaire/{id}', name: 'listePartenaireacteurs')]
    public function  acteurPartenaire(Request $request,$id)
    {   
        $this->permission->userAccess();
        try {  
            $ville=$this->apiController->doGet("acteurs?{$id}")['content'];
            $response = new Response(json_encode($ville));
            return $response;
        } catch (\Exception $e) {
            $this->permission->writeLog($e->getMessage());
            $this->session->set('flash', 'error');
            $this->addFlash('error',"Une erreur est survenue impossible de charger les données !");

            return [];
        }
    }

    //liste des statuts
    private function status(){
        return $this->config["status"];
    }

    //menu
    private function menu(){
        $menu= [
            "1" => array ('pdv','pdv')
        ]; 
        return $menu;
    }

    #[Route('/pdv/getSearchPdv/{id}', name: 'getSearchPdv')]
    public function getSearchPdv($id)
    {
        $this->permission->userAccess();

        //$offset = $_GET['iDisplayStart'];

        try {
            $pdv_data=$this->apiController->doGet("pdvs?{$id}");
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

    #[Route('/pdv/getPdv', name: 'getPdv')]
    public function getPdv(): Response
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

    #[Route('/pdv', name: 'pdv')]
    public function index(): Response
    {
        $data= [
            "databd" => true,
            "filtre" => false,
            "statut" => $this->status(),
            "typepdv" => $this->typepdv(),
            "partenaire" => $this->partenaire(),
            "region" => $this->region(),
            "ville" => $this->ville(),
            "tab" => 'lister',
            "menu" => $this->menu(),
        ];
        $this->permission->writeLog('liste des PDV');
        return $this->render("pdv/pdv.html.twig",$data);
    }

    #[Route('/pdv/ajouter', name: 'addpdv')]
    public function addpdv(Request $request): Response
    {
        if ($request->getMethod()=='POST') {
            
            $this->permission->userAccess();
            //recupération des infos à enregistrer
            $numero_tete_pont=$request->get('numero_tete_pont');
            $nom_commercial=$request->get('nom_commercial');
            $entite_controle=$request->get('entite_controle');
            $nom_responsable=$request->get('nom_responsable');
            $contact_responsable=$request->get('contact_responsable');
            $numero_pdv=$request->get('numero_pdv');
            $localisation=$request->get('localisation');
            $typepdv=$request->get('typepdv');
            $profilePdv=$request->get('profilePdv');
            $typeacteur=$request->get('typeacteur');
            $partenaire=$request->get('partenaire');
            $ville=$request->get('ville');
            $numero_revendeur=$request->get('numero_revendeur');

            $body=[
                'numeroRevendeur'=> $numero_revendeur,
                'entiteControle'=> $entite_controle,
                'nom_responsable'=> $nom_responsable,
                'contact_responsable'=> $contact_responsable,
                'numero_tete_pont'=> $numero_tete_pont,
                'nom_commercial'=> $nom_commercial,
                'numero_pdv'=> $numero_pdv,
                'initialAdresse'=> $localisation,
                'type_pdv'=> $typepdv,
                'profilePdv'=> $profilePdv,
                'type_acteur'=> $typeacteur,
                'partenaire'=> $partenaire,
                'ville'=> $ville
            ];

            try {
                $sendApi=$this->apiController->doPost("pdvs",$body,'PDV ajouté avec succès');

                if( $sendApi < 300 ){
                    $this->addFlash('info',$this->session->get('response'));
                    $this->session->set('flash', 'info');
                    $this->permission->writeLog('Ajouter un PDV');
                }
                else{
                    $this->session->set('flash', 'error');
                    $this->addFlash('error',"Erreur  d'ajoute de  pdv");
                    $this->permission->writeLog("Erreur  d'ajoute de pdv");
                }

                return $this->redirectToRoute('pdv');    
                
            } catch (\Exception $e) {
                $this->session->set('flash', 'error');
                $this->addFlash('error',$this->session->get('response'));
                $this->permission->writeLog('Erreur d\'ajout PDV : données : '.json_encode($body));

                return $this->redirectToRoute('addpdv');
            }

        }else {
            $data= [
                "typepdv" => $this->typepdv(),
                "profile" => $this->profile(),
                "partenaire" => $this->partenaire(),
                "typeacteur" => $this->typeacteur(),
                "region" => $this->region(),
                "ville" => $this->ville(),
                "tab" => 'ajouter',
                "menu" => $this->menu(),
            ]; 
            return $this->render("pdv/pdv.html.twig",$data);  
        }
    }

    #[Route('/pdv/modifier/{id}', name: 'editpdv')]
    public function editpdv(Request $request,$id): Response
    {
        if ($request->getMethod()=='POST') {
            
            $this->permission->userAccess();

            //recupération des infos à enregistrer
            $numero_tete_pont=$request->get('numero_tete_pont');
            $nom_commercial=$request->get('nom_commercial');
            $entite_controle=$request->get('entite_controle');
            $nom_responsable=$request->get('nom_responsable');
            $contact_responsable=$request->get('contact_responsable');
            $numero_pdv=$request->get('numero_pdv');
            $localisation=$request->get('localisation');
            $typepdv=$request->get('typepdv');
            $profilePdv=$request->get('profilePdv');
            $typeacteur=$request->get('typeacteur');
            $partenaire=$request->get('partenaire');
            $ville=$request->get('ville');
            $numero_revendeur=$request->get('numero_revendeur');

            $body=[
                'numeroRevendeur'=> $numero_revendeur,
                'entiteControle'=> $entite_controle,
                'nom_responsable'=> $nom_responsable,
                'contact_responsable'=> $contact_responsable,
                'numero_tete_pont'=> $numero_tete_pont,
                'nom_commercial'=> $nom_commercial,
                'numero_pdv'=> $numero_pdv,
                'initialAdresse'=> $localisation,
                'type_pdv'=> $typepdv,
                'profilePdv'=> $profilePdv,
                'type_acteur'=> $typeacteur,
                'partenaire'=> $partenaire,
                'ville'=> $ville
            ];

            try {  
                $sendApi=$this->apiController->doPut("pdv/{$id}/update",$body,'PDV modifié avec succès');

                if($sendApi < 300){

                    $this->addFlash('info',$this->session->get('response'));
                    $this->session->set('flash', 'info');
                    $this->permission->writeLog('Modifier un PDV identifiant :'.$id);
                }
                else{
                    $this->session->set('flash', 'error');
                    $this->addFlash('error',"Erreur  de modification pdv");
                    $this->permission->writeLog("Erreur  de modification ");
                }

                return $this->redirectToRoute('pdv');    

            } catch (\Exception $e) {
                $this->session->set('flash', 'error');
                $this->addFlash('error',$this->session->get('response'));
                $this->permission->writeLog('Erreur de modification PDV : données : '.json_encode($body));

                return $this->redirectToRoute('editpdv',array('id'=>$id));
            }
        }else {  

            $data= [
                "dataSelect" => $this->dataSelect($id),
                "typepdv" => $this->typepdv(),
                "profile" => $this->profile(),
                "partenaire" => $this->partenaire(),
                "typeacteur" => $this->typeacteur(),
                "region" => $this->region(),
                "ville" => $this->ville(),
                "tab" => 'modifier',
                "menu" => $this->menu(),
            ]; 
            return $this->render("pdv/pdv.html.twig",$data);            

        }
    }

    #[Route('/pdv/fiche', name: 'addfichepdv')]
    public function addfichepdv(Request $request): Response
    {

        if ($request->getMethod()=='POST') {
            
            //recupération des infos à enregistrer
            $pdvs=$request->get('listepdvId');
            $controleur=$request->get('listeActeurs');
             $pdvsT = explode(',',$pdvs);
             $pdvsTT = array_map('intval',$pdvsT);

            $body=$pdvsTT;

            try {
                $sendApi=$this->apiController->doPost("acteur/{$controleur}/pdvs",$body,'Fiche PDV ajoutée avec succès');

                if( $sendApi < 300 ){
                    $this->addFlash('info',$this->session->get('response'));
                    $this->session->set('flash', 'info');
                    $this->permission->writeLog('Ajouter une fiche');
                }
                else{
                    $this->session->set('flash', 'error');
                    $this->addFlash('error',"Erreur d'ajoute de la fiche");
                    $this->permission->writeLog("Erreur d'ajoute de  la fiche");
                }

                return $this->redirectToRoute('pdv');    
                
            } catch (\Exception $e) {
                $this->session->set('flash', 'error');
                $this->addFlash('error',$this->session->get('response'));
                $this->permission->writeLog('Erreur d\'ajout PDV : données : '.json_encode($body));

                return $this->redirectToRoute('addfichepdv');
            }

        }else {
            $data= [
                "partenaire" => $this->partenaire(),
                "typepdv" => $this->typepdv(),
                "typeacteur" => $this->typeacteur(),
                "region" => $this->region(),
                "tab" => 'fiche',
                "menu" => $this->menu(),
            ]; 
            return $this->render("pdv/pdv.html.twig",$data);  
        }
    }

    #[Route('/pdv/import', name: 'importpdv')]
    public function importpdv(Request $request): Response
    {
        if ($request->getMethod()=='POST') {
            
            $this->permission->userAccess();

            $typepdv=$request->get('typepdv');
            $partenaire=$request->get('partenaire');
            $ville=$request->get('ville');
            $typeacteur=$request->get('typeacteur');

            $filedata = $_FILES['listepdvs']['tmp_name'];

            $body=[
                'type_pdv' => $typepdv,
                'partenaire' => $partenaire,
                'ville' => $ville,
                'type_acteur' => implode(" , " , $typeacteur),
                'file' => curl_file_create($filedata , 'text/csv')
            ];

            try {
                $sendApi=$this->apiController->doImport("pdvs/upload",$body) ;

                if($sendApi < 300){

                    $this->addFlash('info','Importation PDV reussie');
                    $this->session->set('flash', 'info');
                    $this->permission->writeLog('Importation PDV');
                }
                else{

                    $this->addFlash('error',"Echec d'Importation PDV ");
                    $this->session->set('flash', 'info');
                    $this->permission->writeLog("Echec d'Importation PDV");
                }

                return $this->redirectToRoute('pdv');

            } catch (\Exception $e) {
                $this->session->set('flash', 'error');
                $this->addFlash('error',$this->session->get('response'));
                $this->permission->writeLog('Erreur d\'importation PDV');

                return $this->redirectToRoute('importpdv');
            }

        }else {
            $data= [
                "typepdv" => $this->typepdv(),
                "partenaire" => $this->partenaire(),
                "typeacteur" => $this->typeacteur(),
                "region" => $this->region(),
                "ville" => $this->ville(),
                "tab" => 'importer',
                "menu" => $this->menu(),
            ]; 
            return $this->render("pdv/pdv.html.twig",$data);
        }
    }
}
