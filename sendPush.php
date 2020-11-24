<?php

//replace with your token browser
$token = "fake_token_1234";
$payload = createPayload("Test Message", "Hi, how are you?", ["push"]);
$apns = connect();
sendPush($apns, $token, $payload);

function createPayload($title, $body, $urlArgs) {        
    $payload = array();      
    $payload['aps'] = array(
        'alert' => array(
            "title"=> $title,
            "body"=>$body
        ),
        "url-args" => $urlArgs,
        'badge' => 0
        // you can add custom values here
    );
    return $payload;
}

function connect() {       
      $push_apple = 'ssl://gateway.push.apple.com:2195';
      // Replace this with the path for your website Certificate pem
      $certificate = "/config/project/certificates/fake.pem";
      // Replace this with the path for your website entrust cert
      $entrust = "/config/project/certificates/fake_entrust_2048_ca.cer";
      //Replace with your pem passphrase
      $passphrase = "fake_passphrase";
      $ctx = stream_context_create();
      stream_context_set_option($ctx, 'ssl', 'local_cert', $certificate);
      stream_context_set_option($ctx, 'ssl', 'passphrase', $passphrase);
      stream_context_set_option($ctx, 'ssl', 'cafile', $entrust);
      // Open a connection to the APNS server
      try{
          $apns = stream_socket_client($push_apple, $err, $errstr, 60, STREAM_CLIENT_CONNECT | STREAM_CLIENT_PERSISTENT, $ctx);
      }catch(Exception $e){
          $apns = FALSE;
          echo "Error generating apns for safari push: ".$e->getMessage();
      }
      return $apns;
}

   

function sendPush($apns, $token, $payload){
        $sended = false;    
        if ($apns) {
            // Encode the payload as JSON
            $payload = json_encode($payload);
            if (!ctype_xdigit($token)) {
                echo "The token: ($token) must be hexadecimal");
            }else {
                // Build the binary notification
                $msg = chr( 0 ).pack( 'n', 32 ).pack( 'H*', $token ).pack( 'n', strlen( $payload ) ).$payload;
                // Send it to the server
                $sended = fwrite( $apns, $msg, strlen( $msg ) );
                fflush($apns);
            }    
            fclose($apns);
        }
        retrun $sended;
    }
?>
