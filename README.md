[![Latest Stable Version](https://img.shields.io/packagist/v/gpslab/domain-event-bundle.svg?maxAge=3600&label=stable)](https://packagist.org/packages/gpslab/domain-event-bundle)
[![PHP Version Support](https://img.shields.io/travis/php-v/gpslab/domain-event-bundle.svg?maxAge=3600)](https://packagist.org/packages/gpslab/domain-event-bundle)
[![Total Downloads](https://img.shields.io/packagist/dt/gpslab/domain-event-bundle.svg?maxAge=3600)](https://packagist.org/packages/gpslab/domain-event-bundle)
[![Build Status](https://img.shields.io/github/checks-status/gpslab/domain-event-bundle/master.svg?label=build&maxAge=3600)](https://travis-ci.org/gpslab/domain-event-bundle)
[![Coverage Status](https://img.shields.io/coveralls/gpslab/domain-event-bundle.svg?maxAge=3600)](https://coveralls.io/github/gpslab/domain-event-bundle?branch=master)
[![Scrutinizer Code Quality](https://img.shields.io/scrutinizer/g/gpslab/domain-event-bundle.svg?maxAge=3600)](https://scrutinizer-ci.com/g/gpslab/domain-event-bundle/?branch=master)
[![License](https://img.shields.io/packagist/l/gpslab/domain-event-bundle.svg?maxAge=3600)](https://github.com/gpslab/domain-event-bundle)

Domain event bundle
===================

Bundle to create the domain layer of your [Domain-driven design (DDD)](https://en.wikipedia.org/wiki/Domain-driven_design) application.

Installation
------------

Pretty simple with [Composer](http://packagist.org), run:

```sh
composer req gpslab/domain-event-bundle
```

Usage
-----

Create a domain event

```php
use Symfony\Contracts\EventDispatcher\Event;

final class PurchaseOrderCreatedEvent extends Event
{
    public CustomerId $customer_id;
    public \DateTimeImmutable $create_at;

    public function __construct(CustomerId $customer_id, \DateTimeImmutable $create_at)
    {
        $this->customer_id = $customer_id;
        $this->create_at = $create_at;
    }
}
```

Raise your event

```php
use GpsLab\Bundle\DomainEvent\Event\Aggregator\AbstractAggregateEvents;

class PurchaseOrder extends AbstractAggregateEvents
{
    private CustomerId $customer_id;
    private \DateTimeImmutable $create_at;

    public function __construct(CustomerId $customer_id)
    {
        $this->customer_id = $customer_id;
        $this->create_at = new \DateTimeImmutable();

        $this->raise(new PurchaseOrderCreatedEvent($customer_id, $this->create_at));
    }
}
```

Create event subscriber

```php
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class SendEmailOnPurchaseOrderCreated implements EventSubscriberInterface
{
    private \Swift_Mailer $mailer;

    public function __construct(\Swift_Mailer $mailer)
    {
        $this->mailer = $mailer;
    }

    public static function getSubscribedEvents(): array
    {
        return [
            PurchaseOrderCreatedEvent::class => 'onPurchaseOrderCreated',
        ];
    }

    public function onPurchaseOrderCreated(PurchaseOrderCreatedEvent $event): void
    {
        $message = $this->mailer
            ->createMessage()
            ->setTo('recipient@example.com')
            ->setBody(sprintf(
                'Purchase order created at %s for customer #%s',
                $event->create_at->format('Y-m-d'),
                $event->customer_id,
            ));

        $this->mailer->send($message);
    }
}
```

Publish events

```php
// do what you need to do on your Domain
$purchase_order = new PurchaseOrder(new CustomerId(1));

$em->persist($purchase_order);
$em->flush();
```

License
-------

This bundle is under the [MIT license](http://opensource.org/licenses/MIT). See the complete license in the file: LICENSE
