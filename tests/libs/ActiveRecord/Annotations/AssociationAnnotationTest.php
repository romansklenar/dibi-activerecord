<?php

require_once 'PHPUnit/Framework.php';

/**
 * Test class for AssociationAnnotation.
 */
class AssociationAnnotationTest extends PHPUnit_Framework_TestCase {

	public function testConstruct() {
		$cmp['mock'][0] = new MockAnnotation(array(
			'0' => "Clients",
			'1' => "Invoices",
			'clientOf' => "Firm",
		));
		$rc = new ClassReflection('TestAnnotatedClass');
		$this->assertEquals($cmp, $rc->getAnnotations());
	}

}



class MockAnnotation extends AssociationAnnotation {
	
}


/**
 * @mock(Clients, Invoices, clientOf => Firm)
 */
class TestAnnotatedClass {

}