<?php

namespace App\Controller\Formulaire;

use App\Controller\ServicePermissionController;
use App\Controller\Admin\RequestController;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Session\SessionInterface;

class PrevalisationController extends AbstractController
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

     // GESTION DES formulairesS
    //recuperer la liste des formulairess
    private function data($offset)
    {
        $this->permission->userAccess();
        try {
            $databd=$this->apiController->doGet("visites/prevalidations?offset={$offset}&limit=50") ;
           return $databd;
        } catch (\Exception $e) {
            $this->permission->writeLog($e->getMessage());
            $this->session->set('flash', 'error');
            $this->addFlash('error',"Une erreur est survenue impossible de charger les données !");

            return [];
        }
    }

    //recuperer un seul formulaires
    private function dataSelect($id)
    {
        $this->permission->userAccess();
        try {
            $databd=$this->apiController->doGet("visites/prevalidations/{$id}") ;
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

    #[Route('/prevalisation/getPrevalidation', name: 'getPrevalidation')]
    public function getPrevalidation(): Response
    {
        //$offset = $_GET['iDisplayStart'];
        $this->permission->userAccess();

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

    #[Route('/prevalisation/getSearchPreva/{id}', name: 'getSearchPreva')]
    public function getSearchPreva($id): Response
    {
        //$offset = $_GET['iDisplayStart'];
        $this->permission->userAccess();

        $num = $_GET['iDisplayStart'];
        $op=(int)$num/50;
         (int)$offset=$op;
        try {
            $pdv_data=$this->apiController->doGet("visites/prevalidations?{$id}&offset={$offset}&limit=50");
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

    //menu
    private function menu(){
        $menu= [
            "1" => array ('prevalisation','prevalisation')
        ];
        return $menu;
    }

    //liste des statuts
    private function status(){
        return $this->config["status"];
    }

    #[Route('/prevalisation', name: 'prevalisation')]
    public function prevalisation(): Response
    {
        $this->permission->userAccess();
        $data= [
            "databd" => true,
            "statut" => $this->status(),
            "tab" => 'lister',
            "menu" => $this->menu(),
        ];
        $this->permission->writeLog('liste des visites de prevalidation');

        return $this->render("formulaire/prevalisation.html.twig",$data);
    }

    //recuperer la signature
    private function signature($id){
        $this->permission->userAccess();
        try {
            $databd=$this->apiController->doGet("/visites/prevalidations/{$id}/sign") ;

            return $databd;
        } catch (\Exception $e) {
            $this->permission->writeLog($e->getMessage());
            $this->session->set('flash', 'error');
            $this->addFlash('error',"Une erreur est survenue impossible de charger les données !");

            return [];
        }
    }


    #[Route('/prevalisation/detail/{id}', name: 'detailprevalidationfiche')]
    public function detailfiche(Request $request,$id): Response
    {

        $data= [
            "dataSelect" => $this->dataSelect($id),
            "onglet" => 'Détail de la visite',
            "tab" => 'detailprevalidation',
            "menu" => $this->menu(),
            "signature" => $this->signature($id)
        ];

        $this->permission->writeLog('Detail fiche prévalidation identifiant :'.$id);

        $data['images'] = $data["dataSelect"]['response']['images'];
        $data['base_64_prefix'] = "data:image/jpeg;base64,";

        return $this->render("formulaire/prevalisation.html.twig",$data);
    }

    #[Route('/prevalisation/valider/{id}', name: 'validerform')]
    public function validerfiche(Request $request,$id): Response
    {
        if ($request->getMethod()=='POST') {

            $this->permission->userAccess();

            $body=[
                'motive'=> ''
            ];
            try {
                $sendApi=$this->apiController->doPost("visites/prevalidations/{$id}/validate?status=1",$body,'Visite de Délocalisation\Infiltration validé avec sucès');

                if($sendApi < 300){

                    $this->addFlash('info',$this->session->get('response'));
                    $this->session->set('flash', 'info');
                    $this->permission->writeLog('Fiche validee identifiant :'.$id);

                }else{

                    $this->session->set('flash', 'error');
                    $this->addFlash('error','Erreur  de validation de la  Visite de Délocalisation\Infiltration');
                    $this->permission->writeLog('Erreur  de validation de la  Visite de Délocalisation\Infiltration'.$id);
                }

                return $this->redirectToRoute('prevalisation');

            } catch (\Exception $e) {

                $this->addFlash('error',$this->session->get('response'));
                $this->session->set('flash', 'error');
                $this->permission->writeLog($e->getMessage());

                return $this->redirectToRoute('prevalisation');
            }
        }
    }

    #[Route('/prevalisation/invalider/{id}', name: 'invaliderform')]
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
                $sendApi=$this->apiController->doPost("visites/prevalidations/{$id}/validate?status=2",$body,'Visite de Délocalisation\Infiltration invalidé avec sucès');

                if($sendApi < 300) {
                    $this->addFlash('info',$this->session->get('response'));
                    $this->session->set('flash', 'info');
                    $this->permission->writeLog('Fiche invalidee identifiant :'.$id);
                }
                else {

                    $this->session->set('flash', 'error');
                    $this->addFlash('error','Erreur de invalidation de la visite Délocalisation\Infiltration ');
                    $this->permission->writeLog('Erreur de invalidation de la visite Délocalisation\Infiltration'.$id);
                }

                return $this->redirectToRoute('prevalisation');

            } catch (\Exception $e) {

                $this->addFlash('error',$this->session->get('response'));
                $this->session->set('flash', 'error');
                $this->permission->writeLog($e->getMessage());

                return $this->redirectToRoute('prevalisation');
            }
        }
    }

    #[Route('/prevalisation/ajouter', name: 'adddformprevalisation')]
    public function addformprevalisation(Request $request): Response
    {
        if ($request->getMethod()=='POST') {

            $this->permission->userAccess();
            //recupération des infos à enregistrer
            $form_json=$request->get('form_json');
            $_POST = $form_json;
            $body=json_decode($form_json , true);

            try {
                $sendApi=$this->apiController->doPost("prevalisation",$body,'Formualire de Délocalisation\Infiltration ajouté avec succès');

                if ($sendApi < 300) {
                    $this->addFlash('info',$this->session->get('response'));
                    $this->session->set('flash', 'info');
                    $this->permission->writeLog('Ajouter formulaire de prévalisation');

                }
                else{

                    $this->session->set('flash', 'error');
                    $this->addFlash('error','Erreur  ajoute de formaulaire de Délocalisation\Infiltration ');
                    $this->permission->writeLog('Erreur  ajoute de formaulaire de Délocalisation\Infiltration');
                }

                return $this->redirectToRoute('prevalisation');

            } catch (\Exception $e) {
                $this->session->set('flash', 'error');
                $this->addFlash('error',$this->session->get('response'));
                $this->permission->writeLog($e->getMessage());

                return $this->redirectToRoute('addformprevalisation');
            }

        }else {
            $data= [
                "typechamp" => $this->typechamp(),
                "tab" => 'ajouter',
                "menu" => $this->menu(),
            ];
            return $this->render("formulaire/prevalisation.html.twig",$data);

        }
    }

    #[Route('/prevalisation/modifier/{id}', name: 'editdformprevalisation')]
    public function editformprevalisation(Request $request,$id): Response
    {
        if ($request->getMethod()=='POST') {

            $this->permission->userAccess();

            //recupération des infos à enregistrer
            $form_json=$request->get('form_json');
            $body=json_decode($form_json , true);
            $_POST = $form_json;

            try {
                $sendApi=$this->apiController->doPut("prevalisation/{$id}/update",$body,'Formualire de Délocalisation\Infiltration modifié avec succès');

                if($sendApi < 300 ){
                    $this->addFlash('info',$this->session->get('response'));
                    $this->session->set('flash', 'info');
                    $this->permission->writeLog('Modifier un formulaire de prévalisation identifiant :'.$id);
                }
                else{

                    $this->session->set('flash', 'error');
                    $this->addFlash('error','Erreur  de modification  du formaulaire de Délocalisation\Infiltration ');
                    $this->permission->writeLog('Erreur  de modification  du formaulaire de Délocalisation\Infiltration');
                }

                return $this->redirectToRoute('prevalisation');

            } catch (\Exception $e) {
                $this->session->set('flash', 'error');
                $this->addFlash('error',$this->session->get('response'));
                $this->permission->writeLog($e->getMessage());

                return $this->redirectToRoute('editformprevalisation',array('id'=>$id));
            }

        }else {

            $data= [
                "dataSelect" => $this->dataSelect($id),
                "tab" => 'modifier',
                "menu" => $this->menu(),
            ];
            $data["prevalidation"] = json_encode($data["dataSelect"]['formulaire']['sections']);
            $data["prevalidation"] = str_replace("'" , "’" , $data["prevalidation"]);
            return $this->render("formulaire/prevalisation.html.twig",$data);
        }
    }
}
