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

class ParametreFormulaireController extends AbstractController
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
            $databd=$this->apiController->doGet("questionnaires") ;
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
            $databd=$this->apiController->doGet("questionnaire/{$id}") ;
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

    //liste des statuts
    private function status(){
        return $this->config["status"];
    }

    //menu
    private function menu(){
        $menu= [
            "1" => array ('formulaire','parametreformulaire')
        ]; 
        return $menu;
    }

    #[Route('/parametre_formulaire', name: 'parametreformulaire')]
    public function parametreformulaire(): Response
    {
        $data= [
            "databd" => $this->data(),
            "tab" => 'lister',
            "typechamp" => $this->typechamp(),
            "statut" => $this->status(),
            "menu" => $this->menu(),
        ];
        $this->permission->writeLog('liste des parametres du formulaires');

        return $this->render("formulaire/parametres.html.twig",$data);
    }

    #[Route('/parametre_formulaire/ajouter', name: 'addparametreformulaire')]
    public function addparametreformulaire(Request $request): Response
    {
        if ($request->getMethod()=='POST') {
            
            $this->permission->userAccess();

            //recupération des infos à enregistrer
            $libelle=$request->get('libelle');
            $type_champ=$request->get('type_champ');
            $listeoption=$request->get('listeoption');

            $body=[
                'questionnaire'=> [
                    'libelle'=> $libelle,
                    'type'=> $type_champ,
                    'listeoption'=> $listeoption
                ]
            ];

            try {                
                $sendApi=$this->apiController->doPost("questionnaires",$body,'Paramettre du formulaire ajoutè avec succès');

                if($sendApi < 300){
                    $this->addFlash('info',$this->session->get('response'));
                    $this->session->set('flash', 'info');
                    $this->permission->writeLog('Ajouter un parametre du formulaire');

                }
                else{
                    $this->session->set('flash', 'error');
                    $this->addFlash('error','Erreur  de ajoute Paramettre du formulaire');
                    $this->permission->writeLog('Erreur  de ajoute Paramettre du formulaire');
                }

                return $this->redirectToRoute('parametreformulaire');
                
            } catch (\Exception $e) {
                $this->session->set('flash', 'error');
                $this->addFlash('error',$this->session->get('response'));
                $this->permission->writeLog($e->getMessage());

                return $this->redirectToRoute('addparametreformulaire');
            }

        }else {
            $data= [
                "typechamp" => $this->typechamp(),
                "tab" => 'ajouter',
                "menu" => $this->menu(),
            ]; 
            return $this->render("formulaire/parametres.html.twig",$data);  
        }
    }

    #[Route('/parametre_formulaire/modifier/{id}', name: 'editparametreformulaire')]
    public function editparametreformulaire(Request $request,$id): Response
    {
        if ($request->getMethod()=='POST') {
            
            $this->permission->userAccess();
                                
            //recupération des infos à enregistrer
            $libelle=$request->get('libelle');
            $type_champ=$request->get('type_champ');
            $listeoption=$request->get('listeoption');

            $body=[
                'questionnaire'=> [
                    'libelle'=> $libelle,
                    'type'=> $type_champ,
                    'listeoption'=> $listeoption
                ]
            ];

            try {
                $sendApi=$this->apiController->doPut("questionnaire/{$id}/update",$body,'Parametre du formulaire modifié avec succès');

                if($sendApi < 300){

                    $this->addFlash('info',$this->session->get('response'));
                    $this->session->set('flash', 'info');
                    $this->permission->writeLog('Modifier un parametres identifiant :'.$id);
                }
                else{

                    $this->session->set('flash', 'error');
                    $this->addFlash('error','Erreur  de modification  Paramettre du formulaire');
                    $this->permission->writeLog('Erreur  de modification Paramettre du formulaire'.$id);
                }

                return $this->redirectToRoute('parametreformulaire');
                
            } catch (\Exception $e) {
                $this->session->set('flash', 'error');
                $this->addFlash('error',$this->session->get('response'));
                $this->permission->writeLog($e->getMessage());

                return $this->redirectToRoute('editparametreformulaire',array('id'=>$id));
            }

        }else {
            $data= [
                "typechamp" => $this->typechamp(),
                "dataSelect" => $this->dataSelect($id),
                "tab" => 'modifier',
                "menu" => $this->menu(),
            ]; 
            return $this->render("formulaire/parametres.html.twig",$data);  
           
        }
    }

    #[Route('/parametre_formulaire/supprimer/{id}', name: 'delparametreformulaire')]
    public function delparametreformulaire(Request $request,$id): Response
    {
        if ($request->getMethod()=='POST') {
            
            $this->permission->userAccess();

            try {
                $sendApi=$this->apiController->doDelete("questionnaire/{$id}/delete",'Parametre du formulaire suprimé avec succès');

                if($sendApi < 300 ){

                    $this->addFlash('info',$this->session->get('response'));
                    $this->session->set('flash', 'info');
                    $this->permission->writeLog('Supprimer un parametres identifiant :'.$id);
                }
                else{

                    $this->session->set('flash', 'error');
                    $this->addFlash('error','Erreur  de suppression   Paramettre du formulaire');
                    $this->permission->writeLog('Erreur  de suppression Paramettre du formulaire'.$id);
                }

                return $this->redirectToRoute('parametreformulaire');    

            } catch (\Exception $e) { 

                $this->addFlash('error',$this->session->get('response'));
                $this->session->set('flash', 'error');
                $this->permission->writeLog($e->getMessage());

                return $this->redirectToRoute('parametreformulaire');    
            }
        }
    }
}
