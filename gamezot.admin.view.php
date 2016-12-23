<?php

/**
 * @class  gamezotAdminView
 * @author Huhani (mmia268@gmail.com)
 * @brief  Gamezot module admin view class.
 **/

class gamezotAdminView extends gamezot
{
	function init(){
		$this->setTemplatePath($this->module_path . 'tpl/');
	}
}

/* End of file gamezot.admin.view.php */
/* Location: ./modules/gamezot/gamezot.admin.view.php */
