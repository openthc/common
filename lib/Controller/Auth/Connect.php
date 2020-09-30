<?php
/**
 * Inbound Connection from Registered Application
 */

namespace OpenTHC\Controller\Auth;

use Edoceo\Radix\DB\SQL;

class Connect extends \OpenTHC\Controller\Base
{
	protected $_connect_info; // @deprecated

	protected $_Company_Auth;
	protected $_Company_Base;

	protected $_Contact_Auth;
	protected $_Contact_Base;

	protected $_License;

	protected $_Service;

	function __invoke($REQ, $RES, $ARG)
	{
		// Reset Session
		$_SESSION = array();

		// Find the Program or Service that is connecting
		if (empty($_GET['client_id'])) {
			return $RES->withJSON([
				'data' => [],
				'meta' => [ 'detail' => 'Invalid Application [CAC#027]'],
			], 400);
		}

		// Auth Database Connection
		$cfg = \OpenTHC\Config::get('database/auth');
		if (empty($cfg)) {
			return $RES->withJSON([
				'meta' => [ 'detail' => 'Fatal Database Error [CAC#024]'],
				'data' => [],
			], 500);
		}

		$dbc_auth = new SQL(sprintf('pgsql:host=%s;dbname=%s', $cfg['hostname'], $cfg['database']), $cfg['username'], $cfg['password']);

		// Lookup Program
		$sql = 'SELECT * FROM auth_service WHERE code = ?';
		$arg = array($_GET['client_id']);
		$App = $dbc_auth->fetchRow($sql, $arg);
		if (empty($App['id'])) {
			return $RES->withJSON([
				'data' => null,
				'meta' => [ 'detail' => 'Invalid Application [CAC#034]'],
			], 400);
		}

		// Only Live Applications
		if (200 != $App['stat']) {
			return $RES->withJSON([
				'data' => null,
				'meta' => [ 'detail' => 'Invalid Application [CAC#042]'],
			], 400);
		}

		// With Live Flag
		if (($App['flag'] & 0x00000001) == 0) {
			return $RES->withJSON([
				'data' => null,
				'meta' => [ 'detail' => 'Invalid Application [CAC#039]'],
			], 400);
		}

		// Decrypt passed in data with the App Secret
		$tmp_auth = _decrypt($_GET['_'], $App['hash']);
		if (empty($tmp_auth)) {
			return $RES->withJSON([
				'data' => null,
				'meta' => [ 'detail' => 'Invalid Parameters [CAC#051]'],
			], 400);
		}

		$tmp_auth = json_decode($tmp_auth, true);
		if (empty($tmp_auth)) {
			return $RES->withJSON([
				'data' => null,
				'meta' => [ 'detail' => 'Invalid Parameters [CAC#056]'],
			], 400);
		}

		$tmp_auth['company']['id'] = strtoupper($tmp_auth['company']['id']);
		$tmp_auth['license']['id'] = strtoupper($tmp_auth['license']['id']);

		//var_dump($tmp_auth);
		//var_dump($_SESSION);

		// Lookup Auth_Company
		try {
			$this->_Company_Auth = $dbc_auth->fetchRow('SELECT * FROM auth_company WHERE id = :c0', [
				':c0' => $tmp_auth['company']['id']
			]);
		} catch (\Exception $e) {
			// Ignore
		}

		// Legacy Search
		try {
			$this->_Company_Auth = $dbc_auth->fetchRow('SELECT * FROM auth_company WHERE ulid = :c0', [
				':c0' => $tmp_auth['company']['id']
			]);
			$this->_Company_Auth['id'] = $this->_Company_Auth['ulid'];
			unset($this->_Company_Auth['ulid']);
		} catch (\Exception $e) {
			// Ignore
		}

		// Lookup Auth_Contact
		$sql = 'SELECT * FROM auth_contact WHERE username = ?';
		$arg = array($tmp_auth['contact']['email']);
		$this->_Contact_Auth = $dbc_auth->fetchRow($sql, $arg);

		// Main Database Connection
		$cfg = \OpenTHC\Config::get('database/main');
		if (empty($cfg)) {
			return $RES->withJSON([
				'meta' => [ 'detail' => 'Fatal Database Error [CAC#125]'],
				'data' => [],
			], 500);
		}

		$dbc_main = new SQL(sprintf('pgsql:host=%s;dbname=%s', $cfg['hostname'], $cfg['database']), $cfg['username'], $cfg['password']);

		// Lookup Company
		$sql = 'SELECT * FROM company WHERE id = :c0';
		$res = $dbc_main->fetchRow($sql, [ ':c0' => $this->_Company_Auth['id'] ]);
		if (empty($res['id'])) {
			return $RES->withJSON([
				'data' => null,
				'meta' => [ 'detail' => sprintf('Invalid Company "%s" [CAC#067]', $this->_Company_Auth['id']) ],
			], 400);
		}
		$Company = $res;

		// Lookup License
		$sql = 'SELECT * FROM license WHERE company_id = ? AND id = ?';
		$arg = array($Company['id'], $tmp_auth['license']['id']);
		$License = $dbc_main->fetchRow($sql, $arg);
		if (empty($License['id'])) {
			return $RES->withJSON([
				'data' => null,
				'meta' => [ 'detail' => sprintf('Invalid License "%s" [CAC#076]', $tmp_auth['license']['id']) ],
			], 400);
		}

		// Lookup Contact
		$x = $tmp_auth['contact']['email'];
		$x = strtolower(trim($x));
		if (!filter_var($x, FILTER_VALIDATE_EMAIL)) {
			_exit_text('Invalid Contact [CAC#084]', 400);
		}
		$tmp_auth['contact']['email'] = $x;

		$sql = 'SELECT * FROM contact WHERE company_id = ? AND email = ?';
		$arg = array($Company['id'], $tmp_auth['contact']['email']);
		$this->_Contact_Base = $dbc_main->fetchRow($sql, $arg);
		if (empty($this->_Contact_Base['id'])) {
			$this->_Contact_Base = array(
				'id' => _ulid(),
				'company_id' => $Company['id'],
				'email' => $tmp_auth['contact']['email']
			);
			$this->_Contact_Base['id'] = $dbc->insert('contact', $this->_Contact_Base);
		}

		// Validate Contact_Auth <=> Contact_Base
		if (empty($this->_Contact_Auth['id'])) {
			// $this->_Contact_Auth = array(
			// 	'id' => $this->_Contact_Base['id'],
			// 	'contact_id' => $this->_Contact_Base['id'],
			// 	'company_id' => $Company['id'],
			// 	'username' => $tmp_auth['contact']['email'],
			// 	'password' => sha1($_GET['_']),
			// );
			// $this->_Contact_Auth['id'] = $dbc->insert('auth_contact', $this->_Contact_Auth);
		}

		// if ($this->_Contact_Auth['company_id'] != $Company['id']) {
		// 	_exit_text([
		// 		'this' => $this->_Contact_Auth,
		// 		'Company' => $Company,
		// 	]);
		// 	_exit_text('Please Contact Support [CAC#192]');
		// }

		if ($this->_Contact_Auth['ulid'] != $this->_Contact_Base['id']) {
			//_exit_text([
			//	'CA' => $this->_Contact_Auth,
			//	'CB' => $this->_Contact_Base
			//]);
			_exit_text('Please Contact Support [CAC#137]', 409);
		}

		// Primary Objects
		$_SESSION['Contact'] = $this->_Contact_Base;
		$_SESSION['Company'] = $Company;
		$_SESSION['License'] = $License;

		// Canon
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
