ReactFilesystem
===============

Filesystem WIP for [EIO](http://php.net/eio), keep in mind that this can be very unstable at times and is not stable by a long shot.

## Examples ##

`Adding examples here over time.`

## Reading files ##

```php
$filesystem->getContents('test.txt')->then(function($contents) {
});
```

Which is a convenience method for:

```php
$filesystem->open('test.txt')->then(function($stream) {
    return React\Stream\BufferedSink::createPromise($stream);
})->then(function($contents) {
});
```

Which in it's turn is a convenience method for:

```php
$filesystem->file('test.txt')->open('r')->then(function ($stream) use ($node) {
    $buffer = '';
    $deferred = new \React\Promise\Deferred();
    $stream->on('data', function ($data) use (&$buffer) {
        $buffer += $data;
    });
    $stream->on('end', function ($data) use ($stream, $deferred, &$buffer) {
        $stream->close();
        $deferred->resolve(&$buffer);
    });
    return $deferred->promise();
});
```

## Writing files ##

Open a file for writing (`w` flag) and write `abcde` to `test.txt` and close it. Create it (`c` flag) when it doesn't exists and truncate it (`t` flag) when it does.

```php
$filesystem->file('test.txt')->open('cwt')->then(function ($stream) {
    $stream->write('a');
    $stream->write('b');
    $stream->write('c');
    $stream->write('d');
    $stream->end('e');
});
```
