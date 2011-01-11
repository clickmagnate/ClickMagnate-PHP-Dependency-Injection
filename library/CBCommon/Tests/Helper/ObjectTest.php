<?php

/*
THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS "AS IS" AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT
NOT LIMITED TO, THE IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE ARE DISCLAIMED. IN NO EVENT SHALL
THE COPYRIGHT HOLDER OR CONTRIBUTORS BE LIABLE FOR ANY DIRECT, INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES
(INCLUDING, BUT NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES; LOSS OF USE, DATA, OR PROFITS; OR BUSINESS INTERRUPTION)
HOWEVER CAUSED AND ON ANY THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT (INCLUDING NEGLIGENCE OR OTHERWISE)
ARISING IN ANY WAY OUT OF THE USE OF THIS SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.
 */

class ObjectTest extends PHPUnit_Framework_TestCase {

    public function testToArray() {
        $this->assertThat(
          array('firstName' => 'Foo', 'lastName' => 'Bar', 'age' => 24, 'email' => 'foo@bar.com'),
          $this->equalTo(CBCommon_Helper_Object::ToArray(new TestObject()))
        );
    }

    public function testGetArgumentNamesFromMethod_MethodWithArgs() {
       $object = new TestObject();
       $results = CBCommon_Helper_Object::GetArgumentNamesFromMethod($object, "TestParametersOnMethod");
       $this->assertThat(array('name','email','address','city', 'state', 'zipcode'), $this->equalTo($results));
    }

    public function testGetArgumentNamesFromMethod_MethodWithNoArgs() {
       $object = new TestObject();
       $results = CBCommon_Helper_Object::GetArgumentNamesFromMethod($object, "TestDocBlockMethod");
       $this->assertThat(array(), $this->equalTo($results));
    }

    public function testAttributesFromClass() {
        $object = new TestObject();

        $expected = array();
        $expected[] = new CBCommon_Domain_Attribute("test", "Testing Attributes Class Grabber", $object);

        $results = CBCommon_Helper_Object::GetAttributesFromClass($object);
        $this->assertThat($expected, $this->equalTo($results));
    }

    public function testAttributesFromMethod() {
        $object = new TestObject();
        $expected = array();
        $expected[] = new CBCommon_Domain_Attribute("test", "Testing Attributes Method Grabber");

        $results = CBCommon_Helper_Object::GetAttributesFromMethod($object,"TestDocBlockMethod", $object->TestDocBlockMethod());
        $this->assertThat($expected, $this->equalTo($results));
    }   

    public function testAttributesFromProperty() {
        $object = new TestObject();
        $expected = array();
        $expected[] = new CBCommon_Domain_Attribute("test", "Testing Attributes Property Grabber", $object->age);

        $results = CBCommon_Helper_Object::GetAttributesFromProperty($object,"age");
        $this->assertThat($expected, $this->equalTo($results));
    }

    public function testGetPropertyAttributesDictionary() {

        $object = new TestObject();

        $ageArray = array();
        $ageArray[] = new CBCommon_Domain_Attribute("test", "Testing Attributes Property Grabber", $object->age);

        $emailArray = array();
        $emailArray[] = new CBCommon_Domain_Attribute("test", "Testing Attributes Property Grabber", $object->email);

        $expected = array();
        $expected['age'] = $ageArray;
        $expected['email'] = $emailArray;

        $results = CBCommon_Helper_Object::GetPropertyAttributesDictionary($object);
        $this->assertThat($expected, $this->equalTo($results));
    }

}

/**
 * @test Testing Attributes Class Grabber
 */
class TestObject extends CBCommon_Pattern_MagicClass {

    public $firstName = 'Foo';
    public $lastName = 'Bar';
    /**
     * @test Testing Attributes Property Grabber
     */
    public $age = 24;
    private $favoriteFood = 'oreos';

    /**
     * @test Testing Attributes Property Grabber
     */
    public $email = "foo@bar.com";
    
    /**
     * @test Testing Attributes Method Grabber
     */
    public function TestDocBlockMethod() {
        //Do Something
    }

    public function TestParametersOnMethod($name, $email, $address, $city, $state, $zipcode) {
        
    }
    
}
?>
