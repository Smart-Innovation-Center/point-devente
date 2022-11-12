<?php

namespace App\Controller\Admin;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpClient\HttpClient;
use Symfony\Component\HttpFoundation\Session\Session;

class RequestController extends AbstractController
{
    private $url;
    public $client;
    public $header;
    private $session;
    // private $cookie;

    public function __construct()
    {
        $this->session = new Session();

        $this->client = HttpClient::create([
            'verify_peer'  => false,
            'verify_host'  => false
        ]);

        $this->url = $_ENV['urlApi'];

    }


    private function header(): array
    {

        $header=[
            'Content-Type'=>'application/json',
            'ApiToken'=>" ".$this->session->get('ApiToken')
        ];

        return $header;
    }



    #[Route('/doconnect', methods: 'post')]
    public function doConnect(string $path, array $body)
    {
        try {
            $response = $this->client->request(
                'POST',
                $this->url.$path,
                array(
                    'headers'=>[
                        'Content-Type'=>'application/json'
                    ],
                    'body'=> json_encode($body,true)
                )
            );

            if ($response->getStatusCode()<300) {
                $this->session->set('ApiToken', $response->getHeaders()['apitoken'][0]);

                $token=$response->getHeaders()['apitoken'][0];
                $tokenParts = explode(".", $token);
                $tokenHeader = base64_decode($tokenParts[0]);
                $tokenPayload = base64_decode($tokenParts[1]);
                $jwtHeader = json_decode($tokenHeader);
                $jwtPayload = json_decode($tokenPayload);
                $roles=$jwtPayload->roles;

                if (!empty($roles)) {
                    $tab="";

                    foreach ($roles as $key=>$value)
                    {
                        foreach ($value as $key1=>$value1)
                        {
                            $tab=$tab.$value1.";";
                        }
                    }

                    if(strpos($tab, 'SUPER ADMIN') !== false){
                        $this->session->set('superAdmin','SUPER ADMIN');
                        $this->session->set('monRole','SUPER ADMIN');
                    } else if(strpos($tab, 'SENIOR MANAGER') !== false){
                        $this->session->set('seniorManager','SENIOR MANAGER');
                        $this->session->set('monRole','SENIOR MANAGER');
                    } else if(strpos($tab, 'COORDINATION DRDI') !== false){
                        $this->session->set('drdi','COORDINATION DRDI');
                        $this->session->set('monRole','COORDINATION DRDI');
                    } else if(strpos($tab, 'ADOM') !== false){
                        $this->session->set('boom','BOOM');
                        $this->session->set('monRole','BOOM');
                    } else if(strpos($tab, 'BOOM') !== false){
                        $this->session->set('boom','BOOM');
                        $this->session->set('monRole','BOOM');
                    }else{
                        $this->session->set('manager','MANAGER');
                        $this->session->set('monRole','MANAGER');
                    }

                } else {
                    $this->session->set('manager','MANAGER');
                    $this->session->set('monRole','MANAGER');
                }

                $this->session->set('username', $jwtPayload->sub);
                $_SESSION['user_username'] =  $jwtPayload->sub;

                return $response->getStatusCode();

            }else {
                return $response->toArray()['message'];
            }

        } catch (\Exception $e) {
            return $e->getMessage();
        }
    }

    #[Route('/doimport', methods: 'post')]
    public function doImport(string $path, array $body)
    {
        $curl = curl_init();
        curl_setopt_array($curl, array(
            CURLOPT_URL => $this->url.$path,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_SSL_VERIFYHOST => false,
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_HEADER=>true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_POSTFIELDS => $body ,
            CURLOPT_HTTPHEADER => array(
                "ApiToken:{$this->session->get('ApiToken')}"
            ),
        ));


        $response = curl_exec($curl);

        if (curl_errno($curl)) {
            return curl_error($curl);
        }

        $http_code = curl_getinfo($curl, CURLINFO_HTTP_CODE);

        if($http_code == intval(200)){
            return   $http_code;
        }
        else{
            return   $http_code;
        }
    }


    public function displayMessage($statusCode,$succesMessage,$errorMessage ='Erreur de message'){

        if($statusCode<300){
            return $succesMessage;
        }else{
            return $errorMessage;
        }
    }

    #[Route('/dopost', methods: 'post')]
    public function doPost(string $path, array $body,$statusMessage)
    {
        $response = $this->client->request(
            'POST',
            $this->url.$path,
            array(
                'headers'=>$this->header(),
                'body'=> json_encode($body,true)
            )
        );

        if ($response->getStatusCode()<300) {
            $this->session->set('response', $this->displayMessage($response->getStatusCode(),$statusMessage));
            return $response->getStatusCode();
        }else {
            return $response;
        }

    }

    #[Route('/doget', methods: 'get')]
    public function doGet(string $path): array
    {
        $response = $this->client->request(
            'GET',
            $this->url.$path,
            array(
                'headers'=>$this->header()
            )
        );

        return $response->toArray();
    }

    #[Route('/dodelete/id', methods: 'delete')]
    public function doDelete(string $path ,$statusMessage): int
    {
        $response = $this->client->request(
            'DELETE',
            $this->url.$path,
            array(
                'headers'=>$this->header()
            )
        );
        $this->session->set('response', $this->displayMessage($response->getStatusCode(),$statusMessage));
        return $response->getStatusCode();
    }

    #[Route('/doput/id', methods: 'put')]
    public function doPut(string $path, array $body,$statusMessage): int
    {
        $response = $this->client->request(
            'PUT',
            $this->url.$path,
            array(
                'headers'=>$this->header(),
                'body'=> json_encode($body,true)
            )
        );

        $this->session->set('response', $this->displayMessage($response->getStatusCode(),$statusMessage));
        return $response->getStatusCode();
    }

    #[Route('/doactivet/id', methods: 'put')]
    public function doActivet(string $path,$statusMessage): int
    {
        $response = $this->client->request(
            'PUT',
            $this->url.$path,
            array(
                'headers'=>$this->header()
            )
        );
        $this->session->set('response', $this->displayMessage($response->getStatusCode(),$statusMessage));
        return $response->getStatusCode();
    }

}
