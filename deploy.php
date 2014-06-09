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

function findBy($prop, $withValue, $assoc{
	for($i = 0, $j = count($assoc); $i < $j; $i++){
		foreach($assoc[$i] as $field => $value){
			if($prop == $field && $value == $withValue){
				return $assoc[$i];
			}
		}
	}
}
 
$repo = findBy("name", $repo_name, $config->repos);

// If as secret is set, compare hashes
if( !empty( $repo->secret )){
	$body = file_get_contents('php://input');

	$localSignature = hash_hmac('sha1', $body, $repo->secret);

	$remoteSignature = str_replace("sha1=", "", $_SERVER['HTTP_X_HUB_SIGNATURE']);

	if( $remoteSignature !== $localSignature ){
		die('Bad request');
	}
}

// cd into the repo path 
chdir($repo->path);

// Execute git commands
//exec( 'pwd; whoami; git pull 2>&1', $output );
exec('./build.sh $repo->name $repo->path 2>&1', $output);

// Record results for email
$message = '';
foreach($output as $line){
	$message .=  $line . "\n";
}

// Optionally send email
if($config->sendEmail === true){
	mail($config->email, $repo_name . " deployed", $message);
}
