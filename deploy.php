<?php
/**
 * Git Auto Deploy
 * Automatically Deploy your Github Projects
 * 
 * @author  micahblu <github.com/micahblu>
 * @version  0.1
 * 
 */


define('SECRET', '');


if(!$_REQUEST['payload']) {
	die('Bad request: No payload');
}

$repoMap = array(
	"[reponame]" => "[server_repo_path]"
);

// If as secret is set, compare hashes
if( defined( SECRET ) ){
	$body = file_get_contents('php://input');

	$localSignature = hash_hmac('sha1', $body, $secret);

	$remoteSignature = str_replace("sha1=", "", $_SERVER['HTTP_X_HUB_SIGNATURE']);

	if( $remoteSignature !== $localSignature ){
		die('Bad request');
	}
}

$payload = json_decode($_REQUEST['payload']);

chdir( $repoMap[$payload->repository->name] );

exec( 'whoami; git pull 2>&1', $output );

$message = '';
foreach($output as $line){
	$message .=  $line . "\n";
}

mail("micahblu@gmail.com", $payload->repository->name . " deployed", $message);
print_r($output);
