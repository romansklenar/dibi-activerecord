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
		$this->assertEquals('OrderDetails', $asc->getIntersectEntity(ActiveMapper::getConnection()->getDatabaseInfo()));

		$asc = new HasAndBelongsToManyAssociation('Product', 'Order', 'OrderDetails');
		$this->assertEquals('OrderDetails', $asc->getIntersectEntity(ActiveMapper::getConnection()->getDatabaseInfo()));

		
		$asc = new HasAndBelongsToManyAssociation('Posts', 'Tags', 'PostsTags');
		$this->assertEquals('PostsTags', $asc->getIntersectEntity(ActiveMapper::getConnection('#nette_style')->getDatabaseInfo()));

		$asc = new HasAndBelongsToManyAssociation('Tag', 'Post', 'PostsTags');
		$this->assertEquals('PostsTags', $asc->getIntersectEntity(ActiveMapper::getConnection('#nette_style')->getDatabaseInfo()));


		
		Inflector::$railsStyle = TRUE;
		$asc = new HasAndBelongsToManyAssociation('Album', 'Song', 'albums_songs');
		$this->assertEquals('albums_songs', $asc->getIntersectEntity(ActiveMapper::getConnection('#rails_style')->getDatabaseInfo()));

		$asc = new HasAndBelongsToManyAssociation('Songs', 'Album', 'albums_songs');
		$this->assertEquals('albums_songs', $asc->getIntersectEntity(ActiveMapper::getConnection('#rails_style')->getDatabaseInfo()));
	}

	public function testGetIntersectEntityByAutodetect() {
		Inflector::$railsStyle = FALSE;
		$asc = new HasAndBelongsToManyAssociation('Posts', 'Tags');
		$this->assertEquals('PostsTags', $asc->getIntersectEntity(ActiveMapper::getConnection('#nette_style')->getDatabaseInfo()));

		$asc = new HasAndBelongsToManyAssociation('Tag', 'Post');
		$this->assertEquals('PostsTags', $asc->getIntersectEntity(ActiveMapper::getConnection('#nette_style')->getDatabaseInfo()));


		Inflector::$railsStyle = TRUE;
		$asc = new HasAndBelongsToManyAssociation('Album', 'Song');
		$this->assertEquals('albums_songs', $asc->getIntersectEntity(ActiveMapper::getConnection('#rails_style')->getDatabaseInfo()));

		$asc = new HasAndBelongsToManyAssociation('Songs', 'Album');
		$this->assertEquals('albums_songs', $asc->getIntersectEntity(ActiveMapper::getConnection('#rails_style')->getDatabaseInfo()));
	}

	public function testGetIntersectEntityFails() {
		Inflector::$railsStyle = FALSE;
		$this->setExpectedException('InvalidStateException');
		$asc = new HasAndBelongsToManyAssociation('Order', 'Product');
		$asc->getIntersectEntity(ActiveMapper::getConnection()->getDatabaseInfo());
	}

	public function testRetreiveReferenced() {
		Inflector::$railsStyle = FALSE;
		$order = Order::find(10100);
		$asc = new HasAndBelongsToManyAssociation('Order', 'Product', 'OrderDetails');
		$asc = new HasAndBelongsToManyAssociation('Order', 'Products', 'OrderDetails');
		
		$ref = $asc->retreiveReferenced($order)->load();
		$this->assertType('ActiveCollection', $ref);
		$this->assertEquals(4, $ref->count());
		$this->assertEquals('S18_1749', $ref->first()->productCode);
		$this->assertEquals('S24_3969', $ref->last()->productCode);

		$product = Product::find('S10_1678');
		$asc = new HasAndBelongsToManyAssociation('Product', 'Order', 'OrderDetails');
		$asc = new HasAndBelongsToManyAssociation('Products', 'Orders', 'OrderDetails');

		$ref = $asc->retreiveReferenced($product)->load();
		$this->assertType('ActiveCollection', $ref);
		$this->assertEquals(28, $ref->count());
		$this->assertEquals('10107', $ref->first()->orderNumber);
		$this->assertEquals('10417', $ref->last()->orderNumber);



		$post = Post::find(4);
		$this->assertType('ActiveCollection', $post->tags);
		$this->assertEquals(2, count($post->tags));
		$this->assertType('Tag', $tag = $post->tags->first());
		$this->assertEquals(1, $tag->id);
		$this->assertType('Tag', $tag = $post->tags->last());
		$this->assertEquals(3, $tag->id);

		$tag = Tag::find(1);
		$this->assertType('ActiveCollection', $tag->posts);
		$this->assertEquals(5, count($tag->posts));
		$this->assertType('Post', $post = $tag->posts->first());
		$this->assertEquals(1, $post->id);
		$this->assertType('Post', $post = $tag->posts->last());
		$this->assertEquals(7, $post->id);



		Inflector::$railsStyle = TRUE;

		$album = Album::find(3);
		$this->assertType('ActiveCollection', $album->songs);
		$this->assertEquals(6, count($album->songs));
		$this->assertType('Song', $song = $album->songs->first());
		$this->assertEquals(2, $song->id);
		$this->assertType('Song', $song = $album->songs->last());
		$this->assertEquals(8, $song->id);

		$song = Song::find(7);
		$this->assertType('ActiveCollection', $song->albums);
		$this->assertEquals(3, count($song->albums));
		$this->assertType('Album', $album = $song->albums->first());
		$this->assertEquals(1, $album->id);
		$this->assertType('Album', $album = $song->albums->last());
		$this->assertEquals(3, $album->id);
	}
}