<?php declare(strict_types = 1);

namespace PHPStan\Rules\Symfony;

use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Twig\Environment;
use function rand;

final class ExampleTwigController extends AbstractController
{

	public function foo(): void
	{
		$this->render('foo.html.twig');
		$this->renderBlock('foo.html.twig');
		$this->renderBlockView('foo.html.twig');
		$this->renderForm('foo.html.twig');
		$this->renderView('foo.html.twig');
		$this->stream('foo.html.twig');

		$this->render('bar.html.twig');
		$this->renderBlock('bar.html.twig');
		$this->renderBlockView('bar.html.twig');
		$this->renderForm('bar.html.twig');
		$this->renderView('bar.html.twig');
		$this->stream('bar.html.twig');

		$twig = new Environment();

		$twig->render('foo.html.twig');
		$twig->display('foo.html.twig');
		$twig->load('foo.html.twig');

		$twig->render('bar.html.twig');
		$twig->display('bar.html.twig');
		$twig->load('bar.html.twig');

		$templatedEmail = new TemplatedEmail();

		$templatedEmail->htmlTemplate('foo.html.twig');
		$templatedEmail->textTemplate('foo.html.twig');

		$templatedEmail->textTemplate('bar.html.twig');
		$templatedEmail->textTemplate('bar.html.twig');

		$name = 'foo.html.twig';

		$this->render($name);

		$name = 'bar.html.twig';

		$this->render($name);

		$name = rand(0, 1) ? 'foo.html.twig' : 'bar.html.twig';

		$this->render($name);

		$name = rand(0, 1) ? 'bar.html.twig' : 'baz.html.twig';

		$this->render($name);

		$this->render($this->getName());
	}

	private function getName(): string
	{
		return 'baz.html.twig';
	}

}
