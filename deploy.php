<?php
/**
 * Git Auto Deploy
 * Automatically Deploy your Github Projects
 * 
 * @author  micahblu <github.com/micahblu>
 * @version  0.1
 * 
 */


if(!$_REQUEST['payload']) {
	die('Bad request: No payload');
}

$config = json_decode(file_get_contents("config.json"));

$payload = json_decode($_REQUEST['payload']);

$repo_name = $payload->repository->name;

// If as secret is set, compare hashes
if( !empty( $config->map->{$repo_name}->secret )){
	$body = file_get_contents('php://input');

	$localSignature = hash_hmac('sha1', $body, $secret);

	$remoteSignature = str_replace("sha1=", "", $_SERVER['HTTP_X_HUB_SIGNATURE']);

	if( $remoteSignature !== $localSignature ){
		die('Bad request');
	}
}

chdir( $repoMap[$payload->repository->name] );

exec( 'whoami; git pull 2>&1', $output );

$message = '';
foreach($output as $line){
	$message .=  $line . "\n";
}

mail($config->email, $payload->repository->name . " deployed", $message);
print_r($output);
