Prank v0.75 - Common Sense PHP Framework
========================================

Prank is a YAPF - Yet Another [PHP][] Framework

Prank tries to utilize the common sense approach to web application design.
It's in alpha stage, and is only fit for experimentation.

Prank is build around [PHP5.4][] (which is required) and is tested on
Apache 2.2 and MySQL 5.0.

[PHP]:    http://php.net/                        "PHP"
[PHP5.4]: http://php.net/migration54.changes.php "What has changed in PHP 5.4"

Philosophy
----------

Prank’s design supports the development of web applications more advanced than
simple dynamic sites. Many of used concepts and solutions are far from being
efficient (although I try to make them as scalable as possible) – they are
meant to make the prototyping as fast as possible, and heighten programmer
enjoyment.

If you want your PHP to scale and use as few resources as possible, don’t use a
framework.

Prank is also a highly opinionated software - I'm not trying to please
everybody, or even please myself in full. In all events, Prank is meant to take
the most sane and common-sense way of doing things.

Design Goals
------------

*  Code should be *nice*. This framework is not intended for anybody enjoying
   large XML files and extremely long method calls.

*  Object Oriented design as basis of functionality, functions as syntax sugar
   and helpers for common tasks. There's nothing wrong with sugar.

*  Clever use of Lambda/Closures and smart static methods when applicable.

Usage
=====

This is an ongoing experiment, and it is in continuous alpha stage, so use at
your own risk. There's no use of writing a proper usage document at the moment,
as things change frequently around here. However, Most of the code is pretty
self-explanatory. Take look at the provided /app/ directory, to see how to
create your own test-apps. If you have comments or suggestions, contact me
through [brego][].

Enjoy ;)

[brego]: mailto:brego.dk@gmail.com