<?php 
/***************************************************************************
 *                               users.php
 *                            -------------------
 *   begin                : Saturday, Sept 24, 2005
 *   copyright            : (C) 2005 Paden Clayton - Fast Track Sites
 *   email                : sales@fasttacksites.com
 *
 *
 ***************************************************************************/

/***************************************************************************
Redistribution and use in source and binary forms, with or without
modification, are permitted provided that the following conditions are met:
    * Redistributions of source code must retain the above copyright
      notice, this list of conditions and the following disclaimer.
    * Redistributions in binary form must reproduce the above copyright
      notice, this list of conditions and the following disclaimer in the
      documentation and/or other materials provided with the distribution.
    * Neither the name of the <organization> nor the
      names of its contributors may be used to endorse or promote products
      derived from this software without specific prior written permission.

THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS "AS IS" AND
ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT LIMITED TO, THE IMPLIED
WARRANTIES OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE ARE
DISCLAIMED. IN NO EVENT SHALL <COPYRIGHT HOLDER> BE LIABLE FOR ANY
DIRECT, INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES
(INCLUDING, BUT NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES;
LOSS OF USE, DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND
ON ANY THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT
(INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE OF THIS
SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.
 ***************************************************************************/
 
//=========================================================
// Gets a username from a userid
//=========================================================
function getUsernameFromID($userID) {
	$sql = "SELECT username FROM `" . USERSDBTABLEPREFIX . "users` WHERE id='" . $userID . "'";
	$result = mysql_query($sql);
	
	if ($result && mysql_num_rows($result) > 0) {
		while ($row = mysql_fetch_array($result)) {
			return $row['username'];
		}	
		mysql_free_result($result);
	}
}

//=========================================================
// Gets a user's userlevel from a userid
//=========================================================
function getUserlevelFromID($userID) {
	$level = "";
	
	$sql = "SELECT user_level FROM `" . USERSDBTABLEPREFIX . "users` WHERE id='" . $userID . "' LIMIT 1";
	$result = mysql_query($sql);
	
	if ($result && mysql_num_rows($result) > 0) {
		while ($row = mysql_fetch_array($result)) {
			$level = ($row['user_level'] == ADMIN) ? "Administrator" : "Moderator";
			$level = ($row['user_level'] == USER) ? "User" : $level;
			$level = ($row['user_level'] == BANNED) ? "Banned" : $level;
		}	
		mysql_free_result($result);
	}
	
	return $level;
}

//=========================================================
// Gets an email address from a userid
//=========================================================
function getEmailAddressFromID($userID) {
	$sql = "SELECT email_address FROM `" . USERSDBTABLEPREFIX . "users` WHERE id='" . $userID . "'";
	$result = mysql_query($sql);
	
	if ($result && mysql_num_rows($result) > 0) {
		while ($row = mysql_fetch_array($result)) {
			return $row['email_address'];
		}	
		mysql_free_result($result);
	}
}

?>