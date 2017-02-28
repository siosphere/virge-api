<?php

use Virge\Api\Controller\EntryController;
use Virge\Routes;

/**
 * 
 * @author Michael Kramer
 */

Routes::add('api', EntryController::class, 'entry');