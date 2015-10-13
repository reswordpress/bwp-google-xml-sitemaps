<?php

use \Mockery as Mockery;

/**
 * @covers BWP_Sitemaps_Provider_Taxonomy
 */
class BWP_Sitemaps_Provider_Taxonomy_Test extends BWP_Sitemaps_PHPUnit_Provider_Unit_TestCase
{
	protected $excluder;

	protected $provider;

	protected function setUp()
	{
		parent::setUp();

		$this->excluder = Mockery::mock('BWP_Sitemaps_Excluder');

		$this->provider = new BWP_Sitemaps_Provider_Taxonomy($this->plugin, $this->excluder);
	}

	protected function tearDown()
	{
		parent::tearDown();
	}

	/**
	 * @covers BWP_Sitemaps_Provider_Taxonomy::get_taxonomies
	 */
	public function test_get_taxonomies()
	{
		$post_format = new stdClass();
		$post_format->name = 'post_format';

		$taxonomy = new stdClass();
		$taxonomy->name = 'category';

		$taxonomies = array(
			$post_format, $taxonomy
		);

		$this->bridge
			->shouldReceive('get_taxonomies')
			->with(array('public' => true), 'objects')
			->andReturn($taxonomies)
			->byDefault();

		$this->assertEquals(array($taxonomy), $this->provider->get_taxonomies());
	}

	/**
	 * @covers BWP_Sitemaps_Provider_Taxonomy::get_terms
	 * @dataProvider get_test_get_terms_arguments
	 */
	public function test_get_terms($taxonomy, array $ids = array(), array $excluded_ids = array(), $limit = 200)
	{
		$term1 = new stdClass();
		$term2 = new stdClass();

		$terms = array($term1, $term2);

		$this->bridge
			->shouldReceive('get_terms')
			->with($taxonomy, array(
				'include'    => $ids,
				'exclude'    => $excluded_ids,
				'number'     => $limit,
				'hide_empty' => false
			))
			->andReturn($terms)
			->byDefault();

		$this->assertEquals($terms, $this->provider->get_terms($taxonomy, $ids, $excluded_ids, $limit));
	}

	public function get_test_get_terms_arguments()
	{
		return array(
			array('category'),
			array('post_tag')
		);
	}

	/**
	 * @covers BWP_Sitemaps_Provider_Taxonomy::get_terms
	 */
	public function test_get_terms_should_throw_exception_when_both_ids_and_excluded_ids_are_provided()
	{
		$this->setExpectedException('DomainException', 'only term ids or excluded term ids can be provided, not both');

		$this->provider->get_terms('category', array(1,2), array(3,4));
	}

	/**
	 * @covers BWP_Sitemaps_Provider_Taxonomy::get_terms_by_name
	 */
	public function test_get_terms_by_name()
	{
		$this->excluder
			->shouldReceive('get_excluded_items')
			->andReturn(array(1,2))
			->byDefault();

		$term1 = new stdClass();
		$term2 = new stdClass();

		$terms = array($term1, $term2);

		$this->bridge
			->shouldReceive('get_terms')
			->with('category', array(
				'name__like' => 'name',
				'exclude'    => array(1,2),
				'hide_empty' => false
			))
			->andReturn($terms)
			->byDefault();

		$this->assertEquals($terms, $this->provider->get_terms_by_name('category', 'name'));
	}
}
