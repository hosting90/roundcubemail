<?php

/**
 * H90 Password Driver
 *
 * Driver for passwords stored in Hosting90 hosting system
 *
 * @version 1.0
 * @author Jiri Lunacek <jiri.lunacek@hosting90.cz>
 *
 * Copyright (C) Hosting90 Systems s.r.o.
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program. If not, see http://www.gnu.org/licenses/.
 */


class rcube_h90_password
{
    /**
     * Update current user password
     *
     * @param string $curpass Current password
     * @param string $passwd  New password
     *
     * @return int Result
     */
    function save($curpass, $passwd)
    {
			$rcmail = rcmail::get_instance();
			$username = $_SESSION['username'];
			
			$url = 'https://administrace.hosting90.cz/cron/update_mailbox_password';
			$ch = curl_init($url . '?mailbox='. $username);
			$data = [
				'password_old' => $curpass,
				'password_new' => $passwd,
				'remote_addr' => $_SERVER['REMOTE_ADDR'],
			];

			curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
			$result = curl_exec($ch);
			curl_close($ch);
			
			$result = json_decode($result);
			if ($result === FALSE) {
				return PASSWORD_ERROR;
			}
			if ($result->result === true) {
				return PASSWORD_SUCCESS;
			} else {
				switch ($result->error) {
					case 'same_as_previous':
						return PASSWORD_COMPARE_NEW;
						break;
					case 'too_simple':
						return PASSWORD_CONSTRAINT_VIOLATION;
						break;
					case 'invalid_login':
						return PASSWORD_COMPARE_OLD;
						break;
					default:
						return PASSWORD_ERROR;
						break;
				}
			}
			return PASSWORD_ERROR;
    }

}
