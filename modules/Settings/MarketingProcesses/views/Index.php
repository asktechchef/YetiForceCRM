<?php

/**
 * Settings MarketingProcesses index view class.
 *
 * @copyright YetiForce Sp. z o.o
 * @license YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 */
class Settings_MarketingProcesses_Index_View extends Settings_Vtiger_Index_View
{
    /**
     * Process.
     *
     * @param \App\Request $request
     */
    public function process(\App\Request $request)
    {
        \App\Log::trace('Start '.__METHOD__);
        $qualifiedModule = $request->getModule(false);
        $moduleModel = Settings_MarketingProcesses_Module_Model::getCleanInstance();

        $viewer = $this->getViewer($request);
        $viewer->assign('QUALIFIED_MODULE', $qualifiedModule);
        $viewer->assign('MODULE_MODEL', $moduleModel);
        $viewer->assign('LEADS_MODULE_MODEL', Vtiger_Module_Model::getInstance('Leads'));
        $viewer->assign('ACCOUNTS_MODULE_MODEL', Vtiger_Module_Model::getInstance('Accounts'));
        $viewer->view('Index.tpl', $qualifiedModule);
        \App\Log::trace('End '.__METHOD__);
    }

    public function getFooterScripts(\App\Request $request)
    {
        $headerScriptInstances = parent::getFooterScripts($request);
        $moduleName = $request->getModule();

        $jsFileNames = [
            "modules.Settings.$moduleName.resources.Index",
            'modules.Settings.Leads.resources.LeadMapping',
        ];

        $jsScriptInstances = $this->checkAndConvertJsScripts($jsFileNames);
        $headerScriptInstances = array_merge($headerScriptInstances, $jsScriptInstances);

        return $headerScriptInstances;
    }
}
