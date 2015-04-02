<?php


class Page {
	public $url = '';
	public $currentPage = 0;
	public $currentTag = '';
	public $currentTagClass = '';
	public $listNum = array();
	public $listTag = '';
	public $listTagClass = '';
	public $firstTag = '';
	public $firstTagClass = '';	
	public $preTag = '';
	public $preTagClass = '';
	public $nextTag = '';
	public $nextTagClass = '';
	public $lastTag = '';
	public $lastTagClass = '';
	public $isDisplayTotal = FALSE;
	public $totalTag = '';
	public $totalTagClass = '';	
	public $allNum = 0;	
	public $footIndexNum = 0;
	public $preNum = 0;
	public $isAjax = FALSE;
	public $ajaxFun = 'ajaxgets';
	public $ajaxDivId = '';  //操作DIV或是锚点
	function init($config = array('')) {
		//加载配置文件
		if(file_exists(APP_CONFIG_PATH . 'page.php') && !load_config('FootIndexNum'))
			load_config(include APP_CONFIG_PATH . 'page.php');
		$classVarArr = get_class_vars(__CLASS__);
		if(is_array($config)) {
			foreach($config as $k => $v) {
				if(in_array($k, array_keys($classVarArr))) {
					$this->$k = $v;
				}
			}
		}
		
		//处理参数
		$params = array('current', 'list', 'pre', 'next', 'first', 'last', 'total');
		foreach($params as $v) {
			$start 	= $v . 'Tag';
			$class	= $v . 'TagClass';

			if(empty($this->$start)) $this->$start = load_config($start);
			if(empty($this->$class)) $this->$class = load_config($class);
		}
		//默认列表当前页
		if(empty($this->currentPage)) {
			$this->currentPage = load_config('currentPage');
		}
		//默认列表每页显示条数
		if(empty($this->preNum)) {
			$this->preNum = load_config('PreNum');
		}
		//默认页脚显示翻页条数
		if(empty($this->footIndexNum)) {
			$this->footIndexNum = load_config('footIndexNum');
		}
		return $this->multi();
	}
	
	private function multi() {
		$page = $this->footIndexNum;
		$this->url .= strpos($this->url, '?') ? '&' : '?';
		$multiPage = '';
		if($this->allNum > $this->preNum) {
			$offset = intval($this->footIndexNum / 2);  //位移步长
			$pages = @ceil($this->allNum / $this->preNum);
			if($page > $pages) {
				$from = 1;
				$to = $pages;
			} else {
				$from = $this->currentPage - $offset;
				$to = $from + $page - 1;
				if($from < 1) {
					$to = $this->currentPage + 1 - $from;
					$from = 1;
					if($to - $from < $page) {
						$to = $page;
					}
				} elseif($to > $pages) {
					$from = $pages - $page + 1;
					$to = $pages;
				}
			}
			$multiPage = '';
			$urlplus = $this->ajaxDivId?"#{$this->ajaxDivId}":'';
			if($this->currentPage - $offset > 1 && $pages > $page) {
				$multiPage .= $this->handle_url('first', '1');
			}
			if($this->currentPage > 1) {
				$multiPage .= $this->handle_url('prev', '&lsaquo;&lsaquo;');
			}
			for($i = $from; $i <= $to; $i++) {
				if($i == $this->currentPage) {  
					
	                $multiPage .= "<{$this->currentTag} class=\"{$this->currentTagClass}\">$i</{$this->currentTag}>";      //modify by hudie 2010-7-10
				} else {
					$multiPage .= $this->handle_url('list', $i);
				}
			}
			if($this->currentPage < $pages) {
				$multiPage .= $this->handle_url('next', '&rsaquo;&rsaquo;');
			}
			if($to < $pages) {
				$multiPage .= $this->handle_url('last', $pages);
			}
			if($multiPage && $this->isDisplayTotal) {
				$totalWord = langpreg('common', 'pageTotal', array($pages));
	            $multiPage .= $this->handle_url('total',$totalWord);
	            
			}
		}
		return $multiPage;
	}
	private function handle_url($type, $display_str = '') {
		$newUrl = '';
		$urlplus = $this->ajaxDivId?"#{$this->ajaxDivId}":'';
		switch($type) {
			case 'first':
				$newUrl .= "<{$this->firstTag} ";
				if($this->isAjax) {
					$newUrl .= "href=\"javascript:;\" onclick=\"{$this->ajaxFun}('{$this->url}page=$display_str&ajaxdiv={$this->ajaxDivId}', '{$this->ajaxDivId}')\"";
				} else {
					$newUrl .= "href=\"{$this->url}page=1{$urlplus}\"";
				}
				$newUrl .= " class=\"{$this->firstTagClass}\">$display_str...</{$this->firstTag}>";
				break;
			case 'prev':
				$newUrl .= "<{$this->preTag} ";
				if($this->isAjax) {
					$newUrl .= "href=\"javascript:;\" onclick=\"{$this->ajaxFun}('{$this->url}page=".($this->currentPage-1)."&ajaxdiv={$this->ajaxDivId}', '{$this->ajaxDivId}')\"";
				} else {
					$newUrl .= "href=\"{$this->url}page=".($this->currentPage-1)."$urlplus\"";
				}
				$newUrl .= " class=\"{$this->preTagClass}\">$display_str</{$this->preTag}>";
				break;
			case 'list':
				$newUrl .= "<{$this->listTag} ";
				if($this->isAjax) {
					$newUrl .= "href=\"javascript:;\" onclick=\"{$this->ajaxFun}('{$this->url}page=$display_str&ajaxdiv={$this->ajaxDivId}', '{$this->ajaxDivId}')\"";
				} else {
					$newUrl .= "href=\"{$this->url}page=$display_str{$urlplus}\"";
				}
				$newUrl .= " class=\"{$this->listTagClass}\">$display_str</{$this->listTag}>";
				break;
			case 'next':
				$newUrl .= "<{$this->nextTag} ";
				if($this->isAjax) {
					$newUrl .= "href=\"javascript:;\" onclick=\"{$this->ajaxFun}('{$this->url}page=".($this->currentPage+1)."&ajaxdiv={$this->ajaxDivId}', '{$this->ajaxDivId}')\"";
				} else {
					$newUrl .= "href=\"{$this->url}page=".($this->currentPage+1)."{$urlplus}\"";
				}
				$newUrl .= " class=\"{$this->nextTagClass}\">$display_str</{$this->nextTag}>";
				break;
			case 'last':
				$newUrl .= "<{$this->lastTag} ";
				if($this->isAjax) {
					$newUrl .= "href=\"javascript:;\" onclick=\"{$this->ajaxFun}('{$this->url}page=$display_str&ajaxdiv={$this->ajaxDivId}', '{$this->ajaxDivId}')\"";
				} else {
					$newUrl .= "href=\"{$this->url}page=$display_str{$urlplus}\"";
				}
				$newUrl .= " class=\"{$this->lastTagClass}\">...$display_str</{$this->lastTag}>";
				break;
			case 'total':
				$newUrl .= "<{$this->totalTag} class=\"{$this->totalTagClass}\" href=\"javascript:void(0)\">{$display_str}</{$this->totalTag}>";
				break;
			default:
				break;
		}
		return $newUrl;
	}
}
