<?php
namespace Pasarela\Bancomer\Block;
class Success extends \Magento\Framework\View\Element\Template
{
	public function __construct(\Magento\Framework\View\Element\Template\Context $context)
	{
		parent::__construct($context);
	}

	public function imprimir(){
		echo 'algo';
	}
}