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
				'meta' => [ 'detail' => 'Invalid Application [CAC-027]'],
			], 400);
		}

		// Auth Database Connection
		$cfg = \OpenTHC\Config::get('database/auth');
		if (empty($cfg)) {
			return $RES->withJSON([
				'data' => [],
				'meta' => [ 'detail' => 'Fatal Database Error [CAC-024]'],
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
				'meta' => [ 'detail' => 'Invalid Service [CAC-034]'],
			], 400);
		}

		// Only Live Service
		if (200 != $App['stat']) {
			return $RES->withJSON([
				'data' => null,
				'meta' => [ 'detail' => 'Invalid Service [CAC-042]'],
			], 400);
		}

		// Decrypt passed in data with the App Secret
		$tmp_auth = _decrypt($_GET['_'], $App['hash']);
		if (empty($tmp_auth)) {
			return $RES->withJSON([
				'data' => null,
				'meta' => [ 'detail' => 'Invalid Parameters [CAC-051]'],
			], 400);
		}

		$tmp_auth = json_decode($tmp_auth, true);
		if (empty($tmp_auth)) {
			return $RES->withJSON([
				'data' => null,
				'meta' => [ 'detail' => 'Invalid Parameters [CAC-056]'],
			], 400);
		}

		// Cleanup Data
		$tmp_auth['company']['id'] = strtoupper($tmp_auth['company']['id']);
		$tmp_auth['license']['id'] = strtoupper($tmp_auth['license']['id']);
		$tmp_auth['contact']['id'] = strtoupper($tmp_auth['contact']['id']);
		$tmp_auth['contact']['email'] = strtolower(trim($tmp_auth['contact']['email']));
		if (!filter_var($tmp_auth['contact']['email'], FILTER_VALIDATE_EMAIL)) {
			return $RES->withJSON([
				'data' => null,
				'meta' => [ 'detail' => 'Invalid Contact [CAC-093]' ]
			], 400);
		}


		// Lookup Auth_Contact
		$sql = 'SELECT * FROM auth_contact WHERE id = :ct';
		$arg = [ ':ct' => $tmp_auth['contact']['id'] ];
		$this->_Contact_Auth = $dbc_auth->fetchRow($sql, $arg);

		$this->_Company_Auth = $dbc_auth->fetchRow('SELECT * FROM auth_company WHERE id = :c0', [
			':c0' => $tmp_auth['company']['id']
		]);

		if (empty($this->_Contact_Auth['id']) || empty($this->_Company_Auth['id'])) {
			return $RES->withJSON([
				'data' => null,
				'meta' => [ 'detail' => 'Invalid Company or Contact [CAC-109]' ]
			], 403);
		}

		// Main Database Connection
		$cfg = \OpenTHC\Config::get('database/main');
		if (empty($cfg)) {
			return $RES->withJSON([
				'meta' => [ 'detail' => 'Fatal Database Error [CAC-125]'],
				'data' => [],
			], 500);
		}

		$dbc_main = new SQL(sprintf('pgsql:host=%s;dbname=%s', $cfg['hostname'], $cfg['database']), $cfg['username'], $cfg['password']);

		// Lookup Main Company
		$sql = 'SELECT * FROM company WHERE id = :c0';
		$this->_Company_Base = $dbc_main->fetchRow($sql, [ ':c0' => $this->_Company_Auth['id'] ]);
		if (empty($this->_Company_Base['id'])) {
			return $RES->withJSON([
				'data' => null,
				'meta' => [ 'detail' => sprintf('Invalid Company "%s" [CAC-067]', $this->_Company_Auth['id']) ],
			], 400);
		}

		// Lookup License
		$sql = 'SELECT * FROM license WHERE company_id = ? AND id = ?';
		$arg = array($this->_Company_Auth['id'], $tmp_auth['license']['id']);
		$License = $dbc_main->fetchRow($sql, $arg);
		if (empty($License['id'])) {
			return $RES->withJSON([
				'data' => null,
				'meta' => [ 'detail' => sprintf('Invalid License "%s" [CAC-076]', $tmp_auth['license']['id']) ],
			], 400);
		}

		// Lookup Contact
		$sql = 'SELECT id, flag, email, phone FROM contact WHERE id = :ct0';
		$arg = [ ':ct0' => $this->_Contact_Auth['id'] ];
		$this->_Contact_Base = $dbc_main->fetchRow($sql, $arg);
		if (empty($this->_Contact_Base['id'])) {
			$this->_Contact_Base = [
				'id' => $tmp_auth['contact']['id'],
				'email' => $tmp_auth['contact']['email']
			];
			$this->_Contact_Base['id'] = $dbc_main->insert('contact', $this->_Contact_Base);
		}

		// Primary Objects
		$_SESSION['Contact'] = array_merge($this->_Contact_Base, $this->_Contact_Auth);
		$_SESSION['Company'] = array_merge($this->_Company_Base, $this->_Company_Auth);
		$_SESSION['License'] = $License;

		// Suggested CRE?
		if (!empty($tmp_auth['cre'])) {
			$_SESSION['cre'] = $tmp_auth['cre'];
		}

		$_SESSION['sql-hash'] = sha1(json_encode($tmp_auth['cre']));
		$_SESSION['tz'] = $_SESSION['Company']['tz'];

		$this->_connect_info = $tmp_auth;

		return $RES->withRedirect('/auth/back');

	}
}
