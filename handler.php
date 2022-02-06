<?php 

// Connect functions 
include_once __DIR__ . "/bot.php"; // Bot func
include_once __DIR__ . "/prices.php"; // Price func

class Handler {

    private $BOT;
    private $PRICE;
    public $CONFIG;

    function __construct($tocken) {

        // Create class bot
        $this->BOT = new Bot($tocken);

        // Create class price
        $this->PRICE = new Prices();

        // Set webhoock
        $this->BOT->setWebhoock();
        
        // Config
        $this->CONFIG = json_decode( file_get_contents("responseConfig.json") );
        
    }

    // Finds the right handler
    public function responseToChanges($data)
    {

        if ( isset( $data['callback_query'] ) ) {
			$this->responseCallback($data['callback_query']);
        } else {
        	$this->responseMessages($data['message']);
        }

    }
    
    // Processing inline buttons
	private function responseCallback($data)
    {
    	$config = $this->CONFIG->tockenInterface;
    	
    	// chat info
        $chat_id = $data['message']['chat']['id'];
        $message_id = $data['message']['message_id'];
    	
    	// btn data
        $callback_btn = json_decode( $data['data'] );
        $tocken_identyfi = $callback_btn->identyfi;
        $tocken_preview = $callback_btn->preview;
    
    	// Proccesing comeback
    	if ($tocken_identyfi == "comeback") {
        	return $this->responseMessages(["text" => "/start", "message_id" => $message_id, "chat" => ["id" => $chat_id], "update" => 1]);
        }

		// Proccesing update btn
        if ((string)$tocken_preview === (string)$config->buttons[1]->preview) {
        	
        	$var = "/start";
    		$tocken;
        	foreach ( $this->CONFIG->command->$var->buttons as $btn ) {
        		
        		if ($btn->identyfi == $tocken_identyfi) {
        			$tocken = $btn;
        			break;
        		}
        
        	}
        	
        	$interface = $this->createCryptoMessage($config, ["identyfi" => $tocken->identyfi, "preview" => $tocken->preview]);
            return $this->BOT->updateMessage($chat_id, $message_id, $interface['text'], $interface['keyboard']);
        }
        
        // Simple variant
        $interface = $this->createCryptoMessage($config, ["identyfi" => $tocken_identyfi, "preview" => $tocken_preview]);
		return $this->BOT->updateMessage($chat_id, $message_id, $interface['text'], $interface['keyboard']);
    }
    

    // Processing command
    private function responseMessages($data)
    {
        // config with commads
        $config = $this->CONFIG->command;

        // data about user
        $user_msg = $data['text'];
        $user_id = $data['chat']['id'];
        
        // Command found
        if ( property_exists( $config, $user_msg ) ) {

            $command = $config->$user_msg;

            if ( empty( (array)$command) ) {
                $error_msg = "It command dosen`t work, Sorry";
                return $this->BOT->sendMessage($user_id, $error_msg);
            }
            
            // Text for message from config
            $configText = $command->text;
            
            // Create keyboard for the messasge
			$keyboard = $this->configurateKeyboard($command->buttons);
			
			if(isset($data['update'])) {
				return $this->BOT->updateMessage($user_id, $data['message_id'], $configText, $keyboard);
			}
			
            // Send Message
      		return $this->BOT->sendMessage($user_id, $configText, $keyboard);

        }

        // send error message
        $error_message = "Command not found☹️";
        return $this->BOT->sendMessage($user_id, $error_message);
 
    }


    // Configurate keyboard from config file
    private function configurateKeyboard($btns)
    {

        // keyboard pattern
        $keyboard = array(
            "type" => "inline_keyboard",
            "buttons" => [

            ]
        );

        // Create btns
        foreach ($btns as $btn) {
            $newBtn = array();
   
    		$newBtn['text'] = $btn -> preview;
            
    		// Configurate callback_data
			$data = (array)$btn;

            // Set callback_data
            $newBtn['callback_data'] = json_encode($data);

            // Add btn in array with btns
            array_push($keyboard['buttons'], $newBtn);
    
        }

        return $keyboard;
    }
    
    // Create keyboard and text for cryptocurency
    private function createCryptoMessage($config, $tocken) // $token is array
    {
    	// Set current tocken (use for upadate)
    	$this->current_tocken = ["identyfi" => $tocken['identyfi'], "preview" => $tocken['preview'] ];
    	
    	// Get price (for message)
		$prices = $this->getPriceTocken($tocken['identyfi']);
        	
		// Message	
		$date = date('Y-m-d H:i:s');
		$text = "👾 " . $tocken['preview'] . "\n\nUSD: " . $prices['usd'] . "$" . "\nRUB: " . $prices['rub'] . "₽\n\nUpdate: $date";
		
		// Keyboard for message
        $config->buttons[1]->identyfi = $tocken['identyfi'];
		$keyboard = $this->configurateKeyboard($config->buttons);
		
		return ["keyboard" => $keyboard, "text" => $text];
    }
    
    // Get price cryptocurency
    private function getPriceTocken($identyfi) {
        $prices = $this->PRICE->getPrice($identyfi)[$identyfi];
        return ["usd" => $prices['usd'], "rub" => $prices['rub']];
    }

}