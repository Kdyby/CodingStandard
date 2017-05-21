# Coding Standard

[![Build Status](https://travis-ci.org/Kdyby/CodingStandard.svg?branch=master)](https://travis-ci.org/Kdyby/CodingStandard)
[![Downloads this Month](https://img.shields.io/packagist/dm/kdyby/coding-standard.svg)](https://packagist.org/packages/kdyby/coding-standard)
[![Latest stable](https://img.shields.io/packagist/v/kdyby/coding-standard.svg)](https://packagist.org/packages/kdyby/coding-standard)
[![Coverage Status](https://coveralls.io/repos/github/Kdyby/CodingStandard/badge.svg?branch=master)](https://coveralls.io/github/Kdyby/CodingStandard?branch=master)

Variation of [Slevomat Coding Standard](https://github.com/slevomat/coding-standard) and [Consistence Coding Standard](https://github.com/consistence/coding-standard) of [PHPCodeSniffer](https://github.com/squizlabs/PHP_CodeSniffer) coding style rules.

## Installation

Install the [`kdyby/coding-standard`](https://packagist.org/packages/kdyby/coding-standard) with [Composer](https://getcomposer.org):

```sh
composer require --dev kdyby/coding-standard
```

## Usage

There are two standards defined, one for PHP 5.6 and one for PHP 7.1.

```bash
vendor/bin/phpcs --standard=vendor/kdyby/coding-standard/KdybyCodingStandard/ruleset-7.1.xml --encoding=utf-8 -sp src tests
```

## Customization

To allow customization, just include one of the standards (either `ruleset-5.6.xml` or `ruleset-7.1.xml`) in you project's `ruleset.xml`, depending on the minimal version supported.

```xml
<?xml version="1.0"?>
<ruleset name="My Project">
    <rule ref="vendor/kdyby/coding-standard/KdybyCodingStandard/ruleset-7.1.xml"/>

    <!-- custom settings -->
</ruleset>
```
