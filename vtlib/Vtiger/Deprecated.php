<?php
/* +**********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 * Contributor(s): YetiForce.com
 * ********************************************************************************** */
namespace vtlib;

/**
 * Functions that need-rewrite / to be eliminated.
 */
class Deprecated
{

	public static function getFullNameFromArray($module, $fieldValues)
	{
		$entityInfo = \App\Module::getEntityInfo($module);
		$fieldsName = $entityInfo['fieldname'];
		$displayName = self::getCurrentUserEntityFieldNameDisplay($module, $fieldsName, $fieldValues);
		return $displayName;
	}

	/**
	 * this function returns the entity field name for a given module; for e.g. for Contacts module it return concat(lastname, ' ', firstname)
	 * @param1 $module - name of the module
	 * @param2 $fieldsName - fieldname with respect to module (ex : 'Accounts' - 'accountname', 'Contacts' - 'lastname','firstname')
	 * @param3 $fieldValues - array of fieldname and its value
	 * @return string $fieldConcatName - the entity field name for the module
	 */
	public static function getCurrentUserEntityFieldNameDisplay($module, $fieldsName, $fieldValues)
	{
		if (strpos($fieldsName, ',') === false) {
			return $fieldValues[$fieldsName];
		} else {
			$accessibleFieldNames = [];
			foreach (explode(',', $fieldsName) as $field) {
				if ($module === 'Users' || \App\Field::getColumnPermission($module, $field)) {
					$accessibleFieldNames[] = $fieldValues[$field];
				}
			}
			if (count($accessibleFieldNames) > 0) {
				return implode(' ', $accessibleFieldNames);
			}
		}
		return '';
	}

	public static function getModuleTranslationStrings($language, $module)
	{
		static $cachedModuleStrings = [];

		if (!empty($cachedModuleStrings[$module])) {
			return $cachedModuleStrings[$module];
		}
		$newStrings = \Vtiger_Language_Handler::getModuleStringsFromFile($language, $module);
		$cachedModuleStrings[$module] = $newStrings['languageStrings'];

		return $cachedModuleStrings[$module];
	}

	/** Function to check the file access is made within web root directory and whether it is not from unsafe directories */
	public static function checkFileAccessForInclusion($filepath)
	{
		$unsafeDirectories = ['storage', 'cache', 'test'];
		$realfilepath = realpath($filepath);

		/** Replace all \\ with \ first */
		$realfilepath = str_replace('\\\\', '\\', $realfilepath);
		$rootdirpath = str_replace('\\\\', '\\', ROOT_DIRECTORY . DIRECTORY_SEPARATOR);

		/** Replace all \ with / now */
		$realfilepath = str_replace('\\', '/', $realfilepath);
		$rootdirpath = str_replace('\\', '/', $rootdirpath);

		$relativeFilePath = str_replace($rootdirpath, '', $realfilepath);
		$filePathParts = explode('/', $relativeFilePath);

		if (stripos($realfilepath, $rootdirpath) !== 0 || in_array($filePathParts[0], $unsafeDirectories)) {
			\App\Log::error(__METHOD__ . '(' . $filepath . ') - Sorry! Attempt to access restricted file. realfilepath: ' . print_r($realfilepath, true));
			throw new \App\Exceptions\AppException('Sorry! Attempt to access restricted file.');
		}
	}

	/** Function to check the file deletion within the deletable (safe) directories */
	public static function checkFileAccessForDeletion($filepath)
	{
		$safeDirectories = ['storage', 'cache', 'test'];
		$realfilepath = realpath($filepath);

		/** Replace all \\ with \ first */
		$realfilepath = str_replace('\\\\', '\\', $realfilepath);
		$rootdirpath = str_replace('\\\\', '\\', ROOT_DIRECTORY . DIRECTORY_SEPARATOR);

		/** Replace all \ with / now */
		$realfilepath = str_replace('\\', '/', $realfilepath);
		$rootdirpath = str_replace('\\', '/', $rootdirpath);

		$relativeFilePath = str_replace($rootdirpath, '', $realfilepath);
		$filePathParts = explode('/', $relativeFilePath);

		if (stripos($realfilepath, $rootdirpath) !== 0 || !in_array($filePathParts[0], $safeDirectories)) {
			\App\Log::error(__METHOD__ . '(' . $filepath . ') - Sorry! Attempt to access restricted file. realfilepath: ' . print_r($realfilepath, true));
			throw new \App\Exceptions\AppException('Sorry! Attempt to access restricted file.');
		}
	}

	/** Function to check the file access is made within web root directory. */
	public static function checkFileAccess($filepath)
	{
		if (!self::isFileAccessible($filepath)) {

			\App\Log::error(__METHOD__ . '(' . $filepath . ') - Sorry! Attempt to access restricted file. realfilepath: ' . print_r($realfilepath, true));
			throw new \App\Exceptions\AppException('Sorry! Attempt to access restricted file.');
		}
	}

	/**
	 * function to return whether the file access is made within vtiger root directory
	 * and it exists.
	 * @param String $filepath relative path to the file which need to be verified
	 * @return Boolean true if file is a valid file within vtiger root directory, false otherwise.
	 */
	public static function isFileAccessible($filepath)
	{
		$realfilepath = realpath($filepath);

		/** Replace all \\ with \ first */
		$realfilepath = str_replace('\\\\', '\\', $realfilepath);
		$rootdirpath = str_replace('\\\\', '\\', ROOT_DIRECTORY . DIRECTORY_SEPARATOR);

		/** Replace all \ with / now */
		$realfilepath = str_replace('\\', '/', $realfilepath);
		$rootdirpath = str_replace('\\', '/', $rootdirpath);

		if (stripos($realfilepath, $rootdirpath) !== 0) {
			return false;
		}
		return true;
	}

	/**
	 * This function is used to get the blockid of the settings block for a given label.
	 * @param string $label
	 * @return int
	 */
	public static function getSettingsBlockId($label)
	{
		$blockId = 0;
		$dataReader = (new \App\Db\Query())->select(['blockid'])
				->from('vtiger_settings_blocks')
				->where(['label' => $label])
				->createCommand()->query();
		if ($dataReader->count() === 1) {
			$blockId = $dataReader->readColumn(0);
		}
		$dataReader->close();
		return $blockId;
	}

	public static function getSqlForNameInDisplayFormat($input, $module, $glue = ' ')
	{
		$entityFieldInfo = \App\Module::getEntityInfo($module);
		$fieldsName = $entityFieldInfo['fieldnameArr'];
		if (is_array($fieldsName)) {
			foreach ($fieldsName as &$value) {
				$formattedNameList[] = $input[$value];
			}
			$formattedNameListString = implode(",'" . $glue . "',", $formattedNameList);
		} else {
			$formattedNameListString = $input[$fieldsName];
		}
		$sqlString = "CONCAT(" . $formattedNameListString . ")";
		return $sqlString;
	}
	/* Function to get the related tables data
	 * @param - $module - Primary module name
	 * @param - $secmodule - Secondary module name
	 * return Array $rel_array tables and fields to be compared are sent
	 * */

	public static function getRelationTables($module, $secmodule)
	{
		$adb = PearDatabase::getInstance();
		$primary_obj = CRMEntity::getInstance($module);
		$secondary_obj = CRMEntity::getInstance($secmodule);

		$ui10_query = $adb->pquery("SELECT vtiger_field.tabid AS tabid,vtiger_field.tablename AS tablename, vtiger_field.columnname AS columnname FROM vtiger_field INNER JOIN vtiger_fieldmodulerel ON vtiger_fieldmodulerel.fieldid = vtiger_field.fieldid WHERE (vtiger_fieldmodulerel.module=? && vtiger_fieldmodulerel.relmodule=?) || (vtiger_fieldmodulerel.module=? && vtiger_fieldmodulerel.relmodule=?)", [$module, $secmodule, $secmodule, $module]);
		if ($adb->numRows($ui10_query) > 0) {
			$ui10_tablename = $adb->queryResult($ui10_query, 0, 'tablename');
			$ui10_columnname = $adb->queryResult($ui10_query, 0, 'columnname');

			if ($primary_obj->table_name == $ui10_tablename) {
				$reltables = [$ui10_tablename => ["" . $primary_obj->table_index . "", "$ui10_columnname"]];
			} else if ($secondary_obj->table_name == $ui10_tablename) {
				$reltables = [$ui10_tablename => ["$ui10_columnname", "" . $secondary_obj->table_index . ""], "" . $primary_obj->table_name . "" => "" . $primary_obj->table_index . ""];
			} else {
				if (isset($secondary_obj->tab_name_index[$ui10_tablename])) {
					$rel_field = $secondary_obj->tab_name_index[$ui10_tablename];
					$reltables = [$ui10_tablename => ["$ui10_columnname", "$rel_field"], "" . $primary_obj->table_name . "" => "" . $primary_obj->table_index . ""];
				} else {
					$rel_field = $primary_obj->tab_name_index[$ui10_tablename];
					$reltables = [$ui10_tablename => ["$rel_field", "$ui10_columnname"], "" . $primary_obj->table_name . "" => "" . $primary_obj->table_index . ""];
				}
			}
		} else {
			if (method_exists($primary_obj, setRelationTables)) {
				$reltables = $primary_obj->setRelationTables($secmodule);
			} else {
				$reltables = '';
			}
		}
		if (is_array($reltables) && !empty($reltables)) {
			$rel_array = $reltables;
		} else {
			$rel_array = ["vtiger_crmentityrel" => ["crmid", "relcrmid"], "" . $primary_obj->table_name . "" => "" . $primary_obj->table_index . ""];
		}
		return $rel_array;
	}

	/**
	 * This function returns no value but handles the delete functionality of each entity.
	 * Input Parameter are $module - module name, $return_module - return module name, $focus - module object, $record - entity id, $return_id - return entity id.
	 */
	public static function deleteEntity($destinationModule, $sourceModule, CRMEntity $focus, $destinationRecordId, $sourceRecordId, $relatedName = false)
	{
		\App\Log::trace("Entering deleteEntity method ($destinationModule, $sourceModule, $destinationRecordId, $sourceRecordId)");
		if ($destinationModule != $sourceModule && !empty($sourceModule) && !empty($sourceRecordId)) {
			$eventHandler = new App\EventHandler();
			$eventHandler->setModuleName($sourceModule);
			$eventHandler->setParams([
				'CRMEntity' => $focus,
				'sourceModule' => $sourceModule,
				'sourceRecordId' => $sourceRecordId,
				'destinationModule' => $destinationModule,
				'destinationRecordId' => $destinationRecordId,
			]);
			$eventHandler->trigger('EntityBeforeUnLink');

			$focus->unlinkRelationship($destinationRecordId, $sourceModule, $sourceRecordId, $relatedName);
			$focus->trackUnLinkedInfo($sourceRecordId);

			$eventHandler->trigger('EntityAfterUnLink');
		} else {
			$currentUserPrivilegesModel = Users_Privileges_Model::getCurrentUserPrivilegesModel();
			if (!$currentUserPrivilegesModel->isPermitted($destinationModule, 'Delete', $destinationRecordId)) {
				throw new \App\Exceptions\AppException('LBL_PERMISSION_DENIED');
			}
			$focus->trash($destinationModule, $destinationRecordId);
		}
		\App\Log::trace('Exiting deleteEntity method ...');
	}

	/**
	 * Function to related two records of different entity types
	 */
	public static function relateEntities(CRMEntity $focus, $sourceModule, $sourceRecordId, $destinationModule, $destinationRecordIds, $relatedName = false)
	{
		\App\Log::trace("Entering relateEntities method ($sourceModule, $sourceRecordId, $destinationModule, $destinationRecordIds)");
		if (!is_array($destinationRecordIds))
			$destinationRecordIds = [$destinationRecordIds];

		$data = [
			'CRMEntity' => $focus,
			'sourceModule' => $sourceModule,
			'sourceRecordId' => $sourceRecordId,
			'destinationModule' => $destinationModule,
		];
		$eventHandler = new App\EventHandler();
		$eventHandler->setModuleName($sourceModule);
		foreach ($destinationRecordIds as &$destinationRecordId) {
			$data['destinationRecordId'] = $destinationRecordId;
			$eventHandler->setParams($data);
			$eventHandler->trigger('EntityBeforeLink');
			$focus->saveRelatedModule($sourceModule, $sourceRecordId, $destinationModule, $destinationRecordId, $relatedName);
			CRMEntity::trackLinkedInfo($sourceRecordId);
			$eventHandler->trigger('EntityAfterLink');
		}
		\App\Log::trace("Exiting relateEntities method ...");
	}
}
