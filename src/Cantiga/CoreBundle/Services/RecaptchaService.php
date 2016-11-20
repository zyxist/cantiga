<?php
/*
 * This file is part of Cantiga Project. Copyright 2016 Cantiga contributors.
 *
 * Cantiga Project is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 3 of the License, or
 * (at your option) any later version.
 *
 * Cantiga Project is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with Foobar; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
 */
namespace Cantiga\CoreBundle\Services;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

/**
 * Integration with ReCAPTCHA.
 *
 * @author Tomasz Jędrzejewski
 */
class RecaptchaService
{
	const VERIFY_URL = 'https://www.google.com/recaptcha/api/siteverify';
	private $siteKey;
	private $secretKey;
	
	public function __construct($siteKey, $secretKey)
	{
		$this->siteKey = $siteKey;
		$this->secretKey = $secretKey;
	}
	
	public function generateHtmlCode()
	{
		return '<div class="g-recaptcha" data-sitekey="'.$this->siteKey.'"></div>';
	}
	
	public function verifyRecaptcha(Request $request)
	{
		if (empty($this->secretKey)) {
			return true;
		}
		
		$payload = $request->get('g-recaptcha-response', '');
		
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, self::VERIFY_URL);
		curl_setopt($ch, CURLOPT_POST, 1);
		curl_setopt($ch, CURLOPT_POSTFIELDS, 'secret='.$this->secretKey.'&response='.$payload.'&remoteip='.$_SERVER['REMOTE_ADDR']); 
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		
		$result = curl_exec($ch);
		$errno = curl_errno($ch);
		if ($errno !== 0) {
			throw new AccessDeniedException();
		}
		curl_close($ch);
		
		$answer = json_decode($result);
		if (isset($answer->success) && $answer->success == true) {
			return true;
		}
		return false;
	}
}
