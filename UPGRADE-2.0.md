UPGRADE FROM 1.x to 2.0
=======================

Configuration
-------------

Before:

```yml
gpslab_domain_event:
    # Event bus service
    # Support 'listener_locator', 'queue' or a custom service
    # As a default used 'listener_locator'
    bus: 'listener_locator'

    # Event queue service
    # Support 'memory', 'memory_unique' or a custom service
    # As a default used 'memory_unique'
    queue: 'memory_unique'

    # Event listener locator
    # Support 'voter', 'named_event' or custom service
    # As a default used 'named_event'
    locator: 'named_event'

    # Evant name resolver used in 'named_event' event listener locator
    # Support 'event_class', 'event_class_last_part', 'named_event' or a custom service
    # As a default used 'event_class'
    name_resolver: 'event_class'
```

After:

```yml
gpslab_domain_event:
    # Event bus service
    # Support 'listener_located', 'queue' or a custom service
    # As a default used 'listener_located'
    bus: 'listener_located'

    # Event queue service
    # Support 'pull_memory', 'subscribe_executing' or a custom service
    # As a default used 'pull_memory'
    queue: 'pull_memory'

    # Event listener locator
    # Support 'symfony', 'container', 'direct_binding' or custom service
    # As a default used 'symfony'
    locator: 'symfony'

    # Publish domain events post a Doctrine flush event
    # As a default used 'false'
    publish_on_flush: true
```

Publish domain events
---------------------

* Publish domain events post a Doctrine flush event.

Removed locators
----------------

* Removed `domain_event.locator.voter` service.
* Removed `domain_event.locator.named_event` service.
* Ignore tag `domain_event.locator.voter` for `VoterLocator`.
* Ignore tag `domain_event.locator.named_event` for `NamedEventLocator`.

Created locators
----------------

* Created `domain_event.locator.symfony` service of
   `GpsLab\Domain\Event\Listener\Locator\SymfonyContainerEventListenerLocator` locator.
* Created `domain_event.locator.container` service of
   `GpsLab\Domain\Event\Listener\Locator\ContainerEventListenerLocator` locator.
* Created `domain_event.locator.direct_binding` service of
   `GpsLab\Domain\Event\Listener\Locator\DirectBindingEventListenerLocator` locator.

Removed name resolvers
----------------------

* Removed `domain_event.name_resolver.event_class_last_part` name resolver service.
* Removed `domain_event.name_resolver.event_class` name resolver service.
* Removed `domain_event.name_resolver.named_event` name resolver service.

Removed queue
-------------

* Removed `domain_event.queue.memory` queue service.
* Removed `domain_event.queue.memory_unique` queue service.

Created queue
-------------

* Created `domain_event.queue.pull_memory` service of `GpsLab\Domain\Event\Queue\Pull\MemoryPullEventQueue` queue.
* Created `domain_event.queue.subscribe_executing` service of
   `GpsLab\Domain\Event\Queue\Subscribe\ExecutingSubscribeEventQueue` queue.

Changed event bus
-----------------

* Changed class for `domain_event.bus.listener_locator`.

   Before used `GpsLab\Domain\Event\Bus\Bus` class.

   After used `GpsLab\Domain\Event\Bus\ListenerLocatedEventBus` class.

* Service `domain_event.bus.queue` not use a `domain_event.bus.listener_locator` service.
