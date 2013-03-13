<?php

uses(DS . 'view' . DS . 'helpers' . DS . 'global');

class CrudController extends AppController {

	var $model;
	var $model_name;
	var $addSuccessMsg = '添加成功';
	var $updateSuccessMsg = '更新成功';
	var $results;
	var $id;
	var $reflash = 0;
	var $addSuccessRedirectUrl = '';
	var $updateSuccessRedirectUrl = '';
	var $deleteSuccessRedirectUrl = '';
	var $page_show = 20;
	var $page_sortBy = 'id';
	var $page_direction = 'DESC';
	var $page_maxPages = 10;
	var $data = array();

	function beforeFilter() {
		parent::beforeFilter();
	}

	function beforeRender() {
		parent::beforeRender();
		$this->set('cn', $this->name);
		if(isset ($this->mn))$this->set('mn', $this->mn);
	}

	function index($id=null) {

		$this->id = $id;
		$condition_str = $this->_index_build_condition($id);
		if ($condition_str == false)
			$condition_str = '';

		$this->Pagination->show = $this->page_show;
		$this->Pagination->sortBy = $this->page_sortBy;
		$this->Pagination->direction = $this->page_direction;
		$this->Pagination->maxPages = $this->page_maxPages;
		list($order, $limit, $page) = $this->Pagination->init($condition_str, array('modelClass' => $this->model->name));

		$this->results = $this->model->findAll($condition_str, '', $order, $limit, $page);

		$this->_before_render_index($id);
		$this->set('results', $this->results);
	}

	function edit($id=null) {

		//处理提交的信息

		if (!empty($this->data)) {

			if ($this->_editValidate()) {

				if (!$this->updateSuccessRedirectUrl)
					$this->updateSuccessRedirectUrl = @$this->data[$this->model->name]['referer'];

				if (!$this->addSuccessRedirectUrl && isset($this->data[$this->model->name]['referer']))
					$this->addSuccessRedirectUrl = $this->data[$this->model->name]['referer'];

				//update
				if (isset($this->data[$this->model->name][$this->model->primaryKey]) && $this->data[$this->model->name][$this->model->primaryKey]) {

					if ($this->_beforeUpdate($id) && $this->model->save($this->data)) {
						$this->_afterUpdate($this->data[$this->model->name][$this->model->primaryKey]);
						$this->flash($this->updateSuccessMsg . '，系统现自动返回列表', $this->updateSuccessRedirectUrl == '' ? '/' . $this->name . '/index' : $this->updateSuccessRedirectUrl, 1, $this->reflash);
					}
					else {
						$this->_updateFail();
					}
				}
				else { //add

					if ($this->_beforeAdd() && $this->model->save($this->data)) {
						$id = $this->model->id;
						$this->_afterAdd($id);

						$this->flash($this->addSuccessMsg . '，系统现自动返回列表', $this->addSuccessRedirectUrl == '' ? '/' . $this->name . '/index' : $this->addSuccessRedirectUrl, 1, $this->reflash);
					}
					else {
						$this->_addFail();
					}
				}
			}
			else {
				$this->_editValidateFail();
			}
		}
		else {
			$this->model->id = $id;
			$this->data = $this->model->read();
		}

		$this->set('id', $id);
		$this->_before_render_edit($id);
	}

	function delete($id) {//利用触发删除搜索索引
		if (!$this->deleteSuccessRedirectUrl)
			$this->deleteSuccessRedirectUrl = $this->referer();
		$this->model->id = $id;
		$this->data = $this->model->find();

		if ($this->data && $this->_beforeDelete()) {
			if ($this->model->del($id)) {
				$this->_afterDelete($id);
				$this->flash('指定信息删除成功，系统现自动返回列表', $this->deleteSuccessRedirectUrl == '' ? '/' . $this->name . '/index' : $this->deleteSuccessRedirectUrl, 1, $this->reflash);
			}
			else {
				$this->flash('指定信息删除失败，请通知管理员处理，系统现自动返回列表', $this->deleteSuccessRedirectUrl == '' ? '/' . $this->name . '/index' : $this->deleteSuccessRedirectUrl, 1, $this->reflash);
			}
		}
		else {
			$this->flash('找不到资料，系统现自动返回列表', $this->deleteSuccessRedirectUrl == '' ? '/' . $this->name . '/index' : $this->deleteSuccessRedirectUrl, 1, $this->reflash);
		}
	}

	//custom action

	function _index_build_condition($id=null) {
		return false;
	}

	function _before_render_index($id=null) {

	}

	function _before_render_edit($id=null) {

	}

	function _editValidate() {

		if (!$this->model->validates($this->data)) {
			return false;
		}
		return true;
	}

	function _editValidateFail() {
		return true;
	}

	function _beforeUpdate($id) {
		return true;
	}

	function _beforeAdd() {
		return true;
	}

	function _beforeDelete() {
		return true;
	}

	function _afterUpdate($id=null) {
		return true;
	}

	function _afterAdd($id=null) {
		return $this->_afterUpdate($id);
	}

	function _addFail() {
		return true;
	}

	function _updateFail() {
		return true;
	}

	function _afterDelete($id='') {

	}

}

?>