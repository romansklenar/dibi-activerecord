<?php

require_once __DIR__ . '/../ActiveRecordDatabaseTestCase.php';

/**
 * Test class for HasAndBelongsToManyAssociation.
 */
class HasAndBelongsToManyAssociationTest extends ActiveRecordDatabaseTestCase {
	
	public function testConstruct() {
		/**
		 * Manager is referenced by Employee (Employee has foreign key)
		 * Customer @hasMany(Order)
		 * Order @hasMany(Customer)
		 */
		Inflector::$railsStyle = FALSE;
		$singular = new HasAndBelongsToManyAssociation('Customer', 'Order'); // Customer @hasMany(Order)
		$plural   = new HasAndBelongsToManyAssociation('Customer', 'Orders'); // Customer @hasMany(Orders)
		$this->assertEquals($singular, $plural);

		$singular = new HasAndBelongsToManyAssociation('Order', 'Customer'); // Order @hasMany(Customer)
		$plural   = new HasAndBelongsToManyAssociation('Order', 'Customers'); // Order @hasMany(Customers)
		$this->assertEquals($singular, $plural);
	}

	public function testIsInRelation() {
		Inflector::$railsStyle = FALSE;
		$asc = new HasAndBelongsToManyAssociation('Customer', 'Order');
		$this->assertTrue($asc->isInRelation('Order'));
		$this->assertFalse($asc->isInRelation('Customer'));

		$asc = new HasAndBelongsToManyAssociation('Customer', 'Orders');
		$this->assertTrue($asc->isInRelation('Order'));
		$this->assertFalse($asc->isInRelation('Customer'));

		$asc = new HasAndBelongsToManyAssociation('Order', 'Customer');
		$this->assertTrue($asc->isInRelation('Customer'));
		$this->assertFalse($asc->isInRelation('Order'));

		$asc = new HasAndBelongsToManyAssociation('Order', 'Customers');
		$this->assertTrue($asc->isInRelation('Customer'));
		$this->assertFalse($asc->isInRelation('Order'));
	}

	public function testGetIntersectEntityByGivenManually() {
		Inflector::$railsStyle = FALSE;
		$asc = new HasAndBelongsToManyAssociation('Order', 'Product', 'OrderDetails');
		$this->assertEquals('OrderDetails', $asc->getIntersectEntity('Order', 'Product', Mapper::getConnection()->getDatabaseInfo()));

		$asc = new HasAndBelongsToManyAssociation('Product', 'Order', 'OrderDetails');
		$this->assertEquals('OrderDetails', $asc->getIntersectEntity('Product', 'Order', Mapper::getConnection()->getDatabaseInfo()));

		
		Inflector::$railsStyle = TRUE;
		$asc = new HasAndBelongsToManyAssociation('Food', 'Ingredient', 'food_ingredients');
		$this->assertEquals('food_ingredients', $asc->getIntersectEntity('Food', 'Ingredient', Mapper::getConnection('#rails_style')->getDatabaseInfo()));

		$asc = new HasAndBelongsToManyAssociation('Ingredient', 'Food', 'food_ingredients');
		$this->assertEquals('food_ingredients', $asc->getIntersectEntity('Ingredient', 'Food', Mapper::getConnection('#rails_style')->getDatabaseInfo()));

	}

	public function testGetIntersectEntityByAutodetect() {
		Inflector::$railsStyle = FALSE;
		$asc = new HasAndBelongsToManyAssociation('Programmer', 'Project');
		$this->assertEquals('ProjectsProgrammers', $asc->getIntersectEntity('Programmer', 'Project', Mapper::getConnection('#nette_style')->getDatabaseInfo()));

		$asc = new HasAndBelongsToManyAssociation('Project', 'Programmer');
		$this->assertEquals('ProjectsProgrammers', $asc->getIntersectEntity('Project', 'Programmer', Mapper::getConnection('#nette_style')->getDatabaseInfo()));


		Inflector::$railsStyle = TRUE;
		$asc = new HasAndBelongsToManyAssociation('Food', 'Ingredient');
		$this->assertEquals('foods_ingredients', $asc->getIntersectEntity('Food', 'Ingredient', Mapper::getConnection('#rails_style')->getDatabaseInfo()));

		$asc = new HasAndBelongsToManyAssociation('Ingredient', 'Food');
		$this->assertEquals('foods_ingredients', $asc->getIntersectEntity('Ingredient', 'Food', Mapper::getConnection('#rails_style')->getDatabaseInfo()));
	}

	public function testGetIntersectEntityFails() {
		Inflector::$railsStyle = FALSE;
		$this->setExpectedException('InvalidStateException');
		$asc = new HasAndBelongsToManyAssociation('Order', 'Product');
		$asc->getIntersectEntity('Order', 'Product', Mapper::getConnection()->getDatabaseInfo());
	}

	public function testRetreiveReferenced() {
		Inflector::$railsStyle = FALSE;
		$order = Order::find(10100);
		$asc = new HasAndBelongsToManyAssociation('Order', 'Product', 'OrderDetails');
		$asc = new HasAndBelongsToManyAssociation('Order', 'Products', 'OrderDetails');
		
		$ref = $asc->retreiveReferenced($order)->load();
		$this->assertType('object', $ref);
		$this->assertTrue($ref instanceof ActiveRecordCollection);
		$this->assertEquals(4, $ref->count());
		$this->assertEquals('S18_1749', $ref->first()->productCode);
		$this->assertEquals('S24_3969', $ref->last()->productCode);

		$product = Product::find('S10_1678');
		$asc = new HasAndBelongsToManyAssociation('Product', 'Order', 'OrderDetails');
		$asc = new HasAndBelongsToManyAssociation('Products', 'Orders', 'OrderDetails');

		$ref = $asc->retreiveReferenced($product)->load();
		$this->assertType('object', $ref);
		$this->assertTrue($ref instanceof ActiveRecordCollection);
		$this->assertEquals(28, $ref->count());
		$this->assertEquals('10107', $ref->first()->orderNumber);
		$this->assertEquals('10417', $ref->last()->orderNumber);



		$post = Post::find(4);
		$this->assertTrue($post->tags instanceof ActiveRecordCollection);
		$this->assertEquals(2, count($post->tags));
		$this->assertTrue(($tag = $post->tags->first()) instanceof Tag);
		$this->assertEquals(1, $tag->id);
		$this->assertTrue(($tag = $post->tags->last()) instanceof Tag);
		$this->assertEquals(3, $tag->id);

		$tag = Tag::find(1);
		$this->assertTrue($tag->posts instanceof ActiveRecordCollection);
		$this->assertEquals(5, count($tag->posts));
		$this->assertTrue(($post = $tag->posts->first()) instanceof Post);
		$this->assertEquals(1, $post->id);
		$this->assertTrue(($post = $tag->posts->last()) instanceof Post);
		$this->assertEquals(7, $post->id);



		Inflector::$railsStyle = TRUE;

		$album = Album::find(3);
		$this->assertTrue($album->songs instanceof ActiveRecordCollection);
		$this->assertEquals(6, count($album->songs));
		$this->assertTrue(($song = $album->songs->first()) instanceof Song);
		$this->assertEquals(2, $song->id);
		$this->assertTrue(($song = $album->songs->last()) instanceof Song);
		$this->assertEquals(8, $song->id);

		$song = Song::find(7);
		$this->assertTrue($song->albums instanceof ActiveRecordCollection);
		$this->assertEquals(3, count($song->albums));
		$this->assertTrue(($album = $song->albums->first()) instanceof Album);
		$this->assertEquals(1, $album->id);
		$this->assertTrue(($album = $song->albums->last()) instanceof Album);
		$this->assertEquals(3, $album->id);
	}
}