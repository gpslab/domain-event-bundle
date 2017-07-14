[![Latest Stable Version](https://img.shields.io/packagist/v/gpslab/domain-event-bundle.svg?maxAge=3600&label=stable)](https://packagist.org/packages/gpslab/domain-event-bundle)
[![Total Downloads](https://img.shields.io/packagist/dt/gpslab/domain-event-bundle.svg?maxAge=3600)](https://packagist.org/packages/gpslab/domain-event-bundle)
[![Build Status](https://img.shields.io/travis/gpslab/domain-event-bundle.svg?maxAge=3600)](https://travis-ci.org/gpslab/domain-event-bundle)
[![Coverage Status](https://img.shields.io/coveralls/gpslab/domain-event-bundle.svg?maxAge=3600)](https://coveralls.io/github/gpslab/domain-event-bundle?branch=master)
[![Scrutinizer Code Quality](https://img.shields.io/scrutinizer/g/gpslab/domain-event-bundle.svg?maxAge=3600)](https://scrutinizer-ci.com/g/gpslab/domain-event-bundle/?branch=master)
[![SensioLabs Insight](https://img.shields.io/sensiolabs/i/3a2581f1-dec0-4f48-8133-b996cd9a62b5.svg?maxAge=3600&label=SLInsight)](https://insight.sensiolabs.com/projects/3a2581f1-dec0-4f48-8133-b996cd9a62b5)
[![StyleCI](https://styleci.io/repos/69584393/shield?branch=master)](https://styleci.io/repos/69584393)
[![License](https://img.shields.io/github/license/gpslab/domain-event-bundle.svg?maxAge=3600)](https://github.com/gpslab/domain-event-bundle)

Domain event bundle
===================

Bundle to create the domain layer of your **DDD** application.

This **Symfony** bundle is a wrapper for [gpslab/domain-event](https://github.com/gpslab/domain-event), look it for more details.

Installation
------------

Pretty simple with [Composer](http://packagist.org), run:

```sh
composer require gpslab/domain-event-bundle
```

Add GpsLabDomainEventBundle to your application kernel

```php
// app/AppKernel.php
public function registerBundles()
{
    return array(
        // ...
        new GpsLab\Bundle\DomainEvent\GpsLabDomainEventBundle(),
        // ...
    );
}
```

Configuration
-------------

Example configuration

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
```

Usage
-----

Create a domain event

```php
use GpsLab\Domain\Event\Event

class PurchaseOrderCreatedEvent implements Event
{
    private $customer_id;
    private $create_at;

    public function __construct(CustomerId $customer_id, \DateTimeImmutable $create_at)
    {
        $this->customer_id = $customer_id;
        $this->create_at = $create_at;
    }

    public function customerId()
    {
        return $this->customer_id;
    }

    public function createAt()
    {
        return $this->create_at;
    }
}
```

Raise your event

```php
use GpsLab\Domain\Event\Aggregator\AbstractAggregateEvents;

final class PurchaseOrder extends AbstractAggregateEvents
{
    private $customer_id;
    private $create_at;

    public function __construct(CustomerId $customer_id)
    {
        $this->customer_id = $customer_id;
        $this->create_at = new \DateTimeImmutable();

        $this->raise(new PurchaseOrderCreatedEvent($customer_id, $this->create_at));
    }
}
```

Create listener

```php
use GpsLab\Domain\Event\Event;

class SendEmailOnPurchaseOrderCreated
{
    private $mailer;

    public function __construct(\Swift_Mailer $mailer)
    {
        $this->mailer = $mailer;
    }

    public function onPurchaseOrderCreated(PurchaseOrderCreatedEvent $event)
    {
        $message = $this->mailer
            ->createMessage()
            ->setTo('recipient@example.com')
            ->setBody(sprintf(
                'Purchase order created at %s for customer #%s',
                $event->getCreateAt()->format('Y-m-d'),
                $event->getCustomer()->getId()
            ));

        $this->mailer->send($message);
    }
}
```

Register event listener

```yml
services:
    acme.domain.purchase_order.event.created.send_email_listener:
        class: SendEmailOnPurchaseOrderCreated
        arguments: [ '@mailer' ]
        tags:
            - { name: domain_event.listener, event: PurchaseOrderCreatedEvent, method: onPurchaseOrderCreated }
```

Publish events in listener

```php
// get event bus from DI container
$bus = $this->get('domain_event.bus');

// do what you need to do on your Domain
$purchase_order = new PurchaseOrder(new CustomerId(1));

// this will clear the list of event in your AggregateEvents so an Event is trigger only once
$events = $purchase_order->pullEvents();

// You can have more than one event at a time.
foreach($events as $event) {
    $bus->publish($event);
}

// You can use one method
//$bus->pullAndPublish($purchase_order);
```

Listener method name
--------------------

You do not need to specify the name of the event handler method. By default, the
[__invoke](http://php.net/manual/en/language.oop5.magic.php#object.invoke) method is used.


```php
use GpsLab\Domain\Event\Event;

class SendEmailOnPurchaseOrderCreated
{
    private $mailer;

    public function __construct(\Swift_Mailer $mailer)
    {
        $this->mailer = $mailer;
    }

    public function __invoke(PurchaseOrderCreatedEvent $event)
    {
        $message = $this->mailer
            ->createMessage()
            ->setTo('recipient@example.com')
            ->setBody(sprintf(
                'Purchase order created at %s for customer #%s',
                $event->getCreateAt()->format('Y-m-d'),
                $event->getCustomer()->getId()
            ));

        $this->mailer->send($message);
    }
}
```

Register event listener

```yml
services:
    acme.domain.purchase_order.event.created.send_email_listener:
        class: SendEmailOnPurchaseOrderCreated
        arguments: [ '@mailer' ]
        tags:
            - { name: domain_event.listener, event: PurchaseOrderCreatedEvent }
```

Use pull Predis queue
---------------------

Install Predis with [Composer](http://packagist.org), run:

```sh
composer require predis/predis
```

Register services:

```yml
services:
    # Predis
    acme.predis:
        class: Predis\Client
        arguments: [ '127.0.0.1' ]

    # Events serializer for queue
    acme.domain.event.queue.serializer:
        class: GpsLab\Domain\Event\Queue\Serializer\SymfonySerializer
        arguments: [ '@serializer', 'json' ]

    # Predis event queue
    acme.domain.event.queue:
        class: GpsLab\Domain\Event\Queue\Pull\PredisPullEventQueue
        arguments: [ '@acme.predis', '@acme.domain.event.queue.serializer', '@logger', 'event_queue_name' ]
```

Change config for use custom queue:

```yml
gpslab_domain_event:
    queue: 'acme.domain.event.queue'
```

And now you can use custom queue:

```php
$container->get('domain_event.queue')->publish($domain_event);
```

In latter pull events from queue:

```php
$queue = $container->get('domain_event.queue');
$bus = $container->get('domain_event.bus');

while ($event = $queue->pull()) {
    $bus->publish($event);
}
```

Use Predis subscribe queue
--------------------------

Install Predis PubSub adapter with [Composer](http://packagist.org), run:

```sh
composer require superbalist/php-pubsub-redis
```

Register services:

```yml
services:
    # Predis
    acme.predis:
        class: Predis\Client
        arguments: [ '127.0.0.1' ]

    # Predis PubSub adapter
    acme.predis.pubsub:
        class: Superbalist\PubSub\Redis\RedisPubSubAdapter
        arguments: [ '@acme.predis' ]

    # Events serializer for queue
    acme.domain.event.queue.serializer:
        class: GpsLab\Domain\Event\Queue\Serializer\SymfonySerializer
        arguments: [ '@serializer', 'json' ]

    # Predis event queue
    acme.domain.event.queue:
        class: GpsLab\Domain\Event\Queue\Subscribe\PredisSubscribeEventQueue
        arguments: [ '@acme.predis.pubsub', '@acme.domain.event.queue.serializer', '@logger', 'event_queue_name' ]
```

Change config for use custom queue:

```yml
gpslab_domain_event:
    queue: 'acme.domain.event.queue'
```

And now you can use custom queue:

```php
$container->get('domain_event.queue')->publish($domain_event);
```

Subscribe on the queue:

```php
$container->get('domain_event.queue')->subscribe(function (Event $event) {
    // do somthing
});
```

> **Note**
>
> You can use subscribe handlers as a services and [tag](http://symfony.com/doc/current/service_container/tags.html) it
for optimize register.

Many queues
-----------

You can use many queues for separation the flows. For example, you want to handle events of different Bounded Contexts
separately from each other.

```yml
services:
    acme.domain.purchase_order.event.queue:
        class: GpsLab\Domain\Event\Queue\Pull\PredisPullEventQueue
        arguments: [ '@acme.predis', '@acme.domain.event.queue.serializer', '@logger', 'purchase_order_event_queue' ]

    acme.domain.article_comment.event.queue:
        class: GpsLab\Domain\Event\Queue\Pull\PredisPullEventQueue
        arguments: [ '@acme.predis', '@acme.domain.event.queue.serializer', '@logger', 'article_comment_event_queue' ]
```

And now you can use a different queues.

In **Purchase order** Bounded Contexts.

```php
$event = new PurchaseOrderCreatedEvent(
    new CustomerId(1),
    new \DateTimeImmutable()
);

$container->get('acme.domain.purchase_order.event.queue')->publish($event);
```

In **Article comment** Bounded Contexts.

```php
$event = new ArticleCommentedEvent(
    new ArticleId(1),
    new AuthorId(1),
    $comment
    new \DateTimeImmutable()
);

$container->get('acme.domain.article_comment.event.queue')->publish($event);
```

> **Note**
>
> Similarly, you can split the subscribe queues.

License
-------

This bundle is under the [MIT license](http://opensource.org/licenses/MIT). See the complete license in the file: LICENSE
