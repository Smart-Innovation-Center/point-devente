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

class DysfonctionnementController extends AbstractController
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

    // GESTION DES formulaire
    //recuperer la liste de questionnaires
    private function data()
    {                      
        $this->permission->userAccess();
        try {  
            $databd=$this->apiController->doGet("params/dysfonctionnements") ;
            return $databd;
        } catch (\Exception $e) {
            $this->permission->writeLog($e->getMessage());
            $this->session->set('flash', 'error');
            $this->addFlash('error',"Une erreur est survenue impossible de charger les données !");

            return [];
        }
    }

    //recuperer un seul questionnaire
    private function dataSelect($id)
    {                    
        $this->permission->userAccess();
        try {  
            $databd=$this->apiController->doGet("params/dysfonctionnements/{$id}") ;
            return $databd;
        } catch (\Exception $e) {
            $this->permission->writeLog($e->getMessage());
            $this->session->set('flash', 'error');
            $this->addFlash('error',"Une erreur est survenue impossible de charger les données !");

            return [];
        }
    }
    
    //liste des type de champ
    private function typechamp(){
        return $this->config["typechamp"];
    }

    //menu
    private function menu(){
        $menu= [
            "1" => array ('formulaire','dysfonctionnement')
        ]; 
        return $menu;
    }

    #[Route('/dysfonctionnement', name: 'dysfonctionnement')]
    public function dysfonctionnement(): Response
    {
        $data= [
            "databd" => $this->data(),
            "tab" => 'lister',
            "typechamp" => $this->typechamp(),
            "menu" => $this->menu(),
        ];
        $this->permission->writeLog('liste des parametres du formulaires');

        return $this->render("formulaire/dysfonctionnement.html.twig",$data);
    }

    #[Route('/dysfonctionnement/ajouter', name: 'adddysfonctionnement')]
    public function adddysfonctionnement(Request $request): Response
    {
        if ($request->getMethod()=='POST') {
            
            $this->permission->userAccess();

            //recupération des infos à enregistrer
            $form_json=$request->get('form_json');
            $body=json_decode($form_json , true);
            $_POST = $form_json;

            try {
                                
                $sendApi=$this->apiController->doPost("params/dysfonctionnements",$body,'Dysfonctionnement ajouté avec sucès');

                if($sendApi < 300){
                    $this->addFlash('info',$this->session->get('response'));
                    $this->session->set('flash', 'info');
                    $this->permission->writeLog('Ajouter un parametre du formulaire');
                }
                else{

                    $this->session->set('flash', 'error');
                    $this->addFlash('error','Erreur ajout de dysfonctionnements ');
                    $this->permission->writeLog('Erreur ajout de dysfonctionnements ');
                }

                return $this->redirectToRoute('dysfonctionnement');
                
            } catch (\Exception $e) {
                $this->session->set('flash', 'error');
                $this->addFlash('error',$this->session->get('response'));
                $this->permission->writeLog($e->getMessage());

                return $this->redirectToRoute('adddysfonctionnement');
            }

        }else {
            $data= [
                "typechamp" => $this->typechamp(),
                "tab" => 'ajouter',
                "menu" => $this->menu(),
            ]; 
            return $this->render("formulaire/dysfonctionnement.html.twig",$data);  
        }
    }

    #[Route('/dysfonctionnement/modifier/{id}', name: 'editdysfonctionnement')]
    public function editdysfonctionnement(Request $request,$id): Response
    {
        if ($request->getMethod()=='POST') {
            
            $this->permission->userAccess();
                                
            //recupération des infos à enregistrer
            $form_json=$request->get('form_json');
            $body=json_decode($form_json , true);
            $_POST = $form_json;

            try {
                $sendApi=$this->apiController->doPut("params/dysfonctionnements/{$id}",$body,'Dysfonctionnement modifié avec succès ');

                if($sendApi < 300){
                    $this->addFlash('info',$this->session->get('response'));
                    $this->session->set('flash', 'info');
                    $this->permission->writeLog('Modifier un parametres identifiant :'.$id);
                }
                else{
                    $this->session->set('flash', 'error');
                    $this->addFlash('error','Erreur de modification Dysfonctionnement');
                    $this->permission->writeLog('Erreur de modification Dysfonctionnement'.$id);
                }

                return $this->redirectToRoute('dysfonctionnement');
                
            } catch (\Exception $e) {
                $this->session->set('flash', 'error');
                $this->addFlash('error',$this->session->get('response'));
                $this->permission->writeLog($e->getMessage());

                return $this->redirectToRoute('editdysfonctionnement',array('id'=>$id));
            }

        }else {
            $data= [
                "typechamp" => $this->typechamp(),
                "dataSelect" => $this->dataSelect($id),
                "tab" => 'modifier',
                "menu" => $this->menu(),
            ]; 

            $data["dysfonctionnement"] = json_encode($data["dataSelect"]);
            $data["dysfonctionnement"] = str_replace("'" , "’" , $data["dysfonctionnement"]);
            return $this->render("formulaire/dysfonctionnement.html.twig",$data);  
           
        }
    }

    #[Route('/dysfonctionnement/supprimer/{id}', name: 'deldysfonctionnement')]
    public function deldysfonctionnement(Request $request,$id): Response
    {
        if ($request->getMethod()=='POST') {
            
            $this->permission->userAccess();

            try {
                
                $sendApi=$this->apiController->doDelete("params/dysfonctionnements/{$id}",'Dysfonctionnement supprimé avec succès');

                if($sendApi < 300){
                    $this->addFlash('info',$this->session->get('response'));
                    $this->session->set('flash', 'info');
                    $this->permission->writeLog('Supprimer un parametres identifiant :'.$id);

                }
                else{

                    $this->session->set('flash', 'error');
                    $this->addFlash('error','Erreur de suppression  Dysfonctionnement');
                    $this->permission->writeLog('Erreur de suppression Dysfonctionnement'.$id);
                }

                return $this->redirectToRoute('dysfonctionnement');    

            } catch (\Exception $e) { 

                $this->addFlash('error',$this->session->get('response'));
                $this->session->set('flash', 'error');
                $this->permission->writeLog($e->getMessage());

                return $this->redirectToRoute('dysfonctionnement');    
            }
        }
    }
}
