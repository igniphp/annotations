# Igni Annotations

## Introduction
Igni annotations is an attempt to provide meta data programing for php by extending docblock comments.
Syntax is compatible with latest [annotations rfc](https://wiki.php.net/rfc/annotations_v2).

The following annotations are not supported for various reasons:
 - `@Compiled` - as there is no compiling
 - `@SupressWarning` - There is no simple way to implement it in user-land
 - `@Repeatable` - all annotations are repeatable by default
 - `@Inherited` - same as `@SupressWarning`, there is no simple way to track php's inheritance tree in user-land
 

 

