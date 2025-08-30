<?php declare(strict_types = 1);

namespace BrowserKitAssertionTrait;

use Symfony\Bundle\FrameworkBundle\Test\BrowserKitAssertionsTrait;
use Symfony\Component\BrowserKit\AbstractBrowser;
use function PHPStan\Testing\assertType;

class Foo {
    use BrowserKitAssertionsTrait;

	/**
	 * @param mixed $mixed
	 */
    public function test(AbstractBrowser $browser, ?AbstractBrowser $nullableBrowser, $mixed)
    {
        assertType('Symfony\Component\BrowserKit\AbstractBrowser', $this->getClient());
        assertType('null', $this->getClient(null));
        assertType('Symfony\Component\BrowserKit\AbstractBrowser', $this->getClient($browser));
        assertType('Symfony\Component\BrowserKit\AbstractBrowser|null', $this->getClient($nullableBrowser));
        assertType('Symfony\Component\BrowserKit\AbstractBrowser|null', $this->getClient($mixed));
    }
}
