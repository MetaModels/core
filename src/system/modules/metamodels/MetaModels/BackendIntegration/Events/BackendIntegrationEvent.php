<?php

namespace MetaModels\BackendIntegration\Events;

use Symfony\Component\EventDispatcher\Event;

class BackendIntegrationEvent extends Event
{
	const NAME = 'metamodels.backend-integration';

	/**
	 * @var array
	 */
	protected $modules;

	public function addModule($value)
	{

	}

	public function getModule($name)
	{

	}
}
