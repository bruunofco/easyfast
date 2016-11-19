<?php
namespace EasyFast\Common;

/**
 * Class Annotations
 * @package EasyFast\Common
 * @author Bruno Oliveira <bruno@salluzweb.com.br>
 */
class AnnotationReader
{
    /**
     * @param $class
     * @param null $annotation
     * @return mixed
     */
    public static function getAnnotationClass($class, $annotation = null)
    {
        $rc = new \ReflectionClass($class);
        preg_match_all('#@(.*?)\n#s', $rc->getDocComment(), $ann);
        foreach ($ann[1] as $an) {
            $an = explode(' ', $an, 2);
            $anns[$an[0]] = trim($an[1]);
        }

        if (!is_null($annotation)) {
            if (!isset($anns[$annotation])) {
                return false;
            }
            return $anns[$annotation];
        }

        return $anns;
    }

    /**
     * @param $class
     * @param $method
     * @param null $annotation
     * @return mixed
     */
    public static function getAnnotationMethod($class, $method, $annotation = null)
    {
        $rc = new \ReflectionMethod($class, $method);
        preg_match_all('#@(.*?)\n#s', $rc->getDocComment(), $ann);
        foreach ($ann[1] as $an) {
            // TODO: Verificar quando houver mais de uma annotation com o mesmo nome
            $an = explode(' ', $an, 2);
            $anns[$an[0]] = $an[1];
        }

        if (!is_null($annotation)) {
            if (!isset($anns[$annotation])) {
                return false;
            }
            return $anns[$annotation];
        }

        return $anns;
    }
}