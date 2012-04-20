<?php
/*
 * HtmlCache Plugin
 * Copyright (c) 2009 Matt Curry
 * http://pseudocoder.com
 * http://github.com/mcurry/html_cache
 *
 * @author        mattc <matt@pseudocoder.com>
 * @license       MIT
 *
 */
class HtmlCacheBaseHelper extends Helper {

/**
 * options property
 *
 * @var array
 * @access public
 */
	public $options = array(
		'test_mode' => false,
		'host' => null,
		'domain' => false,
		'www_root' => null
	);

/**
 * helpers property
 *
 * @var array
 * @access public
 */
	public $helpers = array('Session');

/**
 * path property
 *
 * @var string ''
 * @access public
 */
	public $path = '';

/**
 * isFlash property
 *
 * @var bool false
 * @access public
 */
	public $isFlash = false;

/**
 * beforeRender method
 *
 * @return void
 * @access public
 */
	public function beforeRender() {
		if($this->Session->read('Message')) {
			$this->isFlash = true;
		}
	}

/**
 * afterLayout method
 *
 * @return void
 * @access public
 */
	public function afterLayout() {
		if(!$this->_isCachable()) {
			return;
		}

		if (empty($this->options['www_root'])) {
			$this->options['www_root'] = WWW_ROOT . 'GET'	. DS;
		}

		$view =& ClassRegistry::getObject('view');

		//handle 404s
		if ($view->name == 'CakeError') {
			$path = $this->params['url']['url'];
		} else {
			$path = $this->here;
		}

		$path = implode(DS, array_filter(explode('/', $path)));
		if($path !== '') {
			$path = DS . ltrim($path, DS);
		}

		$host = '';
		if($this->options['domain']) {
			if (!empty($_SERVER['HTTP_HOST'])) {
				$host = DS . $_SERVER['HTTP_HOST'];
			} elseif ($this->options['host']) {
				$host = DS . $this->options['host'];
			}
		}	

		$path = $this->options['www_root'] . $host . $path;
		if ((empty($view->params['url']['ext']) || $view->params['url']['ext'] === 'html') && !preg_match('@.html?$@', $path)) {
			$path .= DS . 'index.html';
		}
		$dir = dirname($path);
		if (!is_dir($path)) {
			mkdir($dir, 0777, true);
		}
		file_put_contents($path, $view->output, LOCK_EX);
	}

/**
 * isCachable method
 *
 * @return void
 * @access protected
 */
	protected function _isCachable() {
		if (!$this->options['test_mode'] && Configure::read('debug') > 0) {
			return false;
		}

		if ($this->isFlash) {
			return false;
		}

		if ($_SERVER['REQUEST_METHOD'] != 'GET') {
			return false;
		}

		return true;
	}
}