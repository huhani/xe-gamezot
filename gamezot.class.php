<?php
/*! Copyright (C) 2016 BGM STORAGE. All rights reserved. */
/**
 * @class  gamezot
 * @author Huhani (mmia268@gmail.com)
 * @brief  Gamezot module high class.
 */

class gamezot extends ModuleObject
{


	private $triggers = array(
		array( 'member.deleteMember',			'gamezot',	'controller',	'triggerDeleteMember',					'after'	),
		//array( 'document.insertDocument',	'gamezot',	'controller',	'triggerBeforeInsertDocument',      'before'	),
		array( 'document.updateDocument',	'gamezot',	'controller',	'triggerBeforeUpdateDocument',      'before'	),
		array( 'document.deleteDocument',	'gamezot',	'controller',	'triggerBeforeDeleteDocument',      'before'	),
		//array( 'comment.insertComment',		'gamezot',	'controller',	'triggerBeforeInsertComment',       'before'	),
		array( 'comment.updateComment',		'gamezot',	'controller',	'triggerBeforeUpdateComment',       'before'	),
		array( 'comment.deleteComment',		'gamezot',	'controller',	'triggerBeforeDeleteComment',       'before'	),
		array( 'moduleObject.proc',			'gamezot',	'controller',	'triggerBeforeModuleProc',				'before'	),
	);


	function moduleInstall()
	{
		$oModuleModel = getModel('module');
		$oModuleController = getController('module');

		foreach ($this->triggers as $trigger) {
			$oModuleController->insertTrigger($trigger[0], $trigger[1], $trigger[2], $trigger[3], $trigger[4]);
		}

		return new Object();
	}




	function moduleUninstall()
	{
		$oModuleModel = getModel('module');
		$oModuleController = getController('module');

		foreach ($this->triggers as $trigger)
		{
			$oModuleController->deleteTrigger($trigger[0], $trigger[1], $trigger[2], $trigger[3], $trigger[4]);
		}

		return new Object();

	}




	function checkUpdate()
	{
		$oModuleModel = getModel('module');
		foreach ($this->triggers as $trigger)
		{
			if (!$oModuleModel->getTrigger($trigger[0], $trigger[1], $trigger[2], $trigger[3], $trigger[4]))
			{
				return true;
			}
		}

		return false;
	}

	function moduleUpdate()
	{

		$oModuleModel = getModel('module');
		$oModuleController = getController('module');
		foreach ($this->triggers as $trigger)
		{
			if (!$oModuleModel->getTrigger($trigger[0], $trigger[1], $trigger[2], $trigger[3], $trigger[4]))
			{
				$oModuleController->insertTrigger($trigger[0], $trigger[1], $trigger[2], $trigger[3], $trigger[4]);
			}
		}

		return new Object();
	}

}

/* End of file gamezot.class.php */
/* Location: ./modules/gamezot/gamezot.class.php */
