<?php

defined('TYPO3') or die();

(function (): void {
    \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addTypoScriptSetup('
tt_content.jwtue_roleandcontactcard.dataProcessing {
    20 = JwTue\RolesAndContacts\DataProcessing\RoleProcessor
}
');
})();
