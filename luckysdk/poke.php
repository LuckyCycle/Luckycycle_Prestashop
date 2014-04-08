<a href="get.php">SHOW</a> | <a href="poke.php">POKE</a> | <a href="draw.php">DRAW</a>
<hr />

<?php

require_once './lucky.php';

$api_key = '5ddb2b0631c47caaf17868d89c01261e80159fd7';

if (isset($_GET['operation_id'])) {

	$op = $_GET['operation_id'];
	$uid = $_GET['user_id'];
	$item_id = $_GET['item_id'];
	$item_value = $_GET['item_value'];
	$item_currency = $_GET['item_currency'];
	$email = $_GET['email'];
	$firstname = $_GET['firstname'];
	$lastname = $_GET['lastname'];
	$random_data = $_GET['random_data'];

	if ($op) {

		$req = new LuckyCycleApi('http://localhost:3000');
		$req->setApiKey($api_key);
		$req->setOperationId($op);

		//$data = array();
		//$headers = array( 'X-LuckyApiKey'  =>  $api_key );
		//$path = '/api/v1/operations/' . $op . '/show';
		//$poke = $req->get( $path , $data, $headers );

		$pokedata = array(
		    'operation_id' => $op,
		    'user_uid' => $uid,
		    'item_uid' => $item_id,
		    'item_value' => $item_value,
		    'item_currency' => $item_currency,
		    'email' => $email,
		    'firstname' => $firstname,
		    'lastname' => $lastname,

		    //, 'random_data' => 1
		);
		if($random_data) {
			$pokedata['random_data'] = 1;
		}

		$poke = $req->poke($pokedata);

	}
} else {
	echo("Enter an operation id");
}

?>


Enter an operation id to make a poke: 

<form action="poke.php" method="">
	<input type="text" name="operation_id" placeholder="Operation id">
	<input type="text" name="user_id" placeholder="User id">
	<input type="text" name="item_id" placeholder="Item id">
	<input type="text" name="item_value" placeholder="Amount" value="100">
	<input type="text" name="item_currency" placeholder="Currency" value="EUR">
	<input type="text" name="email" placeholder="Email" value="email@email.be">
	<input type="text" name="firstname" placeholder="Firstname" value="firstname">
	<input type="text" name="lastname" placeholder="Lastname" value="lastname">
	<input type="checkbox" name="random_data" value="1">
	
	<input type="submit">
</form>



<pre>
<? print_r($poke) ?>
</pre>