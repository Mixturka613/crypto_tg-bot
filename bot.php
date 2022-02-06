<?php

// Library requests
require_once 'vendor/autoload.php';
use GuzzleHttp\Client;

class Bot 
{
    private $link;
    private $client;

    function __construct($tocken) {
        // basic request to the bot
        $this->link = "https://api.telegram.org/bot" . $tocken . "/";
        $this->client = new GuzzleHttp\Client(['base_uri' => $this->link, 'verify' => false ]);
    }

    public function setWebhoock() {

        $res = $this->client->request("GET", "getWebhookInfo")->getBody()->getContents();

        if (!empty(json_decode($res)->result->url)) {
            return false;
        }

        $server = $_SERVER["HTTP_HOST"];
        $onHTTPS = $_SERVER['HTTPS'] == "On" ? true : false;

        if (!$onHTTPS) {
            return false;
        }

        return $this->client->request("GET", "setWebhook?url=https://$server");
    }

    public function sendMessage($chatId, $message, $keyboard="" ) {
        $keyboard = $this->createKeyboard($keyboard);
        return $this->client->request("GET", "sendMessage?chat_id=$chatId&text=$message&reply_markup=$keyboard")->getBody()->getContents();
    }

    public function updateMessage($chatId, $messageId, $text, $keyboard="") {
        $keyboard = $this->createKeyboard($keyboard);
        return $this->client->request("GET", "editMessageText?chat_id=$chatId&message_id=$messageId&text=$text&reply_markup=$keyboard")->getBody()->getContents();
    }
    

    // Вот это нужно было бы зарефакторить
    private function createKeyboard($setings) {
    	
        if(empty($setings)) {
            return "";
        }
        

        $keyboard = array();
        $btns = $setings['buttons'];

        $a=0;
        for ($i=0; $i < count($btns); $i++) { 
    
            $keyboard[$a][] = $btns[$i];

            if (($i + 1) % 3 == 0) {
                $a++;
            }
                
        }

    	$str = array( $setings['type'] => $keyboard );
    	$keyboard = json_encode($str);

    	return $keyboard;
    }
}

