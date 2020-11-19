<?php

namespace App\Http\Controllers;

use App\Repositories\BaseRepository;
use App\Services\RestfulService;
use App\Transformers\BaseTransformer;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Routing\Controller;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Dingo\Api\Routing\Helpers;
use App\Http\Controllers\Features\RestfulControllerTrait;
use App\Http\Controllers\Features\AuthorizesUserActionsOnModelsTrait;
use App\Models\RestfulModel;

abstract class BaseRestfulController extends Controller
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;
    use Helpers;
    use RestfulControllerTrait, AuthorizesUserActionsOnModelsTrait;

    /**
     * @var RestfulService
     */
    protected $restfulService = null;

    /**
     *  @var BaseRepository|null
     */
    protected $repository = null;

    /**
     *  @var RestfulModel
     */
    protected $model = null;

    /**
     * Specify the repository that should be associated with this controller.
     *
     * @return string
     */
    abstract public static function repository();

    /**
     * Returns model associated with this Controller (repository).
     * 
     * @return string|null
     */
    public static function model()
    {
        if (! is_null(static::repository())) {
            return static::repository()::model();
        }
    }

    /**
     * Usually a transformer will be associated with a model, however if you don't specify a model or with to
     * override the transformer at a controller level (for example if it's a controller for a dashboard resource), then
     * you can do so by specifying a transformer here
     *
     * @var null|BaseTransformer The transformer this controller should use
     */
    public static $transformer = null;

    /**
     * RestfulController constructor.
     *
     * @param RestfulService $restfulService
     */
    public function __construct(RestfulService $restfulService)
    {
        $this->restfulService = $restfulService->setModel(static::model());
        $this->repository = static::makeRepository(static::repository());
        $this->model = static::makeModel(static::model());
    }

    /**
     * Makes Model instance
     *
     * @param string $name
     * @throws \Exception
     *
     * @return RestfulModel|null
     */
    public static function makeModel($name)
    {
        if (is_null($name)) {
            return null;
        }

        $model = new $name;
        if (!$model instanceof RestfulModel) {
            throw new \Exception("Class {$name} must be an instance of App\\Models\\RestfulModel");
        }

        return $model;
    }

    /**
     * Makes Repository instance
     *
     * @param string $name
     * @throws \Exception
     *
     * @return BaseRepository|null
     */
    public static function makeRepository($name)
    {
        if (is_null($name)) {
            return null;
        }
        
        $repository = new $name;
        if (!$repository instanceof BaseRepository) {
            throw new \Exception("Class {$name} must be an instance of App\\Repositories\\BaseRepository");
        }

        return $repository;
    }
}
