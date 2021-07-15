<?php
declare(strict_types=1);

/**
 * GpsLab component.
 *
 * @author  Peter Gribanov <info@peter-gribanov.ru>
 * @license http://opensource.org/licenses/MIT
 */

namespace GpsLab\Bundle\DomainEvent\Tests\Event\Subscriber;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Event\OnFlushEventArgs;
use Doctrine\ORM\Event\PostFlushEventArgs;
use GpsLab\Bundle\DomainEvent\Event\Publisher;
use GpsLab\Bundle\DomainEvent\Event\Subscriber\DoctrineEventSubscriber;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class DoctrineEventSubscriberTest extends TestCase
{
    /**
     * @var MockObject&Publisher
     */
    private Publisher $publisher;

    /**
     * @var MockObject&EntityManagerInterface
     */
    private EntityManagerInterface $em;

    private DoctrineEventSubscriber $subscriber;

    protected function setUp(): void
    {
        $this->em = $this->getMockBuilder(EntityManagerInterface::class)->getMock();
        $this->publisher = $this
            ->getMockBuilder(Publisher::class)
            ->disableOriginalConstructor()
            ->getMock()
        ;

        $this->subscriber = new DoctrineEventSubscriber($this->publisher);
    }

    public function testPreFlush(): void
    {
        $event = $this
            ->getMockBuilder(OnFlushEventArgs::class)
            ->disableOriginalConstructor()
            ->getMock()
        ;
        $event
            ->expects($this->once())
            ->method('getEntityManager')
            ->willReturn($this->em)
        ;

        $this->publisher
            ->expects($this->once())
            ->method('aggregateEvents')
            ->with($this->em)
        ;

        $this->subscriber->onFlush($event);
    }

    public function testPostFlush(): void
    {
        $event = $this
            ->getMockBuilder(PostFlushEventArgs::class)
            ->disableOriginalConstructor()
            ->getMock()
        ;
        $event
            ->expects($this->atLeastOnce())
            ->method('getEntityManager')
            ->willReturn($this->em)
        ;

        $this->publisher
            ->expects($this->once())
            ->method('aggregateEvents')
            ->with($this->em)
        ;
        $this->publisher
            ->expects($this->once())
            ->method('dispatchEvents')
            ->with($this->em)
        ;

        $this->subscriber->postFlush($event);
    }
}
