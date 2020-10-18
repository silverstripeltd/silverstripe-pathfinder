# SilverStripe Pathfinder

## Introduction

This module enables CMS users to create a succession of questions and answers, which then suggests content to visitors using tags (taxonomy), acting as a pathfinder to pages on the site.

## Requirements

See the "require" section of [composer.json](https://github.com/codecraft/silverstripe-pathfinder/blob/master/composer.json)

Relies on [silverstripe/silverstripe-taxonomy](https://github.com/silverstripe/silverstripe-taxonomy)

## Features

- Create paths of questions and answers that, when completed, suggest content to the user
- Customise Pathfinder content:
    - Introduction wording
    - Results found wording
    - No Results found wording
    - Support wording (Displayed on results page)
- Control content suggestions by tagging Pages with Taxonomy Terms
- Answers can be single-choice or multi-choice
- Questions can be organised in Flows, making CMS usage easier and diversifying pathing logic
- Pathfinders can be created as a Page or added as an Extension to existing models
- Users' progress is preserved when navigating away from a partially completed Pathfinder
- Multiple and customisable progress storage types
- Shortcodes provided to help author Pathfinder content
- Can be customised with extensions

## Installation

```cli
composer require codecraft/silverstripe-pathfinder
```

## Configuration

### Progress Store

Pathfinder uses a `ProgressStore` to dynamically track the progress of a user. This is how the Pathfinder knows which path the user is on, based on their precession of answers.

The default ProgressStore is the `SessionProgressStore`, and stores progress in the user's PHP session.

To change the session store, update the `ProgressStore` injector configuration, to assign the `class` of the progress store you need:
```yaml
SilverStripe\Core\Injector\Injector:
  CodeCraft\Pathfinder\Model\Store\ProgressStore:
    class: CodeCraft\Pathfinder\Model\Store\SessionProgressStore
```

#### Available Progress Stores

- `CodeCraft\Pathfinder\Model\Store\SessionProgressStore` - **(Default)** Stores progress in the PHP session. __Expires when the user's session expires__
- `CodeCraft\Pathfinder\Model\Store\RequestVarProgressStore` - Stores progress in an encoded URL request variable. __Expires when the URL request variable is discarded.__
- `CodeCraft\Pathfinder\Model\Store\LocalStorageProgressStore` - Stores progress in Local storage. __Expires when local storage is cleared.__

#### Create a custom Progress Store

A custom progress store can be created by subclassing `CodeCraft\Pathfinder\Model\Store\ProgressStore` and modifying the `Injector` configuration.

Example subclass:

```php
<?php

use CodeCraft\Pathfinder\Model\Store\ProgressStore;

/**
 * My custom progress store
 */
class MyProgressStore extends ProgressStore {}
```

Example `Injector` configuration:

```yaml
SilverStripe\Core\Injector\Injector:
  CodeCraft\Pathfinder\Model\Store\ProgressStore:
    class: MyProgressStore
```


## Is this the same as [dnadesign/silverstripe-elemental-decisiontree](https://github.com/dnadesign/silverstripe-elemental-decisiontree)?

Main differences:

- Pathfinder can be used as a Page
- CMS users don't need to assign suggestions to each answer (Pathfinder uses a suggestion pattern of content tagged by taxonomy terms)

## Versioning
This library follows [Semver](http://semver.org/). According to Semver, you will be able to upgrade to any minor or patch version of this library without any breaking changes to the public API. Semver also requires that we clearly define the public API for this library.

All methods, with public visibility, are part of the public API. All other methods are not part of the public API. Where possible, we'll try to keep protected methods backwards-compatible in minor/patch versions, but if you're overriding methods then please test your work before upgrading.

## Reporting Issues
Please create an issue for any bugs you've found, or features you're missing.

## Credits

- Initial concept by [James Ford](https://www.linkedin.com/in/jamesrford/) and [Stephen Makrogianni](https://github.com/StephenMakrogianni)
