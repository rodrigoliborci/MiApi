<?php


ini_set('display_errors',1);
ini_set('display_startup_errors',1);
error_reporting(-1);

require 'vendor/autoload.php';
require 'Models/User.php';
require 'Models/sensor.php';


function simple_encrypt($text,$salt){  
   return trim(base64_encode(mcrypt_encrypt(MCRYPT_RIJNDAEL_256, $salt, $text, MCRYPT_MODE_ECB, mcrypt_create_iv(mcrypt_get_iv_size(MCRYPT_RIJNDAEL_256, MCRYPT_MODE_ECB), MCRYPT_RAND))));
}
 
function simple_decrypt($text,$salt){  
    return trim(mcrypt_decrypt(MCRYPT_RIJNDAEL_256, $salt, base64_decode($text), MCRYPT_MODE_ECB, mcrypt_create_iv(mcrypt_get_iv_size(MCRYPT_RIJNDAEL_256, MCRYPT_MODE_ECB), MCRYPT_RAND)));
}



$app = new \Slim\Slim();


$app->enc_key = '1234567891011214';


$app->config('databases', [
    'default' => [
        'driver'    => 'mysql',
        'host'      => 'sql4.freemysqlhosting.net',
        'database'  => 'sql497075',
        'username'  => 'sql497075',
        'password'  => 'qSzcljxNL5',
        'charset'   => 'utf8',
        'collation' => 'utf8_general_ci',
        'prefix'    => ''
    ]
]);

$app->add(new Zeuxisoo\Laravel\Database\Eloquent\ModelMiddleware);
$app->view(new \JsonApiView());
$app->add(new \JsonApiMiddleware());
$app->add(new \Slim\Middleware\ContentTypes());

$app->options('/(:name+)', function() use ($app) {
    $app->render(200,array('msg' => 'API-True'));
});

$app->get('/', function () use ($app) {
	$app->render(200,array('msg' => 'API-True'));
});
$app->get('/usuario', function () use ($app) {
	$db = $app->db->getConnection();
	$users = $db->table('users')->select('id', 'usuario')->get();
	$app->render(200,array('data' => $users));
});
$app->get('/sensor', function () use ($app) {
	$db = $app->db->getConnection();
	$sensores = $db->table('sensores')->select('id', 'nombre', 'humedad','updated_at')->get();
	$app->render(200,array('data' => $sensores));
});


$app->post('/sensor', function () use ($app) {
	$input = $app->request->getBody();
	$name = $input['name'];
	if(empty($name)){
		$app->render(500,array(
			'error' => TRUE,
            'msg'   => 'name is required',
        ));
	}
	$password = $input['password'];
	if(empty($password)){
		$app->render(500,array(
			'error' => TRUE,
            'msg'   => 'password is required',
        ));
	}
	$email = $input['email'];
	if(empty($email)){
		$app->render(500,array(
			'error' => TRUE,
            'msg'   => 'email is required',
        ));
	}
    $user = new sensores();
    $user->name = $name;
   $user->password = $password;
   $user->email = $email;
   $user->save();
    $app->render(200,array('data' => $user->toArray()));
});
$app->get('/sensor/:id', function ($id) use ($app) {
	$sensor = sensore::find($id);
	if(empty($sensor)){
		$app->render(404,array(
			'error' => TRUE,
            'msg'   => 'user not found',
        ));
	}
	$app->render(200,array('data' => $sensor->toArray()));
});
$app->post('/login', function () use ($app) {
	$input = $app->request->getBody();
	$email = $input['email'];
	if(empty($email)){
		$app->render(500,array(
			'error' => TRUE,
            'msg'   => 'email is required',
        ));
	}
	$password = $input['password'];
	if(empty($password)){
		$app->render(500,array(
			'error' => TRUE,
            'msg'   => 'password is required',
        ));
	}
	$db = $app->db->getConnection();
	$user = $db->table('users')->select()->where('email', $email)->first();
	if(empty($user)){
		$app->render(500,array(
			'error' => TRUE,
            'msg'   => 'user not exist',
        ));
	}
	if($user->pass != $password){
		$app->render(500,array(
			'error' => TRUE,
            'msg'   => 'password dont match',
        ));
	}
	$token = simple_encrypt($user->id, $app->enc_key);	
	$app->render(200,array('token' => $token));
});
$app->post('/usuario', function () use ($app) {
	$input = $app->request->getBody();
	$name = $input['name'];
	if(empty($name)){
		$app->render(500,array(
			'error' => TRUE,
            'msg'   => 'name is required',
        ));
	}
	$password = $input['password'];
	if(empty($password)){
		$app->render(500,array(
			'error' => TRUE,
            'msg'   => 'password is required',
        ));
	}
	$email = $input['email'];
	if(empty($email)){
		$app->render(500,array(
			'error' => TRUE,
            'msg'   => 'email is required',
        ));
	}
    $user = new User();
    $user->name = $name;
   $user->password = $password;
   $user->email = $email;
   $user->save();
    $app->render(200,array('data' => $user->toArray()));
});

$app->put('/sensor/:id', function ($id) use ($app) {
	$input = $app->request->getBody();
	
	$name = $input['name'];
	if(empty($name)){
		$app->render(500,array(
			'error' => TRUE,
            'msg'   => 'name is required',
        ));
	}
	$password = $input['password'];
	if(empty($password)){
		$app->render(500,array(
			'error' => TRUE,
            'msg'   => 'password is required',
        ));
	}
	$email = $input['email'];
	if(empty($email)){
		$app->render(500,array(
			'error' => TRUE,
            'msg'   => 'email is required',
        ));
	}
	$user = User::find($id);
	if(empty($user)){
		$app->render(404,array(
			'error' => TRUE,
            'msg'   => 'user not found',
        ));
	}
    $user->usuario = $name;
    $user->pass = $password;
    $user->email = $email;
    $user->save();
    $app->render(200,array('data' => $user->toArray()));
});

$app->get('/usuario/:id', function ($id) use ($app) {
	$user = User::find($id);
	if(empty($user)){
		$app->render(404,array(
			'error' => TRUE,
            'msg'   => 'user not found',
        ));
	}
	$app->render(200,array('data' => $user->toArray()));
});



$app->delete('/usuario/:id', function ($id) use ($app) {
	$user = User::find($id);
	if(empty($user)){
		$app->render(404,array(
			'error' => TRUE,
            'msg'   => 'user not found',
        ));
	}
	$user->delete();
	$app->render(200);
});
/*
//login
$app->post('/login', function () use ($app) {
	$input = $app->request->getBody();
	$email = $input['email'];
	if(empty($email)){
		$app->render(500,array(
			'error' => TRUE,
            'msg'   => 'email is required',
        ));
	}
	$password = $input['password'];
	if(empty($password)){
		$app->render(500,array(
			'error' => TRUE,
            'msg'   => 'password is required',
        ));
	}
	$db = $app->db->getConnection();
	$user = $db->table('users')->select()->where('email', $email)->first();
	if(empty($user)){
		$app->render(500,array(
			'error' => TRUE,
            'msg'   => 'user not exist',
        ));
	}
	if($user->password != $password){
		$app->render(500,array(
			'error' => TRUE,
            'msg'   => 'password dont match',
        ));
	}

	$token = simple_encrypt($user->id, $app->enc_key);	
	$app->render(200,array('token' => $token));
});

//logout
$app->get('/logout', function() use($app) {
 	$token="";
	$app->render(200,array('token' => ''));

});
*/
//logout
$app->get('/logout', function() use($app) {
 	$token="";
	$app->render(200,array('token' => ''));

});
//perfil
$app->get('/me', function () use ($app) {
	$token = $app->request->headers->get('auth-token');
	if(empty($token)){
		$app->render(500,array(
			'error' => TRUE,
            'msg'   => 'Not logged',
        ));
	}
	$id_user_token = simple_decrypt($token, $app->enc_key);
	$user = User::find($id_user_token);
	if(empty($user)){
		$app->render(500,array(
			'error' => TRUE,
            'msg'   => 'Not logged',
        ));
	}
	$app->render(200,array('data' => $user->toArray()));
});


$app->run();
?>
