<?php
/**
 * @author  Dmitriy Lukin <lukin.d87@gmail.com>
 */

namespace XrTools;

/**
 * Custom template parts builder
 */
class Template {

	private $parts = [];

	private $config = [];

	private $locale;

	public function __construct(Locale $locale){
		$this->locale = $locale;
	}

	public function set($part, $value){
		if(is_array($part)){
			$this->setMulti($part);
		}
		else {
			$this->parts[$part] = $value;
		}
	}

	public function push($part, $value){
		$this->parts[$part][] = $value;
	}

	public function setMulti(array $parts){
		foreach ($parts as $key => $value) {
			$this->parts[$key] = $value;
		}
	}
	
	public function get($parts = null){
		if(!isset($parts)){
			return $this->parts;
		}
		elseif(is_array($parts)){
			$result = [];

			foreach ($parts as $key) {
				$result[$key] = $this->parts[$key] ?? null;
			}

			return $result;
		}
		else {
			return $this->parts[$parts] ?? null;
		}
	}

	public function config($key, $value = null){
		// write config
		if(isset($value)){
			$this->config[$key] = $value;
			return;
		}
		// read config
		else {
			return $this->config[$key] ?? null;
		}
	}

	/**
	 * Proxy to Locale Service
	 * @return \XrTools\Locale
	 */
	public function locale(){
		return $this->locale;
	}

	////////////
	// :TODO:REFACTOR: //
	////////////




	/**
	 *	:TODO:REFACTOR: Переделать (пока что это просто перенесено из general.php)
	 * 
	 * Формирует канонический адрес в $GLOBALS['head']['rel_canonical'] для мета тега rel=canonical в шаблонах вывода страниц
	 * @param  array  $ruleset Set of rules to generate a canonical URL. Processed in this order:
	 *                         	<ul>
	 *                         		<li> <strong> url_go_max </strong> int
	 *                         			- last index of an array $GLOBALS['arr_url_go'] which canonical URL is generated from (indexes greater than url_go_max are ignored)
	 *                         		<li> <strong> query_count_max </strong> int
	 *                         			- ignore the whole query string in canonical URL if there are more _GET parameters than this number
	 *                         	</ul>
	 * @param  array  $sys     Settings:
	 *                         	<ul>
	 *                         		<li> <strong> return_only </strong> bool (off)
	 *                         			- do not save result to $GLOBALS['head']['rel_canonical'] | 
	 *                         		<li> <strong> rewrite </strong> bool (off)
	 *                         			- by default result is saved only if $GLOBALS['head']['rel_canonical'] is empty.
	 *                         			  This option forces to overwrite the existing canonical URL settings (i.e. for subpage to override it's inherited settings from parent)
	 *                         	</ul>
	 * @return string          Generated canonical URL
	 */
	function rel_canonical_rules($ruleset = array(), $sys=array()){
		// результат
		$rel_canonical = false;
		
		// проверяем максимальный уровень arr_url_go
		if(isset($ruleset['url_go_max'])){
			$url_go_max = (int) $ruleset['url_go_max'];
			
			if(isset($GLOBALS['arr_url_go'][$url_go_max])){
				$rel_canonical = '';
				for($i=0; $i<=$url_go_max; $i++){
					$rel_canonical .= '/'.$GLOBALS['arr_url_go'][$i];
				}
			}
		}
		
		// проверяем максимальное кол-во аргументов в запросе (если уже не настроено)
		if($rel_canonical === false && isset($ruleset['query_count_max'])){
			$query_count_max = (int) $ruleset['query_count_max'];
			
			// учитываем $_GET['go']
			if(count($_GET)-1 > $query_count_max){
				$rel_canonical = '/'.$_GET['go'];
			}
		}
		
		// если нужно только вернуть результат и не нужна авто-настройка параметра в шаблоне
		// авто-настройка записывается только если атрибут пуст или присутствует настройка 'rewrite'=>true
		if(empty($sys['return_only']) && (empty($GLOBALS['head']['rel_canonical']) || !empty($sys['rewrite']))){
			$GLOBALS['head']['rel_canonical'] = $rel_canonical;
		}
		
		return $rel_canonical;
	}






}
