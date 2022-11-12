<?php

namespace App\Controller\Reporting;

use App\Controller\ServicePermissionController;
use App\Controller\Admin\RequestController;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Session\SessionInterface;

class HistoriqueVisiteController extends AbstractController
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

    // GESTION DES HISTORIQUES DE VISITE
    //recuperer la liste des historiques
    private function data($route)
    {              
        $this->permission->userAccess();
        try {  
            $databd=$this->apiController->doGet("$route") ;
            return $databd;
        } catch (\Exception $e) {
            $this->permission->writeLog($e->getMessage());
            $this->session->set('flash', 'error');
            $this->addFlash('error',"Une erreur est survenue impossible de charger les données !");

            return [];
        }
    }

    //recuperer un seul historiques
    private function dataSelect($route)
    {              
        $this->permission->userAccess();
        try {  
            $databd=$this->apiController->doGet("$route") ;
            return $databd;
        } catch (\Exception $e) {
            $this->permission->writeLog($e->getMessage());
            $this->session->set('flash', 'error');
            $this->addFlash('error',"Une erreur est survenue impossible de charger les données !");

            return [];
        }
    }

    //recuperer liste des acteurs
    private function acteur(){              
        $this->permission->userAccess();
        try {  
            $databd=$this->apiController->doGet("acteurs") ;
            return $databd;
        } catch (\Exception $e) {
            $this->permission->writeLog($e->getMessage());
            $this->session->set('flash', 'error');
            $this->addFlash('error',"Une erreur est survenue impossible de charger les données !");

            return [];
        }
    }


    //recuperer la signature
    private function signature($id){
        $this->permission->userAccess();
       // die($id);
        try {
            $databd=$this->apiController->doGet("visites/{$id}/sign") ;
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
    //recuperer liste des type de visites
    private function typevisite(){              
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
    //recuperer liste des regions
    private function region(){              
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

    //liste des statuts
    private function status(){
        return $this->config["status"];
    }

    //menu
    private function menu(){
        $menu= [
            "1" => array ('reporting','historiques')
        ];
        return $menu;
    }

    #[Route('/historiques/getControlleSupervisee', name: 'getControlleSupervisee')]
    public function getControlleSupervisee(): Response
    {
        //$offset = $_GET['iDisplayStart'];

        $this->permission->userAccess();

        $num = $_GET['iDisplayStart'];
        $op=(int)$num/50;
         (int)$offset=$op;
        try {
            $pdv_data = $this->data("supervsionDone?offset={$offset}&limit=50");
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

    #[Route('/historiques/getControlleEvaluee', name: 'getControlleEvaluee')]
    public function getControlleEvaluee(): Response
    {
       // $offset = $_GET['iDisplayStart'];

        $this->permission->userAccess();

        $num = $_GET['iDisplayStart'];
        $op=(int)$num/50;
         (int)$offset=$op;
        try {
            $pdv_data = $this->data("evaluationDone?offset={$offset}&limit=50");
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

    #[Route('/historiques/getControlleInvalidee', name: 'getControlleInvalidee')]
    public function getControlleInvalidee(): Response
    {
      //  $offset = $_GET['iDisplayStart'];

        $this->permission->userAccess();

        $num = $_GET['iDisplayStart'];
        $op=(int)$num/50;
         (int)$offset=$op;
        try {
            $pdv_data = $this->data("visites/search?status=2&offset={$offset}&limit=50");
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

    #[Route('/historiques/getControlleValidee', name: 'getControlleValidee')]
    public function getControlleValidee(): Response
    {
        //$offset = $_GET['iDisplayStart'];

        $this->permission->userAccess();

        $num = $_GET['iDisplayStart'];
        $op=(int)$num/50;
         (int)$offset=$op;
        try {
            $pdv_data = $this->data("visites/search?status=1&offset={$offset}&limit=50");
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

    #[Route('/historiques/getControlle', name: 'getControlle')]
    public function getControlle(): Response
    {
      //  $offset = $_GET['iDisplayStart'];
        $this->permission->userAccess();

        $num = $_GET['iDisplayStart'];
        $op=(int)$num/50;
         (int)$offset=$op;
        try {
            $pdv_data = $this->data("visites/search?offset={$offset}&limit=50");
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

    #[Route('/historiques/getSearchControlle/{id}', name: 'getSearchControlle')]
    public function getSearchControlle($id): Response
    {
     //   $offset = $_GET['iDisplayStart'];
        $this->permission->userAccess();

        $num = $_GET['iDisplayStart'];
        $op=(int)$num/50;
         (int)$offset=$op;
        try {
            $pdv_data=$this->apiController->doGet("visites/search?{$id}&offset={$offset}&limit=50");
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

    #[Route('/historiques/liste_ville/{id}', name: 'villes')]
    public function getvilles(Request $request,$id): Response
    {  
        $this->permission->userAccess();
        try {  
            $ville=$this->apiController->doGet("region/{$id}/villes");
            $response = new Response(json_encode($ville));
            return $response;
        } catch (\Exception $e) {
            $this->permission->writeLog($e->getMessage());
            $this->session->set('flash', 'error');
            $this->addFlash('error',"Une erreur est survenue impossible de charger les données !");

            return [];
        }
    }

    #[Route('/historiques', name: 'historiques')]
    public function historiques(): Response
    {
        $data= [
            "databd" => true,
            "filtre" => false,
            "partenaire" => $this->partenaire(),
            "typevisite" => $this->typevisite(),
            "region" => $this->region(),
            "onglet" => 'Visites de contrôle éffectuées',
            "tab" => 'lister',
            "menu" => $this->menu(),
        ];
        $this->permission->writeLog('liste des visites effectuees');
        return $this->render("reporting/historiques.html.twig",$data);
    }

    #[Route('/historiques/visites_evaluation', name: 'historiquesevaluation')]
    public function historiquesevaluation(): Response
    {
        $data= [
            "databd" => true,
            "filtre" => false,
            "acteur" => $this->acteur(),
            "partenaire" => $this->partenaire(),
            "typevisite" => $this->typevisite(),
            "region" => $this->region(),
            "onglet" => 'Visites d\'évaluation effectuées',
            "tab" => 'lister4',
            "menu" => $this->menu(),
        ];
        $this->permission->writeLog('liste des visites d\'evaluation');

        return $this->render("reporting/historiques.html.twig",$data);
    }

    #[Route('/historiques/visites_supervision', name: 'historiquessupervision')]
    public function historiquessupervision(): Response
    {
        $data= [
            "databd" => true,
            "filtre" => false,
            "acteur" => $this->acteur(),
            "partenaire" => $this->partenaire(),
            "typevisite" => $this->typevisite(),
            "region" => $this->region(),
            "onglet" => 'Visites de supervision effectuées',
            "tab" => 'lister5',
            "menu" => $this->menu(),
        ];
        $this->permission->writeLog('liste des visites de supervision');
        return $this->render("reporting/historiques.html.twig",$data);
    }

    #[Route('/historiques/visites_validees', name: 'historiquesvalider')]
    public function historiquesvalider(): Response
    {
        $data = [
            "databd" => true,
            "filtre" => false,
            "onglet" => 'Visites Validées',
            "tab" => 'lister2',
            "menu" => $this->menu(),
        ];
        $this->permission->writeLog('liste des visites validees');
        return $this->render("reporting/historiques.html.twig", $data);
    }

    #[Route('/historiques/visites_invalider', name: 'historiquesinvalider')]
    public function historiquesinvalider(): Response
    {
        $data= [
            "databd" => true,
            "filtre" => false,
            "onglet" => 'Visites Invalider',
            "tab" => 'lister3',
            "menu" => $this->menu(),
        ];
        $this->permission->writeLog('liste des visites invalidees');
        return $this->render("reporting/historiques.html.twig",$data);
    }

    #[Route('/historiques/invalider/{id}', name: 'invaliderfiche')]
    public function invaliderfiche(Request $request,$id): Response
    {
        if ($request->getMethod()=='POST') {
            
            $this->permission->userAccess();

            //recupération des infos à enregistrer
            $motif=$request->get('motif');
            $body=[
                'motive'=> $motif
            ];

            try {
                $sendApi=$this->apiController->doPut("visitecontrol/{$id}/nok",$body,'Fiche invalideé avec succès');

                if($sendApi < 300){

                    $this->addFlash('info',$this->session->get('response'));
                    $this->session->set('flash', 'info');
                    $this->permission->writeLog('Fiche invalidee identifiant :'.$id);

                }
                else{

                    $this->session->set('flash', 'error');
                    $this->addFlash('error',"Erreur  d'invaliditeé de fiche");
                    $this->permission->writeLog("Erreur  d'invaliditeé de fiche");
                }

                return $this->redirectToRoute('historiques');

            } catch (\Exception $e) {

                $this->addFlash('error',$this->session->get('response'));
                $this->session->set('flash', 'error');
                $this->permission->writeLog($e->getMessage());

                return $this->redirectToRoute('historiques');
            }
        }
    }

    #[Route('/historiques/reaffecter/{id}', name: 'reaffecterfiche')]
    public function reaffecterfiche(Request $request,$id): Response
    {
        if ($request->getMethod()=='POST') {
            
            $this->permission->userAccess();
            $acteur=$request->get('acteur');
            $typeVisit=$request->get('typeVisit');

            try {
                $sendApi=$this->apiController->doPost("visite/{$id}/reaffectation?acteur={$acteur}&typeVisite={$typeVisit}",[],'Fiche reaffecteé avec succès');

                if($sendApi < 300){
                    $this->addFlash('info',$this->session->get('response'));
                    $this->session->set('flash', 'info');
                    $this->permission->writeLog('Fiche reaffectee identifiant :'.$id);
                }else{
                    $this->session->set('flash', 'error');
                    $this->addFlash('error',"Erreur  de reacffectation de fiche ");
                    $this->permission->writeLog("Erreur  de reacffectation de fiche ");
                }

                return $this->redirectToRoute('historiques');

            } catch (\Exception $e) {
                $this->addFlash('error',$this->session->get('response'));
                $this->session->set('flash', 'error');
                $this->permission->writeLog($e->getMessage());

                return $this->redirectToRoute('historiques');
            }
        }
    }

    #[Route('/historiques/valider/{id}', name: 'validerfiche')]
    public function validerfiche(Request $request,$id): Response
    {
        if ($request->getMethod()=='POST') {
            
            $this->permission->userAccess();
            try {
                $sendApi=$this->apiController->doActivet("visitecontrol/{$id}/ok",'Fiche valideé avec succès');

                if($sendApi < 300){
                    $this->addFlash('info',$this->session->get('response'));
                    $this->session->set('flash', 'info');
                    $this->permission->writeLog('Fiche validee identifiant :'.$id);
                }else{

                    $this->session->set('flash', 'error');
                    $this->addFlash('error',"Erreur  de valideé de fiche ");
                    $this->permission->writeLog("Erreur  de valideé de fiche ");
                }

                return $this->redirectToRoute('historiques');

            } catch (\Exception $e) {

                $this->addFlash('error',$this->session->get('response'));
                $this->session->set('flash', 'error');
                $this->permission->writeLog($e->getMessage());

                return $this->redirectToRoute('historiques');
            }
        }
    }

    #[Route('/historiques/detail/{id}', name: 'detailfiche')]
    public function detailfiche(Request $request,$id): Response
    {

        $data= [
            "dataSelect" => $this->dataSelect("visite/{$id}"),
            "acteur" => $this->acteur(),
            "typevisite" => $this->typevisite(),
            "typeacteur" => $this->typeacteur(),
            "onglet" => 'Détail de la visite',
            "tab" => 'detail',
            "menu" => $this->menu(),
            "signature" => $this->signature($id)
        ];


        $this->permission->writeLog('Detail fiche identifiant :'.$id);

        $data['images'] = $data["dataSelect"]['response']['images'];
        $data['base_64_prefix'] = "data:image/jpeg;base64,";

        return $this->render("reporting/historiques.html.twig",$data);
    }

    #[Route('/historiques/detailevaluation/{id}', name: 'detailevaluation')]
    public function detailevaluation(Request $request,$id): Response
    {
        $data= [
            "dataSelect" => $this->dataSelect("visite/{$id}"),
            "acteur" => $this->acteur(),
            "typevisite" => $this->typevisite(),
            "typeacteur" => $this->typeacteur(),
            "onglet" => 'Détail de la visite',
            "tab" => 'detailevaluation',
            "menu" => $this->menu(),
            "signature" => $this->signature($id)
        ];
        $this->permission->writeLog('Detail fiche identifiant :'.$id);

        $data['images'] = $data["dataSelect"]['response']['images'];
        $data['base_64_prefix'] = "data:image/jpeg;base64,";

        return $this->render("reporting/historiques.html.twig",$data);
    }

    #[Route('/historiques/detailsupervision/{id}', name: 'detailsupervision')]
    public function detailsupervision(Request $request,$id): Response
    {
        $data= [
            "dataSelect" => $this->dataSelect("visite/{$id}"),
            "acteur" => $this->acteur(),
            "typevisite" => $this->typevisite(),
            "typeacteur" => $this->typeacteur(),
            "onglet" => 'Détail de la visite',
            "tab" => 'detailsupervision',
            "menu" => $this->menu(),
            "signature" => $this->signature($id)
        ];
        $this->permission->writeLog('Detail fiche identifiant :'.$id);

        $data['images'] = $data["dataSelect"]['response']['images'];
        $data['base_64_prefix'] = "data:image/jpeg;base64,";

        return $this->render("reporting/historiques.html.twig",$data);
    }

    #[Route('/historiques/detailadministration/{id}', name: 'detailadministration')]
    public function detailadministration(Request $request,$id): Response
    {
        $data= [
            "dataSelect" => $this->dataSelect("visite/{$id}"),
            "acteur" => $this->acteur(),
            "typevisite" => $this->typevisite(),
            "typeacteur" => $this->typeacteur(),
            "onglet" => 'Détail de la visite',
            "tab" => 'detailadministration',
            "menu" => $this->menu(),
            "signature" => $this->signature($id)
        ];
        $this->permission->writeLog('Detail fiche identifiant :'.$id);

        $data['images'] = $data["dataSelect"]['response']['images'];
        $data['base_64_prefix'] = "data:image/jpeg;base64,";

        return $this->render("reporting/historiques.html.twig",$data);
    }

    #[Route('/historiques/fiche_synthese_controle/{id}', name: 'fichesynthesecontrole')]
    public function fichesynthesecontrole(Request $request,$id): Response
    {
        $data= [
            "databd" => $this->dataSelect("visit/{$id}/synthese"),
            "onglet" => 'Fichier synthèse de la visite de contrôle',
            "tab" => 'detailcontrole',
            "menu" => $this->menu()
        ];
        $this->permission->writeLog('Detail fichier synthese standard identifiant :'.$id);

        return $this->render("reporting/historiques.html.twig",$data);
    }

    #[Route('/historiques/fiche_synthese_evaluation/{id}', name: 'fichesyntheseevaluation')]
    public function fichesyntheseevaluation(Request $request,$id): Response
    {
        $data= [
            "databd" => $this->dataSelect("visit/{$id}/synthese"),
            "onglet" => 'Fichier synthèse de la visite d\'évaluation',
            "tab" => 'detailefichevaluation',
            "menu" => $this->menu()
        ];
        $this->permission->writeLog('Detail fichier synthese evaluation identifiant :'.$id);

        return $this->render("reporting/historiques.html.twig",$data);
    }
}
