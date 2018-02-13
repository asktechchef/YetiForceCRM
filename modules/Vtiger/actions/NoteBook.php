<?php
/* +**********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.1
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 * Contributor(s): YetiForce.com
 * ********************************************************************************** */

class Vtiger_NoteBook_Action extends \App\Controller\Action
{
    use \App\Controller\ExposeMethod;

    /**
     * Function to check permission.
     *
     * @param \App\Request $request
     *
     * @throws \App\Exceptions\NoPermittedForAdmin
     */
    public function checkPermission(\App\Request $request)
    {
        $currentUserModel = Users_Record_Model::getCurrentUserModel();
        if (!$currentUserModel->isAdminUser()) {
            throw new \App\Exceptions\NoPermittedForAdmin('LBL_PERMISSION_DENIED');
        }
    }

    public function __construct()
    {
        $this->exposeMethod('noteBookCreate');
    }

    public function noteBookCreate(\App\Request $request)
    {
        $dataValue['contents'] = $request->get('notePadContent');
        $dataValue['lastSavedOn'] = date('Y-m-d H:i:s');
        $data = \App\Json::encode((object) $dataValue);
        $size = \App\Json::encode(['width' => $request->getInteger('width'), 'height' => $request->getInteger('height')]);
        $db = \App\Db::getInstance();
        $db->createCommand()
            ->insert('vtiger_module_dashboard', [
                'linkid' => $request->getInteger('linkId'),
                'blockid' => $request->getInteger('blockid'),
                'filterid' => 0,
                'title' => $request->get('notePadName'),
                'data' => $data,
                'isdefault' => $request->getInteger('isdefault'),
                'size' => $size,
            ])->execute();
        $result = [];
        $result['success'] = true;
        $result['widgetId'] = $db->getLastInsertID('vtiger_module_dashboard_id_seq');
        $response = new Vtiger_Response();
        $response->setResult($result);
        $response->emit();
    }

    public function validateRequest(\App\Request $request)
    {
        $request->validateWriteAccess();
    }
}
