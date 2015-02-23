<?php defined('BASEPATH') OR exit('No direct script access allowed');

	/**
	 * Copyright (C) 2015  Nir Simionovich
	 *
	 * This library is free software; you can redistribute it and/or
	 * modify it under the terms of the GNU Lesser General Public
	 * License as published by the Free Software Foundation; either
	 * version 2.1 of the License, or (at your option) any later version.
	 *
	 * This library is distributed in the hope that it will be useful,
	 * but WITHOUT ANY WARRANTY; without even the implied warranty of
	 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the GNU
	 * Lesser General Public License for more details.
	 *
	 * You should have received a copy of the GNU Lesser General Public
	 * License along with this library; if not, write to the Free Software
	 * Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301  USA
	 * Also add information on how to contact you by electronic and paper mail.
	 *
	 * Greenfield Technologies Ltd., hereby disclaims all copyright interest in
	 * the library `logstash-http-wrapper' (a GELF Format HTTP Wrapper)
	 * written by Nir Simionovich and its respective list of contributors.
	 */

	require APPPATH . '/libraries/REST_Controller.php';

	class Gelf extends REST_Controller
	{

		private $logger;
		public $requestInput;

		public function __construct()
		{
			parent::__construct();

			try {

				$this->load->library('Lib_stashLogger');
				$this->logger = new Lib_stashLogger(GELF_HOST, GELF_PORT, GELF_FACILITY);

			} catch (Exception $e) {
				$response = array("error" => $e->getMessage());
				$this->response($response, $e->getCode()); // 200 being the HTTP response code
			}
		}

		private function json_validate($string)
		{
			if (is_string($string)) {
				@json_decode($string);

				return (json_last_error() === JSON_ERROR_NONE);
			}

			return FALSE;
		}

		public function test_get()
		{
			print_r($this->requestInput);
		}

		public function test_post()
		{
			print_r($this->requestInput);
		}

		public function publish_post()
		{
			try {

				$inputRaw = $this->post(NULL, TRUE);

				$jsonObject = json_decode(json_encode($inputRaw));

				/* Mandatory members */
				if ((!isset($jsonObject->shortmessage)) || (is_null($jsonObject->shortmessage)))
					throw new Exception("Missing mandatory member - shortmessage", 503);
				if ((!isset($jsonObject->fullmessage)) || (is_null($jsonObject->fullmessage)))
					throw new Exception("Missing mandatory member - fullmessage", 503);
				if ((!isset($jsonObject->loglevel)) || (is_null($jsonObject->loglevel)))
					throw new Exception("Missing mandatory member - loglevel", 503);

				/*
				LogLevel::EMERGENCY,    // 0
		        LogLevel::ALERT,        // 1
                LogLevel::CRITICAL,     // 2
		        LogLevel::ERROR,        // 3
                LogLevel::WARNING,      // 4
                LogLevel::NOTICE,       // 5
                LogLevel::INFO,         // 6
                LogLevel::DEBUG         // 7
				*/

				/* LogLevel translation */
				if (isset($jsonObject->loglevel)) {
					switch (strtoupper($jsonObject->loglevel)) {
						case "EMERGENCY":
						case "EMER":
						case "0":
						case 0:
							$LOG_LEVEL = \Psr\Log\LogLevel::EMERGENCY;
							break;
						case "ALERT":
						case "1":
						case 1:
							$LOG_LEVEL = \Psr\Log\LogLevel::ALERT;
							break;
						case "CRITICAL":
						case "CRIT":
						case "2":
						case 2:
							$LOG_LEVEL = \Psr\Log\LogLevel::CRITICAL;
							break;
						case "ERROR":
						case "ERR":
						case "3":
						case 3:
							$LOG_LEVEL = \Psr\Log\LogLevel::ERROR;
							break;
						case "WARNING":
						case "WARN":
						case "4":
						case 4:
							$LOG_LEVEL = \Psr\Log\LogLevel::WARNING;
							break;
						case "NOTICE":
						case "5":
						case 5:
							$LOG_LEVEL = \Psr\Log\LogLevel::NOTICE;
							break;
						case "INFO":
						case "6":
						case 6:
							$LOG_LEVEL = \Psr\Log\LogLevel::INFO;
							break;
						case "DEBUG":
						case "7":
						case 7:
							$LOG_LEVEL = \Psr\Log\LogLevel::DEBUG;
							break;
						default:
							$LOG_LEVEL = \Psr\Log\LogLevel::INFO;
							break;
					}
				}

				/* Optional members */
				$keys = array();
				if (isset($jsonObject->keys)) {
					foreach ($jsonObject->keys as $key => $value)
						$keys[$key] = $value;
				}

				$tags = array();
				if (isset($jsonObject->tags)) {
					foreach ($jsonObject->tags as $tag)
						$tags[] = $tag;
				}


				/*
				 * 				$this->stasher->sendMessage(LOG_INFO,
					"Attempting reservation for " . $postObject->reserve_to . " contact " . $postObject->contact_msisdn,
					"Attempting reservation for " . $postObject->reserve_to . " contact " . $postObject->contact_msisdn,
					'eventlog',
					array(
						'client_ip' => $_SERVER['REMOTE_ADDR'],
						'resevation' => $postObject->contact_msisdn
					),
					array('reservation', 'approval', strtoupper($response['condition'])),
					__FILE__,
					__LINE__
				);
				 */
			} catch (Exception $e) {
				$response = array("error" => $e->getMessage());
				$this->response($response, $e->getCode()); // 200 being the HTTP response code
			}
		}

	}
