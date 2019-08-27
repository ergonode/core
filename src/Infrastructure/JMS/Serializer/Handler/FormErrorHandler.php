<?php

/**
 * Copyright © Bold Brand Commerce Sp. z o.o. All rights reserved.
 * See LICENSE.txt for license details.
 */

declare(strict_types = 1);

namespace Ergonode\Core\Infrastructure\JMS\Serializer\Handler;

use Ergonode\Core\Infrastructure\Mapper\FormErrorMapper;
use JMS\Serializer\Context;
use JMS\Serializer\GraphNavigatorInterface;
use JMS\Serializer\Handler\SubscribingHandlerInterface;
use JMS\Serializer\Visitor\SerializationVisitorInterface;
use Symfony\Component\Form\Form;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 */
class FormErrorHandler implements SubscribingHandlerInterface
{
    /**
     * @var TranslatorInterface
     */
    private $translator;

    /**
     * @var FormErrorMapper
     */
    private $formErrorMapper;

    /**
     * @param TranslatorInterface $translator
     * @param FormErrorMapper     $formErrorMapper
     */
    public function __construct(TranslatorInterface $translator, FormErrorMapper $formErrorMapper)
    {
        $this->translator = $translator;
        $this->formErrorMapper = $formErrorMapper;
    }

    /**
     * @return array
     */
    public static function getSubscribingMethods(): array
    {
        $methods = [];
        $formats = ['json', 'xml', 'yml'];

        foreach ($formats as $format) {
            $methods[] = [
                'direction' => GraphNavigatorInterface::DIRECTION_SERIALIZATION,
                'type' => Form::class,
                'format' => $format,
                'method' => 'serialize',
            ];
        }

        return $methods;
    }

    /**
     * @param SerializationVisitorInterface $visitor
     * @param Form                          $form
     * @param array                         $type
     * @param Context                       $context
     *
     * @return mixed
     */
    public function serialize(SerializationVisitorInterface $visitor, Form $form, array $type, Context $context)
    {
        return $visitor->visitArray(
            [
                'code' => Response::HTTP_BAD_REQUEST,
                'message' => $this->translator->trans('Form validation error'),
                'errors' => $this->formErrorMapper->map($form),
            ],
            $type
        );
    }
}
