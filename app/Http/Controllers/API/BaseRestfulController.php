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

abstract class BaseRestfulController extends Controller
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;
    use Helpers;
    use Features\RestfulControllerTrait;
    use Features\AuthorizesUserActionsOnModelsTrait;

    /**
     * @var \App\Services\RestfulService
     */
    protected $restfulService = null;

    /**
     *  @var \App\Repositories\BaseRepository
     */
    protected $repository = null;

    /**
     *  @var \Illuminate\Database\Eloquent\Model
     */
    protected $model = null;

    /**
     * Specify the repository that you want to be associated with this controller.
     *
     * @return string
     */
    abstract public static function repository();

    /**
     * Returns model associated with this Controller based on its repository.
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

        if (! is_null(static::repository())) {
            $this->repository = static::makeRepository(static::repository());
            $this->model = $this->repository->makeModel();
        }
    }

    /**
     * Make Repository instance
     *
     * @param string $name
     * @throws \Exception
     *
     * @return \App\Repositories\BaseRepository
     */
    public static function makeRepository(string $name)
    {
        $repository = new $name;

        if (!$repository instanceof BaseRepository) {
            throw new \Exception("Class {$name} must be an instance of App\\Repositories\\BaseRepository");
        }

        return $repository;
    }
}
