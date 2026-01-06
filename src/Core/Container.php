<?php

namespace App\Core;

class Container
{
    //always execute function
    public array $bindings = [];
    public array $shared = [];
    public array $instances = [];
    public function bind(string $key, callable $value)
    {
        $this->bindings[$key] = $value;
    }

    public function singleton(string $key, callable $value)
    {
        $this->shared[$key] = $value;
    }

    public function get(string $key)
    {
        /// Already resolved singleton
        if(isset($this->instances[$key])){
            return $this->instances[$key];
        }
        // Normal binding
        if(isset($this->bindings[$key])){
            return $this->bindings[$key]($this);
        }

        // Singleton binding
        if(isset($this->shared[$key])){
            $instance = $this->shared[$key]($this);
            $this->instances[$key] = $instance; //cache
            return $instance;
        }

        // Auto-resolve concrete class
        if(class_exists($key)){
            return $this->resolve($key);
        }


        throw new \Exception("Cannot find service {$key}");
    }

   protected function resolve(string $class)
    {
        $reflector = new \ReflectionClass($class);
        $constructor = $reflector->getConstructor();

        if(!$constructor)  return new $class;;

        $dependencies = [];
        foreach($constructor->getParameters() as $param){
             $type = $param->getType();
             // No type hint
            if (!$type instanceof \ReflectionNamedType) {
                throw new \Exception(
                    "Cannot resolve parameter \${$param->getName()} in {$class}"
                );
            }

            // Class dependency
            if (!$type->isBuiltin()) {
                $dependencies[] = $this->get($type->getName());
                continue;
            }

            // Scalar with default value
            if ($param->isDefaultValueAvailable()) {
                $dependencies[] = $param->getDefaultValue();
                continue;
            }

            throw new \Exception(
                "Cannot resolve parameter \${$param->getName()} in {$class}"
            );

        }
        
        return $reflector->newInstanceArgs($dependencies);
    }


}