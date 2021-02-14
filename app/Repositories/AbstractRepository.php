<?php
/**
 * Created by PhpStorm.
 * User: Dmitry
 * Date: 20.11.2015
 * Time: 20:34.
 */
namespace App\Repositories;

use Illuminate\Database\Eloquent\Model;

/**
 * Class AbstractRepository.
 */
abstract class AbstractRepository
{
    /**
     * @var Model
     */
    protected $model;
	
    /**
     * @param Model $model
     */
    public function __construct(Model $model)
    {
        $this->model = $model;
    }
	
	public abstract function model();

    /**
     * Get all models.
     *
     * @param array $columns
     *
     * @return \Illuminate\Database\Eloquent\Collection|static[]
     */
    public function all($columns = ['*'])
    {
        return $this->model->all($columns);
    }

    /**
     * Find model by Id.
     *
     * @param $id
     * @param array $columns
     *
     * @return mixed
     */
    public function find($id, $columns = ['*'])
    {
        return $this->model->find($id, $columns);
    }
}
