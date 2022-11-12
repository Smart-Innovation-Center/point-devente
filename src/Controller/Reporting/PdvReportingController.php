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

class PdvReportingController extends AbstractController
{
    public $apiController;
    private $session;
    private $permission;

    public function __construct(SessionInterface $session,ServicePermissionController $permission)
    {       
        $this->apiController=new RequestController();
        $this->permission= $permission;
        $this->session = $session;
    }

    // GESTION DES REPORTING
    //recuperer la liste
    private function data($route)
    {                      
        $this->permission->userAccess();
        try {  
            $databd=$this->apiController->doGet($route) ;
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
            "1" => array ('reporting','pdv_reporting')
        ]; 
        return $menu;
    }

    #[Route('/pdv_reporting/getReportingPdv', name: 'getReportingPdv')]
    public function getReportingPdv(): Response
    {
      //  $offset = $_GET['iDisplayStart'];

        $this->permission->userAccess();

        $num = $_GET['iDisplayStart'];
        $op=(int)$num/50;
         (int)$offset=$op;
        try {
            $pdv_data = $this->data("pdvcontrolles?offset={$offset}&limit=50");
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

    #[Route('/pdv_reporting/getSearchReportingPdv/{id}', name: 'getSearchReportingPdv')]
    public function getSearchReportingPdv($id): Response
    {
     //   $offset = $_GET['iDisplayStart'];
        $this->permission->userAccess();

        $num = $_GET['iDisplayStart'];
        $op=(int)$num/50;
         (int)$offset=$op;
        try {
            $pdv_data = $this->data("{$id}?offset={$offset}&limit=50");
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

    #[Route('/pdv_reporting', name: 'pdvcontrole')]
    public function pdv(): Response
    {
        $data= [
            "databd" => $this->data("pdvs?offset=0&limit=50"),
            "tab" => 'lister',
            "onglet" => 'Liste des PDV',
            "menu" => $this->menu(),
        ];
        $this->permission->writeLog('liste des PDV reporting');
        return $this->render("reporting/pdv.html.twig",$data);
    }

    #[Route('/pdv_reporting/pdv_controle', name: 'pdv_reporting')]
    public function pdvcontrole(): Response
    {
        $data= [
            "databd" => true,
            "tab" => 'lister',
            "filtre" => false,
            "onglet" => 'Liste des PDV Contrôlés',
            "menu" => $this->menu(),
        ];
        $this->permission->writeLog('liste des PDV controles');
        return $this->render("reporting/pdv.html.twig",$data);
    }

    #[Route('/pdv_reporting/pdv_non_controle', name: 'pdvnoncontrole')]
    public function pdvnoncontrole(): Response
    {
        $data= [
            "databd" => true,
            "tab" => 'lister3',
            "filtre" => false,
            "onglet" => 'Liste des PDV non Contrôlés',
            "menu" => $this->menu(),
        ];
        $this->permission->writeLog('liste des PDV non controles');
        return $this->render("reporting/pdv.html.twig",$data);
    }

    #[Route('/pdv_reporting/geolocalisation_pdv_controle', name: 'geolocalisationcontrole')]
    public function geolocalisationcontrole(): Response
    {
       $test = $this->data("pdvcontrolles");
       if (!empty($test)) {
           $test=$test['content'];
       }
        $data= [
            "databdmap" => $test,
            "tab" => 'lister',
            "filtre" => false,
            "onglet" => 'PDV Contrôlés',
            "menu" => [
                "1" => array ('reporting','geolocalisationcontrole')
            ],
        ];
        return $this->render("reporting/geolocalisation.html.twig",$data);
    }

    #[Route('/pdv_reporting/geolocalisation_pdv_non_controle', name: 'geolocalisationnoncontrole')]
    public function geolocalisationnoncontrole(): Response
    {
        $test = $this->data("pdvnoncontrolles");
        if (!empty($test)) {
            $test=$test['content'];
        }
        //die(var_dump($test));
        $data= [
            "databdmap" => $test,
            "tab" => 'lister2',
            "filtre" => false,
            "onglet" => 'PDV non Contrôlés',
            "menu" => [
                "1" => array ('reporting','geolocalisationcontrole')
            ],
        ];
        return $this->render("reporting/geolocalisation.html.twig",$data);
    }

    #[Route('/pdv_reporting/filtre', name: 'rechercherpdvreporting')]
    public function recherche(Request $request): Response
    {
        if ($request->getMethod()=='POST') {
            
            $this->permission->userAccess();

            //recupération des infos à enregistrer
            $maroute=$request->get('maroute');
            if ($maroute==''){
                $maroute="pdvcontrolles";
            }

            $saisie=[
                'maroute'=> $maroute
            ];

            try {
                $sendApi=$this->apiController->doGet("$maroute?offset=1&limit=50");

                $data= [
                    "databd" => $sendApi['content'],
                    "saisie" => $saisie,
                    "tab" => 'lister',
                    "filtre" => true,
                    "onglet" => 'Liste des PDV',
                    "menu" => $this->menu(),
                ];
                $this->addFlash('info',$this->session->get('response'));
                $this->session->set('flash', 'info');

                return $this->render("reporting/pdv.html.twig",$data);

            } catch (\Exception $e) {
                $this->session->set('flash', 'error');
                $this->addFlash('error',$this->session->get('response'));
                $this->permission->writeLog($e->getMessage());

                return $this->redirectToRoute('pdv_reporting');
            }

        }
    }
}
