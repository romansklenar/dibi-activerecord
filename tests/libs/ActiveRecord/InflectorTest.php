<?php

require_once 'PHPUnit/Framework.php';

/**
 * Test class for Inflector.
 */
class InflectorTest extends PHPUnit_Framework_TestCase {

	public function testPluralize() {
		$this->assertSame('dogs', Inflector::pluralize('dog'));
		$this->assertSame('Dogs', Inflector::pluralize('Dog'));
		$this->assertSame('DOGs', Inflector::pluralize('DOG'));

		$this->assertSame('quizzes', Inflector::pluralize('quiz'));
		$this->assertSame('tomatoes', Inflector::pluralize('tomato'));
		$this->assertSame('mice', Inflector::pluralize('mouse'));
		$this->assertSame('people', Inflector::pluralize('person'));
		$this->assertSame('equipment', Inflector::pluralize('equipment'));
		$this->assertSame('companies', Inflector::pluralize('company'));
	}

	public function testSingularize() {
		$this->assertSame('dog', Inflector::singularize('dogs'));
		$this->assertSame('Dog', Inflector::singularize('Dogs'));
		$this->assertSame('DOG', Inflector::singularize('DOGS'));

		$this->assertSame('quiz', Inflector::singularize('quizzes'));
		$this->assertSame('tomato', Inflector::singularize('tomatoes'));
		$this->assertSame('mouse', Inflector::singularize('mice'));
		$this->assertSame('person', Inflector::singularize('people'));
		$this->assertSame('equipment', Inflector::singularize('equipment'));
		$this->assertSame('company', Inflector::singularize('companies'));
	}

	public function testIsPlural() {
		$this->assertFalse(Inflector::isPlural('dog'));
		$this->assertFalse(Inflector::isPlural('Dog'));
		$this->assertFalse(Inflector::isPlural('DOG'));
		$this->assertFalse(Inflector::isPlural('quiz'));
		$this->assertFalse(Inflector::isPlural('tomato'));
		$this->assertFalse(Inflector::isPlural('mouse'));
		$this->assertFalse(Inflector::isPlural('person'));
		$this->assertFalse(Inflector::isPlural('company'));

		$this->assertTrue(Inflector::isPlural('dogs'));
		$this->assertTrue(Inflector::isPlural('Dogs'));
		$this->assertTrue(Inflector::isPlural('DOGS'));
		$this->assertTrue(Inflector::isPlural('quizzes'));
		$this->assertTrue(Inflector::isPlural('tomatoes'));
		$this->assertTrue(Inflector::isPlural('mice'));
		$this->assertTrue(Inflector::isPlural('people'));
		$this->assertTrue(Inflector::isPlural('equipment'));
		$this->assertTrue(Inflector::isPlural('companies'));
	}

	public function testIsSingular() {
		$this->assertFalse(Inflector::isSingular('dogs'));
		$this->assertFalse(Inflector::isSingular('Dogs'));
		$this->assertFalse(Inflector::isSingular('DOGS'));
		$this->assertFalse(Inflector::isSingular('quizzes'));
		$this->assertFalse(Inflector::isSingular('tomatoes'));
		$this->assertFalse(Inflector::isSingular('mice'));
		$this->assertFalse(Inflector::isSingular('people'));
		$this->assertFalse(Inflector::isSingular('companies'));

		$this->assertTrue(Inflector::isSingular('dog'));
		$this->assertTrue(Inflector::isSingular('Dog'));
		$this->assertTrue(Inflector::isSingular('DOG'));
		$this->assertTrue(Inflector::isSingular('quiz'));
		$this->assertTrue(Inflector::isSingular('tomato'));
		$this->assertTrue(Inflector::isSingular('mouse'));
		$this->assertTrue(Inflector::isSingular('person'));
		$this->assertTrue(Inflector::isSingular('equipment'));
		$this->assertTrue(Inflector::isSingular('company'));
	}

	public function testIsRegular() {
		$this->markTestSkipped();
		$this->assertTrue(Inflector::isRegular('dogs'));
		$this->assertFalse(Inflector::isRegular('person'));
	}

	public function testIsCountable() {
		$this->markTestSkipped();
		$this->assertTrue(Inflector::isCountable('tomatoes'));
		$this->assertFalse(Inflector::isCountable('equipment'));
	}

	public function testIsIrregular() {
		$this->markTestSkipped();
		$this->assertFalse(Inflector::isIrregular('dogs'));
		$this->assertTrue(Inflector::isIrregular('person'));
	}

	public function testIsUncountable() {
		$this->markTestSkipped();
		$this->assertFalse(Inflector::isUncountable('tomatoes'));
		$this->assertTrue(Inflector::isUncountable('equipment'));
	}

	public function testOrdinalize() {
		$this->assertSame("0th", Inflector::ordinalize(0));
		$this->assertSame("1st", Inflector::ordinalize(1));
		$this->assertSame("2nd", Inflector::ordinalize(2));
		$this->assertSame("3rd", Inflector::ordinalize(3));
		$this->assertSame("4th", Inflector::ordinalize(4));
		$this->assertSame("5th", Inflector::ordinalize(5));
		$this->assertSame("10th", Inflector::ordinalize(10));
		$this->assertSame("11th", Inflector::ordinalize(11));
		$this->assertSame("12th", Inflector::ordinalize(12));
		$this->assertSame("13th", Inflector::ordinalize(13));
		$this->assertSame("14th", Inflector::ordinalize(14));
		$this->assertSame("20th", Inflector::ordinalize(20));
		$this->assertSame("21st", Inflector::ordinalize(21));
		$this->assertSame("22nd", Inflector::ordinalize(22));
		$this->assertSame("23rd", Inflector::ordinalize(23));
		$this->assertSame("24th", Inflector::ordinalize(24));
		$this->assertSame("25th", Inflector::ordinalize(25));
		$this->assertSame("100th", Inflector::ordinalize(100));
		$this->assertSame("101st", Inflector::ordinalize(101));
		$this->assertSame("102nd", Inflector::ordinalize(102));
		$this->assertSame("103rd", Inflector::ordinalize(103));
		$this->assertSame("104th", Inflector::ordinalize(104));
		$this->assertSame("105th", Inflector::ordinalize(105));
		$this->assertSame("1000th", Inflector::ordinalize(1000));
		$this->assertSame("1001st", Inflector::ordinalize(1001));
		$this->assertSame("1002nd", Inflector::ordinalize(1002));
		$this->assertSame("1003rd", Inflector::ordinalize(1003));
		$this->assertSame("1004th", Inflector::ordinalize(1004));
		$this->assertSame("1005th", Inflector::ordinalize(1005));
	}

	public function testHumanize() {
		$this->assertSame("Employee salary", Inflector::humanize("employee_salary"));
		$this->assertSame("Author", Inflector::humanize("author_id"));
	}

	public function testDemodulize() {
		$this->assertSame("Order", Inflector::demodulize("\Admin\Models\Order"));
		$this->assertSame("Order", Inflector::demodulize("Admin\Models\Order"));
		$this->assertSame("Order", Inflector::demodulize("\Models\Order"));
		$this->assertSame("Order", Inflector::demodulize("Models\Order"));
		$this->assertSame("Order", Inflector::demodulize("\Order"));
		$this->assertSame("Order", Inflector::demodulize("Order"));
	}

	public function testForeignKey() {
		Inflector::$railsStyle = TRUE;
		$this->assertSame("message_id", Inflector::foreignKey("\Front\Mailer\Message"));
		$this->assertSame("post_id", Inflector::foreignKey("\Front\Blog\Post"));
		$this->assertSame("user_id", Inflector::foreignKey("\Backend\User"));
		$this->assertSame("message_id", Inflector::foreignKey("Front\Mailer\Message"));
		$this->assertSame("post_id", Inflector::foreignKey("Front\Blog\Post"));
		$this->assertSame("user_id", Inflector::foreignKey("Backend\User"));
		$this->assertSame("message_id", Inflector::foreignKey("Message"));
		$this->assertSame("post_id", Inflector::foreignKey("Post"));
		$this->assertSame("user_id", Inflector::foreignKey("User"));
		$this->assertSame("message_id", Inflector::foreignKey("Messages"));
		$this->assertSame("post_id", Inflector::foreignKey("Posts"));
		$this->assertSame("user_id", Inflector::foreignKey("Users"));

		Inflector::$railsStyle = FALSE;
		$this->assertSame("messageId", Inflector::foreignKey("\Front\Mailer\Message"));
		$this->assertSame("postId", Inflector::foreignKey("\Front\Blog\Post"));
		$this->assertSame("userId", Inflector::foreignKey("\Backend\User"));
		$this->assertSame("messageId", Inflector::foreignKey("Front\Mailer\Message"));
		$this->assertSame("postId", Inflector::foreignKey("Front\Blog\Post"));
		$this->assertSame("userId", Inflector::foreignKey("Backend\User"));
		$this->assertSame("messageId", Inflector::foreignKey("Message"));
		$this->assertSame("postId", Inflector::foreignKey("Post"));
		$this->assertSame("userId", Inflector::foreignKey("User"));
		$this->assertSame("messageId", Inflector::foreignKey("Messages"));
		$this->assertSame("postId", Inflector::foreignKey("Posts"));
		$this->assertSame("userId", Inflector::foreignKey("Users"));
	}

	public function testIntersectEntity() {
		Inflector::$railsStyle = TRUE;
		$this->assertSame("messages_posts", Inflector::intersectEntity("\Front\Mailer\Message", "\Front\Blog\Post"));
		$this->assertSame("messages_posts", Inflector::intersectEntity("\Front\Mailer\Messages", "\Front\Blog\Posts"));
		$this->assertSame("posts_messages", Inflector::intersectEntity("\Front\Blog\Post", "\Front\Mailer\Message"));
		$this->assertSame("posts_messages", Inflector::intersectEntity("\Front\Blog\Posts", "\Front\Mailer\Messages"));
		$this->assertSame("users_messages", Inflector::intersectEntity("\Backend\User", "Front\Mailer\Message"));
		$this->assertSame("users_messages", Inflector::intersectEntity("\Backend\Users", "Front\Mailer\Messages"));
		$this->assertSame("posts_users", Inflector::intersectEntity("Front\Blog\Post", "Backend\User"));
		$this->assertSame("posts_users", Inflector::intersectEntity("Front\Blog\Posts", "Backend\Users"));
		$this->assertSame("messages_posts", Inflector::intersectEntity("Message", "Post"));
		$this->assertSame("messages_posts", Inflector::intersectEntity("Messages", "Posts"));
		$this->assertSame("users_messages", Inflector::intersectEntity("User", "Message"));
		$this->assertSame("users_messages", Inflector::intersectEntity("Users", "Messages"));
		$this->assertSame("posts_users", Inflector::intersectEntity("Post", "User"));
		$this->assertSame("posts_users", Inflector::intersectEntity("Posts", "Users"));

		Inflector::$railsStyle = FALSE;
		$this->assertSame("MessagesPosts", Inflector::intersectEntity("\Front\Mailer\Message", "\Front\Blog\Post"));
		$this->assertSame("MessagesPosts", Inflector::intersectEntity("\Front\Mailer\Messages", "\Front\Blog\Posts"));
		$this->assertSame("PostsMessages", Inflector::intersectEntity("\Front\Blog\Post", "\Front\Mailer\Message"));
		$this->assertSame("PostsMessages", Inflector::intersectEntity("\Front\Blog\Posts", "\Front\Mailer\Messages"));
		$this->assertSame("UsersMessages", Inflector::intersectEntity("\Backend\User", "Front\Mailer\Message"));
		$this->assertSame("UsersMessages", Inflector::intersectEntity("\Backend\Users", "Front\Mailer\Messages"));
		$this->assertSame("PostsUsers", Inflector::intersectEntity("Front\Blog\Post", "Backend\User"));
		$this->assertSame("PostsUsers", Inflector::intersectEntity("Front\Blog\Posts", "Backend\Users"));
		$this->assertSame("MessagesPosts", Inflector::intersectEntity("Message", "Post"));
		$this->assertSame("MessagesPosts", Inflector::intersectEntity("Messages", "Posts"));
		$this->assertSame("UsersMessages", Inflector::intersectEntity("User", "Message"));
		$this->assertSame("UsersMessages", Inflector::intersectEntity("Users", "Messages"));
		$this->assertSame("PostsUsers", Inflector::intersectEntity("Post", "User"));
		$this->assertSame("PostsUsers", Inflector::intersectEntity("Posts", "Users"));
	}

	public function testCamelize() {
		$this->assertSame('ActiveRecord', Inflector::camelize('active_record'));
		$this->assertSame('activeRecord', Inflector::camelize('active_record', FALSE));

		$this->markTestIncomplete();

		$this->assertSame('ActiveRecord\Errors', Inflector::camelize('./ActiveRecord/Errors'));
		$this->assertSame('activeRecord\Errors', Inflector::camelize('./ActiveRecord/Errors', FALSE));
		$this->assertSame('ActiveRecord\Errors', Inflector::camelize('active_record/errors'));
		$this->assertSame('activeRecord\Errors', Inflector::camelize('active_record/errors', FALSE));
	}

	public function testDasherize() {
		$this->assertSame('puni-puni', Inflector::dasherize('puni_puni'));
	}

	public function testUnderscore() {
		$this->assertSame('active_record', Inflector::underscore('ActiveRecord'));

		$this->markTestIncomplete();
		
		$this->assertSame('active_record/errors', Inflector::underscore('ActiveRecord::Errors'));
	}

	public function testTitleize() {
		$this->assertSame('Man From The Boondocks', Inflector::titleize('man from the boondocks'));
		$this->assertSame('X-Men: The Last Stand', Inflector::titleize('x-men: the last stand'));
	}

	public function testTableize() {
		Inflector::$railsStyle = TRUE;
		$this->assertSame('raw_scaled_scorers', Inflector::tableize('RawScaledScorer'));
		$this->assertSame('egg_and_hams', Inflector::tableize('egg_and_ham'));
		$this->assertSame('fancy_categories', Inflector::tableize('fancyCategory'));

		$this->assertSame('users', Inflector::tableize('User'));
		$this->assertSame('users', Inflector::tableize('UserModel'));
		$this->assertSame('models', Inflector::tableize('Model'));
		$this->assertSame('models', Inflector::tableize('ModelModel'));
		$this->assertSame('modelations', Inflector::tableize('Modelation'));
		$this->assertSame('modelations', Inflector::tableize('ModelationModel'));

		Inflector::$railsStyle = FALSE;
		$this->assertSame('RawScaledScorers', Inflector::tableize('RawScaledScorer'));
		$this->assertSame('EggAndHams', Inflector::tableize('egg_and_ham'));
		$this->assertSame('FancyCategories', Inflector::tableize('fancyCategory'));

		$this->assertSame('Users', Inflector::tableize('User'));
		$this->assertSame('Users', Inflector::tableize('UserModel'));
		$this->assertSame('Models', Inflector::tableize('Model'));
		$this->assertSame('Models', Inflector::tableize('ModelModel'));
		$this->assertSame('Modelations', Inflector::tableize('Modelation'));
		$this->assertSame('Modelations', Inflector::tableize('ModelationModel'));
	}

	public function testClassify() {
		$this->assertSame('EggAndHam', Inflector::classify('egg_and_hams'));
		$this->assertSame('Post', Inflector::classify('posts'));
		$this->assertSame('RawScaledScorer', Inflector::classify('raw_scaled_scorers'));
		$this->assertSame('FancyCategory', Inflector::classify('fancy_categories'));
		
		$this->assertSame('EggAndHam', Inflector::classify('EggAndHams'));
		$this->assertSame('Post', Inflector::classify('Posts'));
		$this->assertSame('RawScaledScorer', Inflector::classify('RawScaledScorers'));
		$this->assertSame('FancyCategory', Inflector::classify('FancyCategories'));
	}
}