<?php
/**
 * Set a Password
 * @todo move to auth/once/password or something?
 *
 * SPDX-License-Identifier: MIT
 */

namespace OpenTHC\Controller\Account;

use OpenTHC\CSRF;
use OpenTHC\Auth_Context_Ticket;

class Password extends \OpenTHC\Controller\Base
{
	/**
	 * HTTP GET handler
	 * @param \Slim\Http\Request $REQ
	 * @param \Slim\Http\Response $RES
	 * @param array $ARG
	 */
	function __invoke(\Slim\Http\Request $REQ, \Slim\Http\Response $RES, array $ARG)
	{
		$ARG = $this->parseArg();

		$data = $this->data;
		$data['Page']['title'] = 'Account :: Password Update';
		$data['auth_username'] = $ARG['contact']['username'];
		$data['CSRF'] = CSRF::getToken();

		if (!empty($_GET['e'])) {
			switch ($_GET['e']) {
			case 'CAP-047':
				$data['Page']['flash'] = 'Invalid password';
				break;
			case 'CAP-052':
				$data['Page']['flash'] = 'Invalid password';
				break;
			case 'CAP-057':
				$data['Page']['flash'] = 'Invalid password';
				break;
			case 'CAP-062':
				$data['Page']['flash'] = 'Passwords do not match';
				break;
			}
		}

		return $RES->write( $this->render('account/password.php', $data) );

	}

	/**
	 * HTTP POST handler
	 * @param \Slim\Http\Request $REQ
	 * @param \Slim\Http\Response $RES
	 * @param array $ARG
	 */
	function post(\Slim\Http\Request $REQ, \Slim\Http\Response $RES, array $ARG)
	{
		CSRF::verify($_POST['CSRF']);

		$ARG = $this->parseArg();

		// Set Their Password
		switch (strtolower($_POST['a'])) {
		case 'update':

			$p = $_POST['p0'];

			if (empty($p) || empty($_POST['p1'])) {
				return $RES->withRedirect('/account/password?' . http_build_query([
					'_' => $_GET['_'],
					'e' => 'CAP-047',
				]));
			}

			if (strlen($p) < 8) {
				return $RES->withRedirect('/account/password?' . http_build_query([
					'_' => $_GET['_'],
					'e' => 'CAP-052',
				]));
			}

			if (preg_match_all('/\w|\d/', $p) < 8) {
				return $RES->withRedirect('/account/password?' . http_build_query([
					'_' => $_GET['_'],
					'e' => 'CAP-057',
				]));
			}

			if ($p != $_POST['p1']) {
				return $RES->withRedirect('/account/password?' . http_build_query([
					'_' => $_GET['_'],
					'e' => 'CAP-062',
				]));
			}

			$dbc_auth = $this->_container->DBC_AUTH;

			$arg = [];
			$arg[':c0'] = $ARG['contact']['id'];
			$arg[':pw'] = password_hash($_POST['p0'], PASSWORD_DEFAULT);

			$sql = 'UPDATE auth_contact SET password = :pw WHERE id = :c0';
			$dbc_auth->query($sql, $arg);

			// Log It
			$dbc_auth->insert('log_event', [
				'contact_id' => $ARG['contact']['id'],
				'code' => 'Contact/Password/Update',
				'meta' => json_encode($_SESSION),
			]);

			$RES = $RES->withAttribute('Contact', [
				'id' => $ARG['contact']['id'],
				'username' => $ARG['contact']['username'],
				'password' => $arg[':pw'],
			]);

			return $RES->withRedirect('/auth/open?' . http_build_query([
				'e' => 'CAP-080',
				'service' => $ARG['service'],
			]));

			break;
		}
	}

	/**
	 */
	private function parseArg()
	{
		$ARG = [];

		if (!empty($_GET['_'])) {

			$act = new Auth_Context_Ticket($this->_container->DBC_AUTH, $_GET['_']);
			if (!empty($act['id'])) {
				$ARG = json_decode($act['meta'], true);
			}
		}

		switch ($ARG['intent']) {
		case 'account-create':
		case 'password-reset':
		case 'password-update':
			// OK
			break;
		default:
			__exit_text('Invalid Request [CAP-110]', 400);
		}

		return $ARG;
	}
}
