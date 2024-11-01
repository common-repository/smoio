<?php

class smoio_publisher {

	protected $secret;
	protected $hub_url;
	protected $last_response;
	
	public function __construct($secret='', $hub_url='http://org.smo.io') {
		if ($secret=='') {
			throw new Exception('Please specify the secret string');
		}
		$this->secret = $secret;

		if (!isset($hub_url)) {
			throw new Exception('Please specify a hub url');
		}
		$this->hub_url = $hub_url;
	}

	public function publish($content) {
		$p['hub.mode'] = 'publish';
		$p['hub.content'] = $content;
		$p['hub.signature'] = hash_hmac('sha1', $content, sha1($this->secret));
		
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $this->hub_url);
		curl_setopt($ch, CURLOPT_POST, true);
		curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($p));
		curl_setopt($ch, CURLOPT_USERAGENT, 'smoio publisher 0.9.1');
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		$response = curl_exec($ch);
		$this->last_response = $response;
		
		$info = curl_getinfo($ch);
		curl_close($ch);
		
		if ($info['http_code'] == 200) {
			return true;
		}
		return false;
	}
}

?>