<?php

use Cake\Event\EventManager;
use MonFields\Listeners\AddEventsListener;

EventManager::instance()->on(new AddEventsListener);
EventManager::instance()->on(new \App\Listeners\HiddenFieldsListener());