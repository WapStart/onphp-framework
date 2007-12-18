<?php
/*****************************************************************************
 *   Copyright (C) 2006-2007, onPHP's MetaConfiguration Builder.             *
 *   Generated by onPHP-0.9.300 at 2007-05-15 15:32:37                       *
 *   This file is autogenerated - do not edit.                               *
 *****************************************************************************/
/* $Id$ */

	//FIXME: ComplexBuilderDAO is not present in trunk!!!
	abstract class AutoRegionDAO extends ComplexBuilderDAO
	{
		public function getTable()
		{
			return 'region';
		}
		
		public function getObjectName()
		{
			return 'Region';
		}
		
		public function getSequence()
		{
			return 'region_id';
		}
		
		public function uncacheLists()
		{
			StatisticVisitor::dao()->uncacheLists();
			StatisticQuery::dao()->uncacheLists();
			
			return parent::uncacheLists();
		}
		
		/**
		 * @return InsertOrUpdateQuery
		**/
		public function setQueryFields(InsertOrUpdateQuery $query, Region $region)
		{
			return
				$query->
					set('id', $region->getId())->
					set('name', $region->getName())->
					set('country', $region->getCountry())->
					set(
						'parent_id',
						$region->getParent()
							? $region->getParent()->getId()
							: null
					);
		}
		
		/**
		 * @return Region
		**/
		protected function makeSelf(&$array, $prefix = null)
		{
			$region = new Region();
			
			$region->
				setId($array[$prefix.'id'])->
				setName($array[$prefix.'name'])->
				setCountry(
					isset($array[$prefix.'country'])
						? $array[$prefix.'country']
						: null
				);
			
			return $region;
		}
		
		/**
		 * @return Region
		**/
		protected function makeCascade(/* Region */ $region, &$array, $prefix = null)
		{
			if (isset($array[$prefix.'parent_id'])) {
				$region->setParent(
					Region::dao()->getById($array[$prefix.'parent_id'])
				);
			}
			
			return $region;
		}
		
		/**
		 * @return Region
		**/
		protected function makeJoiners(/* Region */ $region, &$array, $prefix = null)
		{
			// forcing cascade strategy
			if (isset($array[$prefix.'parent_id'])) {
				$region->setParent(
					Region::dao()->getById($array[$prefix.'parent_id'])
				);
			}
			
			return $region;
		}
	}
?>