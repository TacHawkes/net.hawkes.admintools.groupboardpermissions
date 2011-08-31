<?php
require_once(WCF_DIR.'lib/system/event/EventListener.class.php');

/**
 * Copies user group rights and users from one group to another
 *
 * This file is part of Admin Tools 2.
 *
 * Admin Tools 2 is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * Admin Tools 2 is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with Admin Tools 2.  If not, see <http://www.gnu.org/licenses/>.
 *
 * @author	Oliver Kliebisch
 * @copyright	2009 Oliver Kliebisch
 * @license	GNU General Public License <http://www.gnu.org/licenses/>
 * @package	net.hawkes.admintools
 * @package	net.hawkes.admintools.groupboardpermissions
 * @category WCF
 */
class GroupCopyBoardPermissionsListener implements EventListener {
	public $permissionSettings = array();

	/**
	 * @see EventListener::execute()
	 */
	public function execute($eventObj, $className, $eventName) {
		$parameters = $eventObj->data['parameters']['user.usergroupcopy'];				
		if ($parameters['copyBoardPermissions']) {
			$this->readPermissionSettings();
			
			$sql = "DELETE FROM wbb".WBB_N."_board_to_group
                     WHERE groupID = ".$parameters['targetGroup'];
			WCF::getDB()->sendQuery($sql);

			$sql = "INSERT INTO wbb".WBB_N."_board_to_group (boardID, groupID, ".implode(',', $this->permissionSettings).")
					SELECT boardID, ".$parameters['targetGroup'].", ".implode(',', $this->permissionSettings)."
					FROM wbb".WBB_N."_board_to_group
					WHERE groupID = ".$parameters['sourceGroup'];			
			WCF::getDB()->sendQuery($sql);
		}
	}

	/**
	 * Gets available permission settings.
	 */
	protected function readPermissionSettings() {
		$sql = "SHOW COLUMNS FROM wbb".WBB_N."_board_to_group";
		$result = WCF::getDB()->sendQuery($sql);
		while ($row = WCF::getDB()->fetchArray($result)) {
			if ($row['Field'] != 'boardID' && $row['Field'] != 'groupID') {
				$this->permissionSettings[] = $row['Field'];
			}
		}
	}
}
?>