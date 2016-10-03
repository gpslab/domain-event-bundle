[![Latest Stable Version](https://img.shields.io/packagist/v/gpslab/domain-event-bundle.svg?maxAge=3600&label=stable)](https://packagist.org/packages/gpslab/domain-event-bundle)
[![Latest Unstable Version](https://img.shields.io/packagist/vpre/gpslab/domain-event-bundle.svg?maxAge=3600&label=unstable)](https://packagist.org/packages/gpslab/domain-event-bundle)
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

## Installation

Pretty simple with [Composer](http://packagist.org), run:

```sh
composer require gpslab/domain-event-bundle
```

## Configuration

Example configuration

```yml
gpslab_domain_event:
    # Event listener locator
    # Support 'voter', 'named_event' or custom service
    # As a default used 'named_event'
    locator: 'named_event'

    # Evant name resolver used in 'named_event' event listener locator
    # Support 'event_class', 'event_class_last_part', 'named_event' or a custom service
    # As a default used 'event_class'
    name_resolver: 'event_class'
```

## Usage

Create a domain event

```php
use GpsLab\Domain\Event\EventInterface;

class PurchaseOrderCreatedEvent implements EventInterface
{
    private $customer;
    private $create_at;

    public function __construct(Customer $customer, \DateTimeImmutable $create_at)
    {
        $this->customer = $customer;
        $this->create_at = $create_at;
    }

    public function getCustomer()
    {
        return $this->customer;
    }

    public function getCreateAt()
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
    public function __construct(Customer $customer)
    {
        $this->raise(new PurchaseOrderCreatedEvent($customer, new \DateTimeImmutable()));
    }
}
```

Create listener

```php
use GpsLab\Domain\Event\EventInterface;
use GpsLab\Domain\Event\Listener\ListenerInterface;

class SendEmailOnPurchaseOrderCreated implements ListenerInterface
{
    private $mailer;

    public function __construct(\Swift_Mailer $mailer)
    {
        $this->mailer = $mailer;
    }

    public function handle(EventInterface $event)
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
    domain_event.listener.purchase_order.send_email_on_created:
        class: SendEmailOnPurchaseOrderCreated
        arguments: [ '@mailer' ]
        tags:
            - { name: domain_event.named_event_listener, event: PurchaseOrderCreatedEvent }
```

Publish events in listener

```php
// get event bus from DI container
$bus = $this->get('domain_event.bus');

// do what you need to do on your Domain
$purchase_order = new PurchaseOrder(new Customer(1));

// this will clear the list of event in your AggregateEvents so an Event is trigger only once
$events = $purchase_order->pullEvents();

// You can have more than one event at a time.
foreach($events as $event) {
    $bus->publish($event);
}

// You can use one method
//$bus->pullAndPublish($purchase_order);
```

## License

This bundle is under the [MIT license](http://opensource.org/licenses/MIT). See the complete license in the file: LICENSE
