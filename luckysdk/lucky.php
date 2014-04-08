<?php

require_once 'PestJSON.php';

class LuckyCycleApi extends PestJSON
{
	const DEV_URL = 'http://localhost:3000';
	const PROD_URL = 'http://www.luckycycle.com/api/v1';

	public function __construct($base_url)
	{
		parent::__construct(self::DEV_URL);
		//$this->mode = 
	}

	/**
	 * @var string api key
	 */
	public $api_key;

	/**
	 * @var string operation id
	 */
	public $operation_id;

	/**
	 * @var boolean mode
	 */
	public $mode;

	public function setApiKey($api_key) {
		$this->api_key = $api_key;
	}

	public function setOperationId($operation_id) {
		$this->operation_id = $operation_id;	
	}

	public function show() {
		$path = '/api/v1/operations/' . $this->operation_id . '/show';
		$data = array();
		$headers = array( 'X-LuckyApiKey'  =>  $this->api_key );
		
		return $this->get($path, $data, $headers);
	}

	public function poke($data) {
		$path = '/api/v1/operations/' . $this->operation_id . '/poke';
		$headers = array( 'X-LuckyApiKey'  =>  $this->api_key );
		
		return $this->post($path, $data, $headers);
	}

	public function draw($data) {
		$path = '/api/v1/operations/' . $this->operation_id . '/draw';
		$headers = array( 'X-LuckyApiKey'  =>  $this->api_key );
		
		return $this->post($path, $data, $headers);
	}

}

?>