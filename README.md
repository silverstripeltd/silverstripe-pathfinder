# SilverStripe Pathfinder

## Introduction

This module enables CMS users to create a succession of questions and answers, which then suggests content to visitors using tags (taxonomy), acting as a pathfinder to pages on the site.

## Requirements

See the "require" section of [composer.json](https://github.com/codecraft/silverstripe-pathfinder/blob/master/composer.json)

Relies on [silverstripe/silverstripe-taxonomy](https://github.com/silverstripe/silverstripe-taxonomy)

## Features

- Create paths of questions and answers that, when completed, suggest content to the user
- Customise Pathfinder content:
    - Introduction
    - Results found
    - No Results found
    - Support (can be displayed alongside Pathfinder and/or suggested results)
- Control content suggestions by tagging Pages with Taxonomy Terms
- Answers can be single-choice or multi-choice
- Questions can be organised in Flows, making CMS usage easier and diversifying pathing logic
- Pathfinders can be created as a Page or added as an Extension to existing models
- Users' progress preserved when navigating away from a partially completed Pathfinder
- Shortcodes provided to help author Pathfinder content
- Can be extended to control suggestion results

## Installation

```cli
composer require codecraft/silverstripe-pathfinder
```

## Configuration

No configuration required.

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
