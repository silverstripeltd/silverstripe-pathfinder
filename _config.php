<?php

use CodeCraft\Pathfinder\Model\Pathfinder;
use SilverStripe\View\Parsers\ShortcodeParser;

ShortcodeParser::get('default')
    ->register('reset_link', [Pathfinder::class, 'reset_link']);
