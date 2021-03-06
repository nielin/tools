<?php
App::uses('HttpSocketLib', 'Tools.Lib');

/**
 * Use Webservices to get current rates etc
 *
 * @author Mark Scherer
 * @license MIT
 * 2010-09-19 ms
 */
class CurrencyBitcoinLib {

	public $settings = array(
		'currency' => 'EUR', # set to NULL or empty for all
		'api' => 'bitmarket', # bitmarket or bitcoincharts
	);

	/**
	 * @see https://bitmarket.eu/api
	 * 2011-10-06 ms
	 */
	public function bitmarket($options = array()) {
		$options = array_merge($this->settings, $options);
		$url = 'https://bitmarket.eu/api/ticker';
		$res = $this->_getBitmarket($url);

		if (!$res) {
			return false;
		}
		if (empty($options['currency'])) {
			return $res['currencies'];
		}
		if (empty($res['currencies'][$options['currency']])) {
			return false;
		}
		return $res['currencies'][$options['currency']];
	}

	/**
	 * working
	 * @see http://bitcoincharts.com/about/markets-api/
	 * 2011-10-06 ms
	 */
	public function bitcoincharts($options = array()) {
		$options = array_merge($this->settings, $options);
		$url = 'http://bitcoincharts.com/t/markets.json';
		$res = $this->_getBitcoincharts($url);
		if (!$res) {
			return false;
		}
		$array = array();
		foreach ($res as $val) {
			$array[$val['currency']] = $val;
			unset($array[$val['currency']]['currency']);
		}

		if (empty($options['currency'])) {
			return $array;
		}
		if (empty($array[$options['currency']])) {
			return false;
		}
		return $array[$options['currency']];
	}

	/**
	 * @param array $options
	 * - currency
	 * - api
	 * 2011-10-07 ms
	 */
	public function rate($options = array()) {
		$options = array_merge($this->settings, $options);
		$res = $this->{$options['api']}($options);

		if ($res && isset($res['sell'])) {
			# bitmarket
			$current = $res['sell'];
		} elseif ($res && isset($res['ask'])) {
			# bitcoincharts
			$current = $res['ask'];
		}
		if (isset($current)) {
			return $this->calcRate($current);
		}
		return false;
	}

	/**
	 * calc BTC relative to 1 baseCurrency
	 * @param float $value
	 * @return float $relativeValue
	 * 2011-10-07 ms
	 */
	public function calcRate($current) {
		return 1.0 / (float)$current;
	}

	/**
	 * historic trade data
	 * @see http://bitcoincharts.com/about/markets-api/
	 * 2011-10-06 ms
	 */
	public function trades() {
		//TODO
	}

	protected function _getBitmarket($url) {
		if (!($res = $this->_get($url))) {
			return false;
		}
		if (!($res = json_decode($res, true))) {
			return false;
		}
		return $res;
	}

	protected function _getBitcoincharts($url) {
		if (!($res = $this->_get($url))) {
			return false;
		}
		if (!($res = json_decode($res, true))) {
			return false;
		}
		return $res;
	}


	protected function _get($url) {
		$http = new HttpSocketLib();
		if (!($res = $http->fetch($url))) {
			return false;
		}
		return $res;
	}

}
