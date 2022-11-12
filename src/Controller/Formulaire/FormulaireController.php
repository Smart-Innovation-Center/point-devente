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

class FormulaireController extends AbstractController
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
    private function data()
    {      
        $this->permission->userAccess();
        try {  
            $databd=$this->apiController->doGet("formulaires") ;
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
            $databd=$this->apiController->doGet("formulaires/{$id}") ;
            return $databd;
        } catch (\Exception $e) {
            $this->permission->writeLog($e->getMessage());
            $this->session->set('flash', 'error');
            $this->addFlash('error',"Une erreur est survenue impossible de charger les données !");

            return [];
        }
    }


    //recuperer liste des types de visite
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

    //liste des statuts
    private function status(){
        return $this->config["status"];
    }

    //liste des types de champs
    private function typechamp(){
        return $this->config["typechamp"];
    }

    //liste des paramétres du formulaires
    private function parametres(){
              
        $this->permission->userAccess();
        try {  
            $databd=$this->apiController->doGet("questionnaires") ;
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
            "1" => array ('formulaire','formulaires')
        ];
        return $menu;
    }

    #[Route('/formulaires', name: 'formulaire')]
    public function formulaire(): Response
    {
        $data= [
            "databd" => $this->data(),
            "statut" => $this->status(),
            "parametre" => $this->parametres(),
            "typevisite" => $this->typevisite(),
            "typechamp" => $this->typechamp(),
            "tab" => 'lister',
            "menu" => $this->menu(),
        ];
        $this->permission->writeLog('liste des formulairess');

        return $this->render("formulaire/formulaire.html.twig",$data);
    }

    #[Route('/formulaires/ajouter', name: 'addformulaire')]
    public function addformulaire(Request $request): Response
    {
        if ($request->getMethod()=='POST') {
            
            $this->permission->userAccess();
            //recupération des infos à enregistrer
            $form_json=$request->get('form_json');
            $_POST = $form_json;

            $body=json_decode($form_json , true);

            try {
                $sendApi=$this->apiController->doPost("formulaires",$body,'Formulaire ajouté avec succès');

                if($sendApi < 300){
                    $this->addFlash('info',$this->session->get('response'));
                    $this->session->set('flash', 'info');
                    $this->permission->writeLog('Ajouter formulaire');
                }
                else{

                    $this->session->set('flash', 'error');
                    $this->addFlash('error','Erreur ajout de  formulaire');
                    $this->permission->writeLog('Erreur ajout de  formulaire');
                }

                return $this->redirectToRoute('formulaire');

            } catch (\Exception $e) {
                $this->session->set('flash', 'error');
                $this->addFlash('error',$this->session->get('response'));
                $this->permission->writeLog($e->getMessage());

                return $this->redirectToRoute('addformulaire');
            }

        }else {
            $data= [
                "parametre" => $this->parametres(),
                "typevisite" => $this->typevisite(),
                "typechamp" => $this->typechamp(),
                "tab" => 'ajouter',
                "menu" => $this->menu(),
            ];
            return $this->render("formulaire/formulaire.html.twig",$data);

        }
    }

    #[Route('/formulaires/modifier/{id}', name: 'editformulaire')]
    public function editformulaire(Request $request,$id): Response
    {
        if ($request->getMethod()=='POST') {
            
            $this->permission->userAccess();

            //recupération des infos à enregistrer
            $form_json=$request->get('form_json');
            $body=json_decode($form_json , true);
            $_POST = $form_json;

            try {
                $sendApi=$this->apiController->doPut("formulaire/{$id}/update",$body,'Formulaire modifié avec succès ');

                if($sendApi < 300) {

                    $this->addFlash('info',$this->session->get('response'));
                    $this->session->set('flash', 'info');
                    $this->permission->writeLog('Modifier un formulaire identifiant :'.$id);
                }
                else{

                    $this->session->set('flash', 'error');
                    $this->addFlash('error','Erreur de modification formulaire ');
                    $this->permission->writeLog('Erreur de modification formaulaire'.$id);
                }

                return $this->redirectToRoute('formulaire');

            } catch (\Exception $e) {
                $this->session->set('flash', 'error');
                $this->addFlash('error',$this->session->get('response'));
                $this->permission->writeLog($e->getMessage());

                return $this->redirectToRoute('editformulaire',array('id'=>$id));
            }

        }else {

            $data= [
                "dataSelect" => $this->dataSelect($id),
                "parametre" => $this->parametres(),
                "typevisite" => $this->typevisite(),
                "typechamp" => $this->typechamp(),
                "tab" => 'modifier',
                "menu" => $this->menu(),
            ];
            $data["form_data"] = json_encode($data["dataSelect"]['formulaire']['sections']);
            $data["form_data"] = str_replace("'" , "’" , $data["form_data"]);
            return $this->render("formulaire/formulaire.html.twig",$data);
        }
    }
   

    #[Route('/prevalisation/ajouter', name: 'addformprevalisation')]
    public function addformprevalisation(Request $request): Response
    {
        if ($request->getMethod()=='POST') {
            
            $this->permission->userAccess();
            //recupération des infos à enregistrer
            $form_json=$request->get('form_json');
            $_POST = $form_json;

            $body=json_decode($form_json , true);

            try {
                $sendApi=$this->apiController->doPost("formulaires",$body,'Formulaire de Délocalisation\Infiltration  ajouté avec succès');

                if($sendApi < 300) {
                    $this->addFlash('info',$this->session->get('response'));
                    $this->session->set('flash', 'info');
                    $this->permission->writeLog('Ajouter formulaire de prévalidation');
                }
                else{

                    $this->session->set('flash', 'error');
                    $this->addFlash('error','Erreur de ajoute  formulaire de prévalidation ');
                    $this->permission->writeLog('Erreur de ajoute  formulaire de prévalidation');
                }

                return $this->redirectToRoute('formulaire');

            } catch (\Exception $e) {
                $this->session->set('flash', 'error');
                $this->addFlash('error',$this->session->get('response'));
                $this->permission->writeLog($e->getMessage());

                return $this->redirectToRoute('addformprevalisation');
            }

        }else {
            $data= [
                "typechamp" => $this->typechamp(),
                "parametre" => $this->parametres(),
                "typevisite" => $this->typevisite(),
                "tab" => 'ajouter2',
                "menu" => $this->menu(),
            ];
            return $this->render("formulaire/formulaire.html.twig",$data);

        }
    }

    #[Route('/prevalisation/modifier/{id}', name: 'editformprevalisation')]
    public function editformprevalisation(Request $request,$id): Response
    {
        if ($request->getMethod()=='POST') {
            
            $this->permission->userAccess();

            //recupération des infos à enregistrer
            $form_json=$request->get('form_json');
            $body=json_decode($form_json , true);
            $_POST = $form_json;

            try {
                $sendApi=$this->apiController->doPut("formulaire/{$id}/update",$body,'Formulaire de Délocalisation\Infiltration  modifié avec succès');

                if($sendApi < 300) {

                    $this->addFlash('info',$this->session->get('response'));
                    $this->session->set('flash', 'info');
                    $this->permission->writeLog('Modifier un formulaire de prévalisation identifiant :'.$id);
                }
                else {
                    $this->session->set('flash', 'error');
                    $this->addFlash('error','Erreur de  Modifier du formulaire de prévalisation identifiant');
                    $this->permission->writeLog('Erreur de  Modifier du formulaire de prévalisation identifiant'.$id);
                }

                return $this->redirectToRoute('formulaire');

            } catch (\Exception $e) {
                $this->session->set('flash', 'error');
                $this->addFlash('error',$this->session->get('response'));
                $this->permission->writeLog($e->getMessage());

                return $this->redirectToRoute('editformprevalisation',array('id'=>$id));
            }

        }else {

            $data= [
                "dataSelect" => $this->dataSelect($id),
                "typechamp" => $this->typechamp(),
                "parametre" => $this->parametres(),
                "typevisite" => $this->typevisite(),
                "tab" => 'modifier2',
                "menu" => $this->menu(),
            ];
            $data["prevalidation"] = json_encode($data["dataSelect"]['formulaire']['sections']);
            $data["prevalidation"] = str_replace("'" , "’" , $data["prevalidation"]);
            die(var_dump($data["dataSelect"]['formulaire']));
            return $this->render("formulaire/formulaire.html.twig",$data);
        }
    }

    #[Route('/formulaires/desactiver/{id}', name: 'desactiverformulaire')]
    public function desactiverformulaire(Request $request,$id): Response
    {
        if ($request->getMethod()=='POST') {
            
            $this->permission->userAccess();
            try {
                $sendApi=$this->apiController->doActivet("formulaire/{$id}/desactivate",'Formulaire de Délocalisation\Infiltration desactivé');

                if($sendApi < 300){
                    $this->addFlash('info',$this->session->get('response'));
                    $this->session->set('flash', 'info');
                    $this->permission->writeLog('Desactivation formulaire identifiant :'.$id);
                }
                else{
                    $this->session->set('flash', 'error');
                    $this->addFlash('error','Erreur de Desactivation  du formulaire de Délocalisation\Infiltration');
                    $this->permission->writeLog('Erreur de  Desactivation du formulaire de Délocalisation\Infiltration identifiant'.$id);
                }

                return $this->redirectToRoute('formulaire');

            } catch (\Exception $e) {

                $this->addFlash('error',$this->session->get('response'));
                $this->session->set('flash', 'error');
                $this->permission->writeLog($e->getMessage());

                return $this->redirectToRoute('formulaire');
            }
        }
    }

    #[Route('/formulaires/activer/{id}', name: 'activerformulaire')]
    public function activerformulaire(Request $request,$id): Response
    {
        if ($request->getMethod()=='POST') {
            
            $this->permission->userAccess();
            try {
                $sendApi=$this->apiController->doActivet("formulaire/{$id}/activate",'Formulaire de Délocalisation\Infiltration activé');

                if($sendApi < 300){
                    $this->addFlash('info',$this->session->get('response'));
                    $this->session->set('flash', 'info');
                    $this->permission->writeLog('Activation formulaire : données : '.$id);
                }
                else{
                    $this->session->set('flash', 'error');
                    $this->addFlash('error','Erreur de activation  du formulaire de Délocalisation\Infiltration');
                    $this->permission->writeLog('Erreur de  activation du formulaire de Délocalisation\Infiltration identifiant'.$id);
                }

                return $this->redirectToRoute('formulaire');

            } catch (\Exception $e) {

                $this->addFlash('error',$this->session->get('response'));
                $this->session->set('flash', 'error');
                $this->permission->writeLog($e->getMessage());

                return $this->redirectToRoute('formulaire');
            }
        }
    }

    #[Route('/formulaires/supprimer/{id}', name: 'delformulaire')]
    public function delformulaire(Request $request,$id): Response
    {
        if ($request->getMethod()=='POST') {
            
            $this->permission->userAccess();
            try {

                $sendApi=$this->apiController->doDelete("formulaire/{$id}/delete",'Formulaire de Délocalisation\Infiltration suprimé avec succès');

                if ($sendApi < 300 ){
                    $this->addFlash('info',$this->session->get('response'));
                    $this->session->set('flash', 'info');
                    $this->permission->writeLog('Supprimer un formulaire identifiant :'.$id);
                }
                else{

                    $this->session->set('flash', 'error');
                    $this->addFlash('error','Erreur de suppression   du formulaire de Délocalisation\Infiltration');
                    $this->permission->writeLog('Erreur de  suppression du formulaire de Délocalisation\Infiltration identifiant'.$id);
                }

                return $this->redirectToRoute('formulaire');

            } catch (\Exception $e) {

                $this->addFlash('error',$this->session->get('response'));
                $this->session->set('flash', 'error');
                $this->permission->writeLog($e->getMessage());

                return $this->redirectToRoute('formulaire');
            }
        }
    }

    #[Route('/formulaires/filtre', name: 'rechercheformulaire')]
    public function recherche(Request $request): Response
    {
        if ($request->getMethod()=='POST') {
            
            $this->permission->userAccess();

            //recupération des infos à enregistrer
            $status=$request->get('status');
            $typevisite=$request->get('typevisite');

            $saisie=[
                'typevisite'=> $typevisite,
                'status'=> $status
            ];
            $body="typeVisite=".$typevisite."&actived=".$status;

            try {
                $sendApi=$this->apiController->doGet("formulaires/search?$body");
                $data= [
                    "databd" => $sendApi,
                    "saisie" => $saisie,
                    "statut" => $this->status(),
                    "parametre" => $this->parametres(),
                    "typevisite" => $this->typevisite(),
                    "typechamp" => $this->typechamp(),
                    "tab" => 'lister',
                    "menu" => $this->menu(),
                ];
                
                return $this->render("formulaire/formulaire.html.twig",$data);

            } catch (\Exception $e) {
                $this->addFlash('error',$this->session->get('response'));
                $this->session->set('flash', 'error');
                $this->permission->writeLog($e->getMessage());

                return $this->redirectToRoute('formulaire');
            }

        }
    }
}
