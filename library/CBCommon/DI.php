<?php

/*
THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS "AS IS" AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT
NOT LIMITED TO, THE IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE ARE DISCLAIMED. IN NO EVENT SHALL
THE COPYRIGHT HOLDER OR CONTRIBUTORS BE LIABLE FOR ANY DIRECT, INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES
(INCLUDING, BUT NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES; LOSS OF USE, DATA, OR PROFITS; OR BUSINESS INTERRUPTION)
HOWEVER CAUSED AND ON ANY THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT (INCLUDING NEGLIGENCE OR OTHERWISE)
ARISING IN ANY WAY OUT OF THE USE OF THIS SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.
 */

class CBCommon_DI{

    private $config;

    protected function __construct(Zend_Config $config) {
        $this->config = $config;
    }

    public function canLookupInRegistry($key) {
        return (is_string($key) || is_int($key)) && Zend_Registry::isRegistered($key);
    }

    public function _get($key) {
        
        if ($this->canLookupInRegistry($key)) {
            return Zend_Registry::get($key);
        }

        $injectArray = $this->config->inject->toArray();

        if ($injectArray != null && count($injectArray) > 0) {
            if (isset($injectArray[$key])) {

                if (isset($injectArray[$key]['class'])) {
                    $className = $injectArray[$key]['class'];
                    unset($injectArray[$key]['class']);
                } else {
                    throw new Exception("need class definition in object definition");
                }

                $methodType = '__construct';

                if (!CBCommon_Helper_Object::HasPublicConstructor($className)) {
                    if (CBCommon_Helper_Object::HasPublicStaticMethod($className, 'getInstance')) {
                        $methodType = 'getInstance';
                    }
                }

                $args = array();
                $arguments = CBCommon_Helper_Object::GetArgumentNamesFromMethod($className, $methodType);

                foreach ($arguments as $argument) {
                    $argumentConfig = $injectArray[$key][$argument];

                    if ($argumentConfig == 'new_array') {
                        $args[] = array();
                    } else if ($this->canLookupInRegistry($argumentConfig)) {
                        $obj = Zend_Registry::get($argumentConfig);
                        $args[] = $obj;
                    } else if (is_array($argumentConfig) || !isset($injectArray[$argumentConfig])) {
                        //String Reference
                        $args[] = $argumentConfig;
                    } else {
                        //Object reference
                        $args[] = $this->_get($argumentConfig);
                    }
                }

                if (count($args) != count($arguments)) {
                    throw new Exception("Tried to inject an object but missing matching constructor arguments");
                }

                $obj = null;

                if ($methodType == '__construct') {
                    $r = new ReflectionClass($className);
                    $obj = $r->newInstanceArgs($args);
                }

                if ($methodType == 'getInstance') {                    
                    $obj = call_user_func($className.'::getInstance');                    
                }

                if ($obj != null) {
                    $this->_set($key, $obj);
                }
                return $obj;
            }
        }
    }

    public function _set($key, $value) {
        Zend_Registry::set($key, $value);
    }

    //Static helpers

    private static $instance;

    public static function getInstance($fileLocation = '') {
        if (self::$instance == null) {

            if ($fileLocation != '') {
                $config = new Zend_Config_Ini($fileLocation);
            } else if (Zend_Registry::isRegistered('config')) {
                $config = Zend_Registry::get('config');
            }

            if (isset($config)) {
                self::$instance = new CBCommon_DI($config);
            }
        }
        return self::$instance;
    }

    public static function Get($key) {
        $instance = self::getInstance();
        return $instance->_get($key);
    }

    public static function Set($key) {
        $instance = self::getInstance();
        return $instance->_set($key);
    }
}
