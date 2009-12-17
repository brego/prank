Prank v0.30 - Common Sense PHP Framework
========================================

Prank is a common sense [PHP][] framework, which tries to utilize the common
sense approach to web application design. It is currently still in alpha stage,
and is only fit for experimentation.

Prank is build around many new features of [PHP5.3][] (which is required) and
is tested on Apache 2.2 and MySQL 5.0.

[PHP]:    http://php.net/                                    "PHP"
[PHP5.3]: http://www.php.net/archive/2008.php#id2008-08-01-1 "PHP5.3 alpha"

Philosophy
----------

Prank’s design is supposed to support the development of web applications more
advanced than simple dynamic sites. Many of used concepts and solutions are far
from being efficient (although I try to make them as scalable as possible)
– they are meant to fasten up the prototyping and programmer enjoyment. If you
want your PHP to scale and use as few resources as possible, don’t use a
framework.

Prank is also a highly opinionated software - I'm not trying to please
everybody, or even please myself in full. In all events, Prank is meant to take
the most sane and common-sense way of doing things. Which is, of course,
subjective.

Design Goals
------------

*  Code should be *nice*. This framework is not intended for anybody enjoying
   large XML files and extremely long method calls. We're writing *poetry*
   here, not trying to save the world.

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