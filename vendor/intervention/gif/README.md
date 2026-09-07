# Intervention GIF
## Native PHP GIF Encoder/Decoder

[![Latest Version](https://img.shields.io/packagist/v/intervention/gif.svg)](https://packagist.org/packages/intervention/gif)
![build](https://github.com/Intervention/gif/actions/workflows/build.yml/badge.svg)
[![Monthly Downloads](https://img.shields.io/packagist/dm/intervention/gif.svg)](https://packagist.org/packages/intervention/gif/stats)
[![Support me on Ko-fi](https://raw.githubusercontent.com/Intervention/gif/main/.github/images/support.svg)](https://ko-fi.com/interventionphp)

Intervention GIF is a PHP encoder and decoder for the GIF image format that
does not depend on any image processing extension.

Only the special `Splitter::class` class divides the data stream of an animated
GIF into individual `GDImage` objects for each frame and is therefore dependent
on the GD library.

The library is the main component of [Intervention
Image](https://github.com/Intervention/image) for processing animated GIF files
with the GD library, but also works independently.

## Installation

You can easily install this package using [Composer](https://getcomposer.org).
Just request the package with the following command:

```bash
composer require intervention/gif
```

## Code Examples

### Decoding

```php
use Intervention\Gif\Decoder;

// Decode filepath to Intervention\Gif\GifDataStream::class
$gif = Decoder::decode('images/animation.gif');

// Decoder can also handle binary content directly
$gif = Decoder::decode($contents);
```

### Encoding

Use the Builder class to create a new GIF image.

```php
use Intervention\Gif\Builder;

// create new gif canvas
$gif = Builder::canvas(width: 32, height: 32);

// add animation frames to canvas
$delay = .25; // delay in seconds after next frame is displayed
$left = 0; // position offset (left)
$top = 0; // position offset (top)

// add animation frames with optional delay in seconds
// and optional position offset for each frame
$gif->addFrame('images/frame01.gif', $delay, $left, $top);
$gif->addFrame('images/frame02.gif', $delay, $left);
$gif->addFrame('images/frame03.gif', $delay);
$gif->addFrame('images/frame04.gif');

// set loop count; 0 for infinite looping
$gif->setLoops(12);

// encode
$data = $gif->encode();
```


## Requirements

- PHP >= 8.1

## Development & Testing

With this package comes a Docker image to build a test suite and analysis
container. To build this container you have to have Docker installed on your
system. You can run all tests with this command.

```bash
docker-compose run --rm --build tests
```

Run the static analyzer on the code base.

```bash
docker-compose run --rm --build analysis
```

## Authors

This library is developed and maintained by [Oliver Vogel](https://intervention.io)

Thanks to the community of [contributors](https://github.com/Intervention/gif/graphs/contributors) who have helped to improve this project.

## License

Intervention GIF is licensed under the [MIT License](LICENSE).
