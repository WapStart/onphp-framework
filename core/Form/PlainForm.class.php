<?php
/****************************************************************************
 *   Copyright (C) 2005-2007 by Konstantin V. Arkhipov, Anton E. Lebedevich *
 *                                                                          *
 *   This program is free software; you can redistribute it and/or modify   *
 *   it under the terms of the GNU General Public License as published by   *
 *   the Free Software Foundation; either version 2 of the License, or      *
 *   (at your option) any later version.                                    *
 *                                                                          *
 ****************************************************************************/
/* $Id$ */

	/**
	 * Common Primitive-handling.
	 * 
	 * @ingroup Form
	**/
	abstract class PlainForm
	{
		protected $aliases		= array();
		protected $primitives	= array();
		
		/**
		 * @return PlainForm
		**/
		public function clean()
		{
			foreach ($this->primitives as $prm)
				$prm->clean();
			
			return $this;
		}
		
		/**
		 * @throws MissingElementException
		 * @return PlainForm
		**/
		public function addAlias($primitiveName, $alias)
		{
			if (!isset($this->primitives[$primitiveName]))
				throw new MissingElementException(
					"{$primitiveName} does not exist"
				);

			$this->aliases[$alias] = $primitiveName;
			
			return $this;
		}
		
		public function primitiveExists($name)
		{
			return
				(
					isset($this->primitives[$name])
					|| isset($this->aliases[$name])
				);
		}
		
		/**
		 * @throws WrongArgumentException
		 * @return PlainForm
		**/
		public function add(BasePrimitive $prm, $alias = null)
		{
			$name = $prm->getName();
			
			Assert::isFalse(
				isset($this->primitives[$name]),
				'i am already exists!'
			);

			$this->primitives[$name] = $prm;
			
			if ($alias)
				$this->addAlias($name, $alias);
			
			return $this;
		}
		
		/**
		 * @throws MissingElementException
		 * @return PlainForm
		**/
		public function drop($name)
		{
			if (!isset($this->primitives[$name]))
				throw new MissingElementException(
					"can not drop inexistent primitive '{$name}'"
				);
			
			unset($this->primitives[$name]);
			
			return $this;
		}

		/**
		 * @throws MissingElementException
		 * @return BasePrimitive
		**/
		public function &get($name)
		{
			if (isset($this->aliases[$name], $this->primitives[$this->aliases[$name]]))
				return $this->primitives[$this->aliases[$name]];
			elseif (isset($this->primitives[$name]))
				return $this->primitives[$name];

			throw new MissingElementException("knows nothing about '{$name}'");
		}
		
		public function getValue($name)
		{
			return $this->get($name)->getValue();
		}
		
		public function getRawValue($name)
		{
			return $this->get($name)->getRawValue();
		}
		
		public function getActualValue($name)
		{
			return $this->get($name)->getActualValue();
		}

		public function getDisplayValue($name)
		{
			$primitive = $this->get($name);
			
			if ($primitive instanceof FiltrablePrimitive) 
				return $primitive->getDisplayValue();
			else
				return $primitive->getActualValue();		
		}

		public function getChoiceValue($name)
		{
			$prm	= $this->get($name);
			
			Assert::isTrue($prm instanceof ListedPrimitive);
			
			$list	= $prm->getList();
			$value	= $prm->getValue();
			
			if ($value instanceof Identifiable)
				$value = $value->getId();

			if ($value !== null)
				return $list[$value];

			return null;
		}
		
		public function getActualChoiceValue($name)
		{
			$prm	= $this->get($name);
			
			Assert::isTrue($prm instanceof ListedPrimitive);
			
			$list	= $prm->getList();
			$value	= $prm->getActualValue();
			$default= $prm->getDefault();
			
			if ($value instanceof Identifiable) {
				$value = $value->getId();
				
				if ($default)
					$default = $default->getId();
			}
			
			if ($value !== null && isset($list[$value]))
				return $list[$value];
			elseif (isset($list[$default]))
				return $list[$default];

			return null;
		}

		public function getPrimitiveNames()
		{
			return array_keys($this->primitives);
		}
		
		public function getPrimitiveList()
		{
			return $this->primitives;
		}
	}
?>