<?php declare(strict_types=1);

use Symfony\Component\Form\FormError;
use Symfony\Component\Form\FormErrorIterator;
use Symfony\Component\Form\FormInterface;
use function PHPStan\Testing\assertType;

/** @var FormInterface $form */
$form = new stdClass();

assertType(FormErrorIterator::class . '<'. FormError::class .'>', $form->getErrors());
assertType(FormErrorIterator::class . '<'. FormError::class .'>', $form->getErrors(false));
assertType(FormErrorIterator::class . '<'. FormError::class .'>', $form->getErrors(false, true));

assertType(FormErrorIterator::class . '<'. FormError::class .'>', $form->getErrors(true));
assertType(FormErrorIterator::class . '<'. FormError::class .'>', $form->getErrors(true, true));

assertType(FormErrorIterator::class . '<'. FormError::class .'>', $form->getErrors(false, false));

assertType(FormErrorIterator::class . '<'. FormError::class .'|'. FormErrorIterator::class . '>', $form->getErrors(true, false));
