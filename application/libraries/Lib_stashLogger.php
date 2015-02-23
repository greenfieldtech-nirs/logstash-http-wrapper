<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

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

    class Lib_stashLogger
    {

        private $CI;            //   -  instance of the Codeigniter core for using the getIPV4 helper

        // Logger Transports
        private $transport = NULL;
        private $publisher = NULL;
        private $message = NULL;
        private $logger = NULL;

        public function __construct($LOGSTASH_SERVER = NULL, $LOGSTASH_PORT = NULL, $LOGSTASH_FACILITY = "default")
        {
            try {

                $this->CI =& get_instance();

                if (is_null($LOGSTASH_SERVER))
                    throw new Exception("Event logging enabled, but no logstash server defined", 503);

                if (is_null($LOGSTASH_PORT))
                    throw new Exception("Event logging enabled, but no logstash port defined", 503);

                $this->transport = new Gelf\Transport\UdpTransport($LOGSTASH_SERVER, $LOGSTASH_PORT, Gelf\Transport\UdpTransport::CHUNK_SIZE_LAN);

                if (is_null($this->transport))
                    throw new Exception("Transport instance for logstash has failed, bailing out", 503);

                $this->publisher = new Gelf\Publisher();

                if (is_null($this->publisher))
                    throw new Exception("Published isntance for logstash has failed, bailing out", 503);

                $this->publisher->addTransport($this->transport);

                $this->message = new Gelf\Message();
                $this->logger = new Gelf\Logger($this->publisher, $LOGSTASH_FACILITY);

            } catch (Exception $e) {
                return array('result' => $e->getMessage(), 'code' => $e->getCode());
            }
        }

        public function sendMessage($level = LOG_INFO, $short = NULL, $full = NULL, $facility = "syslog", $additional = NULL, $tags = NULL, $file = NULL, $line = NULL)
        {
            try {

                switch ($level) {
                    case LOG_INFO:
                        $mLogStashLevel = Psr\Log\LogLevel::INFO;
                        break;
                    case LOG_WARNING:
                        $mLogStashLevel = Psr\Log\LogLevel::WARNING;
                        break;
                    case LOG_ERR:
                        $mLogStashLevel = Psr\Log\LogLevel::ERROR;
                        break;
                    case LOG_CRIT:
                        $mLogStashLevel = Psr\Log\LogLevel::CRITICAL;
                        break;
                    case LOG_ALERT:
                        $mLogStashLevel = Psr\Log\LogLevel::ALERT;
                        break;
                    case LOG_DEBUG:
                        $mLogStashLevel = Psr\Log\LogLevel::DEBUG;
                        break;
                    case LOG_EMERG:
                        $mLogStashLevel = Psr\Log\LogLevel::EMERGENCY;
                        break;
                    default:
                        $mLogStashLevel = Psr\Log\LogLevel::NOTICE;
                        break;
                }

                if (is_null($short))
                    throw new Exception("Short message can't be null", 503);

                if (is_null($full))
                    $full = $short;

                $this->message->setShortMessage($short)
                    ->setLevel($mLogStashLevel)
                    ->setFullMessage($full)
                    ->setFacility($facility);

                if ((!is_null($additional)) && (is_array($additional))) {
                    foreach ($additional as $additionalKey => $additionalValue) {
                        $this->message->setAdditional($additionalKey, $additionalValue);
                    }
                }

                if (is_array($tags)) {
                    $this->message->setAdditional('tags', json_encode($tags));
                } else if (!is_null($tags)) {
                    $this->message->setAdditional('tags', $tags);
                } else {
                    $this->message->setAdditional('tags', 'general');
                }

                if (!is_null($file))
                    $this->message->setFile($file);

                if (!is_null($line))
                    $this->message->setFile($line);

                $this->publisher->publish($this->message);

                return FALSE;

            } catch (Exception $e) {
                return array('result' => $e->getMessage(), 'code' => $e->getCode());
            }
        }

        public function eventLogger($short = NULL, $full = NULL, $tags = NULL)
        {
            try {

                if (is_null($short))
                    throw new Exception("Message can't be null", 503);
                if (is_null($full))
                    throw new Exception("Full can't be null", 503);

                $this->sendMessage(LOG_INFO, $short, $full, 'event-facility', NULL, $tags);

                return FALSE;

            } catch (Exception $e) {
                return array('result' => $e->getMessage(), 'code' => $e->getCode());
            }
        }

        public function alert($message = NULL)
        {
            try {

                if (is_null($message))
                    throw new Exception("Message can't be null", 503);

                $this->logger->alert($message);

                return FALSE;

            } catch (Exception $e) {
                return array('result' => $e->getMessage(), 'code' => $e->getCode());
            }
        }

        public function info($message = NULL)
        {
            try {

                if (is_null($message))
                    throw new Exception("Message can't be null", 503);

                $this->logger->info($message);

                return FALSE;

            } catch (Exception $e) {
                return array('result' => $e->getMessage(), 'code' => $e->getCode());
            }
        }

        public function error($message = NULL)
        {
            try {

                if (is_null($message))
                    throw new Exception("Message can't be null", 503);

                $this->logger->error($message);

                return FALSE;

            } catch (Exception $e) {
                return array('result' => $e->getMessage(), 'code' => $e->getCode());
            }
        }

        public function warn($message = NULL)
        {
            try {

                if (is_null($message))
                    throw new Exception("Message can't be null", 503);

                $this->logger->warning($message);

                return FALSE;

            } catch (Exception $e) {
                return array('result' => $e->getMessage(), 'code' => $e->getCode());
            }
        }

    }