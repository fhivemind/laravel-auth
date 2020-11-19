<?php

namespace App\Repositories;

use Illuminate\Database\Eloquent\Model;

/**
 * 
 * Implements BaseRepository.
 * 
 * Controls the way how the model is being handled on a DB level. 
 * This injects the DB logic into the model.
 * 
 * Serves as a bridge between model and controller.
 * 
 * The authorization logic should be handled preferably before this.
 * However, it is possible to do so inside the repository as well.
 * 
 * It operates with underlying Eloquent Model.
 * 
 */
abstract class BaseRepository
{
    /**
     * @var \App\Models\RestfulModel
     */
    protected $model = null;

    /**
     *
     * @throws \Exception
     */
    public function __construct()
    {
        $this->makeModel();
    }

    /**
     * Configure the Model
     *
     * @return string
     */
    abstract public static function model();

    /**
     * Make Model instance
     *
     * @throws \Exception
     *
     * @return Model
     */
    public function makeModel()
    {
        $name = static::model();
        $model = new $name;

        if (!$model instanceof Model) {
            throw new \Exception("Class {$name} must be an instance of Illuminate\\Database\\Eloquent\\Model");
        }

        return $this->model = $model;
    }

    /**
     * Build a query for retrieving all records.
     *
     * @param array $search
     * @param int|null $skip
     * @param int|null $limit
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function allQuery($search = [], $relations = [], $skip = null, $limit = null)
    {
        $query = $this->newQuery($relations);

        if (count($search)) {
            foreach($search as $key => $value) {
                if (in_array($key, $this->model->getAttributes())) {
                    $query->where($key, $value);
                }
            }
        }

        if (!is_null($skip)) {
            $query->skip($skip);
        }

        if (!is_null($limit)) {
            $query->limit($limit);
        }

        return $query;
    }

    /**
     * Retrieve all records with given filter criteria
     *
     * @param array $search
     * @param int|null $skip
     * @param int|null $limit
     * @param array $columns
     *
     * @return \Illuminate\Contracts\Pagination\LengthAwarePaginator|\Illuminate\Database\Eloquent\Builder[]|\Illuminate\Database\Eloquent\Collection
     */
    public function all($search = [], $skip = null, $limit = null, $columns = ['*'], $relations = [])
    {
        $query = $this->allQuery($search, $skip, $limit);

        return $query->get($columns);
    }

    /**
     * Create model record
     *
     * @param array $input
     *
     * @return Model
     */
    public function create($input)
    {
        $model = $this->model->newInstance($input);

        $model->save();

        return $model;
    }

    /**
     * Find model record for given id
     *
     * @param int $id
     * @param array $columns
     * @param array $relations
     *
     * @return \Illuminate\Database\Eloquent\Builder|\Illuminate\Database\Eloquent\Builder[]|\Illuminate\Database\Eloquent\Collection|Model|null
     */
    public function find($id, $columns = ['*'], $relations = [])
    {
        $query = $this->newQuery($relations);

        return $query->find($id, $columns);
    }

    /**
     * Update model record for given id
     *
     * @param array $input
     * @param int $id
     *
     * @return \Illuminate\Database\Eloquent\Builder|\Illuminate\Database\Eloquent\Builder[]|\Illuminate\Database\Eloquent\Collection|Model
     */
    public function update($input, $id)
    {
        $query = $this->newQuery();

        $model = $query->findOrFail($id);

        $model->fill($input);

        $model->save();

        return $model;
    }

    /**
     * @param int $id
     *
     * @throws \Exception
     *
     * @return bool|mixed|null
     */
    public function delete($id)
    {
        $query = $this->newQuery();

        $model = $query->findOrFail($id);

        return $model->delete();
    }

    /**
     * Creates a query for current model.
     *
     * @param array $relations
     * 
     * @return Query
     */
    public function newQuery($relations = [])
    {
        return $this->model->newQuery()->with($relations);
    }
}
