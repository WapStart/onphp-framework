<?php
/***************************************************************************
 *   Copyright (C) 2006-2012 by Konstantin V. Arkhipov                     *
 *                                                                         *
 *   This program is free software; you can redistribute it and/or modify  *
 *   it under the terms of the GNU Lesser General Public License as        *
 *   published by the Free Software Foundation; either version 3 of the    *
 *   License, or (at your option) any later version.                       *
 *                                                                         *
 ***************************************************************************/

	/**
	 * Connector for PECL's Memcache extension by Antony Dovgal.
	 *
	 * @see http://tony2001.phpclub.net/
	 * @see http://pecl.php.net/package/memcache
	 *
	 * @ingroup Cache
	**/
	class PeclMemcached extends CachePeer
	{
		const DEFAULT_PORT						= 11211;
		const DEFAULT_HOST						= '127.0.0.1';
		const DEFAULT_TIMEOUT					= 1;

		const CAS_EMPTY							= 'I_AM_EMPTY';

		const CONNECTION_STRATEGY_COMMON		= 11;
		const CONNECTION_STRATEGY_PERSISTENT 	= 12;
		
		protected $host				= null;
		protected $port				= null;
		private $instance			= null;
		private $requestTimeout 	= null;
		private $connectTimeout 	= null;
		private $triedConnect		= false;
		private $connectionStrategy = self::CONNECTION_STRATEGY_COMMON;
		
		/**
		 * @return PeclMemcached
		**/
		public static function create(
			$host = self::DEFAULT_HOST,
			$port = self::DEFAULT_PORT,
			$connectTimeout = self::DEFAULT_TIMEOUT
		)
		{
			return new self($host, $port, $connectTimeout);
		}
		
		public function __construct(
			$host = self::DEFAULT_HOST,
			$port = self::DEFAULT_PORT,
			$connectTimeout = self::DEFAULT_TIMEOUT
		)
		{
			$this->host = $host;
			$this->port = $port;
			$this->connectTimeout = $connectTimeout;
		}
		
		public function __destruct()
		{
			if ($this->alive) {
				try {
					$this->instance->close();
				} catch (BaseException $e) {
					// shhhh.
				}
			}
		}

		public function getConnectionStrategy()
		{
			return $this->connectionStrategy;
		}

		public function setConnectionStrategy($strategy)
		{
			if (
				self::CONNECTION_STRATEGY_COMMON != $strategy
				&& self::CONNECTION_STRATEGY_PERSISTENT != $strategy
			)
				throw new InvalidArgumentException(
					sprintf('Undefined strategy "%s"', $strategy)
				);


			$this->connectionStrategy = $strategy;
		}
		
		public function isAlive()
		{
			$this->ensureTriedToConnect();
			
			return parent::isAlive();
		}
		
		/**
		 * @return PeclMemcached
		**/
		public function clean()
		{
			$this->ensureTriedToConnect();
			
			try {
				$this->instance->flush();
			} catch (BaseException $e) {
				$this->alive = false;
			}
			
			return parent::clean();
		}
		
		public function increment($key, $value)
		{
			$this->ensureTriedToConnect();
			
			try {
				return $this->instance->increment($key, $value);
			} catch (BaseException $e) {
				return null;
			}
		}
		
		public function decrement($key, $value)
		{
			$this->ensureTriedToConnect();
			
			try {
				return $this->instance->decrement($key, $value);
			} catch (BaseException $e) {
				return null;
			}
		}
		
		public function getList($indexes)
		{
			$this->ensureTriedToConnect();
			
			return
				($return = $this->get($indexes))
					? $return
					: array();
		}
		
		public function get($index)
		{
			return $this->doGet($index);
		}
		
		public function getc($index, &$cas)
		{
			return $this->doGet($index, $cas);
		}
		
		public function cas($key, $value, $expires = Cache::EXPIRES_MEDIUM, $cas)
		{
			$this->ensureTriedToConnect();
			
			try {
				return
					$this->instance->cas(
						$key, 
						$value,
						null,
						$expires,
						$cas
					);
				
			} catch (BaseException $e) {
				return $this->alive = false;
			}	
		}
		
		public function delete($index)
		{
			$this->ensureTriedToConnect();
			
			try {
				// second parameter required, wrt new memcached protocol:
				// delete key 0 (see process_delete_command in the memcached.c)
				// Warning: it is workaround!
				return $this->instance->delete($index, 0);
			} catch (BaseException $e) {
				return $this->alive = false;
			}
			
			Assert::isUnreachable();
		}
		
		public function append($key, $data)
		{
			$this->ensureTriedToConnect();
			
			try {
				return $this->instance->append($key, $data);
			} catch (BaseException $e) {
				return $this->alive = false;
			}
			
			Assert::isUnreachable();
		}
		
		/**
		 * @param float $requestTimeout time in seconds
		 * @return PeclMemcached
		 */
		public function setTimeout($requestTimeout)
		{
			$this->ensureTriedToConnect();
			$this->requestTimeout = $requestTimeout;
			$this->instance->setServerParams($this->host, $this->port, $requestTimeout);
			
			return $this;
		}
		
		/**
		 * @return float 
		 */
		public function getTimeout()
		{
			return $this->requestTimeout;
		}

		public function getHost()
		{
			return $this->host;
		}

		public function getPort()
		{
			return $this->port;
		}
		
		protected function ensureTriedToConnect()
		{
			if ($this->triedConnect) 
				return $this;
			
			$this->triedConnect = true;
			
			$this->connect();
			
			return $this;
		}
		
		protected function store(
			$action, $key, $value, $expires = Cache::EXPIRES_MEDIUM
		)
		{
			$this->ensureTriedToConnect();
			
			try {
				return
					$this->instance->$action(
						$key,
						$value,
						$this->compress
							? MEMCACHE_COMPRESSED
							: false,
						$expires
					);
			} catch (BaseException $e) {
				return $this->alive = false;
			}
			
			Assert::isUnreachable();
		}
		
		protected function connect()
		{
			$this->alive = true;
			$this->instance = new Memcache();

			if (
				self::CONNECTION_STRATEGY_PERSISTENT
				== $this->connectionStrategy
			) {
				try {
					$this->instance->pconnect(
						$this->host,
						$this->port,
						$this->connectTimeout
					);

					return true;
				} catch(BaseException $e) {
					// try to connect in a common way
				}
			}

			try {
				$this->instance->connect(
					$this->host,
					$this->port,
					$this->connectTimeout
				);
			} catch (BaseException $e) {
				$this->alive = false;
			}

			return $this->alive;
		}
		
		private function doGet($index, &$cas = self::CAS_EMPTY)
		{
			$this->ensureTriedToConnect();
			
			try {
				$cazz = null;
				
				$result =
					(
						($cas === self::CAS_EMPTY)
							? $this->instance->get($index)
							: $this->instance->get($index, null, $cazz)
					);
				
				$cas = $cazz;
				
				return $result;
				
			} catch (BaseException $e) {
				if(strpos($e->getMessage(), 'Invalid key') !== false)
					return null;
				
				$this->alive = false;
				
				return null;
			}
			
			Assert::isUnreachable();
		}
	}
?>
