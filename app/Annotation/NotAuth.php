<?php


namespace App\Annotation;

use Hyperf\Di\Annotation\AbstractAnnotation;

/**
 * Class NotAuth
 * @Annotation()
 * @Target({"METHOD","CLASS"})
 * @package App\Annotation
 */
class NotAuth extends AbstractAnnotation
{

}