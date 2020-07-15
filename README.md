RSocket PHP
================

RSocket PHP SDK based on ReactPHP and RxPHP.

# Requirements

* PHP 7.4.x

# Examples

### RSocket Client

```php
Scheduler::setDefaultFactory(function () use ($loop) {
    return new Scheduler\EventLoopScheduler($loop);
});

$rsocketPromise = RSocketConnector::create($loop)->connect("tcp://127.0.0.1:42252");

$rsocketPromise->then(function (RSocket $rsocket) {
    $observablePayload = $rsocket->requestResponse(Payload::fromText("text/plain", "Ping"));
    $observablePayload->subscribe(
        function (Payload $x) {
            echo 'Result: ' . $x->getDataUtf8();
        }
    );
});
```

### RSocket Server

```php
Scheduler::setDefaultFactory(function () use ($loop) {
    return new Scheduler\EventLoopScheduler($loop);
});

$listenUri = "tcp://127.0.0.1:42252";
$socketAcceptor = CallableSocketAcceptor::handle(function ($setupPayload, $sendingRSocket) {
    return AbstractRSocket::requestResponseHandler(function ($payload) {
        return Observable::of(Payload::fromText("metadata", "PONG"));
    });
});
$server = RSocketServer::create($loop, $socketAcceptor)->bind($listenUri);
print("RSocket Server started on ${listenUri}");
```

# RSocket

- Operations
  - [x] REQUEST_FNF
  - [x] REQUEST_RESPONSE
  - [x] REQUEST_STREAM
  - [ ] REQUEST_CHANNEL
  - [x] METADATA_PUSH
- More Operations
  - [x] Error
  - [ ] Cancel
  - [x] Keepalive
- QoS
  - [ ] RequestN
  - [ ] Lease
- Transport
  - [x] TCP
  - [ ] Websocket
- High Level APIs
  - [x] Client
  - [x] Server
- Misc
  - [x] RxPHP

# References

* RSocket: https://rsocket.io
* ReactPHP: https://github.com/reactphp/reactphp
* ReactPHP Projects: https://github.com/reactphp/reactphp/wiki/Users
* RxPHP: https://github.com/ReactiveX/RxPHP
* New .phpstorm.meta.php features: https://blog.jetbrains.com/phpstorm/2019/02/new-phpstorm-meta-php-features/
* PHP Reactive Programming: https://www.packtpub.com/web-development/php-reactive-programming