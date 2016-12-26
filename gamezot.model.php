<?php
/*! Copyright (C) 2016 BGM STORAGE. All rights reserved. */
/**
 * @class gamezotdModel
 * @author Huhani (mmia268@gmail.com)
 * @brief Gamezot module model class.
 */

class gamezotModel extends gamezot
{
	function init(){
	}

	function getConfig(){
		static $config = null;
		if(is_null($config))	{
			$oModuleModel = getModel('module');
			$config = $oModuleModel->getModuleConfig('gamezot');
			if(!$config){
				$config = new stdClass;
			}

			unset($config->body);
			unset($config->_filter);
			unset($config->error_return_url);
			unset($config->act);
			unset($config->module);
		}

		return $config;
	}

	function getGamezotCommentGrant(){
		$mid = Context::get('mid');
		$comment_srl = Context::get('comment_srl');
		$logged_info = Context::get('logged_info');
		
		if(!$comment_srl){
			return new Object(-1, "msg_invalid_request");
		}
		
		$is_manager = $logged_info ? $this->checkIsBoardAdmin($mid) : false;
		if($comment_srl){
			$oCommentModel = getModel('comment');
			$oComment = $oCommentModel->getComment($comment_srl, $is_manager);
		}

		if(!$oComment->isExists())	{
			return new Object(-1, "msg_invalid_request");
		}

		$this->add("grant", !$oComment->isGranted() ? 0 : 1);
	}


	function checkIsBoardAdmin($mid = false){
		$mid = $mid ? $mid : Context::get('mid');
		if(!$mid){
			return false;
		}

		$logged_info = Context::get('logged_info');
		if(!$logged_info){
			return false;
		}

		$oModuleModel = getModel('module');
		$module_info = $oModuleModel->getModuleInfoByMid($mid);
		$admin_member = $oModuleModel->getAdminId($module_info->module_srl);
		$is_module_admin = false;

		if($logged_info->is_admin == 'Y'){
			$is_module_admin = true;
		} else {

			if(!empty($admin_member)){
				foreach($admin_member as $value){
					if($value->member_srl === $logged_info->member_srl){
						$is_module_admin = true;
						break;
					}
				}
			}
			if(!$is_module_admin) {
				$getGrant = $this->_getBoardAdminGroup($module_info);
				$member_group_list = $logged_info->group_list;
				foreach($getGrant as $value){
					if(isset($member_group_list[$value])){
						$is_module_admin = true;
					}
				}
			}

		}

		return $is_module_admin;
	}

	function _getBoardAdminGroup($module_info){

		if(!$module_info){
			$mid = Context::get('mid');
			$oModuleModel = getModel('module');
			$module_info = $oModuleModel->getModuleInfoByMid($mid);
		}

		$args = new stdClass();
		$args->module_srl = $module_info->module_srl;
		$output = executeQueryArray('module.getModuleGrants', $args);

		$oMemberModel = getModel('member');
		$group_list = $oMemberModel->getGroups($module_info->site_srl);

		$adminGroup_array = array();

		foreach($output->data as $manager_group){
			if($manager_group->name === "manager"){
				foreach($group_list as $val){
					if($val->group_srl === $manager_group->group_srl){
						array_push($adminGroup_array, $manager_group->group_srl);
					}
				}
			}
		}

		return $adminGroup_array;
	}


}

/* End of file gamezot.model.php */
/* Location: ./modules/gamezot/gamezot.model.php */
