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
    public function __construct(CustomerId $customer_id)
    {
        $this->raise(new PurchaseOrderCreatedEvent($customer_id, new \DateTimeImmutable()));
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

    public function invoke(PurchaseOrderCreatedEvent $event)
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

License
-------

This bundle is under the [MIT license](http://opensource.org/licenses/MIT). See the complete license in the file: LICENSE
