<?php
/*! Copyright (C) 2016 BGM STORAGE. All rights reserved. */
/**
 * @class gamezotController
 * @author Huhani (mmia268@gmail.com)
 * @brief Gamezot module controller class.
 */

class gamezotController extends gamezot
{
	function init(){
		//Context::loadLang('./modules/gamezot/lang/');
	}

	function triggerDeleteMember(&$obj){
		//탈퇴한 회원일 경우 member_srl * -1로 업데이트
		//$args = new stdClass();
		//$args->target_member_srl = $obj->member_srl;
		//$output = executeQuery('gamezot.updateGamezotByDeletedMember', $args);

		return new Object();
	}


	function triggerBeforeUpdateDocument(&$obj){
		$oGamezotModel = getModel('gamezot');
		$isBoardAdmin = $oGamezotModel->checkIsBoardAdmin();
		if($isBoardAdmin){
			return new Object();
		}

		$document_srl = $obj->document_srl ? $obj->document_srl : Context::get('document_srl');
		if(!$document_srl){
			return new Object();
		}

		$oDocumentModel = getModel('document');
		$oDocumentController = getController('document');
		$oDocument = $oDocumentModel->getDocument($document_srl);
		if (!$oDocument->isExists()){
			return new Object();
		}

		$document_content = $oDocument->get('content');
		if($this->_isDeletedContentByDocument($document_content)){
			return new Object(-1, 'msg_document_deleted');
		}

		if($oDocument->get('status') == "PUBLIC" && $obj->status != "PUBLIC"){
			$obj->status = "PUBLIC";
		}

		return new Object();
	}

	function triggerBeforeDeleteDocument(&$obj){
		$oGamezotModel = getModel('gamezot');
		$isBoardAdmin = $oGamezotModel->checkIsBoardAdmin();
		if($isBoardAdmin){
			return new Object();
		}

		$document_srl = $obj->document_srl ? $obj->document_srl : Context::get('document_srl');

		$oDocumentModel = getModel('document');
		$oDocumentController = getController('document');
		$oDocument = $oDocumentModel->getDocument($document_srl);
		if (!$oDocument->isExists()){
			return new Object();
		}

		$document_content = $oDocument->get('content');
		if($this->_isDeletedContentByDocument($document_content)){
			return new Object(-1, 'msg_document_deleted');
		}

		return new Object();
	}

	function _triggerBeforeDeleteDocument(&$obj){
		$oGamezotModel = getModel('gamezot');
		$isBoardAdmin = $oGamezotModel->checkIsBoardAdmin();
		if($isBoardAdmin){
			return new Object();
		}

		$document_srl = Context::get('document_srl');

		$oDocumentModel = getModel('document');
		$oDocumentController = getController('document');
		$oDocument = $oDocumentModel->getDocument($document_srl);
		if (!$oDocument->isExists()){
			return new Object();
		}

		if(!$oDocument->isGranted()){
			return new Object(-1, 'msg_not_permitted');
		}

		$document_content = $oDocument->get('content');
		if($this->_isDeletedContentByDocument($document_content)){
			return new Object(-1, 'msg_document_deleted');
		}

		global $lang;

		$args = (object)$oDocument->variables;
		$args->title = $lang->msg_document_deleted;
		$args->title_bold = 'N';
		$args->title_color = '888888';
		$args->content = sprintf('<!--DeletedDocument--><p>%s</p>', $lang->msg_document_deleted);
		$args->comment_status = $args->commentStatus = 'DENY';
		$output = executeQuery('document.updateDocument', $args);

		if(!$output->toBool()){
			return $output;
		}

		$oFileController = getController('file');
		$output = $oFileController->deleteFiles($document_srl);

		$oCacheHandler = CacheHandler::getInstance('object');
		if($oCacheHandler->isSupport()){
			$cache_key = 'document_item:'. getNumberingPath($document_srl) . $document_srl;
			$oCacheHandler->delete($cache_key);
		}

		$obj->setRedirectUrl(getNotEncodedUrl('', 'mid', Context::get('mid'), 'act', '', 'page', Context::get('page'), 'document_srl', ''));
		$obj->add('mid', Context::get('mid'));
		$obj->add('page', Context::get('page'));
		if(Context::get('xeVirtualRequestMethod') !== 'xml'){
			$obj->setMessage('success_deleted');
		}
		$obj->act = '';

		return new Object();
	}

	function triggerBeforeUpdateComment(&$obj){
		$oGamezotModel = getModel('gamezot');
		$isBoardAdmin = $oGamezotModel->checkIsBoardAdmin();
		if($isBoardAdmin){
			return new Object();
		}

		$comment_srl = $obj->comment_srl ? $obj->comment_srl : Context::get('comment_srl');

		$oCommentModel = getModel('comment');
		$oComment = $oCommentModel->getComment($comment_srl);
		if (!$oComment->isExists()){
			return new Object();
		}

		$comment_content = $oComment->get('content');
		if($this->_isDeletedContentByComment($comment_content)){
			return new Object(-1, 'msg_already_comment_deleted');
		}

		if ($oCommentModel->getChildCommentCount($oComment->get('comment_srl')) > 0){
			return new Object(-1, 'msg_comment_update_deny_child');
		}
	}

	function triggerBeforeDeleteComment(&$obj){
		$oGamezotModel = getModel('gamezot');
		$isBoardAdmin = $oGamezotModel->checkIsBoardAdmin();
		if($isBoardAdmin){
			return new Object();
		}

		$comment_srl = $obj->comment_srl ? $obj->comment_srl : Context::get('comment_srl');
		$oCommentModel = getModel('comment');
		$oComment = $oCommentModel->getComment($comment_srl);
		if (!$oComment->isExists()){
			return new Object();
		}

		$comment_content = $oComment->get('content');
		if($this->_isDeletedContentByComment($comment_content)){
			return new Object(-1, 'msg_already_comment_deleted');
		}

		return new Object();

	}

	function _triggerBeforeDeleteComment(&$obj){
		$oGamezotModel = getModel('gamezot');
		$isBoardAdmin = $oGamezotModel->checkIsBoardAdmin();
		if($isBoardAdmin){
			return new Object();
		}

		$comment_srl = Context::get('comment_srl');
		$oCommentModel = getModel('comment');

		$oComment = $oCommentModel->getComment($comment_srl);
		if (!$oComment->isExists()){
			return new Object();
		}
		if(!$oComment->isGranted()){
			return new Object(-1, 'msg_not_permitted');
		}

		$comment_content = $oComment->get('content');
		if($this->_isDeletedContentByComment($comment_content)){
			return new Object(-1, 'msg_already_comment_deleted');
		}

		global $lang;

		$args = (object)$oComment->variables;
		$args->content = sprintf('<!--DeletedComment--><span style="color:#888888">%s</span>', $lang->msg_comment_deleted);
		$output = executeQuery('comment.updateComment', $args);
		if (!$output->toBool())	{
			return $output;
		}

		$oFileController = getController('file');
		$output = $oFileController->deleteFiles($comment_srl);

		$obj->add('mid', Context::get('mid'));
		$obj->add('page', Context::get('page'));
		$obj->add('comment_srl', $output->get('comment_srl'));
		if(Context::get('xeVirtualRequestMethod') !== 'xml'){
			$obj->setMessage('success_deleted');
		}
		$obj->act = '';

	}

	function _triggerBeforeDocumentVote(&$obj){
		$oGamezotModel = getModel('gamezot');
		$isBoardAdmin = $oGamezotModel->checkIsBoardAdmin();
		if($isBoardAdmin){
			return new Object();
		}

		$document_srl = Context::get('target_srl');

		$oDocumentModel = getModel('document');
		$oDocumentController = getController('document');
		$oDocument = $oDocumentModel->getDocument($document_srl);
		if (!$oDocument->isExists()){
			return new Object();
		}

		$document_content = $oDocument->get('content');
		if($this->_isDeletedContentByDocument($document_content)){
			return new Object(-1, $obj->act == 'procDocumentVoteUp' ? 'msg_document_deleted_vote_up' : 'msg_document_deleted_vote_down');
		}

		return new Object();
	}

	function _triggerBeforeCommentVote(&$obj){
		$oGamezotModel = getModel('gamezot');
		$isBoardAdmin = $oGamezotModel->checkIsBoardAdmin();
		if($isBoardAdmin){
			return new Object();
		}

		$comment_srl = Context::get('target_srl');
		$oCommentModel = getModel('comment');
		$oComment = $oCommentModel->getComment($comment_srl);
		if (!$oComment->isExists()){
			return new Object();
		}

		$comment_content = $oComment->get('content');
		if($this->_isDeletedContentByComment($comment_content)){
			return new Object(-1, $obj->act == 'procCommentVoteUp' ? 'msg_comment_deleted_vote_up' : 'msg_comment_deleted_vote_down');
		}

		return new Object();
	}


	function triggerBeforeModuleProc(&$oModule){

		switch($oModule->act){
			case "procDocumentVoteUp":
			case "procDocumentVoteDown":
				return $this->_triggerBeforeDocumentVote($oModule);
			break;

			case "procBoardDeleteDocument":
				return $this->_triggerBeforeDeleteDocument($oModule);
			break;

			case "dispBoardDelete":
				return $this->triggerBeforeDeleteDocument($oModule);
			break;

			case "dispBoardWrite":
				return $this->triggerBeforeUpdateDocument($oModule);
			break;

			case "procBoardDeleteComment":
				return $this->_triggerBeforeDeleteComment($oModule);
			break;

			case "procCommentVoteUp":
			case "procCommentVoteDown":
				return $this->_triggerBeforeCommentVote($oModule);
			break;

			case "dispBoardModifyComment":
				return $this->triggerBeforeUpdateComment($oModule);
			break;

			case "dispBoardDeleteComment":
				return $this->triggerBeforeDeleteComment($oModule);
			break;

		}

		return new Object();
	}

	function _isDeletedContentByComment($content){
		return strlen($content) > 21 && substr($content,0 , 21) == "<!--DeletedComment-->" ? TRUE : FALSE;
	}

	function _isDeletedContentByDocument($content){
		return strlen($content) > 22 && substr($content,0 , 22) == "<!--DeletedDocument-->" ? TRUE : FALSE;
	}

}

/* End of file gamezot.controller.php */
/* Location: ./modules/gamezot/gamezot.controller.php */
