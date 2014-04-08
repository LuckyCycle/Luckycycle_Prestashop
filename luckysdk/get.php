<a href="get.php">SHOW</a> | <a href="poke.php">POKE</a> | <a href="draw.php">DRAW</a>
<hr />

<?php

require_once './lucky.php';

$api_key = '5ddb2b0631c47caaf17868d89c01261e80159fd7';

if (isset($_GET['operation_id'])) {

	$op = $_GET['operation_id'];

	if ($op) {

		$req = new LuckyCycleApi('http://localhost:3000');
		$req->setApiKey($api_key);
		$req->setOperationId($op);

		//$data = array();
		//$headers = array( 'X-LuckyApiKey'  =>  $api_key );
		//$path = '/api/v1/operations/' . $op . '/show';
		//$poke = $req->get( $path , $data, $headers );

		$poke = $req->show();

	}
} else {
	echo("Enter an operation id");
}

?>


Enter an operation id:

<form action="get.php">
	<input type="text" name="operation_id" placeholder="Operation id">
	<input type="submit">
</form>



<pre>
<? print_r($poke) ?>
</pre>
