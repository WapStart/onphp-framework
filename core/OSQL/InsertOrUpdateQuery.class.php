<?php
/***************************************************************************
 *   Copyright (C) 2004-2005 by Konstantin V. Arkhipov                     *
 *   voxus@gentoo.org                                                      *
 *                                                                         *
 *   This program is free software; you can redistribute it and/or modify  *
 *   it under the terms of the GNU General Public License as published by  *
 *   the Free Software Foundation; either version 2 of the License, or     *
 *   (at your option) any later version.                                   *
 *                                                                         *
 ***************************************************************************/
/* $Id$ */

	abstract class InsertOrUpdateQuery
		extends QuerySkeleton
		implements SQLTableName
	{
		protected $table	= null;
		protected $fields	= array();
		
		abstract public function setTable($table);
		
		public function getTable()
		{
			return $this->table;
		}

		public function set($field, $value = null)
		{
			$this->fields[$field] = $value;
			
			return $this;
		}
		
		public function setId($field, /* Identifiable */ $object = null)
		{
			if ($object instanceof Identifiable)
				$this->set($field, $object->getId());
			elseif (is_null($object))
				$this->set($field, null);

			return $this;
		}
		
		public function setBoolean($field, $value = false)
		{
			if (true === $value)
				return $this->set($field, 'true');
			else
				return $this->set($field, 'false');
		}
		
		/**
		 * Adds values from associative array
		 * 
		 * @param	array	associative array('name' => 'value')
		 * @access	public
		 * @return	InsertQuery
		**/
		public function arraySet($fields)
		{
			Assert::isArray($fields);

			$this->fields = array_merge($this->fields, $fields);

			return $this;
		}
	}
?>