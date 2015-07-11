
```php
$r = new Runner('redis-cli');

try {
    $r->send("GET test\n");
    print "Got line: " . $r->readLine() . "\n";
    $r->send("SET test 1\nGet test\n");
    print "Got line: " . $r->readLine() . "\n";
    print "Got line: " . $r->readLine() . "\n";
    print "Got line: " . $r->readLineIfAny() . "\n";

    $r->send("fds\n");
    print "Got stderr line: " . $r->readStderrLineIfAny() . "\n";
    $code = $r->close();
} catch (Exception $e) {
}

print "Got return value: " . $r->getReturnValue() . $r->readStderrLineIfAny() . "\n";
```