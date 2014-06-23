<?php
/**
 * Git Auto Deploy
 * Automatically Deploy your Github Projects
 * 
 * @author  micahblu <github.com/micahblu>
 * @version  0.3
 * 
 */

if(!$_REQUEST['payload']) {
	die('Bad request: No payload');
}

$config = json_decode(file_get_contents("config.json"));

$payload = json_decode($_REQUEST['payload']);

$repo_name = $payload->repository->name;

// If as secret is set, compare hashes
if( !empty( $config->secret )){
	$body = file_get_contents('php://input');

	$localSignature = hash_hmac('sha1', $body, $config->secret);

	$remoteSignature = str_replace("sha1=", "", $_SERVER['HTTP_X_HUB_SIGNATURE']);

	if( $remoteSignature !== $localSignature ){
		die('Bad request');
	}
}

// Execute git commands
exec('./build.sh 2>&1', $output);

// Record results for email
$message = '';
foreach($output as $line){
	$message .=  $line . "\n";
}

// Optionally send email
if($config->sendEmail === true){
	mail($config->email, $repo_name . " deployed", $message);
}

// Let's dump the output for logs 
print_r($output);