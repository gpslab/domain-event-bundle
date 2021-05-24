<?php

/**
 * GpsLab component.
 *
 * @author    Peter Gribanov <info@peter-gribanov.ru>
 * @copyright Copyright (c) 2011, Peter Gribanov
 * @license   http://opensource.org/licenses/MIT
 */

namespace GpsLab\Bundle\DomainEvent\Tests\Fixtures;

use Doctrine\Common\Persistence\Proxy as CommonProxy;
use Doctrine\Persistence\Proxy;

if (interface_exists(CommonProxy::class)) {
    class SimpleObjectProxy extends SimpleObject implements CommonProxy
    {
        /**
         * @var bool
         */
        public $__isInitialized__ = false;

        public function __load()
        {
            if (!$this->__isInitialized__) {
                $this->camelCase = 'proxy-boo';
                $this->__isInitialized__ = true;
            }
        }

        /**
         * @return bool
         */
        public function __isInitialized()
        {
            return $this->__isInitialized__;
        }
    }
} else {
    class SimpleObjectProxy extends SimpleObject implements Proxy
    {
        /**
         * @var bool
         */
        public $__isInitialized__ = false;

        public function __load()
        {
            if (!$this->__isInitialized__) {
                $this->camelCase = 'proxy-boo';
                $this->__isInitialized__ = true;
            }
        }

        /**
         * @return bool
         */
        public function __isInitialized()
        {
            return $this->__isInitialized__;
        }
    }
}
