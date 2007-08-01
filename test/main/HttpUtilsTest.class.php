<?php
/***************************************************************************
 *   Copyright (C) 2007 by Anton E. Lebedevich                             *
 *                                                                         *
 *   This program is free software; you can redistribute it and/or modify  *
 *   it under the terms of the GNU Lesser General Public License as        *
 *   published by the Free Software Foundation; either version 3 of the    *
 *   License, or (at your option) any later version.                       *
 *                                                                         *
 ***************************************************************************/
/* $Id$ */

	final class HttpUtilsTest extends UnitTestCase
	{
		public function testCurlGet()
		{
			$request = HttpRequest::create()->
				setUri(
					GenericUri::parse('http://onphp.org/')
				)->
				setMethod(HttpMethod::get());
			
			$response = CurlHttpClient::create()->
				setTimeout(3)->
				send($request);
				
			$this->assertEqual(
				$response->getStatus()->getId(), 
				HttpStatus::CODE_200
			);
			
			$this->assertPattern(
				'/quite official site/',
				$response->getBody()
			);
		}
	}
?>