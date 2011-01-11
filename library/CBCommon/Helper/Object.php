<?php

/*
THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS "AS IS" AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT
NOT LIMITED TO, THE IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE ARE DISCLAIMED. IN NO EVENT SHALL
THE COPYRIGHT HOLDER OR CONTRIBUTORS BE LIABLE FOR ANY DIRECT, INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES
(INCLUDING, BUT NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES; LOSS OF USE, DATA, OR PROFITS; OR BUSINESS INTERRUPTION)
HOWEVER CAUSED AND ON ANY THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT (INCLUDING NEGLIGENCE OR OTHERWISE)
ARISING IN ANY WAY OUT OF THE USE OF THIS SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.
 */

class CBCommon_Helper_Object {
    
    public static function ToArray ($object) {
        $class_vars = get_class_vars(get_class($object));

        $array = array();
        foreach ($class_vars as $name => $value) {
            $array[$name] = $object->$name;
        }

        return $array;
    }

    public static function AutoMap($from, $to) {
        $classVars = get_class_vars(get_class($to));

        foreach ($classVars as $name => $value) {
            $to->$name = $from->$name;
        }
    }

    public static function GetDocblock($class, $methodName = null) {
        if ($methodName == null) {
            return self::GetDocblockFromClass($class);
        } else {
            return self::GetDocblockFromMethod($class, $methodName);
        }
    }

    public static function GetAttributesFromClass($object) {
      $r = new Zend_Reflection_Class($object);

      $docblock = $r->getDocblock();

      $arrayOfAttributes = array();
      foreach ($docblock->getTags() as $tag) {
          $arrayOfAttributes[] = new CBCommon_Domain_Attribute($tag->getName(), $tag->getDescription(), $object);
      }
      return $arrayOfAttributes;
    }
    
    public static function GetAttributesFromMethod($class, $methodName) {

      $r = new Zend_Reflection_Method($class, $methodName);
      $docblock = $r->getDocblock();

      $arrayOfAttributes = array();
      foreach ($docblock->getTags() as $tag) {
          $arrayOfAttributes[] = new CBCommon_Domain_Attribute($tag->getName(), $tag->getDescription(), $class->$methodName());
      }
      return $arrayOfAttributes;
    }

    public static function GetArgumentNamesFromMethod($class, $methodName) {
        $r = new Zend_Reflection_Method($class, $methodName);
        $params = $r->getParameters();
        $parameterNames = array();
        foreach ($params as $param) {
            $parameterNames[] = $param->name;
        }
        return $parameterNames;
    }

    public static function HasPublicConstructor($class) {
        return self::IsMethodPublic($class, '__construct');
    }

    public static function HasPublicNonAbstractConstructor($class) {
        return self::IsMethodPublicAndNotAbstract($class, '__construct');
    }

    public static function IsMethodPublic($class, $methodName) {
        $r = new Zend_Reflection_Method($class, $methodName);
        return $r->isPublic();
    }

    public static function IsMethodPublicAndNotAbstract($class, $methodName) {
        $r = new Zend_Reflection_Method($class, $methodName);
        return $r->isPublic() && !$r->isAbstract();
    }

    public static function HasPublicStaticMethod($class, $methodName) {
        $r = new Zend_Reflection_Method($class, $methodName);
        return ($r->isStatic() && $r->isPublic());
    }

    public static function ClassIsAbstract($class) {
        $r = new ReflectionClass($class);
        return $r->isAbstract();
    }

    public static function GetAttributesFromProperty($object, $propertyName) {
        $class = get_class($object);
        $r = new ReflectionClass($class);
        $property = $r->getProperty($propertyName);

        $docblock = new Zend_Reflection_Docblock($property->getDocComment());
        foreach ($docblock->getTags() as $tag) {
          $arrayOfAttributes[] = new CBCommon_Domain_Attribute($tag->getName(), $tag->getDescription(), $object->$propertyName);
        }
        return $arrayOfAttributes;
    }

    /**
     * Returns an dictionary of propertyName -> array of CBCommon_Domain_Attribute objects
     */
    public static function GetPropertyAttributesDictionary($object) {

        $r = new ReflectionClass(get_class($object));

        $propertyAttributesArray = array();

        foreach ($r->getProperties() as $property) {
            $name = $property->getName();
            if ($property->getDocComment() != null) {
                $docblock = new Zend_Reflection_Docblock($property->getDocComment());

                $propertyAttributes = array();
                foreach ($docblock->getTags() as $tag) {
                  $propertyAttributes[] = new CBCommon_Domain_Attribute($tag->getName(), $tag->getDescription(), $object->$name);
                }
                $propertyAttributesArray[$name] = $propertyAttributes;
            }
        }

        return $propertyAttributesArray;
    }

    public static function ImplementsInterface ($class, $interface) {
        $reflector = new ReflectionClass($class);
        return ($reflector->implementsInterface($interface));
    }
}