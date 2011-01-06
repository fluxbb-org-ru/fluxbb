<?php
/*
 * ReCaptcha wrapper 
 * (c) 1010 artoodetoo
 * Based on Google ReCaptcha (without mailhide function)
 *-----------------------------------------------------------------------------
 * This is a PHP library that handles calling reCAPTCHA.
 *    - Documentation and latest version
 *          http://recaptcha.net/plugins/php/
 *    - Get a reCAPTCHA API Key
 *          https://www.google.com/recaptcha/admin/create
 *    - Discussion group
 *          http://groups.google.com/group/recaptcha
 *
 * Copyright (c) 2007 reCAPTCHA -- http://recaptcha.net
 * AUTHORS:
 *   Mike Crawford
 *   Ben Maurer
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in
 * all copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
 * THE SOFTWARE.
 */

/**
 * The reCAPTCHA server URL's
 */

class Recaptcha
{
	protected
		// Set by constructor
		$publicKey  = '',
		$privateKey = '';

    public function __construct(array $params = NULL)
    {
        if (is_array($params)) {
	    	foreach ($params as $name => $value) {
	    		$this->$name = $value;
			}
		}
    }

	const
		API_SERVER        = 'http://www.google.com/recaptcha/api',
		API_SECURE_SERVER = 'https://www.google.com/recaptcha/api',
		VERIFY_SERVER     = 'www.google.com';

	public
        $error;

	/**
	 * Encodes the given data into a query string format
	 * @param $data - array of string elements to be encoded
	 * @return string - encoded request
	 */
	private function _qsencode($data)
	{
        $req = '';
        foreach ( $data as $key => $value ) {
            $req .= $key . '=' . urlencode( stripslashes($value) ) . '&';
		}

        // Cut the last '&'
        $req = substr($req, 0, strlen($req) - 1);
        return $req;
	}



	/**
	 * Submits an HTTP POST to a reCAPTCHA server
	 * @param string $host
	 * @param string $path
	 * @param array $data
	 * @param int port
	 * @return array response
	 */
	private function _httpPost($host, $path, $data, $port = 80)
	{

        $req = $this->_qsencode ($data);

        $http_request  = "POST {$path} HTTP/1.0\r\n";
        $http_request .= "Host: {$host}\r\n";
        $http_request .= "Content-Type: application/x-www-form-urlencoded;\r\n";
        $http_request .= "Content-Length: " . strlen($req) . "\r\n";
        $http_request .= "User-Agent: reCAPTCHA/PHP\r\n";
        $http_request .= "\r\n";
        $http_request .= $req;

        $response = '';
        if (false == ( $fs = @fsockopen($host, $port, $errno, $errstr, 10) )) {
            die ('Could not open socket');
        }

        fwrite($fs, $http_request);

        while (!feof($fs)) {
            $response .= fgets($fs, 1160); // One TCP-IP packet
		}
        fclose($fs);
        $response = explode("\r\n\r\n", $response, 2);

        return $response;
	}



    /**
     * Gets the challenge HTML (javascript and non-javascript version).
     * This is called from the browser, and the resulting reCAPTCHA HTML widget
     * is embedded within the HTML form it was called from.
     * @param string $error The error given by reCAPTCHA (optional, default is null)
     * @param boolean $use_ssl Should the request be made over ssl? (optional, default is false)

     * @return string - The HTML to be embedded in the user's form.
     */
    function getHtml($error = null, $use_ssl = false)
    {
    	$pubkey = $this->publicKey;

		if ($pubkey == null || $pubkey == '') {
			die ('To use reCAPTCHA you must get an API key from <a href="https://www.google.com/recaptcha/admin/create">https://www.google.com/recaptcha/admin/create</a>');
		}
	
    	if ($use_ssl) {
			$server = self::API_SECURE_SERVER;
		} else {
			$server = self::API_SERVER;
		}

		$errorpart = '';
		if ($error) {
			$errorpart = '&amp;error=' . $error;
		}
		return '<script type="text/javascript" src="'. $server . '/challenge?k=' . $pubkey . $errorpart . '"></script>

    	<noscript>
      		<iframe src="'. $server . '/noscript?k=' . $pubkey . $errorpart . '" height="300" width="500" frameborder="0"></iframe><br/>
      		<textarea name="recaptcha_challenge_field" rows="3" cols="40"></textarea>
      		<input type="hidden" name="recaptcha_response_field" value="manual_challenge"/>
    	</noscript>';
    }




	/**
	  * Calls an HTTP POST function to verify if the user's guess was correct
	  * @param string $challenge
	  * @param string $response
	  * @param array $extra_params an array of extra variables to post to the server
	  * @return boolean
	  */
	public function checkAnswer($challenge, $response, $extra_params = array())
	{
		$privkey = $this->privateKey; 
		$remoteip = $_SERVER['REMOTE_ADDR']; 

		if ($privkey == null || $privkey == '') {
			die('To use reCAPTCHA you must get an API key from <a href="https://www.google.com/recaptcha/admin/create">https://www.google.com/recaptcha/admin/create</a>');
		}

		if ($remoteip == null || $remoteip == '') {
			die('For security reasons, you must pass the remote ip to reCAPTCHA');
		}

	
	
        //discard spam submissions
        if ($challenge == null || strlen($challenge) == 0 || $response == null || strlen($response) == 0) {
            $this->error = 'incorrect-captcha-sol';
            return false;
        }

        $response = $this->_httpPost(self::VERIFY_SERVER, '/recaptcha/api/verify',
                          array(
                                 'privatekey' => $privkey,
                                 'remoteip'   => $remoteip,
                                 'challenge'  => $challenge,
                                 'response'   => $response
                                 ) + $extra_params
                          );

        $answers = explode("\n", $response [1]);

        if (trim ($answers[0]) == 'true') {
            return true;
        }

        $this->error = $answers[1];
        return false;

	}

}