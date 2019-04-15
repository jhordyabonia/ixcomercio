<?php
namespace Cdi\Custom\Helper;
 
use Magento\Framework\App\Helper\AbstractHelper;

class Data extends AbstractHelper{
 
    public function getAttributeArrayFromJson($json){
		$fields = array();
        $atss = json_decode($json);
		$data = array();
		foreach($atss as $val){
			//inicia un nuevo array
			if($val->name == 'title'){
				if(!empty($data)) $fields[] = $data;
				$data = array('type' => 'data');
			}
			$data[$val->name] = $val->value;
		}
		$fields[] = $data;
		return $fields;
    }
 
}