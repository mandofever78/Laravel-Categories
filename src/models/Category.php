<?php namespace Mandofever78\LaravelCategories;

use Baum\Node;

class Category extends Node
{

	/**
	 * Status values for the database
	 */
	const ENABLED = 'ENABLED';
	const DISABLED = 'DISABLED';

	/**
	 * Table name.
	 *
	 * @var string
	 */
	protected $table = 'mandofever78_categories';

	/**
	 * Used for Cviebrock/EloquentSluggable
	 * @var array
	 */

	/**
	 * Stores the old parent id before editing
	 * @var integer
	 */
	protected $oldParentId = null;

	/**
	 * The "booting" method of the model.
	 *
	 * @return void
	 */
	protected static function boot() {

		parent::boot();
		static::creating(function ($category) {
			$slug = \Str::slug($category->name);
			$increment = 0;
			while(Category::where('slug',$slug)->count() > 0) {
				$increment++;
				$slug = \Str::slug($category->name.' '.$increment);
			}
			$category->slug = $slug;
		});	
		static::updating(function ($category) {

			// Baum triggers a parent move, which puts the item last in the list, even if the old and new parents are the same
			if ($category->isParentIdSame())
			{
				$category->stopBaumParentMove();
			}

		});

	}

	/**
	 * Returns true if the parent_id value in the database is different to the current value in the model's dirty attributes
	 * @return bool
	 */
	protected function isParentIdSame()
	{
		$dirty = $this->getDirty();
		$oldNavItem = self::where('id','=',$this->id)->first();
		$oldParent = $oldNavItem->parent;
		$oldParentId = $oldParent->id;
		$isParentColumnSet = isset($dirty[$this->getParentColumnName()]);
		if ($isParentColumnSet)
		{
			$isNewParentSameAsOld = $dirty[$this->getParentColumnName()] == $oldParentId;
		}
		else
		{
			$isNewParentSameAsOld = false;
		}
		return $isParentColumnSet && $isNewParentSameAsOld;
	}

	/**
	 * Removes the parent_id field from the model's attributes and sets $moveToNewParentId static property on the parent
	 * Baum\Node model class to false to prevent Baum from triggering a move. This can be required because Baum triggers
	 * a parent move, which puts the item last in the list, even if the old and new parents are the same.
	 */
	protected function stopBaumParentMove()
	{
		unset($this->{$this->getParentColumnName()});
		static::$moveToNewParentId = FALSE;
	}

	/**
	 * Adds a query scope to add conditions to query object
	 *
	 * @param $query
	 * @return mixed
	 */
	public function scopeLive($query)
	{
		return $query->where('status', '=', self::ENABLED)
		->where('published_date', '<=', \Carbon\Carbon::now());
	}

	/**
	 * Accessor for path attribute, which is a string consisting of the ancestors of each node, separated by " > ".
	 * @return string
	 */
	public function getPathAttribute()
	{
		$ancestors = $this->getAncestors();
		$return = array();
		foreach($ancestors as $ancestor) {
			$return[] = $ancestor->name;
		}
		$return[] = $this->name;
		return implode(' > ', $return);
	}

}
