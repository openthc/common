<?php
/**
 * Inbound Connection from Registered Application
 */

namespace OpenTHC\Controller\Auth;

use Edoceo\Radix;
use Edoceo\Radix\Session;
use Edoceo\Radix\DB\SQL;

use OpenTHC\Contact;

class Connect extends \OpenTHC\Controller\Base
{
	function __invoke($REQ, $RES, $ARG)
	{
		$_SESSION = array();

		$db_conf = \OpenTHC\Config::get('database_auth');
		if (empty($db_conf)) {
			return $RES->withJSON([
				'meta' => [ 'detail' => 'Fatal Database Error [CAC#024]'],
				'data' => [],
			], 500);
		}

		$dbc = new SQL(sprintf('pgsql:host=%s;dbname=%s', $db_conf['hostname'], $db_conf['database']), $db_conf['username'], $db_conf['password']);

		// Find the Program or Service that is connecting
		if (empty($_GET['client_id'])) {
			return $RES->withJSON([
				'meta' => [ 'detail' => 'Invalid Application [CAC#027]'],
				'data' => [],
			], 400);
		}

		$sql = 'SELECT * FROM auth_program WHERE code = ?';
		$arg = array($_GET['client_id']);
		$App = $dbc->fetchRow($sql, $arg);
		if (empty($App['id'])) {
			return $RES->withJSON([
				'meta' => [ 'detail' => 'Invalid Application [CAC#034]'],
				'data' => [],
			], 400);
		}

		// Only Live Applications
		if (200 != $App['stat']) {
			return $RES->withJSON([
				'meta' => [ 'detail' => 'Invalid Application [CAC#042]'],
				'data' => [],
			], 400);
		}

		// With Live Flag
		if (($App['flag'] & 0x00000001) == 0) {
			return $RES->withJSON([
				'meta' => [ 'detail' => 'Invalid Application [CAC#039]'],
				'data' => [],
			], 400);
		}

		// Decrypt passed in data with the App Secret
		$tmp_auth = _decrypt($_GET['_'], $App['hash']);
		if (empty($tmp_auth)) {
			return $RES->withJSON([
				'meta' => [ 'detail' => 'Invalid Parameters [CAC#051]'],
				'data' => [],
			], 400);
		}

		$tmp_auth = json_decode($tmp_auth, true);
		if (empty($tmp_auth)) {
			return $RES->withJSON([
				'meta' => [ 'detail' => 'Invalid Parameters [CAC#056]'],
				'data' => [],
			], 400);
		}

		$tmp_auth['company']['id'] = strtoupper($tmp_auth['company']['id']);
		$tmp_auth['license']['id'] = strtoupper($tmp_auth['license']['id']);

		//var_dump($tmp_auth);
		//var_dump($_SESSION);

		// Lookup Company
		$sql = 'SELECT * FROM company WHERE id = ?';
		$arg = array($tmp_auth['company']['id']);
		$res = $dbc->fetchRow($sql, $arg);
		if (empty($res['id'])) {
			return $RES->withJSON([
				'meta' => [ 'detail' => sprintf('Invalid Company "%s" [CAC#067]', $tmp_auth['company']['id']) ],
				'data' => [],
			], 400);
		}
		$Company = $res;

		// Lookup License
		$sql = 'SELECT * FROM license WHERE company_id = ? AND id = ?';
		$arg = array($Company['id'], $tmp_auth['license']['id']);
		$License = $dbc->fetchRow($sql, $arg);
		if (empty($License['id'])) {
			return $RES->withJSON([
				'meta' => [ 'detail' => sprintf('Invalid License "%s" [CAC#076]', $tmp_auth['license']['id']) ],
				'data' => [],
			], 400);
		}

		// Lookup Contact
		if (!empty($tmp_auth['contact']['id'])) {

		}

		$x = $tmp_auth['contact']['email'];
		$x = strtolower(trim($x));
		if (!filter_var($x, FILTER_VALIDATE_EMAIL)) {
			_exit_text('Invalid Contact [CAC#084]', 400);
		}
		$tmp_auth['contact']['email'] = $x;

		$sql = 'SELECT * FROM contact WHERE company_id = ? AND email = ?';
		$arg = array($Company['id'], $tmp_auth['contact']['email']);
		$Contact = $dbc->fetchRow($sql, $arg);
		if (empty($Contact['id'])) {
			$Contact = array(
				'id' => _ulid(),
				'company_id' => $Company['id'],
				'email' => $tmp_auth['contact']['email']
			);
			$Contact['id'] = $dbc->insert('contact', $Contact);
		}

		// Lookup Auth_Contact
		$sql = 'SELECT * FROM auth_contact WHERE username = ?';
		$arg = array($tmp_auth['contact']['email']);
		$AppUser = $dbc->fetchRow($sql, $arg);
		if (empty($AppUser['id'])) {
			$AppUser = array(
				'id' => $Contact['id'],
				'contact_id' => $Contact['id'],
				'company_id' => $Company['id'],
				'username' => $tmp_auth['contact']['email'],
				'password' => sha1($_GET['_']),
			);
			$AppUser['id'] = $dbc->insert('auth_contact', $AppUser);
		}

		if ($AppUser['company_id'] != $Company['id']) {
			_exit_text('Please Contact Support [CAC#124]');
		}

		// Validate Contact
		if (empty($AppUser['contact_id'])) {
			$AppUser['contact_id'] = $Contact['id'];
			$sql = 'UPDATE auth_contact SET contact_id = ? WHERE id = ?';
			$arg = array($Contact['id'], $AppUser['id']);
			$dbc->query($sql, $arg);
		}
		if ($AppUser['contact_id'] != $Contact['id']) {
			//print_r($AppUser);
			//print_r($Contact);
			_exit_text('Please Contact Support [CAC#137]');
		}


		// OK
		$_SESSION['Company'] = $Company;
		$_SESSION['gid'] = $Company['id'];

		$_SESSION['License'] = $License;
		$_SESSION['Contact'] = $Contact;

		$_SESSION['AppUser'] = $AppUser; // @deprcated
		$_SESSION['uid'] = $AppUser['id'];

		// Canon
		//var_dump($tmp_auth);
		if (!empty($tmp_auth['cre'])) {
			$_SESSION['cre'] = array(
				'engine' => $tmp_auth['cre']['engine'],
				'client' => $tmp_auth['cre']['client'],
			);
			$_SESSION['cre-auth'] = array(
				'company' => $Company['guid'],
				'license' => $tmp_auth['cre']['client']['license'],
				'license-key' => $tmp_auth['cre']['client']['license-key'],
			);
		} else {
			// Legacy Shit
			// Save State
			$_SESSION['cre'] = array(
				'code' => 'usa/wa',
				'engine' => 'leafdata',
			);
			$_SESSION['cre-base'] = 'leafdata';
			$_SESSION['cre-auth'] = array(
				'company' => $Company['guid'],
				'license' => $tmp_auth['cre']['auth']['license'],
				'license-key' => $tmp_auth['cre']['auth']['secret'],
			);
		}

		$_SESSION['sql-hash'] = sha1(json_encode($tmp_auth['cre']));

		$this->_connect_info = $tmp_auth;

		return $RES->withRedirect('/auth/back');

	}
}
