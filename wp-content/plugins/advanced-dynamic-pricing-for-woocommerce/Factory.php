<?php

namespace ADP;

class Factory
{
    const PROJECT_NAMESPACE = __NAMESPACE__;
    const BASE_VERSION_NAMESPACE = self::PROJECT_NAMESPACE . "\\BaseVersion";
    const PRO_VERSION_NAMESPACE = self::PROJECT_NAMESPACE . "\\ProVersion";

    protected static function convertAlias($name)
    {
        return "Includes\\" . str_replace("_", "\\", $name);
    }

    /**
     * @param string $name
     *
     * @return string
     */
    public static function getClassName($name)
    {
        $className    = self::BASE_VERSION_NAMESPACE . "\\" . self::convertAlias($name);
        $proClassName = self::PRO_VERSION_NAMESPACE . "\\" . self::convertAlias($name);

        if (class_exists($proClassName)) {
            $className = $proClassName;
        }

        return $className;
    }

    public static function callStaticMethod($name, $method, ...$arguments)
    {
        $className = self::getClassName($name);

        return call_user_func_array(array($className, $method), $arguments);
    }

    public static function get($name, ...$arguments)
    {
        $className = self::getClassName($name);

        /**
         * Support Singletons
         * Hope we do not need it in the future
         */
        if (method_exists($className, 'get_instance')) {
            return $className::get_instance();
        }

        try {
            $class = new \ReflectionClass($className);
        } catch (\ReflectionException $e) {
            echo $e->getMessage();

            return null;
        }

        return $class->newInstanceArgs($arguments);
    }

    public static function getClassNamesInNameSpace($namespace)
    {
        $classNames = array();
        $path       = AutoLoader::convertNameSpaceIntoPath($namespace);

        foreach (glob($path . "/*") as $filename) {
            $baseClassName = str_replace(".php", "", basename($filename));
            $className     = $namespace . "\\" . $baseClassName;

            if (class_exists($className)) {
                $reflection = new \ReflectionClass($className);

                if ( ! $reflection->isAbstract()) {
                    $classNames[] = $className;
                }
            }
        }

        return $classNames;
    }

    public static function getClassNames($name)
    {
        $result = [];
        $baseClassNames = self::getClassNamesInNameSpace(
            self::BASE_VERSION_NAMESPACE . "\\" . self::convertAlias($name)
        );
        $proClassNames = self::getClassNamesInNameSpace(
            self::PRO_VERSION_NAMESPACE . "\\" . self::convertAlias($name)
        );

        foreach ($baseClassNames as $className) {
            if (!in_array(
                str_replace(self::BASE_VERSION_NAMESPACE, self::PRO_VERSION_NAMESPACE, $className),
                $proClassNames,
                true
            )) {
                $result[] = $className;
            }
        }

        return array_merge($result, $proClassNames);
    }

    public static function getShortClassName($className) {
        if (class_exists($className)) {
            $reflection = new \ReflectionClass($className);
            return $reflection->getShortName();
        } else {
            return false;
        }
    }
}
