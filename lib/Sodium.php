<?php
/**
 * OpenTHC Wrapper for libsodium
 *
 * SPDX-License-Identifier: MIT
 */

namespace OpenTHC;

class Sodium
{
	private $pk;

	private $sk;

	/**
	 *
	 */
	function __construct(string $pk, ?string $sk=null)
	{
		$this->setPublicKey($pk);
		if ( ! empty($sk)) {
			$this->setSecretKey($sk);
		}

		// Determine Things by strlen?

	}

	// function createEncryptPair($seed);
	// function createSignPair($seed);

	protected function setPublicKey(string $pk)
	{
		$this->pk = $this->b64decode($pk);
	}

	protected function setSecretKey(string $sk)
	{
		$this->sk = $this->b64decode($sk);
	}

	/**
	 * @param string $crypt the data to decrypt
	 * @param string $nonce the Random Bytes
	 * @param string $spkey senders public key
	 */
	function decrypt(string $crypt, string $nonce, string $spkey) : string
	{
		$crypt = $this->b64decode($crypt);

		// Get the Client Somethign?
		$rsk = $this->sk;
		$spk = $this->b64decode($spkey);
		$rskey = sodium_crypto_box_keypair_from_secretkey_and_publickey($rsk, $spk);
		$nonce = $this->b64decode($nonce);
		$plain = sodium_crypto_box_open($crypt, $nonce, $rskey);

		return $plain;

	}

	/**
	 * @param string $plain the Plain Text
	 * @param $rpk The Recipient Public Key
	 * @return string
	 */
	function encrypt($plain, ?string $rpk) : string
	{
		if (is_array($plain)) {
			$plain = json_encode($plain);
		} elseif (is_object($plain)) {
			$plain = json_encode($plain);
		}

		// Sender Secret Key
		$ssk = $this->sk;

		// Recipient Public Key
		$rpk = $this->b64decode($rpk);
		if (empty($rpk)) {
			$rpk = $this->pk;
		}

		$key = sodium_crypto_box_keypair_from_secretkey_and_publickey($ssk, $rpk);
		$nonce = random_bytes(SODIUM_CRYPTO_BOX_NONCEBYTES);
		$crypt = sodium_crypto_box($plain, $nonce, $key);

		$b64_nonce = $this->b64encode($nonce);
		$b64_crypt = $this->b64encode($crypt);

		$ret = sprintf('%s.%s', $b64_nonce, $b64_crypt);

		return $ret;
	}

	/**
	 *
	 */
	function sign($plain)
	{
		// Make sure the keys are the correct type
		// $signed = sodium_crypto_sign($this->sk);
	}

	/**
	 * base64 decode helper
	 */
	protected function b64decode($x)
	{
		if (preg_match('/[\w\-\+\=]+/', $x)) {
			return sodium_base642bin($x, SODIUM_BASE64_VARIANT_URLSAFE_NO_PADDING);
		}
		return $x;
	}

	/**
	 * base64 encode helper
	 */
	protected function b64encode($x)
	{
		return sodium_bin2base64($x, SODIUM_BASE64_VARIANT_URLSAFE_NO_PADDING);
	}

}
